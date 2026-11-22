<?php
// Run: scratch/runtime/php74/php.exe application/modules/alokasi/tests/Penampung_menu_test.php --isolated-local-db
if (PHP_SAPI !== 'cli' || !in_array('--isolated-local-db', $argv, true)) exit("Requires --isolated-local-db; creates and drops its own fixture database on localhost:3307.\n");
define('BASEPATH', __DIR__);
define('ENVIRONMENT', 'development');
require dirname(__DIR__, 3) . '/config/development/database.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$cfg = $db[$active_group];
$conn = new mysqli('127.0.0.1', $cfg['username'], $cfg['password'], '', 3307);
$fixture = 'test_alokasi_penampung_' . bin2hex(random_bytes(6));
$migration = file_get_contents(dirname(__DIR__) . '/migrations/005_register_penampung_menus.sql');
$checks = 0;
function menuCheck($ok, $message) {
    global $checks; $checks++;
    if (!$ok) throw new RuntimeException($message);
}
function runMenuSql($conn, $sql) {
    try {
        $conn->multi_query($sql);
        do { if ($result = $conn->store_result()) $result->free(); }
        while ($conn->more_results() && $conn->next_result());
    } catch (Throwable $e) { $conn->rollback(); throw $e; }
}
function menuSnapshot($conn) {
    $data = array();
    foreach (array('menus'=>'id','permissions'=>'id_permission','group_permissions'=>'id_group,id_permission','user_permissions'=>'id_user,id_permission') as $table=>$order) {
        $data[$table] = $conn->query("SELECT * FROM $table ORDER BY $order")->fetch_all(MYSQLI_ASSOC);
    }
    return $data;
}
function menuFixture($conn, $sharedPermission = false) {
    foreach (array('menus','permissions','group_permissions','user_permissions','user_groups') as $table) $conn->query("TRUNCATE $table");
    $anchorPermission = $sharedPermission ? 102 : 101;
    $conn->query("INSERT INTO menus(id,title,link,icon,target,group_menu,parent_id,permission_id,status,`order`,pusat) VALUES
        (1,'Alokasi Kalibrasi','alokasi_kalibrasi','fa fa-angle-right','sametab',1,451,$anchorPermission,1,4,0),
        (2,'Following','following','fa fa-angle-right','sametab',1,451,999,1,5,0),
        (3,'Other group','other','fa fa-angle-right','sametab',2,451,999,1,5,0)");
    $conn->query("INSERT INTO permissions(id_permission,nm_permission,id_menu) VALUES (101,'Alokasi_Kalibrasi.View',1),(102,'Alokasi.View',4)");
    $conn->query('INSERT INTO group_permissions VALUES (10,101),(10,102),(20,101),(30,102)');
    $conn->query('INSERT INTO user_groups VALUES (100,10),(102,20),(103,20),(104,30),(105,20),(105,30),(108,10)');
    $conn->query('INSERT INTO user_permissions VALUES (101,101),(101,102),(102,102),(106,101),(107,102),(108,101)');
}
try {
    $conn->query("CREATE DATABASE `$fixture`");
    $conn->select_db($fixture);
    foreach (array('menus','permissions','group_permissions','user_permissions','user_groups') as $table) {
        $conn->query("CREATE TABLE `$table` LIKE `" . $conn->real_escape_string($cfg['database']) . "`.`$table`");
    }
    menuFixture($conn);
    runMenuSql($conn, $migration);
    $menus = $conn->query("SELECT m.*,p.nm_permission FROM menus m JOIN permissions p ON p.id_permission=m.permission_id WHERE m.link LIKE 'alokasi_%' AND m.id<>1 ORDER BY m.`order`")->fetch_all(MYSQLI_ASSOC);
    menuCheck(count($menus) === 4, 'Four menus registered');
    foreach ($menus as $i=>$menu) {
        menuCheck((int)$menu['order'] === 5+$i && (int)$menu['status'] === 1 && (int)$menu['parent_id'] === 451 && (int)$menu['group_menu'] === 1 && $menu['target'] === 'sametab', 'Sidebar location inherited');
        menuCheck($menu['nm_permission'] === str_replace(' ', '_', $menu['title']) . '.View', 'Permission matches controller');
    }
    menuCheck((int)$conn->query('SELECT `order` FROM menus WHERE id=2')->fetch_row()[0] === 9, 'Following menu shifted four positions');
    menuCheck((int)$conn->query('SELECT `order` FROM menus WHERE id=3')->fetch_row()[0] === 5, 'Other group unchanged');
    foreach (array(100,101,102,105,108) as $user) {
        $n = $conn->query("SELECT COUNT(DISTINCT m.id) FROM menus m WHERE m.link LIKE 'alokasi_%' AND m.id<>1 AND
            (EXISTS(SELECT 1 FROM user_permissions up WHERE up.id_user=$user AND up.id_permission=m.permission_id)
            OR EXISTS(SELECT 1 FROM user_groups ug JOIN group_permissions gp ON ug.id_group=gp.id_group WHERE ug.id_user=$user AND gp.id_permission=m.permission_id))")->fetch_row()[0];
        menuCheck((int)$n === 4, "Effective grant inherited for user $user");
    }
    foreach (array(103,104,106,107) as $user) {
        menuCheck((int)$conn->query("SELECT COUNT(*) FROM user_permissions WHERE id_user=$user AND id_permission NOT IN (101,102)")->fetch_row()[0] === 0, 'One source permission does not expand access');
    }
    menuCheck((int)$conn->query('SELECT COUNT(*) FROM group_permissions WHERE id_group=10 AND id_permission NOT IN(101,102)')->fetch_row()[0] === 4, 'Group grants inherited');
    menuCheck((int)$conn->query('SELECT COUNT(*) FROM group_permissions WHERE id_group IN(20,30) AND id_permission NOT IN(101,102)')->fetch_row()[0] === 0, 'Partial groups do not gain access');
    menuCheck((int)$conn->query('SELECT COUNT(*) FROM user_permissions WHERE id_user IN(100,108) AND id_permission NOT IN(101,102)')->fetch_row()[0] === 0, 'No redundant direct grants for group holders');
    $snapshot = menuSnapshot($conn);
    runMenuSql($conn, $migration);
    menuCheck(menuSnapshot($conn) === $snapshot, 'Rerun leaves menus, permissions, order and grants unchanged');

    menuFixture($conn, true);
    runMenuSql($conn, $migration);
    menuCheck((int)$conn->query('SELECT COUNT(*) FROM group_permissions WHERE id_group=30 AND id_permission NOT IN(101,102)')->fetch_row()[0] === 4, 'Shared sidebar/controller permission supported');

    menuFixture($conn);
    $before = menuSnapshot($conn);
    try {
        runMenuSql($conn, str_replace('INSERT INTO permissions (', 'INSERT INTO missing_fixture_table (', $migration));
        menuCheck(false, 'Injected failure must abort');
    } catch (mysqli_sql_exception $e) { menuCheck(menuSnapshot($conn) === $before, 'Transaction failure rolls back menu writes and ordering'); }
    foreach (array('ap_targets','ap_effective_users','ap_eligible_users','ap_eligible_groups') as $table) $conn->query("DROP TEMPORARY TABLE IF EXISTS $table");

    foreach (array('DELETE FROM menus WHERE id=1', 'DELETE FROM permissions WHERE id_permission=102') as $remove) {
        menuFixture($conn); $conn->query($remove); $before = menuSnapshot($conn);
        try { runMenuSql($conn, $migration); menuCheck(false, 'Missing reference must fail'); }
        catch (mysqli_sql_exception $e) { menuCheck(menuSnapshot($conn) === $before, 'Missing reference causes no persistent changes'); }
        $conn->query('DROP TEMPORARY TABLE IF EXISTS ap_reference_guard');
    }
    echo "PASS: $checks sidebar migration checks (isolated MySQL fixture, actual project table schemas)\n";
} finally {
    $conn->query("DROP DATABASE `$fixture`");
    $conn->close();
}

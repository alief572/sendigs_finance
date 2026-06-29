<?php
$db = new mysqli('localhost', 'root', '', 'db_sendigs_finance');
$res = $db->query('DESCRIBE tr_invoicing');
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}

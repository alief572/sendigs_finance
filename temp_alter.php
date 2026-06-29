<?php
$db = new mysqli('localhost', 'root', 'root', 'db_sendigs_finance');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$sql = "ALTER TABLE db_sendigs_finance.ms_petty_cash MODIFY COLUMN approver INT(11) NULL COMMENT 'FK to users.id_user'";
if ($db->query($sql) === TRUE) {
    echo "Table altered successfully";
} else {
    echo "Error altering table: " . $db->error;
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

$active_group = 'default';
$query_builder = true;
$db['default'] = [
    'hostname' => '127.0.0.1',
    'port' => 3306,
    'username' => 'root',
    'password' => '',
    'database' => 'sendigs_upgrade_fixture',
    'dbdriver' => 'mysqli',
    'db_debug' => false,
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci',
];
$db['hris'] = $db['default'];

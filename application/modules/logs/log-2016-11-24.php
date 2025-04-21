<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2016-11-24 03:22:42 --> Severity: Warning --> mysqli::real_connect(): (HY000/1045): Access denied for user 'root'@'localhost' (using password: NO) C:\xampp\htdocs\lab\inventory_3\system\database\drivers\mysqli\mysqli_driver.php 202
ERROR - 2016-11-24 03:22:42 --> Unable to connect to the database
ERROR - 2016-11-24 03:37:46 --> Could not find the language line "bf_or"
ERROR - 2016-11-24 03:47:32 --> 404 Page Not Found: /index
ERROR - 2016-11-24 04:43:35 --> 404 Page Not Found: /index
ERROR - 2016-11-24 05:11:10 --> 404 Page Not Found: /index
ERROR - 2016-11-24 05:38:50 --> 404 Page Not Found: /index
ERROR - 2016-11-24 05:40:04 --> 404 Page Not Found: /index
ERROR - 2016-11-24 05:41:35 --> Query error: Table 'inventory_3.menusx' doesn't exist - Invalid query: SELECT `t1`.*
FROM `menusX` as `t1`
LEFT JOIN `menus` as `t2` ON `t1`.`id` = `t2`.`parent_id`
WHERE `t1`.`parent_id` =0
AND `t1`.`group_menu` = 1
AND `t1`.`status` = 1
GROUP BY `t1`.`id`
ORDER BY `t1`.`order` ASC
ERROR - 2016-11-24 07:27:53 --> Could not find the language line "bf_or"
ERROR - 2016-11-24 07:27:59 --> Could not find the language line "bf_or"
ERROR - 2016-11-24 07:28:07 --> Could not find the language line "bf_or"
ERROR - 2016-11-24 07:48:57 --> Query error: Unknown column 'user_groupsx.id_group' in 'field list' - Invalid query: SELECT `user_groupsx`.`id_group`, `groups`.`nm_group`
FROM `user_groups`
JOIN `groups` ON `user_groups`.`id_group` = `groups`.`id_group`
WHERE `id_user` = '3'
ORDER BY `nm_group` ASC
ERROR - 2016-11-24 07:49:55 --> Query error: Table 'inventory_3.user_groupsx' doesn't exist - Invalid query: SELECT *
FROM `users`
JOIN `user_groupsx` ON `users`.`id_user` = `user_groups`.`id_user`
JOIN `group_permissions` ON `user_groups`.`id_group` = `group_permissions`.`id_group`
JOIN `permissions` ON `group_permissions`.`id_permission` = `permissions`.`id_permission`
WHERE `nm_permission` = 'Supplier.View'
AND `users`.`id_user` = '3'
ERROR - 2016-11-24 07:51:15 --> Query error: Table 'inventory_3.user_permissionsx' doesn't exist - Invalid query: SELECT *
FROM `users`
JOIN `user_permissionsx` ON `users`.`id_user` = `user_permissions`.`id_user`
JOIN `permissions` ON `user_permissions`.`id_permission` = `permissions`.`id_permission`
WHERE `nm_permission` = 'Supplier.View'
AND `users`.`id_user` = '3'
ERROR - 2016-11-24 07:55:54 --> Query error: Unknown column 'permissionsx.id_permission' in 'field list' - Invalid query: SELECT `permissionsx`.`id_permission`
FROM `users`
JOIN `user_groups` ON `users`.`id_user` = `user_groups`.`id_user`
JOIN `group_permissions` ON `user_groups`.`id_group` = `group_permissions`.`id_group`
JOIN `permissions` ON `group_permissions`.`id_permission` = `permissions`.`id_permission`
WHERE `users`.`id_user` = '3'
ERROR - 2016-11-24 07:57:03 --> Query error: Unknown column 'permissionsx.id_permission' in 'field list' - Invalid query: SELECT `permissionsx`.`id_permission`
FROM `users`
JOIN `user_permissions` ON `users`.`id_user` = `user_permissions`.`id_user`
JOIN `permissions` ON `user_permissions`.`id_permission` = `permissions`.`id_permission`
WHERE `users`.`id_user` = '3'
ERROR - 2016-11-24 08:11:01 --> 404 Page Not Found: /index
ERROR - 2016-11-24 08:11:43 --> Could not find the language line "bf_or"
ERROR - 2016-11-24 08:11:50 --> Could not find the language line "bf_or"
ERROR - 2016-11-24 08:15:02 --> Query error: Table 'inventory_3.menusx' doesn't exist - Invalid query: SELECT `t1`.*
FROM `menusx` as `t1`
LEFT JOIN `menus` as `t2` ON `t1`.`id` = `t2`.`parent_id`
WHERE `t1`.`parent_id` =0
AND `t1`.`group_menu` = 1
AND `t1`.`status` = 1
GROUP BY `t1`.`id`
ORDER BY `t1`.`order` ASC
ERROR - 2016-11-24 08:16:19 --> Query error: Table 'inventory_3.menusx' doesn't exist - Invalid query: SELECT `t1`.*
FROM `menusx` as `t1`
WHERE `t1`.`parent_id` = '13'
AND `t1`.`group_menu` = 1
AND `t1`.`status` = 1
GROUP BY `t1`.`id`, `t1`.`id`
ORDER BY `t1`.`order` ASC
ERROR - 2016-11-24 09:21:19 --> 404 Page Not Found: /index
ERROR - 2016-11-24 10:33:51 --> 404 Page Not Found: /index
ERROR - 2016-11-24 10:35:43 --> Could not find the language line "bf_or"

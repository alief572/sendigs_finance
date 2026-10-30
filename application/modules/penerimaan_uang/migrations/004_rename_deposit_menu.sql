-- Rename sidebar menu "Deposit Customer" to "Monitoring Deposit" (link deposit_customer).
UPDATE `menus` SET `title` = 'Monitoring Deposit' WHERE `link` = 'deposit_customer';

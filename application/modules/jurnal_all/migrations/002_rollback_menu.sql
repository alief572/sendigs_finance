-- Restore visibility only. Deploy the corresponding routes rollback to restore old entry URLs.
START TRANSACTION;
UPDATE menus m JOIN jurnal_all_menu_backup b ON b.menu_id=m.id SET m.status=b.previous_status;
UPDATE menus SET status=0 WHERE link IN ('jurnal_all','report_jurnal_all');
COMMIT;

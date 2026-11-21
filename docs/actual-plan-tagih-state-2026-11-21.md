# Actual Plan Tagih: tab dan pencarian bertahan

Perpindahan bulan, perubahan filter, dan proses data sebelumnya membuat ulang DataTables sehingga pencarian serta posisi halaman hilang. View kini memakai satu instance dan ajax.reload(null, false); filter request membaca kontrol terkini. stateSave dengan stateDuration -1 menyimpan kondisi tabel selama sesi browser. Aktivasi tab Tagihan Macet dan validasi nilai bulan tersimpan diperbaiki.

Cakupan: views/index.php dan tests/State_client_test.js pada actual_plan_tagih. Query, controller, transaksi, jurnal dan audit writer masing-masing branch dipertahankan. Perubahan workspace lain tidak disertakan.

Validasi paket: tes client Node untuk tab, pencarian/paging, filter, proses normal/macet sukses/ditolak/error, batch, update, pemulihan bulan, search kosong, storage invalid/ditolak; lint PHP view dan pemeriksaan whitespace. Regresi audit 66 skenario dijalankan pada development; main tidak memiliki suite audit modul tersebut. Tes client memakai stub jQuery/DataTables; tanpa browser atau DB live.

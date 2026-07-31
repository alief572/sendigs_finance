-- Alter view_rekap_actual_plan_tagih_dev
-- Menambahkan kolom nm_konsultan_1, nm_konsultan_2, nm_sales dari tabel kons_tr_spk_penawaran
-- agar tidak perlu query N+1 di controller

CREATE OR REPLACE VIEW `view_rekap_actual_plan_tagih_dev` AS
SELECT
    `a`.`id_spk_penawaran` AS `id_spk_penawaran`,
    `a`.`id_customer` AS `id_customer`,
    `a`.`nm_customer` AS `nm_customer`,
    `a`.`nm_konsultan_1` AS `nm_konsultan_1`,
    `a`.`nm_konsultan_2` AS `nm_konsultan_2`,
    `a`.`nm_sales` AS `nm_sales`,
    `a`.`nilai_kontrak` AS `nilai_kontrak`,
    `c`.`id` AS `id_company`,
    `c`.`nm_company` AS `nm_company`,
    `d`.`nm_paket` AS `nm_paket`,
    `a`.`sts_spk` AS `sts_spk`,
    `a`.`input_date` AS `input_date`,
    `b`.`company` AS `id_company_ref`,
    COALESCE(`inv`.`total_invoice`, 0) AS `nominal_invoice`,
    COALESCE(`uninv`.`total_uninvoice`, 0) AS `nominal_uninvoice`,
    COALESCE(`mct`.`total_macet`, 0) AS `macet`,
    COALESCE(`mt`.`thn`, YEAR(`a`.`input_date`)) AS `tahun_data`,
    COALESCE(SUM(CASE WHEN `mt`.`bln` = 1 THEN `mt`.`nominal` END), 0) AS `jan`,
    COALESCE(SUM(CASE WHEN `mt`.`bln` = 2 THEN `mt`.`nominal` END), 0) AS `feb`,
    COALESCE(SUM(CASE WHEN `mt`.`bln` = 3 THEN `mt`.`nominal` END), 0) AS `mar`,
    COALESCE(SUM(CASE WHEN `mt`.`bln` = 4 THEN `mt`.`nominal` END), 0) AS `apr`,
    COALESCE(SUM(CASE WHEN `mt`.`bln` = 5 THEN `mt`.`nominal` END), 0) AS `may`,
    COALESCE(SUM(CASE WHEN `mt`.`bln` = 6 THEN `mt`.`nominal` END), 0) AS `jun`,
    COALESCE(SUM(CASE WHEN `mt`.`bln` = 7 THEN `mt`.`nominal` END), 0) AS `jul`,
    COALESCE(SUM(CASE WHEN `mt`.`bln` = 8 THEN `mt`.`nominal` END), 0) AS `aug`,
    COALESCE(SUM(CASE WHEN `mt`.`bln` = 9 THEN `mt`.`nominal` END), 0) AS `sep`,
    COALESCE(SUM(CASE WHEN `mt`.`bln` = 10 THEN `mt`.`nominal` END), 0) AS `oct`,
    COALESCE(SUM(CASE WHEN `mt`.`bln` = 11 THEN `mt`.`nominal` END), 0) AS `nov`,
    COALESCE(SUM(CASE WHEN `mt`.`bln` = 12 THEN `mt`.`nominal` END), 0) AS `dec`
FROM `db_consultant_new`.`kons_tr_spk_penawaran` `a`
LEFT JOIN `db_consultant_new`.`kons_tr_penawaran` `b`
    ON `b`.`id_quotation` = `a`.`id_penawaran`
LEFT JOIN `db_consultant_new`.`kons_tr_company` `c`
    ON `c`.`id` = `b`.`company`
LEFT JOIN `db_consultant_new`.`kons_master_konsultasi_header` `d`
    ON `d`.`id_konsultasi_h` = `a`.`id_project`
LEFT JOIN (
    SELECT
        `kons_tr_plan_tagih_detail`.`id_spk_penawaran` AS `id_spk_penawaran`,
        SUM(`kons_tr_plan_tagih_detail`.`nominal_payment`) AS `total_invoice`
    FROM `db_sendigs_ss`.`kons_tr_plan_tagih_detail`
    WHERE `kons_tr_plan_tagih_detail`.`sts_invoice` = '1'
    GROUP BY `kons_tr_plan_tagih_detail`.`id_spk_penawaran`
) `inv` ON `inv`.`id_spk_penawaran` = `a`.`id_spk_penawaran`
LEFT JOIN (
    SELECT
        `kons_tr_plan_tagih_detail`.`id_spk_penawaran` AS `id_spk_penawaran`,
        SUM(`kons_tr_plan_tagih_detail`.`nominal_payment`) AS `total_uninvoice`
    FROM `db_sendigs_ss`.`kons_tr_plan_tagih_detail`
    WHERE `kons_tr_plan_tagih_detail`.`sts_invoice` <> '1'
      AND `kons_tr_plan_tagih_detail`.`status_terakhir` <> '3'
    GROUP BY `kons_tr_plan_tagih_detail`.`id_spk_penawaran`
) `uninv` ON `uninv`.`id_spk_penawaran` = `a`.`id_spk_penawaran`
LEFT JOIN (
    SELECT
        `kons_tr_plan_tagih_detail`.`id_spk_penawaran` AS `id_spk_penawaran`,
        SUM(`kons_tr_plan_tagih_detail`.`nominal_payment`) AS `total_macet`
    FROM `db_sendigs_ss`.`kons_tr_plan_tagih_detail`
    WHERE `kons_tr_plan_tagih_detail`.`status_terakhir` = '3'
      AND `kons_tr_plan_tagih_detail`.`sts_invoice` <> '1'
    GROUP BY `kons_tr_plan_tagih_detail`.`id_spk_penawaran`
) `mct` ON `mct`.`id_spk_penawaran` = `a`.`id_spk_penawaran`
LEFT JOIN (
    SELECT
        `tmp`.`id_spk_penawaran` AS `id_spk_penawaran`,
        YEAR(`tmp`.`tanggal_actual_plan_tagih`) AS `thn`,
        MONTH(`tmp`.`tanggal_actual_plan_tagih`) AS `bln`,
        SUM(`tmp`.`nominal`) AS `nominal`
    FROM (
        SELECT
            `act`.`id_spk_penawaran` AS `id_spk_penawaran`,
            `act`.`tanggal_actual_plan_tagih` AS `tanggal_actual_plan_tagih`,
            `det`.`nominal_payment` AS `nominal`,
            ROW_NUMBER() OVER (PARTITION BY `act`.`id_detail_plan_tagih` ORDER BY `act`.`created_date` DESC) AS `rn`,
            `act`.`tagih_mundur` AS `tagih_mundur`
        FROM `db_sendigs_ss`.`kons_tr_actual_plan_tagih` `act`
        JOIN `db_sendigs_ss`.`kons_tr_plan_tagih_detail` `det`
            ON `act`.`id_detail_plan_tagih` = `det`.`id`
    ) `tmp`
    WHERE `tmp`.`rn` = 1
      AND `tmp`.`tagih_mundur` IN ('1', '2')
    GROUP BY `tmp`.`id_spk_penawaran`, `thn`, `bln`

    UNION ALL

    SELECT
        `p`.`id_spk_penawaran` AS `id_spk_penawaran`,
        YEAR(`p`.`tgl_plan_tagih`) AS `thn`,
        MONTH(`p`.`tgl_plan_tagih`) AS `bln`,
        SUM(`p`.`nominal_payment`) AS `nominal`
    FROM `db_sendigs_ss`.`kons_tr_plan_tagih_detail` `p`
    WHERE EXISTS(
        SELECT 1
        FROM `db_sendigs_ss`.`kons_tr_actual_plan_tagih` `act`
        WHERE `act`.`id_detail_plan_tagih` = `p`.`id`
          AND `act`.`tagih_mundur` IN ('1', '2', '3')
    ) IS FALSE
    GROUP BY `p`.`id_spk_penawaran`, `thn`, `bln`
) `mt` ON `mt`.`id_spk_penawaran` = `a`.`id_spk_penawaran`
GROUP BY `a`.`id_spk_penawaran`, `tahun_data`;

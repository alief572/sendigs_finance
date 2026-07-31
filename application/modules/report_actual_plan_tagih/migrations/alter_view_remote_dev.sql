-- Alter view_rekap_actual_plan_tagih_dev di REMOTE (db_sendigs_ss_dev)
-- Menambahkan kolom nm_konsultan_1, nm_konsultan_2, nm_sales

CREATE OR REPLACE VIEW `view_rekap_actual_plan_tagih_dev` AS
select
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
    coalesce(`inv`.`total_invoice`,0) AS `nominal_invoice`,
    (`a`.`nilai_kontrak` - coalesce(`inv`.`total_invoice`,0)) AS `nominal_uninvoice`,
    coalesce(`mct`.`total_macet`,0) AS `macet`,
    coalesce(`mt`.`thn`,year(`a`.`input_date`)) AS `tahun_data`,
    coalesce(sum((case when (`mt`.`bln` = 1) then `mt`.`nominal` end)),0) AS `jan`,
    coalesce(sum((case when (`mt`.`bln` = 2) then `mt`.`nominal` end)),0) AS `feb`,
    coalesce(sum((case when (`mt`.`bln` = 3) then `mt`.`nominal` end)),0) AS `mar`,
    coalesce(sum((case when (`mt`.`bln` = 4) then `mt`.`nominal` end)),0) AS `apr`,
    coalesce(sum((case when (`mt`.`bln` = 5) then `mt`.`nominal` end)),0) AS `may`,
    coalesce(sum((case when (`mt`.`bln` = 6) then `mt`.`nominal` end)),0) AS `jun`,
    coalesce(sum((case when (`mt`.`bln` = 7) then `mt`.`nominal` end)),0) AS `jul`,
    coalesce(sum((case when (`mt`.`bln` = 8) then `mt`.`nominal` end)),0) AS `aug`,
    coalesce(sum((case when (`mt`.`bln` = 9) then `mt`.`nominal` end)),0) AS `sep`,
    coalesce(sum((case when (`mt`.`bln` = 10) then `mt`.`nominal` end)),0) AS `oct`,
    coalesce(sum((case when (`mt`.`bln` = 11) then `mt`.`nominal` end)),0) AS `nov`,
    coalesce(sum((case when (`mt`.`bln` = 12) then `mt`.`nominal` end)),0) AS `dec`
from ((((((`db_consultant_new_dev`.`kons_tr_spk_penawaran` `a`
    left join `db_consultant_new_dev`.`kons_tr_penawaran` `b` on((`b`.`id_quotation` = `a`.`id_penawaran`)))
    left join `db_consultant_new_dev`.`kons_tr_company` `c` on((`c`.`id` = `b`.`company`)))
    left join `db_consultant_new_dev`.`kons_master_konsultasi_header` `d` on((`d`.`id_konsultasi_h` = `a`.`id_project`)))
    left join (
        select
            `db_sendigs_ss_dev`.`tr_invoicing`.`id_spk_penawaran` AS `id_spk_penawaran`,
            sum(`db_sendigs_ss_dev`.`tr_invoicing`.`total_nominal`) AS `total_invoice`
        from `db_sendigs_ss_dev`.`tr_invoicing`
        group by `db_sendigs_ss_dev`.`tr_invoicing`.`id_spk_penawaran`
    ) `inv` on((`inv`.`id_spk_penawaran` = `a`.`id_spk_penawaran`)))
    left join (
        select
            `ats`.`id_spk_penawaran` AS `id_spk_penawaran`,
            sum(`pd`.`nominal_payment`) AS `total_macet`
        from (
            (select
                `db_sendigs_ss_dev`.`kons_tr_actual_plan_tagih`.`id_spk_penawaran` AS `id_spk_penawaran`,
                `db_sendigs_ss_dev`.`kons_tr_actual_plan_tagih`.`id_detail_plan_tagih` AS `id_detail_plan_tagih`,
                `db_sendigs_ss_dev`.`kons_tr_actual_plan_tagih`.`tagih_mundur` AS `tagih_mundur`,
                `db_sendigs_ss_dev`.`kons_tr_actual_plan_tagih`.`macet` AS `macet`,
                row_number() OVER (PARTITION BY `db_sendigs_ss_dev`.`kons_tr_actual_plan_tagih`.`id_detail_plan_tagih` ORDER BY `db_sendigs_ss_dev`.`kons_tr_actual_plan_tagih`.`created_date` desc) AS `rn`
            from `db_sendigs_ss_dev`.`kons_tr_actual_plan_tagih`) `ats`
            join `db_sendigs_ss_dev`.`kons_tr_plan_tagih_detail` `pd` on((`ats`.`id_detail_plan_tagih` = `pd`.`id`))
        )
        where ((`ats`.`rn` = 1) and ((`ats`.`tagih_mundur` = '3') or (`ats`.`macet` = '1')))
        group by `ats`.`id_spk_penawaran`
    ) `mct` on((`mct`.`id_spk_penawaran` = `a`.`id_spk_penawaran`)))
    left join (
        select
            `tmp`.`id_spk_penawaran` AS `id_spk_penawaran`,
            `tmp`.`thn` AS `thn`,
            `tmp`.`bln` AS `bln`,
            sum(`tmp`.`nominal`) AS `nominal`
        from (
            select
                `act`.`id_spk_penawaran` AS `id_spk_penawaran`,
                year(`act`.`tanggal_actual_plan_tagih`) AS `thn`,
                month(`act`.`tanggal_actual_plan_tagih`) AS `bln`,
                `det`.`nominal_payment` AS `nominal`,
                row_number() OVER (PARTITION BY `act`.`id_detail_plan_tagih` ORDER BY `act`.`created_date` desc) AS `rn`,
                `act`.`tagih_mundur` AS `tagih_mundur`
            from (`db_sendigs_ss_dev`.`kons_tr_actual_plan_tagih` `act`
                join `db_sendigs_ss_dev`.`kons_tr_plan_tagih_detail` `det` on((`act`.`id_detail_plan_tagih` = `det`.`id`)))
        ) `tmp`
        where ((`tmp`.`rn` = 1) and (`tmp`.`tagih_mundur` in ('1','2')))
        group by `tmp`.`id_spk_penawaran`,`tmp`.`thn`,`tmp`.`bln`

        union all

        select
            `p`.`id_spk_penawaran` AS `id_spk_penawaran`,
            year(`p`.`tgl_plan_tagih`) AS `thn`,
            month(`p`.`tgl_plan_tagih`) AS `bln`,
            sum(`p`.`nominal_payment`) AS `nominal`
        from (`db_sendigs_ss_dev`.`kons_tr_plan_tagih_detail` `p`
            left join `db_sendigs_ss_dev`.`kons_tr_actual_plan_tagih` `act` on((`p`.`id` = `act`.`id_detail_plan_tagih`)))
        where (`act`.`id` is null)
        group by `p`.`id_spk_penawaran`,`thn`,`bln`
    ) `mt` on((`mt`.`id_spk_penawaran` = `a`.`id_spk_penawaran`)))
group by `a`.`id_spk_penawaran`,`tahun_data`;

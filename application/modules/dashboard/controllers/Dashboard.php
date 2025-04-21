<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends Admin_Controller
{
	/*
 * @author Yunaz
 * @copyright Copyright (c) 2016, Yunaz
 *
 * This is controller for Penerimaan
 */
	public function __construct()
	{
		parent::__construct();

		$this->load->model('dashboard/dashboard_model');

		$this->template->page_icon('fa fa-dashboard');
	}

	public function index()
	{
		$this->template->title('Dashboard');
		$no_so = $this->uri->segment(3);
		$no_so2 = $this->uri->segment(4);
		// echo $no_so;
		if (empty($no_so)) {
			$sql_beet = $this->db->query("SELECT no_so FROM sales_order_header WHERE delivery_date >= DATE(NOW()) LIMIT 1")->result();
			if (!empty($sql_beet)) {
				$no_so = $sql_beet[0]->no_so;
			}
		}
		if (empty($no_so2)) {
			$sql_beet = $this->db->query("SELECT no_so FROM sales_order_header WHERE delivery_date >= DATE(NOW()) LIMIT 1")->result();
			if (!empty($sql_beet)) {
				$no_so2 = $sql_beet[0]->no_so;
			}
		}

		// $no_so = 'SO200022';

		$sql_order1 	= "SELECT SUM(a.qty_propose - a.qty_delivery) AS qty_order FROM sales_order_detail a LEFT JOIN ms_inventory_category2 b ON a.product=b.id_category2 WHERE a.no_so = '" . $no_so . "' AND b.id_category1 <> 'I2000002'";
		$rest_order1 	= $this->db->query($sql_order1)->result();

		$sql_order2 	= "SELECT SUM(a.qty_propose - a.qty_delivery) AS qty_order FROM sales_order_detail a LEFT JOIN ms_inventory_category2 b ON a.product=b.id_category2 WHERE a.no_so = '" . $no_so2 . "' AND b.id_category1 = 'I2000002'";
		$rest_order2 	= $this->db->query($sql_order2)->result();

		$sql_propose1 	= "SELECT SUM(a.qty_order - a.qty_delivery) AS qty_propose FROM sales_order_detail a LEFT JOIN ms_inventory_category2 b ON a.product=b.id_category2 WHERE a.no_so = '" . $no_so . "' AND b.id_category1 <> 'I2000002'";
		$rest_propose1 	= $this->db->query($sql_propose1)->result();

		$sql_propose2 	= "SELECT SUM(a.qty_order - a.qty_delivery) AS qty_propose FROM sales_order_detail a LEFT JOIN ms_inventory_category2 b ON a.product=b.id_category2 WHERE a.no_so = '" . $no_so2 . "' AND b.id_category1 = 'I2000002'";
		$rest_propose2 	= $this->db->query($sql_propose2)->result();

		$sql_fg1 	= "SELECT
									SUM( IF(a.qty_stock > c.qty_order,c.qty_order,a.qty_stock ) ) AS qty_stock,
									SUM(a.qty_stock) AS qty_over
								FROM
									warehouse_product a
									LEFT JOIN ms_inventory_category2 b ON a.id_product = b.id_category2
									LEFT JOIN sales_order_detail c ON a.id_product = c.product
								WHERE
									a.category = 'product'
									AND b.id_category1 <> 'I2000002'
									AND c.qty_order > 0
									AND c.no_so = '" . $no_so . "'";
		$rest_fg1 	= $this->db->query($sql_fg1)->result();

		$sql_fg2 	= "SELECT
									SUM( IF(a.qty_stock > c.qty_order,c.qty_order,a.qty_stock ) ) AS qty_stock,
									SUM(a.qty_stock) AS qty_over
								FROM
									warehouse_product a
									LEFT JOIN ms_inventory_category2 b ON a.id_product = b.id_category2
									LEFT JOIN sales_order_detail c ON a.id_product = c.product
								WHERE
									a.category = 'product'
									AND b.id_category1 = 'I2000002'
									AND c.qty_order > 0
									AND c.no_so = '" . $no_so2 . "'";
		$rest_fg2 	= $this->db->query($sql_fg2)->result();

		$qty_order1 = ($rest_order1[0]->qty_order > 0) ? $rest_order1[0]->qty_order : 0;
		$qty_order2 = ($rest_order2[0]->qty_order > 0) ? $rest_order2[0]->qty_order : 0;

		$qty_propose1 = ($rest_propose1[0]->qty_propose > 0) ? $rest_propose1[0]->qty_propose : 0;
		$qty_propose2 = ($rest_propose2[0]->qty_propose > 0) ? $rest_propose2[0]->qty_propose : 0;

		$qtyfg1 = ($rest_fg1[0]->qty_stock > 0) ? $rest_fg1[0]->qty_stock : 0;
		$qtyfg2 = ($rest_fg2[0]->qty_stock > 0) ? $rest_fg2[0]->qty_stock : 0;

		$qtybal1 = $qty_propose1 - $qtyfg1;
		$qtybal2 = $qty_propose2 - $qtyfg2;

		$progres1 = 0;
		$progres2 = 0;
		if ($qtyfg1 > 0 and $qty_propose1 > 0) {
			$progres1 = ($qtyfg1 / $qty_propose1) * 100;
		}
		if ($qtyfg2 > 0 and $qty_propose2 > 0) {
			$progres2 = ($qtyfg2 / $qty_propose2) * 100;
		}

		$over1 = $rest_fg1[0]->qty_over - $qty_propose1;
		$over2 = $rest_fg2[0]->qty_over - $qty_propose2;
		if ($rest_fg1[0]->qty_over - $qty_propose1 < 0) {
			$over1 = 0;
		}
		if ($rest_fg2[0]->qty_over - $qty_propose2 < 0) {
			$over2 = 0;
		}

		// echo $rest_fg1[0]->qty_stock;

		$data = array(
			'qty_order1' => $qty_order1,
			'qty_order2' => $qty_order2,
			'qty_propose1' => $qty_propose1,
			'qty_propose2' => $qty_propose2,
			'qtyfg1' => $qtyfg1,
			'qtyfg2' => $qtyfg2,
			'qtybal1' => $qtybal1,
			'qtybal2' => $qtybal2,
			'progres1' => $progres1,
			'progres2' => $progres2,
			'no_so' => $no_so,
			'no_so2' => $no_so2,
			'over1' => $over1,
			'over2' => $over2
		);

		$this->template->render('index', $data);
	}

	public function purchase()
	{
		// $this->template->set('results');

		$get_list_late_pr_approval = $this->db->query('
			SELECT
				a.no_pr as no_pr,
				"PPIC" as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY) as max_approval_date,
				DATEDIFF(IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) as late_day
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN material_planning_base_on_produksi_detail b ON b.so_number = a.so_number
			WHERE
				b.status_app != "Y" AND
				a.sts_app != "Y" AND 
				(a.category = "pr material" OR a.category = "pr stok") AND
				a.rejected IS NULL AND
				DATEDIFF(IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) > 0
			GROUP BY a.so_number

			UNION ALL

			SELECT
				a.no_pengajuan as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY) as max_approval_date,
				DATEDIFF(IF(a.sts_app_date IS NOT NULL, DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) as late_day
			FROM
				rutin_non_planning_header a
				LEFT JOIN ms_department b ON b.id = a.id_dept
			WHERE
				a.sts_app != "Y" AND
				a.rejected IS NULL AND
				DATEDIFF(IF(a.sts_app_date IS NOT NULL, DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) > 0
			ORDER BY late_day DESC
		')->result_array();

		$get_list_all_pr_approval = $this->db->query('
			SELECT
				a.no_pr as no_pr,
				"PPIC" as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY) as max_approval_date,
				DATEDIFF(IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) as late_day,
				IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), "") as aktual
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN material_planning_base_on_produksi_detail b ON b.so_number = a.so_number	
			GROUP BY a.so_number

			UNION ALL

			SELECT
				a.no_pengajuan as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY) as max_approval_date,
				DATEDIFF(IF(a.sts_app_date IS NOT NULL, DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) as late_day,
				IF(a.sts_app_date IS NOT NULL, DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), "") as aktual
			FROM
				rutin_non_planning_header a
				LEFT JOIN ms_department b ON b.id = a.id_dept
			WHERE
				a.rejected IS NULL
			GROUP BY a.no_pengajuan
			ORDER BY late_day DESC
		')->result_array();

		$get_list_all_pr_approval_summary = $this->db->query('
			SELECT
				a.no_pr as no_pr,
				"PPIC" as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY) as max_approval_date,
				DATEDIFF(IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) as late_day,
				IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), "") as aktual
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN material_planning_base_on_produksi_detail b ON b.so_number = a.so_number	
			WHERE
				a.rejected IS NULL AND
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y"), INTERVAL 2 DAY) = "' . date('Y') . '"
			GROUP BY a.so_number

			UNION ALL

			SELECT
				a.no_pengajuan as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY) as max_approval_date,
				DATEDIFF(IF(a.sts_app_date IS NOT NULL, DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) as late_day,
				IF(a.sts_app_date IS NOT NULL, DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), "") as aktual
			FROM
				rutin_non_planning_header a
				LEFT JOIN ms_department b ON b.id = a.id_dept
			WHERE
				a.rejected IS NULL AND
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y"), INTERVAL 2 DAY) = "' . date('Y') . '"
			GROUP BY a.no_pengajuan
		')->result_array();

		$all_month_count = 12;
		$summary_pr_approval = array();
		for ($i = 1; $i <= $all_month_count; $i++) {
			$approval_percentage = 0;
			$late_approval = 0;
			$ontime_approval = 0;
			$all_approval = 0;
			foreach ($get_list_all_pr_approval_summary as $all_pr_approval) {

				if (date('m', strtotime($all_pr_approval['max_approval_date'])) == sprintf('%02d', $i)) {
					if ($all_pr_approval['aktual'] <= $all_pr_approval['max_approval_date']) {
						$ontime_approval += 1;
					} else {
						$late_approval += 1;
					}
					$all_approval += 1;
				}
			}

			if ($all_approval > 0) {
				$approval_percentage = ($late_approval / $all_approval) * 100;
			}

			$summary_pr_approval[$i] = $approval_percentage;
		}

		$get_list_late_po_release = $this->db->query('
			SELECT
				a.no_pr as no_pr,
				"PPIC" as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY) as max_approval_date,
				DATEDIFF(IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) as late_day,
				IF(d.created_on IS NULL, "", DATE_FORMAT(d.created_on, "%Y-%m-%d")) as aktual
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN material_planning_base_on_produksi_detail b ON b.so_number = a.so_number
				LEFT JOIN dt_trans_po c ON c.idpr = b.id
				LEFT JOIN tr_purchase_order d ON d.no_po = c.no_po
			WHERE
				b.status_app = "Y" AND 
				a.rejected IS NULL AND
				DATEDIFF(DATE_FORMAT(NOW(), "%Y-%m-%d"), DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) > 0 AND
				(SELECT COUNT(aa.no_surat) FROM tr_purchase_order aa LEFT JOIN dt_trans_po ab ON ab.no_po = aa.no_po WHERE ab.idpr = b.id AND aa.tipe IS NULL) < 1
			GROUP BY a.so_number

			UNION ALL

			SELECT
				a.no_pr as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY) as max_approval_date,
				DATEDIFF(DATE_FORMAT(NOW(), "%Y-%m-%d"), DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) as late_day,
				IF(e.created_on IS NULL, "", DATE_FORMAT(e.created_on, "%Y-%m-%d")) as aktual
			FROM
				rutin_non_planning_header a
				LEFT JOIN ms_department b ON b.id = a.id_dept
				LEFT JOIN rutin_non_planning_detail c ON c.no_pengajuan = a.no_pengajuan
				LEFT JOIN dt_trans_po d ON d.idpr = c.id
				LEFT JOIN tr_purchase_order e ON e.no_po = d.no_po
			WHERE
				a.sts_app = "Y" AND
				a.rejected IS NULL AND
				DATEDIFF(DATE_FORMAT(NOW(), "%Y-%m-%d"), DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) > 0 AND
				(SELECT COUNT(aa.no_surat) FROM tr_purchase_order aa LEFT JOIN dt_trans_po ab ON ab.no_po = aa.no_po WHERE ab.idpr = b.id AND aa.tipe = "pr depart") < 1
			GROUP BY a.no_pengajuan
			ORDER BY late_day DESC
		')->result_array();

		$get_list_all_po_release = $this->db->query('
			SELECT
				a.no_pr as no_pr,
				"PPIC" as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY) as max_approval_date,
				DATEDIFF(IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) as late_day,
				IF(d.created_on IS NULL, "", DATE_FORMAT(d.created_on, "%Y-%m-%d")) as aktual
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN material_planning_base_on_produksi_detail b ON b.so_number = a.so_number
				LEFT JOIN dt_trans_po c ON c.idpr = b.id
				LEFT JOIN tr_purchase_order d ON d.no_po = c.no_po
			WHERE
				b.status_app = "Y" AND 
				a.rejected IS NULL AND
				(SELECT COUNT(aa.no_surat) FROM tr_purchase_order aa LEFT JOIN dt_trans_po ab ON ab.no_po = aa.no_po WHERE ab.idpr = b.id AND aa.tipe IS NULL) < 1
			GROUP BY a.so_number

			UNION ALL

			SELECT
				a.no_pr as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY) as max_approval_date,
				DATEDIFF(DATE_FORMAT(NOW(), "%Y-%m-%d"), DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) as late_day,
				IF(e.created_on IS NULL, "", DATE_FORMAT(e.created_on, "%Y-%m-%d")) as aktual
			FROM
				rutin_non_planning_header a
				LEFT JOIN ms_department b ON b.id = a.id_dept
				LEFT JOIN rutin_non_planning_detail c ON c.no_pengajuan = a.no_pengajuan
				LEFT JOIN dt_trans_po d ON d.idpr = c.id
				LEFT JOIN tr_purchase_order e ON e.no_po = d.no_po
			WHERE
				a.sts_app = "Y" AND
				a.rejected IS NULL AND
				(SELECT COUNT(aa.no_surat) FROM tr_purchase_order aa LEFT JOIN dt_trans_po ab ON ab.no_po = aa.no_po WHERE ab.idpr = b.id AND aa.tipe = "pr depart") < 1
			GROUP BY a.no_pengajuan
			ORDER BY late_day DESC
		')->result_array();

		$get_list_all_po_release_summary = $this->db->query('
			SELECT
				a.no_pr as no_pr,
				"PPIC" as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY) as max_approval_date,
				DATEDIFF(IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) as late_day,
				IF(d.created_on IS NULL, "", DATE_FORMAT(d.created_on, "%Y-%m-%d")) as aktual
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN material_planning_base_on_produksi_detail b ON b.so_number = a.so_number
				LEFT JOIN dt_trans_po c ON c.idpr = b.id
				LEFT JOIN tr_purchase_order d ON d.no_po = c.no_po
			WHERE
				b.status_app = "Y" AND 
				a.rejected IS NULL AND
				(SELECT COUNT(aa.no_surat) FROM tr_purchase_order aa LEFT JOIN dt_trans_po ab ON ab.no_po = aa.no_po WHERE ab.idpr = b.id AND aa.tipe IS NULL) < 1 AND
				DATE_ADD(DATE_FORMAT(b.app_date, "%Y"), INTERVAL 4 DAY) = "' . date('Y') . '"
			GROUP BY a.so_number

			UNION ALL

			SELECT
				a.no_pr as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY) as max_approval_date,
				DATEDIFF(DATE_FORMAT(NOW(), "%Y-%m-%d"), DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) as late_day,
				IF(e.created_on IS NULL, "", DATE_FORMAT(e.created_on, "%Y-%m-%d")) as aktual
			FROM
				rutin_non_planning_header a
				LEFT JOIN ms_department b ON b.id = a.id_dept
				LEFT JOIN rutin_non_planning_detail c ON c.no_pengajuan = a.no_pengajuan
				LEFT JOIN dt_trans_po d ON d.idpr = c.id
				LEFT JOIN tr_purchase_order e ON e.no_po = d.no_po
			WHERE
				a.sts_app = "Y" AND
				a.rejected IS NULL AND
				(SELECT COUNT(aa.no_surat) FROM tr_purchase_order aa LEFT JOIN dt_trans_po ab ON ab.no_po = aa.no_po WHERE ab.idpr = b.id AND aa.tipe = "pr depart") < 1 AND
				DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y"), INTERVAL 4 DAY) = "' . date('Y') . '" 
			ORDER BY late_day DESC
		')->result_array();

		$summary_po_release = array();
		for ($i = 1; $i <= 12; $i++) {
			$release_percentage = 0;
			$late_release = 0;
			$ontime_release = 0;
			$all_release = 0;
			foreach ($get_list_all_po_release_summary as $all_po_release) {

				if (date('m', strtotime($all_po_release['max_approval_date'])) == sprintf('%02d', $i)) {
					if ($all_po_release['aktual'] <= $all_po_release['max_approval_date']) {
						$ontime_release += 1;
					} else {
						$late_release += 1;
					}
					$all_release += 1;
				}
			}

			if ($all_release > 0) {
				$release_percentage = ($late_release / $all_release) * 100;
			}

			$summary_po_release[$i] = $release_percentage;
		}


		$get_all_approved_pr = $this->db->query("
			SELECT
				a.so_number as so_number,
				a.no_pr as no_pr,
				'PPIC' as department,
				DATE_FORMAT(a.created_date, '%Y-%m-%d') as pr_date,
				'non depart' as tipe,
				a.category as tipe_pr
			FROM
				material_planning_base_on_produksi a 
			WHERE
				DATE_FORMAT(a.created_date, '%Y') = '" . date('Y') . "' AND
				a.rejected IS NULL AND
				(a.category = 'pr material' OR a.category = 'pr stok') AND
				a.close_pr IS NULL
			UNION ALL

			SELECT 
				a.no_pengajuan as so_number,
				a.no_pr as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, '%Y-%m-%d') as pr_date,
				'pr depart' as tipe,
				'PR Departement' as tipe_pr
			FROM
				rutin_non_planning_header a 
				LEFT JOIN ms_department b ON b.id = a.id_dept
			WHERE
				a.rejected IS NULL AND
				DATE_FORMAT(a.created_date, '%Y') = '" . date('Y') . "' AND
				a.close_pr IS NULL
		")->result_array();

		$this->template->set('list_late_pr_approval', $get_list_late_pr_approval);
		$this->template->set('list_all_pr_approval', $get_list_all_pr_approval);
		$this->template->set('summary_pr_approval', $summary_pr_approval);
		$this->template->set('list_late_po_release', $get_list_late_po_release);
		$this->template->set('list_all_po_release', $get_list_all_po_release);
		$this->template->set('summary_po_release', $summary_po_release);
		$this->template->set('list_all_approved_pr', $get_all_approved_pr);
		$this->template->title('Dashboard Purchase');
		$this->template->render('dashboard_purchase');
	}

	public function list_approval_pr()
	{
		$get_list_late_pr_approval = $this->db->query('
			SELECT
				a.no_pr as no_pr,
				"PPIC" as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY) as max_approval_date,
				DATEDIFF(IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) as late_day
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN material_planning_base_on_produksi_detail b ON b.so_number = a.so_number
			WHERE
				b.status_app != "Y" AND
				a.sts_app != "Y" AND 
				a.rejected IS NULL AND
				(a.category = "pr material" OR a.category = "pr stok") AND
				DATEDIFF(IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) > 0 AND
				DATE_FORMAT(a.created_date, "%Y") = "' . date('Y') . '" AND
				a.close_pr IS NULL
			GROUP BY a.so_number

			UNION ALL

			SELECT
				a.no_pengajuan as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY) as max_approval_date,
				DATEDIFF(IF(a.sts_app_date IS NOT NULL, DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) as late_day
			FROM
				rutin_non_planning_header a
				LEFT JOIN ms_department b ON b.id = a.id_dept
			WHERE
				a.sts_app != "Y" AND
				a.rejected IS NULL AND
				DATEDIFF(IF(a.sts_app_date IS NOT NULL, DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) > 0 AND
				DATE_FORMAT(a.created_date, "%Y") = "' . date('Y') . '" AND
				a.close_pr IS NULL
			ORDER BY late_day DESC
		')->result_array();

		$get_list_all_pr_approval = $this->db->query('
			SELECT
				a.no_pr as no_pr,
				"PPIC" as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY) as max_approval_date,
				DATEDIFF(IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) as late_day,
				IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), "") as aktual
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN material_planning_base_on_produksi_detail b ON b.so_number = a.so_number
			WHERE
				DATE_FORMAT(a.created_date, "%Y") = "' . date('Y') . '" AND
				a.rejected IS NULL AND
				(a.category = "pr material" OR a.category = "pr stok") AND
				a.close_pr IS NULL
			GROUP BY a.so_number

			UNION ALL

			SELECT
				a.no_pengajuan as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY) as max_approval_date,
				DATEDIFF(IF(a.sts_app_date IS NOT NULL, DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) as late_day,
				IF(a.sts_app_date IS NOT NULL, DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), "") as aktual
			FROM
				rutin_non_planning_header a
				LEFT JOIN ms_department b ON b.id = a.id_dept
			WHERE
				a.rejected IS NULL AND
				DATE_FORMAT(a.created_date, "%Y") = "' . date('Y') . '" AND
				a.close_pr IS NULL
			GROUP BY a.no_pengajuan
			ORDER BY late_day DESC
		')->result_array();

		$get_list_all_pr_approval_summary = $this->db->query('
			SELECT
				a.no_pr as no_pr,
				"PPIC" as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY) as max_approval_date,
				DATEDIFF(IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) as late_day,
				IF(b.app_date IS NOT NULL, DATE_FORMAT(b.app_date, "%Y-%m-%d"), "") as aktual
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN material_planning_base_on_produksi_detail b ON b.so_number = a.so_number	
			WHERE
				DATE_FORMAT(a.created_date, "%Y") = "' . date('Y') . '" AND
				(a.category = "pr material" OR a.category = "pr stok") AND
				a.close_pr IS NULL
			GROUP BY a.so_number

			UNION ALL

			SELECT
				a.no_pengajuan as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY) as max_approval_date,
				DATEDIFF(IF(a.sts_app_date IS NOT NULL, DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.created_date, "%Y-%m-%d"), INTERVAL 2 DAY)) as late_day,
				IF(a.sts_app_date IS NOT NULL, DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), "") as aktual
			FROM
				rutin_non_planning_header a
				LEFT JOIN ms_department b ON b.id = a.id_dept
			WHERE
				a.rejected IS NULL AND
				DATE_FORMAT(a.created_date, "%Y") = "' . date('Y') . '" AND
				a.close_pr IS NULL
			GROUP BY a.no_pengajuan
		')->result_array();

		$all_month_count = 12;
		$summary_pr_approval = array();
		for ($i = 1; $i <= 12; $i++) {
			$approval_percentage = 0;
			$late_approval = 0;
			$ontime_approval = 0;
			$all_approval = 0;
			foreach ($get_list_all_pr_approval_summary as $all_pr_approval) {
				if (str_replace(' ', '', $all_pr_approval['max_approval_date']) !== "") {
					if (date('m', strtotime($all_pr_approval['max_approval_date'])) == sprintf('%02d', $i)) {
						if ($all_pr_approval['aktual'] <= $all_pr_approval['max_approval_date']) {
							$ontime_approval += 1;
						} else {
							if ($all_pr_approval['aktual'] !== '') {
								$late_approval += 1;
							}
						}
						if ($all_approval['aktual'] !== '') {
							$all_approval += 1;
						}
					}
				}
			}

			if ($all_approval > 0) {
				$approval_percentage = ceil(($late_approval / $all_approval) * 100);
				if ($late_approval < 1 && $all_approval > 0) {
					$approval_percentage = 100;
				}
			}

			$summary_pr_approval[$i] = $approval_percentage;
		}


		$data = array();
		$data['list_late_pr_approval'] = $get_list_late_pr_approval;
		$data['list_all_pr_approval'] = $get_list_all_pr_approval;
		$data['summary_pr_approval'] = $summary_pr_approval;
		$this->load->view('list_approval_po', $data);
	}

	public function list_po_release()
	{

		$get_all_approved_pr = $this->db->query("
			SELECT
				a.so_number as so_number,
				a.no_pr as no_pr,
				'PPIC' as department,
				DATE_FORMAT(a.created_date, '%Y-%m-%d') as pr_date,
				'non depart' as tipe,
				a.category as tipe_pr
			FROM
				material_planning_base_on_produksi a 
			WHERE
				DATE_FORMAT(a.created_date, '%Y') = '" . date('Y') . "' AND
				a.rejected IS NULL AND
				(a.category = 'pr material' OR a.category = 'pr stok') AND
				a.close_pr IS NULL
			UNION ALL

			SELECT 
				a.no_pengajuan as so_number,
				a.no_pr as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, '%Y-%m-%d') as pr_date,
				'pr depart' as tipe,
				'PR Departement' as tipe_pr
			FROM
				rutin_non_planning_header a 
				LEFT JOIN ms_department b ON b.id = a.id_dept
			WHERE
				a.rejected IS NULL AND
				DATE_FORMAT(a.created_date, '%Y') = '" . date('Y') . "' AND
				a.close_pr IS NULL
		")->result_array();

		$get_list_late_po_release = $this->db->query('
			SELECT
				a.no_pr as no_pr,
				"PPIC" as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY) as max_approval_date,
				DATEDIFF(IF(d.created_on IS NOT NULL, DATE_FORMAT(d.created_on, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) as late_day,
				IF(d.created_on IS NULL, "", DATE_FORMAT(d.created_on, "%Y-%m-%d")) as aktual
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN material_planning_base_on_produksi_detail b ON b.so_number = a.so_number AND b.app_date > a.created_date
				LEFT JOIN dt_trans_po c ON c.idpr = b.id AND c.tipe IS NULL
				LEFT JOIN tr_purchase_order d ON d.no_po = c.no_po
			WHERE
				a.rejected IS NULL AND
				b.status_app = "Y" AND 
				DATEDIFF(DATE_FORMAT(NOW(), "%Y-%m-%d"), DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) > 0 AND
				(SELECT COUNT(aa.no_surat) FROM tr_purchase_order aa LEFT JOIN dt_trans_po ab ON ab.no_po = aa.no_po WHERE ab.idpr = b.id AND aa.tipe IS NULL) < 1 AND
				DATE_FORMAT(a.created_date, "%Y") = "' . date('Y') . '" AND
				(a.category = "pr material" OR a.category = "pr stok") AND
				a.close_pr IS NULL
			GROUP BY a.so_number

			UNION ALL

			SELECT
				a.no_pr as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY) as max_approval_date,
				DATEDIFF(IF(e.created_on IS NOT NULL, DATE_FORMAT(e.created_on, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) as late_day,
				IF(e.created_on IS NULL, "", DATE_FORMAT(e.created_on, "%Y-%m-%d")) as aktual
			FROM
				rutin_non_planning_header a
				LEFT JOIN ms_department b ON b.id = a.id_dept
				LEFT JOIN rutin_non_planning_detail c ON c.no_pengajuan = a.no_pengajuan
				LEFT JOIN dt_trans_po d ON d.idpr = c.id AND d.tipe = "pr depart"
				LEFT JOIN tr_purchase_order e ON e.no_po = d.no_po
			WHERE
				a.rejected IS NULL AND
				a.sts_app = "Y" AND
				DATEDIFF(DATE_FORMAT(NOW(), "%Y-%m-%d"), DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) > 0 AND
				(SELECT COUNT(aa.no_surat) FROM tr_purchase_order aa LEFT JOIN dt_trans_po ab ON ab.no_po = aa.no_po WHERE ab.idpr = c.id AND aa.tipe = "pr depart") < 1 AND
				DATE_FORMAT(a.created_date, "%Y") = "' . date('Y') . '" AND
				a.close_pr IS NULL
			GROUP BY a.no_pengajuan
			ORDER BY late_day DESC
		')->result_array();

		$get_list_all_po_release = $this->db->query('
			SELECT
				a.no_pr as no_pr,
				"PPIC" as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY) as max_approval_date,
				DATEDIFF(IF(d.created_on IS NOT NULL, DATE_FORMAT(d.created_on, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) as late_day,
				IF(d.created_on IS NULL, "", DATE_FORMAT(d.created_on, "%Y-%m-%d")) as aktual
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN material_planning_base_on_produksi_detail b ON b.so_number = a.so_number AND b.app_date > a.created_date
				LEFT JOIN dt_trans_po c ON c.idpr IN (SELECT aa.id FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number) AND c.tipe IS NULL
				LEFT JOIN tr_purchase_order d ON d.no_po = c.no_po
			WHERE
				a.rejected IS NULL AND
				b.status_app = "Y" AND 
				(SELECT COUNT(aa.no_surat) FROM tr_purchase_order aa LEFT JOIN dt_trans_po ab ON ab.no_po = aa.no_po WHERE ab.idpr IN (SELECT aaa.id FROM material_planning_base_on_produksi_detail aaa WHERE aaa.so_number = a.so_number) AND aa.tipe IS NULL) < 1 AND
				DATE_FORMAT(a.created_date, "%Y") = "' . date('Y') . '" AND
				(a.category = "pr material" OR a.category = "pr stok") AND
				a.close_pr IS NULL
			GROUP BY a.so_number

			UNION ALL

			SELECT
				a.no_pr as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY) as max_approval_date,
				DATEDIFF(IF(e.created_on IS NOT NULL, DATE_FORMAT(e.created_on, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) as late_day,
				IF(e.created_on IS NULL, "", DATE_FORMAT(e.created_on, "%Y-%m-%d")) as aktual
			FROM
				rutin_non_planning_header a
				LEFT JOIN ms_department b ON b.id = a.id_dept
				LEFT JOIN rutin_non_planning_detail c ON c.no_pengajuan = a.no_pengajuan
				LEFT JOIN dt_trans_po d ON d.idpr IN (SELECT aa.id FROM rutin_non_planning_detail aa WHERE aa.no_pengajuan = a.no_pengajuan) AND d.tipe = "pr depart"
				LEFT JOIN tr_purchase_order e ON e.no_po = d.no_po
			WHERE
				a.rejected IS NULL AND
				a.sts_app = "Y" AND
				(SELECT COUNT(aa.no_surat) FROM tr_purchase_order aa LEFT JOIN dt_trans_po ab ON ab.no_po = aa.no_po WHERE ab.idpr IN (SELECT aaa.id FROM rutin_non_planning_detail aaa WHERE aaa.no_pengajuan = a.no_pengajuan) AND aa.tipe = "pr depart") < 1 AND
				DATE_FORMAT(a.created_date, "%Y") = "' . date('Y') . '" AND
				a.close_pr IS NULL
			GROUP BY a.no_pengajuan
			ORDER BY late_day DESC
		')->result_array();

		$get_list_all_po_release_summary = $this->db->query('
			SELECT
				a.no_pr as no_pr,
				"PPIC" as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY) as max_approval_date,
				DATEDIFF(IF(d.created_on IS NOT NULL, DATE_FORMAT(d.created_on, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(b.app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) as late_day,
				IF(d.created_on IS NULL, "", DATE_FORMAT(d.created_on, "%Y-%m-%d")) as aktual
			FROM
				material_planning_base_on_produksi a
				LEFT JOIN material_planning_base_on_produksi_detail b ON b.so_number = a.so_number AND b.app_date > a.created_date
				LEFT JOIN dt_trans_po c ON c.idpr IN (SELECT aa.id FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = a.so_number) AND c.tipe IS NULL
				LEFT JOIN tr_purchase_order d ON d.no_po = c.no_po
			WHERE
				a.rejected IS NULL AND
				b.status_app = "Y" AND 
				(SELECT COUNT(aa.no_surat) FROM tr_purchase_order aa LEFT JOIN dt_trans_po ab ON ab.no_po = aa.no_po WHERE ab.idpr IN (SELECT aaa.id FROM material_planning_base_on_produksi_detail aaa WHERE aaa.so_number = a.so_number) AND aa.tipe IS NULL) < 1 AND
				DATE_FORMAT(a.created_date, "%Y") = "' . date('Y') . '" AND
				(a.category = "pr material" OR a.category = "pr stok") AND
				a.close_pr IS NULL
			GROUP BY a.so_number

			UNION ALL

			SELECT
				a.no_pr as no_pr,
				b.nama as department,
				DATE_FORMAT(a.created_date, "%Y-%m-%d") as pr_date,
				DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY) as max_approval_date,
				DATEDIFF(IF(e.created_on IS NOT NULL, DATE_FORMAT(e.created_on, "%Y-%m-%d"), DATE_FORMAT(NOW(), "%Y-%m-%d")), DATE_ADD(DATE_FORMAT(a.sts_app_date, "%Y-%m-%d"), INTERVAL 4 DAY)) as late_day,
				IF(e.created_on IS NULL, "", DATE_FORMAT(e.created_on, "%Y-%m-%d")) as aktual
			FROM
				rutin_non_planning_header a
				LEFT JOIN ms_department b ON b.id = a.id_dept
				LEFT JOIN rutin_non_planning_detail c ON c.no_pengajuan = a.no_pengajuan
				LEFT JOIN dt_trans_po d ON d.idpr IN (SELECT aa.id FROM rutin_non_planning_detail aa WHERE aa.no_pengajuan = a.no_pengajuan) AND d.tipe = "pr depart"
				LEFT JOIN tr_purchase_order e ON e.no_po = d.no_po
			WHERE
				a.rejected IS NULL AND
				a.sts_app = "Y" AND
				(SELECT COUNT(aa.no_surat) FROM tr_purchase_order aa LEFT JOIN dt_trans_po ab ON ab.no_po = aa.no_po WHERE ab.idpr IN (SELECT aaa.id FROM rutin_non_planning_detail aaa WHERE aaa.no_pengajuan = a.no_pengajuan) AND aa.tipe = "pr depart") < 1 AND
				DATE_FORMAT(a.created_date, "%Y") = "' . date('Y') . '" AND
				a.close_pr IS NULL
			GROUP BY a.no_pengajuan
			ORDER BY late_day DESC
		')->result_array();

		$summary_po_release = array();
		for ($i = 1; $i <= 12; $i++) {
			$release_percentage = 0;
			$late_release = 0;
			$ontime_release = 0;
			$all_release = 0;
			foreach ($get_list_all_po_release_summary as $all_po_release) {

				if (str_replace(' ', '', $all_po_release['max_approval_date']) !== '') {
					if (date('m', strtotime($all_po_release['max_approval_date'])) == sprintf('%02d', $i)) {
						if ($i == '1') {
							print($all_po_release['no_pr'] . ' - ' . $all_po_release['max_approval_date'] . ' - ' . date('m', strtotime($all_po_release['max_approval_date'])) . ' - ' . $i . "<br>");
						}
						if ($all_po_release['aktual'] <= $all_po_release['max_approval_date']) {
							$ontime_release += 1;
						} else {
							$late_release += 1;
						}
						$all_release += 1;
					}
				}
			}

			if ($all_release > 0) {
				$release_percentage = ($late_release / $all_release) * 100;
			}

			$summary_po_release[$i] = $release_percentage;
		}

		$this->template->set('list_all_approved_pr', $get_all_approved_pr);
		$this->template->set('list_late_po_release', $get_list_late_po_release);
		$this->template->set('list_all_po_release', $get_list_all_po_release);
		$this->template->set('summary_po_release', $summary_po_release);
		$this->template->render('list_po_release');
	}
	## JSON DATA DASHBOARD
	public function get_add()
	{
		$id 	= $this->uri->segment(3);
		$sql_beet = $this->db->query("SELECT a.no_so, a.delivery_date, b.name_customer FROM sales_order_header a LEFT JOIN master_customer b ON a.code_cust=b.id_customer WHERE a.delivery_date >= DATE(NOW())")->result_array();

		$d_Header = "";
		$d_Header .= "<tr class='header_" . $id . "'>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select id='noso_" . $id . "' class='chosen_select form-control input-sm inline-blockd chosen_select salesorder' data-type='exis'>";
		$d_Header .= "<option value='0'>Select Sales order</option>";
		foreach ($sql_beet as $val => $valx) {
			$d_Header .= "<option value='" . $valx['no_so'] . "'>" . $valx['no_so'] . "  [" . strtoupper(date('d-M-Y', strtotime($valx['delivery_date']))) . "] / " . strtoupper($valx['name_customer']) . "</option>";
		}
		$d_Header .= "</select>";
		$d_Header .= "</td>";

		$d_Header .= "<td align='left'>EXISTING <button type='button' class='btn btn-sm btn-danger delPart' title='Delete' style='float:right;'><i class='fa fa-close'></i></button></td>";
		$d_Header .= "<td align='left'><div style='text-align:right;' class='order' id='exis_order_" . $id . "'></div></td>";
		$d_Header .= "<td align='left'><div style='text-align:right;' class='propose' id='exis_propose_" . $id . "'></div></td>";
		$d_Header .= "<td align='left'><div style='text-align:right; 'class='fg' id='exis_fg_" . $id . "'></div></td>";
		$d_Header .= "<td align='left'><div style='text-align:right; 'class='bal' id='exis_balance_" . $id . "'></div></td>";
		$d_Header .= "<td align='left'><div style='text-align:right;' id='exis_progress_" . $id . "'></div></td>";
		$d_Header .= "</tr>";

		//add part
		$d_Header .= "<tr id='exis_" . $id . "'>";
		$d_Header .= "<td  colspan='7' align='left'><button type='button' class='btn btn-sm btn-primary addPart' title='ADD EXISTING' style='min-width:150px;'><i class='fa fa-plus'></i>&nbsp;&nbsp;ADD EXISTING</button></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_add2()
	{
		$id 	= $this->uri->segment(3);
		$sql_beet = $this->db->query("SELECT a.no_so, a.delivery_date, b.name_customer FROM sales_order_header a LEFT JOIN master_customer b ON a.code_cust=b.id_customer WHERE a.delivery_date >= DATE(NOW())")->result_array();

		$d_Header = "";
		$d_Header .= "<tr class='header2_" . $id . "'>";
		$d_Header .= "<td align='left'>";
		$d_Header .= "<select id='noso2_" . $id . "' class='chosen_select form-control input-sm inline-blockd chosen_select salesorder' data-type='doha'>";
		$d_Header .= "<option value='0'>Select Sales order</option>";
		foreach ($sql_beet as $val => $valx) {
			$d_Header .= "<option value='" . $valx['no_so'] . "'>" . $valx['no_so'] . "  [" . strtoupper(date('d-M-Y', strtotime($valx['delivery_date']))) . "] / " . strtoupper($valx['name_customer']) . "</option>";
		}
		$d_Header .= "</select>";
		$d_Header .= "</td>";

		$d_Header .= "<td align='left'>DOHA <button type='button' class='btn btn-sm btn-danger delPart' title='Delete' style='float:right;'><i class='fa fa-close'></i></button></td>";
		$d_Header .= "<td align='left'><div style='text-align:right;' class='order' id='doha_order_" . $id . "'></div></td>";
		$d_Header .= "<td align='left'><div style='text-align:right;' class='propose' id='doha_propose_" . $id . "'></div></td>";
		$d_Header .= "<td align='left'><div style='text-align:right;' class='fg' id='doha_fg_" . $id . "'></div></td>";
		$d_Header .= "<td align='left'><div style='text-align:right;' class='bal' id='doha_balance_" . $id . "'></div></td>";
		$d_Header .= "<td align='left'><div style='text-align:right;' id='doha_progress_" . $id . "'></div></td>";
		$d_Header .= "</tr>";

		//add part
		$d_Header .= "<tr id='doha_" . $id . "'>";
		$d_Header .= "<td  colspan='7' align='left'><button type='button' class='btn btn-sm btn-success addPart2' title='ADD DOHA' style='min-width:150px;'><i class='fa fa-plus'></i>&nbsp;&nbsp;ADD DOHA</button></td>";
		$d_Header .= "</tr>";

		echo json_encode(array(
			'header'			=> $d_Header,
		));
	}

	public function get_result()
	{
		$id 	= $this->uri->segment(3);
		$no_so 	= $this->uri->segment(4);

		$nomor 	= $this->input->post('nomor');

		if ($id == 'exis') {
			$over 	= $this->input->post('over1');
			$query2	 = $this->db->query("SELECT SUM(a.qty_stock) AS stock FROM warehouse_product a LEFT JOIN ms_inventory_category2 b ON a.id_product=b.id_category2 WHERE a.category = 'product' AND b.id_category1 <> 'I2000002' ")->result();
			$qty2_ = (!empty($query2[0]->qty_stock)) ? $query2[0]->qty_stock : 0;
			$sql_order1 		= "SELECT SUM(a.qty_propose - a.qty_delivery) AS qty_order FROM sales_order_detail a LEFT JOIN ms_inventory_category2 b ON a.product=b.id_category2 WHERE a.no_so = '" . $no_so . "' AND b.id_category1 <> 'I2000002'";
			$rest_order1 		= $this->db->query($sql_order1)->result();
			$sql_propose1 	= "SELECT SUM(a.qty_order - a.qty_delivery) AS qty_propose FROM sales_order_detail a LEFT JOIN ms_inventory_category2 b ON a.product=b.id_category2 WHERE a.no_so = '" . $no_so . "' AND b.id_category1 <> 'I2000002'";
			$rest_propose1 	= $this->db->query($sql_propose1)->result();
			$sql_fg1 				= "SELECT
													SUM( IF(a.qty_stock > c.qty_order,c.qty_order,a.qty_stock ) ) AS qty_stock,
													SUM(a.qty_stock) AS qty_over
												FROM
													warehouse_product a
													LEFT JOIN ms_inventory_category2 b ON a.id_product = b.id_category2
													LEFT JOIN sales_order_detail c ON a.id_product = c.product
												WHERE
													a.category = 'product'
													AND b.id_category1 <> 'I2000002'
													AND c.qty_order > 0
													AND c.no_so = '" . $no_so . "'";
			// echo $sql_fg1;
			$rest_fg1 		= $this->db->query($sql_fg1)->result();
			$qty_order1 	= ($rest_order1[0]->qty_order > 0) ? $rest_order1[0]->qty_order : 0;
			$qty_propose1 = ($rest_propose1[0]->qty_propose > 0) ? $rest_propose1[0]->qty_propose : 0;
			$qtyfg1 			= ($rest_fg1[0]->qty_stock > 0) ? $rest_fg1[0]->qty_stock : 0;
			$qtybal1 			= $qty_propose1 - $qtyfg1;

			$over1 = $rest_fg1[0]->qty_over - $qty_propose1;
			if ($rest_fg1[0]->qty_over - $qty_propose1 < 0) {
				$over1 = 0;
			}
		}

		if ($id == 'doha') {
			$over 	= $this->input->post('over2');
			$query2	 = $this->db->query("SELECT SUM(a.qty_stock) AS stock FROM warehouse_product a LEFT JOIN ms_inventory_category2 b ON a.id_product=b.id_category2 WHERE a.category = 'product' AND b.id_category1 = 'I2000002' ")->result();
			$qty2_ = (!empty($query2[0]->qty_stock)) ? $query2[0]->qty_stock : 0;
			$sql_order1 		= "SELECT SUM(a.qty_propose - a.qty_delivery) AS qty_order FROM sales_order_detail a LEFT JOIN ms_inventory_category2 b ON a.product=b.id_category2 WHERE a.no_so = '" . $no_so . "' AND b.id_category1 = 'I2000002'";
			$rest_order1 		= $this->db->query($sql_order1)->result();
			$sql_propose1 	= "SELECT SUM(a.qty_order - a.qty_delivery) AS qty_propose FROM sales_order_detail a LEFT JOIN ms_inventory_category2 b ON a.product=b.id_category2 WHERE a.no_so = '" . $no_so . "' AND b.id_category1 = 'I2000002'";
			$rest_propose1 	= $this->db->query($sql_propose1)->result();
			$sql_fg1 				= "SELECT
													SUM( IF(a.qty_stock > c.qty_order,c.qty_order,a.qty_stock ) ) AS qty_stock,
													SUM(a.qty_stock) AS qty_over
												FROM
													warehouse_product a
													LEFT JOIN ms_inventory_category2 b ON a.id_product = b.id_category2
													LEFT JOIN sales_order_detail c ON a.id_product = c.product
												WHERE
													a.category = 'product'
													AND b.id_category1 = 'I2000002'
													AND c.qty_order > 0
													AND c.no_so = '" . $no_so . "'";
			$rest_fg1 		= $this->db->query($sql_fg1)->result();
			$qty_order1 	= ($rest_order1[0]->qty_order > 0) ? $rest_order1[0]->qty_order : 0;
			$qty_propose1 = ($rest_propose1[0]->qty_propose > 0) ? $rest_propose1[0]->qty_propose : 0;
			$qtyfg1 			= ($rest_fg1[0]->qty_stock > 0) ? $rest_fg1[0]->qty_stock : 0;
			$qtybal1 			= $qty_propose1 - $qtyfg1;

			$over1 = $rest_fg1[0]->qty_over - $qty_propose1;
			if ($rest_fg1[0]->qty_over - $qty_propose1 < 0) {
				$over1 = 0;
			}
		}


		if ($nomor <> '1') {
			$qtyfg1 = $over;
			if ($over < 0) {
				$qtyfg1 = 0;
			}

			$qtybal1 			= $qty_propose1 - $qtyfg1;
		}

		$progres1 		= 0;
		if ($qtyfg1 > 0 and $qty_propose1 > 0) {
			$progres1 	= ($qtyfg1 / $qty_propose1) * 100;
		}

		// echo $qtyfg1;
		echo json_encode(array(
			'order' 		=> number_format($qty_order1),
			'propose' 	=> number_format($qty_propose1),
			'fg' 				=> number_format($qtyfg1),
			'bal' 			=> number_format($qtybal1),
			'progres' 	=> number_format($progres1, 2) . ' %',
			'no_so' 		=> $no_so,
			'id'				=> $id,
			'over' 			=> $over1,
			'nomor'			=> $nomor
		));
	}

	public function monitoring_po()
	{
		$get_po = $this->db
			->select('a.*, b.nama as nm_vendor')
			->from('tr_purchase_order a')
			->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left')
			->where('a.close_po', null)
			->order_by('a.no_po', 'desc')
			->get()
			->result();

		$get_supplier = $this->db->get_where('new_supplier', ['deleted_by' => null])->result();
		$get_curr = $this->db->get_where('mata_uang', ['deleted' => null])->result();

		$data = [
			'list_po' => $get_po,
			'list_supplier' => $get_supplier,
			'list_curr' => $get_curr
		];
		$this->template->set('results', $data);
		$this->template->title('Monitoring Purchase');
		$this->template->render('monitoring_po');
	}

	public function detail_incoming()
	{
		$no_po = $this->input->post('no_po');
		$kode_trans = $this->input->post('kode_trans');
		$tipe_incoming = $this->input->post('tipe_incoming');

		if ($tipe_incoming == 'material') {

			$get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();
			$get_incoming = $this->db->get_where('tr_incoming_check', ['kode_trans' => $kode_trans])->row();

			$get_incoming_detail = $this->db
				->select('
				a.qty_order as qty_order, 
				a.nm_material as nm_material, 
				a.keterangan as keterangan,
				b.code as kode_product, 
				b.konversi as konversi, 
				c.qty_oke as qty_diterima, 
				d.code as unit, 
				e.code as unit_packing
			')
				->from('tr_incoming_check_detail a')
				->join('new_inventory_4 b', 'b.code_lv4 = a.id_material', 'left')
				->join('tr_checked_incoming_detail c', 'c.id_detail = a.id', 'left')
				->join('ms_satuan d', 'd.id = b.id_unit', 'left')
				->join('ms_satuan e', 'e.id = b.id_unit_packing', 'left')
				->where('a.kode_trans', $kode_trans)
				->where('a.no_ipp', $no_po)
				->get()
				->result();
			// print_r($this->db->last_query());
			// exit;

			$data = [
				'no_surat' => $get_po->no_surat,
				'tanggal_penerimaan' => $get_incoming->tanggal,
				'list_incoming_detail' => $get_incoming_detail
			];

			$this->template->set('results', $data);
			$this->template->render('view_detail_incoming_material');
		} else {
			$get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $no_po])->row();
			// $get_incoming = $this->db->get_where('warehouse_adjustment', ['kode_trans' => $kode_trans])->row();
			$get_incoming = $this->db
				->select('a.*, b.nm_gudang')
				->from('warehouse_adjustment a')
				->join('warehouse b', 'b.id = a.id_gudang_ke', 'left')
				->where('a.kode_trans', $kode_trans)
				->get()
				->row();

			$get_incoming_detail = $this->db
				->select('
					a.qty_order as qty_order, 
					a.nm_material as nm_material, 
					a.keterangan as keterangan,
					b.id_stock as kode_product, 
					b.konversi as konversi, 
					a.qty_oke as qty_diterima, 
					d.code as unit, 
					e.code as unit_packing
				')
				->from('warehouse_adjustment_detail a')
				->join('accessories b', 'b.id = a.id_material', 'left')
				->join('ms_satuan d', 'd.id = b.id_unit', 'left')
				->join('ms_satuan e', 'e.id = b.id_unit_gudang', 'left')
				->where('a.kode_trans', $kode_trans)
				->get()
				->result();

			// print_r($this->db->last_query());
			// exit;

			$data = [
				'no_surat' => $get_po->no_surat,
				'tanggal_penerimaan' => $get_incoming->tanggal,
				'data_po' => $get_po,
				'data_incoming' => $get_incoming,
				'list_incoming_detail' => $get_incoming_detail
			];

			$this->template->set('results', $data);
			$this->template->render('view_detail_incoming_stok');
		}
	}

	public function search_monitoring_po()
	{
		$post = $this->input->post();

		$hasil = '
			<table class="table table-bordered" id="dataTable">
				<thead class="bg-primary">
					<tr>
						<th class="text-center font_11">No.</th>
						<th class="text-center font_11">Tgl PO</th>
						<th class="text-center font_11">No. PO</th>
						<th class="text-center font_11">Vendor</th>
						<th class="text-center font_11">Currency</th>
						<th class="text-center font_11">Item</th>
						<th class="text-center font_11">Qty PO</th>
						<th class="text-center font_11">Price Unit</th>
						<th class="text-center font_11">Value PO</th>
						<th class="text-center font_11">Status Incoming</th>
						<th class="text-center font_11">List Incoming</th>
						<th class="text-center font_11">Receive Invoice</th>
					</tr>
				</thead>
				<tbody>
		';

		$get_po = $this->db->select('a.*, b.nama as nm_vendor');
		$get_po = $this->db->from('tr_purchase_order a');
		$get_po = $this->db->join('new_supplier b', 'b.kode_supplier = a.id_suplier', 'left');
		$get_po = $this->db->where('a.close_po', null);
		if ($post['no_po'] !== '') {
			$get_po = $this->db->where('a.no_po', $post['no_po']);
		}
		if ($post['supplier'] !== '') {
			$get_po = $this->db->where('a.id_suplier', $post['supplier']);
		}
		if ($post['curr'] !== '') {
			$get_po = $this->db->where('a.matauang', $post['curr']);
		}
		if ($post['tgl_before'] !== '') {
			$get_po = $this->db->where('a.tanggal >=', $post['tgl_before']);
		}
		if ($post['tgl_after'] !== '') {
			$get_po = $this->db->where('a.tanggal <=', $post['tgl_after']);
		}
		$get_po = $this->db->order_by('a.tanggal', 'desc');
		$get_po = $this->db->get()->result();

		$no_po = 1;
		foreach ($get_po as $item_po) {

			$jum_detail = $this->db->get_where('dt_trans_po', ['no_po' => $item_po->no_po])->num_rows();

			// $no_rowspan = max($jum_detail, $jum_incoming);
			$no_rowspan = $jum_detail;

			$hasil .= '<tr>';
			$hasil .= '<td class="text-center font_11" rowspan="' . $no_rowspan . '">' . $no_po . '</td>';
			$hasil .= '<td class="text-center font_11" rowspan="' . $no_rowspan . '">' . date('d F Y', strtotime($item_po->tanggal)) . '</td>';
			$hasil .= '<td class="text-center font_11" rowspan="' . $no_rowspan . '">' . $item_po->no_surat . '</td>';
			$hasil .= '<td class="text-center font_11" rowspan="' . $no_rowspan . '">' . $item_po->nm_vendor . '</td>';
			$hasil .= '<td class="text-center font_11" rowspan="' . $no_rowspan . '">' . $item_po->matauang . '</td>';
			$get_list_detail = $this->db->get_where('dt_trans_po', ['no_po' => $item_po->no_po])->result();
			$no_po_detail = 1;
			$ttl_qty_po = 0;
			foreach ($get_list_detail as $item_po_detail) {
				if ($no_po_detail == 1) {
					$hasil .= '<td class="text-left font_11">' . $item_po_detail->namamaterial . '</td>';
					$hasil .= '<td class="text-right font_11">' . number_format($item_po_detail->qty) . '</td>';
					$hasil .= '<td class="text-right font_11">' . number_format($item_po_detail->hargasatuan, 2) . '</td>';
				}
				$ttl_qty_po += $item_po_detail->qty;
				$no_po_detail++;
			}

			$total_incoming = 0;
			$get_total_incoming = $this->db
				->select('SUM(a.qty_ng + a.qty_oke) as ttl_qty_incoming')
				->from('tr_checked_incoming_detail a')
				->where('a.no_ipp', $item_po->no_po)
				->get()
				->row();

			$get_total_incoming2 = $this->db
				->select('SUM(a.qty_rusak + a.qty_oke) as ttl_qty_incoming')
				->from('warehouse_adjustment_detail a')
				->join('warehouse_adjustment b', 'b.kode_trans = a.kode_trans')
				->where('b.no_ipp', $item_po->no_po)
				->get()
				->row();
			$total_incoming = round($get_total_incoming->ttl_qty_incoming + $get_total_incoming2->ttl_qty_incoming);

			$status_incoming = '<div class="badge bg-red">Not Yet</div>';
			if ($total_incoming > 0 && round($total_incoming) < round($ttl_qty_po)) {
				$status_incoming = '<div class="badge bg-yellow">Partial</div>';
			}
			if ($total_incoming > 0 && round($total_incoming) >= round($ttl_qty_po)) {
				$status_incoming = '<div class="badge bg-green">Complete</div>';
			}

			$list_invoice = '';
			$get_no_receive_invoice = $this->db
				->select('a.id, a.id_top')
				->from('tr_invoice_po a')
				->like('a.no_po', $item_po->no_surat)
				->get()
				->result();
			foreach ($get_no_receive_invoice as $item_receive_invoice) {
				$get_top = $this->db->get_where('tr_top_po', ['id' => $item_receive_invoice->id_top])->row();
				// $tipe_top = '(Incoming)';
				$tipe_top = '';
				if (!empty($get_top)) {
					if ($get_top->group_top == '76') {
						$tipe_top = '(DP)';
					}
					if ($get_top->group_top == '77') {
						$tipe_top = '(Progress)';
					}
					if ($get_top->group_top == '78') {
						$tipe_top = '(Retensi)';
					}
				}

				// $list_invoice .= 'No. ' . $item_receive_invoice->id . ' ' . $tipe_top . '<br>';
				if ($tipe_top !== '') {
					$list_invoice .= '<a href="javascript:void(0);" class="view_invoice" data-id="' . $item_receive_invoice->id . '" data-tipe="' . $tipe_top . '">No. ' . $item_receive_invoice->id . ' ' . $tipe_top . '</a> <br><br>';
				}
			}

			$get_kode_trans_inc_material = $this->db->select('a.kode_trans')
				->from('tr_incoming_check a')
				->join('tr_purchase_order b', 'b.no_po = a.no_ipp')
				->where('b.no_surat', $item_po->no_surat)
				->get()
				->result();

			foreach ($get_kode_trans_inc_material as $item) {
				$get_id_inv_inc = $this->db->select('a.id')
					->from('tr_invoice_po a')
					->like('a.no_po', $item->kode_trans, 'both')
					->get()
					->result();
				foreach($get_id_inv_inc as $item_id) {
					$list_invoice .= '<a href="javascript:void(0);" class="view_invoice" data-id="' . $item_id->id . '" data-tipe="(Incoming)">No. ' . $item_id->id . ' (Incoming)</a> <br><br>';
				}
			}

			$get_kode_trans_inc_stok = $this->db->select('a.kode_trans')
			->from('warehouse_adjustment a')
			->join('tr_purchase_order b', 'b.no_po = a.no_ipp')
			->where('b.no_surat', $item_po->no_surat)
			->where('a.category', 'incoming stok')
			->get()
			->result();

			foreach ($get_kode_trans_inc_stok as $item) {
				$get_id_inv_inc = $this->db->select('a.id')
					->from('tr_invoice_po a')
					->like('a.no_po', $item->kode_trans, 'both')
					->get()
					->result();
				foreach($get_id_inv_inc as $item_id) {
					$list_invoice .= '<a href="javascript:void(0);" class="view_invoice" data-id="' . $item_id->id . '" data-tipe="(Incoming)">No. ' . $item_id->id . ' (Incoming)</a> <br><br>';
				}
			}

			$get_kode_trans_inc_non_rutin = $this->db->select('a.kode_trans')
			->from('warehouse_adjustment a')
			->join('tr_purchase_order b', 'b.no_surat = a.no_ipp')
			->where('b.no_surat', $item_po->no_surat)
			->where('a.category', 'incoming non rutin')
			->get()
			->result();

			foreach ($get_kode_trans_inc_non_rutin as $item) {
				$get_id_inv_inc = $this->db->select('a.id')
					->from('tr_invoice_po a')
					->like('a.no_po', $item->kode_trans, 'both')
					->get()
					->result();
				foreach($get_id_inv_inc as $item_id) {
					$list_invoice .= '<a href="javascript:void(0);" class="view_invoice" data-id="' . $item_id->id . '" data-tipe="(Incoming)">No. ' . $item_id->id . ' (Incoming)</a> <br><br>';
				}
			}


			$hasil .= '<td class="text-right font_11" rowspan="' . $no_rowspan . '">' . number_format($item_po->subtotal, 2) . '</td>';
			$hasil .= '<td class="text-center font_11" rowspan="' . $no_rowspan . '">' . $status_incoming . '</td>';

			$list_incoming = '';
			$get_incoming = $this->db
				->select('a.id, a.kode_trans, a.jumlah_mat')
				->from('tr_incoming_check a')
				->like('a.no_ipp', $item_po->no_po)
				->get()
				->result();
			foreach ($get_incoming as $item_incoming) {
				$list_incoming .= '<a href="javascript:void(0);" class="detail_incoming" data-tipe_incoming="material" data-kode_trans="' . $item_incoming->kode_trans . '" data-no_po="' . $item_po->no_po . '">' . $item_incoming->kode_trans . ' - ' . number_format($item_incoming->jumlah_mat) . '</a> <br>';
			}

			$get_incoming2 = $this->db
				->select('a.id, a.kode_trans, a.jumlah_mat')
				->from('warehouse_adjustment a')
				->like('a.no_ipp', $item_po->no_po, 'both')
				->get()
				->result();
			foreach ($get_incoming2 as $item_incoming2) {
				$list_incoming .= '<a href="javascript:void(0);" class="detail_incoming" data-tipe_incoming="stok" data-kode_trans="' . $item_incoming2->kode_trans . '" data-no_po="' . $item_po->no_po . '">' . $item_incoming2->kode_trans . ' - ' . number_format($item_incoming2->jumlah_mat) . '</a> <br>';
			}

			$hasil .= '<td class="text-left font_11" rowspan="' . $no_rowspan . '">' . $list_incoming . ' </td>';

			$hasil .= '<td class="text-left font_11" rowspan="' . $no_rowspan . '">' . $list_invoice . '</td>';
			$hasil .= '</tr>';

			$get_list_detail = $this->db->get_where('dt_trans_po', ['no_po' => $item_po->no_po])->result();
			$no_po_detail = 1;
			foreach ($get_list_detail as $item_po_detail) {
				if ($no_po_detail > 1) {
					$hasil .= '<tr>';
					$hasil .= '<td class="text-left font_11">' . $item_po_detail->namamaterial . '</td>';
					$hasil .= '<td class="text-right font_11">' . number_format($item_po_detail->qty) . '</td>';
					$hasil .= '<td class="text-right font_11">' . number_format($item_po_detail->hargasatuan, 2) . '</td>';
					$hasil .= '</tr>';
				}
				$no_po_detail++;
			}

			$no_po++;
		}

		$hasil .= '
			</tbody>
		</table>
		';

		echo json_encode([
			'hasil' => $hasil
		]);
	}

	public function monitoring_produksi()
	{

		// $this->db->select('b.*');
		// $this->db->from('so_internal_spk a');
		// $this->db->join('so_internal b', 'b.id = a.id_so AND a.status_id = 1', 'left');
		// $this->db->join('new_inventory_4 c', 'c.code_lv4 = b.code_lv4', 'left');
		// $this->db->where('a.sts_close <>', 'Y');
		// $this->db->group_by('b.code_lv4');
		// $get_data = $this->db->get()->result();

		$get_data = $this->db->get_where('stock_product', ['deleted_by' => null])->result();

		$data = [
			'list_data' => $get_data
		];

		$this->template->set($data);
		$this->template->render('dashboard_monitoring_produksi');
	}

	public function view_invoice_po()
	{
		$id = $this->input->post('id');
		$tipe = $this->input->post('tipe');

		if ($tipe !== '(Incoming)') {
			$get_invoice = $this->db->get_where('tr_invoice_po', ['id' => $id])->row_array();
			$id_top = $get_invoice['id_top'];

			$get_po = $this->db->get_where('tr_purchase_order', ['no_surat' => $get_invoice['no_po']])->row();
			$get_top = $this->db->get_where('tr_top_po', ['id' => $id_top])->row();

			$this->template->set('data_invoice', $get_invoice);
			$this->template->set('nilai_ppn', $get_invoice['nilai_ppn']);
			$this->template->set('nilai_disc', $get_invoice['nilai_disc']);
			$this->template->set('nilai_top', $get_top->nilai);
			if ($tipe == '(DP)') {
				$this->template->render('view');
			}
			if ($tipe == '(Progress)') {
				$this->template->render('view_pro');
			}
			if ($tipe == '(Retensi)') {
				$this->template->render('view_ret');
			}
		} else {
			$get_invoice = $this->db->get_where('tr_invoice_po', ['id' => $id])->row_array();
			$id_po = str_replace(', ', ',', $get_invoice['no_po']);
			$no_incoming = explode(',', $id_po);

			$this->template->set('data_invoice', $get_invoice);
			$this->template->set('no_incoming', $no_incoming);
			$this->template->render('view_inc');
		}
	}
}

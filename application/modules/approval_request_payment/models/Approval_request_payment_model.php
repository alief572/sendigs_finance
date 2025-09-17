<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Harboens
 * @copyright Copyright (c) 2022
 *
 * This is Model for Request Payment
 */

class Approval_request_payment_model extends BF_Model
{

    /**
     * @var string  User Table Name
     */
    protected $table_name = 'request_payment';
    protected $key        = 'id';

    /**
     * @var string Field name to use for the created time column in the DB table
     * if $set_created is enabled.
     */
    protected $created_field = 'created_on';

    /**
     * @var string Field name to use for the modified time column in the DB
     * table if $set_modified is enabled.
     */
    protected $modified_field = 'modified_on';

    /**
     * @var bool Set the created time automatically on a new record (if true)
     */
    protected $set_created = true;

    /**
     * @var bool Set the modified time automatically on editing a record (if true)
     */
    protected $set_modified = true;
    /**
     * @var string The type of date/time field used for $created_field and $modified_field.
     * Valid values are 'int', 'datetime', 'date'.
     */
    /**
     * @var bool Enable/Disable soft deletes.
     * If false, the delete() method will perform a delete of that row.
     * If true, the value in $deleted_field will be set to 1.
     */
    protected $soft_deletes = true;

    protected $date_format = 'datetime';

    /**
     * @var bool If true, will log user id in $created_by_field, $modified_by_field,
     * and $deleted_by_field.
     */
    protected $log_user = true;

    /**
     * Function construct used to load some library, do some actions, etc.
     */
    public function __construct()
    {
        parent::__construct();
    }

    // list data request
    public function GetListDataRequest($tab = null, $from_date = null, $to_date = null)
    {
        $where_date1 = '';
        $where_date2 = '';
        $where_date3 = '';
        if ($from_date !== null && $to_date !== null) {
            $where_date1 = " AND a.tgl_doc BETWEEN '" . $from_date . "' AND '" . $to_date . "'";
            $where_date2 = " AND tgl_doc BETWEEN '" . $from_date . "' AND '" . $to_date . "'";
            $where_date3 = " AND a.tanggal_doc BETWEEN '" . $from_date . "' AND '" . $to_date . "'";
        }

        if ($tab !== null) {
            if ($tab == 'kasbon') {
                $this->db->select('a.id, a.tgl, a.deskripsi, "kasbon" as tipe, a.grand_total, a.dokument_link, a.bank, a.bank_number, a.bank_account, a.tipe as tipee, a.sts_reject, a.sts_reject_manage, a.reject_reason, b.nm_lengkap as nama');
                $this->db->from(DBCNL.'.kons_tr_kasbon_project_header a');
                $this->db->join(DBCNL.'.users b', 'b.id_user = a.created_by', 'left');
                $this->db->where('a.sts_req_payment', '');
                $this->db->order_by('a.id', 'desc');
                $data = $this->db->get()->result();
            }
            if ($tab == 'expense') {
                // $data = $this->db->query("SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.informasi as keperluan, 'expense' as tipe,a.jumlah,null as tanggal,a.no_doc as id, bank_id, accnumber, accname, sts_reject, sts_reject_manage, reject_reason FROM ".DBCNL.".tr_expense a left join " . DBACC . ".coa_master as b on a.coa=b.no_perkiraan WHERE a.status=1 AND a.jumlah > 0 ".$where_date1." GROUP BY a.no_doc")->result();

                $this->db->select('a.id, DATE_FORMAT(a.created_date, "%Y-%m-%d") as tgl, b.deskripsi, "expense" as tipe, a.selisih as grand_total, a.document_link as dokument_link, a.bank, a.bank_number, a.bank_account, a.tipe as tipee, a.sts_reject, a.sts_reject_manage, c.nm_lengkap as nama');
                $this->db->from(DBCNL.'.kons_tr_expense_report_project_header a');
                $this->db->join(DBCNL.'.kons_tr_kasbon_project_header b', 'b.id = a.id_header', 'left');
                $this->db->join(DBCNL.'.users c', 'c.id_user = a.created_by', 'left');
                $this->db->where('a.selisih >', 0);
                $this->db->where('a.sts', 1);
                $this->db->where('a.sts_req_payment', '');
                $data = $this->db->get()->result();
            }
        } else {
            $data    = $this->db->query("SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,'Transportasi' as keperluan, 'transportasi' as tipe,(SELECT IF(SUM(aa.jumlah_kasbon) IS NULL, 0, SUM(aa.jumlah_kasbon)) FROM ".DBCNL.".tr_transport aa WHERE aa.no_req = a.no_doc AND aa.req_payment = 0) as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname, a.sts_reject, a.sts_reject_manage, a.reject_reason FROM ".DBCNL.".tr_transport_req a WHERE a.status = 1 " . $where_date1 . "
            GROUP BY no_doc
            union all
            SELECT id as ids,no_doc,nama,tgl_doc,keperluan, 'kasbon' as tipe,jumlah_kasbon as jumlah,null as tanggal,no_doc as id, bank_id, accnumber, accname, sts_reject, sts_reject_manage, reject_reason FROM ".DBCNL.".tr_kasbon WHERE status=1 AND (metode_pembayaran = 1 OR metode_pembayaran IS NULL) " . $where_date1 . "
            GROUP BY no_doc
            union all
            SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.informasi as keperluan, 'expense' as tipe,a.jumlah,null as tanggal,a.no_doc as id, bank_id, accnumber, accname, sts_reject, sts_reject_manage, reject_reason FROM ".DBCNL.".tr_expense a left join " . DBACC . ".coa_master as b on a.coa=b.no_perkiraan WHERE a.status=1 AND a.jumlah > 0  " . $where_date1 . "
            GROUP BY a.no_doc
            union all
            SELECT b.id as ids,a.no_doc,c.nm_lengkap nama,a.tanggal_doc as tgl_doc,b.nama as keperluan, 'periodik' as tipe,b.nilai jumlah,null as tanggal,a.no_doc as id, b.bank_id, b.accnumber, b.accname, b.sts_reject, b.sts_reject_manage, b.reject_reason FROM ".DBCNL.".tr_pengajuan_rutin a join tr_pengajuan_rutin_detail b on a.no_doc=b.no_doc left join users c on a.created_by = c.id_user WHERE a.status='1' and (b.id_payment='0' OR b.id_payment IS NULL) " . $where_date3 . "
            ")->result();
        }

        return $data;
    }

    public function GetListDataRequestNew()
    {
        $data    = $this->db->query("SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,'Transportasi' as keperluan, 'transportasi' as tipe,(SELECT IF(SUM(aa.jumlah_kasbon) IS NULL, 0, SUM(aa.jumlah_kasbon)) FROM ".DBCNL.".tr_transport aa WHERE aa.no_req = a.no_doc AND aa.req_payment = 0) as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname, a.sts_reject, a.sts_reject_manage, a.reject_reason FROM ".DBCNL.".tr_transport_req a WHERE a.status = 1
        GROUP BY no_doc
		")->result();

        return $data;
    }

    public function GetListDataPaymentList()
    {
        $data    = $this->db->query("SELECT id as ids,no_doc,nama,tgl_doc,'Transportasi' as keperluan, 'transportasi' as tipe,jumlah_expense as jumlah,null as tanggal,no_doc as id, bank_id, accnumber, accname, sts_reject, sts_reject_manage, reject_reason FROM ".DBCNL.".tr_transport_req 
        GROUP BY no_doc
		union all
		SELECT id as ids,no_doc,nama,tgl_doc,keperluan, 'kasbon' as tipe,jumlah_kasbon as jumlah,null as tanggal,no_doc as id, bank_id, accnumber, accname, sts_reject, sts_reject_manage, reject_reason FROM ".DBCNL.".tr_kasbon
        GROUP BY no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.informasi as keperluan, 'expense' as tipe,a.jumlah,null as tanggal,a.no_doc as id, bank_id, accnumber, accname, a.sts_reject, a.sts_reject_manage, a.reject_reason FROM ".DBCNL.".tr_expense a left join " . DBACC . ".coa_master as b on a.coa=b.no_perkiraan WHERE a.jumlah >= 0 
        GROUP BY a.no_doc
		union all
		SELECT b.id as ids,a.no_doc,c.nm_lengkap nama,a.tanggal_doc as tgl_doc,b.nama as keperluan, 'periodik' as tipe,b.nilai jumlah,null as tanggal,a.no_doc as id, b.bank_id, b.accnumber, b.accname, b.sts_reject, b.sts_reject_manage, b.reject_reason FROM ".DBCNL.".tr_pengajuan_rutin a join tr_pengajuan_rutin_detail b on a.no_doc=b.no_doc join users c on a.created_by=c.id_user
		")->result();


        return $data;
    }

    // list data payment
    // public function GetListDataPayment($where = '')
    // {
    //     $data    = $this->db->query("SELECT * FROM ".DBCNL.".request_payment WHERE " . $where . " order by id desc")->result();
    //     return $data;
    // }

    /* EDITED BY HIKMAT A.R [18-08-2022] */
    public function GetListDataApproval($where = '')
    {
        $data    = $this->db->query("SELECT a.* FROM ".DBCNL.".request_payment a WHERE " . $where . " order by tanggal desc, tipe ,id")->result();
        return $data;
    }

    public function GetListDataPayment($where = '')
    {
        $data    = $this->db->query("SELECT * FROM ".DBCNL.".payment_approve WHERE " . $where . " order by status asc ,id desc")->result();
        return $data;
    }

    public function GetListDataJurnal()
    {
        $data    = $this->db->query("SELECT nomor,tanggal,tipe,no_reff,stspos,sum(kredit) as total FROM ".DBCNL.".jurnal group by nomor order by nomor desc")->result();
        return $data;
    }

    function generate_id_detail($no = null)
    {
        $generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM ".DBCNL.".payment_approve_details WHERE id LIKE '%PAY1-" . date('m-y') . "%'")->row();
        $kodeBarang = $generate_id->max_id;

        if ($no !== null) {
            $urutan = (int) substr($kodeBarang, 11, 5);
            $urutan += $no;
        } else {
            $urutan = (int) substr($kodeBarang, 11, 5);
            $urutan++;
        }
        $tahun = date('m-y');
        $huruf = "PAY1-";
        $kodecollect = $huruf . $tahun . sprintf("%05s", $urutan);

        return $kodecollect;
    }
    function generate_id($kode = '')
    {
        $generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM ".DBCNL.".payment_approve WHERE id LIKE '%PAY-" . date('m-y') . "%'")->row();
        $kodeBarang = $generate_id->max_id;
        $urutan = (int) substr($kodeBarang, 10, 5);
        $urutan++;
        $tahun = date('m-y');
        $huruf = "PAY-";
        $kodecollect = $huruf . $tahun . sprintf("%06s", $urutan);

        return $kodecollect;
    }

    public function search_payment_list($tgl_from = '', $tgl_to = '', $bank = '')
    {
        $filter_tgl1 = '';
        $filter_tgl2 = '';
        $filter_tgl3 = '';
        $filter_tgl4 = '';
        $filter_tgl5 = '';

        $filter_bank1 = '';
        $filter_bank2 = '';

        if ($tgl_from !== '' && $tgl_to !== '') {
            $filter_tgl1 = " AND a.tgl_doc BETWEEN '" . $tgl_from . "' AND '" . $tgl_to . "'";
            $filter_tgl2 = " AND a.tgl_doc BETWEEN '" . $tgl_from . "' AND '" . $tgl_to . "'";
            $filter_tgl3 = " AND a.tgl_doc BETWEEN '" . $tgl_from . "' AND '" . $tgl_to . "'";
            $filter_tgl4 = " AND a.tanggal_doc BETWEEN '" . $tgl_from . "' AND '" . $tgl_to . "'";
            $filter_tgl5 = " AND a.tanggal_doc BETWEEN '" . $tgl_from . "' AND '" . $tgl_to . "'";
        } else {
            if ($tgl_from !== '' && $tgl_to == '') {
                $filter_tgl1 = " AND a.tgl_doc >= '" . $tgl_from . "'";
                $filter_tgl2 = " AND a.tgl_doc >= '" . $tgl_from . "'";
                $filter_tgl3 = " AND a.tgl_doc >= '" . $tgl_from . "'";
                $filter_tgl4 = " AND a.tanggal_doc >= '" . $tgl_from . "'";
                $filter_tgl5 = " AND a.tanggal_doc >= '" . $tgl_from . "'";
            } else if ($tgl_from == '' && $tgl_to !== '') {
                $filter_tgl1 = " AND a.tgl_doc <= '" . $tgl_to . "'";
                $filter_tgl2 = " AND a.tgl_doc <= '" . $tgl_to . "'";
                $filter_tgl3 = " AND a.tgl_doc <= '" . $tgl_to . "'";
                $filter_tgl4 = " AND a.tanggal_doc <= '" . $tgl_to . "'";
                $filter_tgl5 = " AND a.tanggal_doc <= '" . $tgl_to . "'";
            }
        }

        if ($bank !== '') {
            $filter_bank1 = ' AND b.bank_name LIKE "%' . $bank . '%"';
            $filter_bank2 = ' AND d.bank_name LIKE "%' . $bank . '%"';
        }

        $data    = $this->db->query("SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,'Transportasi' as keperluan, 'transportasi' as tipe,a.jumlah_expense as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM ".DBCNL.".tr_transport_req a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.id != '' " . $filter_tgl1 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.keperluan, 'kasbon' as tipe,a.jumlah_kasbon as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM ".DBCNL.".tr_kasbon a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.id != '' " . $filter_tgl2 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.informasi as keperluan, 'expense' as tipe,a.jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM ".DBCNL.".tr_expense a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.jumlah >= 0 " . $filter_tgl3 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.pic nama,a.tanggal_doc as tgl_doc,a.info as keperluan, 'nonpo' as tipe,a.nilai_request jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM ".DBCNL.".tr_non_po_header a LEFT JOIN request_payment b ON b.no_doc = a.no_doc  WHERE a.id != '' " . $filter_tgl4 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT b.id as ids,a.no_doc,c.nm_lengkap nama,a.tanggal_doc as tgl_doc,b.nama as keperluan, 'periodik' as tipe,b.nilai jumlah,null as tanggal,a.no_doc as id, b.bank_id, b.accnumber, b.accname FROM ".DBCNL.".tr_pengajuan_rutin a join tr_pengajuan_rutin_detail b on a.no_doc=b.no_doc join users c on a.created_by=c.id_user left join request_payment d ON d.no_doc = a.no_doc WHERE b.id != '' " . $filter_tgl5 . " " . $filter_bank2 . "

		")->result();

        $list_tgl_pengajuan_pembayaran = [];
        $get_payment_approve = $this->db->select('no_doc, created_by, pay_by, DATE_FORMAT(created_on, "%d %M %Y") as tgl_pengajuan, IF(pay_on IS NULL, "", DATE_FORMAT(pay_on, "%d %M %Y")) as tgl_pembayaran')->get('payment_approve')->result();
        foreach ($get_payment_approve as $item_payment) {
            $list_tgl_pengajuan_pembayaran[$item_payment->no_doc] = [
                'diajukan_oleh' => $item_payment->created_by,
                'dibayar_oleh' => $item_payment->pay_by,
                'tgl_pengajuan' => $item_payment->tgl_pengajuan,
                'tgl_pembayaran' => $item_payment->tgl_pembayaran
            ];
        }

        $this->template->set('data_payment_list', $data);
        $this->template->set('list_tgl_pengajuan_pembayaran', $list_tgl_pengajuan_pembayaran);
        $this->template->render('search_payment_list');
    }

    public function excel_payment_list($tgl_from = '', $tgl_to = '', $bank = '')
    {
        $filter_tgl1 = '';
        $filter_tgl2 = '';
        $filter_tgl3 = '';
        $filter_tgl4 = '';
        $filter_tgl5 = '';

        $filter_bank1 = '';
        $filter_bank2 = '';

        if ($tgl_from !== '' && $tgl_to !== '') {
            $filter_tgl1 = " AND a.tgl_doc BETWEEN '" . $tgl_from . "' AND '" . $tgl_to . "'";
            $filter_tgl2 = " AND a.tgl_doc BETWEEN '" . $tgl_from . "' AND '" . $tgl_to . "'";
            $filter_tgl3 = " AND a.tgl_doc BETWEEN '" . $tgl_from . "' AND '" . $tgl_to . "'";
            $filter_tgl4 = " AND a.tanggal_doc BETWEEN '" . $tgl_from . "' AND '" . $tgl_to . "'";
            $filter_tgl5 = " AND a.tanggal_doc BETWEEN '" . $tgl_from . "' AND '" . $tgl_to . "'";
        } else {
            if ($tgl_from !== '' && $tgl_to == '') {
                $filter_tgl1 = " AND a.tgl_doc >= '" . $tgl_from . "'";
                $filter_tgl2 = " AND a.tgl_doc >= '" . $tgl_from . "'";
                $filter_tgl3 = " AND a.tgl_doc >= '" . $tgl_from . "'";
                $filter_tgl4 = " AND a.tanggal_doc >= '" . $tgl_from . "'";
                $filter_tgl5 = " AND a.tanggal_doc >= '" . $tgl_from . "'";
            } else if ($tgl_from == '' && $tgl_to !== '') {
                $filter_tgl1 = " AND a.tgl_doc <= '" . $tgl_to . "'";
                $filter_tgl2 = " AND a.tgl_doc <= '" . $tgl_to . "'";
                $filter_tgl3 = " AND a.tgl_doc <= '" . $tgl_to . "'";
                $filter_tgl4 = " AND a.tanggal_doc <= '" . $tgl_to . "'";
                $filter_tgl5 = " AND a.tanggal_doc <= '" . $tgl_to . "'";
            }
        }

        if ($bank !== '') {
            $filter_bank1 = ' AND b.bank_name LIKE "%' . $bank . '%"';
            $filter_bank2 = ' AND d.bank_name LIKE "%' . $bank . '%"';
        }

        $data    = $this->db->query("SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,'Transportasi' as keperluan, 'transportasi' as tipe,a.jumlah_expense as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM ".DBCNL.".tr_transport_req a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.id != '' " . $filter_tgl1 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.keperluan, 'kasbon' as tipe,a.jumlah_kasbon as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM ".DBCNL.".tr_kasbon a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.id != '' " . $filter_tgl2 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.informasi as keperluan, 'expense' as tipe,a.jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM ".DBCNL.".tr_expense a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.jumlah >= 0 " . $filter_tgl3 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.pic nama,a.tanggal_doc as tgl_doc,a.info as keperluan, 'nonpo' as tipe,a.nilai_request jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM ".DBCNL.".tr_non_po_header a LEFT JOIN request_payment b ON b.no_doc = a.no_doc  WHERE a.id != '' " . $filter_tgl4 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT b.id as ids,a.no_doc,c.nm_lengkap nama,a.tanggal_doc as tgl_doc,b.nama as keperluan, 'periodik' as tipe,b.nilai jumlah,null as tanggal,a.no_doc as id, b.bank_id, b.accnumber, b.accname FROM ".DBCNL.".tr_pengajuan_rutin a join tr_pengajuan_rutin_detail b on a.no_doc=b.no_doc join users c on a.created_by=c.id_user left join request_payment d ON d.no_doc = a.no_doc WHERE b.id != '' " . $filter_tgl5 . " " . $filter_bank2 . "

		")->result();

        $list_tgl_pengajuan_pembayaran = [];
        $get_payment_approve = $this->db->select('no_doc, created_by, pay_by, DATE_FORMAT(created_on, "%d %M %Y") as tgl_pengajuan, IF(pay_on IS NULL, "", DATE_FORMAT(pay_on, "%d %M %Y")) as tgl_pembayaran')->get('payment_approve')->result();
        foreach ($get_payment_approve as $item_payment) {
            $list_tgl_pengajuan_pembayaran[$item_payment->no_doc] = [
                'diajukan_oleh' => $item_payment->created_by,
                'dibayar_oleh' => $item_payment->pay_by,
                'tgl_pengajuan' => $item_payment->tgl_pengajuan,
                'tgl_pembayaran' => $item_payment->tgl_pembayaran
            ];
        }

        $dataa = [
            'tgl_from' => $tgl_from,
            'tgl_to' => $tgl_to,
            'bank' => $bank,
            'data_payment_list' => $data,
            'list_tgl_pengajuan_pembayaran' => $list_tgl_pengajuan_pembayaran
        ];
        $this->load->view('excel_payment_list', $dataa);
    }

    // public function generate_no_invoice($kode = '')
    // {
    //     $generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM ".DBCNL.".payment_approve WHERE id LIKE '%BK-" . date('Y-m-') . "%'")->row();
    //     $kodeBarang = $generate_id->max_id;
    //     $urutan = (int) substr($kodeBarang, 12, 5);
    //     $urutan++;
    //     $tahun = date('Y-m-');
    //     $huruf = "PI-";
    //     $kodecollect = $huruf . $tahun . sprintf("%06s", $urutan);

    //     return $kodecollect;
    // }

    public function generate_id_payment($kode_bank = 'PAY')
    {
        $generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM payment_approve WHERE id LIKE '%BK-" . date('my-') . "%'")->row();
        $kodeBarang = $generate_id->max_id;
        $urutan = (int) substr($kodeBarang, 10, 4);
        $urutan++;
        $tahun = date('my-');
        $huruf = "BK-";
        $kodecollect = $huruf . $tahun . sprintf("%04s", $urutan);

        return $kodecollect;
    }

    public function get_payment_list()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');

        $tgl_from = $this->input->post('tgl_from');
        $tgl_to = $this->input->post('tgl_to');
        $bank = $this->input->post('bank');

        $where_search = '';
        $where_search2 = '';
        if (!empty($search)) {
            $where_search = 'AND (
                a.id LIKE "%' . $search['value'] . '%" 
                OR r.tipe LIKE "%'.$search['value'].'%"
                OR c.nm_lengkap LIKE "%'.$search['value'].'%"
                OR a.tgl LIKE "%'.$search['value'].'%"
                OR a.grand_total LIKE "%'.$search['value'].'%"
            )';

            $where_search2 = 'AND (
                a.id LIKE "%' . $search['value'] . '%" 
                OR r.tipe LIKE "%'.$search['value'].'%"
                OR c.nm_lengkap LIKE "%'.$search['value'].'%"
                OR a.created_date LIKE "%'.$search['value'].'%"
                OR a.selisih LIKE "%'.$search['value'].'%"
            )';
        }

        $where_bank = '';
        if ($bank !== '') {
            $where_bank = 'AND r.bank_name = "' . $bank . '"';
        }

        $where_tgl_from = '';
        $where_tgl_from2 = '';
        if ($tgl_from !== '') {
            $where_tgl_from = 'AND a.tgl >= "' . $tgl_from . '"';
            $where_tgl_from2 = 'AND a.created_date >= "' . $tgl_from . '"';
        }

        $where_tgl_to = '';
        $where_tgl_to2 = '';
        if ($tgl_to !== '') {
            $where_tgl_to = 'AND a.tgl <= "' . $tgl_to . '"';
            $where_tgl_to2 = 'AND a.created_date <= "' . $tgl_to . '"';
        }

        $query = '
            SELECT
                a.id as id,
                a.tgl as tgl,
                a.deskripsi as deskripsi,
                r.tipe as tipe,
                "kasbon" as tipee,
                a.grand_total as grand_total,
                a.bank as bank,
                a.bank_number as bank_number,
                a.bank_account as bank_account,
                a.sts_reject as sts_reject,
                a.sts_reject_manage as sts_reject_manage,
                a.reject_reason as reject_reason,
                c.nm_lengkap as nama
            FROM
".DBCNL.".                kons_tr_kasbon_project_header a
                LEFT JOIN users c ON c.id_user = a.created_by
                LEFT JOIN request_payment r ON r.no_doc = a.id
            WHERE
                1 = 1 AND a.sts_req_payment = 1
                ' . $where_tgl_from . '
                ' . $where_tgl_to . '
                ' . $where_bank . '
                ' . $where_search . '
            UNION ALL

            SELECT 
                a.id as id,
                DATE_FORMAT(a.created_date, "%Y-%m-%d") as tgl,
                b.deskripsi as deskripsi,
                r.tipe as tipe,
                "expense" as tipee,
                a.selisih as grand_total,
                a.bank as bank,
                a.bank_number as bank_number,
                a.bank_account as bank_account,
                a.sts_reject as sts_reject,
                a.sts_reject_manage as sts_reject_manage,
                a.reject_reason as reject_reason,
                c.nm_lengkap as nama
            FROM
".DBCNL.".                kons_tr_expense_report_project_header a
                LEFT JOIN kons_tr_kasbon_project_header b ON b.id = a.id_header
                LEFT JOIN users c ON c.id_user = a.created_by
                LEFT JOIN request_payment r ON r.no_doc = a.id
            WHERE
                1 = 1 AND a.sts_req_payment = 1
                ' . $where_tgl_from2 . '
                ' . $where_tgl_to2 . '
                ' . $where_bank . '
                ' . $where_search2 . '
            ORDER BY id DESC
            LIMIT ' . $length . ' OFFSET ' . $start . '
        ';
        $get_data = $this->db->query($query);

        $query_all = '
            SELECT
                a.id as id,
                a.tgl as tgl,
                a.deskripsi as deskripsi,
                r.tipe as tipe,
                "kasbon" as tipee,
                a.grand_total as grand_total,
                a.bank as bank,
                a.bank_number as bank_number,
                a.bank_account as bank_account,
                a.sts_reject as sts_reject,
                a.sts_reject_manage as sts_reject_manage,
                a.reject_reason as reject_reason,
                c.nm_lengkap as nama
            FROM
".DBCNL.".                kons_tr_kasbon_project_header a
                LEFT JOIN users c ON c.id_user = a.created_by
                LEFT JOIN request_payment r ON r.no_doc = a.id
            WHERE
                1 = 1 AND a.sts_req_payment = 1
                ' . $where_tgl_from . '
                ' . $where_tgl_to . '
                ' . $where_bank . '
                ' . $where_search . '
            UNION ALL

            SELECT 
                a.id as id,
                DATE_FORMAT(a.created_date, "%Y-%m-%d") as tgl,
                b.deskripsi as deskripsi,
                r.tipe as tipe,
                "expense" as tipee,
                a.selisih as grand_total,
                a.bank as bank,
                a.bank_number as bank_number,
                a.bank_account as bank_account,
                a.sts_reject as sts_reject,
                a.sts_reject_manage as sts_reject_manage,
                a.reject_reason as reject_reason,
                c.nm_lengkap as nama
            FROM
".DBCNL.".                kons_tr_expense_report_project_header a
                LEFT JOIN kons_tr_kasbon_project_header b ON b.id = a.id_header
                LEFT JOIN users c ON c.id_user = a.created_by
                LEFT JOIN request_payment r ON r.no_doc = a.id
            WHERE
                1 = 1 AND a.sts_req_payment = 1
                ' . $where_tgl_from2 . '
                ' . $where_tgl_to2 . '
                ' . $where_bank . '
                ' . $where_search2 . '
            ORDER BY id DESC
        ';
        $get_data_all = $this->db->query($query_all);

        $hasil = [];

        $no = 0;
        foreach ($get_data->result() as $item) {
            $no++;

            $get_payment_approve = $this->db->select('no_doc, created_by, pay_by, DATE_FORMAT(created_on, "%d %M %Y") as tgl_pengajuan, IF(pay_on IS NULL, "", DATE_FORMAT(pay_on, "%d %M %Y")) as tgl_pembayaran')->get('payment_approve')->result();
            foreach ($get_payment_approve as $item_payment) {
                $list_tgl_pengajuan_pembayaran[$item_payment->no_doc] = [
                    'diajukan_oleh' => $item_payment->created_by,
                    'dibayar_oleh' => $item_payment->pay_by,
                    'tgl_pengajuan' => $item_payment->tgl_pengajuan,
                    'tgl_pembayaran' => $item_payment->tgl_pembayaran
                ];
            }

            $diajukan_oleh = (isset($list_tgl_pengajuan_pembayaran[$item->id])) ? $list_tgl_pengajuan_pembayaran[$item->id]['diajukan_oleh'] : '';

            $tgl_pengajuan = (isset($list_tgl_pengajuan_pembayaran[$item->id])) ? $list_tgl_pengajuan_pembayaran[$item->id]['tgl_pengajuan'] : '';

            $dibayar_oleh = (isset($list_tgl_pengajuan_pembayaran[$item->id])) ? $list_tgl_pengajuan_pembayaran[$item->id]['dibayar_oleh'] : '';

            $tanggal_pembayaran = (isset($list_tgl_pengajuan_pembayaran[$item->id])) ? $list_tgl_pengajuan_pembayaran[$item->id]['tgl_pembayaran'] : '';

            $get_request_payment = $this->db->get_where('request_payment', ['no_doc' => $item->id])->row();

            if (!empty($get_request_payment)) {
                if ($item->sts_reject !== '1' && $item->sts_reject_manage !== '1') {
                    if ($get_request_payment->status == '0') {
                        $status = '<div class="badge bg-yellow text-light">Process</div>';
                    }
                    if ($get_request_payment->status == '1' || $get_request_payment->status == '2') {
                        $get_payment_approve = $this->db->get_where('payment_approve', ['no_doc' => $item->id])->row();
                        if ($get_payment_approve->status == '2') {
                            $status = '<div class="badge bg-green text-light">Paid</div>';
                        } else {
                            $status = '<div class="badge bg-yellow text-light">Approved</div>';
                        }
                    }
                } else {
                    if ($item->sts_reject == '1') {
                        $status = '<div class="badge bg-red">Rejected by Checker</div>';
                    } else if ($item->sts_reject_manage == '1') {
                        $status = '<div class="badge bg-red">Rejected by Management</div>';
                    } else {
                        $status = '<div class="badge bg-blue">Open</div>';
                    }
                }
            } else {
                if ($item->sts_reject == '1') {
                    $status = '<div class="badge bg-red">Rejected by Checker</div>';
                } else if ($item->sts_reject_manage == '1') {
                    $status = '<div class="badge bg-red">Rejected by Management</div>';
                } else {
                    $status = '<div class="badge bg-blue">Open</div>';
                }
            }

            $hasil[] = [
                'no' => $no,
                'no_dokumen' => $item->id,
                'request_by' => $item->nama,
                'tanggal' => $item->tgl,
                'keperluan' => $item->deskripsi,
                'tipe' => $item->tipe,
                'nilai_pengajuan' => number_format($item->grand_total, 2),
                'diajukan_oleh' => $diajukan_oleh,
                'tanggal_pengajuan' => $tgl_pengajuan,
                'dibayar_oleh' => $dibayar_oleh,
                'tanggal_pembayaran' => $tanggal_pembayaran,
                'status' => $status
            ];
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_data_all->num_rows(),
            'recordsFiltered' => $get_data_all->num_rows(),
            'data' => $hasil
        ]);
    }
}

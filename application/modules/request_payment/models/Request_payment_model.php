<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Harboens
 * @copyright Copyright (c) 2022
 *
 * This is Model for Request Payment
 */

class Request_payment_model extends BF_Model
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
            if ($tab == 'transport') {
                $data = $this->db->query("SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,'Transportasi' as keperluan, 'transportasi' as tipe,(SELECT IF(SUM(aa.jumlah_kasbon) IS NULL, 0, SUM(aa.jumlah_kasbon)) FROM tr_transport aa WHERE aa.no_req = a.no_doc AND aa.req_payment = 0) as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname, a.sts_reject, a.sts_reject_manage, a.reject_reason FROM tr_transport_req a WHERE a.status = 1 " . $where_date1 . " GROUP BY no_doc")->result();
            }
            if ($tab == 'kasbon') {
                $data = $this->db->query("SELECT id as ids,no_doc,nama,tgl_doc,keperluan, 'kasbon' as tipe,jumlah_kasbon as jumlah,null as tanggal,no_doc as id, bank_id, accnumber, accname, sts_reject, sts_reject_manage, reject_reason, status, kurang_bayar FROM tr_kasbon WHERE (status=1 AND (metode_pembayaran = 1 OR metode_pembayaran IS NULL))  " . $where_date2 . " GROUP BY no_doc")->result();
            }
            if ($tab == 'expense' || $tab == 'pembayaran_po') {
                $data = $this->db->query("SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.informasi as keperluan, 'expense' as tipe,a.jumlah,null as tanggal,a.no_doc as id, bank_id, accnumber, accname, sts_reject, sts_reject_manage, reject_reason, id_kasbon, kurang_bayar FROM tr_expense a left join " . DBACC . ".coa_master as b on a.coa=b.no_perkiraan WHERE a.status=1 AND a.jumlah > 0 " . $where_date1 . " OR (a.id_kasbon IS NOT NULL AND a.kurang_bayar IS NOT NULL AND a.kurang_bayar > 0 AND a.status=1) GROUP BY a.no_doc")->result();
            }
            if ($tab == 'periodik') {
                $data = $this->db->query(" SELECT b.id as ids,a.no_doc,c.nm_lengkap nama,a.tanggal_doc as tgl_doc,b.nama as keperluan, 'periodik' as tipe,b.nilai jumlah,null as tanggal,a.no_doc as id, b.bank_id, b.accnumber, b.accname, b.sts_reject, b.sts_reject_manage, b.reject_reason FROM tr_pengajuan_rutin a join tr_pengajuan_rutin_detail b on a.no_doc=b.no_doc left join users c on a.created_by = c.id_user WHERE a.status='1' and (b.id_payment='0' OR b.id_payment IS NULL)" . $where_date3)->result();
            }
            if ($tab == 'direct_payment') {
                $this->db->select('a.ids, a.no_doc, b.nm_lengkap as nama, a.tgl_doc, a.deskripsi as keperluan, "direct_payment" as tipe, a.grand_total as jumlah, "" as tgl, a.no_doc as id, a.bank as bank_id, a.bank_number as accnumber, a.bank_account as accname, a.sts_reject, a.sts_reject_manage, a.reject_reason');
                $this->db->from('tr_direct_payment a');
                $this->db->join('users b', 'b.id_user = a.created_by', 'left');
                $this->db->where('a.sts', 1);
                $this->db->where('a.grand_total >', 0);
                $this->db->group_start();
                $this->db->where('a.metode_pembayaran', 1);
                $this->db->or_where('a.metode_pembayaran IS NULL');
                $this->db->group_end();
                $data = $this->db->get()->result();

                // print_r($this->db->last_query());
                // exit;
            }
        } else {
            $data    = $this->db->query("SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,'Transportasi' as keperluan, 'transportasi' as tipe,(SELECT IF(SUM(aa.jumlah_kasbon) IS NULL, 0, SUM(aa.jumlah_kasbon)) FROM tr_transport aa WHERE aa.no_req = a.no_doc AND aa.req_payment = 0) as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname, a.sts_reject, a.sts_reject_manage, a.reject_reason FROM tr_transport_req a WHERE a.status = 1 " . $where_date1 . "
            GROUP BY no_doc
            union all
            SELECT id as ids,no_doc,nama,tgl_doc,keperluan, 'kasbon' as tipe,jumlah_kasbon as jumlah,null as tanggal,no_doc as id, bank_id, accnumber, accname, sts_reject, sts_reject_manage, reject_reason FROM tr_kasbon WHERE status=1 AND (metode_pembayaran = 1 OR metode_pembayaran IS NULL) " . $where_date1 . "
            GROUP BY no_doc
            union all
            SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.informasi as keperluan, 'expense' as tipe,a.jumlah,null as tanggal,a.no_doc as id, bank_id, accnumber, accname, sts_reject, sts_reject_manage, reject_reason FROM tr_expense a left join " . DBACC . ".coa_master as b on a.coa=b.no_perkiraan WHERE a.status=1 AND a.jumlah > 0  " . $where_date1 . "
            GROUP BY a.no_doc
            union all
            SELECT b.id as ids,a.no_doc,c.nm_lengkap nama,a.tanggal_doc as tgl_doc,b.nama as keperluan, 'periodik' as tipe,b.nilai jumlah,null as tanggal,a.no_doc as id, b.bank_id, b.accnumber, b.accname, b.sts_reject, b.sts_reject_manage, b.reject_reason FROM tr_pengajuan_rutin a join tr_pengajuan_rutin_detail b on a.no_doc=b.no_doc left join users c on a.created_by = c.id_user WHERE a.status='1' and (b.id_payment='0' OR b.id_payment IS NULL) " . $where_date3 . "
            ")->result();
        }

        return $data;
    }

    public function GetListDataRequestNew()
    {
        $data = $this->db->query("
            SELECT 
                a.id AS ids,
                a.no_doc,
                a.nama,
                a.tgl_doc,
                'Transportasi' AS keperluan,
                'transportasi' AS tipe,
                (
                    SELECT IF(SUM(aa.jumlah_kasbon) IS NULL, 0, SUM(aa.jumlah_kasbon)) 
                    FROM tr_transport aa 
                    WHERE aa.no_req = a.no_doc 
                    AND aa.req_payment = 0
                ) AS jumlah,
                NULL AS tanggal,
                a.no_doc AS id, 
                a.bank_id, 
                a.accnumber, 
                a.accname, 
                a.sts_reject, 
                a.sts_reject_manage, 
                a.reject_reason 
            FROM tr_transport_req a 
            WHERE a.status = 1
            GROUP BY a.no_doc
        ")->result();

        return $data;
    }


    public function GetListDataPaymentList()
    {
        $data = $this->db->query("
        SELECT id as ids, no_doc, nama, tgl_doc, 'Transportasi' as keperluan, 'transportasi' as tipe, jumlah_expense as jumlah, null as tanggal, no_doc as id, bank_id, accnumber, accname, sts_reject, sts_reject_manage, reject_reason, null as kurang_bayar, null as id_kasbon 
        FROM tr_transport_req 
        
        UNION ALL
        
        SELECT id as ids, no_doc, nama, tgl_doc, keperluan, 'kasbon' as tipe, jumlah_kasbon as jumlah, null as tanggal, no_doc as id, bank_id, accnumber, accname, sts_reject, sts_reject_manage, reject_reason, null as kurang_bayar, null as id_kasbon 
        FROM tr_kasbon
        
        UNION ALL
        
        SELECT a.id as ids, a.no_doc, a.nama, a.tgl_doc, a.informasi as keperluan, 'expense' as tipe, a.jumlah, null as tanggal, a.no_doc as id, bank_id, accnumber, accname, a.sts_reject, a.sts_reject_manage, a.reject_reason, a.kurang_bayar, a.id_kasbon 
        FROM tr_expense a 
        LEFT JOIN " . DBACC . ".coa_master as b ON a.coa = b.no_perkiraan 
        WHERE (a.jumlah >= 0 OR (a.id_kasbon IS NOT NULL AND a.kurang_bayar IS NOT NULL AND a.kurang_bayar > 0 AND a.status=2))
        
        UNION ALL
        
        SELECT b.id as ids, a.no_doc, c.nm_lengkap nama, a.tanggal_doc as tgl_doc, b.nama as keperluan, 'periodik' as tipe, b.nilai jumlah, null as tanggal, a.no_doc as id, b.bank_id, b.accnumber, b.accname, b.sts_reject, b.sts_reject_manage, b.reject_reason, null as kurang_bayar, null as id_kasbon 
        FROM tr_pengajuan_rutin a 
        JOIN tr_pengajuan_rutin_detail b ON a.no_doc = b.no_doc 
        JOIN users c ON a.created_by = c.id_user
    ")->result();

        return $data;
    }

    // list data payment
    // public function GetListDataPayment($where = '')
    // {
    //     $data    = $this->db->query("SELECT * FROM request_payment WHERE " . $where . " order by id desc")->result();
    //     return $data;
    // }

    /* EDITED BY HIKMAT A.R [18-08-2022] */
    public function GetListDataApproval($where = '')
    {
        $data    = $this->db->query("SELECT a.* FROM request_payment a WHERE " . $where . " order by tanggal desc, tipe ,id")->result();
        return $data;
    }

    public function GetListDataPayment($where = '')
    {
        $data    = $this->db->query("SELECT * FROM payment_approve WHERE " . $where . " order by status asc ,id desc")->result();
        return $data;
    }

    public function GetListDataJurnal()
    {
        $data    = $this->db->query("SELECT nomor,tanggal,tipe,no_reff,stspos,sum(kredit) as total FROM jurnal group by nomor order by nomor desc")->result();
        return $data;
    }

    function generate_id_detail($no = null)
    {
        $generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM payment_approve_details WHERE id LIKE '%PAY1-" . date('m-y') . "%'")->row();
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
        $generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM payment_approve WHERE id LIKE '%PAY-" . date('m-y') . "%'")->row();
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

        $data    = $this->db->query("SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,'Transportasi' as keperluan, 'transportasi' as tipe,a.jumlah_expense as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM tr_transport_req a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.id != '' " . $filter_tgl1 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.keperluan, 'kasbon' as tipe,a.jumlah_kasbon as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM tr_kasbon a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.id != '' " . $filter_tgl2 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.informasi as keperluan, 'expense' as tipe,a.jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM tr_expense a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.jumlah >= 0 " . $filter_tgl3 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.pic nama,a.tanggal_doc as tgl_doc,a.info as keperluan, 'nonpo' as tipe,a.nilai_request jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM tr_non_po_header a LEFT JOIN request_payment b ON b.no_doc = a.no_doc  WHERE a.id != '' " . $filter_tgl4 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT b.id as ids,a.no_doc,c.nm_lengkap nama,a.tanggal_doc as tgl_doc,b.nama as keperluan, 'periodik' as tipe,b.nilai jumlah,null as tanggal,a.no_doc as id, b.bank_id, b.accnumber, b.accname FROM tr_pengajuan_rutin a join tr_pengajuan_rutin_detail b on a.no_doc=b.no_doc join users c on a.created_by=c.id_user left join request_payment d ON d.no_doc = a.no_doc WHERE b.id != '' " . $filter_tgl5 . " " . $filter_bank2 . "

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

        $data    = $this->db->query("SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,'Transportasi' as keperluan, 'transportasi' as tipe,a.jumlah_expense as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM tr_transport_req a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.id != '' " . $filter_tgl1 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.keperluan, 'kasbon' as tipe,a.jumlah_kasbon as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM tr_kasbon a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.id != '' " . $filter_tgl2 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.informasi as keperluan, 'expense' as tipe,a.jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM tr_expense a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.jumlah >= 0 " . $filter_tgl3 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.pic nama,a.tanggal_doc as tgl_doc,a.info as keperluan, 'nonpo' as tipe,a.nilai_request jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname FROM tr_non_po_header a LEFT JOIN request_payment b ON b.no_doc = a.no_doc  WHERE a.id != '' " . $filter_tgl4 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT b.id as ids,a.no_doc,c.nm_lengkap nama,a.tanggal_doc as tgl_doc,b.nama as keperluan, 'periodik' as tipe,b.nilai jumlah,null as tanggal,a.no_doc as id, b.bank_id, b.accnumber, b.accname FROM tr_pengajuan_rutin a join tr_pengajuan_rutin_detail b on a.no_doc=b.no_doc join users c on a.created_by=c.id_user left join request_payment d ON d.no_doc = a.no_doc WHERE b.id != '' " . $filter_tgl5 . " " . $filter_bank2 . "

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
    //     $generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM payment_approve WHERE id LIKE '%BK-" . date('Y-m-') . "%'")->row();
    //     $kodeBarang = $generate_id->max_id;
    //     $urutan = (int) substr($kodeBarang, 12, 5);
    //     $urutan++;
    //     $tahun = date('Y-m-');
    //     $huruf = "PI-";
    //     $kodecollect = $huruf . $tahun . sprintf("%06s", $urutan);

    //     return $kodecollect;
    // }

    public function generate_id_payment($kode_bank = null)
    {
        $generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM payment_approve WHERE id LIKE '%BK-" . $kode_bank . "-" . date('my-') . "%'")->row();
        $kodeBarang = $generate_id->max_id;
        $urutan = (int) substr($kodeBarang, 16, 4);
        $urutan++;
        $tahun = date('my-');
        $huruf = "BK-" . $kode_bank . "-";
        $kodecollect = $huruf . $tahun . sprintf("%04s", $urutan);

        return $kodecollect;
    }
}

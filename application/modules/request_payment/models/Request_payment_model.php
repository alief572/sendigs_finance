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

        $data    = $this->db->query("SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,'Transportasi' as keperluan, 'transportasi' as tipe,a.jumlah_expense as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname, a.sts_reject, a.sts_reject_manage FROM tr_transport_req a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.id != '' " . $filter_tgl1 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.keperluan, 'kasbon' as tipe,a.jumlah_kasbon as jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname, a.sts_reject, a.sts_reject_manage FROM tr_kasbon a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.id != '' " . $filter_tgl2 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.nama,a.tgl_doc,a.informasi as keperluan, 'expense' as tipe,a.jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname, a.sts_reject, a.sts_reject_manage FROM tr_expense a LEFT JOIN request_payment b ON b.no_doc = a.no_doc WHERE a.jumlah >= 0 " . $filter_tgl3 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT a.id as ids,a.no_doc,a.pic nama,a.tanggal_doc as tgl_doc,a.info as keperluan, 'nonpo' as tipe,a.nilai_request jumlah,null as tanggal,a.no_doc as id, a.bank_id, a.accnumber, a.accname, a.sts_reject, a.sts_reject_manage FROM tr_non_po_header a LEFT JOIN request_payment b ON b.no_doc = a.no_doc  WHERE a.id != '' " . $filter_tgl4 . " " . $filter_bank1 . "
        GROUP BY a.no_doc
		union all
		SELECT b.id as ids,a.no_doc,c.nm_lengkap nama,a.tanggal_doc as tgl_doc,b.nama as keperluan, 'periodik' as tipe,b.nilai jumlah,null as tanggal,a.no_doc as id, b.bank_id, b.accnumber, b.accname, b.sts_reject, b.sts_reject_manage FROM tr_pengajuan_rutin a join tr_pengajuan_rutin_detail b on a.no_doc=b.no_doc join users c on a.created_by=c.id_user left join request_payment d ON d.no_doc = a.no_doc WHERE b.id != '' " . $filter_tgl5 . " " . $filter_bank2 . "

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
        if ($kode_bank == null) {
            $urutan = (int) substr($kodeBarang, 9, 4);
        } else {
            $urutan = (int) substr($kodeBarang, 16, 4);
        }
        $urutan++;
        $tahun = date('my-');
        $huruf = "BK-" . $kode_bank . "-";
        $kodecollect = $huruf . $tahun . sprintf("%04s", $urutan);

        return $kodecollect;
    }

    public function generate_id_payment2($kode_bank = null, $no_tambah = 0)
    {
        $generate_id = $this->db->query("SELECT MAX(id) AS max_id FROM payment_approve WHERE id LIKE '%BK-" . $kode_bank . "-" . date('my-') . "%'")->row();
        $kodeBarang = $generate_id->max_id;
        if ($kode_bank == null) {
            $urutan = (int) substr($kodeBarang, 9, 4);
        } else {
            $urutan = (int) substr($kodeBarang, 16, 4);
        }
        $urutan++;

        $urutan = ($urutan + $no_tambah);
        $tahun = date('my-');
        $huruf = "BK-" . $kode_bank . "-";
        $kodecollect = $huruf . $tahun . sprintf("%04s", $urutan);

        return $kodecollect;
    }

    public function get_data_req_payment()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');

        $this->db->select('a.*');
        $this->db->from('v_request_payment a');
        $this->db->where('a.status', '1');

        $count_all = $this->db->count_all_results('', false);

        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.no_dokumen', $search['value'], 'both');
            $this->db->or_like('a.request_by', $search['value'], 'both');
            $this->db->or_like('DATE_FORMAT(a.tanggal, "%d-%M-%Y")', $search['value'], 'both');
            $this->db->or_like('a.keperluan', $search['value'], 'both');
            $this->db->or_like('a.kategori', $search['value'], 'both');
            $this->db->or_like('a.nilai_pengajuan', $search['value'], 'both');
            $this->db->group_end();
        }

        $count_filter = $this->db->count_all_results('', false);

        $this->db->order_by('a.tanggal', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        $no = ($start + 0);
        $hasil = [];

        foreach ($get_data->result() as $item) {
            $no++;

            $nmuser = $item->request_by;
            if ($item->kategori == 'Kasbon') {
                $get_kasbon = $this->db->get_where('tr_kasbon', array('no_doc' => $item->no_dokumen))->row();
                $check_detail = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $item->no_dokumen])->result();
                if (count($check_detail)) {
                    if ($get_kasbon->tipe_pr == 'pr departemen') {
                        $this->db->select('b.nm_lengkap');
                        $this->db->from('rutin_non_planning_header a');
                        $this->db->join('users b', 'b.id_user = a.created_by');
                        $this->db->where('a.no_pr', $get_kasbon->id_pr);
                        $get_single_detail = $this->db->get()->row();

                        $nmuser = $get_single_detail->nm_lengkap;
                    }

                    if ($get_kasbon->tipe_pr == 'pr stok') {
                        $this->db->select('b.nm_lengkap');
                        $this->db->from('material_planning_base_on_produksi a');
                        $this->db->join('users b', 'b.id_user = a.created_by');
                        $this->db->where('a.no_pr', $get_kasbon->id_pr);
                        $get_single_detail = $this->db->get()->row();

                        $nmuser = $get_single_detail->nm_lengkap;
                    }
                }
            }

            $check_added = $this->db->get_where('tr_added_req_payment', ['no_doc' => $item->no_dokumen])->result();

            $checked = (count($check_added) > 0) ? 'checked' : '';

            $input_tanggal_pembayaran = '<input type="date" class="form-control form-control-sm" name="tanggal_pembayaran_' . $item->no_dokumen . '">';

            $action = '<input type="checkbox" class="pilih_data" name="pilih[]" value="' . $item->no_dokumen . '" data-kategori="' . $item->kategori . '" ' . $checked . '>';
            $action .= '<input type="hidden" name="kategori_' . $item->no_dokumen . '" value="' . $item->kategori . '">';
            $action .= '<input type="hidden" name="nilai_pengajuan_' . $item->no_dokumen . '" value="' . $item->nilai_pengajuan . '">';

            $btn_print = '';
            if ($item->kategori == 'Periodik') {
                $btn_print = ' <a href="' . base_url('expense/periodik_print/' . $item->id) . '" target="_blank" class="btn btn-sm btn-info" title="Print"><i class="fa fa-print"></i></a>';
            }
            if ($item->kategori == 'Kasbon') {
                $btn_print = ' <a href="' . base_url('expense/kasbon_print/' . $item->id) . '" target="_blank" class="btn btn-sm btn-info" title="Print"><i class="fa fa-print"></i></a>';
            }
            if ($item->kategori == 'Transport') {
                $btn_print = ' <a href="' . base_url('expense/transport_req_print/' . $item->id) . '" target="_blank" class="btn btn-sm btn-info" title="Print"><i class="fa fa-print"></i></a>';
            }
            if ($item->kategori == 'Expense') {
                $btn_print = ' <a href="' . base_url('expense/expense_print/' . $item->id) . '" target="_blank" class="btn btn-sm btn-info" title="Print"><i class="fa fa-print"></i></a>';
            }
            if ($item->kategori == 'Cash') {
                $get_check_non_po = $this->db->get_where('tr_pr_non_po', ['id' => $item->id])->row();
                if ($get_check_non_po->jenis_pr == 'pr departemen' || $get_check_non_po->jenis_pr == 'pr asset') {
                    $btn_print = '<a href="' . base_url('request_payment/print_cash/' . $item->id) . '" target="_blank" class="btn btn-sm btn-info" title="Print"><i class="fa fa-print"></i></a>';
                }
            }

            $keperluan = (!empty($item->keperluan)) ? $item->keperluan : '';
            if ($item->kategori == 'Non-PO') {
                $get_pr_non_po = $this->db->get_where('tr_pr_non_po', ['id' => $item->id])->row();

                if ($get_pr_non_po->jenis_pr == 'pr stok') {
                    $keperluan = 'PR Stock - ' . $get_pr_non_po->no_pr;
                } else if ($get_pr_non_po->jenis_pr == 'pr departemen') {
                    $get_pr_dept = $this->db->get_where('rutin_non_planning_header', ['no_pr' => $get_pr_non_po->no_pr])->row();

                    $keperluan = (!empty($get_pr_dept)) ? $get_pr_dept->project_name : '';
                } else {
                    $get_pr_asset = $this->db->get_where('tran_pr_detail', ['no_pr' => $get_pr_non_po->no_pr])->row();

                    $keperluan = 'PR Asset - ' . $get_pr_non_po->no_pr . ' - ' . $get_pr_asset->nm_barang;
                }
            }

            $hasil[] = [
                'no' => $no,
                'no_dokumen' => $item->no_dokumen . ' ' . $btn_print,
                'request_by' => $nmuser,
                'tanggal' => date('d F Y', strtotime($item->tanggal)),
                'keperluan' => $keperluan,
                'kategori' => $item->kategori,
                'nilai_pengajuan' => number_format($item->nilai_pengajuan, 2),
                'tanggal_pembayaran' => $input_tanggal_pembayaran,
                'action' => $action
            ];
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsFiltered' => $count_filter,
            'recordsTotal' => $count_all,
            'data' => $hasil
        ]);
    }

    public function copy_to_payment()
    {

        $arr_header = [];
        $arr_detail = [];
        $updateDetail = [];
        $updateExpense = [];
        $arr_update_req_payment = [];

        $this->db->select('a.*');
        $this->db->from('request_payment a');
        $this->db->join('payment_approve b', 'b.no_doc = a.no_doc', 'left');
        $this->db->where('b.no_doc IS NULL');
        $this->db->where('a.tipe <>', 'direct_payment');
        $get_request_payment = $this->db->get()->result_array();

        $no = 0;
        $no2 = 1;
        foreach ($get_request_payment as $item) {
            $no_coa_bank = explode(' - ', $item['bank_name']);
            $no_coa_bank = $no_coa_bank[0];

            $kode_bank = '';
            if (!empty($no_coa_bank)) {
                $get_kode_bank = $this->db->get_where(DBACC . '.coa_master', ['no_perkiraan' => $no_coa_bank])->row();
                if (!empty($get_kode_bank)) {
                    $kode_bank = $get_kode_bank->kode_bank;
                }
            }

            $Id = $this->generate_id_payment2($kode_bank, $no);

            $arr_header[] = [
                'id' => $Id,
                'no_doc' => $item['no_doc'],
                'nama' => $item['nama'],
                'tgl_doc' => $item['tgl_doc'],
                'keperluan' => $item['keperluan'],
                'tipe' => $item['tipe'],
                'jumlah' => $item['jumlah'],
                'status' => 1,
                'tanggal' => $item['tanggal'],
                'bank_coa' => $item['bank_coa'],
                'bank_nilai' => $item['bank_nilai'],
                'bank_admin' => $item['bank_admin'],
                'keterangan' => $item['keterangan'],
                'created_by' => $item['created_by'],
                'created_on' => $item['created_on'],
                'approved_by' => $this->auth->user_name(),
                'approved_on' => date('Y-m-d H:i:s'),
                'pay_by' => $item['pay_by'],
                'pay_on' => $item['pay_on'],
                'doc_file' => $item['doc_file'],
                'doc_file_2' => $item['doc_file_2'],
                'bank_id' => $item['bank_id'],
                'accnumber' => $item['accnumber'],
                'accname' => $item['accname'],
                'ids' => $item['ids'],
                'no_request' => $item['no_request'],
                'app_checker' => $item['app_checker'],
                'app_checker_by' => $item['app_checker_by'],
                'app_checker_date' => $item['app_checker_date'],
                'currency' => $item['currency'],
                'bank_name' => $item['bank_name'],
                'admin_bank' => $item['admin_bank'],
                'link_doc' => $item['link_doc'],
                'tipe_pph' => $item['tipe_pph'],
                'total_pph' => $item['total_pph']
            ];

            $arr_update_req_payment[] = [
                'no_doc' => $item['no_doc'],
                'status' => 2
            ];

            if ($item['tipe'] == 'expense') {
                $get_expense_detail = $this->db->get_where('tr_expense_detail', ['no_doc' => $item['no_doc']])->result_array();

                foreach ($get_expense_detail as $item_expense) {

                    $id_detail = $this->Request_payment_model->generate_id_detail($no2);

                    if ($item_expense['id_kasbon'] != null) {
                        $harga = $item_expense['kurang_bayar'];
                        $total = $item_expense['kurang_bayar'];
                    } else {
                        $harga = $item_expense['harga'];
                        $total = $item_expense['total_harga'];
                        if ($item_expense['kasbon'] > 0) {
                            $harga = ($item_expense['kasbon'] * -1);
                            $total = ($item_expense['kasbon'] * -1);
                        }
                    }

                    $arr_detail[]         = [
                        'id'             => $id_detail,
                        'payment_id'     => $Id,
                        'no_doc'         => $item_expense['no_doc'],
                        'tgl_doc'         => $item_expense['tanggal'],
                        'deskripsi'     => $item_expense['deskripsi'],
                        'qty'             => $item_expense['qty'],
                        'harga'         => $harga,
                        'total'         => $total,
                        'keterangan'     => $item_expense['keterangan'],
                        'doc_file'         => $item_expense['doc_file'],
                        'coa'             => $item_expense['coa'],
                        'created_by'     => $this->auth->user_name(),
                        'created_on'     => date("Y-m-d h:i:s"),
                    ];

                    $updateDetail[] = [
                        'id'             => $item_expense['id'],
                        'status'         => '2',
                        'modified_by'     => $this->auth->user_name(),
                        'modified_on'     => date("Y-m-d h:i:s"),
                    ];

                    $updateExpense[] = [
                        'id'             => $item_expense['id'],
                        'status'         => '3',
                        'modified_by'     => $this->auth->user_name(),
                        'modified_on'     => date("Y-m-d h:i:s"),
                    ];

                    // if ($item_expense['id_kasbon'] != null) {
                    //     $Harga[]            = $item_expense['kurang_bayar'];
                    // } else {
                    //     if ($item_expense['id_kasbon'] == '') {
                    //         $Harga[]         = ($item_expense['harga'] * $item_expense['qty']);
                    //     } else {
                    //         $Harga[]         = ($item_expense['kasbon'] * -1);
                    //     }
                    // }

                    $no2++;
                }
            }

            if ($item['tipe'] == 'kasbon') {
                $Id = $this->generate_id_payment2($kode_bank, $no);

                $dtl                 = $this->db->get_where('tr_kasbon', ['no_doc' => $item['no_doc']])->row();

                if (isset($dtl->kurang_bayar) && $dtl->kurang_bayar != null) {
                    $nilai = $dtl->kurang_bayar;
                } else {
                    $nilai = $dtl->jumlah_kasbon;
                }

                $id_detail = $this->Request_payment_model->generate_id_detail($no2);

                $arr_detail[]         = [
                    'id'             => $id_detail,
                    'payment_id'     => $Id,
                    'no_doc'         => $dtl->no_doc,
                    'tgl_doc'         => $dtl->tgl_doc,
                    'deskripsi'     => $dtl->keperluan,
                    'qty'             => '1',
                    'harga'         => $nilai,
                    'total'         => $nilai,
                    'keterangan'     => $dtl->keperluan,
                    'doc_file'         => $dtl->doc_file,
                    'coa'             => $dtl->coa,
                    'created_by'     => $this->auth->user_name(),
                    'created_on'     => date("Y-m-d h:i:s"),
                ];
                $updateDetail[] = [
                    'id'             => $dtl->id,
                    'status'         => '3',
                    'modified_by'     => $this->auth->user_name(),
                    'modified_on'     => date("Y-m-d h:i:s"),
                ];

                $no2++;
            }

            if ($item['tipe'] == 'transportasi') {
                $id_detail = $this->Request_payment_model->generate_id_detail($no2);

                $dtl                 = $this->db->get_where('tr_transport', ['no_doc' => $item['no_doc']])->row();

                $arr_keperluan = [];
                $this->db->select('a.keperluan');
                $this->db->from('tr_transport a');
                $this->db->where('a.no_doc', $item['no_doc']);
                $this->db->group_by('a.keperluan');
                $get_keperluan = $this->db->get()->result_array();

                foreach ($get_keperluan as $itemm) {
                    $arr_keperluan[] = $itemm['keperluan'];
                }

                $keperluan = implode(', ', $arr_keperluan);


                $ArrDetail[]         = [
                    'id'             => $id_detail,
                    'payment_id'     => $Id,
                    'no_doc'         => $dtl->no_req,
                    'tgl_doc'         => $dtl->tgl_doc,
                    'deskripsi'     => $keperluan,
                    'qty'             => '1',
                    'harga'         => $dtl->jumlah_kasbon,
                    'total'         => $dtl->jumlah_kasbon,
                    'keterangan'     => $keperluan,
                    'doc_file'         => $dtl->doc_file,
                    'coa'             => null,
                    'created_by'     => $this->auth->user_name(),
                    'created_on'     => date("Y-m-d h:i:s"),
                ];

                $updateDetail[] = [
                    'id'             => $dtl->id,
                    'status'         => '2',
                    'modified_by'     => $this->auth->user_name(),
                    'modified_on'     => date("Y-m-d h:i:s"),
                ];
                // $Harga[]         = $dtl->jumlah_kasbon;

                $no2++;
            }

            if ($item['tipe'] == 'nonpo') {


                $dtl_get = $this->db->get_where('tr_non_po_detail', ['no_doc' => $item['no_doc']])->row();

                foreach ($dtl_get as $dtl) {
                    $id_detail = $this->Request_payment_model->generate_id_detail($no2);
                    $ArrDetail[]         = [
                        'id'             => $id_detail,
                        'payment_id'     => $Id,
                        'no_doc'         => $dtl->no_doc,
                        'tgl_doc'         => $dtl->tgl_pr,
                        'deskripsi'     => $dtl->deskripsi,
                        'qty'             => '1',
                        'harga'         => $dtl->nilai_satuan_request,
                        'total'         => $dtl->total_request,
                        'keterangan'     => $dtl->keterangan,
                        // 'doc_file' 		=> $dtl->doc_file,
                        'coa'             => null,
                        'created_by'     => $this->auth->user_name(),
                        'created_on'     => date("Y-m-d h:i:s"),
                    ];

                    $updateDetail[] = [
                        'id'             => $dtl->id,
                        'status'         => '1',
                        'modified_by'     => $this->auth->user_name(),
                        'modified_on'     => date("Y-m-d h:i:s"),
                    ];
                    // $Harga[]         = $dtl->total_request;

                    $no2++;
                }
            }

            if ($item['tipe'] == 'periodik') {

                $dtl_get                 = $this->db->get_where('tr_pengajuan_rutin_detail', ['no_doc' => $item['no_doc']])->result_array();

                foreach ($dtl_get as $dtl) {
                    $id_detail = $this->Request_payment_model->generate_id_detail($no2);
                    $ArrDetail[]         = [
                        'id'             => $id_detail,
                        'payment_id'     => $Id,
                        'no_doc'         => $dtl['no_doc'],
                        'tgl_doc'         => $dtl['tanggal'],
                        'deskripsi'     => $dtl['keterangan'],
                        'qty'             => '1',
                        'harga'         => $dtl['nilai'],
                        'total'         => $dtl['nilai'],
                        'keterangan'     => $dtl['keterangan'],
                        'doc_file'         => $dtl['doc_file'],
                        'coa'             => $dtl['coa'],
                        'created_by'     => $this->auth->user_name(),
                        'created_on'     => date("Y-m-d h:i:s"),
                    ];

                    $updateDetail[] = [
                        'id'             => $dtl['id'],
                        'status'         => '1',
                        'modified_by'     => $this->auth->user_name(),
                        'modified_on'     => date("Y-m-d h:i:s"),
                    ];
                    $no2++;
                }

                // $Harga[] 		= $dtl->nilai;

            }

            $no++;
        }

        $this->db->trans_begin();

        if (!empty($arr_header)) {
            $insert_payment_approve = $this->db->insert_batch('payment_approve', $arr_header);
            if (!$insert_payment_approve) {
                print_r($this->db->error()['message']);
                exit;
            }
        }

        if (!empty($arr_detail)) {
            $this->db->insert_batch('payment_approve_details', $arr_detail);
        }

        if (!empty($updateDetail)) {
            $this->db->update_batch('tr_expense', $updateExpense, 'id');
        }

        if (!empty($updateDetail)) {
            $this->db->update_batch('tr_expense_detail', $updateDetail, 'id');
        }

        if (!empty($arr_update_req_payment)) {
            $this->db->update_batch('request_payment', $arr_update_req_payment);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();

            print_r($this->db->_error_message());
            print_r($this->db->_error_number());
            exit;
        } else {
            $this->db->trans_commit();
        }
    }

    public function get_payment_paid()
    {
        $get_payment_approve = $this->db->select('no_doc, created_by, created_by as by_pay, DATE_FORMAT(created_on, "%d %M %Y") as tgl_pengajuan, IF(created_on IS NULL, "", DATE_FORMAT(tgl_bayar, "%d %M %Y")) as tgl_pembayaran')->get_where('payment_approve', ['tgl_bayar <>' => null])->result();

        $list_tgl_pengajuan_pembayaran = [];
        foreach ($get_payment_approve as $item_payment) {
            $list_tgl_pengajuan_pembayaran[$item_payment->no_doc] = [
                'diajukan_oleh' => $item_payment->created_by,
                'dibayar_oleh' => $item_payment->by_pay,
                'tgl_pengajuan' => $item_payment->tgl_pengajuan,
                'tgl_pembayaran' => $item_payment->tgl_pembayaran
            ];
        }

        return $list_tgl_pengajuan_pembayaran;
    }

    public function list_added_req_payment()
    {
        $this->db->select('a.*');
        $this->db->from('tr_added_req_payment a');
        $get_list = $this->db->get()->result();

        return $get_list;
    }

    public function list_all_request_payment()
    {
        $this->db->select('a.*');
        $this->db->from('v_request_payment a');
        $this->db->where('a.status', '1');
        $this->db->order_by('a.tanggal', 'desc');
        $get_data = $this->db->get()->result();

        return $get_data;
    }
}

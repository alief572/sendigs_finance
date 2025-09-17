<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2018, Harboens
 *
 * This is controller for Purchase Order
 */

class Jurnal_fn extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'Jurnal_nomor/Acc_model',
            'Jurnal_nomor/Jurnal_model'
        ));
        date_default_timezone_set("Asia/Bangkok");
        $this->datppn = array('0' => 'Non PPN', '10' => 'PPN');
        $this->datcombodata = array('No' => 'No', 'Asli' => 'Asli', 'Copy' => 'Copy');
    }

    public function index()
    {
        // $data = $this->Purchase_order_model->GetListPR('BIAYA');
        // $this->template->set('results', $data);
        // $this->template->title('Purchase Request Operational Titik (Existing)');
        // $this->template->render('list');
    }

    function jurnal_invoicing()
    {
        //JURNAL PENGAKUAN PIUTANG
        //TERJADI SAAT CREATE INVOICE

        $id        = $this->uri->segment(4);
        $kodejurnal = $this->uri->segment(5);
        $ket = $this->uri->segment(6);

        $invoicing      = $this->db->query("SELECT a.*, b.name_customer FROM tr_invoice a         
        INNER JOIN master_customers b ON a.id_customer = b.id_customer WHERE no_invoice='$id'")->row();


        $nama   = $invoicing->name_customer;
        $nomor  = $invoicing->no_surat;


        $Tgl_Invoice = date('Y-m-d');
        $no_request = $id;
        $tgl_voucher = $Tgl_Invoice;

        $Keterangan_INV            = 'Invoicing ' . ($nama) . ' No ' . ($nomor);

        #AMBIL TEMPLATE JURNAL DAN SIMPAN KE TEMPORARY

        $datajurnal       = $this->Acc_model->GetTemplateJurnal($kodejurnal);
        foreach ($datajurnal as $record) {
            $nokir  = $record->no_perkiraan;
            $tabel  = $record->menu;
            $posisi = $record->posisi;
            $field  = $record->field;
            // if ($field == 'jumlah_bank'){
            // 	$nokir = $kd_bank;
            // } else{
            // 	$nokir  = $record->no_perkiraan;
            // }
            $no_voucher = $id;
            $param  = 'no_invoice';
            $value_param  = $id;
            $val = $this->Acc_model->GetData($tabel, $field, $param, $value_param);
            $nilaibayar = round($val[0]->$field);

            if ($posisi == 'D') {
                $det_Jurnaltes[]  = array(
                    'nomor'         => '',
                    'tanggal'       => $tgl_voucher,
                    'tipe'          => 'JV',
                    'no_perkiraan'  => $nokir,
                    'keterangan'    => $Keterangan_INV,
                    'no_reff'       => $id,
                    'debet'         => $nilaibayar,
                    'kredit'        => 0,
                    'jenis_jurnal'  => $ket,
                    'no_request'    => $no_request
                );
            } elseif ($posisi == 'K') {
                $det_Jurnaltes[]  = array(
                    'nomor'         => '',
                    'tanggal'       => $tgl_voucher,
                    'tipe'          => 'JV',
                    'no_perkiraan'  => $nokir,
                    'keterangan'    => $Keterangan_INV,
                    'no_reff'       => $id,
                    'debet'         => 0,
                    'kredit'        => $nilaibayar,
                    'jenis_jurnal'  => $ket,
                    'no_request'    => $no_request
                );
            }
        }

        $this->db->where('no_reff', $id);
        $this->db->where('jenis_jurnal', $ket);
        $this->db->delete('jurnal');

        $this->db->insert_batch('jurnal', $det_Jurnaltes);


        $noreff     = $id;
        $tipe        = 'jv';
        $jenisjurnal = $ket;
        $data['list_data']         = $this->Jurnal_model->get_detail_jurnal($noreff, $tipe, $jenisjurnal);
        $data['data_perkiraan']    = $this->Acc_model->get_noperkiraan();
        $data['jenis']            = 'JV';
        $data['akses']            = 'jurnal';
        $data['jenis_jurnal']    = $jenisjurnal;
        $data['po_no']            = $id;
        $data['total_po']        = $nilaibayar;
        $data['id_vendor']        = '';
        $data['nama_vendor']    = '';
        $data['no_surat']        = $nomor;
        $this->load->view("v_detail_jurnal", $data);
    }

    public function save_jurnal_invoicing()
    {

        $post        = $this->input->post();
        $session = $this->session->userdata('app_session');
        $data_session    = $this->session->userdata;

        $tgl_inv  = $this->input->post('tgl_jurnal[0]');
        $keterangan  = $this->input->post('keterangan[0]');
        $type        = $this->input->post('type[0]');
        $reff        = $this->input->post('reff[0]');
        $no_req      = $this->input->post('no_request[0]');
        $total       = round($this->input->post('total'));
        $jenis       = $this->input->post('jenis');
        $tipe_jurnal       = $this->input->post('tipe');
        $jenis_jurnal       = $this->input->post('jenis_jurnal');

        $total_po           = $this->input->post('total_po');
        $id_vendor          = $this->input->post('vendor_id');
        $nama_vendor        = $this->input->post('vendor_nm');

        $this->db->trans_begin();

        $Nomor_JV                = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_inv);


        $Bln             = substr($tgl_inv, 5, 2);
        $Thn             = substr($tgl_inv, 0, 4);


        $dataJVhead = array(
            'nomor'             => $Nomor_JV,
            'tgl'                 => $tgl_inv,
            'jml'                => $total,
            'koreksi_no'        => '-',
            'kdcab'                => '101',
            'jenis'                => 'JV',
            'keterangan'         => $keterangan,
            'bulan'                => $Bln,
            'tahun'                => $Thn,
            'user_id'            => $this->auth->user_id(),
            'memo'                => '',
            'tgl_jvkoreksi'        => $tgl_inv,
            'ho_valid'            => ''
        );


        $this->db->insert(DBACC . '.javh', $dataJVhead);



        for ($i = 0; $i < count($this->input->post('type')); $i++) {
            $tipe = $this->input->post('type')[$i];
            $perkiraan = $this->input->post('no_coa')[$i];
            $noreff = $this->input->post('reff')[$i];
            $jenisjurnal = $this->input->post('jenisjurnal')[$i];

            $datadetail = array(
                'tipe'            => $this->input->post('type')[$i],
                'nomor'           => $Nomor_JV,
                'tanggal'         => $this->input->post('tgl_jurnal')[$i],
                'no_perkiraan'    => $this->input->post('no_coa')[$i],
                'keterangan'      => $this->input->post('keterangan')[$i],
                'no_reff'           => $this->input->post('reff')[$i],
                'debet'            => round($this->input->post('debet')[$i]),
                'kredit'          => round($this->input->post('kredit')[$i])
            );
            $this->db->insert(DBACC . '.jurnal', $datadetail);

            $jurnal_posting     = "UPDATE jurnal SET stspos=1 WHERE tipe = '$tipe'
			AND  jenis_jurnal = '$jenisjurnal' AND no_reff  = '$noreff' ";
            $this->db->query($jurnal_posting);
        }

        $Qry_Update_Cabang_acc     = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
        $this->db->query($Qry_Update_Cabang_acc);

        $jurnal_inv     = "UPDATE tr_invoice SET status_jurnal='CLS' WHERE no_invoice = '$reff' ";
        $this->db->query($jurnal_inv);


        $invoicing      = $this->db->query("SELECT a.*, b.name_customer FROM tr_invoice a         
        INNER JOIN master_customers b ON a.id_customer = b.id_customer WHERE no_invoice='$reff'")->row();

        $id_cust   = $invoicing->id_customer;
        $nama   = $invoicing->name_customer;
        $No_Inv  = $invoicing->no_surat;


        $datapiutang = array(
            'tipe'            => 'JV',
            'nomor'            => $Nomor_JV,
            'tanggal'        => $tgl_inv,
            'no_perkiraan'  => '1104-01-01',
            'keterangan'    => $keterangan,
            'no_reff'       => $No_Inv,
            'debet'         => $total,
            'kredit'         =>  0,
            'id_supplier'     => $id_cust,
            'nama_supplier'   => $nama,

        );
        $this->db->insert('tr_kartu_piutang', $datapiutang);


        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $param = array(
                'save' => 0,
                'msg' => "GAGAL, simpan data..!!!",

            );
        } else {
            $this->db->trans_commit();

            $param = array(
                'save' => 1,
                'msg' => "SUKSES, simpan data..!!!",

            );
        }
        echo json_encode($param);
    }

    function jurnal_penerimaan()
    {
        //JURNAL PENERIMAAN
        //TERJADI SAAT CREATE PENERIMAAN

        $id        = $this->uri->segment(4);
        $kodejurnal = $this->uri->segment(5);
        $ket = $this->uri->segment(6);
        $kd_bayar = $id;
        $no_request = $id;
        $data_bayar =  $this->db->query("SELECT * FROM tr_invoice_payment WHERE kd_pembayaran = '$kd_bayar' ")->row();

        $tgl_byr             = $data_bayar->tgl_pembayaran;
        $kd_invoice        = $data_bayar->no_invoice;
        $kd_bank             = $data_bayar->kd_bank;
        $jenis_pph         = $data_bayar->jenis_pph;
        $nama                = addslashes($data_bayar->nm_customer);
        // print_r($nama);
        // exit;
        $jmlpph            = $data_bayar->biaya_pph_idr;

        $id_cust =  $this->db->query("SELECT * FROM master_customers WHERE name_customer LIKE '%$nama%'")->row();

        if (!empty($id_cust)) {
            $idcust  = $id_cust->id_customer;
        } else {
            $idcust  = '-';
        }



        $No_Inv  = $kd_bayar;
        $Tgl_Inv = $tgl_byr;
        $Bln             = substr($Tgl_Inv, 6, 2);
        $Thn             = substr($Tgl_Inv, 0, 4);
        $bulan_bayar = date("n", strtotime($Tgl_Inv));
        $tahun_bayar = date("Y", strtotime($Tgl_Inv));
        $keterangan_byr  = $data_bayar->keterangan;
        $jumlah_total    = round($data_bayar->jumlah_pembayaran_idr);
        $jumlah_terima   = round($data_bayar->jumlah_bank_idr);
        $biaya_admin     = round($data_bayar->biaya_admin_idr);
        $biaya_lain     = round($data_bayar->biaya_pph_idr);
        $deposit         = round($data_bayar->tambah_lebih_bayar);


        $jenis_reff      = $kd_bayar;
        $no_reff         = $kd_bayar;

        $Keterangan_INV            = 'Penerimaan A/N ' . $nama . ' Kode Pembayaran. ' . $No_Inv . ',' . $keterangan_byr;


        $det_Jurnal                = array();
        $det_Jurnal[]            = array(
            'nomor'         => '',
            'tanggal'       => $Tgl_Inv,
            'tipe'          => 'BUM',
            'no_perkiraan'  => $kd_bank,
            'keterangan'    => $Keterangan_INV,
            'no_reff'       => $No_Inv,
            'debet'         => $jumlah_terima,
            'kredit'        => 0,
            'jenis_jurnal'  => $ket,
            'no_request'    => $no_request,

        );

        if ($biaya_admin != 0) {
            $det_Jurnal[]            = array(
                'nomor'         => '',
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUM',
                'no_perkiraan'  => '7201-01-02',
                'keterangan'    => $Keterangan_INV,
                'no_reff'       => $No_Inv,
                'debet'         => $biaya_admin,
                'kredit'        => 0,
                'jenis_jurnal'  => $ket,
                'no_request'    => $no_request,

            );
        }
        $kd_deposit = '2101-07-01';
        if ($deposit != 0) {
            $det_Jurnal[]            = array(
                'nomor'         => '',
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUM',
                'no_perkiraan'  => '2101-08-01',
                'keterangan'    => $Keterangan_INV,
                'no_reff'       => $No_Inv,
                'debet'         => 0,
                'kredit'        => $deposit,
                'jenis_jurnal'  => $ket,
                'no_request'    => $no_request,

            );
        }



        $data_jurnal = $this->db->query("SELECT * FROM tr_invoice_payment_detail WHERE kd_pembayaran = '$kd_bayar' ")->result();

        foreach ($data_jurnal as $jr) {
            $jmlbayar   = $jr->total_bayar_idr;
            $invoice2    = $jr->no_invoice;

            $surat  =  $this->db->query("SELECT * FROM tr_invoice WHERE no_invoice = '$invoice2'")->row();

            $Keterangan_            = 'INVOICE A/N ' . $nama . ' INV NO. ' . $invoice2 . ', ' . $surat->no_surat;


            $det_Jurnal[]              = array(
                'nomor'         => '',
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUM',
                'no_perkiraan'  => '1104-01-01',
                'keterangan'    => $Keterangan_,
                'no_reff'       => $No_Inv,
                'debet'         => 0,
                'kredit'        => $jmlbayar,
                'jenis_jurnal'  => $ket,
                'no_request'    => $no_request,
            );
        }


        $this->db->where('no_reff', $id);
        $this->db->where('jenis_jurnal', $ket);
        $this->db->delete('jurnal');

        $this->db->insert_batch('jurnal', $det_Jurnal);


        $noreff     = $id;
        $tipe        = 'BUM';
        $jenisjurnal = $ket;

        //  print_r($noreff);
        //  exit;

        $data['list_data']         = $this->Jurnal_model->get_detail_jurnal($noreff, $tipe, $jenisjurnal);

        $data['data_perkiraan']    = $this->Acc_model->get_noperkiraan();
        $data['jenis']            = 'BUM';
        $data['akses']            = 'jurnal';
        $data['jenis_jurnal']    = $jenisjurnal;
        $data['po_no']            = $id;
        $data['total_po']        = $jumlah_total;
        $data['id_vendor']        = $idcust;
        $data['nama_vendor']    = $nama;
        $data['no_surat']        = $No_Inv;
        $this->load->view("v_detail_jurnal", $data);
    }

    public function save_jurnal_bum()
    {
        $post        = $this->input->post();
        $session     = $this->session->userdata('app_session');
        $data_session    = $this->session->userdata;
        $tgl_inv  = $this->input->post('tgl_jurnal[0]');
        $keterangan  = $this->input->post('keterangan[0]');
        $type        = $this->input->post('type[0]');
        $reff        = $this->input->post('reff[0]');
        $no_req      = $this->input->post('no_request[0]');
        $total       = round($this->input->post('total'));
        $jenis       = $this->input->post('jenis');
        $tipe_jurnal       = $this->input->post('tipe');
        $jenis_jurnal       = $this->input->post('jenis_jurnal');

        $total_po           = round($this->input->post('total_po'));
        $id_vendor          = $this->input->post('vendor_id');
        $nama_vendor        = $this->input->post('vendor_nm');
        $kd_bayar           = $this->input->post('po_no');


        $this->db->trans_begin();

        ## NOMOR JV ##
        $Nomor_BUM                = $this->Jurnal_model->get_Nomor_Jurnal_BUM('101', $tgl_inv);

        $dataJARH = array(
            'nomor'             => $Nomor_BUM,
            'kd_pembayaran'        => $reff,
            'tgl'                 => $tgl_inv,
            'jml'                => $total,
            'kdcab'                => '101',
            'jenis_reff'        => $reff,
            'no_reff'            => $reff,
            'customer'            => $nama_vendor,
            'terima_dari'        => '-',
            'jenis_ar'            => 'V',
            'note'                => $keterangan,
            'valid'                => $session['id_user'],
            'tgl_valid'            => $tgl_inv,
            'user_id'            => $session['id_user'],
            'tgl_invoice'        => $tgl_inv,
            'ho_valid'            => '',
            'batal'                => '0'
        );

        for ($i = 0; $i < count($this->input->post('type')); $i++) {
            $tipe = $this->input->post('type')[$i];
            $perkiraan = $this->input->post('no_coa')[$i];
            $noreff = $this->input->post('reff')[$i];
            $jenisjurnal = $this->input->post('jenisjurnal')[$i];

            $datadetail = array(
                'tipe'            => $this->input->post('type')[$i],
                'nomor'           => $Nomor_BUM,
                'tanggal'         => $this->input->post('tgl_jurnal')[$i],
                'no_perkiraan'    => $this->input->post('no_coa')[$i],
                'keterangan'      => $this->input->post('keterangan')[$i],
                'no_reff'           => $this->input->post('reff')[$i],
                'debet'            => round($this->input->post('debet')[$i]),
                'kredit'          => round($this->input->post('kredit')[$i])
            );

            $this->db->insert(DBACC . '.jurnal', $datadetail);

            $perkiraan = $this->input->post('no_coa')[$i];

            // print_r($perkiraan);
            // exit;


            if ($perkiraan == '1104-01-01') {

                $datapiutang = array(
                    'tipe'            => 'BUM',
                    'nomor'            => $Nomor_BUM,
                    'tanggal'        => $this->input->post('tgl_jurnal')[$i],
                    'no_perkiraan'   => '1104-01-01',
                    'keterangan'     => $this->input->post('keterangan')[$i],
                    'no_reff'        => $this->input->post('reff')[$i],
                    'debet'          => round($this->input->post('debet')[$i]),
                    'kredit'         => round($this->input->post('kredit')[$i]),
                    'id_supplier'     => $id_vendor,
                    'nama_supplier'   => $nama_vendor,

                );
                $this->db->insert('tr_kartu_piutang', $datapiutang);
            }
            $jurnal_posting     = "UPDATE jurnal SET stspos=1 WHERE tipe = '$tipe'
			AND  jenis_jurnal = '$jenisjurnal' AND no_reff  = '$noreff' ";
            $this->db->query($jurnal_posting);
        }

        ## INSERT JURNAL ##
        $this->db->insert(DBACC . '.jarh', $dataJARH);
        $Qry_Update_Cabang_acc     = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobum=nobum + 1 WHERE nocab='101'";
        $this->db->query($Qry_Update_Cabang_acc);

        $Qry  = "UPDATE tr_invoice_payment SET status_jurnal='1' WHERE kd_pembayaran='$kd_bayar'";
        $this->db->query($Qry);



        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $param = array(
                'save' => 0,
                'msg' => "GAGAL, simpan data..!!!",

            );
        } else {
            $this->db->trans_commit();

            $param = array(
                'save' => 1,
                'msg' => "SUKSES, simpan data..!!!",

            );
        }
        echo json_encode($param);
    }



    public function save_jurnal_revenue()
    {

        $post        = $this->input->post();
        $session = $this->session->userdata('app_session');
        $data_session    = $this->session->userdata;

        $tgl_inv  = $this->input->post('tgl_jurnal[0]');
        $keterangan  = $this->input->post('keterangan[0]');
        $type        = $this->input->post('type[0]');
        $reff        = $this->input->post('reff[0]');
        $no_req      = $this->input->post('no_request[0]');
        $total       = $this->input->post('total');
        $jenis       = $this->input->post('jenis');
        $tipe_jurnal       = $this->input->post('tipe');
        $jenis_jurnal       = $this->input->post('jenis_jurnal');

        $total_po           = round($this->input->post('total_po'));
        $id_vendor          = $this->input->post('vendor_id');
        $nama_vendor        = $this->input->post('vendor_nm');

        $this->db->trans_begin();

        $Nomor_JV                = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_inv);


        $Bln             = substr($tgl_inv, 5, 2);
        $Thn             = substr($tgl_inv, 0, 4);


        $dataJVhead = array(
            'nomor'             => $Nomor_JV,
            'tgl'                 => $tgl_inv,
            'jml'                => $total,
            'koreksi_no'        => '-',
            'kdcab'                => '101',
            'jenis'                => 'JV',
            'keterangan'         => $keterangan,
            'bulan'                => $Bln,
            'tahun'                => $Thn,
            'user_id'            => $this->auth->user_id(),
            'memo'                => '',
            'tgl_jvkoreksi'        => $tgl_inv,
            'ho_valid'            => ''
        );


        $this->db->insert(DBACC . '.javh', $dataJVhead);


        for ($i = 0; $i < count($this->input->post('type')); $i++) {
            $tipe = $this->input->post('type')[$i];
            $perkiraan = $this->input->post('no_coa')[$i];
            $noreff = $this->input->post('reff')[$i];
            $jenisjurnal = $this->input->post('jenisjurnal')[$i];

            $datadetail = array(
                'tipe'            => $this->input->post('type')[$i],
                'nomor'           => $Nomor_JV,
                'tanggal'         => $this->input->post('tgl_jurnal')[$i],
                'no_perkiraan'    => $this->input->post('no_coa')[$i],
                'keterangan'      => $this->input->post('keterangan')[$i],
                'no_reff'           => $this->input->post('reff')[$i],
                'debet'            => round($this->input->post('debet')[$i]),
                'kredit'          => round($this->input->post('kredit')[$i])
            );

            $this->db->insert(DBACC . '.jurnal', $datadetail);
        }


        $Qry_Update_Cabang_acc     = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
        $this->db->query($Qry_Update_Cabang_acc);
        $jurnal_inv     = "UPDATE tr_revenue SET status_jurnal='CLS' WHERE id = '$reff' ";
        $this->db->query($jurnal_inv);


        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $param = array(
                'save' => 0,
                'msg' => "GAGAL, simpan data..!!!",

            );
        } else {
            $this->db->trans_commit();

            $param = array(
                'save' => 1,
                'msg' => "SUKSES, simpan data..!!!",

            );
        }
        echo json_encode($param);
    }


    function jurnal_pembayaran()
    {
        //JURNAL PEMBAYARAN
        //TERJADI SAAT CREATE PEMBAYARAN HUTANG

        $id        = $this->uri->segment(4);
        $kodejurnal = $this->uri->segment(5);
        $ket = $this->uri->segment(6);
        $kd_bayar = $id;
        $no_request = $id;
        $data_bayar =  $this->db->query("SELECT * FROM tr_po_payment WHERE kd_pembayaran = '$kd_bayar' ")->row();


        $tgl_byr             = $data_bayar->tgl_pembayaran;
        $tgl_po             = $data_bayar->tgl_po;
        $no_po              = $data_bayar->no_po;
        $kd_bank             = $data_bayar->kd_bank;
        $hutangidr         = round($data_bayar->jumlah_hutang_idr);
        $bayaridr             = round($data_bayar->jumlah_pembayaran_idr);
        $selisih             = round($data_bayar->selisih);
        $idsupplier        = round($data_bayar->supplier);
        $nama                = round($data_bayar->nama_supplier);
        $kurs              = $data_bayar->kurs_bayar;


        // $id_cust =  $this->db->query("SELECT * FROM master_customers WHERE name_customer = '$nama'")->row();
        // $idcust  = $id_cust->id_customer;



        $No_Inv  = $kd_bayar;
        $Tgl_Inv = $tgl_byr;
        $Bln             = substr($Tgl_Inv, 6, 2);
        $Thn             = substr($Tgl_Inv, 0, 4);
        $bulan_bayar = date("n", strtotime($Tgl_Inv));
        $tahun_bayar = date("Y", strtotime($Tgl_Inv));
        $keterangan_byr  = $data_bayar->no_po;
        $jumlah_total    = round($data_bayar->jumlah_pembayaran_idr);

        $jenis_reff      = $kd_bayar;
        $no_reff         = $kd_bayar;

        $Keterangan_INV            = 'Pembayaran A/N ' . $nama . ' Kode Pembayaran. ' . $No_Inv . ',' . $keterangan_byr;


        $det_Jurnal                = array();

        if ($kurs == 1) {
            $det_Jurnal[]            = array(
                'nomor'         => '',
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUK',
                'no_perkiraan'  => '2101-01-01',
                'keterangan'    => $Keterangan_INV,
                'no_reff'       => $No_Inv,
                'debet'         => $hutangidr,
                'kredit'        => 0,
                'jenis_jurnal'  => $ket,
                'no_request'    => $no_request,

            );
        } else {
            $det_Jurnal[]            = array(
                'nomor'         => '',
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUK',
                'no_perkiraan'  => '2101-01-02',
                'keterangan'    => $Keterangan_INV,
                'no_reff'       => $No_Inv,
                'debet'         => $hutangidr,
                'kredit'        => 0,
                'jenis_jurnal'  => $ket,
                'no_request'    => $no_request,

            );
        }



        if ($selisih > 0) {
            $det_Jurnal[]            = array(
                'nomor'         => '',
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUK',
                'no_perkiraan'  => '7101-01-05',
                'keterangan'    => $Keterangan_INV,
                'no_reff'       => $No_Inv,
                'debet'         => 0,
                'kredit'        => $selisih,
                'jenis_jurnal'  => $ket,
                'no_request'    => $no_request,

            );
        }

        if ($selisih < 0) {
            $det_Jurnal[]            = array(
                'nomor'         => '',
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUK',
                'no_perkiraan'  => '7101-01-05',
                'keterangan'    => $Keterangan_INV,
                'no_reff'       => $No_Inv,
                'debet'         => $selisih * (-1),
                'kredit'        => 0,
                'jenis_jurnal'  => $ket,
                'no_request'    => $no_request,

            );
        }

        $det_Jurnal[]            = array(
            'nomor'         => '',
            'tanggal'       => $Tgl_Inv,
            'tipe'          => 'BUK',
            'no_perkiraan'  => $kd_bank,
            'keterangan'    => $Keterangan_INV,
            'no_reff'       => $No_Inv,
            'debet'         => 0,
            'kredit'        => $jumlah_total,
            'jenis_jurnal'  => $ket,
            'no_request'    => $no_request,

        );








        $this->db->where('no_reff', $id);
        $this->db->where('jenis_jurnal', $ket);
        $this->db->delete('jurnal');

        $this->db->insert_batch('jurnal', $det_Jurnal);


        $noreff     = $id;
        $tipe        = 'BUK';
        $jenisjurnal = $ket;

        //  print_r($noreff);
        //  exit;

        $data['list_data']         = $this->Jurnal_model->get_detail_jurnal($noreff, $tipe, $jenisjurnal);

        $data['data_perkiraan']    = $this->Acc_model->get_noperkiraan();
        $data['jenis']            = 'BUM';
        $data['akses']            = 'jurnal';
        $data['jenis_jurnal']    = $jenisjurnal;
        $data['po_no']            = $id;
        $data['total_po']        = $jumlah_total;
        $data['id_vendor']        = $idsupplier;
        $data['nama_vendor']    = $nama;
        $data['no_surat']        = $No_Inv;
        $this->load->view("v_detail_jurnal", $data);
    }

    public function save_jurnal_pembayaran()
    {



        $session = $this->session->userdata('app_session');
        $data_session    = $this->session->userdata;

        $tgl_po  = $this->input->post('tgl_jurnal[0]');
        $keterangan  = $this->input->post('keterangan[0]');
        $type        = $this->input->post('type[0]');
        $reff        = $this->input->post('reff[0]');
        $no_req      = $this->input->post('no_request[0]');
        $total       = round($this->input->post('total'));
        $jenis       = $this->input->post('jenis');
        $tipe_jurnal       = $this->input->post('tipe');
        $jenis_jurnal       = $this->input->post('jenis_jurnal');

        $total_po           = round($this->input->post('total_po'));
        $id_vendor          = $this->input->post('vendor_id');
        $nama_vendor        = $this->input->post('vendor_nm');




        // print_r($jenis);
        // print_r ($jenis_jurnal);
        // print_r ($reff);
        // exit;





        $this->db->trans_begin();

        $Nomor_JV                = $this->Jurnal_model->get_no_buk('101');


        $Bln             = substr($tgl_po, 5, 2);
        $Thn             = substr($tgl_po, 0, 4);
        // ## NOMOR JV ##
        // $Nomor_JV				= $this->Jurnal_model->get_no_buk('101');




        $dataJVhead = array(
            'nomor'             => $Nomor_JV,
            'tgl'                 => $tgl_po,
            'jml'                => $total,
            'kdcab'                => '101',
            'jenis_reff'        => 'BUK',
            'no_reff'             => $reff,
            'customer'             => $nama_vendor,
            'bayar_kepada'      => $nama_vendor,
            'jenis_ap'            => 'V',
            'note'                => $keterangan,
            'user_id'            => $session['username'],
            'ho_valid'            => '',
            'batal'                => '0'
        );
        $this->db->insert(DBACC . '.japh', $dataJVhead);



        for ($i = 0; $i < count($this->input->post('type')); $i++) {
            $tipe = $this->input->post('type')[$i];
            $perkiraan = $this->input->post('no_coa')[$i];
            $noreff = $this->input->post('reff')[$i];
            $jenisjurnal = $this->input->post('jenisjurnal')[$i];

            $datadetail = array(
                'tipe'        => $this->input->post('type')[$i],
                'nomor'       => $Nomor_JV,
                'tanggal'     => $this->input->post('tgl_jurnal')[$i],
                'no_perkiraan'    => $this->input->post('no_coa')[$i],
                'keterangan'      => $this->input->post('keterangan')[$i],
                'no_reff'           => $this->input->post('reff')[$i],
                'debet'            => round($this->input->post('debet')[$i]),
                'kredit'          => round($this->input->post('kredit')[$i])
            );
            $this->db->insert(DBACC . '.jurnal', $datadetail);

            $jurnal_posting     = "UPDATE jurnal SET stspos=1 WHERE tipe = '$tipe'
			AND  jenis_jurnal = '$jenisjurnal' AND no_reff  = '$noreff' ";
            $this->db->query($jurnal_posting);

            if ($perkiraan == '2101-01-01') {


                $datahutang = array(
                    'tipe'            => $type,
                    'nomor'            => $Nomor_JV,
                    'tanggal'        => $tgl_po,
                    'no_perkiraan'    => $perkiraan,
                    'keterangan'      => $keterangan,
                    'no_reff'           => $reff,
                    'debet'            => 0,
                    'kredit'          => $total_po,
                    'id_supplier'     => $id_vendor,
                    'nama_supplier'   => $nama_vendor,
                    'no_request'      => $no_req,

                );

                $this->db->insert('tr_kartu_hutang', $datahutang);
            } elseif ($perkiraan == '2101-01-02') {


                $datahutang = array(
                    'tipe'            => $type,
                    'nomor'            => $Nomor_JV,
                    'tanggal'        => $tgl_po,
                    'no_perkiraan'    => $perkiraan,
                    'keterangan'      => $keterangan,
                    'no_reff'           => $reff,
                    'debet'            => 0,
                    'kredit'          => $total_po,
                    'id_supplier'     => $id_vendor,
                    'nama_supplier'   => $nama_vendor,
                    'no_request'      => $no_req,

                );

                $this->db->insert('tr_kartu_hutang', $datahutang);
            }
        }


        $Qry_Update_Cabang_acc     = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobuk=nobuk + 1 WHERE nocab='101'";
        $this->db->query($Qry_Update_Cabang_acc);

        $jurnal_po     = "UPDATE tr_po_payment SET status_jurnal='1' WHERE kd_pembayaran = '$reff' ";
        $this->db->query($jurnal_po);





        // print_r($perkiraan);
        // exit;






        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $param = array(
                'save' => 0,
                'msg' => "GAGAL, simpan data..!!!",

            );
        } else {
            $this->db->trans_commit();

            $param = array(
                'save' => 1,
                'msg' => "SUKSES, simpan data..!!!",

            );
        }
        echo json_encode($param);
    }



    function jurnal_hutang()
    {
        //JURNAL PENGAKUAN PIUTANG
        //TERJADI SAAT CREATE INVOICE

        $id        = $this->uri->segment(4);
        $kodejurnal = $this->uri->segment(5);
        $ket = $this->uri->segment(6);

        // print_r($kodejurnal);
        // exit;

        $hutang      = $this->db->query("SELECT a.*, a.id_suplier, b.nama FROM tr_incoming a         
        INNER JOIN new_supplier b ON a.id_suplier = b.kode_Supplier WHERE id_data='$id'")->row();

        $idsupplier   = $hutang->id_suplier;
        $nama   = $hutang->nama;
        $nomor  = $hutang->id_incoming;


        $Tgl_Invoice = date('Y-m-d');
        $no_request = $id;
        $tgl_voucher = $Tgl_Invoice;

        $Keterangan_INV            = 'Hutang ' . ($nama) . ' No ' . ($nomor);

        #AMBIL TEMPLATE JURNAL DAN SIMPAN KE TEMPORARY

        $datajurnal       = $this->Acc_model->GetTemplateJurnal($kodejurnal);
        foreach ($datajurnal as $record) {
            $nokir  = $record->no_perkiraan;
            $tabel  = $record->menu;
            $posisi = $record->posisi;
            $field  = $record->field;
            // if ($field == 'jumlah_bank'){
            // 	$nokir = $kd_bank;
            // } else{
            // 	$nokir  = $record->no_perkiraan;
            // }
            $no_voucher = $id;
            $param  = 'id_data';
            $value_param  = $id;
            $val = $this->Acc_model->GetData($tabel, $field, $param, $value_param);
            $nilaibayar = $val[0]->$field;

            if ($posisi == 'D') {
                $det_Jurnaltes[]  = array(
                    'nomor'         => '',
                    'tanggal'       => $tgl_voucher,
                    'tipe'          => 'JV',
                    'no_perkiraan'  => $nokir,
                    'keterangan'    => $Keterangan_INV,
                    'no_reff'       => $id,
                    'debet'         => $nilaibayar,
                    'kredit'        => 0,
                    'jenis_jurnal'  => $ket,
                    'no_request'    => $no_request
                );
            } elseif ($posisi == 'K') {
                $det_Jurnaltes[]  = array(
                    'nomor'         => '',
                    'tanggal'       => $tgl_voucher,
                    'tipe'          => 'JV',
                    'no_perkiraan'  => $nokir,
                    'keterangan'    => $Keterangan_INV,
                    'no_reff'       => $id,
                    'debet'         => 0,
                    'kredit'        => $nilaibayar,
                    'jenis_jurnal'  => $ket,
                    'no_request'    => $no_request
                );
            }
        }

        $this->db->where('no_reff', $id);
        $this->db->where('jenis_jurnal', $ket);
        $this->db->delete('jurnal');

        $this->db->insert_batch('jurnal', $det_Jurnaltes);


        $noreff     = $id;
        $tipe        = 'jv';
        $jenisjurnal = $ket;
        $data['list_data']         = $this->Jurnal_model->get_detail_jurnal($noreff, $tipe, $jenisjurnal);
        $data['data_perkiraan']    = $this->Acc_model->get_noperkiraan();
        $data['jenis']            = 'JV';
        $data['akses']            = 'jurnal';
        $data['jenis_jurnal']    = $jenisjurnal;
        $data['po_no']            = $id;
        $data['total_po']        = $nilaibayar;
        $data['id_vendor']        = $idsupplier;
        $data['nama_vendor']    = $nama;
        $data['no_surat']        = $nomor;
        $this->load->view("v_detail_jurnal", $data);
    }

    public function save_jurnal_hutang()
    {

        $post        = $this->input->post();
        $session = $this->session->userdata('app_session');
        $data_session    = $this->session->userdata;

        $tgl_inv  = $this->input->post('tgl_jurnal[0]');
        $keterangan  = $this->input->post('keterangan[0]');
        $type        = $this->input->post('type[0]');
        $reff        = $this->input->post('reff[0]');
        $no_req      = $this->input->post('no_request[0]');
        $total       = round($this->input->post('total'));
        $jenis       = $this->input->post('jenis');
        $tipe_jurnal       = $this->input->post('tipe');
        $jenis_jurnal       = $this->input->post('jenis_jurnal');

        $total_po           = round($this->input->post('total_po'));
        $id_vendor          = $this->input->post('vendor_id');
        $nama_vendor        = $this->input->post('vendor_nm');

        $this->db->trans_begin();

        $Nomor_JV                = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_inv);


        $Bln             = substr($tgl_inv, 5, 2);
        $Thn             = substr($tgl_inv, 0, 4);


        $dataJVhead = array(
            'nomor'             => $Nomor_JV,
            'tgl'                 => $tgl_inv,
            'jml'                => $total,
            'koreksi_no'        => '-',
            'kdcab'                => '101',
            'jenis'                => 'JV',
            'keterangan'         => $keterangan,
            'bulan'                => $Bln,
            'tahun'                => $Thn,
            'user_id'            => $this->auth->user_id(),
            'memo'                => '',
            'tgl_jvkoreksi'        => $tgl_inv,
            'ho_valid'            => ''
        );


        $this->db->insert(DBACC . '.javh', $dataJVhead);



        for ($i = 0; $i < count($this->input->post('type')); $i++) {
            $tipe = $this->input->post('type')[$i];
            $perkiraan = $this->input->post('no_coa')[$i];
            $noreff = $this->input->post('reff')[$i];
            $jenisjurnal = $this->input->post('jenisjurnal')[$i];

            $datadetail = array(
                'tipe'            => $this->input->post('type')[$i],
                'nomor'           => $Nomor_JV,
                'tanggal'         => $this->input->post('tgl_jurnal')[$i],
                'no_perkiraan'    => $this->input->post('no_coa')[$i],
                'keterangan'      => $this->input->post('keterangan')[$i],
                'no_reff'           => $this->input->post('reff')[$i],
                'debet'            => round($this->input->post('debet')[$i]),
                'kredit'          => round($this->input->post('kredit')[$i])
            );
            $this->db->insert(DBACC . '.jurnal', $datadetail);

            $jurnal_posting     = "UPDATE jurnal SET stspos=1 WHERE tipe = '$tipe'
			AND  jenis_jurnal = '$jenisjurnal' AND no_reff  = '$noreff' ";
            $this->db->query($jurnal_posting);
        }

        $Qry_Update_Cabang_acc     = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
        $this->db->query($Qry_Update_Cabang_acc);

        $jurnal_inv     = "UPDATE tr_incoming SET status_jurnal='CLS' WHERE id_data = '$reff' ";
        $this->db->query($jurnal_inv);


        // $hutang      = $this->db->query("SELECT a.*, b.nama_supplier FROM tr_incoming a         
        // INNER JOIN master_supplier b ON a.id_suplier = b.id_suplier WHERE id_data='$reff'")->row();

        // $id_cust   = $invoicing->id_customer;
        // $nama   = $invoicing->name_customer;
        // $No_Inv  = $invoicing->no_surat;



        if ($perkiraan == '2101-01-01') {


            $datahutang = array(
                'tipe'            => $type,
                'nomor'            => $Nomor_JV,
                'tanggal'        => $tgl_inv,
                'no_perkiraan'    => $perkiraan,
                'keterangan'      => $keterangan,
                'no_reff'           => $reff,
                'debet'            => $total,
                'kredit'          => 0,
                'id_supplier'     => $id_vendor,
                'nama_supplier'   => $nama_vendor,
                'no_request'      => $no_req,

            );

            $this->db->insert('tr_kartu_hutang', $datahutang);
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $param = array(
                'save' => 0,
                'msg' => "GAGAL, simpan data..!!!",

            );
        } else {
            $this->db->trans_commit();

            $param = array(
                'save' => 1,
                'msg' => "SUKSES, simpan data..!!!",

            );
        }
        echo json_encode($param);
    }


    function jurnal_delivery()
    {
        //JURNAL PENGAKUAN PIUTANG
        //TERJADI SAAT CREATE INVOICE

        $id        = $this->uri->segment(4);
        $kodejurnal = $this->uri->segment(5);
        $ket = $this->uri->segment(6);

        $invoicing      = $this->db->query("SELECT a.*, b.name_customer FROM tr_delivery_order a         
        INNER JOIN master_customers b ON a.id_customer = b.id_customer WHERE no_do='$id'")->row();


        $idcust   = $invoicing->id_customer;
        $nama   = $invoicing->name_customer;
        $nomor  = $invoicing->no_surat;


        $Tgl_Invoice = date('Y-m-d');
        $no_request = $id;
        $tgl_voucher = $Tgl_Invoice;

        $Keterangan_INV            = 'Delivery Order ' . ($nama) . ' No ' . ($nomor);

        #AMBIL TEMPLATE JURNAL DAN SIMPAN KE TEMPORARY

        $datajurnal       = $this->Acc_model->GetTemplateJurnal($kodejurnal);
        foreach ($datajurnal as $record) {
            $nokir  = $record->no_perkiraan;
            $tabel  = $record->menu;
            $posisi = $record->posisi;
            $field  = $record->field;
            // if ($field == 'jumlah_bank'){
            // 	$nokir = $kd_bank;
            // } else{
            // 	$nokir  = $record->no_perkiraan;
            // }
            $no_voucher = $id;
            $param  = 'no_do';
            $value_param  = $id;
            $val = $this->Acc_model->GetData($tabel, $field, $param, $value_param);
            $nilaibayar = round($val[0]->$field);



            if ($posisi == 'D') {
                $det_Jurnaltes[]  = array(
                    'nomor'         => '',
                    'tanggal'       => $tgl_voucher,
                    'tipe'          => 'JV',
                    'no_perkiraan'  => $nokir,
                    'keterangan'    => $Keterangan_INV,
                    'no_reff'       => $id,
                    'debet'         => $nilaibayar,
                    'kredit'        => 0,
                    'jenis_jurnal'  => $ket,
                    'no_request'    => $no_request
                );
            } elseif ($posisi == 'K') {
                $det_Jurnaltes[]  = array(
                    'nomor'         => '',
                    'tanggal'       => $tgl_voucher,
                    'tipe'          => 'JV',
                    'no_perkiraan'  => $nokir,
                    'keterangan'    => $Keterangan_INV,
                    'no_reff'       => $id,
                    'debet'         => 0,
                    'kredit'        => $nilaibayar,
                    'jenis_jurnal'  => $ket,
                    'no_request'    => $no_request
                );
            }
        }

        $this->db->where('no_reff', $id);
        $this->db->where('jenis_jurnal', $ket);
        $this->db->delete('jurnal');



        $this->db->insert_batch('jurnal', $det_Jurnaltes);


        $noreff     = $id;
        $tipe        = 'jv';
        $jenisjurnal = $ket;
        $data['list_data']         = $this->Jurnal_model->get_detail_jurnal($noreff, $tipe, $jenisjurnal);
        $data['data_perkiraan']    = $this->Acc_model->get_noperkiraan();
        $data['jenis']            = 'JV';
        $data['akses']            = 'jurnal';
        $data['jenis_jurnal']    = $jenisjurnal;
        $data['po_no']            = $id;
        $data['total_po']        = $nilaibayar;
        $data['id_vendor']        = $idcust;
        $data['nama_vendor']    = $nama;
        $data['no_surat']        = $nomor;
        $this->load->view("v_detail_jurnal", $data);
    }

    public function save_jurnal_delivery()
    {

        $post        = $this->input->post();
        $session = $this->session->userdata('app_session');
        $data_session    = $this->session->userdata;

        $tgl_inv  = $this->input->post('tgl_jurnal[0]');
        $keterangan  = $this->input->post('keterangan[0]');
        $type        = $this->input->post('type[0]');
        $reff        = $this->input->post('reff[0]');
        $no_req      = $this->input->post('no_request[0]');
        $total       = $this->input->post('total');
        $jenis       = $this->input->post('jenis');
        $tipe_jurnal       = $this->input->post('tipe');
        $jenis_jurnal       = $this->input->post('jenis_jurnal');

        $total_po           = round($this->input->post('total_po'));
        $id_vendor          = $this->input->post('vendor_id');
        $nama_vendor        = $this->input->post('vendor_nm');

        $this->db->trans_begin();

        $Nomor_JV                = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_inv);


        $Bln             = substr($tgl_inv, 5, 2);
        $Thn             = substr($tgl_inv, 0, 4);


        $dataJVhead = array(
            'nomor'             => $Nomor_JV,
            'tgl'                 => $tgl_inv,
            'jml'                => $total,
            'koreksi_no'        => '-',
            'kdcab'                => '101',
            'jenis'                => 'JV',
            'keterangan'         => $keterangan,
            'bulan'                => $Bln,
            'tahun'                => $Thn,
            'user_id'            => $this->auth->user_id(),
            'memo'                => '',
            'tgl_jvkoreksi'        => $tgl_inv,
            'ho_valid'            => ''
        );


        $this->db->insert(DBACC . '.javh', $dataJVhead);


        for ($i = 0; $i < count($this->input->post('type')); $i++) {
            $tipe = $this->input->post('type')[$i];
            $perkiraan = $this->input->post('no_coa')[$i];
            $noreff = $this->input->post('reff')[$i];
            $jenisjurnal = $this->input->post('jenisjurnal')[$i];

            $datadetail = array(
                'tipe'            => $this->input->post('type')[$i],
                'nomor'           => $Nomor_JV,
                'tanggal'         => $this->input->post('tgl_jurnal')[$i],
                'no_perkiraan'    => $this->input->post('no_coa')[$i],
                'keterangan'      => $this->input->post('keterangan')[$i],
                'no_reff'           => $this->input->post('reff')[$i],
                'debet'            => round($this->input->post('debet')[$i]),
                'kredit'          => round($this->input->post('kredit')[$i])
            );

            $this->db->insert(DBACC . '.jurnal', $datadetail);
        }


        $Qry_Update_Cabang_acc     = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
        $this->db->query($Qry_Update_Cabang_acc);
        $jurnal_inv     = "UPDATE tr_delivery_order SET status_jurnal_do='1' WHERE no_do = '$reff' ";
        $this->db->query($jurnal_inv);


        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $param = array(
                'save' => 0,
                'msg' => "GAGAL, simpan data..!!!",

            );
        } else {
            $this->db->trans_commit();

            $param = array(
                'save' => 1,
                'msg' => "SUKSES, simpan data..!!!",

            );
        }
        echo json_encode($param);
    }

    function jurnal_confirmdelivery()
    {
        //JURNAL PENGAKUAN PIUTANG
        //TERJADI SAAT CREATE INVOICE

        $id        = $this->uri->segment(4);
        $kodejurnal = $this->uri->segment(5);
        $ket = $this->uri->segment(6);

        $invoicing      = $this->db->query("SELECT a.*, b.name_customer FROM tr_delivery_order a         
        INNER JOIN master_customers b ON a.id_customer = b.id_customer WHERE no_do='$id'")->row();


        $idcust   = $invoicing->id_customer;
        $nama   = $invoicing->name_customer;
        $nomor  = $invoicing->no_surat;


        $Tgl_Invoice = date('Y-m-d');
        $no_request = $id;
        $tgl_voucher = $Tgl_Invoice;

        $Keterangan_INV            = 'Delivery Order Confirm ' . ($nama) . ' No ' . ($nomor);

        #AMBIL TEMPLATE JURNAL DAN SIMPAN KE TEMPORARY

        $datajurnal       = $this->Acc_model->GetTemplateJurnal($kodejurnal);
        foreach ($datajurnal as $record) {
            $nokir  = $record->no_perkiraan;
            $tabel  = $record->menu;
            $posisi = $record->posisi;
            $field  = $record->field;
            // if ($field == 'jumlah_bank'){
            // 	$nokir = $kd_bank;
            // } else{
            // 	$nokir  = $record->no_perkiraan;
            // }
            $no_voucher = $id;
            $param  = 'no_do';
            $value_param  = $id;
            $val = $this->Acc_model->GetData($tabel, $field, $param, $value_param);
            $nilaibayar = round($val[0]->$field);



            if ($posisi == 'D') {
                $det_Jurnaltes[]  = array(
                    'nomor'         => '',
                    'tanggal'       => $tgl_voucher,
                    'tipe'          => 'JV',
                    'no_perkiraan'  => $nokir,
                    'keterangan'    => $Keterangan_INV,
                    'no_reff'       => $id,
                    'debet'         => $nilaibayar,
                    'kredit'        => 0,
                    'jenis_jurnal'  => $ket,
                    'no_request'    => $no_request
                );
            } elseif ($posisi == 'K') {
                $det_Jurnaltes[]  = array(
                    'nomor'         => '',
                    'tanggal'       => $tgl_voucher,
                    'tipe'          => 'JV',
                    'no_perkiraan'  => $nokir,
                    'keterangan'    => $Keterangan_INV,
                    'no_reff'       => $id,
                    'debet'         => 0,
                    'kredit'        => $nilaibayar,
                    'jenis_jurnal'  => $ket,
                    'no_request'    => $no_request
                );
            }
        }

        $this->db->where('no_reff', $id);
        $this->db->where('jenis_jurnal', $ket);
        $this->db->delete('jurnal');



        $this->db->insert_batch('jurnal', $det_Jurnaltes);


        $noreff     = $id;
        $tipe        = 'jv';
        $jenisjurnal = $ket;
        $data['list_data']         = $this->Jurnal_model->get_detail_jurnal($noreff, $tipe, $jenisjurnal);
        $data['data_perkiraan']    = $this->Acc_model->get_noperkiraan();
        $data['jenis']            = 'JV';
        $data['akses']            = 'jurnal';
        $data['jenis_jurnal']    = $jenisjurnal;
        $data['po_no']            = $id;
        $data['total_po']        = $nilaibayar;
        $data['id_vendor']        = $idcust;
        $data['nama_vendor']    = $nama;
        $data['no_surat']        = $nomor;
        $this->load->view("v_detail_jurnal", $data);
    }

    public function save_jurnal_confirmdelivery()
    {

        $post        = $this->input->post();
        $session = $this->session->userdata('app_session');
        $data_session    = $this->session->userdata;

        $tgl_inv  = $this->input->post('tgl_jurnal[0]');
        $keterangan  = $this->input->post('keterangan[0]');
        $type        = $this->input->post('type[0]');
        $reff        = $this->input->post('reff[0]');
        $no_req      = $this->input->post('no_request[0]');
        $total       = $this->input->post('total');
        $jenis       = $this->input->post('jenis');
        $tipe_jurnal       = $this->input->post('tipe');
        $jenis_jurnal       = $this->input->post('jenis_jurnal');

        $total_po           = round($this->input->post('total_po'));
        $id_vendor          = $this->input->post('vendor_id');
        $nama_vendor        = $this->input->post('vendor_nm');

        $this->db->trans_begin();

        $Nomor_JV                = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_inv);


        $Bln             = substr($tgl_inv, 5, 2);
        $Thn             = substr($tgl_inv, 0, 4);


        $dataJVhead = array(
            'nomor'             => $Nomor_JV,
            'tgl'                 => $tgl_inv,
            'jml'                => $total,
            'koreksi_no'        => '-',
            'kdcab'                => '101',
            'jenis'                => 'JV',
            'keterangan'         => $keterangan,
            'bulan'                => $Bln,
            'tahun'                => $Thn,
            'user_id'            => $this->auth->user_id(),
            'memo'                => '',
            'tgl_jvkoreksi'        => $tgl_inv,
            'ho_valid'            => ''
        );


        $this->db->insert(DBACC . '.javh', $dataJVhead);


        for ($i = 0; $i < count($this->input->post('type')); $i++) {
            $tipe = $this->input->post('type')[$i];
            $perkiraan = $this->input->post('no_coa')[$i];
            $noreff = $this->input->post('reff')[$i];
            $jenisjurnal = $this->input->post('jenisjurnal')[$i];

            $datadetail = array(
                'tipe'            => $this->input->post('type')[$i],
                'nomor'           => $Nomor_JV,
                'tanggal'         => $this->input->post('tgl_jurnal')[$i],
                'no_perkiraan'    => $this->input->post('no_coa')[$i],
                'keterangan'      => $this->input->post('keterangan')[$i],
                'no_reff'           => $this->input->post('reff')[$i],
                'debet'            => round($this->input->post('debet')[$i]),
                'kredit'          => round($this->input->post('kredit')[$i])
            );

            $this->db->insert(DBACC . '.jurnal', $datadetail);
        }


        $Qry_Update_Cabang_acc     = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
        $this->db->query($Qry_Update_Cabang_acc);
        $jurnal_inv     = "UPDATE tr_delivery_order SET status_jurnal_confirm='1' WHERE no_do = '$reff' ";
        $this->db->query($jurnal_inv);


        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $param = array(
                'save' => 0,
                'msg' => "GAGAL, simpan data..!!!",

            );
        } else {
            $this->db->trans_commit();

            $param = array(
                'save' => 1,
                'msg' => "SUKSES, simpan data..!!!",

            );
        }
        echo json_encode($param);
    }

    function jurnal_penerimaan_np()
    {
        //JURNAL PENERIMAAN
        //TERJADI SAAT CREATE PENERIMAAN

        $id        = $this->uri->segment(4);
        $kodejurnal = $this->uri->segment(5);
        $ket = $this->uri->segment(6);
        $kd_bayar = $id;
        // print_r($kd_bayar);
        // exit;

        $no_request = $id;
        $data_bayar =  $this->db->query("SELECT * FROM tr_invoice_np_payment WHERE kd_pembayaran = '$kd_bayar' ")->row();

        $tgl_byr             = $data_bayar->tgl_pembayaran;
        $kd_invoice        = $data_bayar->no_invoice;
        $kd_bank             = $data_bayar->kd_bank;
        $jenis_pph         = $data_bayar->jenis_pph;
        $nama                = addslashes($data_bayar->nm_customer);
        // print_r($nama);
        // exit;
        $jmlpph            = $data_bayar->biaya_pph_idr;

        $id_cust =  $this->db->query("SELECT * FROM master_customers WHERE name_customer LIKE '%$nama%'")->row();

        if (!empty($id_cust)) {
            $idcust  = $id_cust->id_customer;
        } else {
            $idcust  = '-';
        }



        $No_Inv  = $kd_bayar;
        $Tgl_Inv = $tgl_byr;
        $Bln             = substr($Tgl_Inv, 6, 2);
        $Thn             = substr($Tgl_Inv, 0, 4);
        $bulan_bayar = date("n", strtotime($Tgl_Inv));
        $tahun_bayar = date("Y", strtotime($Tgl_Inv));
        $keterangan_byr  = $data_bayar->keterangan;
        $jumlah_total    = round($data_bayar->jumlah_pembayaran_idr);
        $jumlah_terima   = round($data_bayar->jumlah_bank_idr);
        $biaya_admin     = round($data_bayar->biaya_admin_idr);
        $biaya_lain     = round($data_bayar->biaya_pph_idr);
        $deposit         = round($data_bayar->tambah_lebih_bayar);


        $jenis_reff      = $kd_bayar;
        $no_reff         = $kd_bayar;

        $Keterangan_INV            = 'Penerimaan A/N ' . $nama . ' Kode Pembayaran. ' . $No_Inv . ',' . $keterangan_byr;


        $det_Jurnal                = array();
        $det_Jurnal[]            = array(
            'nomor'         => '',
            'tanggal'       => $Tgl_Inv,
            'tipe'          => 'BUM',
            'no_perkiraan'  => $kd_bank,
            'keterangan'    => $Keterangan_INV,
            'no_reff'       => $No_Inv,
            'debet'         => $jumlah_terima,
            'kredit'        => 0,
            'jenis_jurnal'  => $ket,
            'no_request'    => $no_request,

        );

        if ($biaya_admin != 0) {
            $det_Jurnal[]            = array(
                'nomor'         => '',
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUM',
                'no_perkiraan'  => '7201-01-02',
                'keterangan'    => $Keterangan_INV,
                'no_reff'       => $No_Inv,
                'debet'         => 0,
                'kredit'        => $biaya_admin,
                'jenis_jurnal'  => $ket,
                'no_request'    => $no_request,

            );
        }
        $kd_deposit = '2101-07-01';
        if ($deposit != 0) {
            $det_Jurnal[]            = array(
                'nomor'         => '',
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUM',
                'no_perkiraan'  => '2101-08-01',
                'keterangan'    => $Keterangan_INV,
                'no_reff'       => $No_Inv,
                'debet'         => 0,
                'kredit'        => $deposit,
                'jenis_jurnal'  => $ket,
                'no_request'    => $no_request,

            );
        }



        $data_jurnal = $this->db->query("SELECT * FROM tr_invoice_np_payment_detail WHERE kd_pembayaran = '$kd_bayar' ")->result();


        foreach ($data_jurnal as $jr) {
            $jmlbayar   = $jr->total_bayar_idr;
            $invoice2    = $jr->no_invoice;

            $surat  =  $this->db->query("SELECT * FROM tr_invoice_np_header WHERE id_invoice = '$invoice2'")->row();

            $Keterangan_            = 'INVOICE A/N ' . $nama . ' INV NO. ' . $invoice2 . ', ' . $surat->no_invoice;


            $det_Jurnal[]              = array(
                'nomor'         => '',
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUM',
                'no_perkiraan'  => '1104-01-01',
                'keterangan'    => $Keterangan_,
                'no_reff'       => $No_Inv,
                'debet'         => 0,
                'kredit'        => $jmlbayar,
                'jenis_jurnal'  => $ket,
                'no_request'    => $no_request,
            );
        }


        $this->db->where('no_reff', $id);
        $this->db->where('jenis_jurnal', $ket);
        $this->db->delete('jurnal');

        $this->db->insert_batch('jurnal', $det_Jurnal);


        $noreff     = $id;
        $tipe        = 'BUM';
        $jenisjurnal = $ket;

        //  print_r($noreff);
        //  exit;

        $data['list_data']         = $this->Jurnal_model->get_detail_jurnal($noreff, $tipe, $jenisjurnal);

        $data['data_perkiraan']    = $this->Acc_model->get_noperkiraan();
        $data['jenis']            = 'BUM';
        $data['akses']            = 'jurnal';
        $data['jenis_jurnal']    = $jenisjurnal;
        $data['po_no']            = $id;
        $data['total_po']        = $jumlah_total;
        $data['id_vendor']        = $idcust;
        $data['nama_vendor']    = $nama;
        $data['no_surat']        = $No_Inv;
        $this->load->view("v_detail_jurnal", $data);
    }
}

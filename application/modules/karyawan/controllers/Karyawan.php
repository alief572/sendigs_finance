<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunaz
 * @copyright Copyright (c) 2018, Yunaz
 *
 * This is controller for Karyawan
 */

class Karyawan extends Admin_Controller {

    //Permission
    protected $viewPermission   = "Karyawan.View";
    protected $addPermission    = "Karyawan.Add";
    protected $managePermission = "Karyawan.Manage";
    protected $deletePermission = "Karyawan.Delete";

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload','Image_lib','PHPExcel'));
        $this->load->model(array('Karyawan/Karyawan_model',
                                 'Karyawan/Divisi_model',
                                 'Aktifitas/aktifitas_model'
                                ));

        $this->template->title('Manage Data Karyawan');
        $this->template->page_icon('fa fa-table');

        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $session            = $this->session->userdata('app_session');
        $data = $this->Karyawan_model->select("karyawan.divisi,
                                            karyawan.id_karyawan,
                                            karyawan.nik,
                                            karyawan.nama_karyawan,
                                            karyawan.tempatlahir,
                                            karyawan.tanggallahir,
                                            karyawan.jeniskelamin,
                                            karyawan.alamataktif,
                                            karyawan.sts_aktif,
                                            karyawan.sts_karyawan,
                                            divisi.nm_divisi")
                                            ->join("divisi","karyawan.divisi = divisi.id_divisi")->where('karyawan.deleted',0)
                                            ->order_by('nama_karyawan','ASC')->find_all_by(array('kdcab'=>$session['kdcab']));
        $this->template->set('results', $data);
        $this->template->title('Karyawan');
        $this->template->render('list');
    }

    //Create New Customer
    public function create()
    {
        $this->auth->restrict($this->addPermission);
        $datdiv     = $this->Divisi_model->pilih_div()->result();
        $this->template->set('datdiv',$datdiv);

        $this->template->title('Input Master Karyawan');
        $this->template->render('karyawan_form');
    }

    //Edit Karyawan
    public function edit()
    {
        $this->auth->restrict($this->managePermission);

        $id = $this->uri->segment(3);
        $data  = $this->Karyawan_model->find_by(array('id_karyawan' => $id));
        if(!$data)
        {
            $this->template->set_message("Invalid ID", 'error');
            redirect('Karyawan');
        }

        $datdiv     = $this->Divisi_model->pilih_div()->result();
        //$datdiv = $this->Karyawan_model->get_div($id);

        $this->template->set('data',$data);
        $this->template->set('datdiv',$datdiv);
        $this->template->title('Edit Data Karyawan');
        $this->template->render('karyawan_form');
    }

    function add_divisi()
    {
        $id_divisi   = $this->input->post("id_divisix");
        $nm_divisi   = strtoupper($this->input->post("nm_divisi"));

        if($id_divisi!=""){

            $this->auth->restrict($this->addPermission);

            $data = array(
                            array(
                                'id_divisi'=> $id_divisi,
                                'nm_divisi'=> $nm_divisi,
                            )
                        );
            //Add Data
            $id = $this->Divisi_model->update_batch($data,'id_divisi');

            if(is_numeric($id))
            {
                $keterangan     = "SUKSES, Edit data Divisi atas Nama : ".$nm_divisi;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result = TRUE;
            }
            else
            {
                $keterangan     = "GAGAL, Edit data Divisi atas Nama : ".$nm_divisi;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }

        }else{

            $this->auth->restrict($this->addPermission);

            $data = array(
                        'nm_divisi'=> $nm_divisi,
                    );

            //Add Data
            $id = $this->Divisi_model->insert($data);

            if(is_numeric($id))
            {
                $keterangan     = "SUKSES, tambah data Divisi atas Nama : ".$nm_divisi;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result = TRUE;
            }
            else
            {
                $keterangan     = "GAGAL, tambah data Divisi atas Nama : ".$nm_divisi;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
        }
        //Save Log
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        $param = array(
            'save' => $result
        );

        echo json_encode($param);
    }

    function get_div(){
        $id_divisi = $_GET['id_divisi'];
        $dadiv = $this->Divisi_model->get_div($id_divisi);
        $param = array(
                'nm_divisi' => $dadiv
                );
        echo json_encode($param);
    }

    function ListDV(){
        echo "<div class='box-body'><B>Divisi</B><table id='lis_dv' class='table table-bordered table-striped'>
        <thead>
        <tr>
            <th width='50'>#</th>
            <th>Nama Divisi</th>
            <th width='25'>Action</th>
        </tr>
        </thead>";
        $no=1;
        $data=  $this->Divisi_model->tampil_dv()->result();
        foreach ($data as $d){
            echo "<tr id='dataku$d->id_divisi'>
                <td>$no</td>
                <td>$d->nm_divisi</td>
                <td>
                <a class='text-black' href='javascript:void(0)' title='Edit' onclick=\"edit_dv('".$d->id_divisi."');\"><i class='fa fa-pencil'></i>
                </td>
                </tr>";
            $no++;
        }
        echo "<tfoot>
        <tr>
        </tr>
        </tfoot>";
        echo"</table></div>";
    }

    function edit_dv(){
        $id_dv = $this->input->post('id');
        if(!empty($id_dv)){
            $detail  = $this->Divisi_model->find($id_dv);
        }
        echo json_encode($detail);
    }

    function get_divisi(){
        $rdiv = $this->Divisi_model->pilih_div()->result();
        //echo $result;
        echo "<select id='id_divisi' name='id_divisi' class='form-control pil_divisi select2-hidden-accessible'>";
        echo "<option value=''></option>";
                foreach ($rdiv as $key => $st) :
                    echo "<option value='$st->id_divisi' set_select('id_divisi', $st->id_divisi, isset($data->divisi) && $data->divisi == $st->id_divisi)>$st->nm_divisi
                    </option>";
                endforeach;
        echo "</select>";
    }

    function get_kota(){
        $provinsi = $_GET['provinsi'];
        $datkota = $this->Customer_model->pilih_kota($provinsi)->result();
        //echo $result;
        echo "<select id='kota' name='kota' class='form-control pil_kota select2-hidden-accessible'>";
        echo "<option value=''></option>";
                foreach ($datkota as $key => $st) :
                    echo "<option value='$st->id_kab' set_select('model', $st->id_kab, isset($data->id_kab) && $data->id_kab == $st->id_kab)>$st->nama
                    </option>";
                endforeach;
        echo "</select>";
    }


    public function save_biodata_ajax(){

        $type           = $this->input->post("type");
        $id_karyawan    = $this->input->post("id_karyawan");
        $nama_karyawan  = strtoupper($this->input->post("nama_karyawan"));
        $nik            = $this->input->post("nik");
        $tempatlahir    = $this->input->post("tempatlahir");
        $tanggallahir   = date("Y-m-d", strtotime($this->input->post('tanggallahir')));
        $tgl_join       = date("Y-m-d", strtotime($this->input->post('tgl_join')));
        $tgl_end        = date("Y-m-d", strtotime($this->input->post('tgl_end')));
        $sts_karyawan   = $this->input->post('sts_karyawan');
        $norekening     = $this->input->post('norekening');
        $id_divisi      = $this->input->post('id_divisi');
        $jeniskelamin   = $this->input->post("kelamin");
        $agama          = strtoupper($this->input->post("agama"));
        $noktp          = $this->input->post("noktp");
        $npwp           = $this->input->post('npwp');
        $levelpendidikan= $this->input->post('pendidikan');
        $tanggalmasuk   = date("Y-m-d", strtotime($this->input->post('tglgabung')));
        $nohp           = $this->input->post('nohp');
        $email          = strtoupper($this->input->post('email'));
        $alamataktif    = strtoupper($this->input->post('alamat'));
        $sts_aktif   = $this->input->post('sts_aktif');


        if($type=="edit")
        {
            $this->auth->restrict($this->managePermission);

            if($id_karyawan!="")
            {
                $data = array(
                            array(
                                'id_karyawan'=>$id_karyawan,
                                'nik'=> $nik,
                                'nama_karyawan'=> $nama_karyawan,
                                'tempatlahir'=> $tempatlahir,
                                'tanggallahir'=> $tanggallahir,
                                'divisi'=> $id_divisi,
                                'jeniskelamin'=> $jeniskelamin,
                                'agama'=> $agama,
                                'levelpendidikan'=> $levelpendidikan,
                                'alamataktif'=> $alamataktif,
                                'nohp'=> $nohp,
                                'noktp'=> $noktp,
                                'npwp'=> $npwp,
                                'email'=> $email,
                                'sts_aktif'=> $sts_aktif,
                                'tgl_join'=>$tgl_join,
                                'tgl_end'=>$tgl_end,
                                'sts_karyawan'=>$sts_karyawan,
                                'norekening'=>$norekening,
                            )
                        );

                //Update data
                $result = $this->Karyawan_model->update_batch($data,'id_karyawan');

                $keterangan     = "SUKSES, Edit data Karyawan ".$id_karyawan.", atas Nama : ".$nama_karyawan;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $id_karyawan;
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $karyawan          = $id_karyawan;
            }
            else
            {
                $result = FALSE;

                $keterangan     = "GAGAL, Edit data Karyawan ".$id_karyawan.", atas Nama : ".$nama_karyawan;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $id_karyawan;
                $jumlah         = 1;
                $sql            = $this->db->last_query();
            }

            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        }
        else //Add New
        {
            $this->auth->restrict($this->addPermission);
            $session = $this->session->userdata('app_session');

            $data = array(
                        'nik'=> $nik,
                        'nama_karyawan'=> $nama_karyawan,
                        'tempatlahir'=> $tempatlahir,
                        'tanggallahir'=> $tanggallahir,
                        'divisi'=> $id_divisi,
                        'jeniskelamin'=> $jeniskelamin,
                        'agama'=> $agama,
                        'levelpendidikan'=> $levelpendidikan,
                        'alamataktif'=> $alamataktif,
                        'nohp'=> $nohp,
                        'noktp'=> $noktp,
                        'npwp'=> $npwp,
                        'email'=> $email,
                        'sts_aktif'=> $sts_aktif,
                        'tgl_join'=>$tgl_join,
                        'tgl_end'=>$tgl_end,
                        'sts_karyawan'=>$sts_karyawan,
                        'norekening'=>$norekening,
                        'kdcab'=>$session['kdcab'],
                        );

            //Add Data
            $id = $this->Karyawan_model->insert($data);

            if(is_numeric($id))
            {
                $keterangan     = "SUKSES, tambah data Karyawan  atas Nama : ".$nama_karyawan;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result = TRUE;
            }
            else
            {
                $keterangan     = "GAGAL, tambah data Karyawan  atas Nama : ".$nama_karyawan;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
            //Save Log
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        }

        $param = array(
                'karyawan'=> $karyawan,
                'save' => $result,
                'token' => $this->security->get_csrf_hash()
                );

        echo json_encode($param);
    }

    //Delete
    function hapus_karyawan()
    {
        $this->auth->restrict($this->deletePermission);
        $id = $this->uri->segment(3);

        if($id!=''){

            $result = $this->Karyawan_model->delete($id);

            $keterangan     = "SUKSES, Delete data Karyawan ".$id;
            $status         = 1;
            $nm_hak_akses   = $this->addPermission;
            $kode_universal = $id;
            $jumlah         = 1;
            $sql            = $this->db->last_query();

        }
        else
        {
            $result = 0;
            $keterangan     = "GAGAL, Delete data Karyawan ".$id;
            $status         = 0;
            $nm_hak_akses   = $this->addPermission;
            $kode_universal = $id;
            $jumlah         = 1;
            $sql            = $this->db->last_query();

        }

        //Save Log
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        $param = array(
                'delete' => $result
                );

        echo json_encode($param);
    }

    function print_request($id){
        $id_karyawan = $id;
        $mpdf=new mPDF('','','','','','','','','','');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $kary_data      =  $this->Karyawan_model->find_data('karyawan',$id_karyawan,'id_karyawan');
        $this->template->set('kary_data', $kary_data);
        $show = $this->template->load_view('print_data',$data);

        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

    function rekap_pdf(){
        $mpdf=new mPDF('','','','','','','','','','');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $data_kar = $this->Karyawan_model->rekap_data()->result_array();
        $this->template->set('data_kar', $data_kar);

        $show = $this->template->load_view('print_rekap',$data);

        $this->mpdf->AddPage('L');
        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

    function downloadExcel()
    {
        $data_kar = $this->Karyawan_model->rekap_data()->result_array();

        $objPHPExcel    = new PHPExcel();
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
        //$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(17);
        //$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(17);
        //$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(17);
        //$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(17);
       //// $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(17);

        $objPHPExcel->getActiveSheet()->getStyle(1)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle(2)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle(3)->getFont()->setBold(true);

        $header = array(
            'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'font' => array(
            'bold' => true,
            'color' => array('rgb' => '25500'),
            'name' => 'Verdana'
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle("A1:M2")
                ->applyFromArray($header)
                ->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:M2');
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'REKAP DATA KARYAWAN')
            ->setCellValue('A3', 'No.')
            ->setCellValue('B3', 'NIK')
            ->setCellValue('C3', 'NAMA KARYAWAN')
            ->setCellValue('D3', 'TEMPAT, TGL LAHIR')
            ->setCellValue('E3', 'STATUS KARYAWAN')
            ->setCellValue('F3', 'TGL BERGABUNG')
            ->setCellValue('G3', 'TGL AKHIR KONTRAK')
            ->setCellValue('H3', 'JENIS KELAMIN')
            ->setCellValue('I3', 'AGAMA')
            ->setCellValue('J3', 'DIVISI')
            ->setCellValue('K3', 'NOREKENING')
            ->setCellValue('L3', 'NO HP')
            ->setCellValue('M3', 'ALAMAT');

        $ex = $objPHPExcel->setActiveSheetIndex(0);
        $no = 1;
        $counter = 4;
        foreach ($data_kar as $row):
            $ex->setCellValue('A'.$counter, $no++);
            $ex->setCellValue('B'.$counter, $row['nik']);
            $ex->setCellValue('C'.$counter, $row['nama_karyawan']);
            $ex->setCellValue('D'.$counter, $row['tempatlahir'].", ".date('d-m-Y', strtotime($row['tanggallahir'])));
            $ex->setCellValue('E'.$counter, $row['sts_karyawan']);
            $ex->setCellValue('F'.$counter, date('d-m-Y', strtotime($row['tgl_join'])));
            $ex->setCellValue('G'.$counter, date('d-m-Y', strtotime($row['tgl_end'])));
            $ex->setCellValue('H'.$counter, $row['jeniskelamin']);
            $ex->setCellValue('I'.$counter, $row['agama']);
            $ex->setCellValue('J'.$counter, $row['nm_divisi']);
            $ex->setCellValue('K'.$counter, $row['norekening']);
            $ex->setCellValue('L'.$counter, $row['nohp']);
            $ex->setCellValue('M'.$counter, $row['alamataktif']);

        $counter = $counter+1;
        endforeach;

        $objPHPExcel->getProperties()->setCreator("Yunaz Fandy")
            ->setLastModifiedBy("Yunaz Fandy")
            ->setTitle("Export Rekap Data Karyawan")
            ->setSubject("Export Rekap Data Karyawan")
            ->setDescription("Rekap Data Produk for Office 2007 XLSX, generated by PHPExcel.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("PHPExcel");
        $objPHPExcel->getActiveSheet()->setTitle('Rekap Data Karyawan');
        ob_end_clean();
        $objWriter  = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        header('Last-Modified:'. gmdate("D, d M Y H:i:s").'GMT');
        header('Chace-Control: no-store, no-cache, must-revalation');
        header('Chace-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="ExportRekapKaryawan'. date('Ymd') .'.xls"');

        $objWriter->save('php://output');

    }
}

?>

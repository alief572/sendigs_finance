<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Ichsan
 * @copyright Copyright (c) 2019, Ichsan
 *
 * This is controller for Master Supplier
 */

class Master_country extends Admin_Controller
{
    //Permission
    protected $viewPermission = 'Master_country.View';
    protected $addPermission  = 'Master_country.Add';
    protected $managePermission = 'Master_country.Manage';
    protected $deletePermission = 'Master_country.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array( 'upload', 'Image_lib'));
        $this->load->model(array('Master_country/Country_model',
                                 'Aktifitas/aktifitas_model',
                                ));
        $this->template->title('Manage Data Country');
        $this->template->page_icon('fa fa-table');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('Country');
        $this->template->render('index');
    }

    public function getDataJSON(){
    		$requestData	= $_REQUEST;
    		$fetch			= $this->queryDataJSON(
          $requestData['activation'],
    			$requestData['search']['value'],
    			$requestData['order'][0]['column'],
    			$requestData['order'][0]['dir'],
    			$requestData['start'],
    			$requestData['length']
    		);
    		$totalData		= $fetch['totalData'];
    		$totalFiltered	= $fetch['totalFiltered'];
    		$query			= $fetch['query'];

    		$data	= array();
    		$urut1  = 1;
            $urut2  = 0;
    		foreach($query->result_array() as $row)
    		{
    			$total_data     = $totalData;
                $start_dari     = $requestData['start'];
                $asc_desc       = $requestData['order'][0]['dir'];
                if($asc_desc == 'asc')
                {
                    $nomor = $urut1 + $start_dari;
                }
                if($asc_desc == 'desc')
                {
                    $nomor = ($total_data - $start_dari) - $urut2;
                }

    			$nestedData 	= array();
    				$detail = "";
    			$nestedData[]	= "<div align='center'>".$nomor."</div>";
    			$nestedData[]	= "<div align='left'>".strtoupper($row['id_country'])."</div>";
    			$nestedData[]	= "<div align='left'>".strtoupper($row['name_country'])."</div>";
    			if($this->auth->restrict($this->viewPermission) ) :
            $nestedData[]	= "<div style='text-align:center'>

              <a class='btn btn-sm btn-success edit' href='javascript:void(0)' title='Edit' data-id_country='".$row['id_country']."' style='width:30px; display:inline-block'>
                <span class='glyphicon glyphicon-edit'></span>
              </a>
              <a class='detail btn btn-sm btn-danger delete' href='javascript:void(0)' title='Delete' data-id_country = '".$row['id_country']."'  style='width:30px; display:inline-block'>
                <i class='fa fa-trash'></i>
              </a>
              </div>
      		      ";
            endif;
    			$data[] = $nestedData;
                $urut1++;
                $urut2++;
    		}

    		$json_data = array(
    			"draw"            	=> intval( $requestData['draw'] ),
    			"recordsTotal"    	=> intval( $totalData ),
    			"recordsFiltered" 	=> intval( $totalFiltered ),
    			"data"            	=> $data
    		);

    		echo json_encode($json_data);
  	}

  	public function queryDataJSON($activation, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){
  		// echo $series."<br>";
  		// echo $group."<br>";
  		// echo $komponen."<br>";

      $where_activation = "";
  		if(!empty($activation)){
  			$where_activation = " AND activation = '".$activation."' ";
  		}

  		$sql = "
  			SELECT
  				*
  			FROM
  				master_country
  			WHERE 1=1
          ".$where_activation."
  				AND (
  				id_country LIKE '%".$this->db->escape_like_str($like_value)."%'
  				OR name_country LIKE '%".$this->db->escape_like_str($like_value)."%'
  	        )
  		";

  		// echo $sql;

  		$data['totalData'] = $this->db->query($sql)->num_rows();
  		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
  		$columns_order_by = array(
  			0 => 'nomor',
  			1 => 'id_country',
  			2 => 'name_country'
  		);

  		$sql .= " ORDER BY id_country ASC, ".$columns_order_by[$column_order]." ".$column_dir." ";
  		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

  		$data['query'] = $this->db->query($sql);
  		return $data;
  	}

    public function modal_Process($action="",$id=""){
      $this->template->set('action', $action);
      $this->template->set('id', $id);
  		$this->template->render('modal_Process');
  	}

    public function modal_Helper($action="",$id_sup=""){
      $this->template->set('action', $action);
      $this->template->set('id', $id_sup);
  		$this->template->render('modal_Helper');
  	}

    public function saveCountry(){
  		$data				= $this->input->post();
      $counter = ($this->db->get('master_country')->num_rows())+1;

      $this->db->trans_begin();
      if ($data['type'] == 'edit') {
        $id_country = $data['id_country'];
        $insertData	= array(
          'name_country'	=> strtoupper($data['name_country']),
          'modified_on'	=> date('Y-m-d H:i:s'),
          'modified_by'	=> $this->auth->user_id()
        );
        $this->db->where('id_country',$data['id_country'])->update('master_country',$insertData);
      }else {
        $id_country = "MPB".str_pad($counter, 3, "0", STR_PAD_LEFT);
        $insertData	= array(
          'id_country'    => $id_country,
          'name_country'	=> strtoupper($data['name_country']),
          'activation'  => "active",
          'created_on'	=> date('Y-m-d H:i:s'),
          'created_by'	=> $this->auth->user_id()
        );
        $this->db->insert('master_country',$insertData);
      }
      $this->db->trans_complete();

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $Arr_Kembali	= array(
          'pesan'		=>'Failed Add Changes. Please try again later ...',
          'status'	=> 0
        );
        $keterangan = 'FAILED, '.$data['type'].' Country Data '.$id_country;
        $status = 0;
        $nm_hak_akses = $this->addPermission;
        $kode_universal = $this->auth->user_id();
        $jumlah = 1;
        $sql = $this->db->last_query();
      }
      else{
        $this->db->trans_commit();
        $Arr_Kembali	= array(
          'pesan'		=>'Success Save Item. Thanks ...',
          'status'	=> 1
        );

        $keterangan = 'SUCCESS, '.$data['type'].' Country Data '.$id_country;
        $status = 1;
        $nm_hak_akses = $this->addPermission;
        $kode_universal = $this->auth->user_id();
        $jumlah = 1;
        $sql = $this->db->last_query();
      }
      simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

  		echo json_encode($Arr_Kembali);
    }

    public function deleteCountry($id){
  		$this->db->trans_begin();
      $this->db->where('id_country',$id)->delete('master_country');
      $this->db->trans_complete();

      if($this->db->trans_status() === FALSE){
        $this->db->trans_rollback();
        $Arr_Kembali	= array(
          'pesan'		=>'Delete fail. Please try again later.',
          'status'	=> 0
        );
        $keterangan = 'Delete fail for Country Data '.$id;
        $status = 0;
        $nm_hak_akses = $this->addPermission;
        $kode_universal = $this->auth->user_id();
        $jumlah = 1;
        $sql = $this->db->last_query();
      }
      else{
        $this->db->trans_commit();
        $Arr_Kembali	= array(
          'pesan'		=>'Success delete country item. Thanks.',
          'status'	=> 1
        );

        $keterangan = 'SUCCESS, delete Country Data '.$id;
        $status = 1;
        $nm_hak_akses = $this->addPermission;
        $kode_universal = $this->auth->user_id();
        $jumlah = 1;
        $sql = $this->db->last_query();
      }
      simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

  		echo json_encode($Arr_Kembali);
	  }

    public function produk()
    {
        $id = $this->uri->segment(3);
        $data_sb = $this->Supplier_model->list_supplier_barang($id);

        $data = $this->Supplier_model->find_by(array('id_supplier' => $id));
        $this->template->set('results', $data);
        $this->template->set('data', $data_sb);
        $this->template->title("Daftar produk supplier $data->nm_supplier");
        $this->template->render('supplier_produk');
    }

    public function addproduk()
    {
        $id = $this->uri->segment(3);
        $data = $this->Supplier_model->find_by(array('id_supplier' => $id));

        $data_barang = $this->Barang_model->select('barang_master.id_barang,
                                            barang_master.nm_barang')
                                            ->where('barang_master.deleted', 0)
                                            ->where("`id_barang` NOT IN (SELECT `id_barang` FROM `supplier_barang` where id_supplier='$id')", null, false)
                                            ->order_by('barang_master.nm_barang', 'ASC')->find_all();

        $this->template->set('data', $data_barang);
        $this->template->set('results', $data);
        $this->template->title("Add produk untuk supplier $data->nm_supplier");
        $this->template->render('supplier_produk_add');
    }

    public function addproduk_save()
    {
        $id = $this->uri->segment(3);
        $jumlah = count($_POST['id_barang']);
        for ($i = 0; $i < $jumlah; ++$i) {
            $id_barang = $_POST['id_barang'][$i];
            $datasm = array(
                            'id_supplier' => $id,
                            'id_barang' => $id_barang,
                        );
            $insert = $this->Supplier_model->insert_supplier_barang($datasm);
        }

        if ($insert) {
            $this->session->set_flashdata('success', 'Data Berhasil Disimpan');
            redirect("supplier/produk/$id");
        } else {
            $this->session->set_flashdata('error', 'Gagal Menyimpan Data.');
            redirect_back("supplier/produk/$id");
        }
    }

    public function delete_produk($id, $id_sup)
    {
        $delete = $this->Supplier_model->delete_supplier_barang($id);
        if ($delete !== true) {
            $this->session->set_flashdata('error', 'Gagal Menghapus Data. '.$delete);
        } else {
            $this->session->set_flashdata('success', 'Data Berhasil Dihapus');
        }
        redirect("supplier/produk/$id_sup");
    }

    //Create New Customer
    public function create()
    {
        $this->auth->restrict($this->addPermission);
        $datmatu = $this->Matuang_model->pilih_matu()->result();
        $datnegara = $this->Negara_model->pilih_negara()->result();
        $datprov = $this->Supplier_model->pilih_provinsi()->result();

        $this->template->set('datmatu', $datmatu);
        $this->template->set('datnegara', $datnegara);
        $this->template->set('datprov', $datprov);
        $this->template->title('Input Master Supplier');
        $this->template->render('supplier_form');
    }

    public function add_matu()
    {
        $kode = $this->input->post('kode');
        $mata_uang = $this->input->post('mata_uang');
        $negara = $this->input->post('negara');

        $this->auth->restrict($this->addPermission);

        $data = array(
                        'kode' => $kode,
                        'mata_uang' => $mata_uang,
                        'negara' => $negara,
                        );

        //Add Data
        $id = $this->Matuang_model->insert($data);

        if (is_numeric($id)) {
            $keterangan = 'SUKSES, tambah data mata_uang atas Nama : '.$kode;
            $status = 1;
            $nm_hak_akses = $this->addPermission;
            $kode_universal = 'NewData';
            $jumlah = 1;
            $sql = $this->db->last_query();

            $result = true;
        } else {
            $keterangan = 'GAGAL, tambah data mata_uang atas Nama : '.$kode;
            $status = 0;
            $nm_hak_akses = $this->addPermission;
            $kode_universal = 'NewData';
            $jumlah = 1;
            $sql = $this->db->last_query();
            $result = false;
        }

        //Save Log
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        $param = array(
            'save' => $result,
        );

        echo json_encode($param);
    }

    public function get_matu()
    {
        $rmatu = $this->Matuang_model->pilih_matu()->result();
        //echo $result;
        echo "<select id='mata_uang' name='mata_uang' class='form-control pil_matu select2-hidden-accessible'>";
        echo "<option value=''></option>";
        foreach ($rmatu as $key => $st) :
                    echo "<option value='$st->id' set_select('mata_uang', $st->id, isset($data->mata_uang) && $data->mata_uang == $st->id)>$st->kode - $st->mata_uang - $st->negara
                    </option>";
        endforeach;
        echo '</select>';
    }

    //Edit Mitra
    public function edit()
    {
        $this->auth->restrict($this->managePermission);

        $id = $this->uri->segment(3);
        $data = $this->Supplier_model->find_by(array('id_supplier' => $id));
        if (!$data) {
            $this->template->set_message('Invalid ID', 'error');
            redirect('Supplier');
        }

        $datmatu = $this->Matuang_model->pilih_matu()->result();
        $datnegara = $this->Negara_model->pilih_negara()->result();
        $datprov = $this->Supplier_model->pilih_provinsi()->result();
        $prov = $this->Supplier_model->get_prov($id);
        $datkota = $this->Supplier_model->pilih_kota($prov)->result();

        $this->template->set('datmatu', $datmatu);
        $this->template->set('datnegara', $datnegara);
        $this->template->set('datprov', $datprov);
        $this->template->set('datkota', $datkota);
        $this->template->set('data', $data);
        $this->template->title('Edit Data Supplier');
        $this->template->render('supplier_form');
    }

    //Save customer ajax
    public function save_data_supplier()
    {
        $type = $this->input->post('type');
        $id_supplier = $this->input->post('id_supplier');
        $nm_supplier = strtoupper($this->input->post('nm_supplier'));
        $alamat = strtoupper($this->input->post('alamat'));
        $telpon = $this->input->post('telpon');
        $fax = $this->input->post('fax');
        $npwp = $this->input->post('npwp');
        $alamat_npwp = strtoupper($this->input->post('alamat_npwp'));
        $cp = strtoupper($this->input->post('cp'));
        $hp_cp = $this->input->post('hp_cp');
        $email = strtoupper($this->input->post('email'));
        $keterangan_sup = strtoupper($this->input->post('keterangan'));
        $sts_aktif = $this->input->post('sts_aktif');
        $id_webchat = strtoupper($this->input->post('id_webchat'));

        $id_negara = $this->input->post('id_negara');
        $id_prov = $this->input->post('id_prov');
        $id_kab = $this->input->post('id_kab');

        $mata_uang = $this->input->post('mata_uang');
        $group_produk = $this->input->post('group_produk');

        $produksi_awal = $this->input->post('produksi_awal');
        $produksi_akhir = $this->input->post('produksi_akhir');
        $pengapalan_awal = $this->input->post('pengapalan_awal');
        $pengapalan_akhir = $this->input->post('pengapalan_akhir');
        $pengiriman_awal = $this->input->post('pengiriman_awal');
        $pengiriman_akhir = $this->input->post('pengiriman_akhir');
        $cukai_awal = $this->input->post('cukai_awal');
        $cukai_akhir = $this->input->post('cukai_akhir');

        $matu = array();
        if (!empty($mata_uang)) {
            foreach ($mata_uang as $mat_uang) {
                array_push($matu, $mat_uang);
            }
            $mat_uang = serialize($matu);
        }

        if ($id_supplier == '') {
            $id_supplier = $this->Supplier_model->generate_id($id_negara);
        } else {
            $id_supplier = $id_supplier;
        }
        if ($type == 'edit') {
            $this->auth->restrict($this->managePermission);

            if ($id_supplier != '') {
                $data = array(
                            array(
                                'id_supplier' => $id_supplier,
                                'nm_supplier' => $nm_supplier,
                                'group_produk' => $group_produk,
                                'alamat' => $alamat,
                                'telpon' => $telpon,
                                'fax' => $fax,
                                'email' => $email,
                                'cp' => $cp,
                                'hp_cp' => $hp_cp,
                                'npwp' => $npwp,
                                'alamat_npwp' => $alamat_npwp,
                                'mata_uang' => $mat_uang,
                                'sts_aktif' => $sts_aktif,
                                'id_negara' => $id_negara,
                                'id_prov' => $id_prov,
                                'id_kab' => $id_kab,
                                'id_webchat' => $id_webchat,
                                'keterangan' => $keterangan_sup,
                                'produksi_awal' => $produksi_awal,
                                'produksi_akhir' => $produksi_akhir,
                                'pengapalan_awal' => $pengapalan_awal,
                                'pengapalan_akhir' => $pengapalan_akhir,
                                'pengiriman_awal' => $pengiriman_awal,
                                'pengiriman_akhir' => $pengiriman_akhir,
                                'cukai_awal' => $cukai_awal,
                                'cukai_akhir' => $cukai_akhir,
                            ),
                        );

                //Update data
                $result = $this->Supplier_model->update_batch($data, 'id_supplier');

                $jumlah = count($_POST['cbm']);
                for ($i = 0; $i < $jumlah; ++$i) {
                    $cbm = $_POST['cbm'][$i];
                    $id_cbm = $_POST['id_cbm'][$i];
                    $cbm_kgs = $_POST['cbm_kgs'][$i];
                    $id_supplier_cbm = $_POST['id_supplier_cbm'][$i];
                    if (!empty($_POST['id_supplier_cbm'][$i])) {
                        if (!empty($_POST['cbm'][$i])) {
                            //update
                            $datasm = array(
                                'cbm' => $cbm,
                                'kgs' => $cbm_kgs,
                            );
                            $this->Supplier_model->update_supplier_cbm($id_supplier_cbm, $datasm);
                        } else {
                            //hapus
                            $this->Supplier_model->delete_supplier_cbm($id_supplier_cbm);
                        }
                    } else {
                        if (!empty($_POST['cbm'][$i])) {
                            $datasm = array(
                                'id_supplier' => $id_supplier,
                                'id_cbm' => $id_cbm,
                                'cbm' => $cbm,
                                'kgs' => $_POST['cbm_kgs'][$i],
                            );
                            $this->Supplier_model->insert_supplier_cbm($datasm);
                        }
                    }
                }

                $keterangan = 'SUKSES, Edit data Supplier '.$id_supplier.', atas Nama : '.$nm_supplier;
                $status = 1;
                $nm_hak_akses = $this->addPermission;
                $kode_universal = $id_supplier;
                $jumlah = 1;
                $sql = $this->db->last_query();

                $supplier = $id_supplier;
            } else {
                $result = false;

                $keterangan = 'GAGAL, Edit data Supplier '.$id_supplier.', atas Nama : '.$nm_supplier;
                $status = 1;
                $nm_hak_akses = $this->addPermission;
                $kode_universal = $id_customer;
                $jumlah = 1;
                $sql = $this->db->last_query();
            }

            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else { //Add New
            $this->auth->restrict($this->addPermission);

            $data = array(
                        'id_supplier' => $id_supplier,
                        'nm_supplier' => $nm_supplier,
                        'group_produk' => $group_produk,
                        'alamat' => $alamat,
                        'telpon' => $telpon,
                        'fax' => $fax,
                        'email' => $email,
                        'cp' => $cp,
                        'hp_cp' => $hp_cp,
                        'npwp' => $npwp,
                        'alamat_npwp' => $alamat_npwp,
                        'mata_uang' => $mat_uang,
                        'sts_aktif' => $sts_aktif,
                        'id_negara' => $id_negara,
                        'id_prov' => $id_prov,
                        'id_kab' => $id_kab,
                        'id_webchat' => $id_webchat,
                        'keterangan' => $keterangan_sup,
                        'produksi_awal' => $produksi_awal,
                        'produksi_akhir' => $produksi_akhir,
                        'pengapalan_awal' => $pengapalan_awal,
                        'pengapalan_akhir' => $pengapalan_akhir,
                        'pengiriman_awal' => $pengiriman_awal,
                        'pengiriman_akhir' => $pengiriman_akhir,
                        'cukai_awal' => $cukai_awal,
                        'cukai_akhir' => $cukai_akhir,
                        );

            //Add Data
            $id = $this->Supplier_model->insert($data);
            $jumlah = count($_POST['cbm']);
            for ($i = 0; $i < $jumlah; ++$i) {
                $cbm = $_POST['cbm'][$i];
                $id_cbm = $_POST['id_cbm'][$i];
                if (!empty($cbm)) {
                    $datasm = array(
                            'id_supplier' => $id_supplier,
                            'id_cbm' => $id_cbm,
                            'cbm' => $cbm,
                            'kgs' => $_POST['cbm_kgs'][$i],
                        );
                    $this->Supplier_model->insert_supplier_cbm($datasm);
                }
            }

            if (is_numeric($id)) {
                $keterangan = 'SUKSES, tambah data Supplier '.$id_supplier.', atas Nama : '.$nm_supplier;
                $status = 1;
                $nm_hak_akses = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah = 1;
                $sql = $this->db->last_query();

                $result = true;
                $customer = $id_customer;
            } else {
                $keterangan = 'GAGAL, tambah data Supplier '.$id_supplier.', atas Nama : '.$nm_supplier;
                $status = 0;
                $nm_hak_akses = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah = 1;
                $sql = $this->db->last_query();
                $result = false;
            }
            //Save Log
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }

        $param = array(
                'supplier' => $supplier,
                'save' => $result,
                );

        echo json_encode($param);
    }

    public function hapus_supplier()
    {
        $this->auth->restrict($this->deletePermission);
        $id = $this->uri->segment(3);

        if ($id != '') {
            $result = $this->Supplier_model->delete($id);

            $keterangan = 'SUKSES, Delete data Supplier '.$id;
            $status = 1;
            $nm_hak_akses = $this->addPermission;
            $kode_universal = $id;
            $jumlah = 1;
            $sql = $this->db->last_query();
        } else {
            $result = 0;
            $keterangan = 'GAGAL, Delete data Supplier '.$id;
            $status = 0;
            $nm_hak_akses = $this->addPermission;
            $kode_universal = $id;
            $jumlah = 1;
            $sql = $this->db->last_query();
        }

        //Save Log
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        $param = array(
                'delete' => $result,
                'idx' => $id,
                );

        echo json_encode($param);
    }

    public function print_request($id)
    {
        $id_supplier = $id;
        $mpdf = new mPDF('', '', '', '', '', '', '', '', '', '');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $sup_data = $this->Supplier_model->print_data_supplier($id_supplier);

        $this->template->set('sup_data', $sup_data);
        $show = $this->template->load_view('print_data', $data);

        $this->mpdf->AddPage('P');
        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

    public function print_rekap()
    {
        $mpdf = new mPDF('', '', '', '', '', '', '', '', '', '');
        $mpdf->SetImportUse();
        $mpdf->RestartDocTemplate();

        $rekap = $this->Supplier_model->rekap_data()->result_array();

        $this->template->set('rekap', $rekap);

        $show = $this->template->load_view('print_rekap', $data);

        $this->mpdf->AddPage('L');
        $this->mpdf->WriteHTML($show);
        $this->mpdf->Output();
    }

    public function downloadExcel()
    {
        $rekap = $this->Supplier_model->rekap_data()->result_array();

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(17);

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
                'color' => array('rgb' => '000000'),
                'name' => 'Verdana',
            ),
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:J2')
                ->applyFromArray($header)
                ->getFont()->setSize(14);
        $objPHPExcel->getActiveSheet()->mergeCells('A1:J2');
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Rekap Data Supplier')
            ->setCellValue('A3', 'No.')
            ->setCellValue('B3', 'ID Supplier')
            ->setCellValue('C3', 'Nama Supplier')
            ->setCellValue('D3', 'Negara')
            ->setCellValue('E3', 'Alamat')
            ->setCellValue('F3', 'No Telpon /  Fax')
            ->setCellValue('G3', 'Kontak Person')
            ->setCellValue('H3', 'Hp Kontak Person / WeChat ID')
            ->setCellValue('I3', 'Email')
            ->setCellValue('J3', 'Status');

        $ex = $objPHPExcel->setActiveSheetIndex(0);
        $no = 1;
        $counter = 4;
        foreach ($rekap as $row):
            $ex->setCellValue('A'.$counter, $no++);
        $ex->setCellValue('B'.$counter, strtoupper($row['id_supplier']));
        $ex->setCellValue('C'.$counter, $row['nm_supplier']);
        $ex->setCellValue('D'.$counter, strtoupper($row['nm_negara']));
        $ex->setCellValue('E'.$counter, $row['alamat']);
        $ex->setCellValue('F'.$counter, $row['telpon'].' / '.$row['fax']);
        $ex->setCellValue('G'.$counter, $row['cp']);
        $ex->setCellValue('H'.$counter, $row['hp_cp'].' / '.$row['id_webchat']);
        $ex->setCellValue('I'.$counter, $row['email']);
        $ex->setCellValue('J'.$counter, $row['sts_aktif']);

        $counter = $counter + 1;
        endforeach;

        $objPHPExcel->getProperties()->setCreator('Yunaz Fandy')
            ->setLastModifiedBy('Yunaz Fandy')
            ->setTitle('Export Rekap Data Supplier')
            ->setSubject('Export Rekap Data Supplier')
            ->setDescription('Rekap Data Supplier for Office 2007 XLSX, generated by PHPExcel.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('PHPExcel');
        $objPHPExcel->getActiveSheet()->setTitle('Rekap Data Supplier');
        ob_end_clean();
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        header('Last-Modified:'.gmdate('D, d M Y H:i:s').'GMT');
        header('Chace-Control: no-store, no-cache, must-revalation');
        header('Chace-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="ExportRekapSupplier'.date('Ymd').'.xls"');

        $objWriter->save('php://output');
    }
}

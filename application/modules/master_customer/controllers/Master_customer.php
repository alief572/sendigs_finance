<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Master_customer extends Admin_Controller
{
	//Permission
	protected $viewPermission   = "Master_Customer.View";
	protected $addPermission    = "Master_Customer.Add";
	protected $managePermission = "Master_Customer.Manage";
	protected $deletePermission = "Master_Customer.Delete";

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array(
			'Master_customer/Customer_model',
			'Aktifitas/aktifitas_model',
		));
		$this->template->title('Customer');

		date_default_timezone_set("Asia/Bangkok");

		$this->id_user  = $this->auth->user_id();
		$this->datetime = date('Y-m-d H:i:s');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$where = [
			'deleted_date' => NULL
		];
		$listData = $this->Customer_model->get_data($where);

		$data = [
			'result' =>  $listData
		];

		history("View data customer");
		$this->template->set($data);
		$this->template->title('Master Customer');
		$this->template->render('index');
	}

	public function add($id = null, $tanda = null)
	{
		if (empty($id)) {
			$this->auth->restrict($this->addPermission);
		} else {
			$this->auth->restrict($this->managePermission);
		}
		if ($this->input->post()) {
			$Arr_Kembali			= array();
			$data					= $this->input->post();
			$data_session			= $this->session->userdata;

			//Check Customer
			$qCheckName		= "SELECT * FROM customer WHERE nm_customer = '" . trim(strtoupper($data['nm_customer'])) . "' ";
			$NumCheckName	= $this->db->query($qCheckName)->num_rows();
			// echo $NumCheckName;

			//Check PIC
			$qCheckPIC		= "SELECT * FROM customer_pic WHERE email_pic = '" . trim(strtolower($data['email_pic'])) . "' ";
			$NumCheckPIC	= $this->db->query($qCheckPIC)->num_rows();
			$dataPIC		= $this->db->query($qCheckPIC)->result_array();


			// echo $NumCheckPIC."<br>";

			$Ymonth		= date('ym');

			//Urutan Customer
			$qCust 			= "SELECT MAX(id_customer) AS maxP FROM customer WHERE kdcab='100' AND id_customer LIKE 'C100-" . $Ymonth . "%' ";
			$numRowCust		= $this->db->query($qCust)->num_rows();
			$resultPlant	= $this->db->query($qCust)->result_array();
			$angkaUrut2		= $resultPlant[0]['maxP'];
			$urutan2		= (int)substr($angkaUrut2, 9, 3);
			$urutan2++;
			$urut2			= sprintf('%03s', $urutan2);
			$kodeCust		= "C100-" . $Ymonth . $urut2;
			if (!empty($data['id_customer'])) {
				$kodeCust	= $data['id_customer'];
			}

			//Urutan PIC
			$qPIC 			= "SELECT MAX(id_pic) AS maxPC FROM customer_pic WHERE id_pic LIKE 'PIC-" . $Ymonth . "%' ";
			$resultPIC		= $this->db->query($qPIC)->result_array();
			$angkaUrut2x	= $resultPIC[0]['maxPC'];
			$urutan2x		= (int)substr($angkaUrut2x, 8, 3);
			$urutan2x++;
			$urut2x			= sprintf('%03s', $urutan2x);
			$kodePIC		= "PIC-" . $Ymonth . $urut2x;

			if ($NumCheckPIC > 0) {
				$kodePIC		= $dataPIC[0]['id_pic'];
			}

			$ArrCust = array(
				'id_customer'		=> $kodeCust,
				'nm_customer' 		=> trim($data['nm_customer']),
				'kdcab' 			=> '100',
				'bidang_usaha' 		=> $data['bidang_usaha'],
				'produk_jual' 		=> ucwords(strtolower($data['produk_jual'])),
				'kredibilitas' 		=> $data['kredibilitas'],
				'alamat' 			=> ucwords($data['alamat']),
				'country_code' 		=> $data['country_code'],
				'provinsi' 			=> $data['provinsi'],
				'kota' 				=> $data['kota'],
				'kode_pos' 			=> $data['kode_pos'],
				'telpon' 			=> str_replace('-', '', $data['telpon']),
				'fax' 				=> str_replace('-', '', $data['fax']),
				'npwp' 				=> $data['npwp'],
				'alamat_npwp' 		=> $data['alamat_npwp'],
				'ktp' 				=> "",
				'alamat_ktp' 		=> "",
				'sts_aktif' 		=> $data['sts_aktif'],
				'id_marketing' 		=> $data['id_marketing'],
				'id_pic' 			=> $kodePIC,
				'referensi' 		=> ucwords(strtolower($data['reference_by'])),
				'website' 			=> $data['website'],
				'foto' 				=> "",
				'diskon_toko' 		=> $data['diskon_toko'],
				'created_on' 		=> $this->datetime,
				'created_by' 		=> $this->id_user
			);

			$ArrBidUsaha = array();
			if ($data['bidang_usaha'] != '0') {
				$qBidU	= $this->db->query("SELECT*FROM bidang_usaha WHERE id_bidang_usaha='" . $data['bidang_usaha'] . "' ")->result_array();

				$ArrBidUsaha = array(
					'id_customer' 		=> $kodeCust,
					'bidang_usaha' 		=> $data['bidang_usaha'],
					'keterangan' 		=> $qBidU[0]['bidang_usaha'],
					'created_on' 		=> $this->datetime,
					'created_by' 		=> $this->id_user
				);
			}

			$ArrReferensi = array(
				'id_customer' 		=> $kodeCust,
				'reference_by' 		=> $data['reference_by'],
				'reference_name' 	=> ucwords(strtolower($data['reference_name'])),
				'reference_phone' 	=> str_replace('-', '', $data['reference_phone']),
				'created_on' 		=> $this->datetime,
				'created_by' 		=> $this->id_user
			);

			if ($NumCheckPIC < 1) {
				$ArrPIC	= array(
					'id_pic' 			=> $kodePIC,
					'nm_pic' 			=> ucwords(strtolower($data['nm_pic'])),
					'divisi' 			=> strtolower($data['divisi']),
					'jabatan' 			=> NULL,
					'hp' 				=> str_replace('-', '', $data['hp']),
					'email_pic' 		=> trim(strtolower($data['email_pic'])),
					'created_on' 		=> $this->datetime,
					'created_by' 		=> $this->id_user
				);
				// print_r($ArrPIC);
			}
			$ArrPICUpdate	= array(
				'id_pic' 			=> $kodePIC,
				'nm_pic' 			=> ucwords(strtolower($data['nm_pic'])),
				'divisi' 			=> strtolower($data['divisi']),
				'jabatan' 			=> NULL,
				'hp' 				=> str_replace('-', '', $data['hp']),
				'email_pic' 		=> trim(strtolower($data['email_pic'])),
				'modified_on' 		=> $this->datetime,
				'modified_by' 		=> $this->id_user
			);
			// echo "<pre>";
			// exit;

			//Address
			$address_edit = (!empty($data['invoice_address'])) ? $data['invoice_address'] : [];
			$ArrAddressEdit = [];
			$ArrDelIDKey = [];
			if (!empty($address_edit)) {
				foreach ($address_edit as $key => $value) {
					$ArrDelIDKey[] = $value['id'];
					$ArrAddressEdit[$key]['id'] 				= $value['id'];
					$ArrAddressEdit[$key]['invoice_address'] 	= $value['address'];
					$ArrAddressEdit[$key]['last_by'] 			= $this->id_user;
					$ArrAddressEdit[$key]['last_date'] 			= $this->datetime;
				}
			}

			$address_add = (!empty($data['address_new'])) ? $data['address_new'] : [];
			$ArrAddressNew = [];
			if (!empty($address_add)) {
				$nomor = 0;
				foreach ($address_add as $value) {
					$nomor++;
					$ArrAddressNew[$nomor]['id_customer'] 		= $kodeCust;
					$ArrAddressNew[$nomor]['invoice_address'] 	= $value;
					$ArrAddressNew[$nomor]['last_by'] 			= $this->id_user;
					$ArrAddressNew[$nomor]['last_date'] 		= $this->datetime;
				}
			}


			if ($NumCheckName > 0 and empty($data['id_customer'])) {
				$Arr_Kembali		= array(
					'status'		=> 3,
					'pesan'			=> 'Customer Name Already Exists. Please input different customer name ...'
				);
			} else {
				$this->db->trans_start();
				if (empty($data['id_customer'])) {
					$this->db->insert('customer', $ArrCust);
				} else {
					$this->db->where('id_customer', $kodeCust);
					$this->db->update('customer', $ArrCust);
				}

				if (!empty($ArrBidUsaha)) {
					$this->db->insert('customer_bidang_usaha', $ArrBidUsaha);
				}
				if ($NumCheckPIC < 1) {
					$this->db->insert('customer_pic', $ArrPIC);
				} else {
					$this->db->where('id_pic', $kodePIC);
					$this->db->update('customer_pic', $ArrPICUpdate);
				}
				$this->db->insert('customer_referensi', $ArrReferensi);

				//Cutsomer
				if (!empty($ArrDelIDKey)) {
					$this->db->where_not_in('id', $ArrDelIDKey);
					$this->db->update('customer_address_invoice', array('deleted_by' => $this->id_user, 'deleted_date' => $this->datetime));
				}
				if (!empty($ArrAddressEdit)) {
					$this->db->update_batch('customer_address_invoice', $ArrAddressEdit, 'id');
				}
				if (!empty($ArrAddressNew)) {
					$this->db->insert_batch('customer_address_invoice', $ArrAddressNew);
				}


				$this->db->trans_complete();

				if ($this->db->trans_status() === FALSE) {
					$this->db->trans_rollback();
					$Arr_Kembali	= array(
						'pesan'		=> 'Process data failed. Please try again later ...',
						'status'	=> 2
					);
				} else {
					$this->db->trans_commit();
					$Arr_Kembali	= array(
						'pesan'		=> 'Process data Success. Thank you & have a nice day ...',
						'status'	=> 1
					);
					history('Add/Edit Customer ' . $kodeCust);
				}
			}
			echo json_encode($Arr_Kembali);
		} else {

			$det_Province	= $this->db->order_by('id_prov')->get_where('provinsi', array('country_code' => 'IDN'))->result_array();
			$det_Bidang		= $this->db->order_by('bidang_usaha')->get_where('bidang_usaha', array('deleted' => 'N'))->result_array();
			$restContry		= $this->db->order_by('country_name', 'asc')->get('country')->result_array();
			$restMkt		= $this->db->order_by('nm_karyawan')->get_where('employee', array('department' => 1))->result_array();
			$restHeader		= $this->db->get_where('customer', array('id_customer' => $id))->result_array();
			$restReff		= $this->db->order_by('id_reff', 'desc')->limit(1)->get_where('customer_referensi', array('id_customer' => $id))->result_array();

			$id_pic 		= (!empty($restHeader[0]['id_pic'])) ? $restHeader[0]['id_pic'] : 'X';
			$restPIC		= $this->db->get_where('customer_pic', array('id_pic' => $id_pic))->result_array();
			$restAddress	= $this->db->get_where('customer_address_invoice', array('id_customer' => $id, 'deleted_date' => NULL))->result_array();

			$data = [
				'restHeader' =>  $restHeader,
				'restReff' =>  $restReff,
				'restPIC' =>  $restPIC,
				'restAddress' =>  $restAddress,
				'rows_province' =>  $det_Province,
				'rows_marketing' =>  $restMkt,
				'CountryName' =>  $restContry,
				'rows_bidang' =>  $det_Bidang,
				'tanda' =>  $tanda,
			];

			$this->template->set($data);
			$this->template->title('Add Customer');
			$this->template->render('add');
		}
	}

	public function getDistrict()
	{
		$id_Dist 	= $this->input->post('id_prov');
		$sqlDist	= "SELECT * FROM kabupaten WHERE id_prov='" . $id_Dist . "' ORDER BY nama ASC";
		$restDist	= $this->db->query($sqlDist)->result_array();

		$option	= "<option value='0'>Select An District</option>";
		foreach ($restDist as $val => $valx) {
			$option .= "<option value='" . $valx['id_kab'] . "'>" . $valx['nama'] . "</option>";
		}
		if (COUNT($restDist)) {
			$option .= "<option value=''>Data is empty, skip this input</option>";
		}

		$ArrJson	= array(
			'option' => $option
		);
		echo json_encode($ArrJson);
	}

	public function delete()
	{
		$this->auth->restrict($this->deletePermission);

		$id = $this->input->post('id');
		$data = [
			'deleted_by' 	  => $this->id_user,
			'deleted_date' 	=> $this->datetime
		];

		$this->db->trans_begin();
		$this->db->where('id_customer', $id)->update("customer", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Failed process data!',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success process data!',
				'status'	=> 1
			);
			history("Delete customer master : " . $id);
		}
		echo json_encode($status);
	}
}

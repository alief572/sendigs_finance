<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Syamsudin
 * @copyright Copyright (c) 2022, Syamsudin
 *
 * This is controller for Master diskon
 */

class Ms_diskon extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Master_Discount.View';
	protected $addPermission  	= 'Master_Discount.Add';
	protected $managePermission = 'Master_Discount.Manage';
	protected $deletePermission = 'Master_Discount.Delete';

	public function __construct()
	{
		parent::__construct();

		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model(array(
			'ms_diskon/Ms_diskon_model',
			'Aktifitas/aktifitas_model',
		));
		$this->template->title('Manage Data Supplier');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$deleted = '0';
		$data = $this->Ms_diskon_model->get_data_diskon();
		$this->template->set('results', $data);
		$this->template->title('Discount');
		$this->template->render('index');
	}


	public function AddDiskon($id = null)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');

		if ($id !== null) {
			$get_data = $this->db->query("SELECT a.*, b.nm_lengkap FROM ms_diskon a LEFT JOIN users b ON b.id_user = a.created_by WHERE a.id = '" . $id . "'")->row();
			$get_user = $this->db->query('SELECT id_user, nm_lengkap FROM users')->result();

			$this->template->set('results', [
				'data_diskon' => $get_data,
				'list_user' => $get_user
			]);

			$this->template->page_icon('fa fa-pencil');
			$this->template->title('Edit Discount');
		} else {
			$this->template->page_icon('fa fa-plus');
			$this->template->title('Add Discount');
		}
		$this->template->render('adddiskon');
	}

	function GetProduk()
	{
		$loop = $_GET['jumlah'] + 1;

		$user = $this->db->query("SELECT a.* FROM users as a ")->result();




		echo "
		<tr id='tr_$loop'>
			<td>$loop</td>";


		echo	"
			<td id='tingkatan_$loop'><input type='text' align='right' class='form-control input-sm' id='used_tingkatan_$loop' required name='dt[$loop][tingkatan]'></td>
            <td id='keterangan_$loop'><input type='text' align='right' class='form-control input-sm' id='used_keterangan_$loop' required name='dt[$loop][keterangan]'></td>
            <td id='diskon_awal_$loop'><input type='text' align='right' class='form-control input-sm' id='used_diskon_awal_$loop' required name='dt[$loop][diskon_awal]' value='0'></td>
            <td id='diskon_akhir_$loop'><input type='text' align='right' class='form-control input-sm' id='used_diskon_akhir_$loop' required name='dt[$loop][diskon_akhir]' value='0'></td>
            <td>
				<select id='used_user_$loop' name='dt[$loop][user]' data-no='$loop' class='form-control select' required>
					<option value=''>-Pilih-</option>";
		foreach ($user as $user) {
			echo "<option value='$user->id_user'>$user->nm_lengkap</option>";
		}
		echo	"</select>
			</td>
			<td align='center'>
                <button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return HapusItem($loop);'><i class='fa fa-close'></i></button>
             </td>
			
		</tr>
		";
	}


	public function SaveNewDiskon()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();


		$this->db->trans_begin();

		$numb1 = 0;
		$dt = array();
		if (isset($post['id_diskon'])) {
			// print_r('masuk');
			// exit;
			$this->db->update('ms_diskon', [
				'tingkatan' => $post['tingkatan'],
				'keterangan' => $post['keterangan'],
				'diskon_awal' => $post['diskon_awal'],
				'diskon_akhir' => $post['diskon_akhir'],
				'approved_by' => $post['approved_by'],
				'modified_on' => date('Y-m-d H:i:s'),
				'modified_by' => $this->auth->user_id()
			], ['id' => $post['id_diskon']]);
		} else {
			foreach ($_POST['dt'] as $used) {
				if (!empty($used['tingkatan'])) {
					$numb1++;
					$dt[] =  array(
						'tingkatan'		    => $used['tingkatan'],
						'keterangan'		    => $used['keterangan'],
						'diskon_awal'	    => $used['diskon_awal'],
						'diskon_akhir'	    => $used['diskon_akhir'],
						'approved_by'	    => $used['user'],
						'created_on'			=> date('Y-m-d H:i:s'),
						'created_by'			=> $this->auth->user_id()
					);
				}
			}

			$this->db->insert_batch('ms_diskon', $dt);
		}


		// print_r($dt);
		// exit();


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}


	public function editDiskon($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$diskon = $this->db->get_where('ms_diskon', array('id' => $id))->result();
		$lvl1 = $this->db->get('ms_inventory_type');
		$lvl2 = $this->db->get('ms_top');
		$data = [
			'diskon' => $diskon,
			'lvl1' => $lvl1,
			'lvl2' => $lvl2
		];
		$this->template->set('results', $data);
		$this->template->title('Diskon');
		$this->template->render('editdiskon');
	}

	public function saveEditDiskon()
	{
		$this->auth->restrict($this->editPermission);
		$post = $this->input->post();
		// print_r($post);
		// exit();
		$this->db->trans_begin();
		$data = [
			'id_type'		    => $post['level1'],
			'id_top'		    => $post['top'],
			'nilai_diskon'      => $post['nilai'],
			'modified_on'		=> date('Y-m-d H:i:s'),
			'modified_by'		=> $this->auth->user_id()
		];

		$this->db->where('id', $post['id_diskon'])->update("ms_diskon", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Data. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Data. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}

	public function deleteDiskon()
	{
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];

		$this->db->trans_begin();
		$this->db->where('id', $id)->update("ms_diskon", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. Thanks ...',
				'status'	=> 1
			);
		}
		echo json_encode($status);
	}
}

<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunaz
 * @copyright Copyright (c) 2018, Yunaz
 *
 * This is controller for Salesorder
 */

class Inquiry extends Admin_Controller {

    //Permission
    protected $viewPermission   = "Inquiry.View";
    protected $addPermission    = "Inquiry.Add";
    protected $managePermission = "Inquiry.Manage";
    protected $deletePermission = "Inquiry.Delete";

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload','Image_lib'));
        $this->load->model(array('Inquiry/Inquiry_model',
                                  'Aktifitas/aktifitas_model'
                                ));
        $this->template->title('Inquiry');
        $this->template->page_icon('fa fa-table');

        date_default_timezone_set("Asia/Bangkok");
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		
		if ($this->input->post()){
        $type  = $this->input->post("type");
		}
		else {
        $type = '';
		}
		
		$this->template->page_icon('fa fa-gears');
		$lvl1 = $this->Inquiry_model->get_data('tr_inquiry_hd');
		$deleted = '0';
		$data = $this->Inquiry_model->get_data_inquiry();
		$data2 = [
			
			'inventory_1' => $lvl1,
			
		];
		
		$this->template->set('results', $data);
	    $this->template->set('result', $data2);
        $this->template->title('Inquiry');
        $this->template->render('index.php');
    }

	public function addInquiry()
    {

    		$session = $this->session->userdata('app_session');
			
			$customer    = $this->Inquiry_model->get_data('master_customer');
			$supplier    = $this->Inquiry_model->get_data('master_supplier');
			$material    = $this->Inquiry_model->get_data('ms_material');
			$sales       = $this->Inquiry_model->get_data('ms_karyawan');
			$pic         = $this->Inquiry_model->get_data('child_customer_pic');
			$data = [
			'customer' => $customer,
			'supplier' => $supplier,
			'material' => $material,
			'sales' => $sales,
			'pic' => $pic,
			];
			$this->template->set('results', $data);
		
    		$this->template->title('Add Inquiry');
            $this->template->page_icon('fa fa-edit');
    	    $this->template->title('Add Inquiry');
            $this->template->render('add_inquiry');
    }
	
	public function editInquiry()
    {

    		$session = $this->session->userdata('app_session');
			$this->auth->restrict($this->viewPermission);
		
			$noinquiry = $this->uri->segment(3);
			$inquiry 	= $this->Inquiry_model->viewInquiry($noinquiry);
			$form       = $this->Inquiry_model->get_data('tr_inquiry_dt','no_inquiry',$noinquiry);
			$payment       = $this->Inquiry_model->get_data('tr_inquiry_payment','no_inquiry',$noinquiry);
			$delivery      = $this->Inquiry_model->get_data('tr_inquiry_delivery','no_inquiry',$noinquiry);
		    
			$pic 		 = $this->Inquiry_model->get_data('child_customer_pic');
        	$customer    = $this->Inquiry_model->get_data('master_customer');
			$supplier    = $this->Inquiry_model->get_data('master_supplier');
			$material    = $this->Inquiry_model->get_data('ms_material');
			$sales       = $this->Inquiry_model->get_data('ms_karyawan');
			$pic         = $this->Inquiry_model->get_data('child_customer_pic');
			$PaymentType = $this->Inquiry_model->getArray('ms_tipe_payment',array(),'id_type','nama');
			$form_choice = Array ( '0' => SHEET, 
								   '1' => COIL );
			$data = [
			'noinquiry' => $noinquiry,
			'inquiry' => $inquiry,
			'form'    => $form,
			'payment' => $payment,
			'delivery' => $delivery,
			'customer' => $customer,
			'supplier' => $supplier,
			'material' => $material,
			'sales' => $sales,
			'pic' => $pic,
			'payment_type' => $PaymentType,
			'pilihform'    => $form_choice,
			];
			
			// print_r($PaymentType);
			// exit();
			$this->template->set('results', $data);
		
    		$this->template->title('Edit Inquiry');
            $this->template->page_icon('fa fa-edit');
    	    $this->template->title('Edit Inquiry');
            $this->template->render('edit_inquiry');
			
			
    }
	
	function get_pic(){
        $cust = $_GET['customer'];
		
        $datpic = $this->Inquiry_model->pilih_combobox('child_customer_pic','id_customer', $cust)->result();
        // print_r($datcust);
        // exit();
        echo "<select id='pic_cust' name='pic_cust' class='form-control input-sm select2'>";
        echo "<option value=''>-- Pilih PIC --</option>";
                foreach ($datpic as $key => $st) :			
			    echo "<option value='$st->id_pic' set_select('pic_cust', $st->id_pic, isset($data->name_pic) && $data->name_pic == $st->id_pic) >
                            $st->name_pic
                            </option>";
                endforeach;
        echo "</select>";
    }
	
	
	 //Save using ajax
    public function saveNewInquiry()
    {
			$session = $this->session->userdata('app_session');
            $code= $this->Inquiry_model->generate_no_inquiry();
        
            $this->auth->restrict($this->addPermission);
			$tglinq =$this->input->post('tgl_inquiry');
			$time = strtotime($tglinq);
            $newformat = date('Y-m-d',$time);
			
			// print_r($this->input->post());
			// exit();

            $data = array(
                        'no_inquiry' => $code,
						'tgl_inquiry' => $newformat,
						'id_customer' => $this->input->post('customer'),
						'pic_customer' => $this->input->post('pic_cust'),
						'project'      => $this->input->post('project'),
						'ket_project' => $this->input->post('ket_project'),
						'id_sales'    => $this->input->post('sales'),
                        'created_on' => date('Y-m-d H:i:s'),
                        'created_by' => $session['id_user'],
                        'modified_on' => date('Y-m-d H:i:s'),
                        'modified_by' => $session['id_user'],
                        );
            
			$id = $this->Inquiry_model->insert($data);
			
			for($i=0;$i < count($this->input->post('kode_produk'));$i++){
            $datadetail = array(
                'no_inquiry'    => $code,
                'id_material'   => $this->input->post('kode_produk')[$i],
                'nama_material'   => $this->input->post('nama_produk')[$i],
				'thickness'   => $this->input->post('thickness')[$i],
				'length'   => $this->input->post('length')[$i],
				'width'   => $this->input->post('width')[$i],
                'density'   => $this->input->post('density')[$i],                
                'created_on'    => date('Y-m-d H:i:s'),
                'created_by'    => $session['id_user']
                );
             $this->db->insert('tr_inquiry_dt',$datadetail);
            
        }

            if (is_numeric($id)) {
                $keterangan = 'SUKSES, Simpan Data';
                $status = 1;
                $nm_hak_akses = $this->addPermission;
                $kode_universal = 'Newdata';
                $jumlah = 1;
                $sql = $this->db->last_query();

                $result = true;
                $barang = $id_barang;
            } else {
                $keterangan = 'GAGAL, Simpan Data';
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
                'inquiry' => $code,
				'tgl' => $this->input->post('tgl_inquiry'),
				'project' => $this->input->post('project'),
				'customer' => $this->input->post('customer'),
				'sales' => $this->input->post('sales'),
				'pic' => $this->input->post('pic_cust'),
                'save' => $result,
                );

        echo json_encode($param);
    }
	
	
	 public function saveEditInquiry()
    {
			$session = $this->session->userdata('app_session');
            $code= $this->Inquiry_model->generate_no_inquiry();
        
            $this->auth->restrict($this->addPermission);
			$noinquiry = $this->input->post('no_inquiry');
			
			// print_r($this->input->post());
			// exit();
			
			$where = array('no_inquiry' => $noinquiry);
			
            
			
            $data = array(
                        'no_inquiry' => $this->input->post('no_inquiry'),
						'id_customer' => $this->input->post('customer'),
						'pic_customer' => $this->input->post('pic_cust'),
						'project'      => $this->input->post('project'),
						'ket_project' => $this->input->post('ket_project'),
						'id_sales'    => $this->input->post('sales'),
                        'modified_on' => date('Y-m-d H:i:s'),
                        'modified_by' => $session['id_user'],
                        );
            
			$this->Inquiry_model->getUpdate('tr_inquiry_hd',$data,'no_inquiry',$noinquiry);
			
			$this->Inquiry_model->hapus($where,'tr_inquiry_dt');
			
			for($i=0;$i < count($this->input->post('kode_produk'));$i++){
            $datadetail = array(
                'no_inquiry'    => $this->input->post('no_inquiry'),
                'id_material'   => $this->input->post('kode_produk')[$i],
                'nama_material'   => $this->input->post('nama_produk')[$i],
				'thickness'   => $this->input->post('thickness')[$i],
				'length'   => $this->input->post('length')[$i],
				'width'   => $this->input->post('width')[$i],
                'density'   => $this->input->post('density')[$i],                
                'created_on'    => date('Y-m-d H:i:s'),
                'created_by'    => $session['id_user']
                );
             $id =  $this->db->insert('tr_inquiry_dt',$datadetail);
            
        }

            if ($id == true) {
                $keterangan = 'SUKSES, Simpan Data';
                $status = 1;
                $nm_hak_akses = $this->addPermission;
                $kode_universal = 'Newdata';
                $jumlah = 1;
                $sql = $this->db->last_query();

                $result = true;
                $barang = $id_barang;
            } else {
                $keterangan = 'GAGAL, Simpan Data';
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
                'inquiry' => $noinquiry,
				'tgl' => $this->input->post('tgl_inquiry'),
				'project' => $this->input->post('project'),
				'customer' => $this->input->post('customer'),
				'sales' => $this->input->post('sales'),
				'pic' => $this->input->post('pic_cust'),
				'sample' => $this->input->post('sample1'),
				'instrument' => $this->input->post('instrument'),
				'term' => $this->input->post('term'),
				'save' => $result,
				'id' => $id,
                );

        echo json_encode($param);
    }
	
	
    public function load_detail_old()
    {
        $noinquiry = $_GET['noinquiry'];
        
        $no = 1;
        $data = $this->Inquiry_model->get_data('tr_inquiry_dt','no_inquiry',$noinquiry);
        foreach ($data as $d) {
			
			
            echo "
			    <tr id='dataku$d->id'>
                <td>$d->id_material</td>
                <td>$d->nama_material</td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                 <a class='text-black' href='javascript:void(0)' title='Hapus' onclick=\"hapus_koli('".$d->id_koli."');\"><i class='fa fa-trash'></i>
                 <a class='text-black' href='javascript:void(0)' title='Edit' onclick=\"edit_koli('".$d->id_koli."');\"><i class='fa fa-pencil'></i>
                </a>
                </td>
                </tr>";
            $total += $d->qty;
            ++$no;
        }
      
    }

     public function load_detail_edit()
    {
        $noinquiry = $_GET['noinquiry'];
        
        $numb = 1;
        $data = $this->Inquiry_model->get_data('tr_inquiry_dt','no_inquiry',$noinquiry);
       
	   	$form_choice = Array ( '0' => SHEET, 
							   '1' =>COIL );
		
			
        echo "
		     <!-- form start-->
			<form action='#' method='POST' id='form-detail-coilsheet'>    
		    <table class='table table-bordered' width='100%'>";
			
			 foreach ($data as $d) {
				 
				$data_form = $this->db->query("SELECT * FROM tr_inquiry_dt_form  WHERE id_material='$d->id_material' AND no_inquiry='$noinquiry'");
				
				$row_cnt = $data_form->num_rows();
				
				$hasil = $data_form->result();
				// print_r($row_cnt);
				// exit;
				
				// if($d->id_material == $form->id_material && $form->no_inquiry == $d->no_inquiry ){
				if($row_cnt >0){	
					foreach($hasil as $form){
						$form1	= $form->form;	
						$length	= $form->length_f;	
						$width  = $form->width_f;
						$inner  = $form->inner_d;
						$kg_sheet  = $form->kg_sheet;
						$kg_roll   = $form->kg_roll;
						$qty_pcs   = $form->qty_pcs;
						$qty_roll   = $form->qty_roll;
						$total_kg   = separator($form->total_kg);
						$budget     = separator($form->cust_budget);
						$joint      = $form->joint;
						$max_joint     = $form->max_joint;
						
					}
				}
				else{
			            $form1	= '';	
						$length	= '';	
						$width  = '';	
						$inner  = '';	
						$kg_sheet  = '';	
						$kg_roll   = '';	
						$qty_pcs   = '';	
						$qty_roll   = '';	
						$total_kg   = '';	
						$budget     = '';	
						$joint      = '';	
						$max_joint     = '';	
				}
					
				
			    echo "
			 	<tr id='tr_$numb' >
				<td width='4%' class='text'><b>No</b><br>
				<!--<a class='text-red' href='javascript:void(0)' title='No Deal' onClick='delRow($numb)' '><i class='fa fa-times'></i>
				</a>-->
				$numb
				</td>
				 <td width='10%' class='text'><b>ID MATERIAL</b><br>
				 <input type='text' class='form-control input-sm' id='id_material_$numb'  name='data[$numb][id_material]' value='$d->id_material' placeholder='ID MATERIAL'  readonly>
               	 </td>
				 <td width='10%' class='text'><b>NAMA MATERIAL</b><br>
				 <input type='text' class='form-control input-sm' id='nama_material_$numb'  name='data[$numb][nama_material]' value='$d->nama_material' placeholder='NAMA MATERIAL' readonly >
               	 </td>
				  <td width='10%' class='text'><b>MS_LENGTH</b><br>
				  <input type='text' class='form-control input-sm' id='length_$numb'  name='data[$numb][length]' value='$d->length' placeholder='LENGTH'  readonly>
               	 <input type='hidden' class='form-control input-sm' id='hardness_$numb'  name='data[$numb][hardness]' value='$d->hardness' placeholder='HARDNESS'  readonly>
               	 </td>
				 <td width='10%' class='text'><b>MS_WIDTH</b><br>
				 <input type='text' class='form-control input-sm' id='width1_$numb'  name='data[$numb][width1]' value='$d->width' placeholder='WIDTH' readonly >
               	 </td>
				  <td width='10%' class='text'><b>THICKNESS</b><br>
				 <input type='text' class='form-control input-sm' id='thickness_$numb'  name='data[$numb][thickness]' value='$d->thickness' placeholder='THICKNESS' readonly >
               	 </td>
				 <td width='10%' class='text'><b>DENSITY</b><br>
				 <input type='text' class='form-control input-sm' id='density_$numb'  name='data[$numb][density]' value='$d->density' placeholder='THICKNESS' readonly >
               	 </td>
				             				
				</tr>
				<tr id='trx_$numb' >
				<td></td>
				<td width='10%' class='text'><b>FORM</b><br>
				  <select id='pilihform_$numb' data-nomor='$numb' name='data[$numb][pilihform]' class='form-control input-sm PilihForm'>
				   <option value=''  if ($form1 == '')selected >  --Pilih Form-- </option>
				   <option value='0' if ($form1 == '0')selected >  SHEET</option>
				   <option value='1' if ($form1 == '1')selected >  COIL</option>
				  </select> </td>
				 <td width='10%' class='text'><b>LENGTH</b><br>
				 <input type='text' class='form-control input-sm IsiLength' data-nomor='$numb' id='length2_$numb'  name='data[$numb][length2]' value='$length' placeholder='Length' >
               	 </td>	
				 <td width='10%' class='text'><b>WIDTH</b><br>
				 <input type='text' class='form-control input-sm IsiWidth' data-nomor='$numb' id='width2_$numb'  name='data[$numb][width2]' value='$width' placeholder='Width' >
               	 </td>
				 <td width='10%' class='text'><b>INNER DIAMETER</b><br>
				 <input type='text' class='form-control input-sm' id='inner_$numb'  name='data[$numb][inner]' value='$inner' placeholder='Inner Diameter' >
               	 </td>	
				 <td width='10%' class='text'><b>KG/SHEET</b><br>
				 <input type='text' class='form-control input-sm' id='kg_sheet_$numb'  name='data[$numb][kg_sheet]' value='$kg_sheet' placeholder='KG/SHEET' >
               	 </td>
				 <td width='10%' class='text'><b>KG/ROLL</b><br>
				 <input type='text' class='form-control input-sm' id='kg_roll_$numb'  name='data[$numb][kg_roll]' value='$kg_roll' placeholder='KG/ROLL' >
               	 </td>
				
				
				</tr>
				<tr id='trxx_$numb' >
				<td></td>
				<td width='10%' class='text'><b>QTY ORDER (PCS) </b><br>
				<input type='text' class='form-control input-sm QtyPcs' data-nomor='$numb' id='qty_pcs_$numb'  name='data[$numb][qty_pcs]' value='$qty_pcs' placeholder='QTY ORDER (PCS)' >
				</td>
				<td width='10%' class='text'><b>QTY ORDER (ROLL) </b><br>
				<input type='text' class='form-control input-sm QtyRoll' data-nomor='$numb' id='qty_roll_$numb'  name='data[$numb][qty_roll]' value='$qty_roll' placeholder='QTY ORDER (ROLL)' >
				</td>				
				<td width='10%' class='text'><b>TOTAL KG </b><br>
				<input type='text' class='form-control input-sm' id='total_kg_$numb'  name='data[$numb][total_kg]' value='$total_kg' placeholder='TOTAL KG' >
				</td>
				<td width='10%' class='text'><b>CUSTOMER BUDGET </b><br>
				<input type='text' class='form-control input-sm' id='budget_$numb'  name='data[$numb][budget]' value='$budget' placeholder='CUSTOMER BUDGET' >
				</td>
				  <td width='10%' class='text'>
				 </td>
				
				
				</tr>";
			 
            ++$numb;
        }
       
   		echo"</table> 
		     </form>
		   
			<!-- /.tab-content -->";
          

	}
	
	
	
	 public function load_detail()
    {
        $noinquiry = $_GET['noinquiry'];
        
        $numb = 1;
        $data = $this->Inquiry_model->get_data('tr_inquiry_dt','no_inquiry',$noinquiry);
       
			
        echo "
		     <!-- form start-->
			<form action='#' method='POST' id='form-detail-coilsheet'>    
		    <table class='table table-bordered' width='100%'>";
			
			 foreach ($data as $d) {
				 
			    echo "
			 	<tr id='tr_$numb' >
				<td width='4%' class='text'><b>No</b><br>
				<!--<a class='text-red' href='javascript:void(0)' title='No Deal' onClick='delRow($numb)' '><i class='fa fa-times'></i>
				</a>-->
				$numb
				</td>
				 <td width='10%' class='text'><b>ID MATERIAL</b><br>
				 <input type='text' class='form-control input-sm' id='id_material_$numb'  name='data[$numb][id_material]' value='$d->id_material' placeholder='ID MATERIAL'  readonly>
               	 </td>
				 <td width='10%' class='text'><b>NAMA MATERIAL</b><br>
				 <input type='text' class='form-control input-sm' id='nama_material_$numb'  name='data[$numb][nama_material]' value='$d->nama_material' placeholder='NAMA MATERIAL' readonly >
               	 </td>
				  <td width='10%' class='text'><b>MS_LENGTH</b><br>
				 <input type='text' class='form-control input-sm' id='length1_$numb'  name='data[$numb][length1]' value='$d->length' placeholder='LENGTH'  readonly>
               	 <input type='hidden' class='form-control input-sm' id='hardness_$numb'  name='data[$numb][hardness]' value='$d->hardness' placeholder='HARDNESS'  readonly>
               	 
				 </td>
				 <td width='10%' class='text'><b>MS_WIDTH</b><br>
				 <input type='text' class='form-control input-sm' id='mswidth1_$numb'  name='data[$numb][width1]' value='$d->width' placeholder='WIDTH' readonly >
               	 </td>
				  <td width='10%' class='text'><b>MS_THICKNESS</b><br>
				 <input type='text' class='form-control input-sm' id='thickness_$numb'  name='data[$numb][thickness]' value='$d->thickness' placeholder='THICKNESS' readonly >
               	 </td>
				  <td width='10%' class='text'><b>MS_DENSITY</b><br>
				 <input type='text' class='form-control input-sm' id='density_$numb'  name='data[$numb][density]' value='$d->density' placeholder='DENSITY'  readonly>
               	 </td>
				
					               				
				</tr>
				<tr id='trx_$numb' >
				<td></td>
				 <td width='10%' class='text'><b>FORM</b><br>
				  <select id='pilihform_$numb' data-nomor='$numb' name='data[$numb][pilihform]' class='form-control input-sm PilihForm'>
				   <option value='' set_select('pilihform','', isset($data->pilihform) && $data->pilihform == '');>Select An Option
				  </option>
				  <option value='0' set_select('pilihform','0', isset($data->pilihform) && $data->pilihform == '0');>SHEET
				  </option>
				  <option value='1' set_select('pilihform', '1', isset($data->pilihform) && $data->pilihform == '1');>COIL
				  </option>
				   </option> 
				  </select>  </td>
				 <td width='10%' class='text'><b>LENGTH</b><br>
				 <input type='text' class='form-control input-sm IsiLength' data-nomor='$numb' id='length2_$numb'  name='data[$numb][length2]' value='0' placeholder='Length' >
               	 </td>	
				 <td width='10%' class='text'><b>WIDTH</b><br>
				 <input type='text' class='form-control input-sm IsiWidth'  data-nomor='$numb' id='width2_$numb'  name='data[$numb][width2]' value='0' placeholder='Width' >
               	 </td>
				 <td width='10%' class='text'><b>INNER DIAMETER</b><br>
				 <input type='text' class='form-control input-sm' id='inner_$numb'  name='data[$numb][inner]' placeholder='Inner Diameter' >
               	 </td>	
				 <td width='10%' class='text'><b>KG/SHEET</b><br>
				 <input type='text' class='form-control input-sm' id='kg_sheet_$numb'  name='data[$numb][kg_sheet]' placeholder='KG/SHEET' >
               	 </td>
				 <td width='10%' class='text'><b>KG/ROLL</b><br>
				 <input type='text' class='form-control input-sm' id='kg_roll_$numb'  name='data[$numb][kg_roll]' placeholder='KG/ROLL' >
               	 </td> 
			
				
				</tr>
				<tr id='trxx_$numb' >
				<td></td>
				<td width='10%' class='text'><b>QTY ORDER (PCS) </b><br>
				<input type='text' class='form-control input-sm QtyPcs' data-nomor='$numb' id='qty_pcs_$numb'  name='data[$numb][qty_pcs]' placeholder='QTY ORDER (PCS)' >
				</td>
				<td width='10%' class='text'><b>QTY ORDER (ROLL) </b><br>
				<input type='text' class='form-control input-sm QtyRoll' data-nomor='$numb' id='qty_roll_$numb'  name='data[$numb][qty_roll]' placeholder='QTY ORDER (ROLL)' >
				</td>				
				<td width='10%' class='text'><b>TOTAL KG </b><br>
				<input type='text' class='form-control input-sm TotalKg' id='total_kg_$numb'  name='data[$numb][total_kg]' placeholder='TOTAL KG' readonly>
				</td>
				<td width='10%' class='text'><b>CUSTOMER BUDGET </b><br>
				<input type='text' class='form-control input-sm Budget' id='budget_$numb'  name='data[$numb][budget]' placeholder='CUSTOMER BUDGET' >
				</td>
				<!-- <td width='10%' class='text'><b>SAMBUNGAN COIL</b><br>
				  <select id='sambungan' data-nomor='$numb' name='data[$numb][sambungan]' class='form-control input-sm PilihSambungan'>
				   <option value='' set_select('sambungan','', isset($data->sambungan) && $data->sambungan == '');>Select An Option
				  </option>
				  <option value='1' set_select('sambungan','1', isset($data->sambungan) && $data->sambungan == '1');>Joint
				  </option>
				  <option value='2' set_select('sambungan', '2', isset($data->sambungan) && $data->sambungan == '2');>Marking
				  </option>
				  <option value='3' set_select('sambungan', '3', isset($data->sambungan) && $data->sambungan == '3');>Tidak Joint
				  </option>
				  <option value='4' set_select('sambungan', '4', isset($data->sambungan) && $data->sambungan == '4');>Tidak Marking
				  </option>
				  <option value='5' set_select('sambungan', '5', isset($data->sambungan) && $data->sambungan == '5');>Tidak Joint Dan Marking
				  </option>
				     
				  </select>  </td>
				 <td width='10%' class='text'><b>MAX JOINT/MARKING</b><br>
				 <input type='text' class='form-control input-sm' id='max_sambungan_$numb'  name='data[$numb][max_sambungan]' placeholder='MAXIMUM JOINT/MARKING' >
               	 </td>-->
				 <td width='10%' class='text'></td>
				 <td width='10%' class='text'></td>
				 
				</tr>";
			 
            ++$numb;
        }
       
   		echo"</table> 
		     </form>
		   
			<!-- /.tab-content -->";
          

	}
	
	 //Save using ajax
    public function saveNewForm()
    {
		$session = $this->session->userdata('app_session');
        $this->auth->restrict($this->addPermission);
	 
			
        $numb =0;
		foreach($_POST['data'] as $d){
		$numb++;	
		
		  
			
		 $this->auth->restrict($this->addPermission);

              $data =  array(
			                    'no_inquiry'=>$this->input->post('noinquiry'),
								'id_material'=>$d[id_material],
								'nama_material'=>$d[nama_material],
								'form'=>$d[pilihform],
								'thickness'=>$d[thickness],
								'length'=>$d[length1],
								'width'=>$d[width1],
								'density'=>$d[density],
								'length_f'=>$d[length2],
								'width_f'=>$d[width2],
								'inner_d'=>$d[inner],
								'kg_sheet'=>$d[kg_sheet],
								'kg_roll'=>$d[kg_roll],
								'qty_pcs'=>$d[qty_pcs],
								'qty_roll'=>$d[qty_roll],
								'total_kg'=>str_replace(".","",$d[total_kg]),
								'cust_budget'=>str_replace(".","",$d[budget]),
								'joint'=>$d[sambungan],
								'max_joint'=>$d[max_sambungan],
								'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $session['id_user'],
                                
                            );
							
			 
        	 
			 
            //Add Data
              $this->db->insert('tr_inquiry_dt_form',$data);
			
		    }
		
			       $data_update = array(
                               	'sample'=>$this->input->post('sample'),
								'pay_instrument'=>$this->input->post('pay_instrument'),
								'pay_term'=>$this->input->post('pay_term'),
								);
				   $where = array(
				
                            'no_inquiry'=>$this->input->post('noinquiry'),
						   );

			  $id = $this->Inquiry_model->getUpdate('tr_inquiry_hd',$data_update,$where);
              
			  // print_r($id);
			  // exit();
			 
			 
			$jumlahx = count($_POST['pembayaran']);
			if ($_POST['opsi_top'] == 'persen') {
				for ($i = 0; $i < $jumlahx; ++$i) {
				++$no;
				$tglbayar =$_POST['perkiraan_bayar'][$i];
				$timebayar = strtotime($tglbayar);
				$formattgl = date('Y-m-d',$timebayar);
			
				$detil = array(
				  'no_inquiry'      => $this->input->post('noinquiry'),
				  'nominal'         => $_POST['pembayaran'][$i],
				  'perkiraan_bayar' => $formattgl,
				  'status' 			=> 'open',
				  'tipe_payment'    => 'persen'
				);
			  //Add Data
              $this->db->insert('tr_inquiry_payment',$detil);
				}
			}else {
				for ($i = 0; $i < $jumlahx; ++$i) {
				++$no;
				$tglbayar =$_POST['perkiraan_bayar'][$i];
				$timebayar = strtotime($tglbayar);
				$formattgl = date('Y-m-d',$timebayar);
				$detil = array(
				  'no_inquiry'      => $this->input->post('noinquiry'),
				  'nominal'         => $_POST['pembayaran'][$i],
				  'perkiraan_bayar' => $formattgl,
				  'status'          => 'open',
				  'tipe_payment'    => 'nominal'
				  
				);
				 $this->db->insert('tr_inquiry_payment',$detil);
				}
			}
			
			
			$jumlahxx = count($_POST['qty_kirim']);
			if ($_POST['opsi_top'] == 'persen') {
				for ($ix = 0; $ix < $jumlahxx; ++$ix) {
				++$no;
				$tglbayar1 = $_POST['perkiraan_kirim'][$ix];
				$timebayar1 = strtotime($tglbayar1);
				$formattgl1 = date('Y-m-d',$timebayar1);
				$detil2 = array(
				  'no_inquiry'      => $this->input->post('noinquiry'),
				  'qty_kirim'         => $_POST['qty_kirim'][$ix],
				  'perkiraan_kirim' => $formattgl1,
				  'status' 			=> 'open',
				  
				);
			  //Add Data
              $this->db->insert('tr_inquiry_delivery',$detil2);
				}
			}else {
				for ($ix = 0; $ix < $jumlahxx; ++$ix) {
				++$no;
				$tglbayar1 = $_POST['perkiraan_kirim'][$ix];
				$timebayar1 = strtotime($tglbayar1);
				$formattgl1 = date('Y-m-d',$timebayar1);
				
				$detil2 = array(
				  'no_inquiry'      => $this->input->post('noinquiry'),
				  'qty_kirim'         => $_POST['qty_kirim'][$ix],
				  'perkiraan_kirim'   => $formattgl1,
				  'status'          => 'open',
								  
				);
				
				
				
				 $this->db->insert('tr_inquiry_delivery',$detil2);
				}
			}

        if ($id==1) {
			    $keterangan = 'SUKSES, Simpan Data';
                $status = 1;
                $nm_hak_akses = $this->addPermission;
                $kode_universal = 'Newdata';
                $jumlah = 1;
                $sql = $this->db->last_query();

                $result = true;
                $barang = $id_barang;
			    
               
            } else {
                
				
				$keterangan = 'GAGAL, tambah data Barang '.$id_barang.', atas Nama : '.$nm_barang;
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
	
	
	
	
	
	 //SIMPAN EDIT DATA FORM
    public function saveEditForm()
    {
		$session = $this->session->userdata('app_session');
        $this->auth->restrict($this->addPermission);
	 
	    // print_r($this->input->post());
		// exit();
			  
		$noinquiry = $this->input->post('no_inquiry'); 
        $numb =0;
		foreach($_POST['data'] as $d){
		$numb++;	
		
		$where = array('no_inquiry' => $noinquiry);
		$this->Inquiry_model->hapus($where,'tr_inquiry_dt_form');
			
		 $this->auth->restrict($this->addPermission);
		       $kg = str_replace(".","",$d[total_kg]);
               $budget =str_replace(".","",$d[budget]);
			 
              $data =  array( 
			                    'no_inquiry'=>$this->input->post('noinquiry'), 
								'id_material'=>$d[id_material],
								'nama_material'=>$d[nama_material],
								'form'=>$d[pilihform],
								'length_f'=>$d[length2],
								'width_f'=>$d[width2],
								'inner_d'=>$d[inner],
								'kg_sheet'=>$d[kg_sheet],
								'kg_roll'=>$d[kg_roll],
								'qty_pcs'=>$d[qty_pcs],
								'qty_roll'=>$d[qty_roll],
								'total_kg'=>$kg,
								'cust_budget'=>$budget,
								'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $session['id_user'],
                                
                            );
							
			 
        	 
			 
            //Add Data
              $this->db->insert('tr_inquiry_dt_form',$data);
			
		    }
		
			       $data_update = array(
                               	'sample'=>$this->input->post('sample'),
								'pay_instrument'=>$this->input->post('pay_instrument'),
								'pay_term'=>$this->input->post('pay_term'),
								);
				   $where = array(
				
                            'no_inquiry'=>$this->input->post('noinquiry'),
						   );

			  $id = $this->Inquiry_model->getUpdate('tr_inquiry_hd',$data_update,$where);
              
			  // 
			  
			  
		$this->Inquiry_model->hapus($where,'tr_inquiry_payment');	

        $numb1 =0;
		foreach($_POST['data1'] as $d1){
		$numb1++;	
		        $tglbayar   = $d1[perkiraan_bayar];
		      	$timebayar = strtotime($tglbayar);
				$tglbyr = date('Y-m-d',$timebayar);
              $data1 =  array(
			                    'no_inquiry'=>$this->input->post('noinquiry'),
								'nominal'=>$d1[pembayaran],
								'perkiraan_bayar'=>$tglbyr,
								'status' 	   => 'open',
				                'tipe_payment' => $d1[tipe_payment],
								'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $session['id_user'],
								'modified_on' => date('Y-m-d H:i:s'),
								'modified_by' => $session['id_user'],
                                
                            );
							
			 
        	 
			 
            //Add Data
              $this->db->insert('tr_inquiry_payment',$data1);
			
		    }			
			  
			
			
			
			$this->Inquiry_model->hapus($where,'tr_inquiry_delivery');	
			$numb2 =0;
			foreach($_POST['data2'] as $d2){
			$numb2++;	
			  
			  $tglkirim   = $d2[perkiraan_kirim];
		      $timebayar1 = strtotime($tglkirim);
			  $formattgl1 = date('Y-m-d',$timebayar1);
              $data2 =  array(
			                    'no_inquiry'=>$this->input->post('noinquiry'),
								'qty_kirim'=>$d2[qty_kirim],
								'perkiraan_kirim'=>$formattgl1,
								'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $session['id_user'],
								'modified_on' => date('Y-m-d H:i:s'),
								'modified_by' => $session['id_user'],
                                
                            );
							
			 
        	 
			 
            //Add Data
              $this->db->insert('tr_inquiry_delivery',$data2);
			
		    }			
            if ($id==1) {
			    $keterangan = 'SUKSES, Simpan Data';
                $status = 1;
                $nm_hak_akses = $this->addPermission;
                $kode_universal = 'Newdata';
                $jumlah = 1;
                $sql = $this->db->last_query();

                $result = true;
                $barang = $id_barang;
			    
               
            } else {
                
				
				$keterangan = 'GAGAL, tambah data Barang '.$id_barang.', atas Nama : '.$nm_barang;
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
	
	public function viewInquiry(){
		$this->auth->restrict($this->viewPermission);
		$noinquiry 	= $this->input->post('noinquiry');
		$inquiry 	= $this->Inquiry_model->viewInquiry($noinquiry);
		$form       = $this->Inquiry_model->get_data('tr_inquiry_dt_form','no_inquiry',$noinquiry);
		$payment       = $this->Inquiry_model->get_data('tr_inquiry_payment','no_inquiry',$noinquiry);
		$delivery      = $this->Inquiry_model->get_data('tr_inquiry_delivery','no_inquiry',$noinquiry);
		
		$data = [
			'inquiry' => $inquiry,
			'form'    => $form,
			'payment' => $payment,
			'delivery' => $delivery,
			//'pic' => $pic,
			];
		$this->template->set('results', $data);
		
		$this->template->render('modalViewInquiry');
	}
				  

}
 
?>

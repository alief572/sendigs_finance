<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cycletime_custom extends Admin_Controller
{

  protected $viewPermission   = 'Cycletime_Custom.View';
  protected $addPermission    = 'Cycletime_Custom.Add';
  protected $managePermission = 'Cycletime_Custom.Manage';
  protected $deletePermission = 'Cycletime_Custom.Delete';

  public function __construct()
  {
    parent::__construct();

    // $this->load->library(array( 'upload', 'Image_lib'));
    $this->load->model(array(
      'Cycletime_custom/Cycletime_custom_model',
      'Aktifitas/aktifitas_model',
    ));

    date_default_timezone_set('Asia/Bangkok');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session = $this->session->userdata('app_session');

    $getBy      = "SELECT created_by, created_date FROM cycletime_fast ORDER BY created_date DESC LIMIT 1";
    $restgetBy  = $this->db->query($getBy)->result_array();

    $datax = array(
      'get_by'    => $restgetBy
    );

    history("View index cycletime custom");
    $this->template->title('Cycletime Product Custom');
    $this->template->render('index', $datax);
  }

  public function data_side_cycletime()
  {
    $this->Cycletime_custom_model->get_json_cycletime();
  }

  public function add($id = null)
  {
    $session = $this->session->userdata('app_session');

    $product      = $this->db->select('a.id_product as code_lv4, b.nama')->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'join')->group_by('a.id_product')->get_where('bom_header a', array('a.deleted_date' => NULL, 'a.category' => 'grid custom', 'a.id_product !=' => NULL))->result();
    $costcenter  = $this->db->query("SELECT * FROM ms_costcenter WHERE deleted='0' ORDER BY nama_costcenter ASC ")->result_array();
    $machine  = $this->db->query("SELECT * FROM asset WHERE category='4' AND deleted_date IS NULL GROUP BY SUBSTR(kd_asset, 1, 20) ORDER BY nm_asset ASC ")->result_array();
    $mould  = $this->db->query("SELECT * FROM asset WHERE category='7' AND deleted_date IS NULL GROUP BY SUBSTR(kd_asset, 1, 20) ORDER BY nm_asset ASC ")->result_array();


    $ArrlistCT = $this->db->get_where('cycletime_custom_header', array('deleted_date' => NULL))->result_array();
    $ArrProductCT = [];
    foreach ($ArrlistCT as $key => $value) {
      $ArrProductCT[] = $value['id_product'];
    }

    //data
    $header     = $this->db->get_where('cycletime_custom_header', array('id_time' => $id))->result_array();
    $detail     = $this->db->not_like('category', 'addBO')->get_where('cycletime_custom_detail_header', array('id_time' => $id))->result_array();
    $detailBOH  = $this->db->like('category', 'addBO')->get_where('cycletime_custom_detail_header', array('id_time' => $id))->result_array();
    //listBOM
    $id_product = (!empty($header[0]['id_product'])) ? $header[0]['id_product'] : 0;
    $no_bom = (!empty($header[0]['no_bom'])) ? $header[0]['no_bom'] : 0;

    $ArrlistCT = $this->db->group_by('no_bom')->get_where('cycletime_custom_header', array('deleted_date' => NULL))->result_array();
    $ArrProductCT = [];
    foreach ($ArrlistCT as $key => $value) {
      if ($value['no_bom'] != $no_bom) {
        $ArrProductCT[] = $value['no_bom'];
      }
    }
    $result  = $this->db->select('a.*,b.nama')->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')->get_where('bom_header a', array('a.id_product' => $id_product, 'a.deleted_date' => NULL, 'a.category' => 'grid custom'))->result_array();

    $ArrBOM = [];
    if (!empty($result)) {
      foreach ($result as $val => $valx) {
        if (!in_array($valx['no_bom'], $ArrProductCT)) {
          $variant_product  = (!empty($valx['variant_product'])) ? ' - ' . $valx['variant_product'] : '';
          $warna_product    = (!empty($valx['color'])) ? ' - ' . $valx['color'] : '';
          $surface_product  = (!empty($valx['surface'])) ? ' - ' . $valx['surface'] : '';
          $nama_bom         = strtoupper($valx['nama'] . $variant_product . $warna_product . $surface_product);

          $ArrBOM[$val]['no_bom'] = $valx['no_bom'];
          $ArrBOM[$val]['nama']   = $nama_bom;
        }
      }
    }

    $data = [
      'header' => $header,
      'detail' => $detail,
      'detailBOH' => $detailBOH,
      'product' => $product,
      'mesin' => $machine,
      'mould' => $mould,
      'ArrBOM' => $ArrBOM,
      'costcenter' => $costcenter,
      'ArrProductCT' => $ArrProductCT,
    ];

    $this->template->title('Add Cycletime Product Custom');
    $this->template->render('add', $data);
  }

  public function view()
  {
    $this->auth->restrict($this->viewPermission);
    $id   = $this->input->post('id');
    $header = $this->db->get_where('cycletime_custom_header', array('id_time' => $id))->result();
    // print_r($header);
    $data = [
      'header' => $header
    ];
    $this->template->set('results', $data);
    $this->template->render('view', $data);
  }

  public function get_add()
  {
    $id         = $this->uri->segment(3);
    $className   = $this->uri->segment(4);
    $no   = 0;

    $costcenter  = $this->db->query("SELECT * FROM ms_costcenter WHERE deleted='0' ORDER BY nama_costcenter ASC ")->result_array();
    $d_Header = "";
    // $d_Header .= "<tr>";
    $d_Header .= "<tr class='header" . $className . "_" . $id . "'>";
    $d_Header .= "<td align='center'>" . $id . "</td>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<select name='" . $className . "[" . $id . "][costcenter]' class='chosen-select form-control input-sm inline-blockd costcenter'>";
    $d_Header .= "<option value='0'>Select Costcenter</option>";
    foreach ($costcenter as $val => $valx) {
      $d_Header .= "<option value='" . $valx['id_costcenter'] . "'>" . strtoupper($valx['nama_costcenter']) . "</option>";
    }
    $d_Header .=     "</select>";
    $d_Header .= "</td>";
    $d_Header .= "<td></td>";
    $d_Header .= "<td></td>";
    $d_Header .= "<td></td>";
    $d_Header .= "<td></td>";
    $d_Header .= "<td align='center'>";
    $d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
    $d_Header .= "</td>";
    $d_Header .= "</tr>";

    //add nya
    $d_Header .= "<tr id='" . $className . "_" . $id . "_" . $no . "' class='header" . $className . "_" . $id . "'>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-classname='" . $className . "' class='btn btn-sm btn-primary addSubPart' title='Add Process'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Process</button></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "</tr>";

    //add part
    $d_Header .= "<tr id='" . $className . "_" . $id . "'>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-classname='" . $className . "' class='btn btn-sm btn-warning addPart' title='Add Costcenter'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Costcenter</button></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "</tr>";

    echo json_encode(array(
      'header'      => $d_Header,
    ));
  }

  public function get_add_sub()
  {
    $id   = $this->uri->segment(3);
    $no   = $this->uri->segment(4);
    $className   = $this->uri->segment(5);

    $machine  = $this->db->query("SELECT * FROM asset WHERE category='4' AND deleted_date IS NULL GROUP BY SUBSTR(kd_asset, 1, 20) ORDER BY nm_asset ASC ")->result_array();
    $mould  = $this->db->query("SELECT * FROM asset WHERE category='7' AND deleted_date IS NULL GROUP BY SUBSTR(kd_asset, 1, 20) ORDER BY nm_asset ASC ")->result_array();


    // $process	= $this->db->query("SELECT * FROM ms_process ORDER BY nm_process ASC ")->result_array();
    // echo $qListResin; exit;
    $d_Header = "";
    // $d_Header .= "<tr>";
    $d_Header .= "<tr class='header" . $className . "_" . $id . "'>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='left' style='vertical-align:middle; padding-left: 30px;'>";
    $d_Header .= "<b>Tipe Cycletime</b>";
    $d_Header .= "<div class='radio'>";
    $d_Header .= "<label>";
    $d_Header .= "<input type='radio' class='tipe' name='" . $className . "[" . $id . "][detail][" . $no . "][tipe]' value='production' checked>";
    $d_Header .= "Cycletime Production";
    $d_Header .= "</label>";
    $d_Header .= "<label>";
    $d_Header .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' class='tipe' name='" . $className . "[" . $id . "][detail][" . $no . "][tipe]' value='setting'>";
    $d_Header .= "Cycletime Setting";
    $d_Header .= "</label>";
    $d_Header .= "</div>";
    $d_Header .= "<b>Process Name</b>";
    $d_Header .= "<input type='text' name='" . $className . "[" . $id . "][detail][" . $no . "][process]' class='form-control input-md process' placeholder='Process Name'>";
    $d_Header .= "<b>Machine</b>";
    $d_Header .= "<select name='" . $className . "[" . $id . "][detail][" . $no . "][machine]' class='chosen-select form-control input-sm inline-blockd'>";
    $d_Header .= "<option value='0'>Select Machine</option>";
    foreach ($machine as $val => $valx) {
      $d_Header .= "<option value='" . $valx['kd_asset'] . "'>" . strtoupper($valx['nm_asset']) . "</option>";
    }
    $d_Header .= "<option value='0'>NONE MACHINE</option>";
    $d_Header .=   "</select>";
    $d_Header .= "<b>Mould / Tools</b>";
    $d_Header .= "<select name='" . $className . "[" . $id . "][detail][" . $no . "][mould]' class='chosen-select form-control input-sm inline-blockd'>";
    $d_Header .= "<option value='0'>Select Mould/Tools</option>";
    foreach ($mould as $val => $valx) {
      $d_Header .= "<option value='" . $valx['kd_asset'] . "'>" . strtoupper($valx['nm_asset']) . "</option>";
    }
    $d_Header .= "<option value='0'>NONE MOULD/TOOLS</option>";
    $d_Header .=     "</select>";
    $d_Header .= "<br><br><br></td>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<b>Cycletime (minutes)</b>";
    $d_Header .= "<input type='text' name='" . $className . "[" . $id . "][detail][" . $no . "][cycletime]' class='form-control input-md maskM cycletime' placeholder='Cycletime (Minutes)' >";
    $d_Header .= "</td>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<b>Man Power</b>";
    $d_Header .= "<input type='text' name='" . $className . "[" . $id . "][detail][" . $no . "][qty_mp]' class='form-control input-md maskM' placeholder='Qty Man Power'  data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
    $d_Header .= "</td>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<b>Information</b>";
    $d_Header .= "<input type='text' name='" . $className . "[" . $id . "][detail][" . $no . "][note]' class='form-control input-md' placeholder='Information'  data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''>";
    $d_Header .= "</td>";
    $d_Header .= "<td align='left'>";
    $d_Header .= "<b>VA</b><br>";
    $d_Header .= "<select name='" . $className . "[" . $id . "][detail][" . $no . "][va]' class='chosen-select form-control input-sm inline-blockd'>";
    $d_Header .= "<option value='0'>Select VA</option>";
    $d_Header .= "<option value='Y'>Value Added</option>";
    $d_Header .= "<option value='N'>Non Value Added</option>";
    $d_Header .= "</select>";
    $d_Header .= "</td>";
    $d_Header .= "<td align='center'>";
    $d_Header .= "&nbsp;<button type='button' class='btn btn-sm btn-danger delSubPart' title='Delete Part'><i class='fa fa-close'></i></button>";
    $d_Header .= "</td>";
    $d_Header .= "</tr>";

    //add nya
    $d_Header .= "<tr id='" . $className . "_" . $id . "_" . $no . "' class='header" . $className . "_" . $id . "'>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-classname='" . $className . "' class='btn btn-sm btn-primary addSubPart' title='Add Process'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Process</button></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "<td align='center'></td>";
    $d_Header .= "</tr>";

    echo json_encode(array(
      'header'      => $d_Header,
    ));
  }

  public function save_cycletime()
  {

    $Arr_Kembali  = array();
    $data      = $this->input->post();
    // print_r($data);
    // exit;
    $session     = $this->session->userdata('app_session');
    $Ym          = date('ym');
    $dateTime   = date('Y-m-d H:i:s');

    $idKEY        = $data['id_time'];
    $id_material  = $data['id_time'];
    //pengurutan kode
    if (empty($idKEY)) {
      $srcMtr        = "SELECT MAX(id_time) as maxP FROM cycletime_custom_header WHERE id_time LIKE 'TX-" . $Ym . "%' ";
      $numrowMtr    = $this->db->query($srcMtr)->num_rows();
      $resultMtr    = $this->db->query($srcMtr)->result_array();
      $angkaUrut2    = $resultMtr[0]['maxP'];
      $urutan2      = (int)substr($angkaUrut2, 7, 3);
      $urutan2++;
      $urut2        = sprintf('%03s', $urutan2);
      $id_material  = "TX-" . $Ym . $urut2;
    }

    $ArrHeader    = array(
      'id_time'      => $id_material,
      'id_product'  => $data['produk'],
      'no_bom'      => $data['no_bom'],
      'total_ct_setting'  => str_replace(',', '', $data['total_ct_setting']),
      'total_ct_produksi'  => str_replace(',', '', $data['total_ct_produksi']),
      'moq'                => str_replace(',', '', $data['moq']),
      'created_by'        => $session['id_user'],
      'created_date'      => $dateTime
    );

    $listBOM = (!empty($data['listBOM'])) ? $data['listBOM'] : [];
    $ArrName = ['addJoint', 'addFlatSheet', 'addEndPlate', 'addChequeredPlate', 'addOthers'];

    $ArrLooping = array_merge($listBOM, $ArrName);

    // print_r($ArrLooping);
    // exit;

    $ArrDetailH  = array();
    $ArrDetailD  = array();
    foreach ($ArrLooping as $value) {
      # code...
      if (!empty($data[$value])) {
        foreach ($data[$value] as $val => $valx) {
          $UNIQ = $value . $val;

          $urut = sprintf('%02s', $val);
          $ArrDetailH[$UNIQ]['id_time']       = $id_material;
          $ArrDetailH[$UNIQ]['id_costcenter'] = $id_material . "-" . $urut . "-" . $value;
          $ArrDetailH[$UNIQ]['costcenter']     = $valx['costcenter'];
          $ArrDetailH[$UNIQ]['category']       = $value;
          foreach ($valx['detail'] as $val2 => $valx2) {
            $ArrDetailD[$val2 . $UNIQ]['id_time']       = $id_material;
            $ArrDetailD[$val2 . $UNIQ]['id_costcenter'] = $id_material . "-" . $urut . "-" . $value;
            $ArrDetailD[$val2 . $UNIQ]['tipe']           = $valx2['tipe'];
            $ArrDetailD[$val2 . $UNIQ]['nm_process']     = $valx2['process'];
            $ArrDetailD[$val2 . $UNIQ]['cycletime']     = str_replace(',', '', $valx2['cycletime']);
            $ArrDetailD[$val2 . $UNIQ]['qty_mp']         = str_replace(',', '', $valx2['qty_mp']);
            $ArrDetailD[$val2 . $UNIQ]['note']           = $valx2['note'];
            $ArrDetailD[$val2 . $UNIQ]['machine']       = $valx2['machine'];
            $ArrDetailD[$val2 . $UNIQ]['mould']         = $valx2['mould'];
            $ArrDetailD[$val2 . $UNIQ]['va']             = $valx2['va'];
          }
        }
      }
    }

    // print_r($ArrHeader);
    // print_r($ArrDetailH);
    // print_r($ArrDetailD);
    // exit;

    $this->db->trans_start();
    if (empty($idKEY)) {
      $this->db->insert('cycletime_custom_header', $ArrHeader);
    } else {
      $this->db->where('id_time', $idKEY);
      $this->db->update('cycletime_custom_header', $ArrHeader);
    }

    $this->db->where('id_time', $idKEY);
    $this->db->delete('cycletime_custom_detail_header');

    $this->db->where('id_time', $idKEY);
    $this->db->delete('cycletime_custom_detail_detail');

    if (!empty($ArrDetailH)) {
      $this->db->insert_batch('cycletime_custom_detail_header', $ArrDetailH);
    }
    if (!empty($ArrDetailD)) {
      $this->db->insert_batch('cycletime_custom_detail_detail', $ArrDetailD);
    }
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $Arr_Data  = array(
        'pesan'    => 'Save gagal disimpan ...',
        'status'  => 0
      );
    } else {
      $this->db->trans_commit();
      $Arr_Data  = array(
        'pesan'    => 'Save berhasil disimpan. Thanks ...',
        'status'  => 1
      );
      history("Insert cycletime product custom" . $id_material);
    }

    echo json_encode($Arr_Data);
  }

  public function list_center()
  {
    $id = $this->uri->segment(3);
    $query     = "SELECT * FROM ms_costcenter WHERE id_dept='" . $id . "' ORDER BY nama_costcenter ASC";
    $Q_result  = $this->db->query($query)->result();
    $option   = "<option value='0'>Select an Option</option>";
    foreach ($Q_result as $row) {
      $option .= "<option value='" . $row->nama_costcenter . "'>" . strtoupper($row->nama_costcenter) . "</option>";
    }
    echo json_encode(array(
      'option' => $option
    ));
  }

  public function delete_cycletime()
  {

    $Arr_Kembali  = array();
    $data          = $this->input->post();
    // print_r($data);
    // exit;
    $session        = $this->session->userdata('app_session');
    $id_material   = $data['id'];

    $ArrHeader      = array(
      'deleted'      => "Y",
      'deleted_by'  => $session['id_user'],
      'deleted_date'  => date('Y-m-d H:i:s')
    );

    $this->db->trans_start();
    $this->db->where('id_time', $id_material);
    $this->db->delete('cycletime_custom_header');

    $this->db->where('id_time', $id_material);
    $this->db->delete('cycletime_custom_detail_header');

    $this->db->where('id_time', $id_material);
    $this->db->delete('cycletime_custom_detail_detail');
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $Arr_Data  = array(
        'pesan'    => 'Delete gagal disimpan ...',
        'status'  => 0
      );
    } else {
      $this->db->trans_commit();
      $Arr_Data  = array(
        'pesan'    => 'Delete berhasil disimpan. Thanks ...',
        'status'  => 1
      );
      history("Delete cycletime " . $id_material);
    }

    echo json_encode($Arr_Data);
  }

  public function get_list_bom()
  {
    $id_product = $this->input->post('id_product');

    $ArrlistCT = $this->db->group_by('no_bom')->get_where('cycletime_custom_header', array('deleted_date' => NULL))->result_array();
    $ArrProductCT = [];
    foreach ($ArrlistCT as $key => $value) {
      $ArrProductCT[] = $value['no_bom'];
    }

    $ArrCategory = ['grid standard', 'standard', 'ftackel'];

    $result  = $this->db->select('a.*,b.nama')->join('new_inventory_4 b', 'a.id_product=b.code_lv4', 'left')->get_where('bom_header a', array('a.id_product' => $id_product, 'a.deleted_date' => NULL, 'a.category' => 'grid custom'))->result_array();
    // print_r($result);
    // print_r($ArrProductCT);
    // exit;
    if (!empty($result)) {
      $option  = "";
      $option  = "<option value='0'>Select BOM</option>";
      foreach ($result as $val => $valx) {
        if (!in_array($valx['no_bom'], $ArrProductCT)) {
          $variant_product  = (!empty($valx['variant_product'])) ? ' - ' . $valx['variant_product'] : '';
          $warna_product    = (!empty($valx['color'])) ? ' - ' . $valx['color'] : '';
          $surface_product  = (!empty($valx['surface'])) ? ' - ' . $valx['surface'] : '';
          $option .= "<option value='" . $valx['no_bom'] . "'>" . strtoupper($valx['nama'] . $variant_product . $warna_product . $surface_product) . "</option>";
        }
      }
    } else {
      $option  = "<option value='0'>BOM Not Found</option>";
    }

    $ArrJson  = array(
      'option' => $option
    );
    // exit;
    echo json_encode($ArrJson);
  }

  public function get_bom_product_custom()
  {
    $no_bom         = $this->uri->segment(3);
    $no   = 0;

    $listProduct  = $this->db->get_where('bom_detail a', array('no_bom' => $no_bom, 'category' => 'hi grid std'))->result_array();

    $d_Header = "";
    $nomor = 0;
    foreach ($listProduct as $key => $value) {
      $nomor++;
      $no_bom = $value['code_material'];
      $GetNameBOM = get_name_product_by_bom($no_bom);
      $NameBOM = (!empty($GetNameBOM[$no_bom])) ? $GetNameBOM[$no_bom] : '-';

      $d_Header .= "<tr>";
      $d_Header .= "<td align='center'>#</td>";
      $d_Header .= "<td align='left'>" . $NameBOM . "<input type='hidden' name='listBOM[]' value='add" . $no_bom . "'></td>";
      $d_Header .= "<td></td>";
      $d_Header .= "<td></td>";
      $d_Header .= "<td></td>";
      $d_Header .= "<td></td>";
      $d_Header .= "<td></td>";
      $d_Header .= "</tr>";

      $d_Header .= "<tr id='add" . $no_bom . "_0'>";
      $d_Header .= "<td align='center'></td>";
      $d_Header .= "<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' data-classname='add" . $no_bom . "' class='btn btn-sm btn-warning addPart' title='Add Costcenter'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Costcenter</button></td>";
      $d_Header .= "<td align='center'></td>";
      $d_Header .= "<td align='center'></td>";
      $d_Header .= "<td align='center'></td>";
      $d_Header .= "<td align='center'></td>";
      $d_Header .= "<td align='center'></td>";
      $d_Header .= "</tr>";
    }

    echo json_encode(array(
      'header'      => $d_Header,
    ));
  }
}

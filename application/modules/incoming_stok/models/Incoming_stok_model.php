<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Incoming_stok_model extends BF_Model
{

  protected $ENABLE_ADD;
  protected $ENABLE_MANAGE;
  protected $ENABLE_VIEW;
  protected $ENABLE_DELETE;

  protected $accounting;
  protected $hris;
  protected $consultant;

  public function __construct()
  {
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Incoming_Stok.Add');
    $this->ENABLE_MANAGE  = has_permission('Incoming_Stok.Manage');
    $this->ENABLE_VIEW    = has_permission('Incoming_Stok.View');
    $this->ENABLE_DELETE  = has_permission('Incoming_Stok.Delete');

    $this->accounting = $this->load->database('accounting', true);
    $this->hris = $this->load->database('hris', true);
    $this->consultant = $this->load->database('consultant', true);
  }

  public function generate_id_invoice_jurnal($nomor)
  {
    $Ym             = date('ym');
    $srcMtr            = "SELECT MAX(no_jurnal) as maxP FROM tr_jurnal WHERE no_jurnal LIKE '%" . int_to_roman(date('m')) . "-" . date('-y') . "%' ";
    $resultMtr        = $this->db->query($srcMtr)->result_array();
    $angkaUrut2        = $resultMtr[0]['maxP'];
    $urutan2        = (int)substr($angkaUrut2, 0, 5);
    $urutan2 = $urutan2 + $nomor;
    $urut2            = sprintf('%05s', $urutan2);
    $kode_trans        = $urut2 . '-AJV-' . int_to_roman(date('m')) . '-' . date('y');

    return $kode_trans;
  }

  //request material
  public function data_side_request_material()
  {
    $controller      = ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_json_request_material(
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData      = $fetch['totalData'];
    $totalFiltered  = $fetch['totalFiltered'];
    $query          = $fetch['query'];

    $data  = array();
    $urut1  = 1;
    $urut2  = 0;
    $GET_USER = get_list_user();
    $GET_SUM_BERAT = getTotalBeratSPKSOInternal();
    foreach ($query->result_array() as $row) {
      $total_data     = $totalData;
      $start_dari     = $requestData['start'];
      $asc_desc       = $requestData['order'][0]['dir'];
      if ($asc_desc == 'asc') {
        $nomor = ($total_data - $start_dari) - $urut2;
      }
      if ($asc_desc == 'desc') {
        $nomor = $urut1 + $start_dari;
      }

      $no_po = [];
      $get_no_po =  $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $row['no_ipp']) . "')")->result();
      foreach ($get_no_po as $item) {
        $no_po[] = $item->no_surat;
      }

      $this->db->select('a.no_doc');
      $this->db->from('tr_kasbon a');
      $this->db->where_in('a.no_doc', explode(',', $row['no_ipp']));
      $get_no_kasbon = $this->db->get()->result();
      foreach ($get_no_kasbon as $item) {
        $no_po[] = $item->no_doc;
      }
      $no_po = implode(', ', $no_po);

      $no_pr = [];
      $get_no_pr = $this->db->query("
          SELECT
            c.no_pr
          FROM
            dt_trans_po a
            JOIN tr_purchase_order d ON d.no_po = a.no_po
            JOIN material_planning_base_on_produksi_detail b ON b.id = a.idpr
            JOIN material_planning_base_on_produksi c ON c.so_number = b.so_number
          WHERE
            d.no_surat IN ('" . str_replace(",", "','", str_replace(', ', ',', $no_po)) . "')
          GROUP BY c.no_pr
      ")->result();
      foreach ($get_no_pr as $item_no_pr) {
        $no_pr[] = $item_no_pr->no_pr;
      }

      $this->db->select('a.id_pr');
      $this->db->from('tr_kasbon a');
      $this->db->where_in('a.no_doc', explode(',', $no_po));
      $get_no_kasbon = $this->db->get()->result();

      foreach ($get_no_kasbon as $item_no_pr) {
        $no_pr[] = $item_no_pr->id_pr;
      }

      if (!empty($no_pr)) {
        $no_pr = implode(', ', $no_pr);
      } else {
        $no_pr = '';
      }

      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['kode_trans']) . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y', strtotime($row['tanggal'])) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($no_po) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($no_pr) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_gudang']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['qty_unit'], 2) . "</div>";
      $nestedData[]  = "<div align='left'>" . $row['pic'] . "</div>";
      $username = (!empty($GET_USER[$row['created_by']]['nama'])) ? $GET_USER[$row['created_by']]['nama'] : '-';
      $nestedData[]  = "<div align='left'>" . $username . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y H:i:s', strtotime($row['created_date'])) . "</div>";

      $status = 'Waiting Confirm';
      $warna = 'blue';
      if ($row['sts_confirm'] == 'Y') {
        $status = 'Closed';
        $warna = 'green';
      }
      // $nestedData[]	= "<div align='center'><span class='badge bg-".$warna."'>".$status."</span></div>";


      $release  = "";
      $print    = "";
      $edit    = "";
      $view  = "<button type='button' data-kode_trans='" . $row['kode_trans'] . "' data-tanda='detail' class='btn btn-sm btn-warning detail' title='Detail' data-role='qtip'><i class='fa fa-eye'></i></button>";
      // if($row['sts_confirm'] == 'N'  AND $this->ENABLE_MANAGE){
      //   $edit	= "&nbsp;<button type='button' data-kode_trans='".$row['kode_trans']."' data-tanda='edit' class='btn btn-sm btn-primary detail' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></button>";
      // }
      if ($row['sts_confirm'] == 'N') {
        $print  = "&nbsp;<a href='" . base_url('incoming_stok/print_incoming_stok/' . $row['kode_trans']) . "' target='_blank' class='btn btn-sm btn-info' title='Print SPK Permintaan Material' data-role='qtip'><i class='fa fa-print'></i></a>";
      }
      $nestedData[]  = "<div align='center'>" . $view . $edit . $print . $release . "</div>";
      $data[] = $nestedData;
      $urut1++;
      $urut2++;
    }

    $json_data = array(
      "draw"              => intval($requestData['draw']),
      "recordsTotal"      => intval($totalData),
      "recordsFiltered"   => intval($totalFiltered),
      "data"              => $data
    );

    echo json_encode($json_data);
  }

  public function get_query_json_request_material($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.kode_trans,
              a.no_ipp,
              e.no_surat AS no_surat,
              a.pic,
              a.id_dept,
              a.tanggal,
              a.jumlah_mat AS qty_unit,
              a.created_by,
              a.created_date,
              c.nm_gudang AS nm_gudang,
              a.checked AS sts_confirm
            FROM
              warehouse_adjustment a
              LEFT JOIN warehouse c ON a.id_gudang_ke=c.id
              LEFT JOIN tr_purchase_order e ON a.no_ipp=e.no_po,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND a.category='incoming stok' AND (
              a.kode_trans LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nm_gudang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR e.no_surat LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'kode_trans',
      2 => 'tanggal',
      3 => 'e.no_surat',
      4 => 'c.nm_gudang',
      5 => 'a.jumlah_mat',
      6 => 'pic',
      7 => 'created_by',
      8 => 'created_date',
    );

    $sql .= " ORDER BY a.id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  public function set_jurnal()
  {
    $post = $this->input->post();


    $hasil_jurnal = '';
    $ttl_debit_jurnal = 0;
    $ttl_kredit_jurnal = 0;

    foreach ($post['no_po'] as $item_po) {
      $get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $item_po])->row();

      if (!empty($get_po)) {
        $arr_jurnal_coa = ['1070-10-8', '1050-30-1', '2010-10-2'];

        $id_company = '';
        $nm_company = '';

        $get_department = $this->hris->get_where('departments', ['id' => $get_po->id_dept])->row();
        $get_division = $this->hris->get_where('divisions', ['id' => $get_department->division_id])->row();

        $id_div = (!empty($get_division)) ? $get_division->id : '';
        $nm_div = (!empty($get_division)) ? $get_division->name : '';

        if ($get_department->company_id == 'COM003' || $get_department->company_id == 'COM012') {
          $get_company = $this->consultant->get_where('kons_tr_company', ['id' => '4'])->row();

          $id_company = (!empty($get_company)) ? $get_company->id : '';
          $nm_company = (!empty($get_company)) ? $get_company->nm_company : '';
        }
        if ($get_department->company_id == 'COM006') {
          $get_company = $this->consultant->get_where('kons_tr_company', ['id' => '4'])->row();

          $id_company = (!empty($get_company)) ? $get_company->id : '';
          $nm_company = (!empty($get_company)) ? $get_company->nm_company : '';
        }

        $this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
        $this->accounting->from('coa_master a');
        $this->accounting->where_in('a.no_perkiraan', $arr_jurnal_coa);
        $get_coa_jurnal = $this->accounting->get()->result();

        $no_jurnal = 0;
        foreach ($get_coa_jurnal as $item_coa) {
          $no_jurnal++;

          $debit = 0;
          $kredit = 0;
          if ($item_coa->no_coa == '1070-10-8') {
            $debit = $get_po->subtotal;
            $kredit = 0;

            $hasil_jurnal .= '<tr>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= date('d F Y');
            $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= $id_company;
            $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= $nm_div;
            $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_div]" value="' . $id_div . '">';
            $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_div]" value="' . $nm_div . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= $item_coa->no_coa;
            $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][no_coa]" value="' . $item_coa->no_coa . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= $nm_company;
            $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-left">';
            $hasil_jurnal .= $item_coa->nm_coa;
            $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_coa]" value="' . $item_coa->nm_coa . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-left">';
            $hasil_jurnal .= $item_coa->nm_coa . ' - ' . $get_po->no_surat;
            $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][deskripsi]" value="' . $item_coa->nm_coa . ' - ' . $get_po->no_surat . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-right">';
            $hasil_jurnal .= number_format($debit);
            $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][debit]" value="' . $debit . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-right">';
            $hasil_jurnal .= number_format($kredit);
            $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '</tr>';

            $ttl_debit_jurnal += $debit;
            $ttl_kredit_jurnal += $kredit;
          }
          if ($item_coa->no_coa == '1050-30-1') {
            $get_top_dp_po = $this->db->get_where('tr_top_po', ['no_po' => $item_po, 'group_top' => 75])->result();
            if (!empty($get_top_dp_po)) {
              foreach ($get_top_dp_po as $item_top_dp) {
                $no_jurnal++;
                $kredit = $item_top_dp->nilai;
                $debit = 0;

                $hasil_jurnal .= '<tr>';

                $hasil_jurnal .= '<td class="text-center">';
                $hasil_jurnal .= date('d F Y');
                $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '<td class="text-center">';
                $hasil_jurnal .= $id_company;
                $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '<td class="text-center">';
                $hasil_jurnal .= $nm_div;
                $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_div]" value="' . $id_div . '">';
                $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_div]" value="' . $nm_div . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '<td class="text-center">';
                $hasil_jurnal .= $item_coa->no_coa;
                $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][no_coa]" value="' . $item_coa->no_coa . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '<td class="text-center">';
                $hasil_jurnal .= $nm_company;
                $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '<td class="text-left">';
                $hasil_jurnal .= $item_coa->nm_coa;
                $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_coa]" value="' . $item_coa->nm_coa . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '<td class="text-left">';
                $hasil_jurnal .= $item_coa->nm_coa . ' - ' . $get_po->no_surat;
                $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][deskripsi]" value="' . $item_coa->nm_coa . ' - ' . $get_po->no_surat . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '<td class="text-right">';
                $hasil_jurnal .= number_format($debit);
                $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][debit]" value="' . $debit . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '<td class="text-right">';
                $hasil_jurnal .= number_format($kredit);
                $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
                $hasil_jurnal .= '</td>';

                $hasil_jurnal .= '</tr>';

                $ttl_debit_jurnal += $debit;
                $ttl_kredit_jurnal += $kredit;
              }
            }
          }
          if ($item_coa->no_coa == '2010-10-2') {
            $this->db->select('a.*');
            $this->db->from('tr_top_po a');
            $this->db->where('a.no_po', $item_po);
            $this->db->where('a.group_top <>', 75);
            $get_top_non_dp = $this->db->get()->result();

            foreach ($get_top_non_dp as $item_top) {
              $no_jurnal++;
              $kredit = $item_top->nilai;
              $debit = 0;

              $hasil_jurnal .= '<tr>';

              $hasil_jurnal .= '<td class="text-center">';
              $hasil_jurnal .= date('d F Y');
              $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
              $hasil_jurnal .= '</td>';

              $hasil_jurnal .= '<td class="text-center">';
              $hasil_jurnal .= $id_company;
              $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
              $hasil_jurnal .= '</td>';

              $hasil_jurnal .= '<td class="text-center">';
              $hasil_jurnal .= $nm_div;
              $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_div]" value="' . $id_div . '">';
              $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_div]" value="' . $nm_div . '">';
              $hasil_jurnal .= '</td>';

              $hasil_jurnal .= '<td class="text-center">';
              $hasil_jurnal .= $item_coa->no_coa;
              $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][no_coa]" value="' . $item_coa->no_coa . '">';
              $hasil_jurnal .= '</td>';

              $hasil_jurnal .= '<td class="text-center">';
              $hasil_jurnal .= $nm_company;
              $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
              $hasil_jurnal .= '</td>';

              $hasil_jurnal .= '<td class="text-left">';
              $hasil_jurnal .= $item_coa->nm_coa;
              $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_coa]" value="' . $item_coa->nm_coa . '">';
              $hasil_jurnal .= '</td>';

              $hasil_jurnal .= '<td class="text-left">';
              $hasil_jurnal .= $item_coa->nm_coa . ' - ' . $get_po->no_surat;
              $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][deskripsi]" value="' . $item_coa->nm_coa . ' - ' . $get_po->no_surat . '">';
              $hasil_jurnal .= '</td>';

              $hasil_jurnal .= '<td class="text-right">';
              $hasil_jurnal .= number_format($debit);
              $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][debit]" value="' . $debit . '">';
              $hasil_jurnal .= '</td>';

              $hasil_jurnal .= '<td class="text-right">';
              $hasil_jurnal .= number_format($kredit);
              $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][kredit]" value="' . $kredit . '">';
              $hasil_jurnal .= '</td>';

              $hasil_jurnal .= '</tr>';

              $ttl_debit_jurnal += $debit;
              $ttl_kredit_jurnal += $kredit;
            }
          }
        }
      }
    }

    $response = [
      'hasil_jurnal' => $hasil_jurnal,
      'ttl_debit' => $ttl_debit_jurnal,
      'ttl_kredit' => $ttl_kredit_jurnal
    ];

    echo json_encode($response);
  }
}

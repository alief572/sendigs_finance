<?php

/**
 * ------------------------------------------------------------------------
 * Class Name : Master Konsultan
 * ------------------------------------------------------------------------
 *
 * @author     DandoRidwanto
 * @copyright  2018
 *
 * Last Update : Friday, 30 Maret 2018
 *
 */

class Master_konsultan extends Admin_Controller
{
    /*
     * --------------------------------------------------------------------
     * Constructor
     * --------------------------------------------------------------------
     */
    protected $viewPermission   = 'Master_Konsultan.View';
    protected $addPermission    = 'Master_Konsultan.Add';
    protected $managePermission = 'Master_Konsultan.Manage';
    protected $deletePermission = 'Master_Konsultan.Delete';

    function __construct()
    {
        parent::__construct();
        $this->output->set_header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
        $this->output->set_header("Cache-Control: post-check=0, pre-check=0", false);
        $this->output->set_header("Pragma: no-cache");
    }


    /*
     * --------------------------------------------------------------------
     * Index
     * --------------------------------------------------------------------
     */
    function index()
    {
        $this->template->render('konsultan_view');
    }

    function display_konsultan_json()
    {
        $requestData    = $_REQUEST;
        $fetch          = $this->fetch_data_konsultan(
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );
        $totalData      = $fetch['totalData'];
        $totalFiltered  = $fetch['totalFiltered'];
        $query          = $fetch['query'];

        $data   = array();
        foreach ($query->result_array() as $row) {
            $nestedData     = array();
            $nestedData[]   = $row['nomor'];
            $nestedData[]   = $row['nama_konsultan'];
            $nestedData[]   = $row['bobot'];
            $nestedData[]   = $row['nm_kompetensi'];

            $edit_btn = "";
            if ($this->managePermission) {
                $edit_btn = "
                    <a href='javascript:void(0);' class='btn btn-warning btn-xs edit_modal' data-id='" . $row['id_kons_komp'] . "'>
                        <i class='fa fa-edit'></i> Edit
                    </a>
                ";
            }

            $delete_btn = "";
            if ($this->deletePermission) {
                $delete_btn = "
                    <a href='javascript:void();' 
                        class='btn btn-danger btn-xs delete_konsultan' data-id='".$row['id_kons_komp']."' data-id_komp='".$row['id_kompetensi']."'>
                        <i class='fa fa-trash'></i> Delete 
                    </a>
                ";
            }

            $nestedData[]   = "
                <div class='btn-group'>
                    " . $edit_btn . "
                    " . $delete_btn . "
                </div>
            ";
            $data[] = $nestedData;
        }

        $json_data = array(
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    /*
     * --------------------------------------------------------------------
     * QUERY FOR DATA CONSULTAN
     * --------------------------------------------------------------------
     *
     * Fetch data konsultan ke dalam datatables
     *
     */
    function fetch_data_konsultan($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $sql = "
            SELECT
                (@row:=@row+1) AS nomor,
                a.id_kons_komp,
                b.id_konsultan,
                b.nama_konsultan,
                c.id_kompetensi,
                c.nm_kompetensi,
                c.bobot 
            FROM 
                kons_konsultan_kompetensi AS a 
                LEFT JOIN kons_master_konsultan AS b ON a.id_konsultan = b.id_konsultan
                LEFT JOIN kons_master_kompetensi AS c ON a.id_kompetensi = c.id_kompetensi,
                (SELECT @row := 0) r 
            WHERE 1=1
        ";

        $data['totalData'] = $this->db->query($sql)->num_rows();
        if (! empty($like_value)) {
            $sql .= " AND ( ";
            $sql .= "
                b.nama_konsultan LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
                OR c.bobot LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
                OR c.nm_kompetensi LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
            ";
            $sql .= " ) ";
        }

        $data['totalFiltered']  = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'b.nama_konsultan',
            2 => 'c.bobot',
            3 => 'c.nm_kompetensi'
        );
        $sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . ", nomor ";
        $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";
        $data['query'] = $this->db->query($sql);

        return $data;
    }


    /*
     * --------------------------------------------------------------------
     * FUNCTION ADDED KONSULTAN
     * --------------------------------------------------------------------
     *
     * Proses tambah master konsultan
     *
     */
    public function konsultan_new()
    {
        if ($_POST) {
            $post = $this->input->post();

            $this->db->trans_begin();

            $valid = 1;
            $msg = '';
            if (! empty($post['nm_kompetensi'])) {
                $total = 0;
                foreach ($post['nm_kompetensi'] as $k) {
                    if (! empty($k)) {
                        $total++;
                    }
                }

                if ($total > 0) {
                    if (count(array_unique($post['nm_kompetensi'])) < count($post['nm_kompetensi'])) {
                        // echo $this->query_error('');
                        $valid = 0;
                        $msg = 'Maaf, tidak boleh ada nama kompetensi yang sama !';
                    } else {
                        $loop     = 0;
                        $terinput = 0;
                        foreach ($post['nm_kompetensi'] as $z) {
                            $Max   = "
                            SELECT 
                                a.id_kompetensi 
                            FROM 
                                kons_master_kompetensi AS a 
                            WHERE 
                                a.datet LIKE '" . date('Y-') . "%' ORDER BY a.id_kompetensi DESC LIMIT 1
                            ";
                            $Max   = $this->db->query($Max);
                            $nilai = 1;
                            if ($Max->num_rows() > 0) {
                                $nilai = intval(substr($Max->row()->id_kompetensi, -5)) + 1;
                            }
                            $kode  = 'MKOMP-' . date('Y') . '-' . sprintf('%05d', $nilai);

                            ## I. INSERT KOMPETENSI
                            $kompetensi = array(
                                'id_kompetensi' => $kode,
                                'nm_kompetensi' => $z,
                                'bobot'         => $post['bobot'][$loop],
                                'datet'         => date('Y-m-d'),
                                'input_by'      => $this->session->userdata('usr_username'),
                                'input_date'    => date('Y-m-d H:i:s')
                            );
                            $input_db = $this->db->insert('kons_master_kompetensi', $kompetensi);
                            if ($input_db) {
                                ## II. INSERT KONSULTAN KOMPETENSI
                                $get_id_kompetensi = $this->db
                                    ->select('id_kompetensi')
                                    ->where('id_kompetensi', $kode)
                                    ->limit(1)
                                    ->get('kons_master_kompetensi')
                                    ->row()
                                    ->id_kompetensi;

                                $konsultan = array(
                                    'id_konsultan'  => $post['nm_konsultan'][$loop],
                                    'id_kompetensi' => $get_id_kompetensi
                                );
                                $this->db->insert('kons_konsultan_kompetensi', $konsultan);
                                $terinput++;
                            } else {
                                $valid = 0;
                            }

                            $loop++;
                        }

                        // if ($terinput > 0) {
                        //     $pesan  = "Data Successfully Saved";
                        //     $params['redirect_page']     = "YES";
                        //     $params['redirect_page_URL'] = site_url('master_konsultan');
                        //     echo $this->query_success($pesan, $params);
                        // } else {
                        //     echo $this->query_error('Terjadi kesalahan, coba lagi');
                        // }

                        if ($this->db->trans_status() === false) {
                            $valid = 0;
                            $msg = 'Please try again later !';
                        } else {
                            $valid = 1;
                            $msg = 'Data successfully saved !';
                        }
                    }
                } else {
                    $valid = 0;
                    $msg = "Harap masukan minimal 1 nama kompetensi !";
                }
            } else {
                $valid = 0;
                $msg = "Harap masukan minimal 1 nama kompetensi !";
            }

            if ($valid == 1) {
                $this->db->trans_commit();
            } else {
                $this->db->trans_rollback();
            }

            echo json_encode([
                'status' => $valid,
                'pesan' => $msg
            ]);
        } else {
            $dt['konsultan'] = $this->db
                ->select('id_konsultan, nama_konsultan')
                ->order_by('nama_konsultan', 'asc')
                ->get('kons_master_konsultan');

            $this->load->vars($dt);
            $this->load->view('master_konsultan/konsultan_new');

            $this->template->set($dt);
            $this->template->render('konsultan_new');
        }
    }


    /*
     * --------------------------------------------------------------------
     * FUNCTION EDIT KONSULTAN
     * --------------------------------------------------------------------
     *
     * Proses edit data konsultan
     *
     */
    function konsultan_edit($id_kompetensi)
    {
        if (! $this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            if ($_POST) {
                $this->load->library('form_validation');
                $no = 0;
                foreach ($_POST['nm_kompetensi'] as $i) {
                    $this->form_validation->set_rules('nm_kompetensi[' . $no . ']', 'Nama Kompetensi #' . ($no + 1), 'trim|required');
                    $this->form_validation->set_rules('bobot[' . $no . ']', 'Bobot #' . ($no + 1), 'trim|required|greater_than[0]|max_length[1000000]');
                    $no++;
                }

                $this->form_validation->set_message('required', '%s harus diisi.');
                $this->form_validation->set_message('greater_than', '%s harus lebih besar dari nol.');
                $this->form_validation->set_message('Check_kompetensi', '%s Nama Kompetensi Sudah ada !');
                if ($this->form_validation->run() == TRUE) {
                    if (count(array_unique($_POST['nm_kompetensi'])) < count($_POST['nm_kompetensi'])) {
                        echo $this->query_error('Maaf, tidak boleh ada kompetensi yang sama !');
                    } else {
                        $loop       = 0;
                        $terinput   = 0;
                        date_default_timezone_set('Asia/Jakarta');
                        foreach ($_POST['nm_konsultan'] as $z) {
                            if (empty($_POST['nm_kompetensi'][$loop])) {
                                ## INSERT KOMPETENSI
                                ## INSERT KONSULTAN KOMPETENSI
                            } else {
                                ## I. UPDATE KOMPETENSI
                                $kompetensi = array(
                                    'nm_kompetensi' => $_POST['nm_kompetensi'][$loop],
                                    'bobot' => $_POST['bobot'][$loop]
                                );
                                $update_db = $this->db->where('id_kompetensi', $id_kompetensi)->update('kons_master_kompetensi', $kompetensi);
                                if ($update_db) {
                                    ## II. UPDATE KONSULTAN KOMPETENSI
                                    $konsultan = array(
                                        'id_konsultan' => $z
                                    );
                                    $this->db->where('id_kompetensi', $id_kompetensi)->update('kons_konsultan_kompetensi', $konsultan);
                                    $terinput++;
                                }
                            }
                            $loop++;
                        }

                        if ($terinput > 0) {

                            $pesan  = "<b><i class='fa fa-fw fa-check'></i> Data Successfully Updated</b>";
                            $params['datatable_reload'] = "#my-grid";

                            // $pesan  = "Data Successfully Saved";
                            // $params['redirect_page']     = "YES";
                            // $params['redirect_page_URL'] = site_url('master_konsultan');
                            // echo $this->query_success($pesan, $params);
                            echo $this->query_success($pesan, $params);
                        } else {
                            echo $this->query_error('Terjadi kesalahan, coba lagi');
                        }
                    }
                } else {
                    echo $this->input_error();
                }
            } else {
                $dt['konsultan'] = $this->db
                    ->select('id_konsultan, nama_konsultan')
                    ->order_by('nama_konsultan', 'asc')
                    ->get('kons_master_konsultan');

                $detail = "
                    SELECT 
                        a.id_konsultan,
                        a.id_kompetensi,
                        c.nama_konsultan,
                        b.nm_kompetensi,
                        b.bobot 
                    FROM 
                        kons_konsultan_kompetensi AS a 
                        LEFT JOIN kons_master_kompetensi AS b ON a.id_kompetensi = b.id_kompetensi 
                        LEFT JOIN kons_master_konsultan AS c ON a.id_konsultan = c.id_konsultan 
                    WHERE 1=1
                        AND a.id_kompetensi = '" . $id_kompetensi . "'
                ";
                $dt['detail'] = $this->db->query($detail);
                $dt['id_kompetensi'] = $id_kompetensi;

                $this->load->vars($dt);
                $this->load->view('master_konsultan/konsultan_edit');
            }
        }
    }


    /*
     * --------------------------------------------------------------------
     * FUNCTION DELETE DATA KONSULTAN
     * --------------------------------------------------------------------
     *
     * Proses delete master konsultan
     *
     */
    public function konsultan_delete($id_kompetensi)
    {
        if (! $this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $delete = $this->db->where('id_kompetensi', $id_kompetensi)->delete('kons_konsultan_kompetensi');
            if ($delete) {
                $this->db->where('id_kompetensi', $id_kompetensi)->delete('kons_master_kompetensi');

                $pesan  = "Data Successfully Deleted";
                $params['datatable_reload'] = "#my-grid";
                echo $this->query_success($pesan, $params);
            }
        }
    }


    /*
     * --------------------------------------------------------------------
     * Check Nama Kompetensi
     * --------------------------------------------------------------------
     *
     * Fungsi Check Nama Kompetensi
     *
     */
    function Check_kompetensi($nama)
    {
        $cek = $this->db
            ->select('id_kompetensi')
            ->where('nm_kompetensi', $nama)
            ->limit(1)
            ->get('kons_master_kompetensi');
        if ($cek->num_rows() > 0) {
            return FALSE;
        }

        return TRUE;
    }

    function edit_modal()
    {
        $id = $this->input->post('id');

        $dt['konsultan'] = $this->db
            ->select('id_konsultan, nama_konsultan')
            ->order_by('nama_konsultan', 'asc')
            ->get('kons_master_konsultan');

        $detail = "
            SELECT 
                a.id_konsultan,
                a.id_kompetensi,
                c.nama_konsultan,
                b.nm_kompetensi,
                b.bobot 
            FROM 
                kons_konsultan_kompetensi AS a 
                LEFT JOIN kons_master_kompetensi AS b ON a.id_kompetensi = b.id_kompetensi 
                LEFT JOIN kons_master_konsultan AS c ON a.id_konsultan = c.id_konsultan 
            WHERE 1=1
                AND a.id_kons_komp = '" . $id . "'
        ";
        $dt['detail'] = $this->db->query($detail);
        $dt['id_kompetensi'] = $id;

        $this->template->set($dt);
        $this->template->render('konsultan_edit');
    }

    function save_edit_konsultan() {
        $post = $this->input->post();

        $this->db->trans_begin();

        $get_kons_konsultan_komp = $this->db->get_where('kons_konsultan_kompetensi', ['id_kons_komp' => $post['id']])->row();

        $data_input = [
            'nm_kompetensi' => $post['nm_kompetensi'],
            'bobot' => $post['bobot']
        ];

        $update_kompetensi = $this->db->update('kons_master_kompetensi', $data_input, ['id_kompetensi' => $get_kons_konsultan_komp->id_kompetensi]);

        if($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
            $msg = $this->db->error($update_kompetensi);
        } else {
            $this->db->trans_commit();
            $valid = 1;
            $msg = 'Data has been successfully edited !';
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
        
    }

    function delete_konsultan() {
        $post = $this->input->post();

        $id = $post['id'];
        $id_komp = $post['id_komp'];

        $this->db->trans_begin();
        
        $this->db->delete('kons_konsultan_kompetensi', ['id_kons_komp' => $id]);
        $this->db->delete('kons_master_kompetensi', ['id_kompetensi' => $id_komp]);

        if($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
            $msg = 'Please try again later !';
        } else {
            $this->db->trans_commit();
            $valid = 1;
            $msg = 'Data has successfully deleted !';
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }
}

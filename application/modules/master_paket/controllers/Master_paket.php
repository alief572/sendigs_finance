<?php

/**
 * ------------------------------------------------------------------------
 * Class Name : Master Paket
 * ------------------------------------------------------------------------
 *
 * @author     DandoRidwanto
 * @copyright  2018
 *
 * Last Update : Monday, 02 April 2018
 *
 */

class Master_paket extends Admin_Controller
{
    /*
     * --------------------------------------------------------------------
     * Constructor
     * --------------------------------------------------------------------
     */
    protected $viewPermission     = 'Master_Paket.View';
    protected $addPermission      = 'Master_Paket.Add';
    protected $managePermission = 'Master_Paket.Manage';
    protected $deletePermission = 'Master_Paket.Delete';

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
        $this->auth->restrict($this->viewPermission);
        $this->template->render('paket_view');
    }

    function display_paket_json()
    {
        $requestData    = $_REQUEST;
        $fetch          = $this->fetch_data_paket(
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
            $nestedData[]   = $row['kategori_paket'];
            $nestedData[]   = $row['nm_paket'];

            $edit_btn = '';
            if($this->managePermission) {
                $edit_btn = "
                    <a href='javascript:void(0);' class='btn btn-warning btn-xs edit_modal' data-id='" . $row['id_paket'] . "'>
                        <i class='fa fa-edit'></i> Edit
                    </a>
                ";
            }

            $delete_btn = '';
            if($this->deletePermission) {
                $delete_btn = "
                    <a href='javascript:void(0);' 
                        class='btn btn-danger btn-xs delete_paket'
                        data-id='".$row['id_paket']."'>
                        <i class='fa fa-trash'></i> Delete 
                    </a>
                ";
            }


            $nestedData[]   = "
                <div class='btn-group'>
                    ".$edit_btn."
                    ".$delete_btn."
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
     * Fetch data paket ke dalam datatables
     *
     */
    function fetch_data_paket($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $sql = "
            SELECT
                (@row:=@row+1) AS nomor,
                a.id_paket,
                a.id_kategori,
                b.kategori_paket,
                a.nm_paket 
            FROM 
                kons_master_paket AS a
                LEFT JOIN kons_kategori_paket AS b ON a.id_kategori = b.id_kategori_paket,
                (SELECT @row := 0) r 
            WHERE 1=1
        ";

        $data['totalData'] = $this->db->query($sql)->num_rows();
        if (! empty($like_value)) {
            $sql .= " AND ( ";
            $sql .= "
                b.kategori_paket LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
                OR a.nm_paket LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
            ";
            $sql .= " ) ";
        }

        $data['totalFiltered']  = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'b.kategori_paket',
            2 => 'a.nm_paket'
        );
        $sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . ", nomor ";
        $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";
        $data['query'] = $this->db->query($sql);

        return $data;
    }


    /*
     * --------------------------------------------------------------------
     * New Master Paket
     * --------------------------------------------------------------------
     */
    public function paket_new()
    {
        if ($_POST) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('nm_paket', 'Nama Paket', 'trim|required');
            $this->form_validation->set_rules('kategori', 'Kategori', 'trim|required');
            $this->form_validation->set_message('required', '%s harus diisi.');
            if ($this->form_validation->run() == TRUE) {
                $data = array(
                    'nm_paket'    => $this->input->post('nm_paket'),
                    'id_kategori' => $this->input->post('kategori'),
                    'input_date'  => date('Y-m-d H:i:s'),
                    'input_by'    => $this->session->userdata('usr_username')
                );
                $inserted = $this->db->insert('kons_master_paket', $data);
                if ($inserted) {
                    // delete cache
                    // delete_files(realpath($this->db->cachedir.'/master_paket+display-paket-json'));
                    //##&## INSERT ACTIVITY USERS
                    H_activity_record("Master Paket > Add New Paket > " . $this->input->post('nm_paket'));

                    $pesan  = "Data Successfully Saved";
                    $params['redirect_page']     = "YES";
                    $params['redirect_page_URL'] = site_url('master_paket');
                    echo $this->query_success($pesan, $params);
                } else {
                    echo $this->query_error('Terjadi kesalahan, coba lagi');
                }
            } else {
                echo $this->input_error();
            }
        } else {
            $dt['kategori'] = $this->db
                ->select('id_kategori_paket, kategori_paket')
                ->order_by('kategori_paket', 'asc')
                ->get('kons_kategori_paket');


            $this->template->set($dt);
            $this->template->render('paket_new');
        }
    }


    /*
     * --------------------------------------------------------------------
     * FUNCTION EDIT PAKET
     * --------------------------------------------------------------------
     *
     * Proses edit data paket
     *
     */
    public function paket_edit($id_paket)
    {
        if ($_POST) {
            $paket     = $this->input->post('nm_paket');
            $paket_old = $this->input->post('nm_paket_old');
            $callback  = '';
            if ($paket !== $paket_old) {
                $callback  = '|callback_Check_Paket[nm_paket]';
            }

            $this->load->library('form_validation');
            $this->form_validation->set_rules('nm_paket', 'Nama Paket', 'trim|required' . $callback);
            $this->form_validation->set_message('required', '%s harus diisi.');
            $this->form_validation->set_message('Check_Paket', '%s Sudah ada !');
            if ($this->form_validation->run() == TRUE) {
                $data['nm_paket']    = $this->clean_tag_input($paket);
                $data['id_kategori'] = $this->clean_tag_input($this->input->post('kategori'));

                $updated = $this->db->where('id_paket', $id_paket)->update('kons_master_paket', $data);
                if ($updated) {
                    //##&## INSERT ACTIVITY USERS
                    H_activity_record("Master Paket > Edit Paket > " . $paket);

                    $pesan  = "Data Successfully Edit";
                    $params['datatable_reload'] = "#my-grid";
                    echo $this->query_success($pesan, $params);
                } else {
                    echo $this->query_error('Terjadi kesalahan, coba lagi');
                }
            } else {
                echo $this->input_error();
            }
        } else {
            $dt['paket'] = $this->db
                ->select('id_paket, id_kategori, nm_paket')
                ->where('id_paket', $id_paket)
                ->get('kons_master_paket')
                ->row();

            $dt['kategori'] = $this->db
                ->select('id_kategori_paket, kategori_paket')
                ->get('kons_kategori_paket');

            $this->template->set($dt);
            $this->template->render('paket_edit');
        }
    }


    /*
     * --------------------------------------------------------------------
     * Deleted Master Paket
     * --------------------------------------------------------------------
     */
    public function paket_delete($id_paket, $nm_paket = NULL)
    {
        if (! $this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $deleted = $this->db->where('id_paket', $id_paket)->delete('kons_master_paket');
            if ($deleted) {
                //##&## INSERT ACTIVITY USERS
                H_activity_record("Master Paket > Delete Paket > " . urldecode($nm_paket));

                $pesan  = "Data Successfully Deleted";
                $params['datatable_reload'] = "#my-grid";
                echo $this->query_success($pesan, $params);
            } else {
                echo $this->query_error('Terjadi kesalahan, coba lagi');
            }
        }
    }


    /*
     * --------------------------------------------------------------------
     * Check Nama Paket
     * --------------------------------------------------------------------
     *
     * Fungsi Check Nama Paket
     *
     */
    function Check_Paket($nama)
    {
        $cek = $this->db
            ->select('id_paket')
            ->where('nm_paket', $nama)
            ->limit(1)
            ->get('kons_master_paket');
        if ($cek->num_rows() > 0) {
            return FALSE;
        }

        return TRUE;
    }

    function save_paket()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $data_input = [
            'id_kategori' => $post['kategori'],
            'nm_paket' => $post['nm_paket'],
            'input_date' => date('Y-m-d H:i:s'),
            'input_by' => $this->auth->user_name()
        ];

        $insert_paket = $this->db->insert('kons_master_paket', $data_input);
        if (!$insert_paket) {
            $this->db->trans_rollback();
            print_r($this->db->error($insert_paket));
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
            $msg = 'Please try again later !';
        } else {
            $this->db->trans_commit();
            $valid = 1;
            $msg = 'Data successfully saved !';
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    function edit_modal()
    {
        $id = $this->input->post('id');

        $dt['paket'] = $this->db
            ->select('id_paket, id_kategori, nm_paket')
            ->where('id_paket', $id)
            ->get('kons_master_paket')
            ->row();

        $dt['kategori'] = $this->db
            ->select('id_kategori_paket, kategori_paket')
            ->get('kons_kategori_paket');

        $this->template->set($dt);
        $this->template->render('paket_edit');
    }

    function edit_paket() {
        $post = $this->input->post();

        $this->db->trans_begin();

        $update_paket = $this->db->update('kons_master_paket', [
            'id_kategori' => $post['kategori'],
            'nm_paket' => $post['nm_paket']
        ], ['id_paket' => $post['id_paket']]);

        if(!$update_paket) {
            $this->db->trans_rollback();
            print_r($this->db->error($update_paket));
            exit;
        }

        if($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
            $msg = 'Please try again later !';
        } else {
            $this->db->trans_commit();
            $valid = 1;
            $msg = 'Data successfully edited !';
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    function delete_paket() {
        $id = $this->input->post('id');

        $check_konsultasi = $this->db->get_where('kons_master_konsultasi_header', ['id_paket' => $id]);

        $valid = 1;
        $msg = '';
        if($check_konsultasi->num_rows() > 0) {
            $valid = 0;
            $msg = 'Maaf, ada data Konsultasi dengan paket ini!';
        } else {
            $this->db->trans_begin();

            $delete_paket = $this->db->delete('kons_master_paket', ['id_paket' => $id]);
            if(!$delete_paket) {
                $this->db->trans_rollback();
                print_r($this->db->error($delete_paket));
                exit;
            } else {
                if($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    $valid = 0;
                    $msg = 'Please try again later!';
                } else {
                    $this->db->trans_commit();
                    $valid = 1;
                    $msg = 'Data has been deleted !';
                }
            }
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }
}

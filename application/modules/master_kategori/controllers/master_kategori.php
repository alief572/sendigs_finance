<?php

/**
 * ------------------------------------------------------------------------
 * Class Name : Master kategori
 * ------------------------------------------------------------------------
 *
 * @author     DandoRidwanto
 * @copyright  2018
 *
 * Last Update : Sunday, 11 Maret 2018
 *
 */

class Master_kategori extends Admin_Controller
{
    /*
     * --------------------------------------------------------------------
     * Constructor
     * --------------------------------------------------------------------
     */

    protected $viewPermission   = 'Master_Kategori.View';
    protected $addPermission    = 'Master_Kategori.Add';
    protected $managePermission = 'Master_Kategori.Manage';
    protected $deletePermission = 'Master_Kategori.Delete';

    function __construct()
    {
        parent::__construct();
        $this->output->set_header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
        $this->output->set_header("Cache-Control: post-check=0, pre-check=0", false);
        $this->output->set_header("Pragma: no-cache");
        $this->load->model('m_kategori');
    }


    /*
     * --------------------------------------------------------------------
     * Index
     * --------------------------------------------------------------------
     */
    function index()
    {
        $this->auth->restrict($this->managePermission);
        $this->template->render('kategori_view');
    }

    function display_kategori_json()
    {
        $requestData    = $_REQUEST;
        $fetch          = $this->m_kategori->fetch_data_kategori(
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

            $edit_btn = "";
            if ($this->managePermission) {
                $edit_btn = "
                    <a href='javascript:void(0);' class='btn btn-warning btn-xs edit_modal' data-id='" . $row['id_kategori_paket'] . "'>
                        <i class='fa fa-edit'></i> Edit 
                    </a>
                ";
            }

            $delete_btn = "";
            if ($this->deletePermission) {
                $delete_btn = "
                    <a href='javascript:void(0);' class='btn btn-xs btn-danger delete_kategori' data-id='" . $row['id_kategori_paket'] . "'><i class='fa fa-trash'></i> Delete</a>
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
     * New Category
     * --------------------------------------------------------------------
     */
    public function kategori_new()
    {
        if ($_POST) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('nm_kategori', 'Nama Kategori', 'trim|required');
            $this->form_validation->set_message('required', '%s harus diisi.');
            if ($this->form_validation->run() == TRUE) {
                $data['kategori_paket'] = $this->clean_tag_input($this->input->post('nm_kategori'));
                $inserted = $this->db->insert('kons_kategori_paket', $data);
                if ($inserted) {
                    $pesan  = "Data Successfully Saved";
                    $params['redirect_page']     = "YES";
                    $params['redirect_page_URL'] = site_url('master-kategori');
                    echo $this->query_success($pesan, $params);
                } else {
                    echo $this->query_error('Terjadi kesalahan, coba lagi');
                }
            } else {
                echo $this->input_error();
            }
        } else {
            // $this->load->view('master_kategori/kategori_new');
            $this->template->render('kategori_new');
        }
    }


    /*
     * --------------------------------------------------------------------
     * Edit Nama Kategori
     * --------------------------------------------------------------------
     */
    public function kategori_edit($id_kategori)
    {
        if (! $this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            if ($_POST) {
                $kategori     = $this->input->post('nm_kategori');
                $kategori_old = $this->input->post('nm_kategori_old');
                $callback     = '';
                if ($kategori !== $kategori_old) {
                    $callback  = '|callback_Check_Kategori[nm_kategori]';
                }

                $this->load->library('form_validation');
                $this->form_validation->set_rules('nm_kategori', 'Nama Kategori', 'trim|required' . $callback);
                $this->form_validation->set_message('required', '%s harus diisi.');
                $this->form_validation->set_message('Check_Kategori', '%s Sudah ada !');
                if ($this->form_validation->run() == TRUE) {
                    $data['kategori_paket'] = $this->clean_tag_input($kategori);
                    $updated = $this->db
                        ->where('id_kategori_paket', $id_kategori)
                        ->update('kons_kategori_paket', $data);

                    if ($updated) {
                        $pesan  = "Data Successfully Saved";
                        $params['datatable_reload'] = "#my-grid";
                        echo $this->query_success($pesan, $params);
                    } else {
                        echo $this->query_error('Terjadi kesalahan, coba lagi');
                    }
                } else {
                    echo $this->input_error();
                }
            } else {
                $dt['detail'] = $this->db
                    ->select('id_kategori_paket, kategori_paket')
                    ->where('id_kategori_paket', $id_kategori)
                    ->get('kons_kategori_paket')
                    ->row();

                $this->load->view('master_kategori/kategori_edit', $dt);
            }
        }
    }

    /*
     * --------------------------------------------------------------------
     * Check Nama Kategori
     * --------------------------------------------------------------------
     *
     * Fungsi Check Nama Kategori
     *
     */
    function Check_Kategori($nama)
    {
        $cek = $this->db
            ->select('id_kategori_paket')
            ->where('kategori_paket', $nama)
            ->limit(1)
            ->get('kons_kategori_paket');
        if ($cek->num_rows() > 0) {
            return FALSE;
        }
        return TRUE;
    }

    function save_new_kategori()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $data_insert = [
            'kategori_paket' => $post['nm_kategori']
        ];

        $this->db->insert('kons_kategori_paket', $data_insert);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
            $msg = 'Please try again later !';
        } else {
            $this->db->trans_commit();
            $valid = 1;
            $msg = 'Data successfully saved!';
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    function edit_modal()
    {
        $dt['detail'] = $this->db
            ->select('id_kategori_paket, kategori_paket')
            ->where('id_kategori_paket', $this->input->post('id'))
            ->get('kons_kategori_paket')
            ->row();

        $dt['id'] = $this->input->post('id');

        $this->template->set($dt);
        $this->template->render('kategori_edit');
    }

    function save_edit_kategori()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $data = [
            'kategori_paket' => $post['nm_kategori']
        ];

        $this->db->update('kons_kategori_paket', $data, ['id_kategori_paket' => $post['id']]);

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

    function delete_kategori()
    {
        $id = $this->input->post('id');

        $check_kategori_used = $this->db->get_where('kons_master_paket', ['id_kategori' => $id]);
        if ($check_kategori_used->num_rows() > 0) {
            $valid = 0;
            $msg = 'Maaf, kategori masih dipakai di dalam modul paket !';
        } else {
            $this->db->trans_begin();

            $this->db->delete('kons_kategori_paket', ['id_kategori_paket' => $id]);

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $valid = 0;
                $msg = 'Please try again later !';
            } else {
                $this->db->trans_commit();
                $valid = 1;
                $msg = 'Data successfully deleted !';
            }
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }
}

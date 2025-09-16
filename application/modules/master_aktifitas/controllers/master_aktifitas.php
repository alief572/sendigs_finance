<?php

/**
 * ------------------------------------------------------------------------
 * Class Name : Master Aktifitas
 * ------------------------------------------------------------------------
 *
 * @author     DandoRidwanto
 * @copyright  2018
 *
 * Last Update : Monday, 23 June 2018
 *
 */

class Master_aktifitas extends Admin_Controller
{
    /*
     * --------------------------------------------------------------------
     * Constructor
     * --------------------------------------------------------------------
     */

    protected $viewPermission     = 'Master_Aktifitas.View';
    protected $addPermission      = 'Master_Aktifitas.Add';
    protected $managePermission = 'Master_Aktifitas.Manage';
    protected $deletePermission = 'Master_Aktifitas.Delete';

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

        $this->template->render('aktifitas_view');
    }

    function display_aktifitas_json()
    {
        $requestData    = $_REQUEST;
        $fetch          = $this->fetch_data_aktifitas(
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

            $edit_btn = "";
            if ($this->managePermission) {
                $edit_btn = "<a href='" . site_url('master_aktifitas/aktifitas_edit/' . $row['id_aktifitas']) . "' class='btn btn-warning btn-xs'>
                        <i class='fa fa-edit'></i> Edit
                    </a>";
            }

            $delete_btn = "";
            if ($this->deletePermission) {
                $delete_btn = "<a href='javascript:void(0);' 
                        class='btn btn-danger btn-xs delete_aktifitas'
                        id='DeleteConfirm' data-id='" . $row['id_aktifitas'] . "'>
                        <i class='fa fa-trash'></i> Delete 
                    </a>";
            }

            $total_point    = $this->db->where('id_aktifitas', $row['id_aktifitas'])->get('kons_master_check_point')->num_rows();
            $nestedData     = array();
            $nestedData[]   = $row['nomor'];
            $nestedData[]   = $row['id_aktifitas'];
            $nestedData[]   = $row['datet'];
            $nestedData[]   = $row['nm_aktifitas'];
            $nestedData[]   = number_format($row['harga_aktifitas']);
            $nestedData[]   = $row['bobot'];
            $nestedData[]   = $row['mandays'];
            $nestedData[]   = ($total_point > 0) ? "<b>" . $total_point . "</b> POINT" : "-";
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
     * Fetch data aktifitas ke dalam datatables
     *
     */
    function fetch_data_aktifitas($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $sql = "
            SELECT 
                (@row:=@row+1) AS nomor,
                a.id_aktifitas,
                a.id_paket,
                a.datet,
                a.nm_aktifitas,
                a.harga_aktifitas,
                a.bobot,
                a.mandays,
                a.tahapan,
                a.keterangan,
                a.id_kompetensi 
            FROM 
                kons_master_aktifitas AS a,
                (SELECT @row:=0) r
            WHERE 1=1
        ";

        $data['totalData'] = $this->db->query($sql)->num_rows();
        if (! empty($like_value)) {
            $sql .= " AND ( ";
            $sql .= "
                a.id_aktifitas LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
                OR a.nm_aktifitas LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
                OR a.harga_aktifitas LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
                OR a.bobot LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
                OR a.mandays LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
            ";
            $sql .= " ) ";
        }

        $data['totalFiltered']  = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor',
            1 => 'a.id_aktifitas',
            2 => 'a.datet',
            3 => 'a.nm_aktifitas',
            4 => 'a.harga_aktifitas',
            5 => 'a.bobot',
            6 => 'a.mandays'
        );
        $sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . ", a.input_date DESC, a.datet DESC ";
        $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";
        $data['query'] = $this->db->query($sql);

        return $data;
    }


    /*
     * --------------------------------------------------------------------
     * FUNCTION ADDED AKTIFITAS
     * --------------------------------------------------------------------
     *
     * Proses tambah master aktifitas
     *
     */
    public function aktifitas_new()
    {
        $this->auth->restrict($this->addPermission);
        if ($_POST) {
            $post = $this->input->post();
            $msg = '';
            $valid = 1;
            if (! empty($post['aktifitas_num'])) {
                $total = 0;
                foreach ($post['aktifitas_num'] as $k) {
                    if (! empty($k)) {
                        $total++;
                    }
                }

                if ($total > 0) {
                    $this->load->library('form_validation');
                    $no = 0;
                    foreach ($post['nm_aktifitas'] as $i) {
                        $chk_aktif = $this->db->select('id_chk_point')->where('id_aktifitas', $post['aktifitas_num'][$no])->limit(1)->get('kons_master_check_point');
                        $callback  = '|callback_Check_aktifitas[nm_aktifitas[' . $no . ']]';
                        if ($chk_aktif->num_rows() > 0) {
                            $callback  = '';
                        }

                        $this->form_validation->set_rules('nm_aktifitas[' . $no . ']', 'Aktifitas #' . ($no + 1), 'trim|required' . $callback);
                        $this->form_validation->set_rules('hrg_aktifitas[' . $no . ']', 'Harga #' . ($no + 1), 'trim|required|greater_than[0]|max_length[1000000]');
                        $this->form_validation->set_rules('bobot[' . $no . ']', 'Bobot #' . ($no + 1), 'trim|required');
                        $this->form_validation->set_rules('mandays[' . $no . ']', 'Mandays #' . ($no + 1), 'trim|required');
                        $no++;
                    }
                    $this->form_validation->set_message('required', '%s harus diisi.');
                    $this->form_validation->set_message('greater_than', '%s harus lebih besar dari nol.');
                    $this->form_validation->set_message('Check_aktifitas', '%s Nama Aktifitas Sudah ada !');
                    if ($this->form_validation->run() == TRUE) {
                        if (count(array_unique($post['nm_aktifitas'])) < count($post['nm_aktifitas'])) {
                            $valid = 0;
                            $msg = 'Maaf, tidak boleh ada nama aktifitas yang sama !';
                        } else {
                            $terinput  = 0;
                            $tahapan   = 1;
                            $unique_id = sha1(time(microtime()));
                            foreach ($post['aktifitas_num'] as $key => $value) {
                                $id_aktifitas     = $value;
                                $nm_aktifitas     = $post['nm_aktifitas'][$key];
                                $harga_aktifitas  = $post['hrg_aktifitas'][$key];
                                $bobot            = $post['bobot'][$key];
                                $mandays          = $post['mandays'][$key];
                                $unik_id          = $post['aktifitas_unique_id'][$key];

                                if (! empty($value)) {
                                    $cek_id = $this->db->select('id_aktifitas')->where('id_aktifitas', $id_aktifitas)->get('kons_master_aktifitas');
                                    if (! empty($unik_id) or ($cek_id->num_rows() > 0)) {
                                        ## I. UPDATE AKTIFITAS
                                        $aktifitas = array(
                                            'datet'           => date('Y-m-d'),
                                            'nm_aktifitas'    => $nm_aktifitas,
                                            'harga_aktifitas' => $harga_aktifitas,
                                            'bobot'           => $bobot,
                                            'mandays'         => $mandays,
                                            'tahapan'         => $tahapan,
                                            'keterangan'      => '',
                                            'id_kompetensi'   => '0',
                                            'input_date'      => date('Y-m-d H:i:s'),
                                            'input_by'        => $this->session->userdata('usr_username')
                                        );
                                        $update_db = $this->db
                                            ->where('unique_id', $unik_id)
                                            ->where('id_aktifitas', $id_aktifitas)
                                            ->update('kons_master_aktifitas', $aktifitas);
                                        if ($update_db) {
                                            $terinput++;
                                        }

                                        #%# REMOVE ONE OR ALL ROW JIKA DIPILIH DELETE #%#
                                        // if( ! empty($unik_id))
                                        // {
                                        //     $this->aktifitas_delete_rest($id_paket, $id_aktifitas);
                                        // }
                                        // if(empty($id_aktifitas))
                                        // {
                                        //     $this->aktifitas_delete_all($id_aktifitas);
                                        // }

                                    } else {
                                        if ($cek_id->num_rows() < 1) {
                                            ## II. INSERT AKTIFITAS
                                            $aktifitas = array(
                                                'id_aktifitas'    => $id_aktifitas,
                                                'datet'           => date('Y-m-d'),
                                                'nm_aktifitas'    => $nm_aktifitas,
                                                'harga_aktifitas' => $harga_aktifitas,
                                                'bobot'           => $bobot,
                                                'mandays'         => $mandays,
                                                'tahapan'         => $tahapan,
                                                'keterangan'      => '',
                                                'id_kompetensi'   => '0',
                                                'unique_id'       => $unique_id,
                                                'input_date'      => date('Y-m-d H:i:s'),
                                                'input_by'        => $this->session->userdata('usr_username')
                                            );
                                            $insert_db = $this->db->insert('kons_master_aktifitas', $aktifitas);
                                            if ($insert_db) {
                                                //##&## INSERT ACTIVITY USERS
                                                H_activity_record("Master Aktifitas > Add New Aktifitas > " . $nm_aktifitas);
                                                $terinput++;
                                            }
                                        }
                                    }
                                }

                                $tahapan++;
                            }

                            // if ($terinput > 0) {
                            //     $pesan  = "Data Successfully Saved";
                            //     $params['redirect_page']     = "YES";
                            //     $params['redirect_page_URL'] = site_url('master-aktifitas');
                            //     echo $this->query_success($pesan, $params);
                            // } else {
                            //     echo $this->query_error('Terjadi kesalahan, coba lagi');
                            // }

                            if ($this->db->trans_status() === false) {
                                $this->db->trans_rollback();
                                $msg = 'Please try again later !';
                                $valid = 0;
                            } else {
                                $this->db->trans_commit();
                                $msg = 'Data successfully saved !';
                            }
                        }
                    } else {
                        $msg = 'Please try again later!';
                        $valid = 0;
                    }
                } else {
                    $msg = 'Harap masukan minimal 1 nama aktifitas.';
                    $valid = 0;
                }
            } else {
                $msg = 'Mohon tambahkan aktifitas baru.';
                $valid = 0;
            }

            echo json_encode([
                'status' => $valid,
                'pesan' => $msg
            ]);
        } else {
            $this->template->render('aktifitas_new');
            // $this->load->view('master_aktifitas/aktifitas_new');
        }
    }


    /*
     * --------------------------------------------------------------------
     * FUNCTION EDIT aktifitas
     * --------------------------------------------------------------------
     *
     * Proses edit data aktifitas
     *
     */
    public function aktifitas_edit($id_aktifitas)
    {
        if ($_POST) {
            // $aktifitas     = $this->input->post('nm_aktifitas');
            // $aktifitas_old = $this->input->post('nm_aktifitas_old');
            // $callback      = '';
            // if($aktifitas !== $aktifitas_old){
            //     $callback  = '|callback_Check_aktifitas[nm_aktifitas]';
            // }

            // $this->load->library('form_validation');
            // $this->form_validation->set_rules('nm_aktifitas','Nama aktifitas', 'trim|required'.$callback);
            // $this->form_validation->set_rules('hrg_aktifitas','Harga ', 'trim|required|greater_than[0]|max_length[1000000]');
            // $this->form_validation->set_rules('bobot','Bobot ', 'trim|required');
            // $this->form_validation->set_rules('mandays','Mandays ', 'trim|required');
            // $this->form_validation->set_message('required', '%s harus diisi.');
            // $this->form_validation->set_message('greater_than', '%s harus lebih besar dari nol.');
            // $this->form_validation->set_message('Check_aktifitas', '%s Nama Aktifitas Sudah ada !');
            // if($this->form_validation->run() == TRUE)
            // {
            //     $data['nm_aktifitas']   = $this->clean_tag_input($aktifitas);
            //     $data['harga_aktifitas']= $this->clean_tag_input($this->input->post('hrg_aktifitas'));
            //     $data['bobot']          = $this->clean_tag_input($this->input->post('bobot'));
            //     $data['mandays']        = $this->clean_tag_input($this->input->post('mandays'));
            //     $data['update_date']    = date('Y-m-d H:i:s');
            //     $data['update_by']      = $this->session->userdata('usr_username');
            //     $updated = $this->db->where('id_aktifitas', $id_aktifitas)->update('kons_master_aktifitas', $data);
            //     if($updated)
            //     {
            //         //##&## INSERT ACTIVITY USERS 
            //         H_activity_record("Master Aktifitas > Edit Aktifitas > ID Aktifitas : ".$id_aktifitas);

            //         $pesan  = "Data Successfully Edit";
            //         $params['datatable_reload'] = "#my-grid";
            //         echo $this->query_success($pesan, $params);
            //     }else{
            //         echo $this->query_error('Terjadi kesalahan, coba lagi');
            //     }
            // }
            // else
            // {
            //     echo $this->input_error();
            // }
            if (! empty($_POST['aktifitas_num'])) {
                $total = 0;
                foreach ($_POST['aktifitas_num'] as $k) {
                    if (! empty($k)) {
                        $total++;
                    }
                }

                if ($total > 0) {
                    $this->load->library('form_validation');
                    $no = 0;
                    foreach ($_POST['nm_aktifitas'] as $i) {
                        $chk_aktif = $this->db->select('id_chk_point')->where('id_aktifitas', $_POST['aktifitas_num'][$no])->limit(1)->get('kons_master_check_point');
                        $callback  = '|callback_Check_aktifitas[nm_aktifitas[' . $no . ']]';
                        if ($chk_aktif->num_rows() > 0) {
                            $callback  = '';
                        }

                        $this->form_validation->set_rules('nm_aktifitas[' . $no . ']', 'Aktifitas #' . ($no + 1), 'trim|required' . $callback);
                        $this->form_validation->set_rules('hrg_aktifitas[' . $no . ']', 'Harga #' . ($no + 1), 'trim|required|greater_than[0]|max_length[1000000]');
                        $this->form_validation->set_rules('bobot[' . $no . ']', 'Bobot #' . ($no + 1), 'trim|required');
                        $this->form_validation->set_rules('mandays[' . $no . ']', 'Mandays #' . ($no + 1), 'trim|required');
                        $no++;
                    }
                    $this->form_validation->set_message('required', '%s harus diisi.');
                    $this->form_validation->set_message('greater_than', '%s harus lebih besar dari nol.');
                    $this->form_validation->set_message('Check_aktifitas', '%s Nama Aktifitas Sudah ada !');
                    if ($this->form_validation->run() == TRUE) {
                        $terinput  = 0;
                        $tahapan   = 1;
                        foreach ($_POST['aktifitas_num'] as $key => $value) {
                            $id_aktifitas     = $value;
                            $nm_aktifitas     = $_POST['nm_aktifitas'][$key];
                            $harga_aktifitas  = $_POST['hrg_aktifitas'][$key];
                            $bobot            = $_POST['bobot'][$key];
                            $mandays          = $_POST['mandays'][$key];
                            $unik_id          = $_POST['aktifitas_unique_id'][$key];

                            if (! empty($value)) {
                                $cek_id = $this->db->select('id_aktifitas')->where('id_aktifitas', $id_aktifitas)->get('kons_master_aktifitas');
                                if (! empty($unik_id) or ($cek_id->num_rows() > 0)) {
                                    ## I. UPDATE AKTIFITAS
                                    $aktifitas = array(
                                        'nm_aktifitas'    => $this->clean_tag_input($nm_aktifitas),
                                        'harga_aktifitas' => $harga_aktifitas,
                                        'bobot'           => $bobot,
                                        'mandays'         => $mandays,
                                        'update_date'     => date('Y-m-d H:i:s'),
                                        'update_by'       => $this->session->userdata('usr_username')
                                    );
                                    $update_db = $this->db
                                        ->where('unique_id', $unik_id)
                                        ->where('id_aktifitas', $id_aktifitas)
                                        ->update('kons_master_aktifitas', $aktifitas);
                                    if ($update_db) {
                                        $terinput++;
                                    }
                                }
                            }
                        }
                        if ($terinput > 0) {
                            $pesan  = "Data Successfully Updated";
                            $params['redirect_page']     = "YES";
                            $params['redirect_page_URL'] = site_url('master-aktifitas');
                            echo $this->query_success($pesan, $params);
                        } else {
                            echo $this->query_error('Terjadi kesalahan, coba lagi');
                        }
                    } else {
                        echo $this->input_error();
                    }
                }
            } else {
                echo $this->query_error("Mohon tambahkan aktifitas baru.");
            }
        } else {
            $dt['id_aktifitas'] = $id_aktifitas;
            $dt['aktifitas']    = $this->db->where('id_aktifitas', $id_aktifitas)->get('kons_master_aktifitas');
            // $this->load->view('master_aktifitas/aktifitas_edit', $dt);

            $this->template->set($dt);
            $this->template->render('aktifitas_edit');
        }
    }


    /*
     * --------------------------------------------------------------------
     * Deleted Master aktifitas
     * --------------------------------------------------------------------
     */
    public function aktifitas_delete($id_aktifitas)
    {
        if (! $this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            //##&## INSERT ACTIVITY USERS
            $nm_aktifitas = $this->db->where('id_aktifitas', $id_aktifitas)->get('kons_master_aktifitas')->row();
            H_activity_record("Master Aktifitas > Delete Aktifitas > " . $nm_aktifitas->nm_aktifitas);

            $deleted = $this->db->where('id_aktifitas', $id_aktifitas)->delete('kons_master_aktifitas');
            if ($deleted) {
                //###%&# DELETE SEMUA CHECK POINT JIKA ADA
                $this->cpoint_delete_all($id_aktifitas);

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
     * FUNCTION FOR ADD DATA CHECK POINT
     * --------------------------------------------------------------------
     *
     * Fetch data Check Point
     *
     */
    public function aktifitas_check_point($id_aktifitas = NULL)
    {
        $post = $this->input->post();
        // print_r($post);
        // exit;

        $this->db->trans_begin();
        $msg = '';
        $valid = 1;
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            if ($_POST) {

                // if (!empty($post['check_point'])) {
                //     $total = 0;
                //     foreach ($post['check_point'] as $k) {
                //         if (! empty($k)) {
                //             $total++;
                //         }
                //     }

                //     if ($total > 0) {

                //     } else {
                //         // echo $this->query_error("");
                //         $msg = 'Harap masukan minimal 1 point !';
                //     }
                // } else {
                //     // echo $this->query_error("");
                //     $msg = 'Harap masukan minimal 1 point !';
                // }

                if (count(array_unique($post['check_point'])) < count($post['check_point'])) {
                    $msg = 'Maaf, tidak boleh ada point yang sama !';
                } else {
                    $unique_id = sha1(time(microtime()));
                    $cek_id    = $this->db
                        ->select('id_aktifitas')
                        ->where('id_aktifitas', $this->input->get('id_aktifitas'))
                        ->get('kons_master_aktifitas');

                    ## JIKA ID_AKTIFITAS SUDAH ADA ##
                    if ($cek_id->num_rows() > 0) {
                        ## UPDATE AKTIFITAS
                        $aktifitas = array(
                            // 'id_paket'          => $this->input->get('id_paket'),
                            'datet'             => date('Y-m-d'),
                            'nm_aktifitas'      => $this->input->get('nm_aktifitas'),
                            'harga_aktifitas'   => $this->input->get('hrg_aktifitas'),
                            'bobot'             => $this->input->get('bobot'),
                            'mandays'           => $this->input->get('mandays'),

                            'keterangan'        => '',
                            'id_kompetensi'     => '0',
                            'unique_id'         => $unique_id,
                            'update_date'       => date('Y-m-d H:i:s'),
                            'update_by'         => $this->session->userdata('usr_username')
                        );
                        //'tahapan'           => '',

                        $terinput    = 0;
                        $u_aktifitas = $this->db
                            ->where('id_aktifitas', $this->input->get('id_aktifitas'))
                            ->update('kons_master_aktifitas', $aktifitas);
                        if ($u_aktifitas) {
                            foreach ($post['check_point'] as $key => $value) {
                                $nm_chk_point = $value;
                                $id_chk_point = $post['id_chk_point'][$key];
                                if (! empty($value)) {
                                    if (! empty($id_chk_point)) {
                                        #%# UPDATE CHECK POINT #%#
                                        $point = array(
                                            // 'id_paket'      => $this->input->get('id_paket'),
                                            'id_aktifitas'  => $this->input->get('id_aktifitas'),
                                            'nm_chk_point'  => $nm_chk_point
                                        );
                                        $this->db
                                            ->where('id_chk_point', $id_chk_point)
                                            ->where('id_aktifitas', $this->input->get('id_aktifitas'))
                                            ->update('kons_master_check_point', $point);

                                        #%# REMOVE ONE OR ALL ROW JIKA DIPILIH DELETE #%#
                                        if (! empty($post['unik_id'])) {
                                            $this->cpoint_delete_rest($this->input->get('id_aktifitas'), $post['unik_id']);
                                        }
                                        if (empty($post['unik_id'])) {
                                            $this->cpoint_delete_all($this->input->get('id_aktifitas'));
                                        }
                                    } else {
                                        #%# INSERT CHECK POINT #%#
                                        $i_point = array(
                                            // 'id_paket'      => $this->input->get('id_paket'),
                                            'id_paket'      => '0',
                                            'id_aktifitas'  => $this->input->get('id_aktifitas'),
                                            'nm_chk_point'  => $nm_chk_point,
                                            'unique_id'     => $unique_id . '-' . $key
                                        );
                                        $this->db->insert('kons_master_check_point', $i_point);
                                    }
                                }

                                $terinput++;
                            }
                        }
                    } else {
                        ## JIKA ID_AKTIFITAS BELUM ADA ##
                        $row = $cek_id->row();
                        if (empty($row->id_aktifitas) or $row->id_aktifitas == '') {
                            $loop      = 0;
                            $terinput  = 0;
                            ## I. INSERT AKTIFITAS
                            $aktifitas = array(
                                'id_aktifitas'      => $this->input->get('id_aktifitas'),
                                // 'id_paket'          => $this->input->get('id_paket'),
                                'id_paket'          => '0',
                                'datet'             => date('Y-m-d'),
                                'nm_aktifitas'      => $this->input->get('nm_aktifitas'),
                                'harga_aktifitas'   => $this->input->get('hrg_aktifitas'),
                                'bobot'             => $this->input->get('bobot'),
                                'mandays'           => $this->input->get('mandays'),
                                'tahapan'           => +1,
                                'keterangan'        => '',
                                'id_kompetensi'     => '0',
                                'unique_id'         => $unique_id,
                                'input_date'        => date('Y-m-d H:i:s'),
                                'input_by'          => $this->session->userdata('usr_username')
                            );
                            $i_aktifitas = $this->db->insert('kons_master_aktifitas', $aktifitas);
                            foreach ($post['check_point'] as $z) {
                                ## II. INSERT CHECK POINT
                                $point = array(
                                    // 'id_paket'      => $this->input->get('id_paket'),
                                    'id_paket'          => '0',
                                    'id_aktifitas'  => $this->input->get('id_aktifitas'),
                                    'nm_chk_point'  => $z,
                                    'unique_id'     => $unique_id . '-' . $loop
                                );
                                $this->db->insert('kons_master_check_point', $point);
                                $terinput++;
                                $loop++;
                            }
                        }
                    }

                    // if ($terinput > 0) {
                    //     $pesan  = "Data Successfully Saved";
                    //     $params['redirect_page']     = "YES";
                    //     $params['redirect_page_URL'] = site_url('master-konsultan');
                    //     //hitung jumlah point yg diinput
                    //     $params['count_point']       = $this->db->where('id_aktifitas', $this->input->get('id_aktifitas'))->get('kons_master_check_point')->num_rows();
                    //     $params['indexnya']          = $this->input->get('index_parent');
                    //     echo $this->query_success($pesan, $params);
                    // } else {
                    //     echo $this->query_error('Terjadi kesalahan, coba lagi');
                    // }

                    if ($this->db->trans_status() === false) {
                        $this->db->trans_rollback();
                        $msg = 'Please try again later';
                        $valid = 0;
                    } else {
                        $this->db->trans_commit();
                        $msg = 'Data successfully saved!';
                        $valid = 1;
                    }
                }

                echo json_encode([
                    'status' => $valid,
                    'msg' => $msg,
                    'count_point' => $this->db->where('id_aktifitas', $this->input->get('id_aktifitas'))->get('kons_master_check_point')->num_rows(),
                    'indexnya' =>  $this->input->get('index_parent')
                ]);
            } else {
                $variables  = "nm_aktifitas=" . $this->input->get('nm_aktifitas')[0];
                $variables .= "&hrg_aktifitas=" . $this->input->get('hrg_aktifitas')[0];
                $variables .= "&bobot=" . $this->input->get('bobot')[0];
                $variables .= "&mandays=" . $this->input->get('mandays')[0];
                $variables .= "&id_aktifitas=" . $id_aktifitas;
                $variables .= "&index_parent=" . $this->input->get('indexnya');

                $dt['variables'] = $variables;
                $dt['cek_point'] = $this->db
                    ->select('id_chk_point, id_paket, id_aktifitas, nm_chk_point, unique_id')
                    ->where('id_aktifitas', $id_aktifitas)
                    ->order_by('id_chk_point', 'asc')
                    ->get('kons_master_check_point');

                $this->template->set($dt);
                $this->template->render('aktifitas_new_checkpoint');
            }
        }
    }

    /*
    # #######################################################################
    # ################### DELETE CHECK POINT ALL ROW ########################
    # #######################################################################
    */
    function aktifitas_delete_point($id_aktifitas)
    {
        if (! $this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $loop = $this->db
                ->select('id_chk_point')
                ->where('id_aktifitas', $id_aktifitas)
                ->get('kons_master_check_point');

            if ($loop->num_rows() > 0) {
                foreach ($loop->result() as $n) {
                    $hapus = $this->db
                        ->where('id_chk_point', $n->id_chk_point)
                        ->delete('kons_master_check_point');
                }
                // $hapus_aktifitas = $this->db->where('id_aktifitas', $id_aktifitas)->delete('kons_master_aktifitas');
                echo json_encode(array(
                    'status' => 1,
                    'indexnya' => $this->input->post('indexnya'),
                    'pesan' => 'Data Successfully Deleted'
                ));
            } else {
                echo $this->query_error('Terjadi kesalahan, coba lagi');
            }
        }
    }

    /*
     * --------------------------------------------------------------------
     * Check Nama aktifitas
     * --------------------------------------------------------------------
     *
     * Fungsi Check Nama aktifitas
     *
     */
    function Check_aktifitas($nama)
    {
        $cek = $this->db
            ->select('id_aktifitas')
            ->where('nm_aktifitas', $nama)
            ->limit(1)
            ->get('kons_master_aktifitas');
        if ($cek->num_rows() > 0) {
            return FALSE;
        }

        return TRUE;
    }


    /*
    # #######################################################################
    # ################### DELETE CHECK POINT ONE ROW ########################
    # #######################################################################
    */
    function cpoint_delete_rest($id_aktifitas, $unik_id)
    {
        $loop = $this->db
            ->select('id_chk_point')
            ->where_not_in('unique_id', $unik_id)
            ->where('id_aktifitas', $id_aktifitas)
            ->get('kons_master_check_point');

        if ($loop->num_rows() > 0) {
            foreach ($loop->result() as $n) {
                $hapus = $this->db
                    ->where('id_chk_point', $n->id_chk_point)
                    ->delete('kons_master_check_point');
            }
        }

        return TRUE;
    }

    /*
    # #######################################################################
    # ################### DELETE CHECK POINT ALL ROW ########################
    # #######################################################################
    */
    function cpoint_delete_all($id_aktifitas)
    {
        $loop = $this->db
            ->select('id_chk_point')
            ->where('id_aktifitas', $id_aktifitas)
            ->get('kons_master_check_point');

        if ($loop->num_rows() > 0) {
            foreach ($loop->result() as $n) {
                $hapus = $this->db
                    ->where('id_chk_point', $n->id_chk_point)
                    ->delete('kons_master_check_point');
            }
        }

        return TRUE;
    }

    function delete_aktifitas()
    {
        $id = $this->input->post('id');

        $this->db->trans_begin();

        $this->db->delete('kons_master_check_point', ['id_aktifitas' => $id]);
        $this->db->delete('kons_master_aktifitas', ['id_aktifitas' => $id]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $msg = 'Sorry, data has not been deleted !';
            $valid = 0;
        } else {
            $this->db->trans_commit();
            $msg = 'Data has been deleted successfully !';
            $valid = 1;
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }
}

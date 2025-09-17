<?php

/**
 * ------------------------------------------------------------------------
 * Class Name : Master Konsultasi
 * ------------------------------------------------------------------------
 *
 * @author     Dandoridwanto
 * @copyright  2018
 *
 * Last Update : Friday, 01 April 2018
 *
 */

class Master_konsultasi extends Admin_Controller
{
    /*
     * --------------------------------------------------------------------
     * Constructor
     * --------------------------------------------------------------------
     */

    protected $viewPermission   = 'Master_Konsultasi.View';
    protected $addPermission    = 'Master_Konsultasi.Add';
    protected $managePermission = 'Master_Konsultasi.Manage';
    protected $deletePermission = 'Master_Konsultasi.Delete';

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
        $this->template->render('konsultasi_view');
    }

    function display_konsultasi_json()
    {
        $requestData    = $_REQUEST;
        $fetch          = $this->fetch_data_konsultasi(
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
            $nestedData[]   = $row['id_konsultasi_h'];
            $nestedData[]   = $row['nm_paket'];
            $nestedData[]   = $row['datetimes'];

            $edit_btn = '';
            if ($this->managePermission) {
                $edit_btn = "
                    <a href='" . site_url('master_konsultasi/konsultasi_edit/' . $row['id_konsultasi_h']) . "' class='btn btn-warning btn-xs'>
                        <i class='fa fa-refresh'></i> Update
                    </a>
                ";
            }

            $delete_btn = '';
            if ($this->deletePermission) {
                $delete_btn = "
                    <a href='javascript:void(0);' 
                        class='btn btn-danger btn-xs delete_konsultasi'
                        id='DeleteConfirm'
                        data-id='" . $row['id_konsultasi_h'] . "'>
                        <i class='fa fa-remove'></i> Delete 
                    </a>
                ";
            }

            $nestedData[]   = "
                <div class='btn-group'>
                    <a href='" . site_url('master_konsultasi/konsultasi_detail/' . $row['id_konsultasi_h']) . "' class='btn btn-info btn-xs' id='ShowModal' data-header='Detail Aktifitas' data-class='modal-xxl' data-type='load'>
                        <i class='fa fa-file-text'></i> View
                    </a>
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
    function fetch_data_konsultasi($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $sql = "
            SELECT 
                (@row:=@row+1) AS nomor,
                DATE_FORMAT(a.input_date, '%d-%m-%Y %H:%i') AS datetimes,
                a.*,
                c.kategori_paket,
                b.nm_paket 
            FROM 
                kons_master_konsultasi_header AS a
                LEFT JOIN kons_master_paket AS b ON a.id_paket = b.id_paket
                LEFT JOIN kons_kategori_paket AS c ON b.id_kategori = c.id_kategori_paket,
                (SELECT @row := 0) r 
            WHERE 1=1 AND (
                c.kategori_paket LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
                OR b.nm_paket LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
                OR DATE_FORMAT(a.input_date, '%d-%m-%Y %H:%i') LIKE '%" . $this->db->escape_like_str($like_value) . "%' 
            )
        ";

        $data['totalData'] = $this->db->query($sql)->num_rows();


        $data['totalFiltered']  = $this->db->query($sql)->num_rows();
        $columns_order_by = array(
            0 => 'nomor'
        );
        $sql .= " ORDER BY id_konsultasi_h DESC ";
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
    public function konsultasi_new($id_paket = NULL)
    {
        if ($_POST) {
            $this->db->trans_begin();

            $post = $this->input->post();

            $valid = 1;
            $msg = '';
            if (!empty($post['nm_aktifitas'])) {
                $total = 0;
                foreach ($post['nm_aktifitas'] as $k) {
                    // if (! empty($k)) {
                    $total++;
                    // }
                }

                // print_r($total);
                // exit;

                if ($total > 0) {
                    $this->load->library('form_validation');
                    $no = 0;
                    // foreach ($post['nm_aktifitas'] as $i) {
                    //     $this->form_validation->set_rules('nm_aktifitas[' . $no . ']', 'Aktifitas #' . ($no + 1), 'trim|required|callback_Check_aktifitas[nm_aktifitas[' . $no . ']]');
                    //     $this->form_validation->set_rules('hrg_aktifitas[' . $no . ']', 'Harga #' . ($no + 1), 'trim|required|greater_than[0]|max_length[1000000]');
                    //     $this->form_validation->set_rules('bobot[' . $no . ']', 'Bobot #' . ($no + 1), 'trim|required');
                    //     $this->form_validation->set_rules('mandays[' . $no . ']', 'Mandays #' . ($no + 1), 'trim|required');
                    //     $no++;
                    // }
                    // $this->form_validation->set_message('required', '%s harus diisi.');
                    // $this->form_validation->set_message('greater_than', '%s harus lebih besar dari nol.');
                    // $this->form_validation->set_message('Check_aktifitas', '%s Nama Aktifitas Sudah ada !');
                    // if ($this->form_validation->run() == TRUE) {

                    // } else {
                    //     $valid = 0;
                    //     $msg = 'Gagal !';
                    // }

                    if (count(array_unique($post['nm_aktifitas'])) < count($post['nm_aktifitas'])) {
                        $valid = 0;
                        $msg = 'Maaf, tidak boleh ada nama aktifitas yang sama !';
                    } else {
                        $cek_paket = "SELECT id_paket FROM kons_master_konsultasi_header WHERE id_paket = '" . $post['konsultasi'] . "'";
                        $cek_paket = $this->db->query($cek_paket);
                        if ($cek_paket->num_rows() > 0) {
                            $valid = 0;
                            $msg = 'Maaf, paket sudah ada silahkan pilih yg lain atau update Activity.';
                        } else {
                            $terinput  = 0;
                            $tahapan   = 1;

                            ### .............................................................................
                            ### GET UNIQUE CODE FOR PRIMARY KEY
                            ### .............................................................................
                            $Max = "
                                SELECT 
                                    id_konsultasi_h 
                                FROM 
                                    kons_master_konsultasi_header  
                                WHERE 
                                    datet LIKE '" . DATE('Y-') . "%' ORDER BY id_konsultasi_h DESC 
                                LIMIT 1
                            ";
                            $Max   = $this->db->query($Max);
                            $nilai = 1;
                            if ($Max->num_rows() > 0) {
                                $nilai = intval(substr($Max->row()->id_konsultasi_h, -5)) + 1;
                            }
                            $kode = 'KONS-' . date('Y') . '-' . sprintf('%05d', $nilai);

                            $head['id_konsultasi_h'] = $kode;
                            $head['id_paket']        = $post['konsultasi'];
                            $head['datet']           = date('Y-m-d');
                            $head['input_date']      = date('Y-m-d H:i:s');
                            $head['input_by']        = $this->session->userdata('usr_username');
                            $this->db->insert('kons_master_konsultasi_header', $head);

                            foreach ($post['id_aktifitas'] as $key => $value) {
                                $explode          = explode("*_*", $value);
                                $id_aktifitas     = $explode[0];
                                $nm_aktifitas     = $post['nm_aktifitas'][$key];
                                $harga_aktifitas  = $post['hrg_aktifitas'][$key];
                                $bobot            = $post['bobot'][$key];
                                $mandays          = $post['mandays'][$key];

                                if (! empty($value)) {
                                    $detail['id_konsultasi_h']  = $kode;
                                    $detail['id_aktifitas']     = $id_aktifitas;
                                    $detail['nm_aktifitas']     = $nm_aktifitas;
                                    $detail['harga_aktifitas']  = $harga_aktifitas;
                                    $detail['bobot']            = $bobot;
                                    $detail['mandays']          = $mandays;
                                    $detail['tahapan']          = $tahapan;
                                    $detail['input_date']       = date('Y-m-d H:i:s');
                                    $detail['input_by']         = $this->session->userdata('usr_username');
                                    $detail_input = $this->db->insert('kons_master_konsultasi_detail', $detail);
                                    if ($detail_input) {
                                        $terinput++;
                                    } else {
                                        print_r($this->db->error($detail_input));
                                        $this->db->trans_rollback();
                                        exit;
                                    }

                                    /*$cek_id = $this->db->select('id_aktifitas')->where('id_aktifitas', $id_aktifitas)->get('kons_master_aktifitas');
                                    if(! empty($unik_id) OR ($cek_id->num_rows() > 0))
                                    {
                                        ## I. UPDATE AKTIFITAS
                                        $aktifitas = array(
                                            'id_paket'        => $id_paket,
                                            'datet'           => date('Y-m-d'),
                                            'nm_aktifitas'    => $nm_aktifitas,
                                            'harga_aktifitas' => $harga_aktifitas,
                                            'bobot'           => $bobot,
                                            'mandays'         => $mandays,
                                            'tahapan'         => $tahapan,
                                            'keterangan'      => '',
                                            'id_kompetensi'   => ''
                                        );
                                        $update_db = $this->db
                                            ->where('unique_id', $unik_id)
                                            ->where('id_aktifitas', $id_aktifitas)
                                            ->update('kons_master_aktifitas', $aktifitas);
                                        if($update_db)
                                        {
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

                                    }
                                    else
                                    {
                                        if($cek_id->num_rows() < 1)
                                        {
                                            ## II. INSERT AKTIFITAS
                                            $aktifitas = array(
                                                'id_aktifitas'    => $id_aktifitas,
                                                'id_paket'        => $id_paket,
                                                'datet'           => date('Y-m-d'),
                                                'nm_aktifitas'    => $nm_aktifitas,
                                                'harga_aktifitas' => $harga_aktifitas,
                                                'bobot'           => $bobot,
                                                'mandays'         => $mandays,
                                                'tahapan'         => $tahapan,
                                                'keterangan'      => '',
                                                'id_kompetensi'   => '',
                                                'unique_id'       => $unique_id
                                            );
                                            $insert_db = $this->db->insert('kons_master_aktifitas', $aktifitas);
                                            if($insert_db)
                                            {
                                                $terinput++;
                                            }
                                        }
                                    }*/
                                }

                                $tahapan++;
                            }

                            // if ($terinput > 0) {
                            //     $pesan  = "Data Successfully Saved";
                            //     $params['redirect_page'] = "YES";
                            //     $params['redirect_page_URL'] = site_url('master_konsultasi');
                            //     echo $this->query_success($pesan, $params);
                            // } else {
                            //     echo $this->query_error('Terjadi kesalahan, coba lagi');
                            // }

                            if ($this->db->trans_status() === false) {
                                $valid = 0;
                                $msg = 'Please try again later !';
                            } else {
                                $valid = 1;
                                $msg = 'Data Successfully Saved';
                            }
                        }
                    }
                } else {
                    $valid = 0;
                    $msg = 'Harap masukan minimal 1 nama aktifitas.';
                }
            } else {
                $valid = 0;
                $msg = 'Mohon tambahkan aktifitas baru.';
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
            $sql = "
                SELECT 
                    a.id_aktifitas,
                    a.id_paket,
                    a.datet,
                    a.nm_aktifitas,
                    a.harga_aktifitas,
                    a.bobot,
                    a.mandays,
                    a.tahapan,
                    a.keterangan,
                    a.id_kompetensi,
                    a.unique_id 
                FROM 
                    kons_master_aktifitas AS a 
                WHERE 
                    a.id_paket = '" . $id_paket . "'
            ";
            $dt['id_paket']  = $id_paket;
            $dt['aktifitas'] = $this->db->query($sql);
            $dt['paket']     = $this->db->select('id_paket, nm_paket')->order_by('nm_paket', 'asc')->get('kons_master_paket');
            $dt['all_aktifitas'] = $this->db->order_by('datet', 'desc')->get('kons_master_aktifitas');

            $this->template->set($dt);
            $this->template->render('konsultasi_new');
        }
    }


    /*
     * --------------------------------------------------------------------
     * FUNCTION EDIT KONSULTAN
     * --------------------------------------------------------------------
     *
     * Proses edit master konsultan
     *
     */
    public function konsultasi_edit($id_konsultasi = NULL)
    {
        if ($_POST) {
            $post = $this->input->post();

            $this->db->trans_begin();

            $valid = 1;
            $msg = '';
            if (! empty($post['nm_aktifitas'])) {
                $total = 0;
                foreach ($post['nm_aktifitas'] as $k) {
                    if (! empty($k)) {
                        $total++;
                    }
                }

                if ($total > 0) {
                    $no = 0;

                    if (count(array_unique($post['nm_aktifitas'])) < count($post['nm_aktifitas'])) {
                        $valid = 0;
                        $msg = '<i class="fa fa-remove"></i> Maaf, tidak boleh ada nama aktifitas yang sama !';
                    } else {
                        $terinput  = 0;
                        $tahapan   = 1;
                        $head['update_date'] = date('Y-m-d H:i:s');
                        $head['update_by']   = $this->session->userdata('usr_username');
                        $this->db->where('id_konsultasi_h', $id_konsultasi)->update('kons_master_konsultasi_header', $head);

                        //##%# REMOVE ONE OR ALL ROW JIKA DIPILIH DELETE #%#
                        if (! empty($post['id_konsultasi_d'])) {
                            $this->aktifitas_delete_rest($id_konsultasi, $post['id_konsultasi_d']);
                        }

                        foreach ($post['id_aktifitas'] as $key => $value) {
                            $explode          = explode("*_*", $value);
                            $id_aktifitas     = $explode[0];
                            $nm_aktifitas     = $post['nm_aktifitas'][$key];
                            $harga_aktifitas  = $post['hrg_aktifitas'][$key];
                            $bobot            = $post['bobot'][$key];
                            $mandays          = $post['mandays'][$key];
                            if (! empty($value)) {
                                if (! empty($post['id_konsultasi_d'][$key])) {
                                    $detail['nm_aktifitas']     = $nm_aktifitas;
                                    $detail['harga_aktifitas']  = $harga_aktifitas;
                                    $detail['bobot']            = $bobot;
                                    $detail['mandays']          = $mandays;
                                    $detail['tahapan']          = $tahapan;
                                    $detail['update_date']      = date('Y-m-d H:i:s');
                                    $detail['update_by']        = $this->auth->user_name();
                                    $detail_input = $this->db->where('id_konsultasi_d', $post['id_konsultasi_d'][$key])->update('kons_master_konsultasi_detail', $detail);
                                    if ($detail_input) {
                                        $terinput++;
                                    }
                                } else {
                                    $new['id_konsultasi_h']  = $id_konsultasi;
                                    $new['id_aktifitas']     = $id_aktifitas;
                                    $new['nm_aktifitas']     = $nm_aktifitas;
                                    $new['harga_aktifitas']  = $harga_aktifitas;
                                    $new['bobot']            = $bobot;
                                    $new['mandays']          = $mandays;
                                    $new['tahapan']          = $tahapan;
                                    $new['input_date']       = date('Y-m-d H:i:s');
                                    $new['input_by']         = $this->auth->user_name();
                                    $new_input = $this->db->insert('kons_master_konsultasi_detail', $new);
                                    if ($new_input) {
                                        $terinput++;
                                    } else {
                                        $this->db->trans_rollback();
                                        print_r($this->db->error($new_input));
                                        exit;
                                    }
                                }
                            }
                            $tahapan++;
                        }

                        // if ($terinput > 0) {
                        //     $pesan  = "Data Successfully Saved";
                        //     $params['redirect_page'] = "YES";
                        //     $params['redirect_page_URL'] = site_url('master-konsultasi');
                        //     echo $this->query_success($pesan, $params);
                        // } else {
                        //     echo $this->query_error('Terjadi kesalahan, coba lagi');
                        // }

                        if ($this->db->trans_status() === false) {
                            $valid = 0;
                            $msg = 'Please try again later!';
                        } else {
                            $valid = 1;
                            $msg = 'Data Successfully Saved';
                        }
                    }
                } else {
                    $valid = 0;
                    $msg = 'Harap masukan minimal 1 nama aktifitas.';
                }
            } else {
                $valid = 0;
                $msg = 'Mohon tambahkan aktifitas baru.';
            }

            echo json_encode([
                'status' => $valid,
                'msg' => $msg,
                'count_point' => $this->db->where('id_aktifitas', $id_aktifitas)->get('kons_master_check_point')->num_rows()
            ]);
        } else {
            $dt['id_konsultasi'] = $id_konsultasi;
            $dt['header']        = $this->db->where('id_konsultasi_h', $id_konsultasi)->get('kons_master_konsultasi_header')->row();
            $dt['detail']        = $this->db->query("SELECT * FROM kons_master_konsultasi_detail WHERE id_konsultasi_h = '" . $id_konsultasi . "'");
            $dt['paket']         = $this->db->where('id_paket', $dt['header']->id_paket)->get('kons_master_paket');
            $dt['all_aktifitas'] = $this->db->order_by('nm_aktifitas', 'asc')->get('kons_master_aktifitas');

            $this->template->set($dt);
            $this->template->render('konsultasi_edit');
        }
    }


    /*
     * --------------------------------------------------------------------
     * FUNCTION FOR GET DATA AKTIFITAS
     * --------------------------------------------------------------------
     *
     * Fetch data aktifitas with json
     *
     */
    function get_data_aktifitas()
    {
        $post = $this->input->post();

        $explode = explode("*_*", $post['id_aktifitas']);
        $id_aktv = $explode[0];
        $aktif   = $this->db->where('id_aktifitas', $id_aktv)->limit(1)->get('kons_master_aktifitas');
        if ($aktif->num_rows() > 0) {
            $rows    = $aktif->row();
            $nm_aktifitas = $rows->nm_aktifitas;
            $harga   = $rows->harga_aktifitas;
            $bobot   = $rows->bobot;
            $mandays = $rows->mandays;
        } else {
            $nm_aktifitas = '';
            $harga   = '';
            $bobot   = '';
            $mandays = '';
        }
        echo json_encode(array(
            'status' => 1,
            'id_aktifitas' => $id_aktv,
            'nm_aktifitas' => $nm_aktifitas,
            'harga'  => $harga,
            'bobot'  => $bobot,
            'mandays' => $mandays,
            'total_chk' => @$this->db->where('id_aktifitas', $id_aktv)->get('kons_master_check_point')->num_rows()
        ));
    }

    /*
     * --------------------------------------------------------------------
     * FUNCTION DELETE DATA KONSULTAN
     * --------------------------------------------------------------------
     *
     * Proses delete master konsultan
     *
     */
    public function konsultasi_delete($id_konsultasi)
    {
        if (! $this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            // $dt['status'] = 'inactive';
            $delete = $this->db->where('id_konsultasi_h', $id_konsultasi)->delete('kons_master_konsultasi_header');
            if ($delete) {
                $this->db->where('id_konsultasi_h', $id_konsultasi)->delete('kons_master_konsultasi_detail');
                $pesan  = "Data Successfully Deleted";
                $params['datatable_reload'] = "#my-grid";
                echo $this->query_success($pesan, $params);
            }
        }
    }


    /*
     * --------------------------------------------------------------------
     * FUNCTION VIEW DETAIL KONSULTAN
     * --------------------------------------------------------------------
     *
     * Proses view data konsultan
     *
     */
    function konsultasi_detail($id_konsultasi)
    {
        // if (!$this->input->is_ajax_request()) {
        //     exit('No direct script access allowed');
        // } else {
        //     $sql_header = "SELECT * FROM kons_master_konsultasi_header WHERE id_konsultasi_h = '" . $id_konsultasi . "'";
        //     $sql_detail = "SELECT * FROM kons_master_konsultasi_detail WHERE id_konsultasi_h = '" . $id_konsultasi . "' ORDER BY tahapan";
        //     $dt['konsultasi_header'] = $this->db->query($sql_header);
        //     $dt['konsultasi_detail'] = $this->db->query($sql_detail);
        //     $dt['paket'] = $this->db
        //         ->select('id_paket, nm_paket')
        //         ->order_by('nm_paket', 'asc')
        //         ->get('kons_master_paket');

        //     // $this->load->vars($dt);
        //     // $this->load->view('master_konsultasi/konsultasi_detail');

        //     $this->template->set($dt);
        //     $this->template->render('konsultasi_detail');
        // }

        $sql_header = "SELECT * FROM kons_master_konsultasi_header WHERE id_konsultasi_h = '" . $id_konsultasi . "'";
        $sql_detail = "SELECT * FROM kons_master_konsultasi_detail WHERE id_konsultasi_h = '" . $id_konsultasi . "' ORDER BY tahapan";
        $dt['konsultasi_header'] = $this->db->query($sql_header);
        $dt['konsultasi_detail'] = $this->db->query($sql_detail);
        $dt['paket'] = $this->db
            ->select('id_paket, nm_paket')
            ->order_by('nm_paket', 'asc')
            ->get('kons_master_paket');

        // $this->load->vars($dt);
        // $this->load->view('master_konsultasi/konsultasi_detail');

        $this->template->set($dt);
        $this->template->render('konsultasi_detail');
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

    /*
    # #######################################################################
    # ################### DELETE ACTIVITY ONE ROW ###########################
    # #######################################################################
    */
    function aktifitas_delete_rest($id_konsultasi_h, $id_konsultasi_d)
    {
        $loop = $this->db
            ->select('id_konsultasi_d')
            ->where('id_konsultasi_h', $id_konsultasi_h)
            ->where_not_in('id_konsultasi_d', $id_konsultasi_d)
            ->get('kons_master_konsultasi_detail');

        if ($loop->num_rows() > 0) {
            foreach ($loop->result() as $n) {
                $hapus = $this->db
                    ->where('id_konsultasi_d', $n->id_konsultasi_d)
                    ->delete('kons_master_konsultasi_detail');
            }
        }

        return TRUE;
    }

    public function aktifitas_check_point($id_aktifitas = NULL)
    {
        if (! $this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            if ($_POST) {
                if (! empty($_POST['check_point'])) {
                    $total = 0;
                    foreach ($_POST['check_point'] as $k) {
                        if (! empty($k)) {
                            $total++;
                        }
                    }

                    if ($total > 0) {
                        $this->load->library('form_validation');
                        $no = 0;
                        foreach ($_POST['check_point'] as $i) {
                            $this->form_validation->set_rules('check_point[' . $no . ']', 'Check Point #' . ($no + 1), 'trim|required');
                            $no++;
                        }
                        $this->form_validation->set_message('required', '%s harus diisi.');
                        if ($this->form_validation->run() == TRUE) {
                            if (count(array_unique($_POST['check_point'])) < count($_POST['check_point'])) {
                                echo $this->query_error('Maaf, tidak boleh ada point yang sama !');
                            } else {
                                $unique_id = sha1(time(microtime()));
                                $cek_id    = $this->db
                                    ->select('id_aktifitas')
                                    ->where('id_aktifitas', $_GET['id_aktifitas'])
                                    ->get('kons_master_aktifitas');

                                ## JIKA ID_AKTIFITAS SUDAH ADA ##
                                if ($cek_id->num_rows() > 0) {
                                    ## UPDATE AKTIFITAS
                                    $aktifitas = array(
                                        // 'id_paket'          => $_GET['id_paket'],
                                        'datet'             => date('Y-m-d'),
                                        'nm_aktifitas'      => $_GET['nm_aktifitas'],
                                        'harga_aktifitas'   => $_GET['hrg_aktifitas'],
                                        'bobot'             => $_GET['bobot'],
                                        'mandays'           => $_GET['mandays'],

                                        'keterangan'        => '',
                                        'id_kompetensi'     => '0',
                                        'unique_id'         => $unique_id,
                                        'update_date'       => date('Y-m-d H:i:s'),
                                        'update_by'         => $this->session->userdata('usr_username')
                                    );
                                    //'tahapan'           => '',

                                    $terinput    = 0;
                                    $u_aktifitas = $this->db
                                        ->where('id_aktifitas', $_GET['id_aktifitas'])
                                        ->update('kons_master_aktifitas', $aktifitas);
                                    if ($u_aktifitas) {
                                        foreach ($_POST['check_point'] as $key => $value) {
                                            $nm_chk_point = $value;
                                            $id_chk_point = $_POST['id_chk_point'][$key];
                                            if (! empty($value)) {
                                                if (! empty($id_chk_point)) {
                                                    #%# UPDATE CHECK POINT #%#
                                                    $point = array(
                                                        // 'id_paket'      => $_GET['id_paket'],
                                                        'id_aktifitas'  => $_GET['id_aktifitas'],
                                                        'nm_chk_point'  => $nm_chk_point
                                                    );
                                                    $this->db
                                                        ->where('id_chk_point', $id_chk_point)
                                                        ->where('id_aktifitas', $_GET['id_aktifitas'])
                                                        ->update('kons_master_check_point', $point);

                                                    #%# REMOVE ONE OR ALL ROW JIKA DIPILIH DELETE #%#
                                                    if (! empty($_POST['unik_id'])) {
                                                        $this->cpoint_delete_rest($_GET['id_aktifitas'], $_POST['unik_id']);
                                                    }
                                                    if (empty($_POST['unik_id'])) {
                                                        $this->cpoint_delete_all($_GET['id_aktifitas']);
                                                    }
                                                } else {
                                                    #%# INSERT CHECK POINT #%#
                                                    $i_point = array(
                                                        // 'id_paket'      => $_GET['id_paket'],
                                                        'id_paket'      => '0',
                                                        'id_aktifitas'  => $_GET['id_aktifitas'],
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
                                            'id_aktifitas'      => $_GET['id_aktifitas'],
                                            // 'id_paket'          => $_GET['id_paket'],
                                            'id_paket'          => '0',
                                            'datet'             => date('Y-m-d'),
                                            'nm_aktifitas'      => $_GET['nm_aktifitas'],
                                            'harga_aktifitas'   => $_GET['hrg_aktifitas'],
                                            'bobot'             => $_GET['bobot'],
                                            'mandays'           => $_GET['mandays'],
                                            'tahapan'           => +1,
                                            'keterangan'        => '',
                                            'id_kompetensi'     => '0',
                                            'unique_id'         => $unique_id,
                                            'input_date'        => date('Y-m-d H:i:s'),
                                            'input_by'          => $this->session->userdata('usr_username')
                                        );
                                        $i_aktifitas = $this->db->insert('kons_master_aktifitas', $aktifitas);
                                        foreach ($_POST['check_point'] as $z) {
                                            ## II. INSERT CHECK POINT
                                            $point = array(
                                                // 'id_paket'      => $_GET['id_paket'],
                                                'id_paket'          => '0',
                                                'id_aktifitas'  => $_GET['id_aktifitas'],
                                                'nm_chk_point'  => $z,
                                                'unique_id'     => $unique_id . '-' . $loop
                                            );
                                            $this->db->insert('kons_master_check_point', $point);
                                            $terinput++;
                                            $loop++;
                                        }
                                    }
                                }

                                if ($terinput > 0) {
                                    $pesan  = "Data Successfully Saved";
                                    $params['redirect_page']     = "YES";
                                    $params['redirect_page_URL'] = site_url('master-konsultan');
                                    //hitung jumlah point yg diinput
                                    $params['count_point']       = $this->db->where('id_aktifitas', $_GET['id_aktifitas'])->get('kons_master_check_point')->num_rows();
                                    $params['indexnya']          = $_GET['index_parent'];
                                    echo $this->query_success($pesan, $params);
                                } else {
                                    echo $this->query_error('Terjadi kesalahan, coba lagi');
                                }
                            }
                        } else {
                            echo $this->input_error();
                        }
                    } else {
                        echo $this->query_error("Harap masukan minimal 1 point !");
                    }
                } else {
                    echo $this->query_error("Harap masukan minimal 1 point !");
                }
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

                $this->load->vars($dt);
                $this->load->view('aktifitas_new_checkpoint');
            }
        }
    }

    function delete_konsultasi()
    {
        $id = $this->input->post('id');

        $this->db->trans_begin();

        $valid = 1;
        $msg = '';
        $delete = $this->db->where('id_konsultasi_h', $id)->delete('kons_master_konsultasi_header');
        if ($delete) {
            $delete_detail = $this->db->where('id_konsultasi_h', $id)->delete('kons_master_konsultasi_detail');
            if (!$delete_detail) {
                $valid = 0;
                $msg  = "Please try again later !";
            }
        } else {
            $valid = 0;
            $msg = 'Please try again later !';
        }

        if ($valid == 1) {
            $this->db->trans_commit();
            $msg = 'Data successfully deleted !';
        } else {
            $this->db->trans_rollback();
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    /*
    # #######################################################################
    # ################### DELETE ACTIVITY ALL ROW ###########################
    # #######################################################################
    */
    /*function aktifitas_delete_all($id_konsultasi_h)
    {
        $loop = $this->db
            ->select('id_konsultasi_d')
            ->where('id_konsultasi_h', $id_konsultasi_h)
            ->get('kons_master_konsultasi_detail');

        if($loop->num_rows() > 0)
        {
            foreach($loop->result() as $n) 
            {
                $hapus = $this->db
                    ->where('id_konsultasi_d', $n->id_konsultasi_d)
                    ->delete('kons_master_konsultasi_detail');
            }
        }

        return TRUE;
    }*/


    /*
    # #######################################################################
    # ################### DELETE CHECK POINT ONE ROW ########################
    # #######################################################################
    */
    /*function cpoint_delete_rest($id_aktifitas, $unik_id)
    {
        $loop = $this->db
            ->select('id_chk_point')
            ->where_not_in('unique_id', $unik_id)
            ->where('id_aktifitas', $id_aktifitas)
            ->get('kons_master_check_point');

        if($loop->num_rows() > 0)
        {
            foreach($loop->result() as $n) 
            {
                $hapus = $this->db
                    ->where('id_chk_point', $n->id_chk_point)
                    ->delete('kons_master_check_point');
            }
        }

        return TRUE;
    }*/

    /*
    # #######################################################################
    # ################### DELETE CHECK POINT ALL ROW ########################
    # #######################################################################
    */
    /*function cpoint_delete_all($id_aktifitas)
    {
        $loop = $this->db
            ->select('id_chk_point')
            ->where('id_aktifitas', $id_aktifitas)
            ->get('kons_master_check_point');

        if($loop->num_rows() > 0)
        {
            foreach($loop->result() as $n) 
            {
                $hapus = $this->db
                    ->where('id_chk_point', $n->id_chk_point)
                    ->delete('kons_master_check_point');
            }
        }

        return TRUE;
    }*/
}

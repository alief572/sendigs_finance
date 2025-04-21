function add_bidangusaha()
    {
        $id_bidang_usaha    = $this->input->post("idbidus");
        $bidang_usaha       = strtoupper($this->input->post("bidang_usaha"));
        $keterangan         = strtoupper($this->input->post("keterangan"));


        if ($id_bidang_usaha != "") {
            $this->auth->restrict($this->addPermission);

            $data = array(
                array(
                    'id_bidang_usaha' => $id_bidang_usaha,
                    'bidang_usaha' => $bidang_usaha,
                    'keterangan' => $keterangan,
                )
            );
            //Update Data
            $id = $this->Bidus_model->update_batch($data, 'id_bidang_usaha');

            if (is_numeric($id)) {
                $keterangan     = "SUKSES, Edit data Bidang Usaha atas Nama : " . $bidang_usaha;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result = TRUE;
            } else {
                $keterangan     = "GAGAL, Edit data Bidang Usaha atas Nama : " . $bidang_usaha;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
        } else {
            $this->auth->restrict($this->addPermission);

            $data = array(
                'bidang_usaha' => $bidang_usaha,
                'keterangan' => $keterangan,
            );

            //Add Data
            $id = $this->Bidus_model->insert($data);

            if (is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data Bidang Usaha atas Nama : " . $bidang_usaha;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result = TRUE;
            } else {
                $keterangan     = "GAGAL, tambah data Bidang Usah atas Nama : " . $bidang_usaha;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
        }

        //Save Log
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        $param = array(
            'save' => $result
        );

        echo json_encode($param);
    }


    function add_syaratdok()
    {
        $id_syarat  = $this->input->post("id_syarat");
        $nm_syarat  = strtoupper($this->input->post("nm_syarat"));

        if ($id_syarat != "") {
            $this->auth->restrict($this->addPermission);

            $data = array(
                array(
                    'id_syarat' => $id_syarat,
                    'nm_syarat' => $nm_syarat,
                )
            );
            //Update Data
            $id = $this->Syarat_tagih_model->update_batch($data, 'id_syarat');

            if (is_numeric($id)) {
                $keterangan     = "SUKSES, Edit data Syarat Penagihan Dengan Nama : " . $nm_syarat;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result = TRUE;
            } else {
                $keterangan     = "GAGAL, Edit data Syarat Penagihan Dengan Nama : " . $nm_syarat;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
        } else {
            $this->auth->restrict($this->addPermission);

            $data = array(
                'nm_syarat' => $nm_syarat,
            );

            //Add Data
            $id = $this->Syarat_tagih_model->insert($data);

            if (is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data Syarat Penagihan Dengan Nama : " . $nm_syarat;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result = TRUE;
            } else {
                $keterangan     = "GAGAL, tambah data Syarat Penagihan Dengan Nama : " . $nm_syarat;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
        }

        //Save Log
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        $param = array(
            'save' => $result
        );

        echo json_encode($param);
    }

    function add_referensi()
    {
        $referensi   = strtoupper($this->input->post("referensi"));
        $keterangan  = strtoupper($this->input->post("keterangan"));

        $this->auth->restrict($this->addPermission);

        $data = array(
            'referensi' => $referensi,
            'keterangan' => $keterangan,
        );

        //Add Data
        $id = $this->Reff_model->insert($data);

        if (is_numeric($id)) {
            $keterangan     = "SUKSES, tambah data Referensi atas Nama : " . $referensi;
            $status         = 1;
            $nm_hak_akses   = $this->addPermission;
            $kode_universal = 'NewData';
            $jumlah         = 1;
            $sql            = $this->db->last_query();

            $result = TRUE;
        } else {
            $keterangan     = "GAGAL, tambah data Referensi atas Nama : " . $referensi;
            $status         = 0;
            $nm_hak_akses   = $this->addPermission;
            $kode_universal = 'NewData';
            $jumlah         = 1;
            $sql            = $this->db->last_query();
            $result = FALSE;
        }

        //Save Log
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        $param = array(
            'save' => $result
        );

        echo json_encode($param);
    }

    function get_bidus()
    {
        $rbidus = $this->Bidus_model->pilih_bidus()->result();
        //echo $result;
        echo "<select id='bidang_usaha' name='bidang_usaha' class='form-control pil_bidus select2-hidden-accessible'>";
        echo "<option value=''></option>";
        foreach ($rbidus as $key => $st) :
            echo "<option value='$st->bidang_usaha' set_select('bidang_usaha', $st->bidang_usaha, isset($data->bidang_usaha) && $data->bidang_usaha == $st->bidang_usaha)>$st->bidang_usaha
                    </option>";
        endforeach;
        echo "</select>";
    }

    function get_syardok()
    {
        $rsyardok = $this->Syarat_tagih_model->pilih_syarat()->result();
        //echo $result;
        echo "<select id='syarat_dokumen' name='syarat_dokumen' class='form-control pil_bidus select2-hidden-accessible' multiple='multiple'>";
        echo "<option value=''></option>";
        foreach ($rsyardok as $key => $st) :
            echo "<option value='$st->nm_syarat' set_select('syarat_dokumen', $st->nm_syarat, isset($data->syarat_dokumen) && $data->syarat_dokumen == $st->nm_syarat)>$st->nm_syarat
                    </option>";
        endforeach;
        echo "</select>";
    }

    function load_id_toko()
    {
        $rtoko = $this->Toko_model->pilih_toko($_GET['id'])->result();
        //echo $result;
        echo "<select id='id_toko_foto' name='id_toko_foto' class='form-control pil_toko select2-hidden-accessible'>";
        echo "<option value=''></option>";
        foreach ($rtoko as $key => $st) :
            echo "<option value='$st->id_toko' set_select('id_toko', $st->id_toko, isset($data->id_toko) && $data->id_toko == $st->id_toko)>$st->nm_toko
                    </option>";
        endforeach;
        echo "</select>";
    }

    function load_pic_toko()
    {
        $rpic = $this->Pic_model->pilih_pic($_GET['id'])->result();
        //echo $result;
        echo "<select id='pic' name='pic' class='form-control pil_pic select2-hidden-accessible'>";
        echo "<option value=''></option>";
        foreach ($rpic as $key => $st) :
            echo "<option value='$st->id_pic' set_select('pic', $st->id_pic, isset($data->pic) && $data->pic == $st->id_pic)>$st->nm_pic
                    </option>";
        endforeach;
        echo "</select>";
    }


    function get_reff()
    {
        $rreff = $this->Reff_model->pilih_reff()->result();
        //echo $result;
        echo "<select id='referensi' name='referensi' class='form-control pil_reff select2-hidden-accessible'>";
        echo "<option value=''></option>";
        foreach ($rreff as $key => $st) :
            echo "<option value='$st->referensi' set_select('referensi', $st->referensi, isset($data->referensi) && $data->referensi == $st->referensi)>$st->referensi
                    </option>";
        endforeach;
        echo "</select>";
    }

    function get_kota()
    {
        $provinsi = $_GET['provinsi'];
        $datkota = $this->Customer_model->pilih_kota($provinsi)->result();
        //echo $result;
        echo "<select id='kota' name='kota' class='form-control pil_kota select2-hidden-accessible'>";
        echo "<option value=''></option>";
        foreach ($datkota as $key => $st) :
            echo "<option value='$st->id_kab' set_select('model', $st->id_kab, isset($data->id_kab) && $data->id_kab == $st->id_kab)>$st->nama
                    </option>";
        endforeach;
        echo "</select>";
    }

    //Edit Mitra
    public function edit()
    {
        $this->auth->restrict($this->managePermission);
        $session = $this->session->userdata('app_session');
        $kdcab = $session['kdcab'];
        $id = $this->uri->segment(3);
        $data  = $this->Customer_model->find_by(array('id_customer' => $id));
        if (!$data) {
            $this->template->set_message("Invalid ID", 'error');
            redirect('Customer');
        }

        $datbidus   = $this->Bidus_model->pilih_bidus()->result();
        $datprov    = $this->Customer_model->pilih_provinsi()->result();
        $prov       = $this->Customer_model->get_prov($id);
        $datkota    = $this->Customer_model->pilih_kota($prov)->result();
        $datreff    = $this->Reff_model->pilih_reff()->result();
        $datmark    = $this->Customer_model->pilih_marketing($kdcab)->result();
        $datpic     = $this->Pic_model->pilih_pic($id)->result();
        $datdok     = $this->Syarat_tagih_model->pilih_syarat()->result();

        $this->template->set('datbidus', $datbidus);
        $this->template->set('datprov', $datprov);
        $this->template->set('datkota', $datkota);
        $this->template->set('datreff', $datreff);
        $this->template->set('datmark', $datmark);
        $this->template->set('datpic', $datpic);
        $this->template->set('datdok', $datdok);
        $this->template->set('data', $data);
        $this->template->title('Edit Data Customer');
        $this->template->render('customer_form');
    }

    //Save customer ajax
    public function save_customer_ajax()
    {
        $session = $this->session->userdata('app_session');
        $kdcab          = $session['kdcab'];
        $type           = $this->input->post("type");
        $id_customer    = $this->input->post("id_customer");
        $nm_customer    = strtoupper($this->input->post("nm_customer"));
        $bidang_usaha   = $this->input->post("bidang_usaha");
        $produk_jual    = strtoupper($this->input->post("produk_jual"));
        $kredibilitas   = $this->input->post("kredibilitas");
        $alamat         = strtoupper($this->input->post("alamat"));
        $provinsi       = $this->input->post("provinsi");
        $kota           = $this->input->post('kota');
        $kode_pos       = $this->input->post('kode_pos');
        $telpon         = $this->input->post('telpon');
        $fax            = $this->input->post('fax');
        $npwp           = $this->input->post('npwp');
        $alamat_npwp    = strtoupper($this->input->post('alamat_npwp'));
        $ktp            = $this->input->post('ktp');
        $alamat_ktp     = strtoupper($this->input->post('alamat_ktp'));
        $id_marketing   = $this->input->post('id_marketing');
        $referensi      = $this->input->post('referensi');
        $website        = strtoupper($this->input->post('website'));
        $diskontoko        = $this->input->post('diskon_toko');
        $sts_aktif      = $this->input->post('sts_aktif');
        $limit_piutang    = str_replace(',', '', $this->input->post('limit_piutang'));

        if ($type == "edit") {
            $this->auth->restrict($this->managePermission);

            if ($id_customer != "") {
                $data = array(
                    array(
                        'id_customer' => $id_customer,
                        'nm_customer' => $nm_customer,
                        'bidang_usaha' => $bidang_usaha,
                        'produk_jual' => $produk_jual,
                        'kredibilitas' => $kredibilitas,
                        'alamat' => $alamat,
                        'provinsi' => $provinsi,
                        'kota' => $kota,
                        'kode_pos' => $kode_pos,
                        'telpon' => $telpon,
                        'fax' => $fax,
                        'npwp' => $npwp,
                        'alamat_npwp' => $alamat_npwp,
                        'ktp' => $ktp,
                        'alamat_ktp' => $alamat_ktp,
                        'id_marketing' => $id_marketing,
                        'referensi' => $referensi,
                        'website' => $website,
                        'diskon_toko' => $diskontoko,
                        //'foto'=>$foto,
                        //'notes'=>$notes,
                        'sts_aktif' => $sts_aktif,
                        'limit_piutang'    => $limit_piutang
                    )
                );

                //Update data
                $result = $this->Customer_model->update_batch($data, 'id_customer');

                $keterangan     = "SUKSES, Edit data Customer " . $id_customer . ", atas Nama : " . $nm_customer;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $id_customer;
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $customer       = $id_customer;
            } else {
                $result = FALSE;

                $keterangan     = "GAGAL, Edit data Customer " . $id_customer . ", atas Nama : " . $nm_customer;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $id_customer;
                $jumlah         = 1;
                $sql            = $this->db->last_query();
            }

            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else //Add New
        {
            $this->auth->restrict($this->addPermission);
            //$this->auth->restrict($this->managePermission);
            $data =
                array(
                    'id_customer' => $this->Customer_model->generate_id('C'),
                    'nm_customer' => $nm_customer,
                    'bidang_usaha' => $bidang_usaha,
                    'produk_jual' => $produk_jual,
                    'kredibilitas' => $kredibilitas,
                    'alamat' => $alamat,
                    'provinsi' => $provinsi,
                    'kota' => $kota,
                    'kode_pos' => $kode_pos,
                    'telpon' => $telpon,
                    'fax' => $fax,
                    'npwp' => $npwp,
                    'alamat_npwp' => $alamat_npwp,
                    'id_marketing' => $id_marketing,
                    'referensi' => $referensi,
                    'website' => $website,
                    'diskon_toko' => $diskontoko,
                    'kdcab' => $kdcab,
                    //'foto'=>$foto,
                    //'notes'=>$notes,
                    'sts_aktif' => $sts_aktif,
                    'limit_piutang'    => $limit_piutang
                );

            //Add Data
            $id = $this->Customer_model->insert($data);

            if (is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data Customer " . $id_customer . ", atas Nama : " . $nm_customer;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result         = TRUE;
                $customer       = $id_customer;
            } else {
                $keterangan     = "GAGAL, tambah data Customer " . $id_customer . ", atas Nama : " . $nm_customer;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
            //Save Log
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }

        $param = array(
            'customer' => $customer,
            'save' => $result
        );

        echo json_encode($param);
    }

    function save_toko_ajax()
    {

        $type           = $this->input->post("type1");
        $id_toko        = $this->input->post("id_toko");
        $id_customer    = $this->input->post("customer");
        $nm_toko        = strtoupper($this->input->post("nm_toko"));
        $status_milik   = $this->input->post("status_milik");
        $luas           = $this->input->post("luas");
        $thn_berdiri    = $this->input->post("thn_berdiri");
        $area           = strtoupper($this->input->post("area"));
        $alamat_toko    = strtoupper($this->input->post("alamat_toko"));
        $telpon_toko    = $this->input->post("telpon_toko");
        $fax_toko       = $this->input->post("fax_toko");
        //pic
        $pic            = $this->input->post("pic");
        $hp_pic         = $this->input->post("hp_pic");
        $email          = $this->input->post("email");
        //penagihan
        $hari_tagih     = $this->input->post("hari_tagih");
        $haritag        = array();
        if (!empty($hari_tagih)) {
            foreach ($hari_tagih as $hari2) {
                array_push($haritag, $hari2);
            }
            $hari2          = serialize($haritag);
        }

        $syarat_dokumen = $this->input->post("syarat_dokumen");
        $doksyar        = array();
        if (!empty($syarat_dokumen)) {
            foreach ($syarat_dokumen as $dok2) {
                array_push($doksyar, $dok2);
            }
            $dok2           = serialize($doksyar);
        }

        $metode_bayar   = $this->input->post("metode_bayar");
        $metbay        = array();
        if (!empty($metode_bayar)) {
            foreach ($metode_bayar as $mebay) {
                array_push($metbay, $mebay);
            }
            $mebay           = serialize($metbay);
        }

        $jam_tagih      = $this->input->post("jam_tagih");
        $alamat_tagih   = $this->input->post("alamat_tagih");
        //pembayaran
        $sistem_bayar   = $this->input->post("sistem_bayar");
        $kredit_limit   = $this->input->post("kredit_limit");
        $termin_bayar   = $this->input->post("termin_bayar");
        //FOTO
        $foto_toko      =   $this->input->post("foto_toko");
        $filelama       =   $this->input->post('filelama'); /* variabel file gambar lama */
        $path           =   './photocustomer/'; //path folder

        $gambar         = $id_toko;
        $config = array(
            'upload_path' => './photocustomer/',
            'allowed_types' => 'gif|jpg|png|jpeg',
            'file_name' => $gambar,
            'file_ext_tolower' => TRUE,
            'overwrite' => TRUE,
            'max_size' => 100,
            'max_width' => 1024,
            'max_height' => 768,
            'min_width' => 10,
            'min_height' => 7,
            'max_filename' => 0,
            'remove_spaces' => TRUE
        );

        //$this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('foto_toko')) {
            $result = $this->upload->display_errors();
        } else {

            if ($filelama != '') {
                @unlink($path . $filelama);
                $data_foto = array('upload_data' => $this->upload->data());
                $gambar = $data_foto['upload_data']['file_name'];
            } else {
                $data_foto  = array('upload_data' => $this->upload->data());
                $gambar     = $data_foto['upload_data']['file_name'];
            }
        }

        if ($type == "edit") {
            $this->auth->restrict($this->managePermission);

            if ($id_toko != "") {
                $data = array(
                    array(
                        'id_toko' => $id_toko,
                        'id_customer' => $id_customer,
                        'nm_toko' => $nm_toko,
                        'status_milik' => $status_milik,
                        'luas' => $luas,
                        'thn_berdiri' => $thn_berdiri,
                        'area' => $area,
                        'alamat_toko' => $alamat_toko,
                        'telpon_toko' => $telpon_toko,
                        'fax_toko' => $fax_toko,
                        'email' => $email,
                        'pic' => $pic,
                        'hp_pic' => $hp_pic,
                        'hari_tagih' => $hari2,
                        'jam_tagih' => $jam_tagih,
                        'syarat_dokumen' => $dok2,
                        'alamat_tagih' => $alamat_tagih,
                        'metode_bayar' => $mebay,
                        'sistem_bayar' => $sistem_bayar,
                        'kredit_limit' => $kredit_limit,
                        'termin_bayar' => $termin_bayar,
                    )
                );

                //Update data
                $result = $this->Toko_model->update_batch($data, 'id_toko');

                $keterangan     = "SUKSES, Edit data Toko " . id_toko . ", atas Nama : " . nm_toko;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $id_customer;
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $customer       = $id_customer;
            } else {
                $result = FALSE;

                $keterangan     = "GAGAL, Edit data Toko " . id_toko . ", atas Nama : " . nm_toko;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $id_customer;
                $jumlah         = 1;
                $sql            = $this->db->last_query();
            }

            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else //Add New
        {
            $this->auth->restrict($this->addPermission);

            $data = array(
                'id_toko' => $id_toko,
                'id_customer' => $id_customer,
                'nm_toko' => $nm_toko,
                'status_milik' => $status_milik,
                'luas' => $luas,
                'thn_berdiri' => $thn_berdiri,
                'area' => $area,
                'alamat_toko' => $alamat_toko,
                'telpon_toko' => $telpon_toko,
                'fax_toko' => $fax_toko,
                'email' => $email,
                'pic' => $pic,
                'hp_pic' => $hp_pic,
                'hari_tagih' => $hari2,
                'jam_tagih' => $jam_tagih,
                'syarat_dokumen' => $dok2,
                'alamat_tagih' => $alamat_tagih,
                'metode_bayar' => $mebay,
                'sistem_bayar' => $sistem_bayar,
                'kredit_limit' => $kredit_limit,
                'termin_bayar' => $termin_bayar,
            );

            //Add Data
            $id = $this->Toko_model->insert($data);

            if (is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data Toko " . id_toko . ", atas Nama : " . nm_toko;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result         = TRUE;
                $customer       = $id_customer;
            } else {
                $keterangan     = "GAGAL, tambah data Toko " . id_toko . ", atas Nama : " . nm_toko;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
            //Save Log
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }

        $param = array(
            'customer' => $customer,
            'save' => $result
        );

        echo json_encode($param);
    }

    function save_setup_tagihan()
    {
        $type1              = $this->input->post("type1");
        $id_setup_penagihan = $this->input->post("id_setup_penagihan");
        $id_customer        = $this->input->post("customer");
        $jam_penagihan      = $this->input->post("jam_penagihan");
        $alamat_penagihan   = $this->input->post("alamat_penagihan");
        $senin              = "NO";
        $selasa             = "NO";
        $rabu               = "NO";
        $kamis              = "NO";
        $jumat              = "NO";
        $sj                 = "NO";
        $tb                 = "NO";
        $fp                 = "NO";
        $ba                 = "NO";

        $hari_tagih         = $this->input->post("hari_tagih");
        $haritag = array();
        foreach ($hari_tagih as $hari2) {
            array_push($haritag, $hari2);
        }
        $hari2 = serialize($haritag);

        $dokumen_syarat         = $this->input->post("dokumen_syarat");
        $doksyar = array();
        foreach ($dokumen_syarat as $dok2) {
            array_push($doksyar, $dok2);
        }
        $dok2 = serialize($doksyar);

        if ($type1 == "edit") {
            $this->auth->restrict($this->managePermission);

            if ($id_setup_penagihan != "") {
                $data = array(
                    array(
                        'id_setup_penagihan' => $id_setup_penagihan,
                        'id_customer' => $id_customer,
                        'senin' => $senin,
                        'selasa' => $selasa,
                        'rabu' => $rabu,
                        'kamis' => $kamis,
                        'jumat' => $jumat,
                        'jam_penagihan' => $jam_penagihan,
                        'alamat_penagihan' => $alamat_penagihan,
                        'surat_jalan' => $surat_jalan,
                        'faktur_pajak' => $faktur_pajak,
                        'berita_acara' => $berita_acara,
                        'bukti_pembayaran' => $bukti_pembayaran,
                        'hari_tagih' => $hari2,
                        'dokumen_syarat' => $dok2,
                    )
                );

                //Update data
                $result = $this->Penagihan_model->update_batch($data, 'id_setup_penagihan');

                $keterangan     = "SUKSES, Edit data SetPenagihan " . $id_setup_penagihan;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $id_customer;
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $customer       = $id_customer;
            } else {
                $result = FALSE;

                $keterangan     = "GAGAL, Edit data SetPenagihan " . $id_setup_penagihan;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $id_customer;
                $jumlah         = 1;
                $sql            = $this->db->last_query();
            }

            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else //Add New
        {
            $this->auth->restrict($this->addPermission);

            $data = array(
                'id_setup_penagihan' => $id_setup_penagihan,
                'id_customer' => $id_customer,
                'senin' => $senin,
                'selasa' => $selasa,
                'rabu' => $rabu,
                'kamis' => $kamis,
                'jumat' => $jumat,
                'jam_penagihan' => $jam_penagihan,
                'alamat_penagihan' => $alamat_penagihan,
                'surat_jalan' => $sj,
                'faktur_pajak' => $fp,
                'berita_acara' => $ba,
                'bukti_pembayaran' => $bp,
                'hari_tagih' => $hari2,
                'dokumen_syarat' => $dok2,
            );

            //Add Data
            $id = $this->Penagihan_model->insert($data);

            if (is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data SetPenagihan " . $id_setup_penagihan;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result         = TRUE;
                $customer       = $id_customer;
            } else {
                $keterangan     = "GAGAL, tambah data SetPenagihan " . $id_setup_penagihan;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
            //Save Log
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }

        $param = array(
            'customer' => $customer,
            'save' => $result
        );

        echo json_encode($param);
    }

    function save_setup_pembay()
    {
        $type1                  = $this->input->post("type1");
        $id_setup_pembayaran    = $this->input->post("id_setup_pembayaran");
        $id_customer            = $this->input->post("customer");
        $metode_bayar           = $this->input->post("metode_bayar");
        $sistem_bayar           = $this->input->post("sistem_bayar");
        $keterangan             = $this->input->post("keterangan");

        if ($type1 == "edit") {
            $this->auth->restrict($this->managePermission);

            if ($id_setup_pembayaran != "") {
                $data = array(
                    array(
                        'id_setup_pembayaran' => $id_setup_pembayaran,
                        'id_customer' => $id_customer,
                        'metode_bayar' => $metode_bayar,
                        'sistem_bayar' => $sistem_bayar,
                        'keterangan' => $keterangan,
                    )
                );

                //Update data
                $result = $this->Pembayaran_model->update_batch($data, 'id_setup_pembayaran');

                $keterangan     = "SUKSES, Edit data SetPembayarann " . $id_setup_pembayaran;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $id_customer;
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $customer       = $id_customer;
            } else {
                $result = FALSE;

                $keterangan     = "GAGAL, Edit data SetPembayarann " . $id_setup_pembayaran;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $id_customer;
                $jumlah         = 1;
                $sql            = $this->db->last_query();
            }

            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else //Add New
        {
            $this->auth->restrict($this->addPermission);

            $data = array(
                'id_setup_pembayaran' => $id_setup_pembayaran,
                'id_customer' => $id_customer,
                'metode_bayar' => $metode_bayar,
                'sistem_bayar' => $sistem_bayar,
                'keterangan' => $keterangan,
            );

            //Add Data
            $id = $this->Pembayaran_model->insert($data);

            if (is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data SetPembayarann " . $id_setup_pembayaran;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result         = TRUE;
                $customer       = $id_customer;
            } else {
                $keterangan     = "GAGAL, tambah data SetPembayarann " . $id_setup_pembayaran;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
            //Save Log
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }

        $param = array(
            'customer' => $customer,
            'save' => $result
        );

        echo json_encode($param);
    }

    function save_pic()
    {
        $type1          = $this->input->post("type1");
        $id_pic         = $this->input->post("id_pic");
        $id_customer    = $this->input->post("customerx");
        $nm_pic         = strtoupper($this->input->post("nm_pic"));
        $divisi         = strtoupper($this->input->post("divisi"));
        $email_pic      = strtoupper($this->input->post("email_pic"));
        $hp             = $this->input->post("hp");

        if ($type1 == "edit") {
            $this->auth->restrict($this->managePermission);

            if ($id_pic != "") {
                $data = array(
                    array(
                        'id_pic' => $id_pic,
                        'id_customer' => $id_customer,
                        'nm_pic' => $nm_pic,
                        'divisi' => $divisi,
                        'email_pic' => $email_pic,
                        'hp' => $hp,
                    )
                );

                //Update data
                $result = $this->Pic_model->update_batch($data, 'id_pic');

                $keterangan     = "SUKSES, Edit data PIC " . $id_pic;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $id_customer;
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $customer       = $id_customer;
            } else {
                $result = FALSE;

                $keterangan     = "GAGAL, Edit data PIC " . $id_pic;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = $id_customer;
                $jumlah         = 1;
                $sql            = $this->db->last_query();
            }

            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else //Add New
        {
            $this->auth->restrict($this->addPermission);

            $data = array(
                'id_pic' => $id_pic,
                'id_customer' => $id_customer,
                'nm_pic' => $nm_pic,
                'divisi' => $divisi,
                'email_pic' => $email_pic,
                'hp' => $hp,
            );

            //Add Data
            $id = $this->Pic_model->insert($data);

            if (is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data PIC " . $id_pic;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();

                $result         = TRUE;
                $customer       = $id_customer;
            } else {
                $keterangan     = "GAGAL, tambah data PIC " . $id_pic;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
            //Save Log
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }

        $param = array(
            'customer' => $customer,
            'save' => $result
        );

        echo json_encode($param);
    }

    function add_datafoto()
    {

        $id_customer    =   $this->input->post("id_customer");
        $foto           =   $this->input->post("foto");
        $filelama       =   $this->input->post('filelama'); /* variabel file gambar lama */
        $path           =   './photocustomer/'; //path folder

        $gambar         = $id_customer;
        $config = array(
            'upload_path' => './photocustomer/',
            'allowed_types' => 'gif|jpg|png|jpeg',
            'file_name' => $gambar,
            'file_ext_tolower' => TRUE,
            'overwrite' => TRUE,
            'max_size' => 100,
            'max_width' => 1024,
            'max_height' => 768,
            'min_width' => 10,
            'min_height' => 7,
            'max_filename' => 0,
            'remove_spaces' => TRUE
        );

        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('foto')) {
            $result = $this->upload->display_errors();
        } else {

            if ($filelama != '') {
                @unlink($path . $filelama);
                $data_foto = array('upload_data' => $this->upload->data());
                $gambar = $data_foto['upload_data']['file_name'];

                $data = array(
                    array(
                        'id_customer' => $id_customer,
                        'foto' => $gambar,
                    )
                );
            } else {
                $data_foto  = array('upload_data' => $this->upload->data());
                $gambar     = $data_foto['upload_data']['file_name'];

                $data = array(
                    array(
                        'id_customer' => $id_customer,
                        'foto' => $gambar,
                    )
                );
            }
            //Update data
            $result = $this->Customer_model->update_batch($data, 'id_customer');
        }

        $param = array(
            'save' => $result,
            'gambar' => $gambar,
            'token' => $this->security->get_csrf_hash()
        );

        echo json_encode($param);
    }

    function add_datafoto_toko()
    {

        $id_toko    =   $this->input->post("id_toko_foto");
        $foto       =   $this->input->post("foto_toko");
        $filelama   =   $this->input->post('filelama_toko'); /* variabel file gambar lama */
        $path       =   './photocustomer/'; //path folder

        $gambar         = $id_toko;
        $config = array(
            'upload_path' => './photocustomer/',
            'allowed_types' => 'gif|jpg|png|jpeg|JPG|PNG',
            'file_name' => $gambar,
            'file_ext_tolower' => TRUE,
            'overwrite' => TRUE,
            'max_size' => 2048,
            //'max_width' => 1024,
            //'max_height' => 768,
            //'min_width' => 10,
            //'min_height' => 7,
            //'max_filename' => 0,
            'remove_spaces' => TRUE
        );

        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('foto_toko')) {
            $result = $this->upload->display_errors();
        } else {

            if ($filelama != '') {
                @unlink($path . $filelama);
                $data_foto = array('upload_data' => $this->upload->data());
                $gambar = $data_foto['upload_data']['file_name'];

                $data = array(
                    array(
                        'id_toko' => $id_toko,
                        'foto_toko' => $gambar,
                    )
                );
            } else {
                $data_foto  = array('upload_data' => $this->upload->data());
                $gambar     = $data_foto['upload_data']['file_name'];

                $data = array(
                    array(
                        'id_toko' => $id_toko,
                        'foto_toko' => $gambar,
                    )
                );
            }
            //Update data
            $result = $this->Toko_model->update_batch($data, 'id_toko');
        }

        $param = array(
            'save' => $result,
            'gambar' => $gambar,
            'token' => $this->security->get_csrf_hash()
        );

        echo json_encode($param);
    }

    function get_idcust_pic()
    {
        $customer    = $this->input->post("customer");
        $id_pic = $this->Pic_model->generate_id($customer);
        $param = array(
            'id_pic' => $id_pic
        );

        echo json_encode($param);
    }

    function get_emailpic()
    {
        $pic    = $this->input->post("pic");
        if (!empty($pic)) {
            $detail  = $this->Pic_model->find($pic);
        }
        echo json_encode($detail);
    }

    function get_idtoko()
    {
        $customer    = $this->input->post("customer");
        $id_toko = $this->Toko_model->generate_id($customer);
        $param = array(
            'id_toko' => $id_toko
        );

        echo json_encode($param);
    }

    function get_idpic()
    {
        $customer    = $this->input->post("customer");
        $id_pic = $this->Pic_model->generate_id($customer);
        $param = array(
            'id_pic' => $id_pic
        );

        echo json_encode($param);
    }

    function load_toko()
    {
        $link_foto   = base_url();
        $id_customer = $_GET['id_customer'];
        echo "<div class='box-body'><B>Data Toko</B><table id='listpp' class='table table-bordered table-striped'>
        <thead>
        <tr>
            <th width='50'>#</th>
            <th>Nama Toko</th>
            <th>Kepemilikan</th>
            <th>Luas</th>
            <th>Tahun Berdiri</th>
            <th>Area</th>
            <th>Alamat</th>
            <th>Foto</th>
            <th width='25'>Hapus</th>
        </tr>
        </thead>";
        $no = 1;
        $data =  $this->Toko_model->tampil_toko($id_customer)->result();
        foreach ($data as $d) {
            if ($d->foto_toko == '') {
                $d->foto_toko = 'no_images.jpg';
            } else {
                $d->foto_toko = $d->foto_toko;
            }
            echo "<tr id='dataku$d->id_toko'>
                <td>$no</td>
                <td>$d->nm_toko</td>
                <td>$d->status_milik</td>
                <td>$d->luas</td>
                <td>$d->thn_berdiri</td>
                <td>$d->area</td>
                <td>$d->alamat_toko</td>
                <td><a target='_blank' href='" . $link_foto . "photocustomer/" . $d->foto_toko . "'>
                    <img src='" . $link_foto . "photocustomer/" . $d->foto_toko . "'>
                    </a>
                </td>
                <td>
                 <a class='text-black' href='javascript:void(0)' title='Hapus' onclick=\"hapus_toko('" . $d->id_toko . "');\"><i class='fa fa-trash'></i>
                </a>
                </td>
                </tr>";
            $no++;
        }
        echo "<tfoot>
        <tr>
            <th width='50'>#</th>
            <th>Nama Toko</th>
            <th>Kepemilikan</th>
            <th>Luas</th>
            <th>Tahun Berdiri</th>
            <th>Area</th>
            <th>Alamat</th>
            <th>Foto</th>
            <th width='25'>Hapus</th>
        </tr>
        </tfoot>";
        echo "</table></div>";
    }

    function load_pic()
    {
        $id_customer = $_GET['id_customer'];
        echo "<div class='box-body'><B>Data PIC</B><table id='listpp' class='table table-bordered table-striped'>
        <thead>
        <tr>
            <th width='50'>#</th>
            <th>ID</th>
            <th>Nama PIC</th>
            <th>Divisi</th>
            <th>No Kontak</th>
            <th>Email</th>
            <th width='25'>Hapus</th>
        </tr>
        </thead>";
        $no = 1;
        $data =  $this->Pic_model->tampil_pic($id_customer)->result();
        foreach ($data as $d) {
            echo "<tr id='dataku$d->id_pic'>
                <td>$no</td>
                <td>$d->id_pic</td>
                <td>$d->nm_pic</td>
                <td>$d->divisi</td>
                <td>$d->hp</td>
                <td>$d->email_pic</td>
                <td>
                 <a class='text-black' href='javascript:void(0)' title='Hapus' onclick=\"hapus_pic('" . $d->id_pic . "');\"><i class='fa fa-trash'></i>
                </a>
                </td>
                </tr>";
            $no++;
        }
        echo "<tfoot>
        <tr>
            <th width='50'>#</th>
            <th>ID</th>
            <th>Nama PIC</th>
            <th>Divisi</th>
            <th>No Kontak</th>
            <th width='25'>Hapus</th>
        </tr>
        </tfoot>";
        echo "</table></div>";
    }

    function load_foto()
    {
        $id_customer = $_GET['id_customer'];
        $data =  $this->Customer_model->tampil_foto($id_customer);
?>
        <div class='item'>
            <table id='listpp' class='table table-bordered table-striped'>
                <thead>
                    <tr>
                        <th width='50'>FOTO Customer</th>
                    </tr>
                </thead>
                <tr id='dataku$data->v_no'>
                    <td align="center">
                        <?php if ($data->foto == '') { ?>
                            <b>Tidak Ada Foto</b>
                        <?php } else { ?>
                            <img class="img-thumbnail" src="<?php echo base_url() . 'photocustomer/' . $data->foto; ?>" />
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>

<?php
    }

    //Delete
    function hapus_customer()
    {
        $this->auth->restrict($this->deletePermission);
        $id = $this->uri->segment(3);

        if ($id != '') {

            $result = $this->Customer_model->delete($id);

            $keterangan     = "SUKSES, Delete data Customer " . $id;
            $status         = 1;
            $nm_hak_akses   = $this->addPermission;
            $kode_universal = $id;
            $jumlah         = 1;
            $sql            = $this->db->last_query();
        } else {
            $result = 0;
            $keterangan     = "GAGAL, Delete data Customer " . $id;
            $status         = 0;
            $nm_hak_akses   = $this->addPermission;
            $kode_universal = $id;
            $jumlah         = 1;
            $sql            = $this->db->last_query();
        }

        //Save Log
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        $param = array(
            'delete' => $result
        );

        echo json_encode($param);
    }

    function hapus_toko()
    {
        $this->auth->restrict($this->deletePermission);
        $id = $this->uri->segment(3);

        if ($id != '') {

            $result = $this->Toko_model->delete($id);

            $keterangan     = "SUKSES, Delete data Toko " . $id;
            $status         = 1;
            $nm_hak_akses   = $this->addPermission;
            $kode_universal = $id;
            $jumlah         = 1;
            $sql            = $this->db->last_query();
        } else {
            $result = 0;
            $keterangan     = "GAGAL, Delete data Toko " . $id;
            $status         = 0;
            $nm_hak_akses   = $this->addPermission;
            $kode_universal = $id;
            $jumlah         = 1;
            $sql            = $this->db->last_query();
        }

        //Save Log
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        $param = array(
            'delete' => $result,
            'idx' => $id
        );

        echo json_encode($param);
    }

    function hapus_pic()
    {
        $this->auth->restrict($this->deletePermission);
        $id = $this->uri->segment(3);

        if ($id != '') {

            $result = $this->Pic_model->delete($id);

            $keterangan     = "SUKSES, Delete data PIC" . $id;
            $status         = 1;
            $nm_hak_akses   = $this->addPermission;
            $kode_universal = $id;
            $jumlah         = 1;
            $sql            = $this->db->last_query();
        } else {
            $result = 0;
            $keterangan     = "GAGAL, Delete data PIC" . $id;
            $status         = 0;
            $nm_hak_akses   = $this->addPermission;
            $kode_universal = $id;
            $jumlah         = 1;
            $sql            = $this->db->last_query();
        }

        //Save Log
        simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);

        $param = array(
            'delete' => $result,
            'idx' => $id
        );

        echo json_encode($param);
    }

    function ListBD()
    {
        echo "<div class='box-body'><B>List Bidang Usaha</B><table id='lis_BD' class='table table-bordered table-striped'>
        <thead>
        <tr>
            <th width='50'>#</th>
            <th>Bidang Usaha</th>
            <th>Keterangan</th>
            <th width='25'>Action</th>
        </tr>
        </thead>";
        $no = 1;
        $data =  $this->Bidus_model->tampil_bidus()->result();
        foreach ($data as $d) {
            echo "<tr id='dataku$d->id_bidang_usaha'>
                <td>$no</td>
                <td>$d->bidang_usaha</td>
                <td>$d->keterangan</td>
                <td>
                <a class='text-black' href='javascript:void(0)' title='Edit' onclick=\"edit_bd('" . $d->id_bidang_usaha . "');\"><i class='fa fa-pencil'></i>
                </td>
                </tr>";
            $no++;
        }
        echo "<tfoot>
        <tr>
        </tr>
        </tfoot>";
        echo "</table></div>";
    }

    function ListSD()
    {
        echo "<div class='box-body'><B>List Syarat Penagihan</B><table id='lis_sd' class='table table-bordered table-striped'>
        <thead>
        <tr>
            <th width='50'>#</th>
            <th>Nama Syarat</th>
            <th width='25'>Action</th>
        </tr>
        </thead>";
        $no = 1;
        $data =  $this->Syarat_tagih_model->pilih_syarat()->result();
        foreach ($data as $d) {
            echo "<tr id='dataku$d->id_bidang_usaha'>
                <td>$no</td>
                <td>$d->nm_syarat</td>
                <td>
                <a class='text-black' href='javascript:void(0)' title='Edit' onclick=\"edit_sd('" . $d->id_syarat . "');\"><i class='fa fa-pencil'></i>
                </td>
                </tr>";
            $no++;
        }
        echo "<tfoot>
        <tr>
        </tr>
        </tfoot>";
        echo "</table></div>";
    }

    function edit_bd()
    {
        $id_bd = $this->input->post('id');
        if (!empty($id_bd)) {
            $detail  = $this->Bidus_model->find($id_bd);
        }
        echo json_encode($detail);
    }

    function edit_sd()
    {
        $id_sd = $this->input->post('id');
        if (!empty($id_sd)) {
            $detail  = $this->Syarat_tagih_model->find($id_sd);
        }
        echo json_encode($detail);
    }
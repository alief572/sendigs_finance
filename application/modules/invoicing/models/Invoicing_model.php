<?php

class Invoicing_model extends BF_Model
{
    protected $viewPermission     = 'Invoicing.View';
    protected $addPermission      = 'Invoicing.Add';
    protected $managePermission = 'Invoicing.Manage';
    protected $deletePermission = 'Invoicing.Delete';

    protected $consultant;
    protected $accounting;

    public function __construct()
    {
        $this->consultant = $this->load->database('consultant', true);
        $this->accounting = $this->load->database('accounting', true);
    }

    public function generate_id()
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT MAX(id) as maxP FROM tr_invoicing WHERE id LIKE '%" . date('-y') . "%' ";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        $urutan2        = (int)substr($angkaUrut2, 0, 5);
        $urutan2++;
        $urut2            = sprintf('%05s', $urutan2);
        $kode_trans        = $urut2 . '-INV-' . int_to_roman(date('m')) . '-' . date('y');

        return $kode_trans;
    }

    public function generate_id_invoice_jurnal($nomor)
    {
        $Ym             = date('ym');
        $srcMtr            = "SELECT MAX(id) as maxP FROM tr_jurnal WHERE no_jurnal LIKE '%" . int_to_roman(date('m')) . "-" . date('-y') . "%' ";
        $resultMtr        = $this->db->query($srcMtr)->result_array();
        $angkaUrut2        = $resultMtr[0]['maxP'];
        $urutan2        = (int)substr($angkaUrut2, 0, 5);
        $urutan2 = $urutan2 + $nomor;
        $urut2            = sprintf('%05s', $urutan2);
        $kode_trans        = $urut2 . '-AJV-' . int_to_roman(date('m')) . '-' . date('y');

        return $kode_trans;
    }

    public function get_data_spk()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');
        $filter_status = $this->input->post('filter_status');

        $this->db->select('a.*, COALESCE(e.nm_company, g.nm_company) as company_name, c.nm_customer, d.nm_paket as nm_project, c.nm_project_leader, c.nm_sales, f.no_invoice');
        $this->db->from('kons_tr_plan_tagih_detail a');
        $this->db->join(DBCNL . '.kons_tr_penawaran b', 'b.id_quotation = a.id_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_tr_spk_penawaran c', 'c.id_spk_penawaran = a.id_spk_penawaran', 'left');
        $this->db->join(DBCNL . '.kons_master_konsultasi_header d', 'd.id_konsultasi_h = c.id_project', 'left');
        $this->db->join(DBCNL . '.kons_tr_company e', 'e.id = b.company', 'left');
        $this->db->join(DBCNL . '.kons_tr_company g', 'g.id = c.id_company', 'left');
        $this->db->join('tr_invoicing f', 'f.id_detail_plan_tagih = a.id', 'left');
        $this->db->where('a.status_terakhir', '1');
        $this->db->where("(IF(a.sts_invoice = '1', YEAR(f.tanggal_invoice), YEAR(a.tgl_aktual_plan_tagih)) >= 2026)");

        // Filter status berdasarkan sts_invoice di kons_tr_plan_tagih_detail
        if ($filter_status == 'uninvoiced') {
            $this->db->where('a.sts_invoice !=', '1');
        } elseif ($filter_status == 'invoiced') {
            $this->db->where('a.sts_invoice', '1');
        }
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.id_spk_penawaran', $search['value'], 'both');
            $this->db->or_like('c.nm_customer', $search['value'], 'both');
            $this->db->or_like('d.nm_paket', $search['value'], 'both');
            $this->db->or_like('c.nm_project_leader', $search['value'], 'both');
            $this->db->or_like('c.nm_sales', $search['value'], 'both');
            $this->db->or_like('e.nm_company', $search['value'], 'both');
            $this->db->or_like('g.nm_company', $search['value'], 'both');
            $this->db->or_like('f.no_invoice', $search['value'], 'both');
            $this->db->group_end();
        }

        $this->db->group_by('a.id');

        $db_clone = clone $this->db;
        $count_all = $db_clone->count_all_results();

        $this->db->group_by('a.id');
        $this->db->order_by('a.sts_invoice', 'asc');
        $this->db->order_by('a.id', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        $hasil = [];
        $no = (0 + $start);

        foreach ($get_data->result() as $item) {
            $no++;

            $status = '<span class="badge bg-yellow">Uninvoiced</span>';
            $status2 = '';
            if ($item->sts_invoice == '1') {
                $status = '<span class="badge bg-green">Invoiced</span>';
            }

            $option = '';
            if ($item->sts_invoice != '1') {
                $option = '<a href="' . base_url('invoicing/add_invoice/' . $item->id) . '" class="btn btn-sm btn-warning" title="Create Invoice"><i class="fa fa-pencil"></i></a>';

                $option .= ' <a href="' . base_url('invoicing/add_invoice_vuca/' . $item->id) . '" class="btn btn-sm btn-primary" title="Create Invoice Vuca"><i class="fa fa-pencil"></i></a>';
            } else {
                $get_invoicing = $this->db->get_where('tr_invoicing', ['id_detail_plan_tagih' => $item->id])->row();

                if (!empty($get_invoicing)) {

                    $tipe_invoice = (!empty($get_invoicing)) ? $get_invoicing->tipe_invoice : '0';

                    if ($tipe_invoice == '1') {
                        $option = '<a href="' . base_url('invoicing/view_invoicing_vuca/' . $get_invoicing->id) . '" class="btn btn-sm btn-primary" title="View Invoice Vuca"><i class="fa fa-eye"></i></a>';
                    } else {
                        $option = '<a href="' . base_url('invoicing/view_invoicing/' . $get_invoicing->id) . '" class="btn btn-sm btn-primary" title="View Invoice"><i class="fa fa-eye"></i></a>';
                    }


                    $get_jurnal = $this->db->get_where('tr_jurnal', ['no_transaksi' => $get_invoicing->id, 'sts' => '1'])->result();
                    $get_penerimaan = $this->db->get_where('tr_penerimaan_piutang_detail', ['id_inv' => $get_invoicing->id])->result();
                    if (empty($get_jurnal) && empty($get_penerimaan)) {
                        // if ($get_invoicing->tipe_invoice == '1') {
                        //     $option .= ' <a href="' . base_url('invoicing/edit_invoicing_vuca/' . $get_invoicing->id) . '" class="btn btn-sm btn-success" title="Revisi Invoice"><i class="fa fa-pencil"></i></a>';
                        // } else {
                        //     $option .= ' <a href="' . base_url('invoicing/edit_invoicing/' . $get_invoicing->id) . '" class="btn btn-sm btn-success" title="Revisi Invoice"><i class="fa fa-pencil"></i></a>';
                        // }
                    } else {
                        if (!empty($get_jurnal)) {
                            $status2 = '<span class="badge bg-red">Journaled</span>';
                        }
                    }

                    if ($get_invoicing->tipe_invoice == '1') {
                        $option .= ' <a href="javascript:void(0);" class="btn btn-sm btn-info pilih_print_inv_vuca" title="Print Invoice" data-toggle="modal" data-target="#modal_print_vuca" data-id_inv="' . $get_invoicing->id . '"><i class="fa fa-print"></i></a>';
                        $option .= ' <a href="javascript:void(0);" class="btn btn-sm btn-default pilih_print_kwitansi_vuca" title="Print Kwitansi" data-toggle="modal" data-target="#modal_print_kwitansi_vuca" data-id_inv="' . $get_invoicing->id . '"><i class="fa fa-file-pdf-o"></i></a>';
                    } else {
                        $option .= ' <a href="javascript:void(0);" class="btn btn-sm btn-info pilih_print_inv" title="Print Invoice" data-toggle="modal" data-target="#modal_print" data-id_inv="' . $get_invoicing->id . '"><i class="fa fa-print"></i></a>';
                        $option .= ' <a href="javascript:void(0);" class="btn btn-sm btn-default pilih_print_kwitansi" title="Print Kwitansi" data-toggle="modal" data-target="#modal_print_kwitansi" data-id_inv="' . $get_invoicing->id . '"><i class="fa fa-file-pdf-o"></i></a>';
                    }
                }




                // $option .= ' <a href="' . base_url('invoicing/print_invoicing/' . $get_invoicing->id) . '" class="btn btn-sm btn-info" title="Print Invoice" target="_blank"><i class="fa fa-print"></i></a>';
            }

            $hasil[] = [
                'no' => $no,
                'no_invoice' => $item->no_invoice,
                'company' => $item->company_name,
                'no_spk' => $item->id_spk_penawaran,
                'customer' => $item->nm_customer,
                'project' => $item->nm_project,
                'project_leader' => $item->nm_project_leader,
                'sales' => $item->nm_sales,
                'status' => $status . $status2,
                'option' => $option
            ];
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_all,
            'data' => $hasil
        ]);
    }

    public function get_penawaran_non_konsultasi($id_penawaran)
    {
        $this->consultant->select('a.*');
        $this->consultant->from('kons_tr_penawaran_non_konsultasi a');
        $this->consultant->where('a.id_penawaran', $id_penawaran);
        return $this->consultant->get()->row();
    }

    public function get_detail_penawaran_non_konsultasi($id_penawaran)
    {
        $this->consultant->select('a.id, a.id_header, a.nm_item, a.qty, a.harga, a.total');
        $this->consultant->from('kons_tr_detail_penawaran_non_konsultasi a');
        $this->consultant->where('a.id_header', $id_penawaran);
        return $this->consultant->get()->result();
    }

    public function jurnal_invoicing_non_konsultasi($id_penawaran, $tanggal_invoice = null)
    {
        $get_penawaran = $this->get_penawaran_non_konsultasi($id_penawaran);

        $this->consultant->select('COALESCE(SUM(a.total), 0) as subtotal');
        $this->consultant->from('kons_tr_detail_penawaran_non_konsultasi a');
        $this->consultant->where('a.id_header', $id_penawaran);
        $get_subtotal = $this->consultant->get()->row();

        $total_nominal = $get_subtotal->subtotal ?? 0;

        $id_company = '1';
        $nm_company = 'STM-Vuca';

        $arr_coa_jurnal = ['1102-01-01', '2104-01-07', '1106-01-02', '4101-01-01'];

        $hasil_jurnal = '';

        $this->accounting->select('a.no_perkiraan, a.nama as nm_coa');
        $this->accounting->from('coa_master a');
        $this->accounting->where_in('a.no_perkiraan', $arr_coa_jurnal);
        $get_coa_jurnal = $this->accounting->get()->result_array();

        $no_coa_jurnal = 0;

        $total_debit = 0;
        $total_kredit = 0;

        foreach ($get_coa_jurnal as $item_coa_jurnal) {
            $no_coa_jurnal++;

            $debit = 0;
            $kredit = 0;

            if ($item_coa_jurnal['no_perkiraan'] == '1102-01-01') {
                // $total_nominal = (!empty($get_penawaran)) ? $get_penawaran->subtotal : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 2 / 100);
                $debit = ($total_nominal + $ppn - $pph);
            }

            if ($item_coa_jurnal['no_perkiraan'] == '2104-01-07') {
                // $total_nominal = (!empty($get_penawaran)) ? $get_penawaran->subtotal : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);

                $kredit = $ppn;
            }

            if ($item_coa_jurnal['no_perkiraan'] == '1106-01-02') {
                // $total_nominal = (!empty($get_penawaran)) ? $get_penawaran->subtotal : 0;
                $pph = ($total_nominal * 2 / 100);
                $debit = $pph;
            }

            if ($item_coa_jurnal['no_perkiraan'] == '4101-01-01') {
                // $total_nominal = (!empty($get_penawaran)) ? $get_penawaran->subtotal : 0;
                $dpp_lain_lain = ($total_nominal * 11 / 12);
                $ppn = ($dpp_lain_lain * 12 / 100);
                $pph = ($total_nominal * 2 / 100);

                $kredit = $total_nominal;
            }

            $hasil_jurnal .= '<tr>';

            $hasil_jurnal .= '<td class="text-center">';
            $tgl_jurnal = !empty($tanggal_invoice) ? $tanggal_invoice : date('Y-m-d');
            $hasil_jurnal .= date('d-F-Y', strtotime($tgl_jurnal));
            $hasil_jurnal .= '<input type="hidden" name="tgl_jurnal_' . $no_coa_jurnal . '" value="' . date('Y-m-d', strtotime($tgl_jurnal)) . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= $item_coa_jurnal['no_perkiraan'];
            $hasil_jurnal .= '<input type="hidden" name="coa_jurnal_' . $no_coa_jurnal . '" value="' . $item_coa_jurnal['no_perkiraan'] . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= $nm_company;
            $hasil_jurnal .= '<input type="hidden" name="id_company_' . $no_coa_jurnal . '" value="' . $id_company . '">';
            $hasil_jurnal .= '<input type="hidden" name="nm_company_' . $no_coa_jurnal . '" value="' . $nm_company . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-center">';
            $hasil_jurnal .= $item_coa_jurnal['nm_coa'];
            $hasil_jurnal .= '<input type="hidden" name="nm_coa_' . $no_coa_jurnal . '" value="' . $item_coa_jurnal['nm_coa'] . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-right">';
            $hasil_jurnal .= number_format($debit);
            $hasil_jurnal .= '<input type="hidden" name="debit_' . $no_coa_jurnal . '" value="' . $debit . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '<td class="text-right">';
            $hasil_jurnal .= number_format($kredit);
            $hasil_jurnal .= '<input type="hidden" name="kredit_' . $no_coa_jurnal . '" value="' . $kredit . '">';
            $hasil_jurnal .= '</td>';

            $hasil_jurnal .= '</tr>';

            $total_debit += $debit;
            $total_kredit += $kredit;
        }

        $response = [
            'total_debit' => $total_debit,
            'total_kredit' => $total_kredit,
            'hasil_jurnal' => $hasil_jurnal
        ];

        return $response;
    }

    public function get_invoice($id)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id', $id);
        $get_data = $this->db->get()->row();

        return $get_data;
    }

    public function get_invoice_non_kons($id_penawaran)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id_penawaran', $id_penawaran);
        $this->db->where('a.non_kons', '1');
        $get_data = $this->db->get()->result();

        return $get_data;
    }

    public function get_invoice_non_kons_detail($id_header)
    {
        $this->db->select('a.*');
        $this->db->from('tr_invoice_detail_non_kons a');
        $this->db->where('a.id_header', $id_header);
        $this->db->order_by('a.input_at', 'asc');
        $get_data = $this->db->get()->result();

        return $get_data;
    }

    public function get_list_penawaran_non_kons()
    {
        $this->consultant->select('a.*, b.nm_company');
        $this->consultant->from('kons_tr_penawaran_non_konsultasi a');
        $this->consultant->join('kons_tr_company b', 'b.id = a.id_company', 'left');
        $this->consultant->where('a.sts_quot', '1');
        $this->consultant->where('a.sts_deal', '1');
        $this->consultant->where('a.sts_close', '0');
        $get_data = $this->consultant->get()->result();

        return $get_data;
    }

    public function get_penawaran_non_kons($id_penawaran)
    {
        $this->consultant->select('a.*');
        $this->consultant->from('kons_tr_penawaran_non_konsultasi a');
        $this->consultant->where('a.id_penawaran', $id_penawaran);
        $get_data = $this->consultant->get()->row();

        return $get_data;
    }

    public function get_view_jurnal_invoice_non_kons($id)
    {
        $this->db->select('a.*');
        $this->db->from('tr_jurnal a');
        $this->db->where('a.no_transaksi', $id);
        $this->db->where('a.jenis_transaksi', 'Invoicing');
        $get_jurnal = $this->db->get()->result();

        $hasil_jurnal = '';
        $total_debit = 0;
        $total_kredit = 0;

        foreach ($get_jurnal as $item) {
            $hasil_jurnal .= '
                <tr>
                    <td class="text-center">' . date('Y-m-d', strtotime($item->tgl_jurnal)) . '</td>
                    <td class="text-center">' . $item->coa . '</td>
                    <td class="text-center">' . $item->nm_company . '</td>
                    <td class="text-center">' . $item->nm_coa . '</td>
                    <td class="text-right">' . number_format($item->debit) . '</td>
                    <td class="text-right">' . number_format($item->kredit) . '</td>
                </tr>
            ';

            $total_debit += $item->debit;
            $total_kredit += $item->kredit;
        }

        $response = [
            'hasil_jurnal' => $hasil_jurnal,
            'total_debit' => $total_debit,
            'total_kredit' => $total_kredit
        ];

        return $response;
    }

    public function total_invoiced_non_kons($id_penawaran)
    {
        $this->db->select('COALESCE(SUM(a.total_nominal), 0) as total');
        $this->db->from('tr_invoicing a');
        $this->db->where('a.id_penawaran', $id_penawaran);
        $get_data = $this->db->get()->row();

        return $get_data->total;
    }

    /**
     * Preview nomor invoice berikutnya (read-only, tidak update sequence)
     *
     * @param string $id_company
     * @param string $tipe_invoice '0' untuk Sentral, '1' untuk VUCA
     * @return string format: XXX/{ENTITY}/{YEAR}
     */
    public function preview_no_invoice($id_company, $tipe_invoice = '0')
    {
        $tahun = date('Y');

        $kode_entitas = 'SSC';
        if (in_array($id_company, [1, 6, 7])) {
            $kode_entitas = 'STM';
        } elseif ($id_company == 4 || $tipe_invoice == '1') {
            $kode_entitas = 'VSB';
        }

        // Cek tabel sequence
        $table_exists = $this->db->query("SHOW TABLES LIKE 'ms_invoice_sequence'")->num_rows() > 0;

        if ($table_exists) {
            $seq_row = $this->db->get_where('ms_invoice_sequence', [
                'kode_entitas' => $kode_entitas,
                'tahun' => $tahun
            ])->row();

            if ($seq_row) {
                $sequence = (int)$seq_row->last_sequence + 1;
            } else {
                $sequence = $this->_get_max_sequence_from_invoicing($kode_entitas, $tahun);
            }
        } else {
            $sequence = $this->_get_max_sequence_from_invoicing($kode_entitas, $tahun);
        }

        return sprintf('%03d/%s/%d', $sequence, $kode_entitas, $tahun);
    }

    public function generate_no_invoice($id_company, $tipe_invoice = '0')
    {
        $tahun = date('Y');

        $kode_entitas = 'SSC';
        if (in_array($id_company, [1, 6, 7])) {
            $kode_entitas = 'STM';
        } elseif ($id_company == 4 || $tipe_invoice == '1') {
            $kode_entitas = 'VSB';
        }

        // Cek apakah tabel sequence exist
        $table_exists = $this->db->query("SHOW TABLES LIKE 'ms_invoice_sequence'")->num_rows() > 0;

        if ($table_exists) {
            // Cek tabel sequence
            $seq_row = $this->db->get_where('ms_invoice_sequence', [
                'kode_entitas' => $kode_entitas,
                'tahun' => $tahun
            ])->row();

            if ($seq_row) {
                // Ada di tabel sequence → increment
                $sequence = (int)$seq_row->last_sequence + 1;
                $this->db->where('id', $seq_row->id);
                $this->db->update('ms_invoice_sequence', [
                    'last_sequence' => $sequence,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->auth->user_id()
                ]);
            } else {
                // Belum ada row untuk entitas+tahun ini → fallback ke MAX lalu insert
                $sequence = $this->_get_max_sequence_from_invoicing($kode_entitas, $tahun);

                $this->db->insert('ms_invoice_sequence', [
                    'kode_entitas' => $kode_entitas,
                    'tahun' => $tahun,
                    'last_sequence' => $sequence,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->auth->user_id()
                ]);
            }
        } else {
            // Tabel belum ada → fallback ke logic lama (MAX dari tr_invoicing)
            $sequence = $this->_get_max_sequence_from_invoicing($kode_entitas, $tahun);
        }

        return sprintf('%03d/%s/%d', $sequence, $kode_entitas, $tahun);
    }

    /**
     * Helper: ambil MAX sequence dari tr_invoicing (fallback logic)
     *
     * @param string $kode_entitas  STM/VSB/SSC
     * @param string $tahun         Year (e.g. 2026)
     * @return int   Next sequence number
     */
    private function _get_max_sequence_from_invoicing($kode_entitas, $tahun)
    {
        $query = "SELECT MAX(CAST(SUBSTRING_INDEX(no_invoice, '/', 1) AS UNSIGNED)) as max_seq 
                  FROM tr_invoicing 
                  WHERE no_invoice LIKE '%/" . $this->db->escape_like_str($kode_entitas) . "/" . $this->db->escape_like_str($tahun) . "'";
        $result = $this->db->query($query)->row();

        $sequence = 1;
        if (!empty($result) && !empty($result->max_seq)) {
            $sequence = (int)$result->max_seq + 1;
        }

        return $sequence;
    }

    /**
     * Cek apakah no_invoice sudah pernah digunakan (per company/tipe_invoice)
     *
     * @param string $no_invoice
     * @param string $tipe_invoice  '1' untuk VUCA, '0' atau null untuk Sentral
     * @return bool  true jika sudah ada (duplikat)
     */
    public function is_no_invoice_exists($no_invoice, $tipe_invoice = '0')
    {
        $this->db->from('tr_invoicing');
        $this->db->where('LOWER(no_invoice)', strtolower(trim($no_invoice)));

        if ($tipe_invoice == '1') {
            $this->db->where('tipe_invoice', '1');
        } else {
            $this->db->group_start();
            $this->db->where('tipe_invoice IS NULL');
            $this->db->or_where('tipe_invoice', '0');
            $this->db->or_where('tipe_invoice', '');
            $this->db->group_end();
        }

        return $this->db->count_all_results() > 0;
    }
}

<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Expense_report_project_model extends BF_Model
{

    protected $ENABLE_ADD;
    protected $ENABLE_MANAGE;
    protected $ENABLE_VIEW;
    protected $ENABLE_DELETE;

    protected $gl;
    protected $sendigs;

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Expense_Report_Project.Add');
        $this->ENABLE_MANAGE  = has_permission('Expense_Report_Project.Manage');
        $this->ENABLE_VIEW    = has_permission('Expense_Report_Project.View');
        $this->ENABLE_DELETE  = has_permission('Expense_Report_Project.Delete');

        $this->gl = $this->load->database('gl_sendigs', true);
        $this->sendigs = $this->load->database('sendigs_finance', true);
    }

    function generate_id_expense_report_header($tipe_data)
    {
        $no_doc = '';
        $newcode = '';
        $query_data = 'SELECT * FROM ms_generate WHERE tipe = "' . $tipe_data . '";';
        $data = $this->sendigs->query($query_data)->row();
        if ($data !== false) {
            if (stripos($data->info, 'YEAR', 0) !== false) {
                if ($data->info3 != date("Y")) {
                    $years = date("Y");
                    $number = 1;
                    $newnumber = sprintf('%0' . $data->info4 . 'd', $number);
                } else {
                    $years = $data->info3;
                    $number = ($data->info2 + 1);
                    $newnumber = sprintf('%0' . $data->info4 . 'd', $number);
                }
                $newcode = str_ireplace('XXXX', $newnumber, $data->info);
                $newcode = str_ireplace('YEAR', $years, $newcode);
                $newdata = array('info2' => $number, 'info3' => $years);
            } else {
                $number = ($data->info2 + 1);
                $newnumber = sprintf('%0' . $data->info4 . 'd', $number);
                $newcode = str_ireplace('XXXX', $newnumber, $data->info);
                $newdata = array('info2' => $number);
            }

            $this->sendigs->update('ms_generate', $newdata, array('tipe' => $tipe_data));

            $no_doc = $newcode;
        }

        return $no_doc;
    }

    function preview_id_expense_report_header($tipe_data)
    {
        $no_doc = '';
        $newcode = '';
        $query_data = 'SELECT * FROM ms_generate WHERE tipe = "' . $tipe_data . '";';
        $data = $this->sendigs->query($query_data)->row();
        if ($data !== false) {
            if (stripos($data->info, 'YEAR', 0) !== false) {
                if ($data->info3 != date("Y")) {
                    $years = date("Y");
                    $number = 1;
                    $newnumber = sprintf('%0' . $data->info4 . 'd', $number);
                } else {
                    $years = $data->info3;
                    $number = ($data->info2 + 1);
                    $newnumber = sprintf('%0' . $data->info4 . 'd', $number);
                }
                $newcode = str_ireplace('XXXX', $newnumber, $data->info);
                $newcode = str_ireplace('YEAR', $years, $newcode);
            } else {
                $number = ($data->info2 + 1);
                $newnumber = sprintf('%0' . $data->info4 . 'd', $number);
                $newcode = str_ireplace('XXXX', $newnumber, $data->info);
            }

            $no_doc = $newcode;
        }

        return $no_doc;
    }

    public function list_jurnal_pph21($id_header)
    {
        $get_kasbon = $this->db->get_where('kons_tr_kasbon_project_header', ['id' => $id_header])->row();

        $get_penawaran = $this->db->get_where('kons_tr_penawaran', ['id_quotation' => $get_kasbon->id_penawaran])->row();
        $get_company = $this->db->get_where('kons_tr_company', ['id' => $get_penawaran->company])->row();

        $id_company = (!empty($get_company)) ? $get_company->id : '';
        $nm_company = (!empty($get_company)) ? $get_company->nm_company : '';

        $hasil = '';
        $nominal_pph = 0;

        if (!empty($get_kasbon) && $get_kasbon->tipe == '2') {
            $nominal = 0;

            $this->db->select('a.total_pengajuan as ttl_kasbon');
            $this->db->from('kons_tr_kasbon_project_akomodasi a');
            $this->db->where('a.id_header', $id_header);
            $this->db->where('a.id_item', '15');
            $get_poin_pph = $this->db->get()->result();

            foreach ($get_poin_pph as $item_pph) :
                $nominal += $item_pph->ttl_kasbon;
            endforeach;

            $nominal_w_pph = (100 / (100 - 10) * $nominal);
            $nominal_pph = ($nominal_w_pph - $nominal);

            $coa_pph = '1106-01-01';
            $get_coa_pph = $this->gl->get_where('coa_master', ['no_perkiraan' => $coa_pph])->row();

            $hasil .= '<tr>';

            $hasil .= '<td class="text-center">';
            $hasil .= date('d F Y');
            $hasil .= '<input type="hidden" name="jurnal_pph[1][tanggal_jurnal]" value="' . date('Y-m-d') . '">';
            $hasil .= '</td>';

            $hasil .= '<td>';
            $hasil .= $get_coa_pph->no_perkiraan;
            $hasil .= '<input type="hidden" name="jurnal_pph[1][coa]" value="' . $get_coa_pph->no_perkiraan . '">';
            $hasil .= '</td>';

            $hasil .= '<td>';
            $hasil .= $nm_company;
            $hasil .= '<input type="hidden" name="jurnal_pph[1][id_company]" value="' . $id_company . '">';
            $hasil .= '<input type="hidden" name="jurnal_pph[1][nm_company]" value="' . $nm_company . '">';
            $hasil .= '</td>';

            $hasil .= '<td>';
            $hasil .= $get_coa_pph->nama;
            $hasil .= '<input type="hidden" name="jurnal_pph[1][nm_coa]" value="' . $get_coa_pph->nama . '">';
            $hasil .= '</td>';

            $hasil .= '<td>';
            $hasil .= 'PPh 21';
            $hasil .= '<input type="hidden" name="jurnal_pph[1][keterangan]" value="PPh 21">';
            $hasil .= '</td>';

            $hasil .= '<td class="text-right">';
            $hasil .= number_format(0);
            $hasil .= '<input type="hidden" name="jurnal_pph[1][debit]" value="0">';
            $hasil .= '</td>';

            $hasil .= '<td class="text-right">';
            $hasil .= number_format($nominal_pph);
            $hasil .= '<input type="hidden" name="jurnal_pph[1][kredit]" value="' . $nominal_pph . '">';
            $hasil .= '</td>';

            $hasil .= '</tr>';
        }


        $response = [
            'hasil' => $hasil,
            'nominal_pph' => $nominal_pph
        ];

        return $response;
    }

    public function set_jurnal_expense()
    {
        $post = $this->input->post();

        $id_header = $post['id_header'];

        $id_penawaran = $post['id_penawaran'];

        $kelebihan_kasbon = $post['kelebihan_kasbon'];
        $kelebihan_expense = $post['kelebihan_expense'];
        $kontrol = $post['kontrol'];

        $total_kasbon = $post['total_kasbon'];
        $total_expense = $post['total_expense'];

        $id_bank = $post['id_bank'];

        $get_expense_header = $this->db->get_where('kons_tr_expense_report_project_header', array('id_header' => $id_header))->row();

        $id_expense = $post['id_expense'] ?? ($get_expense_header->id ?? ($get_expense_header->id_expense ?? ''));
        if (empty($id_expense)) {
            $id_expense = $this->preview_id_expense_report_header('format_expense');
        }

        // print_r($post['arr_total_expense']);
        // exit;

        $this->db->select('a.id, a.nm_company');
        $this->db->from('kons_tr_company a');
        $this->db->join('kons_tr_penawaran b', 'b.company = a.id');
        $this->db->where('b.id_quotation', $id_penawaran);
        $get_company = $this->db->get()->row();

        $id_company = (!empty($get_company)) ? $get_company->id : '';
        $nm_company = (!empty($get_company)) ? $get_company->nm_company : '';

        $hasil_jurnal = '';

        $ttl_debit = 0;
        $ttl_kredit = 0;
        if ($kelebihan_expense == '0' && $kelebihan_kasbon == '0') {
            $arr_coa_jurnal = ['5101-01-03', '1103-01-14'];

            $this->gl->select('a.no_perkiraan, a.nama as nm_coa');
            $this->gl->from('coa_master a');
            $this->gl->where_in('a.no_perkiraan', $arr_coa_jurnal);
            $get_coa = $this->gl->get()->result();

            $hasil_jurnal = '';
            $no_jurnal = 0;


            foreach ($get_coa as $item_coa) {
                $no_jurnal++;

                $debit = 0;
                $kredit = 0;

                $deskripsi = $item_coa->nm_coa . (!empty($id_expense) ? ' - ' . $id_expense : '');

                if ($item_coa->no_perkiraan == '5101-01-03') {
                    $get_kasbon = $this->db->get_where('kons_tr_kasbon_project_header', array('id' => $id_header))->row();
                    if (isset($get_kasbon)) {
                        if ($get_kasbon->tipe == 1) {
                        }
                        if ($get_kasbon->tipe == 2) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_akomodasi a');
                            $this->db->join('kons_master_biaya b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_akomodasi = $this->db->get()->result();

                            $no_akomodasi = 0;
                            foreach ($get_akomodasi as $item) {
                                $id_akomodasi = $item->id_akomodasi;
                                if ($item->custom_akomodasi == 0) {
                                    $get_id_akomodasi = $this->db->select('id')->get_where('kons_tr_spk_budgeting_akomodasi', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_akomodasi' => $item->id_akomodasi])->row();

                                    $id_akomodasi = $get_id_akomodasi->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$id_akomodasi])) ? $post['arr_total_expense'][$id_akomodasi] : 0;

                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;
                            }
                        }
                        if ($get_kasbon->tipe == 3) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_others a');
                            $this->db->join('kons_master_biaya b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_others = $this->db->get()->result();

                            $no_others = 0;
                            foreach ($get_others as $item) {
                                // print_r($item->id_others . "<br>");
                                // exit;
                                $id_others = $item->id_others;
                                if ($item->custom_others == 0) {
                                    $get_id_others = $this->db->select('id')->get_where('kons_tr_spk_budgeting_others', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_others' => $item->id_others])->row();

                                    $id_others = $get_id_others->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$id_others])) ? $post['arr_total_expense'][$id_others] : 0;


                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;

                                $no_others++;
                            }
                        }
                        if ($get_kasbon->tipe == 4) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_lab a');
                            $this->db->join('kons_master_lab b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_lab = $this->db->get()->result();

                            $no_lab = 0;
                            foreach ($get_lab as $item) {
                                $no_lab++;

                                $id_lab = $item->id_lab;
                                if ($item->custom_lab == 0) {
                                    $get_id_lab = $this->db->select('id')->get_where('kons_tr_spk_budgeting_lab', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_lab' => $item->id_lab])->row();

                                    $id_lab = $get_id_lab->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$id_lab])) ? $post['arr_total_expense'][$id_lab] : 0;

                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;
                            }
                        }
                        if ($get_kasbon->tipe == 5) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_subcont_tenaga_ahli a');
                            $this->db->join('kons_master_tenaga_ahli b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_subcont_tenaga_ahli = $this->db->get()->result();

                            $no_subcont_tenaga_ahli = 0;
                            foreach ($get_subcont_tenaga_ahli as $item) {
                                $no_subcont_tenaga_ahli++;

                                $id_subcont = $item->id_subcont;
                                if ($item->custom_akomodasi == 0) {
                                    $get_id_subcont = $this->db->select('id')->get_where('kons_tr_spk_budgeting_subcont_tenaga_ahli', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_subcont' => $item->id_subcont])->row();

                                    $id_subcont = $get_id_subcont->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$id_subcont])) ? $post['arr_total_expense'][$id_subcont] : 0;

                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;
                            }
                        }
                        if ($get_kasbon->tipe == 6) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_subcont_perusahaan a');
                            $this->db->join('kons_master_subcont_perusahaan b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_subcont_perusahaan = $this->db->get()->result();

                            $no_subcont_perusahaan = 0;
                            foreach ($get_subcont_perusahaan as $item) {
                                $no_subcont_perusahaan++;

                                $id_subcont = $item->id_subcont;
                                if ($item->custom_akomodasi == 0) {
                                    $get_id_subcont = $this->db->select('id')->get_where('kons_tr_spk_budgeting_subcont_perusahaan', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_subcont' => $item->id_subcont])->row();

                                    $id_subcont = $get_id_subcont->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$item->id_subcont])) ? $post['arr_total_expense'][$item->id_subcont] : 0;

                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;
                            }
                        }
                    }
                } else {

                    if ($item_coa->no_perkiraan == '5101-01-03') {
                        $debit = $total_expense;
                    }
                    if ($item_coa->no_perkiraan == '1103-01-14') {
                        $kredit = $total_kasbon;
                    }

                    $hasil_jurnal .= '<tr>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= date('d F Y');
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][tgl_jurnal]" value="' . date('Y-m-d') . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $item_coa->no_perkiraan;
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][coa]" value="' . $item_coa->no_perkiraan . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $nm_company;
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $item_coa->nm_coa;
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_coa]" value="' . $item_coa->nm_coa . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $deskripsi;
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][deskripsi]" value="' . $deskripsi . '">';
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

                    $ttl_debit += $debit;
                    $ttl_kredit += $kredit;
                }
            }
        }

        if ($kontrol > 0) {
            $arr_coa_jurnal = ['5101-01-03', '1103-01-14'];

            $coa_bank = '';
            if ($post['id_bank'] !== '') {
                $get_bank = $this->sendigs->get_where('ms_bank', ['id' => $post['id_bank']])->row();

                $coa_bank = $get_bank->coa_bank;
                array_push($arr_coa_jurnal, $get_bank->coa_bank);
            }

            $this->gl->select('a.no_perkiraan, a.nama as nm_coa');
            $this->gl->from('coa_master a');
            $this->gl->where_in('a.no_perkiraan', $arr_coa_jurnal);
            $get_coa = $this->gl->get()->result();

            $hasil_jurnal = '';
            $no_jurnal = 0;

            $ttl_debit = 0;
            $ttl_kredit = 0;
            foreach ($get_coa as $item_coa) {
                $no_jurnal++;

                $deskripsi = $item_coa->nm_coa . (!empty($id_expense) ? ' - ' . $id_expense : '');

                $debit = 0;
                $kredit = 0;

                if ($item_coa->no_perkiraan == '5101-01-03') {
                    $get_kasbon = $this->db->get_where('kons_tr_kasbon_project_header', array('id' => $id_header))->row();
                    if (isset($get_kasbon)) {
                        if ($get_kasbon->tipe == 1) {
                        }
                        if ($get_kasbon->tipe == 2) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_akomodasi a');
                            $this->db->join('kons_master_biaya b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_akomodasi = $this->db->get()->result();

                            $no_akomodasi = 0;
                            foreach ($get_akomodasi as $item) {
                                $id_akomodasi = $item->id_akomodasi;
                                if ($item->custom_akomodasi == 0) {
                                    $get_id_akomodasi = $this->db->select('id')->get_where('kons_tr_spk_budgeting_akomodasi', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_akomodasi' => $item->id_akomodasi])->row();

                                    $id_akomodasi = $get_id_akomodasi->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$id_akomodasi])) ? $post['arr_total_expense'][$id_akomodasi] : 0;

                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;
                            }
                        }
                        if ($get_kasbon->tipe == 3) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_others a');
                            $this->db->join('kons_master_biaya b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_others = $this->db->get()->result();

                            $no_others = 0;
                            foreach ($get_others as $item) {
                                // print_r($item->id_others . "<br>");
                                // exit;
                                $id_others = $item->id_others;
                                if ($item->custom_others == 0) {
                                    $get_id_others = $this->db->select('id')->get_where('kons_tr_spk_budgeting_others', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_others' => $item->id_others])->row();

                                    $id_others = $get_id_others->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$id_others])) ? $post['arr_total_expense'][$id_others] : 0;


                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;

                                $no_others++;
                            }
                        }
                        if ($get_kasbon->tipe == 4) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_lab a');
                            $this->db->join('kons_master_lab b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_lab = $this->db->get()->result();

                            $no_lab = 0;
                            foreach ($get_lab as $item) {
                                $no_lab++;

                                $id_lab = $item->id_lab;
                                if ($item->custom_lab == 0) {
                                    $get_id_lab = $this->db->select('id')->get_where('kons_tr_spk_budgeting_lab', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_lab' => $item->id_lab])->row();

                                    $id_lab = $get_id_lab->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$id_lab])) ? $post['arr_total_expense'][$id_lab] : 0;

                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;
                            }
                        }
                        if ($get_kasbon->tipe == 5) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_subcont_tenaga_ahli a');
                            $this->db->join('kons_master_tenaga_ahli b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_subcont_tenaga_ahli = $this->db->get()->result();

                            $no_subcont_tenaga_ahli = 0;
                            foreach ($get_subcont_tenaga_ahli as $item) {
                                $no_subcont_tenaga_ahli++;

                                $id_subcont = $item->id_subcont;
                                if ($item->custom_akomodasi == 0) {
                                    $get_id_subcont = $this->db->select('id')->get_where('kons_tr_spk_budgeting_subcont_tenaga_ahli', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_subcont' => $item->id_subcont])->row();

                                    $id_subcont = $get_id_subcont->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$id_subcont])) ? $post['arr_total_expense'][$id_subcont] : 0;

                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;
                            }
                        }
                        if ($get_kasbon->tipe == 6) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_subcont_perusahaan a');
                            $this->db->join('kons_master_subcont_perusahaan b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_subcont_perusahaan = $this->db->get()->result();

                            $no_subcont_perusahaan = 0;
                            foreach ($get_subcont_perusahaan as $item) {
                                $no_subcont_perusahaan++;

                                $id_subcont = $item->id_subcont;
                                if ($item->custom_akomodasi == 0) {
                                    $get_id_subcont = $this->db->select('id')->get_where('kons_tr_spk_budgeting_subcont_perusahaan', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_subcont' => $item->id_subcont])->row();

                                    $id_subcont = $get_id_subcont->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$item->id_subcont])) ? $post['arr_total_expense'][$item->id_subcont] : 0;

                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;
                            }
                        }
                    }
                } else {

                    if ($item_coa->no_perkiraan == '5101-01-03') {
                        $debit = $total_expense;
                    }
                    if ($item_coa->no_perkiraan == '1103-01-14') {
                        $kredit = $total_kasbon;
                    }
                    if ($coa_bank !== '' && $item_coa->no_perkiraan == $coa_bank) {
                        $debit = $kontrol;
                    }


                    $hasil_jurnal .= '<tr>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= date('d F Y');
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][tgl_jurnal]" value="' . date('Y-m-d') . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $item_coa->no_perkiraan;
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][coa]" value="' . $item_coa->no_perkiraan . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $nm_company;
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $item_coa->nm_coa;
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_coa]" value="' . $item_coa->nm_coa . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $deskripsi;
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][deskripsi]" value="' . $deskripsi . '">';
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

                    $ttl_debit += $debit;
                    $ttl_kredit += $kredit;
                }
            }
        }

        if ($kontrol < 0) {
            $arr_coa_jurnal = ['5101-01-03', '1103-01-14', '9999-99-99'];

            $this->gl->select('a.no_perkiraan, a.nama as nm_coa');
            $this->gl->from('coa_master a');
            $this->gl->where_in('a.no_perkiraan', $arr_coa_jurnal);
            $get_coa = $this->gl->get()->result();

            $hasil_jurnal = '';
            $no_jurnal = 0;

            $ttl_debit = 0;
            $ttl_kredit = 0;
            foreach ($get_coa as $item_coa) {
                $no_jurnal++;

                $deskripsi = $item_coa->nm_coa . (!empty($id_expense) ? ' - ' . $id_expense : '');

                $debit = 0;
                $kredit = 0;

                if ($item_coa->no_perkiraan == '5101-01-03') {
                    $get_kasbon = $this->db->get_where('kons_tr_kasbon_project_header', array('id' => $id_header))->row();
                    if (isset($get_kasbon)) {
                        if ($get_kasbon->tipe == 1) {
                        }
                        if ($get_kasbon->tipe == 2) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_akomodasi a');
                            $this->db->join('kons_master_biaya b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_akomodasi = $this->db->get()->result();

                            $no_akomodasi = 0;
                            foreach ($get_akomodasi as $item) {
                                $id_akomodasi = $item->id_akomodasi;
                                if ($item->custom_akomodasi == 0) {
                                    $get_id_akomodasi = $this->db->select('id')->get_where('kons_tr_spk_budgeting_akomodasi', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_akomodasi' => $item->id_akomodasi])->row();

                                    $id_akomodasi = $get_id_akomodasi->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$id_akomodasi])) ? $post['arr_total_expense'][$id_akomodasi] : 0;

                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;
                            }
                        }
                        if ($get_kasbon->tipe == 3) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_others a');
                            $this->db->join('kons_master_biaya b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_others = $this->db->get()->result();

                            $no_others = 0;
                            foreach ($get_others as $item) {
                                // print_r($item->id_others . "<br>");
                                // exit;
                                $id_others = $item->id_others;
                                if ($item->custom_others == 0) {
                                    $get_id_others = $this->db->select('id')->get_where('kons_tr_spk_budgeting_others', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_others' => $item->id_others])->row();

                                    $id_others = $get_id_others->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$id_others])) ? $post['arr_total_expense'][$id_others] : 0;


                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;

                                $no_others++;
                            }
                        }
                        if ($get_kasbon->tipe == 4) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_lab a');
                            $this->db->join('kons_master_lab b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_lab = $this->db->get()->result();

                            $no_lab = 0;
                            foreach ($get_lab as $item) {
                                $no_lab++;

                                $id_lab = $item->id_lab;
                                if ($item->custom_lab == 0) {
                                    $get_id_lab = $this->db->select('id')->get_where('kons_tr_spk_budgeting_lab', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_lab' => $item->id_lab])->row();

                                    $id_lab = $get_id_lab->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$id_lab])) ? $post['arr_total_expense'][$id_lab] : 0;

                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;
                            }
                        }
                        if ($get_kasbon->tipe == 5) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_subcont_tenaga_ahli a');
                            $this->db->join('kons_master_tenaga_ahli b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_subcont_tenaga_ahli = $this->db->get()->result();

                            $no_subcont_tenaga_ahli = 0;
                            foreach ($get_subcont_tenaga_ahli as $item) {
                                $no_subcont_tenaga_ahli++;

                                $id_subcont = $item->id_subcont;
                                if ($item->custom_akomodasi == 0) {
                                    $get_id_subcont = $this->db->select('id')->get_where('kons_tr_spk_budgeting_subcont_tenaga_ahli', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_subcont' => $item->id_subcont])->row();

                                    $id_subcont = $get_id_subcont->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$id_subcont])) ? $post['arr_total_expense'][$id_subcont] : 0;

                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;
                            }
                        }
                        if ($get_kasbon->tipe == 6) {
                            $this->db->select('a.*, b.no_coa, b.nm_coa');
                            $this->db->from('kons_tr_kasbon_project_subcont_perusahaan a');
                            $this->db->join('kons_master_subcont_perusahaan b', 'b.id = a.id_item', 'left');
                            $this->db->where('a.id_header', $id_header);
                            $get_subcont_perusahaan = $this->db->get()->result();

                            $no_subcont_perusahaan = 0;
                            foreach ($get_subcont_perusahaan as $item) {
                                $no_subcont_perusahaan++;

                                $id_subcont = $item->id_subcont;
                                if ($item->custom_akomodasi == 0) {
                                    $get_id_subcont = $this->db->select('id')->get_where('kons_tr_spk_budgeting_subcont_perusahaan', ['id_spk_penawaran' => $item->id_spk_penawaran, 'id_subcont' => $item->id_subcont])->row();

                                    $id_subcont = $get_id_subcont->id;
                                }

                                $total_pengajuan = (isset($post['arr_total_expense'][$item->id_subcont])) ? $post['arr_total_expense'][$item->id_subcont] : 0;

                                $debit = $total_pengajuan;

                                $no_coa = ($item->no_coa == null) ? '5101-01-03' : $item->no_coa;
                                $nm_coa = ($item->nm_coa == null) ? 'Biaya Pengeluaran Lainnya' : $item->nm_coa;

                                $hasil_jurnal .= '<tr>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= date('d F Y');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $no_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_company;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $nm_coa;
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-center">';
                                $hasil_jurnal .= $item->nm_item . (!empty($id_expense) ? ' - ' . $id_expense : '');
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($debit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '<td class="text-right">';
                                $hasil_jurnal .= number_format($kredit);
                                $hasil_jurnal .= '</td>';

                                $hasil_jurnal .= '</tr>';

                                $ttl_debit += $debit;
                                $ttl_kredit += $kredit;
                            }
                        }
                    }
                } else {
                    if ($item_coa->no_perkiraan == '5101-01-03') {
                        $debit = $total_expense;
                    }
                    if ($item_coa->no_perkiraan == '1103-01-14') {
                        $kredit = $total_kasbon;
                    }
                    if ($item_coa->no_perkiraan == '9999-99-99') {
                        $kredit = ($kontrol * -1);
                    }


                    $hasil_jurnal .= '<tr>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= date('d F Y');
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][tgl_jurnal]" value="' . date('Y-m-d') . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $item_coa->no_perkiraan;
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][coa]" value="' . $item_coa->no_perkiraan . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $nm_company;
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][id_company]" value="' . $id_company . '">';
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_company]" value="' . $nm_company . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $item_coa->nm_coa;
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][nm_coa]" value="' . $item_coa->nm_coa . '">';
                    $hasil_jurnal .= '</td>';

                    $hasil_jurnal .= '<td class="text-center">';
                    $hasil_jurnal .= $deskripsi;
                    $hasil_jurnal .= '<input type="hidden" name="jurnal[' . $no_jurnal . '][deskripsi]" value="' . $deskripsi . '">';
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

                    $ttl_debit += $debit;
                    $ttl_kredit += $kredit;
                }
            }
        }

        $response = [
            'hasil' => $hasil_jurnal,
            'ttl_debit' => $ttl_debit,
            'ttl_kredit' => $ttl_kredit
        ];

        echo json_encode($response);
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Audit_jurnal_payment extends Admin_Controller
{
    protected $viewPermission   = 'Audit_Jurnal_Payment.View';
    protected $managePermission = 'Audit_Jurnal_Payment.Manage';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('audit_jurnal_payment/Audit_jurnal_payment_model');
        $this->template->title('Audit & Penyesuaian Jurnal Payment');
        $this->template->page_icon('fa fa-wrench');

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Audit & Penyesuaian Jurnal Payment');
        $this->template->render('index');
    }

    public function get_data_audit()
    {
        $post = $this->input->post();

        $tgl_jurnal = $post['tgl_jurnal'] ?? '';
        $tgl_from   = '';
        $tgl_to     = '';
        if (!empty($tgl_jurnal)) {
            $exp = explode(' to ', $tgl_jurnal);
            $tgl_from = $exp[0] ?? '';
            $tgl_to   = $exp[1] ?? '';
        }

        $filter = [
            'tgl_from'     => $tgl_from,
            'tgl_to'       => $tgl_to,
            'tipe'         => $post['tipe'] ?? '',
            'status_issue' => $post['status_issue'] ?? '',
            'search'       => $post['search']['value'] ?? ''
        ];

        $audit_result = $this->Audit_jurnal_payment_model->get_audit_data($filter);

        $draw = intval($post['draw'] ?? 1);
        $data_rows = [];
        $no = intval($post['start'] ?? 0);

        foreach ($audit_result['data'] as $item) {
            $no++;
            $p = $item['payment'];
            $c = $item['comparison'];

            // Badge Tipe
            $tipe_raw = ucwords(str_replace('_', ' ', strtolower($p->tipe)));
            $badge_tipe = '<span class="label label-primary" style="font-size:11px; padding:3px 7px; border-radius:4px;">' . htmlspecialchars($tipe_raw) . '</span>';
            if (strpos(strtolower($p->tipe), 'kasbon') !== false) {
                $badge_tipe = '<span class="label label-warning" style="font-size:11px; padding:3px 7px; border-radius:4px;">' . htmlspecialchars($tipe_raw) . '</span>';
            } elseif (strpos(strtolower($p->tipe), 'transport') !== false) {
                $badge_tipe = '<span class="label label-info" style="font-size:11px; padding:3px 7px; border-radius:4px;">' . htmlspecialchars($tipe_raw) . '</span>';
            }

            // Issue Badges
            $issue_html = '';
            if (!$c['has_issue']) {
                $issue_html = '<span class="label label-success" style="font-size:11px; padding:4px 8px; border-radius:4px;"><i class="fa fa-check"></i> Sesuai & Balance</span>';
            } else {
                foreach ($c['issue_types'] as $itype) {
                    if ($itype == 'Unbalanced') {
                        $issue_html .= '<span class="label label-danger" style="font-size:10px; padding:3px 6px; border-radius:4px; margin-right:3px; display:inline-block; margin-bottom:2px;"><i class="fa fa-exclamation-triangle"></i> Tidak Balance</span> ';
                    } elseif ($itype == 'No Journal') {
                        $issue_html .= '<span class="label label-default" style="font-size:10px; padding:3px 6px; border-radius:4px; margin-right:3px; display:inline-block; margin-bottom:2px; background:#e74c3c; color:#fff;"><i class="fa fa-times-circle"></i> Belum Ada Jurnal</span> ';
                    } elseif ($itype == 'Missing Suffix') {
                        $issue_html .= '<span class="label label-warning" style="font-size:10px; padding:3px 6px; border-radius:4px; margin-right:3px; display:inline-block; margin-bottom:2px;"><i class="fa fa-tag"></i> Suffix Hilang</span> ';
                    } elseif ($itype == 'Nominal Mismatch') {
                        $issue_html .= '<span class="label label-danger" style="font-size:10px; padding:3px 6px; border-radius:4px; margin-right:3px; display:inline-block; margin-bottom:2px;"><i class="fa fa-calculator"></i> Selisih Nominal</span> ';
                    } else {
                        $issue_html .= '<span class="label label-info" style="font-size:10px; padding:3px 6px; border-radius:4px; margin-right:3px; display:inline-block; margin-bottom:2px;">' . htmlspecialchars($itype) . '</span> ';
                    }
                }
            }

            // Checkbox for bulk fix
            $checkbox = '<input type="checkbox" class="check_audit" value="' . $p->id . '" ' . (!$c['has_issue'] ? 'disabled' : '') . '>';

            // Balance comparison preview
            $nominal_preview = '<div style="font-size:12px; line-height:1.4;">';
            $nominal_preview .= '<span class="text-muted">Eksisting:</span> D: ' . number_format($c['existing_ttl_deb']) . ' | K: ' . number_format($c['existing_ttl_krd']) . '<br>';
            $nominal_preview .= '<span class="text-primary font-weight-bold">Seharusnya:</span> D: ' . number_format($c['expected_ttl_deb']) . ' | K: ' . number_format($c['expected_ttl_krd']);
            $nominal_preview .= '</div>';

            // Action Buttons
            $action_btn = '<div class="btn-group" style="white-space: nowrap;">';
            $action_btn .= '<button type="button" class="btn btn-sm btn-info btn_compare" data-id="' . $p->id . '" title="Bandingkan Detail" style="border-radius:4px; padding:4px 8px; margin-right:4px;"><i class="fa fa-columns"></i> Compare</button>';
            if ($c['has_issue']) {
                $action_btn .= '<button type="button" class="btn btn-sm btn-success btn_fix_single" data-id="' . $p->id . '" data-doc="' . htmlspecialchars($p->no_doc, ENT_QUOTES) . '" title="Perbaiki Jurnal Ini" style="border-radius:4px; padding:4px 8px;"><i class="fa fa-wrench"></i> Fix</button>';
            }
            $action_btn .= '</div>';

            $data_rows[] = [
                'checkbox'     => $checkbox,
                'no'           => $no,
                'no_transaksi' => '<span style="font-weight:700; color:#0073b7;">' . htmlspecialchars($p->id) . '</span>',
                'no_doc'       => '<span style="font-weight:600; color:#333;">' . htmlspecialchars($p->no_doc ?? '') . '</span>',
                'tipe'         => $badge_tipe,
                'tgl_bayar'    => !empty($p->tgl_bayar) ? date('d F Y', strtotime($p->tgl_bayar)) : '-',
                'jumlah'       => '<span style="font-weight:600;">Rp ' . number_format(floatval($p->jumlah ?? $p->total_payment ?? 0)) . '</span>',
                'status_issue' => $issue_html,
                'balance_info' => $nominal_preview,
                'action'       => $action_btn
            ];
        }

        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => count($audit_result['data']),
            'recordsFiltered' => count($audit_result['data']),
            'data'            => $data_rows,
            'summary'         => $audit_result['summary']
        ]);
    }

    public function modal_compare()
    {
        $id = $this->input->post('id');

        $this->db->where('id', $id);
        $payment = $this->db->get('payment_approve')->row();

        if (empty($payment)) {
            echo '<div class="alert alert-danger">Data payment tidak ditemukan!</div>';
            return;
        }

        // Get existing jurnal
        $target_id = $payment->id;
        $this->db->select('j.*');
        $this->db->from('tr_jurnal j');
        $this->db->group_start();
        $this->db->where('j.no_transaksi', (string)$target_id);
        $this->db->or_where("FIND_IN_SET('{$target_id}', REPLACE(j.no_transaksi, ' ', '')) > 0", null, false);
        if (!empty($payment->id_payment)) {
            $this->db->or_where('j.no_transaksi', (string)$payment->id_payment);
            $this->db->or_where("FIND_IN_SET('{$payment->id_payment}', REPLACE(j.no_transaksi, ' ', '')) > 0", null, false);
        }
        $this->db->group_end();
        $existing_jurnal = $this->db->get()->result();

        // Get expected jurnal
        $expected_jurnal = $this->Audit_jurnal_payment_model->reconstruct_expected_jurnal($payment, $existing_jurnal);
        $comparison      = $this->Audit_jurnal_payment_model->compare_jurnal($existing_jurnal, $expected_jurnal, $payment);

        $data = [
            'payment'         => $payment,
            'existing_jurnal' => $existing_jurnal,
            'expected_jurnal' => $expected_jurnal,
            'comparison'      => $comparison
        ];

        $this->load->view('modal_compare', $data);
    }

    public function fix_single_jurnal()
    {
        $id = $this->input->post('id');

        try {
            $this->db->trans_begin();

            $this->Audit_jurnal_payment_model->fix_jurnal_transaction($id);

            if ($this->db->trans_status() === false) {
                throw new Exception("Terjadi kegagalan transaksi database saat memperbaiki jurnal.");
            }

            $this->db->trans_commit();

            echo json_encode([
                'status' => 1,
                'msg'    => "Sukses! Jurnal pembayaran #{$id} berhasil diperbaiki dan disinkronkan."
            ]);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode([
                'status' => 0,
                'msg'    => "Gagal memperbaiki jurnal: " . $e->getMessage()
            ]);
        }
    }

    public function fix_bulk_jurnal()
    {
        $ids = $this->input->post('ids');

        if (empty($ids) || !is_array($ids)) {
            echo json_encode([
                'status' => 0,
                'msg'    => 'Silakan pilih setidaknya satu data transaksi untuk diperbaiki.'
            ]);
            return;
        }

        $success_count = 0;
        $failed_count  = 0;
        $errors        = [];

        foreach ($ids as $payment_id) {
            try {
                $this->db->trans_begin();

                $this->Audit_jurnal_payment_model->fix_jurnal_transaction($payment_id);

                if ($this->db->trans_status() === false) {
                    throw new Exception("Gagal simpan transaksi");
                }

                $this->db->trans_commit();
                $success_count++;
            } catch (Exception $e) {
                $this->db->trans_rollback();
                $failed_count++;
                $errors[] = "ID #{$payment_id}: " . $e->getMessage();
            }
        }

        $msg = "Proses selesai! {$success_count} transaksi berhasil diperbaiki.";
        if ($failed_count > 0) {
            $msg .= " ({$failed_count} transaksi gagal diproses).";
        }

        echo json_encode([
            'status'        => $success_count > 0 ? 1 : 0,
            'msg'           => $msg,
            'success_count' => $success_count,
            'failed_count'  => $failed_count,
            'errors'        => $errors
        ]);
    }
}

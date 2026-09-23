<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper: PR Progress Stepper (FSD 2026-09)
 * Mendukung 3 modul: PR Department, PR Asset, PR Stock
 * Tabel: tb_pr_flow_template & tb_pr_stage_log
 */

if (!function_exists('get_pr_flow_stages')) {
    /**
     * Ambil daftar stage dari tb_pr_flow_template
     * @param string $pr_type 'department' | 'asset' | 'stock'
     * @param string|null $metode 'Kasbon' | 'Direct Payment' | 'PO' | null
     * @return array [stage_no => stage_name]
     */
    function get_pr_flow_stages($pr_type, $metode = null)
    {
        $CI = &get_instance();

        // Normalisasi nama metode
        $method_name = null;
        if (!empty($metode)) {
            $m = strtolower(trim((string)$metode));
            if ($m == '1' || $m == 'po') {
                $method_name = 'PO';
            } elseif ($m == '2' || $m == 'kasbon') {
                $method_name = 'Kasbon';
            } elseif ($m == '3' || $m == 'direct payment' || $m == 'direct_payment' || $m == 'non_po') {
                $method_name = 'Direct Payment';
            } else {
                $method_name = $metode;
            }
        }

        $stages = [];
        if (!empty($method_name)) {
            $rows = $CI->db->get_where('tb_pr_flow_template', [
                'pr_type'          => $pr_type,
                'metode_pembelian' => $method_name
            ])->result();

            if (!empty($rows)) {
                foreach ($rows as $r) {
                    $stages[(int)$r->stage_no] = $r->stage_name;
                }
                return $stages;
            }
        }

        // Fallback ke template dasar (sebelum metode dipilih)
        $rows = $CI->db->where('pr_type', $pr_type)
                       ->where('metode_pembelian IS NULL', null, false)
                       ->order_by('stage_no', 'asc')
                       ->get('tb_pr_flow_template')
                       ->result();

        foreach ($rows as $r) {
            $stages[(int)$r->stage_no] = $r->stage_name;
        }

        // Hardcoded safety fallback jika tabel template belum terisi
        if (empty($stages)) {
            if ($pr_type == 'department') {
                $stages = [1 => 'PR — Finance', 2 => 'PR — Direktur', 3 => 'Metode Pembelian'];
            } else {
                $stages = [1 => 'PR — Direktur', 2 => 'Metode Pembelian'];
            }
        }

        return $stages;
    }
}

if (!function_exists('render_pr_progress_cell')) {
    /**
     * Render komponen Progress PR (stepper dots, label, badge, meta/reason)
     * @param string $pr_type 'department' | 'asset' | 'stock'
     * @param object|array $item
     * @return string HTML
     */
    function render_pr_progress_cell($pr_type, $item)
    {
        $item = (object) $item;
        $stages_info = evaluate_pr_status($pr_type, $item);

        $stages       = get_pr_flow_stages($pr_type, $stages_info['metode']);
        $current_stage = (int)$stages_info['stage'];
        $status       = $stages_info['status']; // 'active', 'done', 'reject'
        $status_text  = $stages_info['status_text'];
        $doc_meta     = $stages_info['doc_meta'];
        $reason       = $stages_info['reason'];

        $total_stages = count($stages);
        if ($current_stage > $total_stages) {
            $current_stage = $total_stages;
        }

        $current_stage_label = $stages[$current_stage] ?? ($stages[1] ?? 'PR');

        // Generate Dots & Lines HTML
        $dots_html = '';
        $idx = 0;
        foreach ($stages as $stage_num => $label) {
            $idx++;
            $cls = 'pending';
            if ($stage_num < $current_stage) {
                $cls = 'done';
            } elseif ($stage_num == $current_stage) {
                if ($status == 'reject') {
                    $cls = 'reject';
                } elseif ($status == 'done') {
                    $cls = 'done';
                } else {
                    $cls = 'active';
                }
            }

            $line_cls = ($cls == 'done') ? 'done' : '';
            $line_html = ($idx < $total_stages) ? '<span class="step-line ' . $line_cls . '"></span>' : '';
            $dots_html .= '<span class="step-dot ' . $cls . '" data-toggle="tooltip" data-placement="top" title="Tahap ' . $stage_num . ': ' . htmlspecialchars($label) . '"></span>' . $line_html;
        }

        // Badge class
        $badge_cls = 'st-wait';
        if ($status == 'reject') {
            $badge_cls = 'st-reject';
        } elseif ($status == 'done') {
            $badge_cls = 'st-final';
        }

        // Reason or doc meta line
        if ($status == 'reject' && !empty($reason)) {
            $meta_html = '<div class="reject-reason" title="' . htmlspecialchars($reason) . '">Alasan: ' . htmlspecialchars($reason) . '</div>';
        } elseif (!empty($doc_meta)) {
            $prefix = (!empty($stages_info['method_name']) && stripos($doc_meta, $stages_info['method_name']) === false) 
                ? $stages_info['method_name'] . ' - ' 
                : '';
            $meta_html = '<div class="doc-meta">' . $prefix . $doc_meta . '</div>';
        } else {
            $meta_html = '<div class="doc-meta">' . (!empty($stages_info['method_name']) ? $stages_info['method_name'] : 'Belum masuk metode pembelian') . '</div>';
        }

        $html  = '<div class="progress-cell">';
        $html .= '  <div class="steps">' . $dots_html . '</div>';
        $html .= '  <div class="stage-label">' . htmlspecialchars($current_stage_label) . '</div>';
        $html .= '  <span class="status-badge ' . $badge_cls . '">' . htmlspecialchars($status_text) . '</span>';
        $html .= '  ' . $meta_html;
        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('evaluate_pr_status')) {
    /**
     * Evaluasi stage, status, dan teks detail untuk PR
     * @param string $pr_type 'department' | 'asset' | 'stock'
     * @param object $item
     * @return array
     */
    function evaluate_pr_status($pr_type, $item)
    {
        $CI = &get_instance();

        $stage       = 1;
        $status      = 'active'; // 'active', 'done', 'reject'
        $status_text = '';
        $doc_meta    = '';
        $reason      = '';
        $metode      = $item->metode_pembelian ?? null;
        $method_name = null;

        if ($metode == '1' || strtolower((string)$metode) == 'po') {
            $method_name = 'PO';
        } elseif ($metode == '2' || strtolower((string)$metode) == 'kasbon') {
            $method_name = 'Kasbon';
        } elseif ($metode == '3' || strtolower((string)$metode) == 'direct payment' || strtolower((string)$metode) == 'direct_payment') {
            $method_name = 'Direct Payment';
        }

        // ==========================================
        // 1. PR DEPARTMENT
        // ==========================================
        if ($pr_type == 'department') {
            // Cek Reject
            $sts_rej1 = $item->sts_reject1 ?? null;
            $sts_rej2 = $item->sts_reject2 ?? null;
            $sts_rej3 = $item->sts_reject3 ?? null;
            $is_rejected = (($sts_rej1 !== null || $sts_rej2 !== null || $sts_rej3 !== null) && ($item->rejected ?? 0) == 1);
            if ($is_rejected) {
                $status = 'reject';
                $reason = !empty($item->reject_reason3) ? $item->reject_reason3 : (!empty($item->reject_reason1) ? $item->reject_reason1 : ($item->reject_reason ?? 'Pengajuan ditolak'));
                if ($item->sts_reject3 == '1') {
                    $stage = 2;
                    $status_text = 'Ditolak — Direktur';
                } else {
                    $stage = 1;
                    $status_text = 'Ditolak — Finance';
                }
            } elseif (($item->app_3 ?? null) == null) {
                // Belum approved Direktur
                if (empty($item->app_1_by) && empty($item->app_2_by)) {
                    $stage = 1;
                    $status = 'active';
                    $status_text = 'Menunggu Approval — Finance';
                    $doc_meta = 'Belum masuk ke tahap Direktur';
                } else {
                    $stage = 2;
                    $status = 'active';
                    $status_text = 'Menunggu Approval — Direktur';
                    $doc_meta = 'Disetujui Finance &middot; Menunggu Direktur';
                }
            } else {
                // Approved Direktur -> Lolos approval awal
                if (empty($metode)) {
                    $stage = 3;
                    $status = 'active';
                    $status_text = 'Menentukan Metode Pembelian';
                    $doc_meta = 'Belum masuk metode pembelian';
                } elseif ($method_name == 'Kasbon') {
                    if (empty($item->no_doc_kasbon)) {
                        $stage = 3;
                        $status = 'active';
                        $status_text = 'Menunggu Pembuatan Kasbon';
                        $doc_meta = 'Kasbon belum dibuat';
                    } else {
                        $tgl_kb_created = !empty($item->tgl_proses_kasbon) ? date('d M Y', strtotime($item->tgl_proses_kasbon)) : '';
                        $tgl_kb_app = !empty($item->tgl_kasbon_approved) ? date('d M Y', strtotime($item->tgl_kasbon_approved)) : $tgl_kb_created;

                        if (($item->kasbon_status ?? null) == '9' || ($item->kasbon_sts_reject ?? null) == '1') {
                            $stage = (($item->kasbon_sts_finance ?? null) == '1') ? 5 : 4;
                            $status = 'reject';
                            $status_text = 'Kasbon Ditolak';
                            $doc_meta = $item->no_doc_kasbon;
                            $reason = $item->kasbon_reject_reason ?? 'Kasbon ditolak';
                        } elseif (($item->kasbon_status ?? null) == '3') {
                            $stage = 6;
                            $status = 'done';
                            $status_text = 'Request Payment — Selesai';
                            $doc_meta = $item->no_doc_kasbon . ' &middot; request payment selesai ' . $tgl_kb_app;
                        } elseif (($item->kasbon_status ?? null) == '1' || ($item->kasbon_status ?? null) == '2') {
                            $stage = 6;
                            $status = 'active';
                            $status_text = 'Menunggu Request Payment';
                            $doc_meta = $item->no_doc_kasbon . ' &middot; kasbon disetujui ' . $tgl_kb_app;
                        } elseif (($item->kasbon_sts_finance ?? null) == '1') {
                            $stage = 5;
                            $status = 'active';
                            $status_text = 'Menunggu Approval Kasbon — Direktur';
                            $doc_meta = $item->no_doc_kasbon . ($tgl_kb_created ? ' &middot; dibuat ' . $tgl_kb_created : '');
                        } else {
                            $stage = 4;
                            $status = 'active';
                            $status_text = 'Menunggu Approval Kasbon — Finance';
                            $doc_meta = $item->no_doc_kasbon . ($tgl_kb_created ? ' &middot; dibuat ' . $tgl_kb_created : '');
                        }
                    }
                } elseif ($method_name == 'Direct Payment') {
                    if (empty($item->no_doc_non_po)) {
                        $stage = 3;
                        $status = 'active';
                        $status_text = 'Menunggu Request Payment';
                        $doc_meta = 'Belum dibuat request payment';
                    } else {
                        $tgl_np_created = !empty($item->tgl_proses_non_po) ? date('d M Y', strtotime($item->tgl_proses_non_po)) : '';
                        $tgl_payment = !empty($item->tgl_bayar_dp) ? date('d M Y', strtotime($item->tgl_bayar_dp)) : $tgl_np_created;

                        if (!empty($item->dp_paid) && $item->dp_paid == 1) {
                            $stage = 4;
                            $status = 'done';
                            $status_text = 'Request Payment — Selesai';
                            $doc_meta = $item->no_doc_non_po . ' &middot; payment selesai ' . $tgl_payment;
                        } else {
                            $stage = 4;
                            $status = 'active';
                            $status_text = 'Menunggu Request Payment';
                            $doc_meta = $item->no_doc_non_po . ($tgl_np_created ? ' &middot; request dibuat ' . $tgl_np_created : '');
                        }
                    }
                } elseif ($method_name == 'PO') {
                    if (empty($item->no_doc_po)) {
                        $stage = 3;
                        $status = 'active';
                        $status_text = 'Menunggu Pembuatan PO';
                        $doc_meta = 'PO belum dibuat';
                    } else {
                        if (($item->po_rejected ?? 0) == 1) {
                            $stage = 4;
                            $status = 'reject';
                            $status_text = 'PO Ditolak';
                            $doc_meta = $item->no_doc_po;
                        } elseif (($item->po_status ?? 0) >= 2) {
                            $stage = 5;
                            $status = 'done';
                            $status_text = 'PO Disetujui';
                            $doc_meta = $item->no_doc_po;
                        } else {
                            $stage = 4;
                            $status = 'active';
                            $status_text = 'Menunggu Persetujuan PO';
                            $doc_meta = $item->no_doc_po;
                        }
                    }
                }
            }
        }

        // ==========================================
        // 2. PR ASSET (1 level approval: Direktur saja)
        // ==========================================
        elseif ($pr_type == 'asset') {
            $is_reject = (($item->app_status_3 ?? '') == 'D' || ($item->app_status_1 ?? '') == 'D' || ($item->app_status_2 ?? '') == 'D' || ($item->status ?? '') == 'D');
            $is_approved = (($item->app_status_3 ?? '') == 'Y' || ($item->status ?? '') == 'Y');

            if ($is_reject) {
                $stage = 1;
                $status = 'reject';
                $status_text = 'Ditolak — Direktur';
                $reason = !empty($item->reject_reason) ? $item->reject_reason : (!empty($item->reason) ? $item->reason : (!empty($item->app_reason_3) ? $item->app_reason_3 : (!empty($item->app_reason_2) ? $item->app_reason_2 : (!empty($item->app_reason_1) ? $item->app_reason_1 : 'Pengajuan ditolak oleh Direktur'))));
            } elseif (!$is_approved) {
                $stage = 1;
                $status = 'active';
                $status_text = 'Menunggu Approval — Direktur';
                $doc_meta = 'Menunggu persetujuan Direktur';
            } else {
                // Approved Direktur -> Lolos
                if (empty($metode)) {
                    $stage = 2;
                    $status = 'active';
                    $status_text = 'Menentukan Metode Pembelian';
                    $doc_meta = 'Belum masuk metode pembelian';
                } elseif ($method_name == 'Direct Payment') {
                    $no_doc_dp = $item->no_doc_non_po ?? ($item->no_doc_dp ?? null);
                    $tgl_dp = !empty($item->tgl_bayar_dp) ? date('d M Y', strtotime($item->tgl_bayar_dp)) : date('d M Y');
                    if (!empty($item->dp_paid) && $item->dp_paid == 1) {
                        $stage = 3;
                        $status = 'done';
                        $status_text = 'Request Payment — Selesai';
                        $doc_meta = ($no_doc_dp ? $no_doc_dp . ' &middot; ' : '') . 'request payment selesai ' . $tgl_dp;
                    } else {
                        $stage = 3;
                        $status = 'active';
                        $status_text = 'Menunggu Request Payment';
                        $doc_meta = $no_doc_dp ? ($no_doc_dp . ' &middot; request dibuat') : 'Request dibuat';
                    }
                } elseif ($method_name == 'Kasbon') {
                    $no_kb = $item->no_doc_kasbon ?? null;
                    if (empty($no_kb)) {
                        $stage = 2;
                        $status = 'active';
                        $status_text = 'Menunggu Pembuatan Kasbon';
                    } elseif (($item->kasbon_status ?? null) == '9' || ($item->kasbon_sts_reject ?? null) == '1') {
                        $stage = (($item->kasbon_sts_finance ?? null) == '1') ? 4 : 3;
                        $status = 'reject';
                        $status_text = 'Kasbon Ditolak';
                        $doc_meta = $no_kb;
                        $reason = $item->kasbon_reject_reason ?? 'Kasbon ditolak';
                    } elseif (($item->kasbon_status ?? null) == '3') {
                        $stage = 5;
                        $status = 'done';
                        $status_text = 'Request Payment — Selesai';
                        $doc_meta = $no_kb . ' &middot; lunas';
                    } elseif (($item->kasbon_status ?? null) == '1' || ($item->kasbon_status ?? null) == '2') {
                        $stage = 5;
                        $status = 'active';
                        $status_text = 'Menunggu Request Payment';
                        $doc_meta = $no_kb . ' &middot; kasbon disetujui';
                    } elseif (($item->kasbon_sts_finance ?? null) == '1') {
                        $stage = 4;
                        $status = 'active';
                        $status_text = 'Menunggu Approval Kasbon — Direktur';
                        $doc_meta = $no_kb;
                    } else {
                        $stage = 3;
                        $status = 'active';
                        $status_text = 'Menunggu Approval Kasbon — Finance';
                        $doc_meta = $no_kb;
                    }
                } elseif ($method_name == 'PO') {
                    $stage = (!empty($item->po_status) && $item->po_status >= 2) ? 4 : 3;
                    $status = ($stage == 4) ? 'done' : 'active';
                    $status_text = ($stage == 4) ? 'PO Disetujui' : 'Menunggu PO';
                }
            }
        }

        // ==========================================
        // 3. PR STOCK (1 level approval: Direktur saja)
        // ==========================================
        elseif ($pr_type == 'stock') {
            $is_reject = (($item->sts_reject1 ?? null) == "1" || ($item->sts_reject2 ?? null) == "1" || ($item->sts_reject3 ?? null) == "1" || ($item->rejected ?? 0) == 1 || ($item->status ?? '') == 'D' || ($item->app_3 ?? '') == 'D');
            $is_approved = (($item->sts_app ?? '') == "Y" || ($item->app_3 ?? null) == "1" || ($item->status ?? '') == 'Y');

            if ($is_reject) {
                $stage = 1;
                $status = 'reject';
                $status_text = 'Ditolak — Direktur';
                $reason = !empty($item->reject_reason) ? $item->reject_reason : (!empty($item->reject_reason3) ? $item->reject_reason3 : 'Stok masih mencukupi / pengajuan ditolak');
            } elseif (!$is_approved) {
                $stage = 1;
                $status = 'active';
                $status_text = 'Menunggu Approval — Direktur';
                $doc_meta = 'Menunggu persetujuan Direktur';
            } else {
                // Approved Direktur
                if (empty($metode)) {
                    $stage = 2;
                    $status = 'active';
                    $status_text = 'Menentukan Metode Pembelian';
                    $doc_meta = 'Belum masuk metode pembelian';
                } elseif ($method_name == 'Direct Payment') {
                    $no_doc_dp = $item->no_doc_non_po ?? null;
                    if (!empty($item->dp_paid) && $item->dp_paid == 1) {
                        $stage = 3;
                        $status = 'done';
                        $status_text = 'Request Payment — Selesai';
                        $doc_meta = ($no_doc_dp ? $no_doc_dp . ' &middot; ' : '') . 'request payment selesai';
                    } else {
                        $stage = 3;
                        $status = 'active';
                        $status_text = 'Menunggu Request Payment';
                        $doc_meta = $no_doc_dp ? ($no_doc_dp . ' &middot; request dibuat') : 'Request dibuat';
                    }
                } elseif ($method_name == 'Kasbon') {
                    $no_kb = $item->no_doc_kasbon ?? null;
                    if (empty($no_kb)) {
                        $stage = 2;
                        $status = 'active';
                        $status_text = 'Menunggu Pembuatan Kasbon';
                    } elseif (($item->kasbon_status ?? null) == '9' || ($item->kasbon_sts_reject ?? null) == '1') {
                        $stage = (($item->kasbon_sts_finance ?? null) == '1') ? 4 : 3;
                        $status = 'reject';
                        $status_text = 'Kasbon Ditolak';
                        $doc_meta = $no_kb;
                        $reason = $item->kasbon_reject_reason ?? 'Kasbon ditolak';
                    } elseif (($item->kasbon_status ?? null) == '3') {
                        $stage = 5;
                        $status = 'done';
                        $status_text = 'Request Payment — Selesai';
                        $doc_meta = $no_kb . ' &middot; lunas';
                    } elseif (($item->kasbon_status ?? null) == '1' || ($item->kasbon_status ?? null) == '2') {
                        $stage = 5;
                        $status = 'active';
                        $status_text = 'Menunggu Request Payment';
                        $doc_meta = $no_kb . ' &middot; kasbon disetujui';
                    } elseif (($item->kasbon_sts_finance ?? null) == '1') {
                        $stage = 4;
                        $status = 'active';
                        $status_text = 'Menunggu Approval Kasbon — Direktur';
                        $doc_meta = $no_kb;
                    } else {
                        $stage = 3;
                        $status = 'active';
                        $status_text = 'Menunggu Approval Kasbon — Finance';
                        $doc_meta = $no_kb;
                    }
                } elseif ($method_name == 'PO') {
                    $stage = (!empty($item->po_status) && $item->po_status >= 2) ? 4 : 3;
                    $status = ($stage == 4) ? 'done' : 'active';
                    $status_text = ($stage == 4) ? 'PO Disetujui' : 'Menunggu PO';
                }
            }
        }

        // Auto-sync ke tb_pr_stage_log jika belum ada record
        sync_pr_stage_log_silent($pr_type, $item, $stage, $status, $reason, $doc_meta);

        return [
            'stage'       => $stage,
            'status'      => $status,
            'status_text' => $status_text,
            'doc_meta'    => $doc_meta,
            'reason'      => $reason,
            'metode'      => $metode,
            'method_name' => $method_name,
        ];
    }
}

if (!function_exists('sync_pr_stage_log_silent')) {
    /**
     * Tulis / sinkronkan status ke tb_pr_stage_log secara transparan (tanpa error)
     */
    function sync_pr_stage_log_silent($pr_type, $item, $stage, $status, $reason = '', $doc_ref = '')
    {
        try {
            $CI = &get_instance();
            $no_pgj = $item->no_pengajuan ?? ($item->no_pr ?? ($item->so_number ?? null));
            $no_pr  = $item->no_pr ?? null;

            if (empty($no_pgj)) {
                return;
            }

            // Cek apakah log untuk stage ini sudah pernah dibuat
            $exists = $CI->db->select('id, status')
                             ->get_where('tb_pr_stage_log', [
                                 'pr_type'      => $pr_type,
                                 'no_pengajuan' => $no_pgj,
                                 'stage_no'     => $stage
                             ])->row();

            if (!$exists) {
                $CI->db->insert('tb_pr_stage_log', [
                    'pr_type'      => $pr_type,
                    'no_pengajuan' => $no_pgj,
                    'no_pr'        => $no_pr,
                    'stage_no'     => $stage,
                    'status'       => $status,
                    'actor_name'   => $item->nm_lengkap ?? ($item->pic ?? 'System'),
                    'action_date'  => date('Y-m-d H:i:s'),
                    'remarks'      => !empty($reason) ? $reason : null,
                    'doc_ref'      => !empty($doc_ref) ? $doc_ref : null,
                ]);
            } else if ($exists->status !== $status) {
                // Update jika status berubah (misal active -> reject / done)
                $CI->db->update('tb_pr_stage_log', [
                    'status'      => $status,
                    'action_date' => date('Y-m-d H:i:s'),
                    'remarks'     => !empty($reason) ? $reason : null,
                    'doc_ref'     => !empty($doc_ref) ? $doc_ref : null,
                ], ['id' => $exists->id]);
            }
        } catch (Exception $e) {
            // Silently ignore to guarantee index page rendering is uninterrupted
        }
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/../libraries/Piutang_aging.php';
require_once __DIR__ . '/../libraries/Piutang_export.php';
require_once __DIR__ . '/../libraries/Surat_konfirmasi.php';

/** Monitoring Piutang: card umur, list per invoice, rekap per customer, detail, Excel (MP-FR-01..07, MP-FR-12). */
class Monitoring_piutang extends Admin_Controller
{
    protected $viewPermission = 'Monitoring_Piutang.View';
    const FILTER_KEYS = ['company', 'customer', 'cari', 'kategori', 'tahun', 'bulan', 'dari', 'sampai'];
    const KAT_LABEL = ['all' => 'Semua', 'lancar' => 'Lancar', 'potensi' => 'Potensi Macet', 'macet' => 'Macet'];
    const KAT_AGE = ['lancar' => '0 - 60 hari', 'potensi' => '61 - 90 hari', 'macet' => '> 90 hari'];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Monitoring_piutang/Monitoring_piutang_model');
        $this->template->title('Monitoring Piutang');
        $this->template->page_icon('fa fa-line-chart');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->render('index');
    }

    private function json($data, $code = 200)
    {
        return $this->output->set_content_type('application/json')->set_status_header($code)->set_output(json_encode($data));
    }

    private function filters()
    {
        $f = [];
        foreach (self::FILTER_KEYS as $k) {
            $v = $this->input->get($k, true);
            $f[$k] = $v === null ? '' : trim((string) $v);
        }
        if ($f['kategori'] === '') {
            $f['kategori'] = 'all';
        }
        return $f;
    }

    /** Basis terfilter + hari ini; dipakai layar dan Excel. */
    private function base($f)
    {
        $today = Piutang_aging::today();
        return [Piutang_aging::monitoring_base($this->Monitoring_piutang_model->get_rows(false), $f, $today), $today];
    }

    private function list_rows($base, $kategori, $today)
    {
        return array_map(function ($r) use ($today) {
            $d = Piutang_aging::decorate($r, $today);
            $d['terbayar'] = (float) $r['nilai'] - (float) $r['saldo'];
            $d['can_surat'] = Surat_konfirmasi::can_print($r, $today) === null;
            $d['fu_text'] = !empty($r['plan_bayar']) ? 'Plan bayar ' . Piutang_export::date_dmy($r['plan_bayar'])
                : ($d['fu_next_plan'] ? 'FU ' . Piutang_export::date_dmy($d['fu_next_plan']) : '');
            return $d;
        }, Piutang_aging::aged_list($base, $kategori, $today));
    }

    public function data()
    {
        $this->auth->restrict($this->viewPermission);
        try {
            $f = $this->filters();
            list($base, $today) = $this->base($f);
            $rows = $this->list_rows($base, $f['kategori'], $today);
        } catch (InvalidArgumentException $e) {
            return $this->json(['status' => 'error', 'msg' => $e->getMessage()], 400);
        }
        // Rekap per customer kini lewat rekap_data (server-side); di sini hanya nama customer untuk datalist filter.
        return $this->json(['status' => 'success', 'summary' => Piutang_aging::summary($base, $today),
            'customers' => array_column(Piutang_aging::rekap_customer($base, $today), 'customer'),
            'companies' => Piutang_aging::company_options()]);
    }

    /** Respons DataTables server-side: urut (daftar putih $keys) dan potong halaman dikerjakan di server. */
    private function dt_json(array $rows, array $keys, array $extra = [])
    {
        $dir = $this->input->get('dir', true) === 'desc' ? 'desc' : 'asc';
        $page = Piutang_aging::page_rows($rows, (string) $this->input->get('sort', true), $dir, (int) $this->input->get('start'),
            min((int) $this->input->get('length') ?: 10, 500), $keys);
        return $this->json(['draw' => (int) $this->input->get('draw'), 'recordsTotal' => count($rows), 'recordsFiltered' => count($rows), 'data' => $page] + $extra);
    }

    private function dt_error($msg)
    {
        return $this->json(['draw' => (int) $this->input->get('draw'), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => $msg], 400);
    }

    /** Sumber DataTables server-side tab "List Piutang per Invoice": filter sama dengan data(). */
    public function list_data()
    {
        $this->auth->restrict($this->viewPermission);
        try {
            $f = $this->filters();
            list($base, $today) = $this->base($f);
            $rows = $this->list_rows($base, $f['kategori'], $today);
        } catch (InvalidArgumentException $e) {
            return $this->dt_error($e->getMessage());
        }
        $t = ['nilai' => 0.0, 'terbayar' => 0.0, 'saldo' => 0.0];
        foreach ($rows as $r) {
            foreach ($t as $k => $_) {
                $t[$k] += (float) $r[$k];
            }
        }
        return $this->dt_json($rows, Piutang_aging::PAGE_SORT_KEYS, ['totals' => $t]);
    }

    /** Sumber DataTables server-side tab "Rekap per Customer": filter sama dengan data(); nominal dalam sen. */
    public function rekap_data()
    {
        $this->auth->restrict($this->viewPermission);
        try {
            $f = $this->filters();
            list($base, $today) = $this->base($f);
        } catch (InvalidArgumentException $e) {
            return $this->dt_error($e->getMessage());
        }
        $rk = Piutang_aging::rekap_customer($base, $today, $f['kategori']);
        $t = ['count' => 0, 'lancar' => 0, 'potensi' => 0, 'macet' => 0, 'total' => 0];
        foreach ($rk as $x) {
            foreach ($t as $k => $_) {
                $t[$k] += $x[$k];
            }
        }
        return $this->dt_json($rk, Piutang_aging::REKAP_SORT_KEYS, ['totals' => $t]);
    }

    public function detail($id = '')
    {
        $this->auth->restrict($this->viewPermission);
        $row = $this->Monitoring_piutang_model->get_row($id);
        if (!$row) {
            return $this->json(['status' => 'error', 'msg' => 'Invoice tidak ditemukan.'], 404);
        }
        $today = Piutang_aging::today();
        $d = Piutang_aging::decorate($row, $today);
        $d['can_surat'] = Surat_konfirmasi::can_print($row, $today) === null;
        return $this->json(['status' => 'success', 'row' => $d,
            'fu' => Piutang_aging::fu_list($row), 'receipts' => $this->Monitoring_piutang_model->get_receipts($id)]);
    }

    /** Surat Konfirmasi Status Tagihan (MP-FR-16): hanya Potensi Macet / Macet yang belum close dan masih bersisa; selain itu ditolak. */
    public function surat($id = '')
    {
        $this->auth->restrict($this->viewPermission);
        $row = $this->Monitoring_piutang_model->get_row($id);
        if (!$row) {
            show_error('Invoice tidak ditemukan.', 404);
        }
        $today = Piutang_aging::today();
        $why = Surat_konfirmasi::can_print($row, $today);
        if ($why !== null) {
            show_error(html_escape($why), 422);
        }
        $this->output->set_content_type('text/html', 'UTF-8')
            ->set_output(Surat_konfirmasi::render(Surat_konfirmasi::data($row, $today), base_url('assets/images/surat/')));
    }

    public function export()
    {
        $this->auth->restrict($this->viewPermission);
        try {
            $f = $this->filters();
            list($base, $today) = $this->base($f);
            $rows = $this->list_rows($base, $f['kategori'], $today);
        } catch (InvalidArgumentException $e) {
            show_error(html_escape($e->getMessage()), 400);
        }
        // Satu sheet seperti mockup exportMP: judul, blok ringkasan (Total = rumus L+P+M, Belum diterima, Total keseluruhan = rumus), lalu daftar.
        $sum = Piutang_aging::summary($base, $today);
        $kat = $f['kategori'];
        $by = ['lancar' => [0.0, 0], 'potensi' => [0.0, 0], 'macet' => [0.0, 0]];
        foreach ($rows as $r) {
            $by[$r['kategori']][0] += (float) $r['saldo'];
            $by[$r['kategori']][1]++;
        }
        $judul = 'Monitoring Piutang' . ($kat !== 'all' ? ' - ' . self::KAT_LABEL[$kat] . ' (' . self::KAT_AGE[$kat] . ')' : '') . ' - posisi per ' . date('d-m-Y');
        $belum = $kat === 'all' ? $sum['belum'] : null;
        $pre = function ($ws, $r) use ($by, $belum, $rows) {
            $s = PHPExcel_Cell_DataType::TYPE_STRING;
            $t = $r;
            $ws->setCellValueExplicit("A$t", 'Total Piutang', $s);
            $ws->setCellValue("C$t", '=SUM(C' . ($t + 1) . ':C' . ($t + 3) . ')');
            $ws->setCellValueExplicit("D$t", count($rows) . ' invoice', $s);
            $i = $t + 1;
            foreach ($by as $k => $v) {
                $ws->setCellValueExplicit("A$i", self::KAT_LABEL[$k], $s);
                $ws->setCellValueExplicit("B$i", self::KAT_AGE[$k], $s);
                $ws->setCellValue("C$i", $v[0]);
                $ws->setCellValueExplicit("D$i", $v[1] . ' invoice', $s);
                $i++;
            }
            if ($belum !== null) {
                $ws->setCellValueExplicit("A$i", 'Belum diterima customer', $s);
                $ws->setCellValueExplicit("B$i", 'tidak masuk kategori umur', $s);
                $ws->setCellValue("C$i", $belum['cents'] / 100);
                $ws->setCellValueExplicit("D$i", $belum['count'] . ' invoice', $s);
                $i++;
                $ws->setCellValueExplicit("A$i", 'Total keseluruhan sisa piutang', $s);
                $ws->setCellValueExplicit("B$i", 'Total Piutang + belum diterima', $s);
                $ws->setCellValue("C$i", "=C$t+C" . ($i - 1));
                $ws->setCellValueExplicit("D$i", (count($rows) + $belum['count']) . ' invoice', $s);
                $ws->getStyle("A$i:D$i")->getFont()->setBold(true);
                $i++;
            }
            $ws->getStyle("C$t:C" . ($i - 1))->getNumberFormat()->setFormatCode('#,##0.00');
            $ws->getStyle("A$t:D$t")->getFont()->setBold(true);
            return $i + 1; // satu baris kosong sebelum header daftar
        };
        $rows = array_map(function ($r) {
            $r['fu_berikutnya'] = $r['fu_next_ke'] && $r['fu_next_plan'] ? 'FU ' . $r['fu_next_ke'] . ' - ' . Piutang_export::date_dmy($r['fu_next_plan']) : '-';
            return $r;
        }, $rows);
        $list = ['title' => 'Monitoring Piutang', 'heading' => [$judul], 'pre' => $pre, 'cols' => [['h' => 'No', 'k' => '#', 't' => 'int'], ['h' => 'No Invoice', 'k' => 'no_invoice'],
            ['h' => 'Tanggal Invoice', 'k' => 'tgl_invoice', 't' => 'date'], ['h' => 'Customer', 'k' => 'customer'], ['h' => 'Company', 'k' => 'company_name'], ['h' => 'Project', 'k' => 'project'],
            ['h' => 'Nilai Invoice', 'k' => 'nilai', 't' => 'num'], ['h' => 'Terbayar', 'k' => 'terbayar', 't' => 'num'], ['h' => 'Sisa Piutang', 'k' => 'saldo', 't' => 'num'],
            ['h' => 'Umur (hari)', 'k' => 'umur', 't' => 'int'], ['h' => 'Kategori', 'k' => 'kategori_label'], ['h' => 'Tanggal Terima', 'k' => 'tgl_terima', 't' => 'date'],
            ['h' => 'FU Berikutnya', 'k' => 'fu_berikutnya'], ['h' => 'Plan Bayar', 'k' => 'plan_bayar', 't' => 'date']],
            'rows' => $rows, 'sum' => ['nilai', 'terbayar', 'saldo']];
        Piutang_export::send([$list], 'Monitoring_Piutang_' . ($kat !== 'all' ? str_replace(' ', '_', self::KAT_LABEL[$kat]) . '_' : '') . date('Ymd') . '.xlsx');
    }
}

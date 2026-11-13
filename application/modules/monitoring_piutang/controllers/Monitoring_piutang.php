<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once __DIR__ . '/../libraries/Piutang_aging.php';
require_once __DIR__ . '/../libraries/Piutang_export.php';
require_once __DIR__ . '/../libraries/Surat_konfirmasi.php';

/** Monitoring Piutang: card umur, list per invoice, rekap per customer, detail, Excel (MP-FR-01..07, MP-FR-12). */
class Monitoring_piutang extends Admin_Controller
{
    protected $viewPermission = 'Monitoring_Piutang.View';
    const FILTER_KEYS = ['company', 'customer', 'cari', 'kategori'];
    const KAT_LABEL = ['all' => 'Semua', 'lancar' => 'Lancar', 'potensi' => 'Potensi Macet', 'macet' => 'Macet'];

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
            list($base, $today) = $this->base($this->filters());
        } catch (InvalidArgumentException $e) {
            return $this->dt_error($e->getMessage());
        }
        return $this->dt_json(Piutang_aging::rekap_customer($base, $today), Piutang_aging::REKAP_SORT_KEYS);
    }

    public function detail($id = '')
    {
        $this->auth->restrict($this->viewPermission);
        $row = $this->Monitoring_piutang_model->get_row($id);
        if (!$row) {
            return $this->json(['status' => 'error', 'msg' => 'Invoice tidak ditemukan.'], 404);
        }
        return $this->json(['status' => 'success', 'row' => Piutang_aging::decorate($row, Piutang_aging::today()),
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
        $sum = Piutang_aging::summary($base, $today);
        $kat = $f['kategori'];
        $kats = $kat === 'all' ? ['lancar', 'potensi', 'macet'] : [$kat];
        $sumRows = [];
        foreach ($kats as $k) {
            $sumRows[] = ['kategori' => self::KAT_LABEL[$k], 'count' => $sum[$k]['count'], 'sisa' => $sum[$k]['cents'] / 100];
        }
        $summarySheet = ['title' => 'Ringkasan', 'heading' => ['Monitoring Piutang - ' . self::KAT_LABEL[$kat] . ' - ' . date('d-m-Y')],
            'cols' => [['h' => 'Kategori', 'k' => 'kategori'], ['h' => 'Jumlah Invoice', 'k' => 'count', 't' => 'int'], ['h' => 'Sisa Piutang', 'k' => 'sisa', 't' => 'num']],
            'rows' => $sumRows, 'sum' => ['count', 'sisa']];
        if ($kat === 'all') {
            $belum = ['count' => $sum['belum']['count'], 'sisa' => $sum['belum']['cents'] / 100];
            $summarySheet['after'] = function ($ws, $r) use ($belum) {
                // $r = baris TOTAL (Total Piutang); Belum diterima di bawahnya, lalu total keseluruhan = keduanya (rumus)
                $ws->setCellValueExplicit('A' . ($r + 1), 'Belum diterima customer', PHPExcel_Cell_DataType::TYPE_STRING);
                $ws->setCellValue('B' . ($r + 1), (int) $belum['count']);
                $ws->setCellValue('C' . ($r + 1), (float) $belum['sisa']);
                $ws->setCellValueExplicit('A' . ($r + 2), 'Total keseluruhan sisa piutang', PHPExcel_Cell_DataType::TYPE_STRING);
                $ws->setCellValue('B' . ($r + 2), "=B{$r}+B" . ($r + 1));
                $ws->setCellValue('C' . ($r + 2), "=C{$r}+C" . ($r + 1));
                $ws->getStyle('C' . ($r + 1) . ':C' . ($r + 2))->getNumberFormat()->setFormatCode('#,##0.00');
                $ws->getStyle('A' . ($r + 2) . ':C' . ($r + 2))->getFont()->setBold(true);
            };
        }
        $list = ['title' => 'Daftar Piutang', 'cols' => [['h' => 'No', 'k' => '#', 't' => 'int'], ['h' => 'No Invoice', 'k' => 'no_invoice'],
            ['h' => 'Tgl Invoice', 'k' => 'tgl_invoice', 't' => 'date'], ['h' => 'Tgl Diterima', 'k' => 'tgl_terima', 't' => 'date'],
            ['h' => 'Customer', 'k' => 'customer'], ['h' => 'Project', 'k' => 'project'], ['h' => 'Company', 'k' => 'company_name'],
            ['h' => 'Nilai Invoice', 'k' => 'nilai', 't' => 'num'], ['h' => 'Terbayar', 'k' => 'terbayar', 't' => 'num'], ['h' => 'Sisa Piutang', 'k' => 'saldo', 't' => 'num'],
            ['h' => 'Umur (hari)', 'k' => 'umur', 't' => 'int'], ['h' => 'Kategori', 'k' => 'kategori'], ['h' => 'Follow Up Berikutnya / Plan Bayar', 'k' => 'fu_text']],
            'rows' => $rows, 'sum' => ['nilai', 'terbayar', 'saldo']];
        Piutang_export::send([$summarySheet, $list], 'Monitoring_Piutang_' . self::KAT_LABEL[$kat] . '_' . date('Ymd') . '.xlsx');
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'modules/monitoring_piutang/libraries/Piutang_aging.php';
require_once APPPATH . 'modules/monitoring_piutang/libraries/Piutang_export.php';

/** Dashboard Plan Bayar: rencana bayar per bulan vs realisasi penerimaan (MP-FR-14, MP-FR-15). */
class Plan_bayar extends Admin_Controller
{
    protected $viewPermission = 'Dashboard_Plan_Bayar.View';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Monitoring_piutang/Monitoring_piutang_model');
        $this->template->title('Dashboard Plan Bayar');
        $this->template->page_icon('fa fa-calendar');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->set('years', range((int) date('Y') + 1, (int) date('Y') - 5));
        $this->template->render('plan_bayar');
    }

    private function json($data, $code = 200)
    {
        return $this->output->set_content_type('application/json')->set_status_header($code)->set_output(json_encode($data));
    }

    /** Tahun dan Company divalidasi; dipakai layar dan Excel. */
    private function load_data()
    {
        $year = (string) $this->input->get('tahun', true);
        $company = trim((string) $this->input->get('company', true));
        if ($year === '') {
            $year = date('Y');
        }
        if (!ctype_digit($year) || $year < 2000 || $year > 2100) {
            throw new InvalidArgumentException('Tahun tidak valid.');
        }
        $today = Piutang_aging::today();
        $all = $this->Monitoring_piutang_model->get_rows(true); // termasuk invoice close
        $byId = [];
        foreach ($all as $r) {
            $byId[$r['id']] = $r;
        }
        $plan = Piutang_aging::plan_rows($all, $year, $company, $today);
        $real = Piutang_aging::realisasi_rows($this->Monitoring_piutang_model->get_realisasi($year), $byId, $year, $company, $today);
        return [$plan, $real, Piutang_aging::dashboard_summary($plan, $real, $year, $today), $year];
    }

    public function data()
    {
        $this->auth->restrict($this->viewPermission);
        try {
            list($plan, $real, $summary, $year) = $this->load_data();
        } catch (InvalidArgumentException $e) {
            return $this->json(['status' => 'error', 'msg' => $e->getMessage()], 400);
        }
        // Baris rincian diambil tabel serverside lewat plan_data / real_data; di sini hanya ringkasan kartu.
        return $this->json(['status' => 'success', 'year' => $year, 'summary' => $summary,
            'companies' => Piutang_aging::company_options()]);
    }

    /** Beberapa kata = semua kata harus ada di salah satu kolom (huruf besar/kecil diabaikan); tanggal dicari dalam d-m-Y, d/m/Y, dan Y-m-d. */
    private function matches($row, $keys, $q)
    {
        $parts = [];
        foreach ($keys as $k) {
            $v = isset($row[$k]) ? (string) $row[$k] : '';
            $parts[] = $v;
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $v)) {
                $t = strtotime(substr($v, 0, 10));
                $parts[] = date('d-m-Y', $t);
                $parts[] = date('d/m/Y', $t);
            }
        }
        $hay = mb_strtolower(implode(' ', $parts));
        foreach (preg_split('/\s+/', mb_strtolower($q), -1, PREG_SPLIT_NO_EMPTY) as $w) {
            if (mb_strpos($hay, $w) === false) {
                return false;
            }
        }
        return true;
    }

    /** Filter kata cari yang sama untuk tabel di layar dan export Excel. */
    private function search_rows($rows, $isPlan, $q)
    {
        if ($q === '') {
            return $rows;
        }
        $keys = $isPlan ? ['no_invoice', 'tgl_invoice', 'customer', 'project', 'company_name', 'ket_plan_bayar', 'plan_bayar', 'posisi']
            : ['no_invoice', 'tgl_invoice', 'customer', 'project', 'company_name', 'no_penerimaan', 'plan_bayar', 'tgl_bayar', 'ketepatan', 'posisi'];
        $self = $this;
        return array_values(array_filter($rows, function ($r) use ($self, $keys, $q) {
            return $self->matches($r, $keys, $q);
        }));
    }

    /** Endpoint DataTables serverside (draw/start/length/search[value]) untuk tabel Plan dan Realisasi; urutan tetap dari server. */
    private function table_page($which)
    {
        $this->auth->restrict($this->viewPermission);
        $draw = (int) $this->input->get('draw');
        try {
            list($plan, $real) = $this->load_data();
        } catch (InvalidArgumentException $e) {
            return $this->json(['draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => $e->getMessage()]);
        }
        $isPlan = $which === 'plan';
        $rows = Piutang_aging::by_month($isPlan ? $plan : $real, (int) $this->input->get('bulan'));
        $total = count($rows);
        $search = $this->input->get('search');
        $q = is_array($search) && isset($search['value']) ? trim((string) $search['value']) : '';
        $rows = $this->search_rows($rows, $isPlan, $q);
        // Total mengikuti hasil filter (bulan + cari), bukan hanya halaman yang tampil.
        $sum = function ($k) use ($rows) {
            return round(array_sum(array_map('floatval', array_column($rows, $k))), 2);
        };
        if ($isPlan) {
            $agg = ['nilai' => $sum('nilai'), 'nilai_plan' => $sum('nilai_plan'), 'saldo' => $sum('saldo')];
        } else {
            $tepat = 0;
            $telat = 0;
            foreach ($rows as $r) {
                if ($r['ketepatan_days'] !== null) {
                    $r['ketepatan_days'] <= 0 ? $tepat++ : $telat++;
                }
            }
            $agg = ['nominal' => $sum('nominal'), 'tepat' => $tepat, 'telat' => $telat];
        }
        $start = max(0, (int) $this->input->get('start'));
        $length = (int) $this->input->get('length');
        $length = $length < 1 ? 25 : min($length, 500);
        return $this->json(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => count($rows), 'data' => array_slice($rows, $start, $length), 'agg' => $agg]);
    }

    public function plan_data()
    {
        return $this->table_page('plan');
    }

    public function real_data()
    {
        return $this->table_page('real');
    }

    public function export()
    {
        $this->auth->restrict($this->viewPermission);
        try {
            list($plan, $real, $summary, $year) = $this->load_data();
        } catch (InvalidArgumentException $e) {
            show_error(html_escape($e->getMessage()), 400);
        }
        $bulan = (int) $this->input->get('bulan');
        $month = $bulan >= 1 && $bulan <= 12;
        $plan = Piutang_aging::by_month($plan, $bulan);
        $real = Piutang_aging::by_month($real, $bulan);
        $plan = $this->search_rows($plan, true, trim((string) $this->input->get('cari_plan')));
        $real = $this->search_rows($real, false, trim((string) $this->input->get('cari_real')));
        $nm =Piutang_aging::BULAN;
        $plan = array_map(function ($r) use ($nm) {
            $r['bulan_nama'] = $nm[$r['bulan']];
            return $r;
        }, $plan);
        $real = array_map(function ($r) use ($nm) {
            $r['bulan_nama'] = $nm[$r['bulan']];
            return $r;
        }, $real);
        $title = $month ? $nm[$bulan] . ' ' . $year : 'Tahun ' . $year;

        $rincian = ['title' => 'Rincian', 'cols' => [['h' => 'No', 'k' => '#', 't' => 'int'], ['h' => 'Bulan', 'k' => 'bulan_nama'], ['h' => 'Tgl Plan Bayar', 'k' => 'plan_bayar', 't' => 'date'],
            ['h' => 'Ket Plan Bayar', 'k' => 'ket_plan_bayar'], ['h' => 'No Invoice', 'k' => 'no_invoice'], ['h' => 'Tgl Invoice', 'k' => 'tgl_invoice', 't' => 'date'],
            ['h' => 'Customer', 'k' => 'customer'], ['h' => 'Project', 'k' => 'project'], ['h' => 'Company', 'k' => 'company_name'], ['h' => 'Nilai Invoice', 'k' => 'nilai', 't' => 'num'],
            ['h' => 'Nilai Plan', 'k' => 'nilai_plan', 't' => 'num'], ['h' => 'Sisa Piutang Sekarang', 'k' => 'saldo', 't' => 'num'], ['h' => 'Umur', 'k' => 'umur', 't' => 'int'],
            ['h' => 'Kategori', 'k' => 'kategori'], ['h' => 'Status Invoice', 'k' => 'posisi']], 'rows' => $plan, 'sum' => ['nilai', 'nilai_plan', 'saldo']];
        $realisasi = ['title' => 'Realisasi', 'cols' => [['h' => 'No', 'k' => '#', 't' => 'int'], ['h' => 'Bulan Bayar', 'k' => 'bulan_nama'], ['h' => 'No Invoice', 'k' => 'no_invoice'],
            ['h' => 'Tgl Invoice', 'k' => 'tgl_invoice', 't' => 'date'], ['h' => 'Customer', 'k' => 'customer'], ['h' => 'Project', 'k' => 'project'], ['h' => 'Company', 'k' => 'company_name'],
            ['h' => 'Janji Bayar (Plan)', 'k' => 'plan_bayar', 't' => 'date'], ['h' => 'Tanggal Bayar', 'k' => 'tgl_bayar', 't' => 'date'], ['h' => 'Ketepatan', 'k' => 'ketepatan'],
            ['h' => 'Jumlah Dibayar', 'k' => 'nominal', 't' => 'num'], ['h' => 'No Penerimaan', 'k' => 'no_penerimaan'], ['h' => 'Sisa Tagihan Sekarang', 'k' => 'saldo', 't' => 'num'],
            ['h' => 'Nilai Invoice', 'k' => 'nilai', 't' => 'num'], ['h' => 'Status Invoice', 'k' => 'posisi']], 'rows' => $real, 'sum' => ['nominal', 'saldo', 'nilai']];
        $sheets = [$rincian, $realisasi];

        if (!$month) {
            array_unshift($sheets, Piutang_export::plan_summary_sheet($summary, count($plan), count($real), $title));
        }
        Piutang_export::send($sheets, 'Dashboard_Plan_Bayar_' . ($month ? $nm[$bulan] . '_' : '') . $year . '_' . date('Ymd') . '.xlsx');
    }
}

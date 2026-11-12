<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'modules/monitoring_piutang/libraries/Piutang_aging.php';
require_once APPPATH . 'modules/monitoring_piutang/libraries/Piutang_export.php';

/** Menu kerja finance AR: kirim, terima, follow up, plan bayar, close (MP-FR-08..11, MP-FR-13). */
class Follow_up_piutang extends Admin_Controller
{
    protected $viewPermission = 'Follow_Up_Piutang.View';
    protected $managePermission = 'Follow_Up_Piutang.Manage';
    const PER_PAGE = 100;
    const FILTER_KEYS = ['tab', 'company', 'customer', 'cari', 'fu', 'plan_bulan', 'plan_tahun', 'close_tahun', 'inv_tahun', 'inv_bulan'];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Monitoring_piutang/Monitoring_piutang_model');
        $this->template->title('Follow Up Piutang');
        $this->template->page_icon('fa fa-phone');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->set('can_manage', has_permission($this->managePermission));
        $this->template->set('years', range((int) date('Y') + 1, (int) date('Y') - 5));
        $mulaiTahun = (int) substr(Piutang_aging::TANGGAL_MULAI_FOLLOW_UP, 0, 4);
        $this->template->set('inv_years', range(max((int) date('Y'), $mulaiTahun), $mulaiTahun));
        $this->template->set('inv_mulai_tahun', $mulaiTahun);
        $this->template->set('inv_mulai_bulan', (int) substr(Piutang_aging::TANGGAL_MULAI_FOLLOW_UP, 5, 2));
        $this->template->render('index');
    }

    private function golive()
    {
        $this->config->load('piutang', true, true);
        return (string) $this->config->item('tanggal_mulai_follow_up', 'piutang');
    }

    private function filters()
    {
        $f = [];
        foreach (self::FILTER_KEYS as $k) {
            $v = $this->input->get($k, true);
            $f[$k] = $v === null ? '' : trim((string) $v);
        }
        // kotak cari bawaan DataTables (search[value]) = filter "cari"; ?cari= tetap dipakai oleh export
        $dt = $this->input->get('search');
        if ($f['cari'] === '' && is_array($dt) && isset($dt['value'])) {
            $f['cari'] = trim((string) $dt['value']);
        }
        if ($f['tab'] === '') {
            $f['tab'] = 'all';
        }
        if ($f['close_tahun'] === '') {
            $f['close_tahun'] = date('Y');
        }
        return $f;
    }

    private function json($data, $code = 200)
    {
        return $this->output->set_content_type('application/json')->set_status_header($code)->set_output(json_encode($data));
    }

    /** Baris terfilter dan terdekorasi; dipakai layar dan Excel (aturan sama). */
    private function filtered($f, &$counts = null, &$total = null)
    {
        $today = Piutang_aging::today();
        $golive = $this->golive();
        $all = array_values(array_filter($this->Monitoring_piutang_model->get_rows((int) $f['close_tahun']), function ($r) {
            $tgl = substr((string) ($r['tgl_invoice'] ?? ''), 0, 10);
            return Piutang_aging::is_date($tgl) && $tgl >= Piutang_aging::TANGGAL_MULAI_FOLLOW_UP;
        }));
        $counts = Piutang_aging::tab_counts($all, $f, $today, $golive);
        // recordsTotal DataTables = isi tab tanpa filter dan pencarian
        $total = count(Piutang_aging::filter_ar_rows($all, ['tab' => $f['tab'], 'close_tahun' => $f['close_tahun']], $today, $golive));
        $rows = Piutang_aging::filter_ar_rows($all, $f, $today, $golive);
        $rows = array_map(function ($r) use ($today) {
            return Piutang_aging::decorate($r, $today);
        }, Piutang_aging::sort_baru($rows, $today));
        return $rows;
    }

    /**
     * Endpoint DataTables serverside (draw/start/length/search[value]). Filter dan urutan dihitung oleh Piutang_aging
     * (sama dengan Excel); di sini hanya dipotong per halaman. Urutan tetap: label "Baru" paling atas, bukan urutan kolom.
     */
    public function data()
    {
        $this->auth->restrict($this->viewPermission);
        $draw = (int) $this->input->get('draw');
        try {
            $f = $this->filters();
            $rows = $this->filtered($f, $counts, $recordsTotal);
        } catch (InvalidArgumentException $e) {
            return $this->json(['draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'counts' => (object) [], 'error' => $e->getMessage()]);
        }
        $start = max(0, (int) $this->input->get('start'));
        $length = (int) $this->input->get('length');
        $length = $length < 1 ? self::PER_PAGE : min($length, 500);
        $manage = has_permission($this->managePermission);
        $slice = array_slice($rows, $start, $length);
        foreach ($slice as &$r) {
            $r['can_update'] = $manage && !in_array($r['status'], ['plan_bayar', 'close'], true);
            $r['can_close'] = $manage && $r['status'] === 'plan_bayar' && $r['close_block'] === null;
        }
        unset($r);
        return $this->json(['draw' => $draw, 'recordsTotal' => $recordsTotal, 'recordsFiltered' => count($rows), 'data' => array_values($slice),
            'counts' => $counts, 'companies' => Piutang_aging::company_options(),
            'customers' => array_values(array_unique(array_filter(array_column($rows, 'customer'), 'strlen')))]);
    }

    /** Data form Update: baris + status kunci per step + usulan jadwal FU. */
    public function form($id = '')
    {
        $this->auth->restrict($this->viewPermission);
        $row = $this->Monitoring_piutang_model->get_row($id);
        if (!$row) {
            return $this->json(['status' => 'error', 'msg' => 'Invoice tidak ditemukan.'], 404);
        }
        $today = Piutang_aging::today();
        $d = Piutang_aging::decorate($row, $today);
        $fu = Piutang_aging::fu_list($row);
        $done = [];
        foreach ($fu as $x) {
            $done[$x['ke']] = !empty($x['aktual']);
        }
        $locks = ['all' => in_array($d['status'], ['plan_bayar', 'close'], true), 'kirim' => !empty($row['tgl_terima']), 'terima' => !empty($row['tgl_terima']),
            'resi' => !empty($row['no_resi']) && !empty($row['tgl_terima']), 'fu' => []];
        foreach ($fu as $x) {
            $locks['fu'][$x['ke']] = !empty($done[$x['ke']]); // FU terkunci begitu tanggal dilakukan tersimpan
        }
        $suggest = [];
        $nextKe = $d['fu_stage'] === 'selesai' ? count($fu) + 1 : null;
        foreach ($fu as $x) {
            $suggest[$x['ke']] = Piutang_aging::default_fu_plan($row, $x['ke']);
        }
        return $this->json(['status' => 'success', 'row' => $d, 'fu' => $fu, 'locks' => $locks, 'suggest' => $suggest, 'next_extra_ke' => $nextKe,
            'can_add_extra' => $d['status'] === 'diterima' && $this->all_done($fu),
            'today' => $today]);
    }

    private function all_done($fu)
    {
        foreach ($fu as $x) {
            if (empty($x['aktual'])) {
                return false;
            }
        }
        return true;
    }

    public function save()
    {
        $this->auth->restrict($this->managePermission);
        $id = (string) $this->input->post('id', true);
        $input = $this->input->post(null, true) ?: [];
        if ($id === '') {
            return $this->json(['status' => 'error', 'msg' => 'Invoice tidak ditemukan.'], 400);
        }
        // Path bukti tidak pernah diterima dari browser; hanya dari hasil upload di bawah.
        unset($input['bukti_terima'], $input['bukti_terima_nama'], $input['bukti_baru']);
        $uploaded = $this->upload_bukti_semua($err);
        if ($uploaded === null) {
            return $this->json(['status' => 'error', 'msg' => $err], 422);
        }
        $input['bukti_baru'] = $uploaded;
        $res = $this->Monitoring_piutang_model->save_ar($id, $input, (string) $this->auth->user_id());
        // File baru yang tidak jadi dipakai (simpan gagal, atau step terkunci) dibersihkan; file lama tidak pernah dihapus.
        $dipakai = $res['ok'] ? ($res['saved']['bukti'] ?? []) : [];
        foreach ($uploaded as $u) {
            if (!in_array($u[0], $dipakai, true)) {
                @unlink(FCPATH . $u[0]);
            }
        }
        return $this->json($res['ok'] ? ['status' => 'success', 'msg' => 'Data follow up tersimpan.'] : ['status' => 'error', 'msg' => implode("\n", $res['errors'])], $res['ok'] ? 200 : 422);
    }

    const BUKTI_DIR = 'uploads/bukti_terima_invoice/';

    const BUKTI_MAKS_FILE = 10;

    /**
     * Simpan semua file bukti terima (field bukti_terima[]). Mengembalikan daftar [path relatif, nama asli] (kosong bila tidak ada file)
     * atau null dengan pesan aman (tanpa path server) di $err. Satu file gagal membatalkan semuanya: yang sudah terunggah dihapus.
     */
    private function upload_bukti_semua(&$err)
    {
        $names = $_FILES['bukti_terima']['name'] ?? [];
        $names = is_array($names) ? $names : [$names];
        $idx = [];
        foreach ($names as $i => $n) {
            if ((string) $n !== '') {
                $idx[] = $i;
            }
        }
        if (!$idx) {
            return [];
        }
        if (count($idx) > self::BUKTI_MAKS_FILE) {
            $err = 'Maksimal ' . self::BUKTI_MAKS_FILE . ' file bukti terima per simpan.';
            return null;
        }
        $dir = FCPATH . self::BUKTI_DIR;
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            $err = 'Folder penyimpanan bukti tidak dapat dibuat. Hubungi administrator.';
            return null;
        }
        $src = $_FILES['bukti_terima'];
        $out = [];
        foreach ($idx as $k => $i) {
            // CI Upload membaca satu entri $_FILES per file: pecah field array jadi bukti_terima_0, _1, ...
            $key = 'bukti_terima_' . $k;
            $_FILES[$key] = is_array($src['name'])
                ? ['name' => $src['name'][$i], 'type' => $src['type'][$i], 'tmp_name' => $src['tmp_name'][$i], 'error' => $src['error'][$i], 'size' => $src['size'][$i]]
                : $src;
            $this->upload->initialize(['upload_path' => $dir, 'allowed_types' => 'pdf|jpg|jpeg|png', 'max_size' => 5120, 'encrypt_name' => true, 'remove_spaces' => true]);
            if (!$this->upload->do_upload($key)) {
                $err = 'Bukti terima "' . mb_substr(basename((string) $_FILES[$key]['name']), 0, 80) . '" gagal diunggah: ' . trim(strip_tags($this->upload->display_errors('', ' ')));
                foreach ($out as $u) {
                    @unlink(FCPATH . $u[0]);
                }
                return null;
            }
            $data = $this->upload->data();
            $out[] = [self::BUKTI_DIR . $data['file_name'], mb_substr(basename((string) $_FILES[$key]['name']), 0, 255)];
        }
        return $out;
    }

    public function close()
    {
        $this->auth->restrict($this->managePermission);
        $res = $this->Monitoring_piutang_model->close_invoice((string) $this->input->post('id', true), (string) $this->input->post('reason', true), (string) $this->auth->user_id());
        return $this->json($res['ok'] ? ['status' => 'success', 'msg' => 'Invoice berhasil di-close.'] : ['status' => 'error', 'msg' => implode("\n", $res['errors'])], $res['ok'] ? 200 : 422);
    }

    public function history($id = '')
    {
        $this->auth->restrict($this->viewPermission);
        return $this->json(['status' => 'success', 'rows' => $this->Monitoring_piutang_model->get_log($id)]);
    }

    public function export()
    {
        $this->auth->restrict($this->viewPermission);
        try {
            $f = $this->filters();
            $rows = $this->filtered($f);
        } catch (InvalidArgumentException $e) {
            show_error(html_escape($e->getMessage()), 400);
        }
        $cols = [['h' => 'No', 'k' => '#', 't' => 'int'], ['h' => 'No Invoice', 'k' => 'no_invoice'], ['h' => 'Tgl Invoice', 'k' => 'tgl_invoice', 't' => 'date'],
            ['h' => 'Customer', 'k' => 'customer'], ['h' => 'Company', 'k' => 'company_name'], ['h' => 'Project', 'k' => 'project'],
            ['h' => 'Nilai Invoice', 'k' => 'nilai', 't' => 'num'], ['h' => 'Sisa Piutang', 'k' => 'saldo', 't' => 'num'],
            ['h' => 'Tgl Kirim', 'k' => 'tgl_kirim', 't' => 'date'], ['h' => 'Dikirim Via', 'k' => 'kirim_via'], ['h' => 'Ket Kirim', 'k' => 'ket_kirim'],
            ['h' => 'No Resi', 'k' => 'no_resi'], ['h' => 'Tgl Terima', 'k' => 'tgl_terima', 't' => 'date'], ['h' => 'Ket Terima', 'k' => 'ket_terima'],
            ['h' => 'PIC Penerima FU', 'k' => 'fu_pic_nama'], ['h' => 'No WA / Telp PIC', 'k' => 'fu_pic_telp'],
            ['h' => 'Umur', 'k' => 'umur', 't' => 'int'], ['h' => 'Kategori', 'k' => 'kategori']];
        for ($i = 1; $i <= 4; $i++) {
            $cols[] = ['h' => "FU $i Jadwal", 'k' => "fu{$i}_plan", 't' => 'date'];
            $cols[] = ['h' => "FU $i Dilakukan", 'k' => "fu{$i}_aktual", 't' => 'date'];
            $cols[] = ['h' => "FU $i Keterangan", 'k' => "fu{$i}_ket"];
        }
        $cols[] = ['h' => 'FU Tambahan', 'k' => 'fu_extra_text'];
        $cols[] = ['h' => 'Plan Bayar', 'k' => 'plan_bayar', 't' => 'date'];
        $cols[] = ['h' => 'Ket Plan Bayar', 'k' => 'ket_plan_bayar'];
        $cols[] = ['h' => 'Status', 'k' => 'status_label'];
        if ($f['tab'] === 'close') {
            $cols[] = ['h' => 'Tgl Close', 'k' => 'close_date', 't' => 'date'];
            $cols[] = ['h' => 'Ket Close', 'k' => 'close_reason'];
        }
        $rows = array_map(function ($r) {
            $r['umur'] = $r['umur'] === null ? '' : $r['umur'];
            return $r;
        }, $rows);
        Piutang_export::send([['title' => 'Follow Up Piutang', 'heading' => ['Follow Up Piutang - tab ' . $f['tab'] . ' - ' . date('d-m-Y')],
            'cols' => $cols, 'rows' => $rows, 'sum' => ['nilai', 'saldo']]], 'Follow_Up_Piutang_' . $f['tab'] . '_' . date('Ymd') . '.xlsx');
    }
}

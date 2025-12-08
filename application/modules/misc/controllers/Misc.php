<?php
class Misc extends Admin_Controller
{

    protected $accounting;
    protected $consultant;

    public function __construct()
    {
        parent::__construct();

        $this->accounting = $this->load->database('accounting', true);
        $this->consultant = $this->load->database('consultant', true);
    }

    public function buat_ulang_jurnal_invoicing()
    {
        // $arr_id_invoicing = ['00021-INV-VIII-25', '00022-INV-VIII-25', '00023-INV-VIII-25', '00024-INV-VIII-25', '00025-INV-VIII-25', '00026-INV-VIII-25', '00027-INV-VIII-25', '00028-INV-VIII-25', '00029-INV-VIII-25', '00030-INV-VIII-25', '00031-INV-VIII-25', '00032-INV-VIII-25', '00033-INV-VIII-25', '00034-INV-VIII-25', '00035-INV-VIII-25', '00036-INV-VIII-25', '00037-INV-VIII-25', '00039-INV-VIII-25', '00040-INV-VIII-25', '00041-INV-VIII-25', '00042-INV-VIII-25', '00043-INV-VIII-25', '00044-INV-VIII-25', '00045-INV-VIII-25', '00046-INV-VIII-25', '00047-INV-VIII-25', '00048-INV-VIII-25', '00049-INV-VIII-25', '00050-INV-VIII-25', '00051-INV-VIII-25', '00052-INV-VIII-25', '00053-INV-VIII-25', '00054-INV-VIII-25', '00055-INV-VIII-25', '00056-INV-VIII-25', '00057-INV-VIII-25', '00058-INV-VIII-25', '00059-INV-IX-25', '00060-INV-IX-25', '00061-INV-IX-25', '00062-INV-IX-25', '00063-INV-IX-25', '00064-INV-IX-25', '00065-INV-IX-25', '00066-INV-IX-25', '00067-INV-IX-25', '00068-INV-IX-25', '00069-INV-IX-25', '00070-INV-IX-25', '00071-INV-IX-25', '00038-INV-VIII-25', '00074-INV-IX-25', '00075-INV-IX-25', '00072-INV-IX-25', '00073-INV-IX-25', '00076-INV-IX-25', '00077-INV-X-25', '00078-INV-X-25', '00079-INV-X-25', '00080-INV-X-25', '00081-INV-X-25', '00082-INV-X-25', '00083-INV-X-25', '00084-INV-X-25', '00085-INV-X-25', '00086-INV-X-25', '00087-INV-X-25', '00088-INV-X-25', '00089-INV-X-25', '00090-INV-X-25', '00091-INV-X-25', '00092-INV-X-25', '00093-INV-X-25', '00094-INV-X-25', '00095-INV-X-25', '00096-INV-X-25', '00097-INV-X-25', '00098-INV-X-25', '00099-INV-X-25', '0,100-INV-XI-25', '00101-INV-XI-25', '00102-INV-XI-25', '00103-INV-XI-25', '00104-INV-XI-25', '00105-INV-XI-25', '00106-INV-XI-25', '00107-INV-XI-25', '00108-INV-XI-25', '00109-INV-XI-25', '00110-INV-XI-25', '00111-INV-XI-25', '00112-INV-XI-25', '00113-INV-XI-25', '00114-INV-XI-25', '00115-INV-XI-25', '00116-INV-XI-25', '00117-INV-XI-25', '00118-INV-XI-25', '00119-INV-XI-25', '00120-INV-XI-25', '00121-INV-XI-25', '00124-INV-XI-25', '00125-INV-XI-25', '00126-INV-XI-25', '00127-INV-XI-25', '00128-INV-XI-25', '00129-INV-XI-25', '00130-INV-XI-25', '00123-INV-XI-25', '00131-INV-XI-25', '00132-INV-XI-25', '00133-INV-XI-25', '00134-INV-XI-25', '00135-INV-XI-25', '00136-INV-XI-25', '00122-INV-XI-25', '00137-INV-XI-25', '00138-INV-XII-25', '00139-INV-XII-25', '00140-INV-XII-25'];

        $this->db->trans_begin();

        $this->db->select('a.*');
        $this->db->from('tr_invoicing a');
        // $this->db->where_in('a.id', $arr_id_invoicing);
        $get_invoicing = $this->db->get()->result_array();

        $arr_rejurnal = [];

        foreach ($get_invoicing as $item) {

            $this->consultant->select('a.id, a.nm_company');
            $this->consultant->from('kons_tr_company a');
            $this->consultant->join('kons_tr_penawaran b', 'b.company = a.id');
            $this->consultant->where('b.id_quotation', $item['id_penawaran']);
            $get_company = $this->consultant->get()->row();

            $id_company = (!empty($get_company->id)) ? $get_company->id : '';
            $nm_company = (!empty($get_company->nm_company)) ? $get_company->nm_company : '';

            $arr_coa = ['1102-01-01', '1106-01-02', '2104-01-07', '4101-01-01'];

            $this->accounting->select('a.no_perkiraan as no_coa, a.nama as nm_coa');
            $this->accounting->from('coa_master a');
            $this->accounting->where_in('a.no_perkiraan', $arr_coa);
            $get_coa = $this->accounting->get()->result_array();

            foreach ($get_coa as $item_coa) {
                $debit = 0;
                $kredit = 0;
                if ($item_coa['no_coa'] == '1102-01-01') {
                    $debit = $item['total_akhir_jurnal'];
                }
                if ($item_coa['no_coa'] == '1106-01-02') {
                    $debit = $item['pph_jurnal'];
                }
                if ($item_coa['no_coa'] == '2104-01-07') {
                    $kredit = $item['ppn_jurnal'];
                }
                if ($item_coa['no_coa'] == '4101-01-01') {
                    $kredit = $item['total_nominal_jurnal'];
                }

                $arr_rejurnal[] = [
                    'tgl_jurnal' => $item['tanggal_invoice'],
                    'coa' => $item_coa['no_coa'],
                    'id_company' => $id_company,
                    'nm_company' => $nm_company,
                    'nm_coa' => $item_coa['nm_coa'],
                    'debit' => $debit,
                    'kredit' => $kredit,
                    'keterangan' => $item['no_invoice'] . ' - ' . $item['nm_customer'],
                    'sts' => '0',
                    'no_transaksi' => $item['id'],
                    'jenis_transaksi' => 'Invoicing',
                    'created_by' => $this->auth->user_id(),
                    'created_date' => date('Y-m-d H:i:s')
                ];
            }
        }

        if (!empty($arr_rejurnal)) {
            // $this->db->trans_begin();

            try {
                $insert_rejurnal = $this->db->insert_batch('tr_jurnal', $arr_rejurnal);

                // print_r($this->db->last_query());
                // exit;

                $this->db->trans_commit();

                echo 'Berhasil !';
            } catch (Exception $e) {
                $this->db->trans_rollback();

                echo $e->getMessage();
            }
        }
    }
}

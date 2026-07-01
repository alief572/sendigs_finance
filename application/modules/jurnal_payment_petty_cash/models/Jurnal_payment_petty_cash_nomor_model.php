<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Jurnal Payment Petty Cash Nomor Model
 *
 * Handles BUK number generation dan increment counter
 * dari pastibisa_tb_cabang di masing-masing database akuntansi.
 *
 * BUK Number Format: {nocab}BK{subcab}{yy}{sequence}
 * Example: 101BKA2500001
 *
 * @author  Sendigs Dev Team
 */

class Jurnal_payment_petty_cash_nomor_model extends CI_Model
{
    protected $accounting_stm;
    protected $accounting_vuca;
    protected $accounting_sustain;

    public function __construct()
    {
        parent::__construct();

        $this->accounting_stm     = $this->load->database('accounting_stm', true);
        $this->accounting_vuca    = $this->load->database('accounting_vuca', true);
        $this->accounting_sustain = $this->load->database('accounting_sustain', true);
    }

    /**
     * Generate BUK number dari pastibisa_tb_cabang
     *
     * Format: {nocab}BK{subcab}{yy}{sequence}
     * - nocab   = branch code (e.g., '101')
     * - 'BK'    = literal prefix for Buku Kas
     * - subcab  = from pastibisa_tb_cabang.subcab (e.g., 'A')
     * - yy      = 2-digit year
     * - sequence = nobuk + 1, zero-padded to 5 digits
     *
     * @param string $cabang Branch code (e.g., '101')
     * @param string $db_name Database connection key ('accounting_stm', 'accounting_vuca', 'accounting_sustain')
     * @return string|null Generated BUK number or null if not found
     */
    public function get_nomor_buk($cabang, $db_name)
    {
        $db = $this->get_db_connection($db_name);
        if (!$db) {
            return null;
        }

        $query = $db->get_where('pastibisa_tb_cabang', array('nocab' => $cabang));

        if ($query->num_rows() > 0) {
            $row = $query->row();
            $nocab    = $row->nocab;
            $subcab   = $row->subcab;
            $sequence = intval($row->nobuk) + 1;
            $yy       = date('y');

            $nomor_buk = $nocab . 'BK' . $subcab . $yy . str_pad($sequence, 5, '0', STR_PAD_LEFT);

            return $nomor_buk;
        }

        return null;
    }

    /**
     * Increment nobuk counter in pastibisa_tb_cabang
     *
     * @param string $cabang Branch code (e.g., '101')
     * @param string $db_name Database connection key ('accounting_stm', 'accounting_vuca', 'accounting_sustain')
     * @return bool
     */
    public function increment_nobuk($cabang, $db_name)
    {
        $db = $this->get_db_connection($db_name);
        if (!$db) {
            return false;
        }

        $db->set('nobuk', 'nobuk + 1', false);
        $db->where('nocab', $cabang);
        $result = $db->update('pastibisa_tb_cabang');

        return $result;
    }

    /**
     * Get the appropriate database connection based on db_name key
     *
     * @param string $db_name Connection key
     * @return object|null CI_DB instance
     */
    protected function get_db_connection($db_name)
    {
        switch ($db_name) {
            case 'accounting_stm':
                return $this->accounting_stm;
            case 'accounting_vuca':
                return $this->accounting_vuca;
            case 'accounting_sustain':
                return $this->accounting_sustain;
            default:
                return null;
        }
    }
}

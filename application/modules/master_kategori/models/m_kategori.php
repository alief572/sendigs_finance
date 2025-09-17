<?php
class M_kategori extends CI_Model 
{
    /**
     * @copyright dando ridwanto <dando.ridwanto@ymail.com>
     * @since 2018/03/11
     * QUERY FOR PENAWARAN
     */
    function fetch_data_kategori($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $sql = "
            SELECT 
                (@row:=@row+1) AS nomor, 
                id_kategori_paket,
                kategori_paket 
            FROM 
                kons_kategori_paket, 
                (SELECT @row := 0) r 
            WHERE 1=1 
        ";
        $data['totalData'] = $this->db->query($sql)->num_rows();
        if( ! empty($like_value))
        {
            $sql .= " AND (
                kategori_paket LIKE '%".$this->db->escape_like_str($like_value)."%' 
            ";
            $sql .= " ) ";
        }
        
        $data['totalFiltered']  = $this->db->query($sql)->num_rows();
        $columns_order_by = array( 
            0 => 'nomor',
            1 => 'kategori_paket' 
        );
        $sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir.", nomor ";
        $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";
        
        $data['query'] = $this->db->query($sql);
        return $data;
    }
}
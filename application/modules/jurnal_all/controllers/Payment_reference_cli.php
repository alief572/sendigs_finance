<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(__DIR__).'/libraries/Payment_patch.php';
require_once dirname(__DIR__).'/libraries/Payment_normalization.php';

class Payment_reference_cli extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (PHP_SAPI!=='cli') show_404();
        $this->load->database();
        $this->db->db_debug=false;
    }

    public function index($mode='dry-run', $path=null)
    {
        $this->execute($mode,$path,false);
    }

    public function normalize($mode='dry-run', $path=null)
    {
        $this->execute($mode,$path,true);
    }

    private function execute($mode,$path,$normalization)
    {
        try {
            if (!in_array($mode,$normalization?['dry-run','apply','rollback','verify']:['dry-run','apply','rollback'],true)) throw new DomainException('Mode CLI tidak valid.');
            if (!$path || !is_string($path) || strtolower(pathinfo($path,PATHINFO_EXTENSION))!=='json') throw new DomainException('Sertakan path manifest .json di luar document root.');
            $parent=realpath(dirname($path));
            $web=realpath(FCPATH);
            if (!$parent || !$web || !is_writable($parent)) throw new DomainException('Folder manifest tidak tersedia.');
            $normalized=strtolower(str_replace('\\','/',$parent)).'/';
            $root=strtolower(str_replace('\\','/',$web)).'/';
            if (strpos($normalized,$root)===0) throw new DomainException('Manifest harus berada di luar document root.');
            $path=$parent.DIRECTORY_SEPARATOR.basename($path);
            if (is_link($path)) throw new DomainException('Manifest symlink tidak diizinkan.');
            $patch=$normalization?new Payment_normalization($this):new Payment_patch($this);
            if ($normalization && $mode==='verify') {
                Payment_reference::acquire($this->db);
                try { $verified=$patch->verify($path); }
                finally { Payment_reference::release($this->db); }
                echo json_encode(['status'=>1,'verification'=>$verified],JSON_PRETTY_PRINT).PHP_EOL;return;
            } elseif ($normalization && $mode==='dry-run') {
                $result=$patch->prepare($path);
            } elseif ($mode==='dry-run') {
                if (file_exists($path)) throw new DomainException('Manifest sudah ada; gunakan nama baru.');
                Payment_reference::acquire($this->db);
                try { $result=$patch->inventory(); Payment_patch::save($path,$result); }
                finally { Payment_reference::release($this->db); }
            } else {
                if (!is_file($path)) throw new DomainException('Manifest tidak ditemukan.');
                $result=$patch->run($path,$mode==='rollback');
            }
            $counts=[];
            foreach ($result['items'] as $item) $counts[$item['status']]=($counts[$item['status']] ?? 0)+1;
            echo json_encode(['status'=>1,'counts'=>$counts],JSON_PRETTY_PRINT).PHP_EOL;
        } catch (Throwable $error) {
            fwrite(STDERR,'Payment reference: '.$error->getMessage().PHP_EOL);
            exit(1);
        }
    }
}

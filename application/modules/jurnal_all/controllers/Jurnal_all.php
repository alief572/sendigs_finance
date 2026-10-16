<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Jurnal_all extends Admin_Controller
{
    protected $posted=false;

    public function __construct()
    {
        parent::__construct();
        $this->db->db_debug=false;
        $this->load->model('jurnal_all/Jurnal_all_model');
        $this->load->library('Module_audit_recorder');
    }

    public function index()
    {
        $this->endpoint(__FUNCTION__,function(){
            $this->template->title($this->posted?'Report Jurnal':'Jurnal');
            $this->template->page_icon('fa fa-book');
            $tab=$this->input->get('tab');
            $this->template->set(['posted'=>$this->posted,'tabs'=>Jurnal_all_model::TITLES,
                'initialTab'=>is_string($tab)&&isset(Jurnal_all_model::TITLES[$tab])?$tab:'INV',
                'canManage'=>!$this->posted&&has_permission('Jurnal_All.Manage')]);
            $this->template->render('jurnal_all/workspace');
            return ['outcome'=>'rendered'];
        },false);
    }

    public function get_data_jurnal()
    {
        $this->endpoint(__FUNCTION__,function(){return $this->Jurnal_all_model->listing($this->values(),$this->posted);});
    }

    public function get_filters()
    {
        $this->endpoint(__FUNCTION__,function(){return $this->Jurnal_all_model->options($this->values(),$this->posted);});
    }

    public function get_summary()
    {
        $this->endpoint(__FUNCTION__,function(){
            if ($this->posted) throw new InvalidArgumentException('Ringkasan pending hanya tersedia pada Jurnal.');
            $filters=$this->input->post('filters');
            return $this->Jurnal_all_model->summary(is_array($filters)?$filters:[]);
        });
    }

    public function preview()
    {
        $this->endpoint(__FUNCTION__,function(){
            $values=$this->values();
            $detail=$this->Jurnal_all_model->detail($values['id'] ?? 0,$this->posted);
            if (!$this->posted && $detail['header']['source']!==($values['source'] ?? null)) throw new InvalidArgumentException('Sumber jurnal tidak valid.');
            $canManage=!$this->posted&&has_permission('Jurnal_All.Manage');
            if (!$this->posted&&$detail['header']['source']==='EXP'&&!$canManage) throw new RuntimeException('Anda tidak memiliki akses preview jurnal ini.',403);
            $this->load->library('jurnal_all/Jurnal_all_dispatcher');
            $revisable=isset(Jurnal_all_dispatcher::ROUTES[$detail['header']['source']]['revise']);
            $blocked=false;
            if (!$this->posted&&$detail['header']['source']==='PCS') {
                $this->load->model('jurnal_payment_petty_cash/Jurnal_payment_petty_cash_model');
                $blocked=(bool)$this->Jurnal_payment_petty_cash_model->find_unclear_coa($detail['rows']);
                $nativeRows=$this->Jurnal_payment_petty_cash_model->get_detail_by_transaksi($detail['header']['no_transaksi'],$detail['header']['jenis_transaksi']);
                $detail['rows']=array_map(function($row){return (array)$row;},$nativeRows);
            }
            if ($detail['header']['source']==='PCS'&&$detail['header']['jenis_transaksi']==='Petty Cash') $detail['company_sections']=$this->Jurnal_all_model->company_sections($detail['rows']);
            $html=$this->load->view('jurnal_all/preview',['detail'=>$detail,'posted'=>$this->posted],true);
            return ['status'=>1,'html'=>$html,'can_post'=>$canManage&&$detail['is_balance']&&!$blocked,
                'can_revise'=>$canManage&&$revisable,'blocked_coa'=>$blocked];
        });
    }

    public function view_jurnal() { $this->preview(); }
    public function save_posting_jurnal() { $this->writeAction('post',__FUNCTION__); }
    public function revisi_jurnal() { $this->writeAction('revise',__FUNCTION__); }

    public function legacy($tab='INV')
    {
        $tab=isset(Jurnal_all_model::TITLES[$tab])?$tab:'INV';
        $this->module_audit_recorder->begin($this->posted?'report_jurnal_all':'jurnal_all',__FUNCTION__,[],['outcome']);
        try { $this->module_audit_recorder->complete('Halaman jurnal dialihkan',1,['data'=>['outcome'=>'redirected'],'sql'=>null]); }
        catch (Throwable $error) { log_message('error','Jurnal gabungan: audit redirect tidak tersedia.'); }
        redirect(($this->posted?'report_jurnal_all':'jurnal_all').'?tab='.$tab);
    }

    public function export_excel()
    {
        $this->endpoint(__FUNCTION__,function(){
            $rows=$this->Jurnal_all_model->export_rows($this->values(),$this->posted);
            $name=($this->posted?'Report_Jurnal':'Jurnal_Pending').'_'.date('Ymd_His').'.xls';
            $this->output->set_header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            $this->output->set_header('Content-Disposition: attachment; filename="'.$name.'"');
            $this->output->set_header('Cache-Control: no-store');
            $this->load->view('jurnal_all/export_excel',['rows'=>$rows,'posted'=>$this->posted]);
            return ['row_count'=>count($rows),'outcome'=>'exported'];
        },false);
    }

    protected function writeAction($action,$method)
    {
        $this->endpoint($method,function()use($action){
            if ($this->posted||$this->input->method(true)!=='POST'||!has_permission('Jurnal_All.Manage')) throw new RuntimeException('Anda tidak memiliki akses untuk aksi ini.',403);
            $this->load->library('jurnal_all/Jurnal_all_dispatcher');
            $values=$this->values();
            return $this->jurnal_all_dispatcher->execute($action,$values['id'] ?? 0,$values['source'] ?? null,$values);
        });
    }

    protected function values()
    {
        $values=$this->input->method(true)==='GET'?$this->input->get():$this->input->post();
        return is_array($values)?$values:[];
    }

    protected function endpoint($method,$callback,$json=true)
    {
        $module=$this->posted?'report_jurnal_all':'jurnal_all';
        $this->module_audit_recorder->begin($module,$method,[],['row_count','reason','outcome']);
        $ok=false; $audit=['reason'=>'request_failed'];
        try {
            if (!has_permission($this->posted?'Report_Jurnal_All.View':'Jurnal_All.View')) throw new RuntimeException('Anda tidak memiliki hak akses.',403);
            $response=$callback();
            $ok=!isset($response['status'])||(string)$response['status']==='1';
            $audit=isset($response['outcome'])?$response:['row_count'=>isset($response['data'])?count($response['data']):0,$ok?'outcome':'reason'=>$ok?'completed':'action_failed'];
            if ($json) $this->output->set_content_type('application/json')->set_output(json_encode($response));
        } catch (Throwable $error) {
            $code=$error->getCode()===403?403:($error instanceof InvalidArgumentException?400:500);
            $this->output->set_status_header($code);
            $message=$error instanceof InvalidArgumentException||$code===403 ? $error->getMessage() : 'Proses jurnal gagal. Periksa status sebelum mencoba kembali.';
            $draw=$this->values()['draw'] ?? 0;
            $response=['status'=>0,'msg'=>$message,'error'=>$message,'draw'=>is_scalar($draw)?(int)$draw:0,'data'=>[],'recordsTotal'=>0,'recordsFiltered'=>0];
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            $audit=['reason'=>$code===403?'permission_denied':($code===400?'invalid_request':'operation_failed')];
            if ($code===500) log_message('error',$module.': operation failed at '.$method);
        } finally {
            try { $this->module_audit_recorder->complete($ok?'Jurnal gabungan berhasil':'Jurnal gabungan gagal',$ok?1:0,['data'=>$audit,'sql'=>null]); }
            catch (Throwable $error) { log_message('error','Jurnal gabungan: audit writer tidak tersedia.'); }
        }
    }
}

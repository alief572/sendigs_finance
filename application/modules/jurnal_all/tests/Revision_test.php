<?php
define('BASEPATH',true);class BF_Model{}
require dirname(__DIR__,2).'/jurnal_invoicing/models/Jurnal_invoicing_model.php';
require dirname(__DIR__,2).'/jurnal_penerimaan/models/Jurnal_penerimaan_model.php';
$checks=0;function check($ok,$msg){global $checks;$checks++;if(!$ok)throw new RuntimeException($msg);}
class RevisionResult {public function row(){return (object)['no_transaksi'=>'DOC1','jenis_transaksi'=>'Invoicing'];}}
class RevisionDb {
    public $failure='',$writes=[],$events=[];
    public function trans_begin(){return true;}
    public function get_where($table,$where){return new RevisionResult();}
    public function update($table,$values,$where){$this->writes[]=[$table,$values,$where];return $this->failure!=='update';}
    public function trans_status(){return $this->failure!=='transaction';}
    public function trans_commit(){$this->events[]='commit';return $this->failure!=='commit';}
    public function trans_rollback(){$this->events[]='rollback';}
    public function error(){return ['message'=>'fixture failure'];}
}
class RevisionInput {public function post(){return ['id'=>1,'alasan_revisi'=>'Periksa dokumen'];}}
foreach(['Jurnal_invoicing_model','Jurnal_penerimaan_model'] as $class)foreach(['','update','transaction','commit'] as $failure){
    $model=(new ReflectionClass($class))->newInstanceWithoutConstructor();$model->db=new RevisionDb();$model->db->failure=$failure;$model->input=new RevisionInput();
    ob_start();$model->update_sts_revisi_jurnal();$response=json_decode(ob_get_clean(),true);
    check(is_array($response)&&$response['status']===($failure===''?1:0),'native revision outcome '.$class.'/'.$failure);
    check($model->db->writes[0][1]['sts']==='9'&&$model->db->writes[0][1]['alasan_revisi']==='Periksa dokumen','original revision payload preserved');
    check(end($model->db->events)===($failure===''?'commit':'rollback'),'revision failure cannot report committed success');
}
echo "PASS native revision handlers: {$checks} assertions\n";

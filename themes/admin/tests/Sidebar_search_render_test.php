<?php
define('FCPATH',dirname(__DIR__,3).'/');
function base_url($path=''){return '/'.$path;}
function site_url($path=''){return '/'.$path;}
$checks=0;
function verifySidebar($condition,$message){global $checks;$checks++;if(!$condition)throw new RuntimeException($message);}
class SidebarRender {
    public $uri,$menu_generator;
    function render($view,$module,$allowed){
        $this->uri=new class($module){private $module;function __construct($module){$this->module=$module;}function segment($n){return $n===1?$this->module:'index';}};
        $this->menu_generator=new class($allowed){private $allowed;function __construct($allowed){$this->allowed=$allowed;}function build_menus(){return '<ul class="sidebar-menu"><li><a href="/">Dashboard</a></li>'.($this->allowed?'<li class="treeview"><a href="#">Finance</a><ul class="treeview-menu"><li><a href="/invoicing">Invoicing</a></li></ul></li>':'').'</ul>';}};
        $idt=(object)['nm_perusahaan'=>'SENDIGS UJI'];$userData=(object)['username'=>'test-user'];$template=['title'=>$module];
        ob_start();include dirname(__DIR__).'/views/'.$view.'.php';return ob_get_clean();
    }
}
$renderer=new SidebarRender;
foreach(['invoicing','dashboard'] as $module)foreach([true,false]as $allowed){
    $header=$renderer->render('header',$module,$allowed);$footer=$renderer->render('footer',$module,$allowed);
    $dom=new DOMDocument;@$dom->loadHTML('<?xml encoding="UTF-8">'.$header.$footer);$query=new DOMXPath($dom);
    verifySidebar($query->query('//*[@id="sidebar-search"]')->length===1,'One search across modules');
    verifySidebar($query->query('//*[@id="sidebar-menu-search"]')->length===1,'One search input');
    verifySidebar($query->query('//label[@for="sidebar-menu-search"]')->length===1,'Input labelled');
    verifySidebar($query->query('//*[@id="sidebar-menu-search"]')->item(0)->getAttribute('aria-controls')==='sidebar-navigation-menus','Search controls menu region');
    verifySidebar($query->query('//*[@id="sidebar-menu-search"]')->item(0)->getAttribute('aria-describedby')==='sidebar-search-help' && $query->query('//*[@id="sidebar-search-help"]')->length===1,'Input guidance does not announce hidden no-results state');
    verifySidebar($query->query('//*[@id="sidebar-search"]/@hidden')->length===1,'Progressive enhancement hides until initialized');
    verifySidebar($query->query('//*[@id="sidebar-search-clear"]/@hidden')->length===1,'Clear initially hidden');
    verifySidebar($query->query('//*[@id="sidebar-search-empty" and @role="status" and @aria-live="polite"]')->length===1,'Accessible empty feedback');
    verifySidebar($query->query('//*[@id="sidebar-search-mini-btn" and @type="button" and @aria-label]')->length===1,'Mini button accessible');
    verifySidebar(strpos($header,'id="sidebar-menu-search"')<strpos($header,'>Dashboard</a>'),'Search above Dashboard');
    verifySidebar($query->query('//*[@id="sidebar-navigation-menus"]//a[@href="/invoicing"]')->length===($allowed?1:0),'Search markup preserves permission-filtered output');
    verifySidebar($query->query('//link[contains(@href,"sidebar-search.css?v=")]')->length===1,'Local stylesheet cache-busted');
    verifySidebar($query->query('//script[contains(@src,"sidebar-search.js?v=")]')->length===1,'Local script cache-busted');
    verifySidebar(strpos($footer,'assets/adminlte/dist/js/app.min.js')<strpos($footer,'assets/js/sidebar-search.js'),'Search loads after AdminLTE');
    verifySidebar(strpos($header.$footer,'executive-navigation.css')===false && strpos($header.$footer,'navigation-shell.js')===false,'No unrelated shell redesign enabled');
}
define('BASEPATH',FCPATH.'system/');
function check_class($route,$exact=false){return '';}
require FCPATH.'application/libraries/Menu_generator.php';
class DashboardMenuRender extends Menu_generator {
    public function __construct(){
        $this->user_id=1;
        $this->ci=(object)['db'=>new class {
            public function __call($name,$arguments){return $this;}
            public function result(){return [];}
        }];
    }
    protected function get_auth_permission($user_id=0){return [];}
}
$menu=(new DashboardMenuRender())->build_menus();
$dom=new DOMDocument;@$dom->loadHTML($menu);$query=new DOMXPath($dom);
verifySidebar($query->query('//i[contains(@class,"fa-dashboard")]')->length===1,'Dashboard has exactly one icon');
verifySidebar($query->query('//a[@href="/"]/i[contains(@class,"fa-dashboard")]')->length===1,'Dashboard icon belongs to its clickable link');
verifySidebar($query->query('//li/i')->length===0,'No standalone icon above the dashboard link');
echo 'Sidebar search render PASS: '.$checks." checks\n";

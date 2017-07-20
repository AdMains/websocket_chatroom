<?php
use ZPHP\ZPHP;
define('TPL_PATH', ZPHP::getRootPath() . DS . 'template'. DS);
define('STATIC_URL', '/static/');
$config =  array(
        'server_mode' =>'Http',
        'app_path'=>'apps',
        'ctrl_path'=>'ctrl',
        'project'=>array(
        	'view_mode'=>'Php',
        	'ctrl_name'=>'a',				
        	'method_name'=>'m',
            'default_ctrl_name'=>'main\\main',
            'default_method_name'=>'main',
            'static_url' => STATIC_URL,
            'tpl_path'=> TPL_PATH,
            'app_host'=> $_SERVER['HTTP_HOST'],
        ),
    );
$publicConfig = array('route.php','pdo.php','cache.php');
foreach($publicConfig as $file) {
    $file = ZPHP::getProjectPath() . DS . 'common' . DS . 'config'. DS . $file;
    $config += include "{$file}";
}

return $config;

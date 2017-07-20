<?php
use ZPHP\ZPHP;
$rootPath = dirname(__DIR__);
require dirname($rootPath).DIRECTORY_SEPARATOR.'vendor'. DIRECTORY_SEPARATOR . 'autoload.php';
ZPHP::run($rootPath);
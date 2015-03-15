<?php
header('Content-Type: text/html; charset=utf-8');
define('MAX_PHP_TIME', 3600);
ini_set("max_execution_time", MAX_PHP_TIME);
set_time_limit(MAX_PHP_TIME);

require_once($_CFG['_FILE']['cron']);

if (!isset($_CFG['cron']) or !count($_CFG['cron'])) {
    exit('no cron');
}

$time = time();
$res_cron = '';
$i = 1;
if (!isset($_SERVER['HTTP_HOST']) or !$_SERVER['HTTP_HOST'])
    $_SERVER['HTTP_HOST2'] = $_SERVER['HTTP_HOST'] = $_CFG['site']['www'];
$_SERVER['SERVER_PORT'] = 80;
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/index.html';
$_SERVER['HTTP_USER_AGENT'] = $_CFG['site']['www'];
$_SERVER['IS_CRON'] = true;
$_SERVER['IS_CONSOLE'] = true;


$modelName = $_SERVER['argv'][2];
if (!$modelName || !_new_class($modelName, $MODEL)) {
    echo 'Model `' . $modelName . '` cant find ' . PHP_EOL;
    return false;
}

$funct = $_SERVER['argv'][3];
if (!$funct || !is_callable([$MODEL, $funct])) {
    echo 'Function `' . $funct . '` in model `' . $modelName . '` cant find ' . PHP_EOL;
    return false;
}
$param = array_slice($_SERVER['argv'], 4);

//var_dump($modelName, $funct, $param);exit();

echo call_user_func_array([$MODEL, $funct], $param) . PHP_EOL;
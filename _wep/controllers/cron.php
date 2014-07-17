<?php
header('Content-Type: text/html; charset=utf-8');
ini_set("max_execution_time", "1200");
set_time_limit(1200);

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

$pidFile = $_CFG['_PATH']['weptemp'].'cron.pid';
$lastTimeRun = '';
if (file_exists($pidFile))
    $lastTimeRun = file_get_contents($pidFile);

if ($lastTimeRun) {
    $lastTimeRun = explode(PHP_EOL, $lastTimeRun);

    if ($lastTimeRun[0]< (time() - 1800)) {
        trigger_error('Завис крон или процесс сломался '.$lastTimeRun[1], E_USER_WARNING);
    }
    else {
        echo '**wait**';
        return ;
    }
}


foreach ($_CFG['cron'] as $key_cron => $r_cron) {
    $dataJson = getCronData();
	$result = '';
	if (isset($dataJson[$key_cron]['last_time']) && ($dataJson[$key_cron]['last_time'] + $r_cron['time']) > $time) {
		//$res_cron .= 'Рано импортировать файл '. $dataJson[$key_cron]['file']. ', последний раз он импортировался '.date('d.m.Y H:i', $dataJson[$key_cron]['last_time']). ', сейчас ' . date('d.m.Y H:i', $time) . '. (Установленный интервал: '.$dataJson['int' . $key_cron].' минут, осталось ' . round((($dataJson['last_time' . $key_cron] + ($dataJson['int' . $key_cron] * 60) - $time) / 60), 1) . ' минут)' . "\n";
	}
	elseif (!isset($r_cron['active']) or $r_cron['active']) {
        file_put_contents($pidFile, time().PHP_EOL.$key_cron);
		$tt = getmicrotime();
		//'time' => '600', 'file' => '_wepconf/ext/exportboard.class/exportboard.cron.php', 'modul' => '', 'function' => ''
		if (isset($r_cron['file']) and $r_cron['file']) {
			$r_cron['file'] = SITE . $r_cron['file'];
			if (file_exists($r_cron['file'])) {
				$result = include($r_cron['file']);
			}
			else
				$result = 'Can`t find file ' . $r_cron['file'] . ' . //';
		}
		if (isset($r_cron['function']) and $r_cron['function'] and $r_cron['modul']) {
			_new_class($r_cron['modul'], $MODUL);
			eval('$result = $MODUL->' . $r_cron['function'] . ';');
			//function_exists
		}
		elseif (isset($r_cron['function']) and $r_cron['function']) {
			eval('$result = ' . $r_cron['function'] . ';');
		}

		$dataJson[$key_cron]['last_time'] = $time;
		$dataJson[$key_cron]['do_time'] = getmicrotime() - $tt;
		$dataJson[$key_cron]['res' ] = '* ' . str_replace(array("\n", "\r"), array('<br/>', ''), addslashes((string)$result)) . '';
		$res_cron .= $result;
	}

    setCronData($dataJson);
}

file_put_contents($pidFile, '++');

//_chmod($ini_file);

function getCronData() {
    global $_CFG;
    $ini_file = $_CFG['_FILE']['cronTask'];
    if (file_exists($ini_file)) {
        $dataJson = file_get_contents($ini_file);
        $dataJson = json_decode($dataJson, true);
        if (!$dataJson) $dataJson = array();
    }
    else
        $dataJson = array();
    return $dataJson;
}

function setCronData($dataJson) {
    global $_CFG;
    $ini_file = $_CFG['_FILE']['cronTask'];
    file_put_contents($ini_file, json_encode($dataJson));
}

echo $res_cron;

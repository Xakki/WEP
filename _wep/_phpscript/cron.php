<?php
	header('Content-Type: text/html; charset=utf-8');

	if(!isset($_CFG['_PATH']['wep'])) exit('Ошибка конфигурации.');
	require_once($_CFG['_PATH']['wep'].'/config/config.php');
	require_once($_CFG['_FILE']['cron']);
	
	if(!isset($_CFG['cron']) or !count($_CFG['cron'])) {
		exit();
	}

	$ini_file = $_CFG['_PATH']['config'].'cron.ini';
	if(file_exists($ini_file)) 
		$ini_arr = parse_ini_file($ini_file);
	else
		$ini_arr= array();

	$time = time();
	$res_cron = '';
	$i = 1;
	if(!isset($_SERVER['HTTP_HOST']) or !$_SERVER['HTTP_HOST'])
		$_SERVER['HTTP_HOST2'] = $_SERVER['HTTP_HOST'] = $_CFG['site']['www'];
	$_SERVER['SERVER_PORT'] = 80;
	$_SERVER['REQUEST_URI'] = '/index.html';
	$_SERVER['HTTP_USER_AGENT'] = $_CFG['site']['www'];

	foreach($_CFG['cron'] as $key_cron=>$r_cron) {
		$result = '';
		if (isset($ini_arr['last_time' . $key_cron]) && ($ini_arr['last_time' . $key_cron] + $r_cron['time']) > $time) {
			//$res_cron .= 'Рано импортировать файл '. $ini_arr['file'.$key_cron]. ', последний раз он импортировался '.date('d.m.Y H:i', $ini_arr['last_time'.$key_cron]). ', сейчас ' . date('d.m.Y H:i', $time) . '. (Установленный интервал: '.$ini_arr['int' . $key_cron].' минут, осталось ' . round((($ini_arr['last_time' . $key_cron] + ($ini_arr['int' . $key_cron] * 60) - $time) / 60), 1) . ' минут)' . "\n";
		}
		elseif(!isset($r_cron['active']) or $r_cron['active']) {
			$tt  = getmicrotime();
			//'time' => '600', 'file' => '_wepconf/ext/exportboard.class/exportboard.cron.php', 'modul' => '', 'function' => ''
			if(isset($r_cron['file']) and $r_cron['file']) {
				$r_cron['file'] = $_CFG['_PATH']['path'].$r_cron['file'];
				if(file_exists($r_cron['file'])) {
					$result = include($r_cron['file']);
				}else
					$result = 'Can`t find file '.$r_cron['file'].' . //';
			}
			if(isset($r_cron['function']) and $r_cron['function'] and $r_cron['modul']) {
				_new_class($r_cron['modul'],$MODUL);
				eval('$result = $MODUL->'.$r_cron['function'].';');
				//function_exists				
			}elseif(isset($r_cron['function']) and $r_cron['function']) {
				eval('$result = '.$r_cron['function'].';');
			}

			$ini_arr['last_time' . $key_cron] = $time;
			$ini_arr['do_time' . $key_cron] = getmicrotime()-$tt;
			$ini_arr['res' . $key_cron] = '"*'.str_replace(array("\n","\r"),'',addslashes((string)$result)).'"';
			$res_cron .= $result;
		}
	}

	$conf = '';
	foreach ($ini_arr as $k=>$v) {
		$conf .= $k . " = " . $v . "\n";
	}
	umask(0774);
	file_put_contents($ini_file, $conf);
	@chmod($ini_file, 0774);


	echo $res_cron;

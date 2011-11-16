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
	$_SERVER['HTTP_HOST2'] = $_SERVER['HTTP_HOST'] = $_CFG['site']['www'];

	foreach($_CFG['cron'] as $key_cron=>$r_cron) {
		if (isset($ini_arr['last_time' . $key_cron]) && ($ini_arr['last_time' . $key_cron] + $r_cron['time']) > $time) {
			//$res_cron .= 'Рано импортировать файл '. $ini_arr['file'.$key_cron]. ', последний раз он импортировался '.date('d.m.Y H:i', $ini_arr['last_time'.$key_cron]). ', сейчас ' . date('d.m.Y H:i', $time) . '. (Установленный интервал: '.$ini_arr['int' . $key_cron].' минут, осталось ' . round((($ini_arr['last_time' . $key_cron] + ($ini_arr['int' . $key_cron] * 60) - $time) / 60), 1) . ' минут)' . "\n";
		}
		elseif(!isset($r_cron['active']) or $r_cron['active']) {
			$ini_arr['last_time' . $key_cron] = $time;
			$tt  = getmicrotime();
			//'time' => '600', 'file' => '_wepconf/ext/exportboard.class/exportboard.cron.php', 'modul' => '', 'function' => ''
			if($r_cron['file']) {
				$r_cron['file'] = $_CFG['_PATH']['path'].$r_cron['file'];
				if(file_exists($r_cron['file'])) {
					$res_cron .= include($r_cron['file']);
				}else
					$res_cron .= 'Can`t find file '.$r_cron['file'].' . //';
			}
			if(isset($r_cron['function']) and $r_cron['function'] and $r_cron['modul']) {
				_new_class($r_cron['modul'],$MODUL);
				eval('$res_cron .= $MODUL->'.$r_cron['function'].';');
				//function_exists				
			}elseif(isset($r_cron['function']) and $r_cron['function']) {
				eval('$res_cron .= '.$r_cron['function'].';');
			}
			$ini_arr['do_time' . $key_cron] = getmicrotime()-$tt;
		}
	}

	$conf = '';
	foreach ($ini_arr as $k=>$v) {
		$conf .= $k . " = " . $v . "\n";
	}
	umask(0777);
	file_put_contents($ini_file, $conf);
	chmod($ini_file, 0777);


	echo $res_cron;

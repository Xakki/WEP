<?
	header('Content-Type: text/html; charset=utf-8');

	if(!isset($_CFG['_PATH']['wep'])) exit('Ошибка конфигурации.');
	require_once($_CFG['_PATH']['wep'].'/config/config.php');
	
	if(!isset($_CFG['wep']['cron']) or !count($_CFG['wep']['cron'])) {
		exit();
	}

	$ini_file = $_CFG['_PATH']['weptemp'].'cron.ini';
	if(file_exists($ini_file)) 
		$ini_arr = parse_ini_file($ini_file);
	else
		$ini_arr= array();

	$time = time();
	$res = '';
	$i = 1;

	foreach($_CFG['wep']['cron'] as $i=>$r) {
		if (isset($ini_arr['last_time' . $i]) && ($ini_arr['last_time' . $i] + $r['time']) > $time) {
			//$res .= 'Рано импортировать файл '. $ini_arr['file'.$i]. ', последний раз он импортировался '.date('d.m.Y H:i', $ini_arr['last_time'.$i]). ', сейчас ' . date('d.m.Y H:i', $time) . '. (Установленный интервал: '.$ini_arr['int' . $i].' минут, осталось ' . round((($ini_arr['last_time' . $i] + ($ini_arr['int' . $i] * 60) - $time) / 60), 1) . ' минут)' . "\n";
		}
		elseif(!isset($r['active']) or $r['active']) {
			$ini_arr['last_time' . $i] = $time;
			$tt  = getmicrotime();
			//'time' => '600', 'file' => '_wepconf/ext/exportboard.class/exportboard.cron.php', 'modul' => '', 'function' => ''
			if($r['file']) {
				$r['file'] = $_CFG['_PATH']['path'].$r['file'];
				if(file_exists($r['file'])) {
					$res .= include($r['file']);
				}else
					$res .= 'Can`t find file '.$r['file'].' . //';
			}
			if(isset($r['function']) and $r['function'] and $r['modul']) {
				_new_class($r['modul'],$MODUL);
				eval('$res .= $MODUL->'.$r['function'].';');
				//function_exists				
			}elseif(isset($r['function']) and $r['function']) {
				eval('$res .= '.$r['function'].';');
			}
			$ini_arr['do_time' . $i] = getmicrotime()-$tt;
		}
	}

	$conf = '';
	foreach ($ini_arr as $k=>$v) {
		$conf .= $k . " = " . $v . "\n";
	}
	umask(0777);
	file_put_contents($ini_file, $conf);
	chmod($ini_file, 0777);


	echo $res;

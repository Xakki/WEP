<?php
function tools_step1() {
	global $_CFG,$HTML,$_tpl;
	$TEMP_CFG= array();
	$_tpl['styles']['install']=1;
	$file = $_CFG['_PATH']['wep_phpscript'] . '/install/step1.php';
	if(static_main::_prmUserCheck(1))
		return require($file);
	else
		return static_main::m('denied');
}

function tools_step2() {
	global $_CFG,$HTML,$_tpl;
	if(!static_main::_prmUserCheck(1))
		return static_main::m('denied');
	$_tpl['styles']['install']=1;
	$file = $_CFG['_PATH']['wep_phpscript'] . '/install/step2.php';
	return require($file);
}

function tools_step3() {
	global $_CFG,$HTML,$_tpl;
	if(!static_main::_prmUserCheck(1))
		return static_main::m('denied');
	$_tpl['styles']['install']=1;
	$file = $_CFG['_PATH']['wep_phpscript'] . '/install/step3.php';
	return require($file);
}

function tools_updater() {
	$href = 'http://xakki.ru/_json.php?_modul=gitwep&_fn=sourceinfo';
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

function tools_docron() {
	global $_CFG;
	$ttw  = getmicrotime();
	include($_CFG['_PATH']['wep_phpscript'].'/cron.php');
	return '--Крон выполнен, время обработки задач =  '.(getmicrotime()-$ttw).'mc -----';;
}

function tools_cron() {
	global $_CFG,$_tpl;
	if(!static_main::_prmUserCheck(1))
		return static_main::m('denied');
	$result = '';

	$ini_file = $_CFG['_PATH']['weptemp'].'cron.ini';
	if(file_exists($ini_file)) 
		$ini_arr = parse_ini_file($ini_file);
	else
		$ini_arr= array();

	$FP = $_CFG['PATH']['wepname'].'/index.php?_view=list&_modul=_tools&tfunc=tools_cron&';
	$_tpl['styles']['form']=1;
	$DATA = array();
	$DATA['path'] = array(
		$FP=>'Задания'
	);

	$DATA['topmenu']['add'] = array(
		'href' => '_type=add',
		'caption' => 'Добавить задание',
		'sel' => 0,
		'type' => '',
		'css' => 'add',
	);
	if ($_GET['_type'] == 'add' or ($_GET['_type'] == 'edit' and isset($_GET['_id']))) {

		$FORM = array('_*features*_' => array('method' => 'POST', 'name' => 'cron'));

		if (isset($_POST['sbmt'])) {
			if(isset($_GET['_id']))
				$p = (int)$_GET['_id'];
			elseif(!isset($_CFG['wep']['cron']))
				$p = 0;
			else {
				ksort($_CFG['wep']['cron']);
				end($_CFG['wep']['cron']);
				$p = key($_CFG['wep']['cron'])+1;
			}
			$NEWDATA = array();
			$NEWDATA['wep']['cron'] = $_CFG['wep']['cron'];
			$NEWDATA['wep']['cron'][$p] = array(
				'time'=>$_POST['time'],
				'file'=>$_POST['file'],
				'modul'=>$_POST['modul'],
				'function'=>$_POST['function'],
				'active'=>($_POST['active']?1:0),
			);
			list($fl,$mess) = static_tools::saveUserCFG($NEWDATA);
			if(!$fl)
				$FORM['info'] = array('type'=>'info', 'caption'=>'<h3 style="color:red;">Ошибка</h3>');
			else {
				if($_POST['last_time']) {
					$ini_arr['last_time'.$p] = mktime($_POST['last_time'][3],$_POST['last_time'][4],$_POST['last_time'][5],$_POST['last_time'][1],$_POST['last_time'][2],$_POST['last_time'][0]);
					$conf = '';
					foreach ($ini_arr as $k=>$v) {
						$conf .= $k . " = " . $v . "\n";
					}
					umask(0777);
					file_put_contents($ini_file, $conf);
					chmod($ini_file, 0777);
				}
				$_SESSION['messtool'] = array('name'=>'ok','value'=>'Задание успешно добавлено.');
				static_main::redirect('/'.key($DATA['path']));
			}
		}
		$DATA['path'][$FP.'_type=add'] = 'Добавить';
		if($_GET['_type'] == 'edit' and isset($_GET['_id']) and isset($_CFG['wep']['cron'][$_GET['_id']])) {
			$VAL = $_CFG['wep']['cron'][$_GET['_id']];
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
			'css' => '',
			'style' => '',
			'value'=>$VAL['last_time'],
		);
		$FORM['sbmt'] = array(
			'type' => 'submit',
			'value' => 'Сохранить');

		$FORM['formcreat'] = array('form' => $FORM);
		//$FORM['formcreat']['messages'] = $mess;
		global $HTML;
		$result = $HTML->transformPHP($DATA, 'path');
		$result .= $HTML->transformPHP($FORM, 'formcreat');
	}
	elseif($_GET['_type'] == 'del' and isset($_GET['_id'])) {
		$NEWDATA = array();
		$NEWDATA['wep']['cron'] = $_CFG['wep']['cron'];
		unset($NEWDATA['wep']['cron'][$_GET['_id']]);
		list($fl,$mess) = static_tools::saveUserCFG($NEWDATA);
		if(!$fl)
			$_SESSION['messtool'] = array('name'=>'error','value'=>'Ошибка.');
		else
			$_SESSION['messtool'] = array('name'=>'ok','value'=>'Задание успешно Удалено.');
		static_main::redirect('/'.key($DATA['path']));
	} 
	elseif(isset($_GET['_id']) and ($_GET['_type'] == 'act' or $_GET['_type'] == 'dis')) {
		$act = ($_GET['_type'] == 'act'?1:0);
		$NEWDATA = array();
		$NEWDATA['wep']['cron'] = $_CFG['wep']['cron'];
		$NEWDATA['wep']['cron'][$_GET['_id']]['active'] = $act;
		list($fl,$mess) = static_tools::saveUserCFG($NEWDATA);
		if(!$fl)
			$_SESSION['messtool'] = array('name'=>'error','value'=>'Ошибка.');
		else
			$_SESSION['messtool'] = array('name'=>'ok','value'=>'Задание успешно '.($act?'включено':'отключено').'.');
		static_main::redirect('/'.key($DATA['path']));
	}
	else {
		$DATA['data'] = array(
			'thitem'=>array(
				'time'=>array('value'=>'Период'),
				'file'=>array('value'=>'Фаил'),
				'modul'=>array('value'=>'Модуль'),
				'function'=>array('value'=>'Функция'),
				'lasttime'=>array('value'=>'Время прошлого выполнения'),
				'do_time'=>array('value'=>'Время выполнения задачи в мс.')
			),
		);
		if(isset($_CFG['wep']['cron']) and count($_CFG['wep']['cron'])) {
			foreach($_CFG['wep']['cron'] as $k=>$r) {
				$DATA['data']['item'][$k]['tditem'] = array(
					'time'=>array('value'=>$r['time']),
					'file'=>array('value'=>$r['file']),
					'modul'=>array('value'=>$r['modul']),
					'function'=>array('value'=>$r['function']),
					'lasttime'=>array('value'=>date('Y-m-d H:i:s',$ini_arr['last_time'.$k])),
					'do_time'=>array('value'=>$ini_arr['do_time'.$k]),
				);
				$DATA['data']['item'][$k]['active'] = (!isset($r['active'])?1:(int)$r['active']);
				$DATA['data']['item'][$k]['act'] = 1;
				$DATA['data']['item'][$k]['edit'] = 1;
				$DATA['data']['item'][$k]['del'] = 1;
				$DATA['data']['item'][$k]['id'] = $k;
			}
		}
		global $HTML;
		if(isset($_SESSION['messtool'])) {
			$DATA['messages'][] = $_SESSION['messtool'];
			unset($_SESSION['messtool']);
		}
		$DATA['superlist'] = $DATA;
		$result = $HTML->transformPHP($DATA, 'superlist');
	}
	return $result;
}

function tools_worktime() {
	global $_CFG,$_tpl;
	if(!static_main::_prmUserCheck(1))
		return static_main::m('denied');
	$result = '';
	$_tpl['styles']['form']=1;
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
		$DATA = array('_*features*_' => array('method' => 'POST', 'name' => 'step0'));
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

		$DATA['formcreat'] = array('form' => $DATA);
		$DATA['formcreat']['messages'] = $mess;
		global $HTML;
		$result .= $HTML->transformPHP($DATA, 'formcreat');
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
		while ($row = $result->fetch_array()) {
			$arr['vars']['owner_id']=$UGROUP->config["noreggroup"];
			$arr['vars']['active']=0;
			$arr['vars'][$this->mf_createrid]=$arr['vars']['id'];
			$arr['vars']['reg_hash']=md5(time().$arr['vars']['id'].$arr['vars']['name']);
			$pass=$arr['vars']['pass'];
			$arr['vars']['pass']=md5($this->_CFG['wep']['md5'].$arr['vars']['pass']);
			//$_SESSION['user']['id'] = $arr['vars']['id'];
			if(!$UGROUP->child['user']->_add_item($arr['vars'])) {
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

function memcachstatus() {
	global $_CFG;
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
		$datamail['Bcc']=$_POST['Bcc'];
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
			<lable>Bcc</lable> <input type="text" name="Bcc" value="'.$_POST['Bcc'].'"/><br/>
			<lable>Reply-To</lable> <input type="text" name="Reply-To" value="'.$_POST['Reply-To'].'"/><br/>
			<lable>Тема</lable> <input type="text" name="subject" value="'.$_POST['subject'].'"/><br/>
			<lable>Текст</lable><br/><textarea name="text" rows="7" cols="50" >'.htmlspecialchars($_POST['text'],ENT_QUOTES,$_CFG['wep']['charset']).'</textarea><br/>
			<br/><input type="submit" name="Пуск"/></form>';
	return $html;
}

$dataF = array(
	'tools_step1'=>'<span class="tools_item">Настройки сайта</span>',
	'tools_step2'=>'<span class="tools_item">Проверка структуры сайта</span>',
	'tools_step3'=>'<span class="tools_item">Установка модулей и удаление.</span>',
	'tools_updater'=>'<span class="tools_item" style="color:red;">Обновление</span>',
	'tools_cron'=>'<span class="tools_item">Настройка Крона</span>',
	'tools_docron'=>'<span class="tools_item">Выполнить Крон вручную</span>',
	'tools_sendMail'=>'<span class="tools_item">Отправка почты</span>',
	'tools_worktime'=>'<span class="tools_item">Режим "технические работы"</span>',
	'getphpinfo'=>'<span class="tools_item">PHPINFO</span>',
	'memcachstatus'=>'<span class="tools_item">Memcach status</span>',
	'allinfos'=> '<span class="tools_item">Выввод глобальных переменных</span>',
);

if(file_exists($_CFG['_PATH']['phpscript'].'/tools.php'))
	include($_CFG['_PATH']['phpscript'].'/tools.php');

$html = '<div>Выбирите функцию для запуска</div><hr><ul>';
foreach($dataF as $kk=>$rr) {
	if(isset($_GET['tfunc']) and $_GET['tfunc']==$kk) {
		$html .= '<li><a style="font-weight:bold;" href="'.$_CFG['PATH']['wepname'].'/index.php?_view=list&_modul=_tools&tfunc='.$kk.'">'.$rr.'</a>';
		$html .= ' <fieldset><legend>Результат выполнения функции '.$kk.'()</legend>';
		eval('$html .= '.$kk.'();');
		$html .= '</fieldset></li>';
	} else
		$html .= '<li><a href="'.$_CFG['PATH']['wepname'].'/index.php?_view=list&_modul=_tools&tfunc='.$kk.'">'.$rr.'</a></li>';
}
$html .= '</ul>';
$HTML->_templates = 'nologs';
return $html;
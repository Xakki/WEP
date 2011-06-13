<?
function tools_step1() {
	global $_CFG,$HTML,$_tpl;
	$TEMP_CFG= array();
	$_tpl['styles']['install']=1;
	$file = $_CFG['_PATH']['phpscript'] . '/install/step1.php';
	if(static_main::_prmUserCheck(1))
		return require($file);
	else
		return $_CFG['_MESS']['denied'];
}

function tools_step2() {
	global $_CFG,$HTML,$_tpl;
	if(!static_main::_prmUserCheck(1))
		return $_CFG['_MESS']['denied'];
	$_tpl['styles']['install']=1;
	$file = $_CFG['_PATH']['phpscript'] . '/install/step2.php';
	return require($file);
}

function tools_step3() {
	global $_CFG,$HTML,$_tpl;
	if(!static_main::_prmUserCheck(1))
		return $_CFG['_MESS']['denied'];
	$_tpl['styles']['install']=1;
	$file = $_CFG['_PATH']['phpscript'] . '/install/step3.php';
	return require($file);
}

function tools_cron() {
	global $_CFG,$_tpl;
	if(!static_main::_prmUserCheck(1))
		return $_CFG['_MESS']['denied'];
	$result = '';

	$ini_file = $_CFG['_PATH']['temp'].'cron.ini';
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
				reset($_CFG['wep']['cron']);
				$p = key($_CFG['wep']['cron'])+1;
			}
			$NEWDATA = array();
			$NEWDATA['wep']['cron'] = $_CFG['wep']['cron'];
			$NEWDATA['wep']['cron'][$p] = array(
				'time'=>$_POST['time'],
				'file'=>$_POST['file'],
				'modul'=>$_POST['modul'],
				'function'=>$_POST['function'],
			);
			list($fl,$mess) = static_tools::saveUserCFG($NEWDATA);
			if(!$fl)
				$FORM['info'] = array('type'=>'info', 'caption'=>'<h3 style="color:red;">Ошибка</h3>');
			else {
				$_SESSION['messtool'] = array('name'=>'ok','value'=>'Задание успешно добавлено.');
				header('Location: /'.key($DATA['path']));
				die();
			}
		}
		$DATA['path'][$FP.'_type=add'] = 'Добавить';
		if($_GET['_type'] == 'edit' and isset($_GET['_id']) and isset($_CFG['wep']['cron'][$_GET['_id']])) {
			$VAL = $_CFG['wep']['cron'][$_GET['_id']];
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
		$FORM['sbmt'] = array(
			'type' => 'submit',
			'value' => 'Сохранить');

		$FORM['formcreat'] = array('form' => $FORM);
		//$FORM['formcreat']['messages'] = $mess;
		global $HTML;
		$result = $HTML->transformPHP($DATA, 'path');
		$result .= $HTML->transformPHP($FORM, 'formcreat');
	}elseif($_GET['_type'] == 'del' and isset($_GET['_id'])) {
		$NEWDATA = array();
		$NEWDATA['wep']['cron'] = $_CFG['wep']['cron'];
		unset($NEWDATA['wep']['cron'][$_GET['_id']]);
		list($fl,$mess) = static_tools::saveUserCFG($NEWDATA);
		if(!$fl)
			$_SESSION['messtool'] = array('name'=>'error','value'=>'Ошибка.');
		else
			$_SESSION['messtool'] = array('name'=>'ok','value'=>'Задание успешно Удалено.');
		header('Location: /'.key($DATA['path']));
		die();
	}else {
		$DATA['data'] = array(
			'thitem'=>array(
				'time'=>array('value'=>'Период'),
				'file'=>array('value'=>'Фаил'),
				'modul'=>array('value'=>'Модуль'),
				'function'=>array('value'=>'Функция'),
				'lasttime'=>array('value'=>'Время прошлого выполнения')
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
				);
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
		return $_CFG['_MESS']['denied'];
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
		return $_CFG['_MESS']['denied'];
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
				$datamail['mailTo']=$arr['vars']['email'];
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
		return $_CFG['_MESS']['denied'];
	ob_start();
	phpinfo();
	return ob_get_flush();
}
$dataF = array(
	'tools_step1'=>'<span class="tools_item">Настройки сайта</span>',
	'tools_step2'=>'<span class="tools_item">Проверка структуры сайта</span>',
	'tools_step3'=>'<span class="tools_item">Установка модулей и удаление.</span>',
	'tools_cron'=>'<span class="tools_item">Настройка Крона</span>',
	'tools_worktime'=>'<span class="tools_item">Режим "технические работы"</span>',
	'getphpinfo'=>'<span class="tools_item">PHPINFO</span>',
	'allinfos'=> '<span class="tools_item">Выввод глобальных переменных</span>',
);

if(file_exists($_CFG['_PATH']['phpscript2'].'/tools.php'))
	include($_CFG['_PATH']['phpscript2'].'/tools.php');

$html = '<div>Выбирите функцию для запуска</div><hr><ul>';
foreach($dataF as $kk=>$rr) {
	if($_GET['tfunc']==$kk) {
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
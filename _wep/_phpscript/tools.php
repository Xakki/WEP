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
	'tools_step3'=>'<span class="tools_item">Установка модулей и удаление, со всеми патрохами.</span>',
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
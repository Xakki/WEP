<?php
$_SESSION['step'] = 1;
if(!isset($var_const))
	$var_const = array(
		'mess'=>array(),
		'sbmt'=>'Сохранить'
	);
//Подключение к БД и доп параметры

$DEF_CFG = static_tools::getFdata($_CFG['_PATH']['wep'] . '/config/config.php', '/* MAIN_CFG */', '/* END_MAIN_CFG */');
$USER_CFG = static_tools::getFdata($_CFG['_PATH']['wepconf'] . '/config/config.php', '', '', $DEF_CFG);// Текущая полная конфигурация
//print_r('<pre>');print_r($USER_CFG);exit();
$DATA = array();
$DATA['rootlogin'] =  array('type'=>'text','caption'=>'Login БД с правами суперпользователя','style'=>'background-color:#ff9966;');
$DATA['rootpass'] =  array('type'=>'password_new','caption'=>'Пароль БД с правами суперпользователя','style'=>'background-color:#ff9966;');
if(isset($_POST['rootlogin']))
	$DATA['rootlogin']['value'] = $_POST['rootlogin'];
if(isset($_POST['rootpass']))
	$DATA['rootpass']['value'] = $_POST['rootpass'];

include_once($_CFG['_PATH']['wep'] . '/config/config_form.php');
foreach($_CFGFORM as $kt=>$rb) {
	foreach($rb as $k=>$r) {
		if(isset($USER_CFG[$kt][$k])) {
			$r['value'] = $USER_CFG[$kt][$k];
			if(isset($_POST['sbmt'])) {
				if(isset($_POST[$kt][$k])) {
					if(isset($r['multiple']) and $r['multiple'] and count($_POST[$kt][$k]))
						$_POST[$kt][$k] = array_combine($_POST[$kt][$k],$_POST[$kt][$k]);
					$r['value'] = $_POST[$kt][$k];
				}
				elseif($r['type']=='checkbox')
					$r['value'] = $_POST[$kt][$k] = 0;
			}
		}
		$DATA[$kt.'[' . $k.']'] = $r;
	}
}

$mess = array();$txt = '';
$flag = true;
if(!function_exists('openssl_encrypt') and !function_exists('mcrypt_decrypt')) {
	$mess[] = array('name' => 'error', 'value' => 'Необходимо подключить php модуль openssl либо mcrypt');
	$flag = false;
}

if (isset($_POST['sbmt']) and $flag) {
	$sqlfl = true;
	static_tools::checkWepconf();
	if(isset($_POST['rootlogin']) and $_POST['rootlogin']) {
		$sqlfl = false;
		$temp = array(
			'host'=>$_POST['sql']['host'],
			'login'=>$_POST['rootlogin'],
			'password'=>$_POST['rootpass']);
		$rSQL = new sql($temp);
		if($rSQL->ready)
			list($sqlfl,$txt) = $rSQL->sql_install($_POST['sql']);
		else
			$txt = 'Не верный логин-пароль суперпользователя. Не удалось подключиться к БД.';
	}
	if($sqlfl)
		list($sqlfl,$mess) = static_tools::saveUserCFG($_POST,$TEMP_CFG);
	else
		$mess[] = array('error',$txt);
	//Записать в конфиг все данные которые отличаются от данных по умолчанию
	if ($sqlfl) {
		file_put_contents($_CFG['_PATH']['config']. 'hash.key',(md5(time()).md5($_CFG['wep']['md5'])));
		$mess[] = $var_const['mess'];
		$DATA['messages'] = $mess;
		$_SESSION['step'] = 2;
		return $html = $HTML->transformPHP($DATA, 'messages');
		//@header('Location: install.php?step=' . ($_GET['step'] + 1));
		//die('<a href="install.php?step=' . ($_GET['step'] + 1) . '">Следующий шаг</a>');
	}
	$USER_CFG = $_POST;
}
else {
	$mess[] = array('name' => 'ok', 'value' => 'Будте осторожны при вводе этих настроек.');
}

$DATA['_*features*_'] = array('method' => 'POST', 'name' => 'step0');
if($flag) {
	$DATA['sbmt'] = array(
		'type' => 'submit',
		'value' => $var_const['sbmt']);
}

$DATA['formcreat'] = array('form' => $DATA);
$DATA['formcreat']['messages'] = $mess;
$html = $HTML->transformPHP($DATA, 'formcreat');
return $html;
<?
$_SESSION['step'] = 1;
if(!isset($var_const))
	$var_const = array(
		'mess'=>array(),
		'sbmt'=>'Сохранить'
	);
//Подключение к БД и доп параметры
$edit_cfg = array(
	'sql' => true,
	'memcache' => true,
	'wep' => true,
	'site' => true,
);
$DEF_CFG = static_tools::getFdata($_CFG['_PATH']['wep'] . '/config/config.php', '/* MAIN_CFG */', '/* END_MAIN_CFG */');
$USER_CFG = static_tools::getFdata($_CFG['_PATH']['wepconf'] . '/config/config.php', '', '', $DEF_CFG);// Текущая полная конфигурация
//print_r('<pre>');print_r($USER_CFG);exit();
$DATA = array();
include_once($_CFG['_PATH']['wep'] . '/config/config_form.php');
foreach($edit_cfg as $kt=>$rb) {
	if($rb) {
		foreach($_CFGFORM[$kt] as $k=>$r) {
			$r['value'] = $USER_CFG[$kt][$k];
			if(isset($_POST['sbmt'])) {
				if(isset($_POST[$kt][$k])) {
					if($r['multiple'] and count($_POST[$kt][$k]))
						$_POST[$kt][$k] = array_combine($_POST[$kt][$k],$_POST[$kt][$k]);
					$r['value'] = $_POST[$kt][$k];
				}
				elseif($r['type']=='checkbox')
					$r['value'] = $_POST[$kt][$k] = 0;
			}
			$DATA[$kt.'[' . $k.']'] = $r;
		}
	}
}

$mess = array();
if (isset($_POST['sbmt'])) {
	list($fl,$mess) = static_tools::saveUserCFG($_POST,$TEMP_CFG);
	//Записать в конфиг все данные которые отличаются от данных по умолчанию
	if ($fl) {
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
$DATA['sbmt'] = array(
	'type' => 'submit',
	'value' => $var_const['sbmt']);

$DATA['formcreat'] = array('form' => $DATA);
$DATA['formcreat']['messages'] = $mess;
$html = $HTML->transformPHP($DATA, 'formcreat');
return $html;
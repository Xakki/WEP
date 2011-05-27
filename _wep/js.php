<?

	$GLOBALS['_RESULT']	= $DATA = array();
	$_tpl['onload']=$html=$html2='';

	$_CFG['_PATH']['wep'] = dirname($_SERVER['SCRIPT_FILENAME']);
	require_once($_CFG['_PATH']['wep'].'/config/config.php');
	require($_CFG['_PATH']['phpscript'].'/jquery_getjson.php');


	if($_CFG['robot']) {
		$GLOBALS['_RESULT']['html'] = $_CFG['_MESS']['deniedrobot'];
		exit($_CFG['_MESS']['deniedrobot']);
	}
	elseif(!isset($_COOKIE[$_CFG['session']['name']])) {
		$GLOBALS['_RESULT']['html'] = $_CFG['_MESS']['denieda'];
		exit($_CFG['_MESS']['denieda']);
	}

	require_once($_CFG['_PATH']['core'].'html.php');

	$result = static_main::userAuth(); // запскает сессию и проверяет авторизацию
	if(!$result[1]) {
		//header('Location: login.php?ref='.base64_encode($_SERVER['REQUEST_URI']));
		$GLOBALS['_RESULT']['html'] = 'Вы не авторизованы , либо доступ закрыт.';
		exit($GLOBALS['_RESULT']['html']);
	}

	if($_CFG['wep']['access'] and (!isset($_SESSION['user']['id']) or $_SESSION['user']['level']>=5)) {
		$GLOBALS['_RESULT']['html'] = $_CFG['_MESS']['denied'];
		exit($_CFG['_MESS']['denied']);
		//$_tpl['onload']='window.location="login.php?mess=Недостаточно прав доступа."';
	}
	elseif(!$_GET['_modul'] or !$_SESSION['user']['wep']) {
		$GLOBALS['_RESULT']['html'] = $_CFG['_MESS']['errdata'];
		exit($_CFG['_MESS']['errdata']);
		//$_tpl['onload']='fLog(\'<div style="color:red;">'.date('H:i:s').' : Параметры заданны неверно!</div>\',1);fSwin1();';
	}

	if(!_new_class($_GET['_modul'],$MODUL))
		exit(' Модуль '.$_GET['_modul'].' не установлен');
		//$_tpl['onload']='fLog(\'<div style="color:red;">'.date('H:i:s').' : Модуль '.$_GET['_modul'].' не установлен</div>\',1);fSwin1();';

	if(!static_main::_prmModul($_GET['_modul'],array(1,2)))  // Проверка доступа к модулю
		exit('Доступ к модулю '.$_GET['_modul'].' запрещён администратором');
		//$_tpl['onload']='fLog(\'<div style="color:red;">'.date('H:i:s').' : Доступ к модулю '.$_GET['_modul'].' запрещён администратором</div>\',1);fSwin1();';



	if(isset($_COOKIE['cdesign']) and $_COOKIE['cdesign'])
		$_design = $_COOKIE['cdesign'];
	elseif($_SESSION['user']['design'])
		$_design = $_SESSION['user']['design'];
	else 
		$_design = $_CFG['wep']['design'];

	$HTML = new html($_CFG['PATH']['cdesign'],$_design,false);// упрощённый режим

	if(isset($_GET['_oid']) and $_GET['_oid']!='') $MODUL->owner_id = $_GET['_oid'];
	if(isset($_GET['_pid']) and $_GET['_pid']!='') $MODUL->parent_id = $_GET['_pid'];
	if(isset($_GET['_id']) and $_GET['_id']!='') $MODUL->id = $_GET['_id'];

	if($_SESSION['user']['wep'])
		include($_CFG['_PATH']['cdesign'].$_design.'/js.php');
	else {
		exit($_CFG['_MESS']['denied']);
	}

	$GLOBALS['_RESULT'] = array("html" => $html,"html2" => $html2,'eval'=>$_tpl['onload']);

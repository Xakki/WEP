<?
	$GLOBALS['_RESULT']	= $DATA = array();
	$_tpl['onload']=$html=$html2='';

	$_CFG['_PATH']['wepconf'] = dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/_wepconf';
	require_once($_CFG['_PATH']['wepconf'].'/config/config.php');
	require($_CFG['_PATH']['phpscript'].'/jquery_getjson.php');


	if($_SERVER['robot']) {
		$GLOBALS['_RESULT']['html'] = $_CFG['_MESS']['deniedrobot'];
		exit($_CFG['_MESS']['deniedrobot']);
	}
	elseif(!isset($_COOKIE[$_CFG['session']['name']])) {
		$GLOBALS['_RESULT']['html'] = $_CFG['_MESS']['denieda'];
		exit($_CFG['_MESS']['denieda']);
	}

	require_once($_CFG['_PATH']['core'].'html.php');
	require_once($_CFG['_PATH']['core'].'sql.php');
	$SQL = new sql();

	$result = userAuth(); // запскает сессию и проверяет авторизацию
	if(!$result[1]) {
		//header('Location: login.php?ref='.base64_encode($_SERVER['REQUEST_URI']));
		$GLOBALS['_RESULT']['html'] = 'Вы не авторизованы , либо доступ закрыт.';
		exit($GLOBALS['_RESULT']['html']);
	}

	if($_CFG['wep']['access'] and (!isset($_SESSION['user']) or $_SESSION['user']['level']>=5)) {
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

	if(!_prmModul($_GET['_modul'],array(1,2)))  // Проверка доступа к модулю
		exit('Доступ к модулю '.$_GET['_modul'].' запрещён администратором');
		//$_tpl['onload']='fLog(\'<div style="color:red;">'.date('H:i:s').' : Доступ к модулю '.$_GET['_modul'].' запрещён администратором</div>\',1);fSwin1();';



	if($_COOKIE['cdesign'])
		$_design = $_COOKIE['cdesign'];
	elseif($_SESSION['user']['design'])
		$_design = $_SESSION['user']['design'];
	else 
		$_design = $_CFG['wep']['design'];
	$_design = 'default';
	$HTML = new html($_CFG['PATH']['cdesign'],$_design,false);// упрощённый режим

	if($_GET['_oid']!='') $MODUL->owner_id = $_GET['_oid'];
	if($_GET['_pid']!='') $MODUL->parent_id = $_GET['_pid'];
	if($_GET['_id']!='') $MODUL->id = $_GET['_id'];

	if($_SESSION['user']['wep'])
		include($_CFG['_PATH']['cdesign'].$_design.'/js.php');
	else {
		exit($_CFG['_MESS']['denied']);
	}

	//$log = fDisplLogs();
	//$_tpl['onload'] .= (count($log)?'fLog(\''.$log[0].'\',\''.$log[1].'\');':'');
	$_tpl['onload'] .= '$(\'#inftime\').html(\'
	<div style="color:blue;">Обработка страницы '.(getmicrotime()-$_time_start).' c.</div>
	<div style="color:green;">Пaмять '. intval(memory_get_usage()/1024).'/'. intval(memory_get_peak_usage()/1024).' кб</div>
	<div style="color:yellow;">Кол-во SQL запросов "'.count($_CFG['logs']['sql']).'"</div>\');';

	$GLOBALS['_RESULT'] = array("html" => $html,"html2" => $html2,'eval'=>$_tpl['onload']);

?>
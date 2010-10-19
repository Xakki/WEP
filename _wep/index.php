<?
	$_CFG['_PATH']['wepconf'] = dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/_wepconf';
	require_once($_CFG['_PATH']['wepconf'].'/config/config.php');
	require_once($_CFG['_PATH']['core'].'/html.php');
	require_once($_CFG['_PATH']['core'].'/sql.php');
	$SQL = new sql();

	$result = userAuth(); // запскает сессию и проверяет авторизацию
	if(!$result[1]) {
		header('Location: login.php?ref='.base64_encode($_SERVER['REQUEST_URI']));
		exit();
	}

	if($_COOKIE['cdesign'])
		$_design = $_COOKIE['cdesign'];
	elseif($_SESSION['user']['design'])
		$_design = $_SESSION['user']['design'];
	else 
		$_design = $_CFG['wep']['design'];

	$_design = 'default';
	$HTML = new html($_CFG['PATH']['cdesign'],$_design);

	if($_SESSION['user']['wep'])
		include($_CFG['_PATH']['cdesign'].$_design.'/inc.php');
	else {
		$_tpl['mess'] = $_CFG['_MESS']['denied'];
		$HTML->_templates = "login";
	}
?>

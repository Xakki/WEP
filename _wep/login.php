<?	
	$_CFG['_PATH']['wepconf'] = dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/_wepconf';
	require_once($_CFG['_PATH']['wepconf'].'/config/config.php');
	require_once($_CFG['_PATH']['core'].'html.php');
	require_once($_CFG['_PATH']['core'].'sql.php');
	$SQL = new sql();

	session_go(1);

	$delay =4;
	$variant = "";
	$ref= 'index.php';
	if(isset($_REQUEST['ref']) and $_REQUEST['ref']!='') {
		if(substr($_REQUEST['ref'],-2)=='==')
			$ref = base64_decode($_REQUEST['ref']);
		elseif(!strstr($_REQUEST['ref'],'login.php'))
			$ref = $_REQUEST['ref'];
	}
	elseif($_SERVER['HTTP_REFERER']!='' and !strstr($_SERVER['HTTP_REFERER'],'login.php'))
		$ref= $_SERVER['HTTP_REFERER'];

	if(isset($_REQUEST['exit']) && $_REQUEST['exit']=="ok") {
		unset($_SESSION['user']);unset($_SESSION['modulprm']);
		setcookie('remember', '', (time()-1000));
		$mess=$_CFG['_MESS']['exitok'];
		$ref='/index.html';
	}
	elseif(count($_POST) and isset($_POST['login'])) {
		$result = userAuth($_POST['login'],$_POST['pass']);
		if($result[1]) {
			@header("Location: ".$ref);
			die();
		}
	}
	elseif($result = userAuth() and $result[1]) {
		@header("Location: ".$ref);
		die();
	}
	if($_COOKIE['cdesign'])
		$_design = $_COOKIE['cdesign'];
	elseif($_SESSION['user']['design'])
		$_design = $_SESSION['user']['design'];
	else 
		$_design = $_CFG['wep']['design'];
	$HTML = new html($_CFG['PATH']['cdesign'],$_design);
	$HTML->_templates = 'login';
	$_tpl['ref'] = $ref;
	$_tpl['mess'] = '<div class="messhead">'.$result[0].'</div>';
	//print_r('<pre>');print_r($_tpl);print_r('</pre>');
?>
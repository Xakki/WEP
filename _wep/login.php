<?	
	$_CFG['_PATH']['wep'] = dirname($_SERVER['SCRIPT_FILENAME']);
	require_once($_CFG['_PATH']['wep'].'/config/config.php');
	require_once($_CFG['_PATH']['core'].'html.php');
	require_once($_CFG['_PATH']['core'].'sql.php');
	$SQL = new sql();
	if(isset($_GET['install'])) {
		// if(!isset pg table)
		$SQL->_iFlag = 1;
	}

	$delay =4;
	$variant = "";
	$ref= $_CFG['_HREF']['BH'].$_CFG['PATH']['wepname'];
	if(isset($_REQUEST['ref']) and $_REQUEST['ref']!='') {
		if(substr($_REQUEST['ref'],0,1)!='/' and !strstr($_REQUEST['ref'],'.'))
			$ref = base64_decode($_REQUEST['ref']);
		else
			$ref = $_REQUEST['ref'];
		if(strstr($ref,'login.php'))
			$ref = $_CFG['_HREF']['BH'].$_CFG['PATH']['wepname'];
	}
	elseif($_SERVER['HTTP_REFERER']!='' and !strstr($_SERVER['HTTP_REFERER'],'login.php'))
		$ref= $_SERVER['HTTP_REFERER'];

	if(count($_POST) and isset($_POST['login'])) {
		$result = userAuth($_POST['login'],$_POST['pass']);
		if($result[1]) {
			//@header("Location: ".$ref);
			die($ref);
		}
	}
	elseif(isset($_REQUEST['exit']) && $_REQUEST['exit']=="ok") {
		userExit();
		$mess=$_CFG['_MESS']['exitok'];
		$ref = $_CFG['_HREF']['BH'].'index.html';
	}
	elseif(isset($_COOKIE['remember']) and $result = userAuth() and $result[1]) {
		@header("Location: ".$ref);
		die($ref);
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
	if($result[0]) $result[0] = '<div style="color:red;">'.$result[0].'</div>';
	$_tpl['mess'] = '<div class="messhead">'.$result[0].'</div>';
?>
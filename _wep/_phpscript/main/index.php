<?
	if(!$_CFG['_PATH']['wep']) die('ERROR');

	require_once($_CFG['_PATH']['wep'].'/config/config.php');
	require_once($_CFG['_PATH']['core'].'/html.php');	/**отправляет header и печатает страничку*/
	
	if(isset($_GET['_php']) and $_GET['_php']=='sitemap') {
		$SITEMAP = TRUE;
		$PGLIST = new pg_class();
		echo $PGLIST->creatSiteMaps();
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='captcha') {
		require_once($_CFG['_PATH']['wep'].'/_phpscript/main/_captcha.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='json') {
		require_once($_CFG['_PATH']['wep'].'/_phpscript/main/_json.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='js') {
		require_once($_CFG['_PATH']['wep'].'/_phpscript/main/_js.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='redirect') {
		require_once($_CFG['_PATH']['wep'].'/_phpscript/main/_redirect.php');
		exit();
	}

	session_go();
/*
	if(!isset($_SESSION['user']) and isset($_COOKIE['remeber'])) {
		_new_class('ugroup',$UGROUP);
		$USERS = &$UGROUP->childs['users'];
		$USERS->cookieAuthorization();
	}
*/
	$_tpl['logs']=$_tpl['onload']=$_tpl['city']=$_tpl['blockadd']=$_tpl['param']=$_tpl['blockadd']='';
	$rid = 0;

//INCLUDE*****************
	$PGLIST = new pg_class();
		if (!isset($_GET['page'])) 
			$_GET['page'] = "index";
		$_GET['page'] = explode('/',$_GET['page']);
		$PGLIST->id = $_GET['page'][(count($_GET['page'])-1)];
		if(!$PGLIST->id) 
			$PGLIST->id = "index";

	$HTML = new html('_design/',$PGLIST->config['design']);//отправляет header и печатает страничку

//if($_SESSION['_showallinfo']) {print('main1 = '.(getmicrotime()-$main1time).'<hr/>');$main2time = getmicrotime();}

	$PGLIST->display();

//if($_SESSION['_showallinfo']) print('main = '.(getmicrotime()-$main2time).'<hr/>'); // для отладки
	if(!is_array($PGLIST->pageinfo['styles'])) $PGLIST->pageinfo['styles'] = array();
	if(!is_array($_tpl['styles'])) $_tpl['styles'] = array();
	if(!is_array($PGLIST->pageinfo['script'])) $PGLIST->pageinfo['script'] = array();
	if(!is_array($_tpl['script'])) $_tpl['script'] = array();
	$_tpl['styles'] = $PGLIST->pageinfo['styles'] + $_tpl['styles'];
	$_tpl['script'] = $PGLIST->pageinfo['script'] + $_tpl['script'];
$_CFG['fileIncludeOption'] = array(); // чтобы автоматом не подключались стили и скрптыв
/*
	if(!isset($_SESSION['showIEwarning'])) $_SESSION['showIEwarning']=0;
	if($HTML->_fTestIE('MSIE 6') and $_SESSION['showIEwarning']<3) {
		$_SESSION['showIEwarning']++;
		//$_tpl['script'] .='<!--[if IE 6]><script type="text/javascript"></script><![endif]-->';
	}
*/
?>

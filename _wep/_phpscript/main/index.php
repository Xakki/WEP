<?
	if(!$_CFG['_PATH']['wep']) die('ERROR');

	require_once($_CFG['_PATH']['wep'].'/config/config.php');
	require_once($_CFG['_PATH']['core'].'/html.php');	/**отправляет header и печатает страничку*/
	require_once($_CFG['_PATH']['core'].'/sql.php');
	$SQL = new sql();
	
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
	$PGLIST = new pg_class($SQL);
		if (!isset($_GET['page'])) 
			$_GET['page'] = "index";
		$_GET['page'] = explode('/',$_GET['page']);
		$PGLIST->id = $_GET['page'][(count($_GET['page'])-1)];


	$HTML = new html('_design/',$PGLIST->config['design']);//отправляет header и печатает страничку

//if($_SESSION['_showallinfo']) {print_r('main1 = '.(getmicrotime()-$main1time).'<hr/>');$main2time = getmicrotime();}

	$PGLIST->display();

//if($_SESSION['_showallinfo']) print_r('main = '.(getmicrotime()-$main2time).'<hr/>'); // для отладки

	$_tpl['styles'] = $PGLIST->pageinfo['styles'] + $_tpl['styles'];
	$_tpl['script'] = $PGLIST->pageinfo['script'] + $_tpl['script'];
	include($_CFG['_PATH']['core'].'/includesrc.php');
	arraySrcToStr();
/*
	if(!isset($_SESSION['showIEwarning'])) $_SESSION['showIEwarning']=0;
	if($HTML->_fTestIE('MSIE 6') and $_SESSION['showIEwarning']<3) {
		$_SESSION['showIEwarning']++;
		//$_tpl['script'] .='<!--[if IE 6]><script type="text/javascript"></script><![endif]-->';
	}
*/
?>

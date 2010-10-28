<?
	if(!$_CFG['_PATH']['path']) die('ERROR');

	require_once($_CFG['_PATH']['path'].'/_wepconf/config/config.php');
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
		if (!isset($_GET['page'])) $_GET['page'] = "index";
		$PGLIST->id = $_GET['page'];
		if(isset($_GET['path'])) $_GET['path']=explode('/',$_GET['path']);
		else $_GET['path']=array();


	$HTML = new html('_design/',$PGLIST->config['design']);//отправляет header и печатает страничку

//if($_SESSION['_showallinfo']) {print_r('main1 = '.(getmicrotime()-$main1time).'<hr/>');$main2time = getmicrotime();}

	$PGLIST->display();

//if($_SESSION['_showallinfo']) print_r('main = '.(getmicrotime()-$main2time).'<hr/>'); // для отладки

// SCRIPT*****************
		$tempscript =$_tpl['script'];$_tpl['script']='';
		if(is_array($PGLIST->pageinfo['script'])){
			//sort($PGLIST->pageinfo['script']);
			//reset($PGLIST->pageinfo['script']);
			foreach($PGLIST->pageinfo['script'] as $r)
				if($r){
					$_tpl['script'] .='<script type="text/javascript" src="'.$_CFG['_HREF']['_script'].$r.'.js"></script>'."\n";
					if($r=='jquery.fancybox') $_tpl['onload'] .="$('.imagebox a').fancybox();";
					//if($r=='jquery.form') $_tpl['onload'] .="JSFR('form');";//for ajax form
				}
		}
		$_tpl['script'] .=$tempscript;
/*
		if(!isset($_SESSION['showIEwarning'])) $_SESSION['showIEwarning']=0;
		if($HTML->_fTestIE('MSIE 6') and $_SESSION['showIEwarning']<3) {
			$_SESSION['showIEwarning']++;
			//$_tpl['script'] .='<!--[if IE 6]><script type="text/javascript"></script><![endif]-->';
		}
*/
// SCRIPT*****************
//STYLE*******************
		//$_tpl['styles'] .='<link rel="stylesheet" href="/_design/_style/style.css" type="text/css"/>';
		if(is_array($PGLIST->pageinfo['styles']))
			foreach($PGLIST->pageinfo['styles'] as $r)
				if($r!='')// and $r!='style'
					$_tpl['styles'] .='<link rel="stylesheet" href="'.$_CFG['_HREF']['_style'].$r.'.css" type="text/css"/>'."\n";
?>

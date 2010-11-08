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

//STYLE*******************
	$_tpl['styles'] = $PGLIST->pageinfo['styles'] + $_tpl['styles'];
	if($_tpl['styles'] and is_array($_tpl['styles']) and count($_tpl['styles'])) {
		$temp = '';
		foreach($_tpl['styles'] as $kk=>$rr) {
			if($rr==1 and $kk)
				$temp .= '<link type="text/css" href="'.$_CFG['_HREF']['_style'].$kk.'.css" rel="stylesheet"/>'."\n";
			elseif($rr)
				$temp .= $rr."\n";
		}
		$_tpl['styles'] = $temp;
	}

// SCRIPT*****************

	$_tpl['script'] = $PGLIST->pageinfo['script'] + $_tpl['script'];
	if($_tpl['script'] and is_array($_tpl['script']) and count($_tpl['script'])) {
		$temp = '';
		foreach($_tpl['script'] as $kk=>$rr) {
			if($kk=='jquery.fancybox')
				$_tpl['onload'] .= "$('.imagebox a').fancybox();";
			//if($kk=='jquery.form')
			//	$_tpl['onload'] .="JSFR('form');";//for ajax form
			if($rr==1 and $kk)
				$temp .= '<script type="text/javascript" src="'.$_CFG['_HREF']['_script'].$kk.'.js"></script>'."\n";
			elseif($rr)
				$temp .= $rr."\n";
		}
		$_tpl['script'] = $temp;
	}

/*
	if(!isset($_SESSION['showIEwarning'])) $_SESSION['showIEwarning']=0;
	if($HTML->_fTestIE('MSIE 6') and $_SESSION['showIEwarning']<3) {
		$_SESSION['showIEwarning']++;
		//$_tpl['script'] .='<!--[if IE 6]><script type="text/javascript"></script><![endif]-->';
	}
*/
?>

<?
	if(!$_CFG['_PATH']['wep']) die('ERROR');
	
	require_once($_CFG['_PATH']['wep'].'/config/config.php');
	if($_CFG['site']['worktime'] and !isset($_COOKIE['_showallinfo'])) {
		if(!isset($_CFG["site"]["work_text"]) or !$_CFG["site"]["work_text"])
			$_CFG["site"]["work_text"] = '<h1>Технический перерыв.</h1>';
		if(!isset($_CFG["site"]["work_title"]) or !$_CFG["site"]["work_title"])
			$_CFG["site"]["work_title"] = 'Ушёл на базу.';
		if(file_exists($_CFG['_PATH']['phpscript2'].'/main/work.html'))
			$html = file_get_contents($_CFG['_PATH']['phpscript2'].'/main/work.html');
		else
			$html = file_get_contents($_CFG['_PATH']['phpscript'].'/main/work.html');
		$html = str_replace('"', '\"', $html);
		eval('$html = "' .$html . '";');
		echo $html;
		exit();
	}

	// эти html.php не подключаем, если что сами подключат
	if(isset($_GET['_php']) and $_GET['_php']=='json') {
		if(file_exists($_CFG['_PATH']['wepconf'].'/_phpscript/_json.php'))
			require_once($_CFG['_PATH']['wepconf'].'/_phpscript/_json.php');
		else
			require_once($_CFG['_PATH']['wep'].'/_phpscript/main/_json.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='js') {
		if(file_exists($_CFG['_PATH']['wepconf'].'/_phpscript/_js.php'))
			require_once($_CFG['_PATH']['wepconf'].'/_phpscript/_js.php');
		else
			require_once($_CFG['_PATH']['wep'].'/_phpscript/main/_js.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='redirect') {
		if(file_exists($_CFG['_PATH']['wepconf'].'/_phpscript/_redirect.php'))
			require_once($_CFG['_PATH']['wepconf'].'/_phpscript/_redirect.php');
		else
			require_once($_CFG['_PATH']['wep'].'/_phpscript/main/_redirect.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='captcha') {
		if(file_exists($_CFG['_PATH']['wepconf'].'/_phpscript/_captcha.php'))
			require_once($_CFG['_PATH']['wepconf'].'/_phpscript/_captcha.php');
		else
			require_once($_CFG['_PATH']['wep'].'/_phpscript/main/_captcha.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='sitemap') {
		$SITEMAP = TRUE;
		_new_class('pg',$PGLIST);
		echo $PGLIST->creatSiteMaps();
		exit();
	}

	require_once($_CFG['_PATH']['core'].'/html.php');	/**отправляет header и печатает страничку*/

	session_go();

	$_tpl['logs']=$_tpl['onload']=$_tpl['city']=$_tpl['blockadd']=$_tpl['param']=$_tpl['blockadd']='';
	$rid = 0;


//INCLUDE*****************
	_new_class('pg',$PGLIST);
		if (!isset($_GET['page'])) 
			$_GET['page'] = "index";
		if(is_array($_GET['page'])) $_GET['page'] = implode('/',$_GET['page']);
		$_GET['page'] = explode('/',trim($_GET['page'],'/'));

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
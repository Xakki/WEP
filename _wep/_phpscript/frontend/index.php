<?php
	if(!$_CFG['_PATH']['wep']) die('ERROR');
	require_once($_CFG['_PATH']['wep'].'config/config.php');

	$_tpl = array();
	$_tpl['meta'] = $_tpl['logs']=$_tpl['onload']=$_tpl['title']=$_tpl['text']='';

	if($_CFG['site']['worktime'] and !isset($_COOKIE[$_CFG['wep']['_showallinfo']]) and !isset($_GET[$_CFG['wep']['_showallinfo']])) {
		static_main::downSite(); // Exit()
	}

	// эти html.php не подключаем, если что сами подключат
	if(isset($_GET['_php']) and $_GET['_php']=='robotstxt') {
		if(file_exists($_CFG['_PATH']['wepconf'].'_phpscript/robotstxt.php'))
			require_once($_CFG['_PATH']['wepconf'].'_phpscript/robotstxt.php');
		else
			require_once($_CFG['_PATH']['wep'].'_phpscript/frontend/robotstxt.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='json') {
		$_CFG['returnFormat'] = 'json';
		require_once($_CFG['_PATH']['wep'].'_phpscript/frontend/_json.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='js') {
		$_CFG['returnFormat'] = 'json';
		if(file_exists($_CFG['_PATH']['wepconf'].'_phpscript/_js.php'))
			require_once($_CFG['_PATH']['wepconf'].'_phpscript/_js.php');
		else
			require_once($_CFG['_PATH']['wep'].'_phpscript/frontend/_js.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='redirect') {
		if(file_exists($_CFG['_PATH']['wepconf'].'_phpscript/_redirect.php'))
			require_once($_CFG['_PATH']['wepconf'].'_phpscript/_redirect.php');
		else
			require_once($_CFG['_PATH']['wep'].'_phpscript/frontend/_redirect.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='captcha') {
		if(file_exists($_CFG['_PATH']['wepconf'].'_phpscript/_captcha.php'))
			require_once($_CFG['_PATH']['wepconf'].'_phpscript/_captcha.php');
		else
			require_once($_CFG['_PATH']['wep'].'_phpscript/frontend/_captcha.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='rss') {
		if(file_exists($_CFG['_PATH']['wepconf'].'_phpscript/rss.php'))
			require_once($_CFG['_PATH']['wepconf'].'_phpscript/rss.php');
		else
			require_once($_CFG['_PATH']['wep'].'_phpscript/frontend/rss.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='sitemap') {
		setlocale(LC_ALL, $_CFG['wep']['setlocale']);
		header("Cache-Control: max-age=0, must-revalidate");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $_CFG['time']) . " GMT");
		header("Expires: " . gmdate("D, d M Y H:i:s", $_CFG['time']) . " GMT");
		header("Content-type:text/xml;charset=utf-8");
		require_once($_CFG['_PATH']['core'].'html.php');
		$_COOKIE['_showerror'] = 0;
		$SITEMAP = TRUE;
		_new_class('pg',$PGLIST);
		echo $PGLIST->creatSiteMaps();
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='config') {
		$_GET[$_CFG['wep']['_showallinfo']] = 0;
		//Применяется для CKFinder для авторизации по сессии
		require_once($_CFG['_PATH']['core'].'html.php');	/**отправляет header и печатает страничку*/
		session_go();
		return true;
	}
	elseif(isset($_GET['_php']) and $_GET['_type']=='xml') {
		if(file_exists($_CFG['_PATH']['wepconf'].'_phpscript/'.$_GET['_php'].'.xml.php'))
			require_once($_CFG['_PATH']['wepconf'].'_phpscript/'.$_GET['_php'].'.xml.php');
		else
			require_once($_CFG['_PATH']['wep'].'_phpscript/frontend/'.$_GET['_php'].'.xml.php');
		exit();
	}

	require_once($_CFG['_PATH']['core'].'html.php');	/**отправляет header и печатает страничку*/

	$rid = 0;

//INCLUDE*****************
	if(_new_class('pg',$PGLIST)) {
		if (!isset($_REQUEST['pageParam'])) 
			$_REQUEST['pageParam'] = "index";
		if(is_array($_REQUEST['pageParam'])) $_REQUEST['pageParam'] = implode('/',$_REQUEST['pageParam']);
		$_REQUEST['pageParam'] = explode('/',trim($_REQUEST['pageParam'],'/'));

		//if($_SESSION['_showallinfo']) {print('main1 = '.(getmicrotime()-$main1time).'<hr/>');$main2time = getmicrotime();}
			if ($PGLIST->config['auto_auth']) {
				static_main::userAuth();
			}

			$PGLIST->display();

		//if($_SESSION['_showallinfo']) print('main = '.(getmicrotime()-$main2time).'<hr/>'); // для отладки
				  
			if (!$PGLIST->config['auto_include'])
				$_CFG['fileIncludeOption'] = null; // чтобы автоматом не подключались стили и скрптыв
		/*
			if(!isset($_SESSION['showIEwarning'])) $_SESSION['showIEwarning']=0;
			if($HTML->_fTestIE('MSIE 6') and $_SESSION['showIEwarning']<3) {
				$_SESSION['showIEwarning']++;
				//$_tpl['script'] .='<!--[if IE 6]><script type="text/javascript"></script><![endif]-->';
			}
		*/
	} else 
		static_main::downSite('Система ещё не установлена','Модуль "Страницы" не установлен или отключен.');
<?php

	if($_NEED_INSTALL)
	{
		require_once($_CFG['_PATH']['wep_controllers'].'install/index.php');
		exit();
	}

	if(isset($_GET['_php']) and $_GET['_php']=='admin') 
	{
		require_once($_CFG['_PATH']['backend'].'index.php');
		exit();
	}

	if($_CFG['site']['worktime'] and !isset($_COOKIE[$_CFG['wep']['_showallinfo']]) and !isset($_GET[$_CFG['wep']['_showallinfo']])) {
		static_main::downSite(); // Exit()
	}

	// эти wep.php не подключаем, если что сами подключат
	if(isset($_GET['_php']) and $_GET['_php']=='robotstxt') {
		if(file_exists($_CFG['_PATH']['controllers'].'robotstxt.php'))
			require_once($_CFG['_PATH']['controllers'].'robotstxt.php');
		else
			require_once($_CFG['_PATH']['wep_controllers'].'frontend/robotstxt.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='js') {
		if(file_exists($_CFG['_PATH']['controllers'].'_js.php'))
			require_once($_CFG['_PATH']['controllers'].'_js.php');
		else
			require_once($_CFG['_PATH']['wep_controllers'].'frontend/_js.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='redirect') {
		if(file_exists($_CFG['_PATH']['controllers'].'_redirect.php'))
			require_once($_CFG['_PATH']['controllers'].'_redirect.php');
		else
			require_once($_CFG['_PATH']['wep_controllers'].'frontend/_redirect.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='rss') {
		if(file_exists($_CFG['_PATH']['controllers'].'rss.php'))
			require_once($_CFG['_PATH']['controllers'].'rss.php');
		else
			require_once($_CFG['_PATH']['wep_controllers'].'frontend/rss.php');
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='sitemap') {
		header("Cache-Control: max-age=0, must-revalidate");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $_CFG['time']) . " GMT");
		header("Expires: " . gmdate("D, d M Y H:i:s", $_CFG['time']) . " GMT");
		header("Content-type:text/xml;charset=utf-8");
		require_once($_CFG['_PATH']['core'].'wep.php');
		$_COOKIE['_showerror'] = 0;
		$SITEMAP = TRUE;
		_new_class('pg',$PGLIST);
		echo $PGLIST->creatSiteMaps();
		exit();
	}
	elseif(isset($_GET['_php']) and $_GET['_php']=='config') {
		$_GET[$_CFG['wep']['_showallinfo']] = 0;
		//Применяется для CKFinder для авторизации по сессии
		require_once($_CFG['_PATH']['core'].'wep.php');	/**отправляет header и печатает страничку*/
		session_go();
		return true;
	}
	elseif(isset($_GET['_php']) and $_GET['_type']=='xml') {
		if(file_exists($_CFG['_PATH']['controllers'].$_GET['_php'].'.xml.php'))
			require_once($_CFG['_PATH']['controllers'].$_GET['_php'].'.xml.php');
		else
			require_once($_CFG['_PATH']['wep_controllers'].'frontend/'.$_GET['_php'].'.xml.php');
		exit();
	}


	//*****************

	if(_new_class('pg',$PGLIST)) 
	{
		if (!isset($_REQUEST['pageParam'])) 
			$_REQUEST['pageParam'] = "index";
		if(is_array($_REQUEST['pageParam'])) $_REQUEST['pageParam'] = implode('/',$_REQUEST['pageParam']);
		$_REQUEST['pageParam'] = explode('/',trim($_REQUEST['pageParam'],'/'));

		//if($_SESSION['_showallinfo']) {print('main1 = '.(getmicrotime()-$main1time).'<hr/>');$main2time = getmicrotime();}
			if ($PGLIST->config['auto_auth']) {
				static_main::userAuth();
			}

			if(isset($_REQUEST['PGCID']) and $id = (int)$_REQUEST['PGCID'])
				$PGLIST->display_inc($id);
			elseif(isset($_REQUEST['PGMARKER']))
				$PGLIST->display_content($_REQUEST['PGMARKER']);
			else
				$PGLIST->display();

		//if($_SESSION['_showallinfo']) print('main = '.(getmicrotime()-$main2time).'<hr/>'); // для отладки

		/*
			if(!isset($_SESSION['showIEwarning'])) $_SESSION['showIEwarning']=0;
			if(_fTestIE('MSIE 6') and $_SESSION['showIEwarning']<3) {
				$_SESSION['showIEwarning']++;
				//$_tpl['meta'] .='<!--[if IE 6]><script type="text/javascript"></script><![endif]-->';
			}
		*/
	} else 
		static_main::downSite('Система ещё не установлена','Модуль "Страницы" не установлен или отключен.');
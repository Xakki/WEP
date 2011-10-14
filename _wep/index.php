<?php

	$_CFG['_PATH']['wep'] = dirname($_SERVER['SCRIPT_FILENAME']).'/';
	require_once($_CFG['_PATH']['wep'].'config/config.php');
	require_once($_CFG['_PATH']['core'].'html.php');
	$result = static_main::userAuth(); // запскает сессию и проверяет авторизацию

	if(!$result[1]) {
		header('Location: login.php?ref='.base64_encode($_SERVER['REQUEST_URI']));
		exit();
	}

	if(isset($_COOKIE['cdesign']) and $_COOKIE['cdesign'])
		$_design = $_COOKIE['cdesign'];
	elseif($_SESSION['user']['design'])
		$_design = $_SESSION['user']['design'];
	else 
		$_design = $_CFG['wep']['design'];
		
	$HTML = new html($_CFG['PATH']['cdesign'],$_design);
	if(!isset($_GET['_modul'])) $_GET['_modul'] = '';
/*ADMIN*/
	function fAdminMenu($_modul='') {
		global $_CFG;
		$data = array();
		$data['modul'] = $_GET['_modul'];
		$data['user'] = $_SESSION['user'];
		$data['item'] = array();
		if($_SESSION['user']['level']<=1) {
			static_main::_prmModulLoad();
			foreach($_CFG['modulprm'] as $k=>$r) {
				if(static_main::_prmModul($k,array(1,2)) and $r['active']==1) {
					$data['item'][$k] = $r;
					$data['item'][$k]['sel'] = ($_modul==$k?1:0);
				}
			}
			if(isset($_SESSION['user']['level']) and $_SESSION['user']['level']==0)
				$data['item']['_tools'] = array('name'=>'TOOLs','css'=>'am_tools','sel'=>($_modul=='_tools'?1:0));
		}
		/*weppages*/
		/*if(isset($_SESSION['user']) and count($_SESSION['user']['weppages'])) {
			foreach($_SESSION['user']['weppages'] as $k=>$r0)
				$template['sysconf']['item'][$k] = $r;
		}*/
		return $data;
	}

	function fXmlSysconf() {
		global $_CFG;
      $data = array();
		$data['sysconf']['modul'] = $_GET['_modul'];
		$data['sysconf']['user'] = $_SESSION['user'];
		$data['sysconf']['item'] = array();
		if($_SESSION['user']['level']<=1) {
			static_main::_prmModulLoad();
			foreach($_CFG['modulprm'] as $k=>$r) {
				if($r['active']==1 and $r['typemodul']==0 and $r['tablename'] and static_main::_prmModul($k,array(1,2))) {
					if(!$r['name'])
						$r['name'] = $k;
					if(!$r['active'])
						$r['name'] = '<span style="color:gray;">'.$r['name'].'</span>';
					$data['sysconf']['item'][$k] = $r['name'];
				}
			}
			if(isset($_SESSION['user']['level']) and $_SESSION['user']['level']==0)
				$data['sysconf']['item']['_tools'] = 'TOOLs';
		}
		/*weppages*/
		/*if(isset($_SESSION['user']) and count($_SESSION['user']['weppages'])) {
			foreach($_SESSION['user']['weppages'] as $k=>$r0)
				$template['sysconf']['item'][$k] = $r;
		}*/
		return $data;
	}

	function fXmlModulslist() {
		global $_CFG;
      $data = array();
		$data['modulslist']['modul'] = $_GET['_modul'];
		$data['modulslist']['user'] = $_SESSION['user'];
		static_main::_prmModulLoad();
		foreach($_CFG['modulprm'] as $k=>$r) {
			if($r['active']==1 and $r['typemodul']==3 and $r['tablename'] and static_main::_prmModul($k,array(1,2))) {
				if(!$r['name'])
					$r['name'] = $k;
				if(!$r['active'])
					$r['name'] = '<span style="color:gray;">'.$r['name'].'</span>';
				$data['modulslist']['item'][$k] = $r['name'];
			}
		}

		return $data;
	}
/*---------------ADMIN*/

	if($_SESSION['user']['wep']) {
		include($_CFG['_PATH']['cdesign'].$_design.'/inc.php');
		if(static_main::_prmUserCheck(2)) {
			$_tpl['debug'] = '<span class="seldebug"><select onchange="window.location.href=\'/'.$_CFG['PATH']['wepname'].'/index.php?'.$_CFG['wep']['_showallinfo'].'=\'+this.value;">
	<option '.(!$_COOKIE[$_CFG['wep']['_showallinfo']]?'selected="selected"':'').' value="0">Скрыть инфу</option>
	<option '.($_COOKIE[$_CFG['wep']['_showallinfo']]==1?'selected="selected"':'').' value="1">Показать инфу</option>
	<option '.($_COOKIE[$_CFG['wep']['_showallinfo']]==2?'selected="selected"':'').' value="2">Показать SQL запросы</option>
	<option '.($_COOKIE[$_CFG['wep']['_showallinfo']]==3?'selected="selected"':'').' value="3">Показать все логи</option>
	</select></span>';
			$_tpl['debug'] .= '<span class="seldebug"><select onchange="setCookie(\'cdesign\',this.value);window.location.href=\''.$_CFG['PATH']['wepname'].'/index.php\';">
	<option '.($_design=='default'?'selected="selected"':'').' value="default">Default</option>
	<option '.($_design=='extjs'?'selected="selected"':'').' value="extjs">ExtJS</option>
	</select></span>';
		}
		$_tpl['time'] = 'PHP ver.' . phpversion().' | '.date('Y-m-d H:i:s').' | '.date_default_timezone_get().' | ';
	}
	else {
		$_tpl['mess'] = static_main::m('denied');
		$HTML->_templates = "login";
	}
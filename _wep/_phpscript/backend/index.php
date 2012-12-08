<?php
	$_CFG['_F']['adminpage'] = true;
	
	$pageParam = trim($_GET['pageParam'],'/');
	$pageParam  = explode('/', $pageParam);
	$mainPage = array_shift($pageParam);

	if($mainPage=='js.php')
	{
		include('js.php');
		exit();
	}


	require_once($_CFG['_PATH']['core'].'html.php');
	$result = static_main::userAuth(); // запскает сессию и проверяет авторизацию

	if(isset($_COOKIE['cdesign']) and $_COOKIE['cdesign'])
		$_design = $_COOKIE['cdesign'];
	elseif(isset($_SESSION['user']['design']) and $_SESSION['user']['design'])
		$_design = $_SESSION['user']['design'];
	else 
		$_design = $_CFG['wep']['design'];
	$_design = 'default';////////////////////////////
		
	$HTML = new html($_CFG['PATH']['cdesign'],$_design);
	if(!isset($_GET['_modul'])) $_GET['_modul'] = '';

	if($mainPage=='logout')
	{
		static_main::userExit();
		$ref = MY_BH.'index.html';
		static_main::redirect($ref);
		exit();
	}


	if($mainPage=='login')
	{
		$result = array('','');
		$delay =4;
		$variant = "";
		$ref= $_CFG['PATH']['admin'];
		if(isset($_REQUEST['ref']) and $_REQUEST['ref']!='') {
			if(substr($_REQUEST['ref'],0,1)!='/' and !strstr($_REQUEST['ref'],'.'))
				$ref = base64decode($_REQUEST['ref']);
			else
				$ref = $_REQUEST['ref'];
			if(strstr($ref,'login') or strstr($ref,'install'))
				$ref = $_CFG['PATH']['admin'];
		}
		elseif(isset($_SERVER['HTTP_REFERER']) and $_SERVER['HTTP_REFERER']!='' and !strstr($_SERVER['HTTP_REFERER'],'login'))
			$ref= $_SERVER['HTTP_REFERER'];

		$messBlock = 'popMess';
		if(isset($_GET['recover']))
		{
			$result = array('На стадии разработки', '');
			$_tpl['flipped'] = 'flipped';
			$messBlock = 'popMessFlip';
		}
		else
		{
			if(count($_POST) and isset($_POST['login'])) {
				static_main::userExit();
				$result = static_main::userAuth($_POST['login'],$_POST['pass']);
				if($result[1]) {
					static_main::redirect($ref);//STOP
				}
			}
			elseif(isset($_GET['mess']))
				$result[0] = static_main::m($_GET['mess']);
			elseif(isset($_COOKIE['remember']) and $result = static_main::userAuth() and $result[1]) {
				static_main::redirect($ref);//STOP
			}
		}

		$HTML->_templates = 'login';

		$_tpl['forgot'] = 'Забыли?';
		$_tpl['loginLabel'] = 'Логин / Email';
		$_tpl['passLabel'] = 'Пароль';
		$_tpl['rememberLabel'] = 'Запомнить на 20 дней';
		$_tpl['loginSubmit'] = 'Войти';

		$_tpl['forgotLabel'] = 'Ваш Email';
		$_tpl['forgotSubmit'] = 'Восстановить';

		$_tpl['ref'] = $ref;
		$_tpl['actionLogin'] = $_CFG['PATH']['admin'].'login'.(isset($_GET['install'])?'?install':'');
		$_tpl['actionRecover'] = $_CFG['PATH']['admin'].'login?recover=true';
		if($result[0])
		{
			$result[0] = '<div class="err">'.$result[0].'</div>';
			$_tpl[$messBlock] = '<div class="messhead">'.$result[0].'</div>';
		}
		
		exit();
	}

	if(!$result[1]) {
		static_main::redirect($_CFG['PATH']['admin'].'login?ref='.base64encode($_SERVER['REQUEST_URI']));
	}


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
		include($_CFG['_PATH']['cdesign'].$_design.'/inc/index.php');
		if(static_main::_prmUserCheck(2)) {
			if(!isset($_COOKIE[$_CFG['wep']['_showerror']]))
				$_COOKIE[$_CFG['wep']['_showerror']] = 0;
			$_tpl['debug'] = '<span class="seldebug"><select onchange="window.location.href=\''.$_CFG['PATH']['admin'].'?'.$_CFG['wep']['_showerror'].'=\'+this.value;">
	<option '.(!$_COOKIE[$_CFG['wep']['_showerror']]?'selected="selected"':'').' value="0">не показывать ошибки</option>
	<option '.($_COOKIE[$_CFG['wep']['_showerror']]==1?'selected="selected"':'').' value="1">сообщение об ошибке</option>
	<option '.($_COOKIE[$_CFG['wep']['_showerror']]==2?'selected="selected"':'').' value="2">Показать все ошибки</option>
	<option '.($_COOKIE[$_CFG['wep']['_showerror']]==3?'selected="selected"':'').' value="3">DEBUG MODE</option>
	</select></span>';

			if(!isset($_COOKIE[$_CFG['wep']['_showallinfo']]))
				$_COOKIE[$_CFG['wep']['_showallinfo']] = 0;
			$_tpl['debug'] .= '<span class="seldebug"><select onchange="window.location.href=\''.$_CFG['PATH']['admin'].'?'.$_CFG['wep']['_showallinfo'].'=\'+this.value;">
	<option '.(!$_COOKIE[$_CFG['wep']['_showallinfo']]?'selected="selected"':'').' value="0">Скрыть инфу</option>
	<option '.($_COOKIE[$_CFG['wep']['_showallinfo']]==1?'selected="selected"':'').' value="1">Показать инфу</option>
	<option '.($_COOKIE[$_CFG['wep']['_showallinfo']]==2?'selected="selected"':'').' value="2">Показать SQL запросы</option>
	<option '.($_COOKIE[$_CFG['wep']['_showallinfo']]==3?'selected="selected"':'').' value="3">Показать все логи</option>
	</select></span>';

			/*$_tpl['debug'] .= '<span class="seldebug"><select onchange="setCookie(\'cdesign\',this.value);window.location.href=\''.$_CFG['PATH']['admin'].'\';">
	<option '.($_design=='default'?'selected="selected"':'').' value="default">Default</option>
	<option '.($_design=='extjs'?'selected="selected"':'').' value="extjs">ExtJS</option>
	</select></span>';*/
		}
		if(!isset($_SESSION['wep_info'])) {
			if(!$SQL) $SQL = new $_CFG['sql']['type']($_CFG['sql']);
			$info = $SQL->_info();
			$_SESSION['wep_info'] = 'PHP ver.' . phpversion().' | MySQL ver.' . $info['version'][1].' | '.date_default_timezone_get().' | ';
		}
		$_tpl['time'] = $_SESSION['wep_info'].date('Y-m-d H:i:s').' | ';

		if($_CFG['info']['email'])
			$_tpl['contact'] = '<div class="ctd1">e-mail:</div>	<div class="ctd2"><a href="mailto:'.$_CFG['info']['email'].'">'.$_CFG['info']['email'].'</a></div>';
		if($_CFG['info']['icq'])
			$_tpl['contact'] .= '<div class="ctd1">icq:</div><div class="ctd2">'.$_CFG['info']['icq'].'</div>';
		if(isset($_CFG['info']['phone']) and $_CFG['info']['phone'])
			$_tpl['contact'] .= '<div class="ctd1">телефон:</div><div class="ctd2">'.$_CFG['info']['phone'].'</div>';

		$_tpl['wep_ver'] = $_CFG['info']['version'];
	}
	else {
		static_main::redirect('login.php?mess=denied&ref='.base64encode($_SERVER['REQUEST_URI']));
	}
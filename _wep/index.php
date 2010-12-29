<?
error_reporting(-1);

	$_CFG['_PATH']['wep'] = dirname($_SERVER['SCRIPT_FILENAME']);
	require_once($_CFG['_PATH']['wep'].'/config/config.php');
	require_once($_CFG['_PATH']['core'].'/html.php');
	require_once($_CFG['_PATH']['core'].'/sql.php');
	$SQL = new sql();

	$result = userAuth(); // запскает сессию и проверяет авторизацию

	if(!$result[1]) {
		header('Location: login.php?ref='.base64_encode($_SERVER['REQUEST_URI']));
		exit();
	}

	if($_COOKIE['cdesign'])
		$_design = $_COOKIE['cdesign'];
	elseif($_SESSION['user']['design'])
		$_design = $_SESSION['user']['design'];
	else 
		$_design = $_CFG['wep']['design'];
		
	$HTML = new html($_CFG['PATH']['cdesign'],$_design);

/*ADMIN*/
	function fXmlSysconf(){
		global $_CFG;
      $data = array();
		$data['sysconf']['modul'] = $_GET['_modul'];
		$data['sysconf']['user'] = $_SESSION['user'];
		$data['sysconf']['item'] = array();
		if($_SESSION['user']['level']<=1) {
			_prmModulLoad();
			foreach($_CFG['modulprm'] as $k=>$r) {
				if($r['typemodul']<=1 and _prmModul($k,array(1,2))) {
					if(!$r['name'])
						$r['name'] = $k;
					if(!$r['active'])
						$r['name'] = '<span style="color:gray;">'.$r['name'].'</span>';
					$data['sysconf']['item'][$k] = $r['name'];
				}
			}
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
		_prmModulLoad();
		foreach($_CFG['modulprm'] as $k=>$r) {
			if($r['typemodul']==2 and _prmModul($k,array(1,2))) {
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

	if($_SESSION['user']['wep'])
		include($_CFG['_PATH']['cdesign'].$_design.'/inc.php');
	else {
		$_tpl['mess'] = $_CFG['_MESS']['denied'];
		$HTML->_templates = "login";
	}
?>

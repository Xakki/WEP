<?
error_reporting(-1);

	$_CFG['_PATH']['wepconf'] = dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/_wepconf';
	require_once($_CFG['_PATH']['wepconf'].'/config/config.php');
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

	$_design = 'default';
	$HTML = new html($_CFG['PATH']['cdesign'],$_design);

/*ADMIN*/
	function fXmlSysconf(){
		global $_CFG;
        $template = array();
		$template['sysconf']['modul'] = $_GET['_modul'];
		$template['sysconf']['user'] = $_SESSION['user'];
		if($_SESSION['user']['level']<=1) {
			_prmModulLoad();
			$data = array();
			$dir = dir($_CFG['_PATH']['extcore']);
			while (false !== ($entry = $dir->read())) {
				if ($entry!='.' && $entry!='..' && $pos=strpos($entry, '.class')) {
					$entry = _substr($entry, 0, $pos);
					if($entry!='') {
						if(_prmModul($entry,array(1,2))) {
							if(!isset($_CFG['modulprm'][$entry]['name']))
								$data[$entry] = $entry;
							else
								$data[$entry] = $_CFG['modulprm'][$entry]['name'];
						}
					}
				}
			}
			asort($data);
			$dir->close();
			foreach($data as $k=>$r)
				if(_prmModul($k,array(1,2)))
					$template['sysconf']['item'][$k] = $r;
		}
		/*weppages*/
		/*if(isset($_SESSION['user']) and count($_SESSION['user']['weppages'])) {
			foreach($_SESSION['user']['weppages'] as $k=>$r0)
				$template['sysconf']['item'][$k] = $r;
		}*/
		return $template;
	}

	function fXmlModulslist() {
		global $_CFG;
        $template = array();
		$template['modulslist']['modul'] = $_GET['_modul'];
		$template['modulslist']['user'] = $_SESSION['user'];

		$dir = dir($_CFG['_PATH']['ext']);
		while (false !== ($entry = $dir->read())) {
			if ($entry!='.' && $entry!='..' && $pos=strpos($entry, '.class')) {
				$k = _substr($entry, 0, $pos);
				if($k!='') {
					if(_prmModul($k,array(1,2))) {
						_prmModulLoad();
						if(!isset($_CFG['modulprm'][$k]['name']))
							$template['modulslist']['item'][$k] = $k;
						else
							$template['modulslist']['item'][$k] = $_CFG['modulprm'][$k]['name'];
					}
				}
			}
		}
		$dir->close();

		return $template;
	}
/*---------------ADMIN*/

	if($_SESSION['user']['wep'])
		include($_CFG['_PATH']['cdesign'].$_design.'/inc.php');
	else {
		$_tpl['mess'] = $_CFG['_MESS']['denied'];
		$HTML->_templates = "login";
	}
?>

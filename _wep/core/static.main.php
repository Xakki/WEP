<?

class static_main {

	/**
	 * Парсер настроек модулей
	 */
	static function _fParseIni($filename, $form=array()) {
		$dest = $group = "\$data";
		$data = array();
		foreach (file($filename) as $line) {
			$line = trim($line);
			if (preg_match("/^\[(.+)\]( *)/", $line, $regs)) {
				$group = explode(":", $regs[1]);
				$dest = "\$data";
				foreach ($group as $key => $grp)
					$dest .= "[\$group[$key]]";
			} else {
				if (preg_match("/^(\\\"[^\\\"]*\\\"|[^=]*) *= *(\\\"[^\\\"]*\\\"|.*)$/", $line, $regs)) {
					$regs[1] = trim($regs[1], '"');
					$regs[2] = trim($regs[2], '"');
					if (isset($form[$regs[1]]['multiple']) and strpos($regs[2], '|'))
						$regs[2] = explode('|', $regs[2]);
					eval($dest . '[$regs[1]]=$regs[2];');
				}
			}
		}
		return $data;
		//return parse_ini_file($filename,true);
	}

	/**
	 * Вывод названия таблицы у класса , без его подключения,
	 *  главное чтобу в модуле не было указано явно свое название табл
	 */
	static function getTableNameOfClass($name) {
		global $_CFG;
		if (!isset($_CFG['modulprm']))
			self::_prmModulLoad();
		$name = _getExtMod($name);
		if ($_CFG['modulprm'][$name]['tablename'])
			return $_CFG['modulprm'][$name]['tablename'];
		else
			return $_CFG['sql']['dbpref'] . $name;
	}

	static function includeModulFile($Mid, &$OWN=NULL) {
		global $_CFG;
		$Pid = NULL;
		$ret = array('type' => 0, 'path' => '', 'file' => false);
		foreach ($_CFG['modulinc'] as $k => $r) {
			$ret['type'] = $k;
			$ret['path'] = $Mid . '.class/' . $Mid . '.class.php';
			$ret['file'] = $r['path'] . $ret['path'];

			if (is_file($ret['file'])) {
				$ret['path'] = $k . ':' . $ret['path'];
				//include_once($ret['file']);
				return $ret;
			}
			$tempOWN = &$OWN;
			while ($tempOWN and $tempOWN->_cl) {
				$Pid = $tempOWN->_cl;
				$ret['type'] = 5;
				$ret['path'] = $Pid . '.class/' . $Mid . '.childs.php';
				$ret['file'] = $r['path'] . $ret['path'];
				if (is_file($ret['file'])) {
					$ret['path'] = $k . ':' . $ret['path'];
					//include_once($ret['file']);
					return $ret;
				}
				$ret['path'] = $Pid . '.class/' . $Pid . '.class.php';
				$ret['file'] = $r['path'] . $ret['path'];
				if (is_file($ret['file'])) {
					$ret['path'] = $k . ':' . $ret['path'];
					//include_once($ret['file']);
					return $ret;
				}
				$tempOWN = &$tempOWN->owner;
			}
		}
		return array('type' => false, 'path' => false, 'file' => false);
	}

	/*
	  Проверка доступа пол-ля к модулю
	 */

	static function _prmModul($mn, $param=array()) {
		global $_CFG;
		if (isset($_SESSION['user']['id']) and isset($_SESSION['user']['level']) and $_SESSION['user']['level'] == 0)
			return true; // админу можно всё
		if (!isset($_CFG['modulprm']))
			self::_prmModulLoad();
		if (!isset($_CFG['modulprm'][$mn]))
			return false; // отказ, если модуль отключен
		if (isset($_SESSION['user']['level']) and $_SESSION['user']['level'] >= 5)
			return false; //этим всё запрещено
		else {
			if (isset($_CFG['modulprm'][$mn]['access'][0]))
				return false;
			if(isset($_CFG['modulprm'][$mn]['access']['']) and count($_CFG['modulprm'][$mn]['access'])==1)
				return true;
			if (count($param))
				foreach ($param as $r)
					if (isset($_CFG['modulprm'][$mn]['access'][$r]))
						return true;
		}
		return false;
	}

	static function _prmModulLoad() { // подгрука данных прав доступа
		global $_CFG, $SQL;
		if (!isset($_CFG['modulprm'])) {
			$_CFG['modulprm'] = $_CFG['modulprm_ext'] = array();
			$ugroup_id = (isset($_SESSION['user']['gid']) ? (int) $_SESSION['user']['gid'] : 2);
			if(isset($_SESSION['user']['parent_id']) and $_SESSION['user']['parent_id']) {
				$ugroup_id = ' and t2.ugroup_id IN ('.$_SESSION['user']['parent_id'].','.$ugroup_id.')';
			}else
				$ugroup_id = ' and t2.ugroup_id='.$ugroup_id;
			if (!$SQL)
				$SQL = new sql($_CFG['sql']);
			$result = $SQL->execSQL('SELECT t1.*,t2.access, t2.mname FROM `' . $_CFG['sql']['dbpref'] . 'modulprm` t1 LEFT Join `' . $_CFG['sql']['dbpref'] . 'modulgrp` t2 on t2.owner_id=t1.id' . $ugroup_id . ' ORDER BY typemodul,name');
			if ($result->err) {
				//$_POST['sbmt'] = 1;
				//static_tools::_checkmodstruct('modulprm');
				return false;
			}
			$_CFG['modulprm'] = array();
			while ($row = $result->fetch_array()) {
				if ($row['extend'])
					$_CFG['modulprm_ext'][$row['extend']][] = $row['id'];
				if(!isset($_CFG['modulprm'][$row['id']]['access']) or !$_CFG['modulprm'][$row['id']]['access'])
					$_CFG['modulprm'][$row['id']]['access'] = array_flip(explode('|', trim($row['access'], '|')));
				if ($row['mname'])
					$_CFG['modulprm'][$row['id']]['name'] = $row['mname'];
				else
					$_CFG['modulprm'][$row['id']]['name'] = $row['name'];
				$_CFG['modulprm'][$row['id']]['path'] = self::getPathModul($row['path']);
				$_CFG['modulprm'][$row['id']]['active'] = $row['active'];
				$_CFG['modulprm'][$row['id']]['typemodul'] = $row['typemodul'];
				$_CFG['modulprm'][$row['id']]['tablename'] = $row['tablename'];
				$_CFG['modulprm'][$row['id']]['ver'] = $row['ver'];
				$_CFG['modulprm'][$row['id']]['extend'] = $row['extend'];
				$_CFG['modulprm'][$row['id']]['pid'] = $row['parent_id'];
				if ($row['hook']) {
					eval('$hook = ' . $row['hook'] . ';');
					$_CFG['hook'] = self::MergeArrays($_CFG['hook'], $hook);
				}
			}
			/* if (_new_class('modulprm', $MODULs))
			  $_CFG['modulprm'] = $MODULs->userPrm((isset($_SESSION['user']['owner_id']) ? (int) $_SESSION['user']['owner_id'] : 0)); */
		}
		return true;
	}

	/**
	 * Получаем реальный путь из поля path
	 * @param string $path
	 * @return string
	 */
	static function getPathModul($path) {
		global $_CFG;
		if (!$path)
			return '';
		$path = explode(':', $path);
		return $_CFG['modulinc'][$path[0]]['path'] . $path[1];
	}

	/*
	  Проверка доступа пол-ля по уровню привелегии
	 */

	static function _prmUserCheck($level=5) {
		global $_CFG;
		if (isset($_SESSION['user']['id']) and $_SESSION['user']['id']) {
			if (isset($_SESSION['user']['level']) and $_SESSION['user']['level'] <= $level)
				return true;
		}
		return false;
	}

	static function _prmGroupCheck($id=1) {
		global $_CFG;
		if(!is_array($id))
			$id = array($id);
		foreach($id as $r) {
			if (isset($_SESSION['user']['id']) and $_SESSION['user']['id']) {
				if (isset($_SESSION['user']['gid']) and $_SESSION['user']['gid']==$r)
					return true;
			}
		}
		return false;
	}

	/*
	  Ф. авторизации пользователя
	 */

	static function userAuth($login='', $pass='') {
		global $_CFG;
		session_go(1);
		$result = array('', 0);
		if (!isset($_SESSION['user']['id']) or $login) {
			if ($_CFG['wep']['access'] and _new_class('ugroup', $UGROUP)) {
				if (isset($_POST['login']) or $login) {
					$result = $UGROUP->authorization($login, $pass);
				}
				else
					$result = $UGROUP->cookieAuthorization();
			}
			elseif ($_CFG['wep']['login'] and $_CFG['wep']['password']) {
				$flag = 0;
				if ($_COOKIE['remember'] and $_CFG['wep']['login'] == substr($_COOKIE['remember'], ($pos + 1)) and md5($_CFG['wep']['password']) == substr($_COOKIE['remember'], 0, $pos))
					$flag = 1;
				elseif ($login and $pass and $_CFG['wep']['login'] == $login and $_CFG['wep']['password'] == $pass)
					$flag = 1;
				if ($flag) {
					$_SESSION['user']['id'] = 1;
					$_SESSION['user']['name'] = $_CFG['wep']['name'];
					$_SESSION['user']['gname'] = "GOD MODE";
					$_SESSION['user']['level'] = 0;
					$_SESSION['user']['wep'] = 1;
					$_SESSION['user']['gid'] = 1;
					$_SESSION['user']['design'] = $_CFG['wep']['design'];
					$_SESSION['user']['filesize'] = $_CFG['wep']['def_filesize'];
					$_SESSION['FckEditorUserFilesUrl'] = $_CFG['_HREF']['BH'] . $_CFG['PATH']['userfile'];
					$_SESSION['FckEditorUserFilesPath'] = $_CFG['_PATH']['path'] . $_CFG['PATH']['userfile'];
					if ($_POST['remember'] == '1')
						_setcookie('remember', md5($_CFG['wep']['password']) . '_' . $_CFG['wep']['login'], $_CFG['remember_expire']);
					$result = array($_CFG['_MESS']['authok'], 1);
					_setcookie('_showerror', 1);
					//$_COOKIE['_showerror']=1;
				}
			}
		}
		else {
			//if (!$UGROUP)
			//	_new_class('ugroup', $UGROUP);
			$result = array($_CFG['_MESS']['authok'], 1);
		}
		/*if (!$result[1] and isset($_POST['login'])) //вероятно не нужно удалять авторизацию если была не удачная попытка
			self::userExit();*/
		return $result;
	}

	static function userExit() {
		global $_CFG;
		session_go();
		if (isset($_SESSION))
			session_destroy();
		//if(isset($_SESSION))
		//	$_SESSION = array();
		if (isset($_COOKIE['_showallinfo']))
			_setcookie('_showallinfo', '', (time() - 5000));
		if (isset($_COOKIE['_showerror']))
			_setcookie('_showerror', '', (time() - 5000));
		if (isset($_COOKIE['remember']))
			_setcookie('remember', '', (time() - 5000));
		if (isset($_COOKIE[$_CFG['session']['name']]))
			_setcookie($_CFG['session']['name'], '', (time() - 5000));
		//_showerror
		//
	}

	static function _message($type,$msg,$cl='') {
		global $_CFG;
		$ar_type = array('error'=>false, 'alert'=>true, 'notice'=>true, 'ok'=>true);
		$_CFG['logs']['mess'][] = array($type,$msg,$cl);
		return $ar_type[$type];
	}

	static function okr($x, $y) {
		$z = pow(10, $y);
		return $z * round($x / $z);
	}

	static function insertInArray($data, $afterkey, $insert_data) {
		$output = array();
		if (count($data)) {
			foreach ($data as $k => $r) {
				$output[$k] = $r;
				if ($k == $afterkey) {
					//$output = array_merge($output,$insert_data);
					$output = $output + $insert_data;
				}
			}
			return $output;
		}
		return $insert_data;
	}

	static function MergeArrays($Arr1, $Arr2) {
		foreach ($Arr2 as $key => $Value) {
			if (array_key_exists($key, $Arr1) && is_array($Value) && is_array($Arr1[$key])) {
				$Arr1[$key] = self::MergeArrays($Arr1[$key], $Value);
			}
			else
				$Arr1[$key] = $Value;
		}
		return $Arr1;
	}

	static function relativePath($file) {
		$file = str_replace(array('\\\\','\\'),'/',$file);
		$cf = dirname(dirname(__DIR__));
		$cf = str_replace(array('\\\\','\\'),'/',$cf);
		$file = str_replace(array($_SERVER['_DR_'],$cf),'',$file);
		return $file;
	}

	static function pre_text($text, $col, $clearFormat = true) {
		if ($clearFormat)
			$text = strip_tags($text);
		if (mb_strlen($text) >= $col)
		{
			$length = mb_strripos(mb_substr($text, 0, $col), ' ');
			$text = mb_substr($text, 0, $length).'...';
		}
		return $text;
	}
}

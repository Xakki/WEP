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
		if ($_CFG['modulprm'][$name]['tablename'])
			return $_CFG['modulprm'][$name]['tablename'];
		else
			return $_CFG['sql']['dbpref'] . $name;
	}

	/*
	  Проверка доступа пол-ля к модулю
	 */

	static function _prmModul($mn, $param=array()) {
		global $_CFG;
		if (!isset($_CFG['modulprm']))
			self::_prmModulLoad();
		if (!isset($_CFG['modulprm'][$mn]))
			return false; // отказ, если модуль отключен
 if (isset($_SESSION['user']['level']) and $_SESSION['user']['level'] == 0)
			return true; // админу можно всё
 if ($_SESSION['user']['level'] >= 5)
			return false; //этим всё запрещено
 else {
			if (isset($_CFG['modulprm'][$mn]['access'][0]))
				return false;
			if (count($param))
				foreach ($param as $r)
					if (isset($_CFG['modulprm'][$mn]['access'][$r]))
						return true;
		}
		return false;
	}

	static function _prmModulLoad() { // подгрука данных прав доступа
		global $_CFG;
		if (!isset($_CFG['modulprm'])) {
			if (_new_class('modulprm', $MODULs))
				$_CFG['modulprm'] = $MODULs->userPrm((isset($_SESSION['user']['owner_id']) ? (int) $_SESSION['user']['owner_id'] : 0));
		}
	}

	/*
	  Проверка доступа пол-ля по уровню привелегии
	 */

	static function _prmUserCheck($level=5) {
		global $_CFG;
		if (isset($_SESSION['user']['level'])) {
			if ($_SESSION['user']['level'] <= $level)
				return true;
		}
		return false;
	}

	/*
	  Ф. авторизации пользователя
	 */

	static function userAuth($login='', $pass='') {
		global $_CFG, $SQL, $UGROUP;
		session_go(1);
		$result = array('', 0);
		if (!isset($_SESSION['user']['id']) or $login) {
			//$SQL->_iFlag = 1; // проверка табл
			//if($SQL->_iFlag) _new_class('modulprm',$MODULtemp);
			if ($_CFG['wep']['access'] and _new_class('ugroup', $UGROUP)) {
				if (isset($_POST['login']) or $login) {
					$result = $UGROUP->childs['users']->authorization($login, $pass);
				}
				else
					$result = $UGROUP->childs['users']->cookieAuthorization();
			}
			elseif ($_CFG['wep']['login'] and $_CFG['wep']['password']) {
				$flag = 0;
				if ($_COOKIE['remember'] and $_CFG['wep']['login'] == substr($_COOKIE['remember'], ($pos + 1)) and md5($_CFG['wep']['password']) == substr($_COOKIE['remember'], 0, $pos))
					$flag = 1;
				elseif ($login and $pass and $_CFG['wep']['login'] == $login and $_CFG['wep']['password'] == $pass)
					$flag = 1;
				if ($flag) {
					$_SESSION['user']['name'] = $_CFG['wep']['name'];
					$_SESSION['user']['gname'] = "GOD MODE";
					$_SESSION['user']['id'] = 0;
					$_SESSION['user']['level'] = 0;
					$_SESSION['user']['wep'] = 1;
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
			if (!$UGROUP)
				_new_class('ugroup', $UGROUP);
			$result = array($_CFG['_MESS']['authok'], 1);
		}
		if (!$result[1])
			self::userExit();
		return $result;
	}

	static function userExit() {
		global $_CFG;
		session_go();
		if (isset($_SESSION))
			session_destroy();
		//if(isset($_SESSION))
		//	$_SESSION = array();
		if (isset($_COOKIE['remember']))
			_setcookie('remember', '', (time() - 5000));
		if (isset($_COOKIE[$_CFG['session']['name']]))
			_setcookie($_CFG['session']['name'], '', (time() - 5000));
		//_showerror
		//
	}

	static function _message($msg, $type=5) {
		global $_CFG;
		$ar_type = array(0 => E_USER_ERROR, 1 => E_USER_WARNING, 2 => E_USER_NOTICE, 3 => 'modify', 4 => 'notify', 5 => 'ok');
		if ($type < 3)
			trigger_error($msg, $ar_type[$type]);
		elseif ($_COOKIE['_showallinfo'] > 1)
			$_CFG['logs']['mess'][] = array($msg, $ar_type[$type]);
		if ($type < 2)
			return false;
		return true;
	}

	/*
	  Функция SpiderDetect - принимает $_SERVER['HTTP_USER_AGENT'] и возвращает имя кравлера поисковой системы или false.
	 */

	static function SpiderDetect($USER_AGENT='') {
		if (!$USER_AGENT)
			$USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
		$engines = array(
			array('Aport', 'Aport robot'),
			array('Google', 'Google'),
			array('msnbot', 'MSN'),
			array('Rambler', 'Rambler'),
			array('Yahoo', 'Yahoo'),
			array('AbachoBOT', 'AbachoBOT'),
			array('accoona', 'Accoona'),
			array('AcoiRobot', 'AcoiRobot'),
			array('ASPSeek', 'ASPSeek'),
			array('CrocCrawler', 'CrocCrawler'),
			array('Dumbot', 'Dumbot'),
			array('FAST-WebCrawler', 'FAST-WebCrawler'),
			array('GeonaBot', 'GeonaBot'),
			array('Gigabot', 'Gigabot'),
			array('Lycos', 'Lycos spider'),
			array('MSRBOT', 'MSRBOT'),
			array('Scooter', 'Altavista robot'),
			array('AltaVista', 'Altavista robot'),
			array('WebAlta', 'WebAlta'),
			array('IDBot', 'ID-Search Bot'),
			array('eStyle', 'eStyle Bot'),
			array('Mail.Ru', 'Mail.Ru Bot'),
			array('Scrubby', 'Scrubby robot'),
			array('Yandex', 'Yandex'),
			array('YaDirectBot', 'Yandex Direct'),
			array('Bot', 'Bot')
		);

		foreach ($engines as $engine) {
			if (stristr($USER_AGENT, $engine[0])) {
				return $engine[1];
			}
		}

		return '';
	}

}

?>
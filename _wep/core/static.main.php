<?php
/*
  Функция завершения работы скрипта
 */
include $_CFG['_PATH']['core'] . 'observer.php';
function shutdown_function() {
	observer::notify_observers('shutdown_function');
}
register_shutdown_function('shutdown_function'); // Запускается первым при завершении скрипта

		ini_set("max_execution_time", "10");
		set_time_limit(10);

class static_main {

	/**
	* В формат выода сообщения 
	*/
	static function am($type,$msg, $replace=array(), $obj=NULL) {
		return  array($type,self::m($msg, $replace, $obj));
	}
	static function m($msg, $replace=array(), $obj=NULL) {
		global $_CFG;
		if(is_object($replace)) {
			$obj = $replace;
			$replace=array();
		}
		if ($obj and isset($obj->lang[$msg]))
			$msg = $obj->lang[$msg];
		elseif (isset($_CFG['lang'][$msg]))
			$msg = $_CFG['lang'][$msg];
		if (is_array($replace) and count($replace))
			foreach ($replace as $k => $r)
				$msg = str_replace('###' . ($k + 1) . '###', $r, $msg);
		elseif(!is_array($replace) and $replace)
			$msg .= $replace;
		return $msg;
	}

	/* Запись сообщения в лог вывода
	*/
	static function log($type,$msg,$cl='') {
		global $_CFG;
		$ar_type = array('error'=>false, 'alert'=>true, 'notice'=>true, 'ok'=>true);
		if(!$ar_type[$type]) {
			trigger_error($msg, E_USER_WARNING);
			if($_CFG['wep']['debugmode']>2)
				$_CFG['logs']['mess'][] = array($type,$msg,$cl);
		}
		return $ar_type[$type];
	}

	/**
	* вывод лога
	* $type = 0 - все
	* 1 - кроме 'ok'
	* 2 - кроме 'ok', 'modify'
	* 3 - только 'error'
	*/
	static function showLog($type=0) {
		global $_CFG;
		$text = '';
		$flag = true;
		if (isset($_CFG['logs']['mess']) and count($_CFG['logs']['mess'])) {
			foreach ($_CFG['logs']['mess'] as $r) {
				//$c = '';
				if ($r[0] == 'error') {
					//$c = 'red';
					$flag = false;
				}/* elseif ($r[1] == 'warning' and $type < 3)
					$c = 'yellow';
				elseif ($r[1] == 'modify' and $type < 2)
					$c = 'blue';
				elseif ($r[1] == 'ok' and $type < 1)
					$c = 'green';
				elseif ($type < 3)
					$c = 'gray';
				if ($c != '')*/
				$text .='<div class="messelem '.$r[0].'">' . htmlspecialchars($r[1], ENT_QUOTES, $_CFG['wep']['charset']). '</div>';
			}
			$_CFG['logs']['mess'] = array();
		}
		return array($text, $flag);
	}

	/**
	*
	*/
	static function showErr() {
		global $_CFG, $SQL;
		$temp = static_main::showLog(); // сообщения ядра
		if($temp[0])
				$temp[0] = self::spoilerWrap('Сообщения ядра',$temp[0]);
		$notice = '';
		$htmlerr = '';
		/*Вывод ошибок*/
		if(count($GLOBALS['_ERR'])) {
			if($_CFG['wep']['debugmode']==5) {
				return var_export($GLOBALS['_ERR'],true);
			} else {
				foreach ($GLOBALS['_ERR'] as $err) foreach ($err as $r) {
					$var = $r['errtype'] . ' ' . $r['errstr'] . ' , in line ' . $r['errline'] . ' of file <i>' . $r['errfile'] . '</i>';
					if($r['debug']) //$r['errcontext']
						$var = self::spoilerWrap($var,$r['debug'],'bug_'.$r['errno']);
					else
						$var = '<div class="bug_'.$r['errno'].'">'.$var.'</div>';
					$var .= "\n";
					if ($_CFG['_error'][$r['errno']]['prior'] <= 3)
						$htmlerr .= $var;
					else //нотисы отдельно
						$notice .= $var;
				}
			}
		}

		if ($_CFG['wep']['debugmode'] >1 and ($htmlerr != '' or $notice != '' or $temp[0])) {
			if ($notice)
				$htmlerr .= self::spoilerWrap('NOTICE',$notice);
			if ($temp[0])
				$htmlerr .= $temp[0];//self::spoilerWrap('MESSAGES',$temp[0]);
		}
		elseif ($_CFG['wep']['debugmode'] == 1 and ($htmlerr != '' or !$temp[1])){
			$htmlerr = 'На странице возникла ошибка! Приносим свои извинения за временные неудобства! Неполадки будут исправлены в ближайшее время.';
		}
		else {
			$htmlerr = '';
		}
		return $htmlerr;
	}


	static function spoilerWrap($head,$text,$css='') {
		global $_CFG;
		$hash = md5($head);
		return '<div class="bugspoiler-wrap '.$css.'"><div class="spoiler-head" onclick="var obj=this.parentNode;if(obj.className.indexOf(\'unfolded\')>=0) obj.className = obj.className.replace(\'unfolded\',\'\'); else obj.className = obj.className+\' unfolded\';">'.$head.'</div><div class="spoiler-body">' . html_entity_decode($text,ENT_QUOTES,$_CFG['wep']['charset']). '</div></div>';
	}
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
	* Шифрование и дешифрование данных
	*
	*/
	function EnDecryptString( $str, $hashKey=null )
	{
		if(is_null($hashKey)) {
			global $_CFG;
			if(isset($_CFG['HASH_KEY']))
				$hashKey = $_CFG['HASH_KEY'];
			else
				$hashKey = $_CFG['HASH_KEY'] = file_get_contents($_CFG['_FILE']['HASH_KEY']);
		}
		$hashKeyLen = mb_strlen( $hashKey );
		$strLen = mb_strlen( $str );
		for ( $i = 0; $i < $strLen; $i++ )
		{
			$pos = $i % $hashKeyLen;// Если  строка длиннее ключа
			$r = ord( $str[$i] ) ^ ord( $hashKey[$pos] ); // Побитовый XOR ASCII-кодов символов
			$str[$i] = chr($r); // соответствующий полученному ASCII-коду 
		}
		 return $str;
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

	/**
	 * подгрука данных прав доступа и пути подключения модулей
	 * @return bool
	 */
	static function _prmModulLoad() {
		global $_CFG, $SQL;
		if (!isset($_CFG['modulprm'])) {
			session_go();
			$temp = NULL;
			_new_class('modulprm', $MODULPRM,$temp, true);
			$_CFG['modulprm'] = $_CFG['modulprm_ext'] = array();
			$ugroup_id = (isset($_SESSION['user']['gid']) ? (int) $_SESSION['user']['gid'] : $_CFG['wep']['guestid']);
			// Если есть таблица
			if($MODULPRM->SQL->_tableExists($MODULPRM->tablename)) {
				if(isset($_SESSION['user']['parent_id']) and $_SESSION['user']['parent_id']) {
					$ugroup_id = ' and t2.ugroup_id IN ('.$_SESSION['user']['parent_id'].','.$ugroup_id.')';
				}else
					$ugroup_id = ' and t2.ugroup_id='.$ugroup_id;
				$q = 'SELECT t1.*,t2.access, t2.mname FROM `' . $MODULPRM->tablename . '` t1 LEFT Join `' . $MODULPRM->childs['modulgrp']->tablename . '` t2 on t2.owner_id=t1.id' . $ugroup_id . ' ORDER BY t1.typemodul,t1.name';
				$result = $MODULPRM->SQL->execSQL($q);
				if ($result->err) {
					//$_POST['sbmt'] = 1;
					//static_tools::_checkmodstruct('modulprm');
					return false;
				}
				$_CFG['modulprm'] = array();
				while ($row = $result->fetch()) {
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
						if($hook and is_array($hook) and count($hook))
							$_CFG['hook'] = self::MergeArrays($_CFG['hook'], $hook);
					}
				}
			} else {
				// TODO
			}
			/* if (_new_class('modulprm', $MODULs))
			  $_CFG['modulprm'] = $MODULs->userPrm((isset($_SESSION['user']['owner_id']) ? (int) $_SESSION['user']['owner_id'] : 0)); */
		}
		return true;
	}

	/**
	 * Получаем реальный путь из поля path для PG
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

	/**
	 * Проверка доступа пол-ля по уровню привелегии
	 * @param int $level - level пользователя
	 * @return bool
	 */
	static function _prmUserCheck($level=5) {
		global $_CFG;
		//session_go(); // TEST
		if (isset($_SESSION['user']['id']) and $_SESSION['user']['id']) {
			if (isset($_SESSION['user']['level']) and $_SESSION['user']['level'] <= $level)
				return true;
		}
		return false;
	}

	/**
	 * Проверка доступа пользователя по её группе
	 * @param int $id - id группы
	 * @return bool
	 */
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

	/**
	 * авторизации пользователя по входнным данным либо по кукам
	 * @param string $login - логин или емал
	 * @param string $pass - пароль
	 * @return array 0=>текст сообщения , 1=> статус
	 */
	static function userAuth($login='', $pass='') {
		global $_CFG;
		session_go();// запускаем сессию, чтоб проверить авторизован ли пользователь
		$result = array('', 0);
		if (!self::_prmUserCheck() or $login) {
			if ($_CFG['wep']['access']) {
				if ($login) {
					if(_new_class('ugroup', $UGROUP))
						$result = $UGROUP->authorization($login, $pass);
					else
						$result[0] = 'Ugroup modul is off';
				}
				elseif(!self::_prmUserCheck() and isset($_COOKIE['remember'])) {
					if (preg_match("/^[0-9A-Za-z\_]+$/",$_COOKIE['remember'])) {
						if(_new_class('ugroup', $UGROUP))
							$result = $UGROUP->cookieAuthorization();
						else
							$result[0] = 'Ugroup modul is off';
					}
				}
			}
			elseif ($_CFG['wep']['login'] and $_CFG['wep']['password']) {
				// Авторизация без использования БД , логин и пароль берутся из конфига
				$flag = 0;
				
				if (isset($_COOKIE['remember']) and $_COOKIE['remember']) {
					$pos = strpos($_COOKIE['remember'],'_');
					if($_CFG['wep']['login'] == substr($_COOKIE['remember'], ($pos + 1)) and md5($_CFG['wep']['md5'].$_CFG['wep']['password']) == substr($_COOKIE['remember'], 0, $pos))
						$flag = 1;
				}
				elseif ($login and $pass and $_CFG['wep']['login'] == $login and $_CFG['wep']['password'] == $pass)
					$flag = 1;
				if ($flag) {
					session_go(true);// принудительный запуск сессия для пользователя
					$_SESSION['user']['id'] = 1;
					$_SESSION['user']['name'] = $_CFG['wep']['login'];
					$_SESSION['user']['gname'] = "GOD MODE";
					$_SESSION['user']['level'] = 0;
					$_SESSION['user']['wep'] = 1;
					$_SESSION['user']['gid'] = 1;
					$_SESSION['user']['design'] = $_CFG['wep']['design'];
					$_SESSION['user']['filesize'] = $_CFG['wep']['def_filesize'];
					$_SESSION['FckEditorUserFilesUrl'] = $_CFG['_HREF']['BH'] . $_CFG['PATH']['userfile'];
					$_SESSION['FckEditorUserFilesPath'] = $_CFG['_PATH']['path'] . $_CFG['PATH']['userfile'];
					if (isset($_POST['remember']) and $_POST['remember'] == '1')
						_setcookie('remember', md5($_CFG['wep']['md5'].$_CFG['wep']['password']) . '_' . $_CFG['wep']['login'], $_CFG['remember_expire']);
					$result = array(static_main::m('authok'), 1);
					//_setcookie($_CFG['wep']['_showerror'], 2);
					//$_COOKIE['_showerror']=1;
				}
			}
		}
		else {
			//if (!$UGROUP)
			//	_new_class('ugroup', $UGROUP);
			$result = array(static_main::m('authok'), 1);
		}
		/*if (!$result[1] and isset($_POST['login'])) //вероятно не нужно удалять авторизацию если была не удачная попытка
			self::userExit();*/
		return $result;
	}

	/**
	 * Закрытие сессии пользователя
	 * @return void
	 */
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
	}

	/**
	* Получить ID пользователя или зарегить как анонима
	*
	*/
	static function userId($force=false) {
		session_go();
		if(isset($_SESSION['user']['id']))
			return $_SESSION['user']['id'];
		elseif($force) {
			//TODO : Создаем пользователя  гостя
		}
		return null;
	}

/*Функции вспомогательные*/

	/**
	 * Вставка массива , после указанного ключа
	 * @param array $data - Массив в который будет вставляться $insert_data
	 * @param value $afterkey - ключ массива $data, после которого будет вставлен массив $insert_data
	 * @param array $insert_data - вставляемый массив
	 * @return array
	*/
	static function insertInArray(array $data, $afterkey, array $insert_data) {
		$output = array();
		if(!is_array($insert_data)) {
			trigger_error('Не верный переданный 3тий аргумент $insert_data, должен быть массив.', E_USER_WARNING);
			return $data;
		}
		elseif (count($data)) {
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

	/**
	 * Рекурсивное слияние 2х многомерных массивов
	 * @param array $Arr1 - 1ый массив
	 * @param array $Arr2 - 2ой массив
	 * @return array
	*/
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

	/**
	 * ИЗ полного(абсолютного) пути к фаилу получаем относительный путь с корня сайта
	 * @param string $file - абсолютный путь к фаилу
	 * @return string относительный путь к фаилу	
	*/
	static function relativePath($file) {
		global $_CFG;
		$file = str_replace(array('\\\\','\\'),'/',$file);
		$cf = $_CFG['_PATH']['path'];
		$cf = str_replace(array('\\\\','\\'),'/',$cf);
		$cf2 = $_CFG['_PATH']['_path'];
		$cf2 = str_replace(array('\\\\','\\'),'/',$cf2);
		$file = str_replace(array($_SERVER['_DR_'],$cf,$cf2),'',$file);
		return $file;
	}

	/**
	 * Обрезание текста по длине , оставляя максимум целых слов.
	 * @param string $text - текст
	 * @param int $col - максим длина строки
	 * @param bool $clearFormat - чистка строки от тегов
	 * @return string обрезанный текст
	*/
	static function pre_text($text, $col, $clearFormat = true) {
		if ($clearFormat) {
			//temp
			$text = html_entity_decode($text,ENT_QUOTES,'UTF-8');
			if($clearFormat===2) // TEMP : 
				$text = str_replace(array('.<br />',',<br />','<br />'),array('. ',', ','. '),$text);
			$text = trim(strip_tags($text),"\s\t\r\n\0\x0B");// \xA0 из за него кавычки и пробелы тупят
		}
		if (mb_strlen($text) > $col)
		{
			$length = mb_strripos(mb_substr($text, 0, $col), ' ');
			$text = mb_substr($text, 0, $length).'...';
		}
		return $text;
	}

	/**
	 * Замена в тексте ссылок на редирект
	 * @param string $text - текст в котором будет производится поиск
	 * @param int $name - подстановочное название ссылок, если $name==false - то название будет как самы ссылка только без http:// и www
	 * @param int $dolink - 0 - замена всех http на редирект и превращение в ссылки; 1- замена всех http на редирект; 2- замена всех http на редирект в ссылках
	 * @return string текст
	*/
	static function redirectLink($text,$name='Источник',$dolink=0) {
		global $_CFG;

		$cont = array();
		if($dolink==2)
			$match = '/(href=")(http:\/\/|https:\/\/|www\.)[0-9A-Za-zА-Яа-я\/\.\_\-\=\?\&\;]*/u';
		else
			$match = '/(href="|=")?(http:\/\/|https:\/\/|www\.)[0-9A-Za-zА-Яа-я\/\.\_\-\=\?\&\;]*/u';
		preg_match_all($match,$text,$cont);
		if(count($cont[0])) {
			$temp = array();
			foreach($cont[0] as $rc) {
				if(mb_substr($rc,0,2)=='="') {
					$temp[] = $rc;continue;
				}
				
				if(mb_strpos($rc,'href="')!==false)
					$temp[] = 'rel="nofollow" target="_blank" href="'.$_CFG['_HREF']['BH'].'_redirect.php?url='.base64encode(str_replace('href="','',$rc));
				elseif($dolink==0) {
					if(!$name)
						$tn = trim(str_replace(array('href="','http://','https://','www.'),'',$rc),' /');
					else
						$tn = $name;
					$temp[] = '<a href="'.$_CFG['_HREF']['BH'].'_redirect.php?url='.(base64encode($rc)).'" rel="nofollow" target="_blank">'.$tn.'</a>';
				}
				else
					$temp[] = $_CFG['_HREF']['BH'].'_redirect.php?url='.(base64encode($rc));
			}
			$text = str_replace($cont[0],$temp,$text);
		}
		return $text;
	}

	static function redirect($link=true,$NO=false) {
		global $_CFG;
		// TODO : Проверка на зацикленный редирект
		//301 - перемещение на посточнную основу
		/*if($_SERVER['HTTP_REFERER']==$link) {
			header("HTTP/1.0 400 Bad Request");
			//301 Moved Permanently
			die('Warning!!! Self redirect for <a href="'.$link.'">'.$link.'</a>');
		}
		else*/
		if($link===true)
			$link = $_SERVER['HTTP_PROTO'].$_SERVER['HTTP_HOST'].'/'.$_SERVER['REQUEST_URI'];
		
		if($_CFG['wep']['debugmode']<3) {
			if($NO!==false)
				header('HTTP/1.1 '.$NO);
			header("Location: ".$link);
			die($link);
		} else {
			die('Redirect to <a href="'.$link.'">'.$link.'</a>');
		}
	}
	/**
	 * Преобразование массива данных в XML формат
	 * @param array $DATA - путь
	 * @param strin $f - название тега (по умолч item) 
	 * @return string XML
	*/
	static function kData2xml($DATA, $f='item') {
		$XML = '';
		if ($f) {
			$f = str_replace('#', '', $f);
			$attr = '';
			$value = '';
			if (is_array($DATA)) {
				if (is_int(key($DATA))) {
					foreach ($DATA as $k => $r) {
						$attr = '';
						$value = '';
						if (is_array($r)) {
							foreach ($r as $m => $d) {
								if (is_array($d))
									$value .= self::kData2xml($d, $m);
								elseif ($m == 'value')
									$value .= $d;
								elseif ($m == 'name')
									$value .= '<name><![CDATA[' . $d . ']]></name>';
								else
									$attr .= ' ' . str_replace('#', '', $m) . '="' . $d . '"';
							}
						}
						else
							$value = $r;
						$XML .= '<' . $f . $attr . '>' . $value . '</' . $f . ">\n";
					}
					//$XML = '<'.$f.$attr.'>'.$value.'</'.$f.'>';
				}
				else {
					foreach ($DATA as $k => $r) {
						if (is_array($r)) {
							$value .= self::kData2xml($r, $k);
						} elseif ($k == 'value')
							$value .= $r;
						elseif ($k == 'name')
							$value .= '<name><![CDATA[' . $r . ']]></name>';
						else
							$attr .= ' ' . str_replace('#', '', $k) . '="' . $r . '"';
					}
					$XML = '<' . $f . $attr . '>' . $value . '</' . $f . '>';
				}
			}
		}
		return $XML;
	}


	/**
	 * FrontEnd  - Форматирует дату в человеческий вид
	 * @param int $time - время
	 * @param string $format - форматирование
	 * @return string - Дата
	 */
	static function _usabilityDate($time, $format='Y-m-d H:i') {
		global $_CFG;
		$date = getdate($time);
		$de = $_CFG['time'] - $time;
		if ($de < 3600) {
			if ($de < 240) {
				if ($de < 60)
					$date = 'Минуту назад';
				else
					$date = ceil($de / 60) . ' минуты назад';
			}
			else
				$date = ceil($de / 60) . ' минут назад';
		}
		elseif ($_CFG['getdate']['year'] == $date['year'] and $_CFG['getdate']['yday'] == $date['yday'])
			$date = 'Сегодня ' . date('H:i', $time);
		elseif ($_CFG['getdate']['year'] == $date['year'] and $_CFG['getdate']['yday'] - $date['yday'] == 1)
			$date = 'Вчера ' . date('H:i', $time);
		else
			$date = date($format, $time);
		return $date;
	}

	static function _date($format=NULL,$time=NULL) {
		global $_CFG;
		if(is_null($format)) $format = $_CFG['wep']['dateformat'];
		if(is_null($time)) $time = time();
		$date = date($format, $time);
		if(strpos($format,'F')!==false)
			$date = str_replace(array_keys($_CFG['lang']['rumonth']),$_CFG['lang']['rumonth'],$date);
		return $date;
	}

	static function padeji($txt) {
		$lw = mb_substr($txt,-1);
		$ArW = array('а'=>1,''=>1,''=>1,''=>1,''=>1,''=>1,);
		return $txt;
	}

	static function downSite($title=false,$text=false) {
		global $_CFG;
		header('HTTP/1.1 503 Service Unavailable');
		if($title)
			$_CFG["site"]["work_title"] = $title;
		elseif(!isset($_CFG["site"]["work_title"]) or !$_CFG["site"]["work_title"])
			$_CFG["site"]["work_title"] = 'Технический перерыв.';

		if($text)
			$_CFG["site"]["work_text"] = $text;
		elseif(!isset($_CFG["site"]["work_text"]) or !$_CFG["site"]["work_text"])
			$_CFG["site"]["work_text"] = 'Ушёл на базу.';

		if(file_exists($_CFG['_PATH']['phpscript'].'/frontend/work.html'))
			$html = file_get_contents($_CFG['_PATH']['phpscript'].'/work.html');
		else
			$html = file_get_contents($_CFG['_PATH']['wep_phpscript'].'/frontend/work.html');
		$html = str_replace('"', '\"', $html);
		eval('$html = "' .$html . '";');
		echo $html;
		exit();
	}

}



/* SESSION */

function session_go($force=false) { //$force=true - открывает сесиию для не авторизованного пользователя
	if(isset($_SESSION)) return true;
	global $_CFG, $SESSION_GOGO;
	if (!$_CFG['robot'] and (isset($_COOKIE[$_CFG['session']['name']]) or $force)) {
		if($_CFG['wep']['sessiontype'] == 1) {
			if(!$SESSION_GOGO){
				include_once $_CFG['_PATH']['wep_ext'] . 'session.class/session.class.php';
				$SESSION_GOGO = new session_class();
			}
			$SESSION_GOGO->start($force);
		}else {
			session_start();
		}
		return true;
	}
	return false;
}

function _setcookie($name, $value='', $expire='', $path='', $domain='', $secure='') {
	global $_CFG;
	if ($expire == '')
		$expire = $_CFG['session']['expire'];
	if ($path == '')
		$path = $_CFG['session']['path'];
	if ($domain == '')
		$domain = $_CFG['session']['domain'];
	if ($secure == '')
		$secure = $_CFG['session']['secure'];
	setcookie($name, $value, $expire, $path, $domain, $secure);
	$_COOKIE[$name] = $value;
}

/**
 * Инициализация модулей
 */
function _new_class($name, &$MODUL, $OWNER=NULL, $_forceLoad = false) {
	global $_CFG;
	$MODUL = NULL;
	if(is_bool($OWNER)) {
		$_forceLoad = $OWNER;
		$OWNER=NULL;
	}
	if(!$_forceLoad) static_main::_prmModulLoad();
	$name = _getExtMod($name);
	
	if (isset($_CFG['singleton'][$name])) {
		$MODUL = $_CFG['singleton'][$name];
		return true;
	}
	elseif(is_null($OWNER) and isset($_CFG['modulprm'][$name]) and $_CFG['modulprm'][$name]['pid']) {
		// кастыль: при обращении к дочерним классам , находяться родители и от него дается ссылка на класс.
		_new_class($_CFG['modulprm'][$name]['pid'], $MODUL2);
		$MODUL = $MODUL2->childs[$name];
		return true;
	}
	else {
		$class_name = $name . "_class";

		if(!class_exists($class_name,false)) {

			if(isset($_CFG['modulprm'][$name]) and $_CFG['modulprm'][$name]['active'])
				$_forceLoad = true;

			if($_forceLoad and $file = _modulExists($class_name, $OWNER)) {
				require_once($file);
			}
		}

		if(class_exists($class_name,false)) {
			$getparam = array_slice(func_get_args(), 2);
			try {
				$ReflectedClass = new ReflectionClass($class_name);
				//$pClass = $ReflectedClass->getParentClass();
				$MODUL = $ReflectedClass->newInstanceArgs((array)$getparam);
				/* extract($getparam,EXTR_PREFIX_ALL,'param');
				  if(count($getparam)) {
				  $p = '$param'.implode(',$param',array_keys($getparam)).'';
				  } else $p = '';
				  eval('$MODUL = new '.$class_name.'('.$p.');'); */
			} catch (LogicException $Exception) {
				die('Not gonna make it in here...');
			} catch (ReflectionException $Exception) {
				die('Your class does not exist!');
			}
			if ($MODUL and is_object($MODUL))
				return true;
		}
		elseif (!isset($_CFG['modulprm'][$name]) or !$_CFG['modulprm'][$name]['active']) 
			return false;
		elseif (isset($_CFG['modulprm'][$name]) and $_CFG['modulprm'][$name]['pid']) {

			$moduls = array($name);
			while ($_CFG['modulprm'][$name]['pid'])
			{
				$moduls[] = $_CFG['modulprm'][$name]['pid'];
				$name = $_CFG['modulprm'][$name]['pid'];
				if(!$_CFG['modulprm'][$name]['active']) return false;
			}

			$cnt = count($moduls);

			_new_class($moduls[$cnt-1], $MODUL);

			for ($i=$cnt-2; $i>=0; $i--)
			{
				$MODUL = $MODUL->childs[$moduls[$i]];
			}
			return true;
		}
		else{
			trigger_error('Can`t init `' . $class_name . '` modul ', E_USER_WARNING);
		}
	}
	return false;
}

function _getChildModul($name, &$MODUL) {
	global $_CFG;

	static_main::_prmModulLoad();
	if (isset($_CFG['modulprm'][$name]['pid']) && $_CFG['modulprm'][$name]['pid'] != '')
	{
		$moduls = array($name);
		while (isset($_CFG['modulprm'][$name]['pid']) && $_CFG['modulprm'][$name]['pid'] != '')
		{
			$moduls[] = $_CFG['modulprm'][$name]['pid'];
			$name = $_CFG['modulprm'][$name]['pid'];
		}

		$cnt = count($moduls);

		_new_class($moduls[$cnt-1], $MODUL);
		for ($i=$cnt-2; $i>=0; $i--)
		{
			$MODUL = $MODUL->childs[$moduls[$i]];
		}
	}
	else
	{
		_new_class($name, $MODUL);
	}
	if ($MODUL)
		return true;
}

function _getExtMod($name) {
	global $_CFG;
	//$this->mf_actctrl
	if (isset($_CFG['modulprm_ext'][$name]) && isset($_CFG['modulprm'][$name]) && !$_CFG['modulprm'][$name]['active'])
		$name = $_CFG['modulprm_ext'][$name][0];
	return $name;
}
/*
  Автозагрузка модулей
 */

function __autoload($class_name) { //автозагрузка модулей
	if ($file = _modulExists($class_name)) {
		require_once($file);
	}
	if(!class_exists($class_name,false)) {
		trigger_error('Can`t init `'.$class_name.'` modul ', E_USER_WARNING);
		//throw new Exception('Can`t init `' . $class_name . '` modul ');
	}
}

/**
 * Проверка существ модуля
 *
 * Осторожно! Тут хитрая-оптимизированная логика
 * @global array $_CFG
 * @param string $class_name
 * @return string
 */
function _modulExists($class_name, &$OWNER=NULL) {
	global $_CFG;
	$class_name = explode('_', $class_name);

	if (isset($_CFG['modulprm'][$class_name[0]])) {
		$file = $_CFG['modulprm'][$class_name[0]]['path'];
		if ($file and file_exists($file))
			return $file;
	}
	
	$file = $_CFG['_PATH']['core'] . $class_name[0] . (isset($class_name[1]) ? '.' . $class_name[1] : '') . '.php';
	if (file_exists($file))
		return $file;

	$ret = includeModulFile($class_name[0], $OWNER);
	return $ret['file'];
}


	/**
	* Нахождение фаила содержащего класс модуля
	* @Mid - модуль
	*/
	function includeModulFile($Mid, &$OWN=NULL) {
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
			if(!is_null($OWN)) {
				$tempOWN = &$OWN;
				while (!is_null($tempOWN) and $tempOWN->_cl) {
					$Pid = $tempOWN->_cl;
					$ret['type'] = 5;

					$ret['path'] = $Pid . '.class/' . $Mid . '.childs.php';
					$ret['file'] = $r['path'] . $ret['path'];
					if (is_file($ret['file'])) {
						$ret['path'] = $k . ':' . $ret['path'];
						//include_once($ret['file']);
						return $ret;
					}

					$ret['path'] = $Pid . '.class/' . $Pid . '.childs.php';
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
		}
		return array('type' => false, 'path' => false, 'file' => false);
	}

	// получить путь к PHP фаилам сторонних библиотек
	function getLib($name) {
		global $_CFG;
		$file = $_CFG['_PATH']['phpscript'].'lib/'.$name.'.php';
		if(file_exists($file))
			return $file;
		return $_CFG['_PATH']['wep_phpscript'].'lib/'.$name.'.php';
	}

if (!defined('PHP_VERSION_ID')) {
	$version = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

/*
  точное время в милисекундах
 */

function getmicrotime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float) $usec + (float) $sec);
}

/*
  Функция SpiderDetect - принимает $_SERVER['HTTP_USER_AGENT'] и возвращает имя кравлера поисковой системы или false.
 */

function SpiderDetect($USER_AGENT='') {
	if (!$USER_AGENT) {
		if(!isset($_SERVER['HTTP_USER_AGENT'])) {
			return '*';
		}
		$USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
	}
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
		if (stripos($USER_AGENT, $engine[0])!==false) {
			return $engine[1];
		}
	}

	return '';
}

$_CFG['robot'] = SpiderDetect();


/*
  Используем эту ф вместо стандартной, для совместимости с UTF-8
 */
if (function_exists('mb_internal_encoding'))
	mb_internal_encoding($_CFG['wep']['charset']);

function _strlen($val) {
	if (function_exists('mb_strlen'))
		return mb_strlen($val);
	else
		return strlen($val);
}

function _substr($s, $offset, $len = NULL) {
	if (is_null($len)){
		if (function_exists('mb_substr'))
			return mb_substr($s, $offset);
		else
			return substr($s, $offset);
	}
	else {
		if (function_exists('mb_substr'))
			return mb_substr($s, $offset, $len);
		else
			return substr($s, $offset, $len);
	}
}

function _strtolower($txt) {
	if (function_exists('mb_strtolower'))
		return mb_strtolower($txt);
	else
		return strtolower($txt);
}

function _strpos($haystack, $needle, $offset=0) {
	if (function_exists('mb_strpos'))
		return mb_strpos($haystack, $needle, $offset);
	else
		return strpos($haystack, $needle, $offset);
}

function base64encode($txt) {
	$txt = base64_encode($txt);
	return str_replace(array('+','/'),array('-','_'),$txt);
}
function base64decode($txt) {
	$txt = str_replace(array('-','_'),array('+','/'),$txt);
	return base64_decode($txt);
}

// Преобразование типа строки в число
function str2int($string,$concat=true) {
	if(!$concat)
		return floatval($string);
   else
		return floatval(preg_replace('/[^0-9\-]+/','',$string));
}

function isint($val) {
	$res = preg_match_all('/^[0-9]+$/', $val, $matches);
	if($res==1)
		return true;
	return false;
}

function isfloat($val) {
	$res = preg_match_all('/^[0-9]+(,[0-9]{,6})?$/', $val, $matches);
	if($res==1)
		return true;
	return false;
}

function _chmod($file,$mode=null) {
	global $_CFG;
	if(is_null($mode)) $mode = $_CFG['wep']['chmod'];
	chmod($file, $mode);
}


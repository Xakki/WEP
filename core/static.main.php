<?php

if (defined('WEP_PROF')) {
    include_once "../xhprof/xhprof_lib/utils/xhprof_lib.php";
    include_once "../xhprof/xhprof_lib/utils/xhprof_runs.php";
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}

/*
  Функция завершения работы скрипта
 */
include $_CFG['_PATH']['core'] . 'observer.php';

function shutdown_function()
{
    global $_CFG;
    $_CFG['shutdown_function_flag'] = true;
    observer::notify_observers('shutdown_function');

    if (defined('WEP_PROF')) {
        // Останавливаем профайлер
        $xhprof_data = xhprof_disable();

        // Сохраняем отчет и генерируем ссылку для его просмотра
        $xhprof_runs = new XHProfRuns_Default();
        $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_test");
//        $listFile = '../xhprof/idlist.txt';
//        file_put_contents($listFile, $_SERVER['HTTP_HOST'].'|'.$_SERVER['REQUEST_URI'].'|'.time().'|'.$run_id.PHP_EOL ,FILE_APPEND );
    }
}

register_shutdown_function('shutdown_function'); // Запускается первым при завершении скрипта

/**
 * MAIN STATIC CLASS
 */

class static_main
{
    /**
	 * Регистрация автозагрузки
	 */
	    public static function autoload_register()
    {
        spl_autoload_register(array('static_main', 'autoload'));
    }

    /**
	 * Отмена автозагрузки
	 */
	    public static function autoload_unregister()
    {
        spl_autoload_unregister(array('static_main', 'autoload'));
        spl_autoload_register(
            function ($name) {
                if (function_exists('__autoload')) return __autoload($name);
                return false;
            }
        );
    }

    /*
	  Автозагрузка модулей
	 */
	    public static function autoload($class_name)
    {
        if ($file = _modulExists($class_name)) {
            require_once($file);
        }
        if (!class_exists($class_name, false)) {
            trigger_error('Can`t init `' . $class_name . '` modul ', E_USER_WARNING);
			//throw new Exception('Can`t init `' . $class_name . '` modul ');
        }
    }

    /**
	 * В формат выода сообщения
	 */
	    public static function am($type, $msg, $replace = array(), $obj = NULL)
    {
        return array($type, self::m($msg, $replace, $obj));
    }

    /**
	 * Текст сообщения
	 */
	    public static function m($msg, $replace = array(), $obj = NULL)
    {
        global $_CFG;
        if (is_object($replace)) {
            $obj = $replace;
            $replace = array();
        }
        if ($obj and isset($obj->lang[$msg])) $msg = $obj->lang[$msg];
        elseif (isset($_CFG['lang'][$msg])) $msg = $_CFG['lang'][$msg];
        if (is_array($replace) and count($replace))
        foreach ($replace as $k => $r) $msg = str_replace('###' . ($k + 1) . '###', $r, $msg);
        elseif (!is_array($replace) and $replace) $msg .= $replace;
        return $msg;
    }

    /**
	 * Запись сообщения в лог вывода
	 */
	    static function log($type, $msg, $cl = '')
    {
        global $_CFG;
        $ar_type = array('error' => false, 'alert' => true, 'notice' => true, 'ok' => true);
        if (!$ar_type[$type]) {
            trigger_error($msg, E_USER_WARNING);
            if (isDebugMode()) $_CFG['logs']['mess'][] = array($type, $msg, $cl);
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
	    static function showLog($type = 0)
    {
        global $_CFG;
        $text = '';
        $flag = true;
        if (isset($_CFG['logs']['mess']) and count($_CFG['logs']['mess'])) {
            foreach ($_CFG['logs']['mess'] as $r) {
				//$c = '';
                if ($r[0] == 'error') {
					//$c = 'red';
                    $flag = false;
                }
                /* elseif ($r[1] == 'warning' and $type < 3)
									$c = 'yellow';
								elseif ($r[1] == 'modify' and $type < 2)
									$c = 'blue';
								elseif ($r[1] == 'ok' and $type < 1)
									$c = 'green';
								elseif ($type < 3)
									$c = 'gray';
								if ($c != '')*/
				                $text .= '<div class="messelem ' . $r[0] . '">' . _e($r[1]) . '</div>';
            }
            $_CFG['logs']['mess'] = array();
        }
        return array($text, $flag);
    }

    /**
	 *
	 */
	    static function showErr()
    {
        global $_CFG, $SQL;
        $temp = static_main::showLog(); // сообщения ядра
        if ($temp[0]) $temp[0] = self::spoilerWrap('Сообщения ядра', $temp[0]);
        $notice = '';
        $htmlerr = '';
        /*Вывод ошибок*/
        if (count($GLOBALS['_ERR'])) {
            if ($_CFG['wep']['debugmode'] == 5) {
                return var_export($GLOBALS['_ERR'], true);
            } else {
                foreach ($GLOBALS['_ERR'] as $err) {
                    foreach ($err as $r) {
                        $var = $r['errtype'] . ' ' . $r['errstr'] . ' , in line ' . $r['errline'] . ' of file <i>' . $r['errfile'] . '</i>';
                        if ($r['debug']) //$r['errcontext']
                        $var = self::spoilerWrap($var, $r['debug'], 'bug_' . $r['errno']);
                        else $var = '<div class="bug_' . $r['errno'] . '">' . $var . '</div>';
                        $var .= "\n";
                        if ($_CFG['_error'][$r['errno']]['prior'] <= 3) $htmlerr .= $var;
                        else //нотисы отдельно
                        $notice .= $var;
                    }
                }
            }
        }

        if ($_CFG['wep']['debugmode'] > 1 and ($htmlerr != '' or $notice != '' or $temp[0])) {
            if ($notice) $htmlerr .= self::spoilerWrap('NOTICE', $notice);
            if ($temp[0]) $htmlerr .= $temp[0];
			//self::spoilerWrap('MESSAGES',$temp[0]);
        } elseif ($_CFG['wep']['debugmode'] == 1 and ($htmlerr != '' or !$temp[1])) {
            $htmlerr = 'На странице возникла ошибка! Приносим свои извинения за временные неудобства! Неполадки будут исправлены в ближайшее время.';
        } else {
            $htmlerr = '';
        }
        return $htmlerr;
    }

    static function spoilerWrap($head, $text, $css = '')
    {
        global $_CFG;
        $hash = md5($head);
        return '<div class="bugspoiler-wrap ' . $css . '"><div class="spoiler-head" onclick="var obj=this.parentNode;if(obj.className.indexOf(\'unfolded\')>=0) obj.className = obj.className.replace(\'unfolded\',\'\'); else obj.className = obj.className+\' unfolded\';">' . $head . '</div><div class="spoiler-body">' . html_entity_decode($text, ENT_QUOTES, CHARSET) . '</div></div>';
    }

    /**
	 * Парсер настроек модулей
	 */
	    static function _fParseIni($filename, $form = array())
    {
        $dest = $group = "\$data";
        $data = array();
        foreach (file($filename) as $line) {
            $line = trim($line);
            if (preg_match("/^\[(.+)\]( *)/", $line, $regs)) {
                $group = explode(":", $regs[1]);
                $dest = "\$data";
                foreach ($group as $key => $grp) $dest .= "[\$group[$key]]";
            } else {
                if (preg_match("/^(\\\"[^\\\"]*\\\"|[^=]*) *= *(\\\"[^\\\"]*\\\"|.*)$/", $line, $regs)) {
                    $regs[1] = trim($regs[1], '"');
                    $regs[2] = trim($regs[2], '"');
                    if (isset($form[$regs[1]]['multiple']) and strpos($regs[2], '|')) $regs[2] = explode('|', $regs[2]);
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
	    static function EnDecryptString($str, $hashKey = null)
    {
        if (is_null($hashKey)) {
            $hashKey = self::getHashKey();
        }
        $hashKeyLen = mb_strlen($hashKey);
        $strLen = mb_strlen($str);
        for ($i = 0; $i < $strLen; $i++) {
            $pos = $i % $hashKeyLen; // Если  строка длиннее ключа
            $r = ord($str[$i]) ^ ord($hashKey[$pos]); // Побитовый XOR ASCII-кодов символов
            $str[$i] = chr($r); // соответствующий полученному ASCII-коду
        }
        return $str;
    }

    static function getHashKey()
    {
        global $_CFG;
        if (isset($_CFG['HASH_KEY'])) $_CFG['HASH_KEY'];
        else $_CFG['HASH_KEY'] = file_get_contents($_CFG['_FILE']['HASH_KEY']);
        return $_CFG['HASH_KEY'];
    }

    /**
	 * Вывод названия таблицы у класса , без его подключения,
	 *  главное чтобу в модуле не было указано явно свое название табл
	 */
	    static function getTableNameOfClass($name)
    {
        global $_CFG;
        if (!isset($_CFG['modulprm'])) self::_prmModulLoad();
        $name = _getExtMod($name);
        if ($_CFG['modulprm'][$name]['tablename']) return $_CFG['modulprm'][$name]['tablename'];
        else return $_CFG['sql']['dbpref'] . $name;
    }

    /*
	  Проверка доступа пол-ля к модулю
	 */

	    static function _prmModul($mn, $param = array())
    {
        global $_CFG;

        if (isset($_SESSION['user']['id']) and isset($_SESSION['user']['level']) and $_SESSION['user']['level'] == 0) return true; // админу можно всё

        if (!isset($_CFG['modulprm'])) self::_prmModulLoad();
        if (!isset($_CFG['modulprm'][$mn])) return false; // отказ, если модуль отключен
        if (isset($_SESSION['user']['level']) and $_SESSION['user']['level'] >= 5) return false; //этим всё запрещено
        else {
            if (isset($_CFG['modulprm'][$mn]['access'][0])) return false;
            if (isset($_CFG['modulprm'][$mn]['access']['']) and count($_CFG['modulprm'][$mn]['access']) == 1) return true;
            if (count($param))
            foreach ($param as $r)
            if (isset($_CFG['modulprm'][$mn]['access'][$r])) return true;
        }
        return false;
    }

    /**
	 * подгрука данных прав доступа и пути подключения модулей
	 * @return bool
	 */
	    static function _prmModulLoad()
    {
        global $_CFG, $SQL;
        if (!isset($_CFG['modulprm'])) {
            session_go();
            $temp = NULL;
            _new_class('modulprm', $MODULPRM, $temp, true);
            $_CFG['modulprm'] = $_CFG['modulprm_ext'] = array();
            $ugroup_id = (isset($_SESSION['user']['gid']) ? (int)$_SESSION['user']['gid'] : $_CFG['wep']['guestid']);
			// Если есть таблица
            if ($MODULPRM->SQL->_tableExists($MODULPRM->tablename)) {
                if (isset($_SESSION['user']['parent_id']) and $_SESSION['user']['parent_id']) {
                    $ugroup_id = ' and t2.ugroup_id IN (' . $_SESSION['user']['parent_id'] . ',' . $ugroup_id . ')';
                } else $ugroup_id = ' and t2.ugroup_id=' . $ugroup_id;
                $q = 'SELECT t1.*,t2.access, t2.mname FROM `' . $MODULPRM->tablename . '` t1 LEFT Join `' . $MODULPRM->childs['modulgrp']->tablename . '` t2 on t2.owner_id=t1.id' . $ugroup_id . ' ORDER BY t1.typemodul,t1.name';
                $result = $MODULPRM->SQL->execSQL($q);
                if ($result->err) {
					//$_POST['sbmt'] = 1;
					//static_tools::_checkmodstruct('modulprm');
                    return false;
                }
                $_CFG['modulprm'] = array();
                while ($row = $result->fetch()) {
                    if ($row['extend']) $_CFG['modulprm_ext'][$row['extend']][] = $row['id'];
                    if (!isset($_CFG['modulprm'][$row['id']]['access']) or !$_CFG['modulprm'][$row['id']]['access']) $_CFG['modulprm'][$row['id']]['access'] = array_flip(explode('|', trim($row['access'], '|')));
                    if ($row['mname']) $_CFG['modulprm'][$row['id']]['name'] = $row['mname'];
                    else $_CFG['modulprm'][$row['id']]['name'] = $row['name'];
                    $_CFG['modulprm'][$row['id']]['path'] = self::getPathModul($row['path']);
                    $_CFG['modulprm'][$row['id']]['active'] = $row['active'];
                    $_CFG['modulprm'][$row['id']]['typemodul'] = $row['typemodul'];
                    $_CFG['modulprm'][$row['id']]['tablename'] = $row['tablename'];
                    $_CFG['modulprm'][$row['id']]['ver'] = $row['ver'];
                    $_CFG['modulprm'][$row['id']]['extend'] = $row['extend'];
                    $_CFG['modulprm'][$row['id']]['pid'] = $row['parent_id'];
                    if ($row['hook']) {
                        eval('$hook = ' . $row['hook'] . ';');
                        if ($hook and is_array($hook) and count($hook)) $_CFG['hook'] = self::MergeArrays($_CFG['hook'], $hook);
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
	    static function getPathModul($path)
    {
        global $_CFG;
        if (!$path) return '';
        $path = explode(':', $path);
        return $_CFG['modulinc'][$path[0]]['path'] . $path[1];
    }

    /**
	 * Проверка доступа пол-ля по уровню привелегии
	 * @param int $level - level пользователя
	 * @return bool
	 */
	    static function _prmUserCheck($level = 5)
    {
        global $_CFG;
		//session_go(); // TEST
        if (isset($_SESSION['user']['id']) and $_SESSION['user']['id']) {
            if (isset($_SESSION['user']['level']) and $_SESSION['user']['level'] <= $level) return true;
        }
        return false;
    }

    /**
	 * Проверка доступа пользователя по её группе
	 * @param int $id - id группы
	 * @return bool
	 */
	    static function _prmGroupCheck($id = 1)
    {
        global $_CFG;
        if (!is_array($id)) $id = array($id);
        foreach ($id as $r) {
            if (isset($_SESSION['user']['id']) and $_SESSION['user']['id']) {
                if (isset($_SESSION['user']['gid']) and $_SESSION['user']['gid'] == $r) return true;
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
	    static function userAuth($login = '', $pass = '')
    {
        global $_CFG;
        session_go(); // запускаем сессию, чтоб проверить авторизован ли пользователь
        $result = array('', -1);
        if (!self::_prmUserCheck() or $login) {
            if ($_CFG['wep']['access']) {
                if ($login) {
                    $result = array(static_main::m('autherr'), 0);
                    if (_new_class('ugroup', $UGROUP)) $result = $UGROUP->authorization($login, $pass);
                    else $result[0] = 'Ugroup modul is off';
                } elseif (!self::_prmUserCheck() and isset($_COOKIE['remember'])) {
                    if (preg_match("/^[0-9A-Za-z\_]+$/", $_COOKIE['remember'])) {
                        if (_new_class('ugroup', $UGROUP)) $result = $UGROUP->cookieAuthorization();
                        else $result[0] = 'Ugroup modul is off';
                    }
                }
            } elseif ($_CFG['wep']['login'] and $_CFG['wep']['password']) {
				// Авторизация без использования БД , логин и пароль берутся из конфига
                $flag = 0;

                if (isset($_COOKIE['remember']) and $_COOKIE['remember']) {
                    $pos = strpos($_COOKIE['remember'], '_');
                    if ($_CFG['wep']['login'] == substr($_COOKIE['remember'], ($pos + 1)) and md5($_CFG['wep']['md5'] . $_CFG['wep']['password']) == substr($_COOKIE['remember'], 0, $pos)) $flag = 1;
                } elseif ($login or $pass) {
                    $result = array(static_main::m('autherr'), 0);
                    if ($_CFG['wep']['login'] == $login and $_CFG['wep']['password'] == $pass) $flag = 1;
                }

                if ($flag) {
                    session_go(true); // принудительный запуск сессия для пользователя
                    $_SESSION['user']['id'] = 1;
                    $_SESSION['user']['name'] = $_CFG['wep']['login'];
                    $_SESSION['user']['gname'] = "GOD MODE";
                    $_SESSION['user']['level'] = 0;
                    $_SESSION['user']['wep'] = 1;
                    $_SESSION['user']['gid'] = 1;
                    $_SESSION['user']['design'] = $_CFG['wep']['design'];
                    $_SESSION['user']['filesize'] = $_CFG['wep']['def_filesize'];
                    $_SESSION['FckEditorUserFilesUrl'] = MY_BH . $_CFG['PATH']['userfile'];
                    $_SESSION['FckEditorUserFilesPath'] = SITE . $_CFG['PATH']['userfile'];
                    if (isset($_POST['remember']) and $_POST['remember'] == '1') _setcookie(
                        'remember',
                        md5($_CFG['wep']['md5'] . $_CFG['wep']['password']) . '_' . $_CFG['wep']['login'],
                        $_CFG['remember_expire']
                    );
                    $result = array(static_main::m('authok'), 1);
					//_setcookie($_CFG['wep']['_showerror'], 2);
					//$_COOKIE['_showerror']=1;
                }
            }
        } else {
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
	    static function userExit()
    {
        global $_CFG;
        session_go();
        if (isset($_SESSION)) session_destroy();
		//if(isset($_SESSION))
		//	$_SESSION = array();
        if (isset($_COOKIE['remember'])) _setcookie('remember', '', (time() - 5000));
        if (isset($_COOKIE[$_CFG['session']['name']])) _setcookie($_CFG['session']['name'], '', (time() - 5000));
    }

    /**
	 * Получить ID пользователя или зарегить как анонима
	 *
	 */
	    static function userId($force = false)
    {
        session_go();
        if (isset($_SESSION['user']['id'])) return $_SESSION['user']['id'];
        elseif ($force) {
			//TODO : Создаем пользователя  гостя
        }
        return null;
    }

    static public function addTaskManager($name, $func, $param)
    {
        _new_class('crontask', $CRONTASK);
        return $CRONTASK->addCronTask($name, $func, $param);
    }
    /*Функции вспомогательные*/

	    /**
	 * Вставка массива , после указанного ключа
	 * @param array $data - Массив в который будет вставляться $insert_data
	 * @param value $afterkey - ключ массива $data, после которого будет вставлен массив $insert_data
	 * @param array $insert_data - вставляемый массив
	 * @return array
	 */
	    static function insertInArray(array $data, $afterkey, array $insert_data)
    {
        $output = array();
        if (!is_array($insert_data)) {
            trigger_error(
                'Не верный переданный 3тий аргумент $insert_data, должен быть массив.',
                E_USER_WARNING
            );
            return $data;
        } elseif (count($data)) {
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
	    static function MergeArrays($Arr1, $Arr2)
    {
        foreach ($Arr2 as $key => $Value) {
            if (array_key_exists($key, $Arr1) && is_array($Value) && is_array($Arr1[$key])) {
                $Arr1[$key] = self::MergeArrays($Arr1[$key], $Value);
            } else $Arr1[$key] = $Value;
        }
        return $Arr1;
    }

    /**
	 * ИЗ полного(абсолютного) пути к фаилу получаем относительный путь с корня сайта
	 * @param string $file - абсолютный путь к фаилу
	 * @return string относительный путь к фаилу
	 */
	    static function relativePath($file)
    {
        global $_CFG;
        $file = str_replace(array('\\\\', '\\'), '/', $file);
        $cf = SITE;
        $cf = str_replace(array('\\\\', '\\'), '/', $cf);
        $cf2 = $_CFG['_PATH']['_path'];
        $cf2 = str_replace(array('\\\\', '\\'), '/', $cf2);
        $file = str_replace(array($_SERVER['_DR_'], $cf, $cf2), '', $file);
        return $file;
    }

    /**
	 * Обрезание текста по длине , оставляя максимум целых слов.
	 * @param string $text - текст
	 * @param int $col - максим длина строки
	 * @param bool $clearFormat - чистка строки от тегов
	 * @return string обрезанный текст
	 */
	    static function pre_text($text, $col, $clearFormat = true)
    {
        if ($clearFormat) {
			//temp
            $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
            if ($clearFormat === 2) // TODO  : для чего этот высер?
            $text = str_replace(array('.<br />', ',<br />', '<br />'), array('. ', ', ', '. '), $text);
            else $text = str_replace(array('<br/>', '<br/>', '<hr>', '<br>', '><'), array(' ', ' ', ' ', ' ', '> <'), $text);

            $text = trim(strip_tags($text), "\s\t\r\n\0\x0B"); // \xA0 из за него кавычки и пробелы тупят
        }
        if (mb_strlen($text) > $col) {
            $length = mb_strripos(mb_substr($text, 0, $col), ' ');
            $text = mb_substr($text, 0, $length) . '...';
        }
        return $text;
    }

    /**
	 * Замена в тексте ссылок на редирект
	 * @param string $text - текст в котором будет производится поиск
	 * @param int $name - подстановочное название ссылок, если $name==false - то название будет как самы ссылка только без http:// и www, если $name===true - то только домен в названии останется xakki.ru
	 * @param int $dolink - 0 - замена всех http на редирект и превращение в ссылки; 1- замена всех http на редирект; 2- замена всех http на редирект в ссылках
	 * @return string текст
	 */
	static function redirectLink($text, $name = 'Источник', $dolink = 0)
    {
        $cont = array();
        if ($dolink == 2) $match = '/(href=")(http:\/\/|https:\/\/|www\.)[0-9A-Za-zА-Яа-я\/\.\_\-\=\?\&\;]*/u';
        else $match = '/(href="|=")?(http:\/\/|https:\/\/|www\.)[0-9A-Za-zА-Яа-я\/\.\_\-\=\?\&\;]*/u';
        preg_match_all($match, $text, $cont);
        if (count($cont[0])) {
            $temp = array();
            foreach ($cont[0] as $rc) {
                if (mb_substr($rc, 0, 2) == '="') {
                    $temp[] = $rc;
                    continue;
                }

                if (mb_strpos($rc, 'href="') !== false) $temp[] = 'rel="nofollow" target="_blank" href="' . MY_BH . '_redirect.php?url=' . base64encode(str_replace('href="', '', $rc));
                elseif ($dolink == 0) {
                    if (!$name) {
                        $tn = trim(str_replace(array('href="', 'http://', 'https://', 'www.'), '', $rc), ' /');
                    } elseif ($name === true) {
                        $tn = trim(str_replace(array('href="'), '', $rc), ' /');
                        $tmp = parse_url($tn);
                        if ($tmp && isset($tmp['host'])) {
                            $tn = $tmp['host'];
                        }
                    } else {
                        $tn = $name;
                    }
                    $temp[] = '<a href="' . MY_BH . '_redirect.php?url=' . (base64encode($rc)) . '" rel="nofollow" target="_blank">' . $tn . '</a>';
                } else $temp[] = MY_BH . '_redirect.php?url=' . (base64encode($rc));
            }
            $text = str_replace($cont[0], $temp, $text);
        }
        return $text;
    }

    static function redirect($link = true, $NO = '301 Moved Permanently')
    {
        global $_CFG, $_tpl;

        if ($_SERVER['HTTP_REFERER']===$link) {
            trigger_error('Warning!!! Self redirect for '. $link, E_USER_WARNING);
            return;
        }

        $cur = $_SERVER['HTTP_PROTO'] . $_SERVER['HTTP_HOST'] . '/' . $_SERVER['REQUEST_URI'];
        $cookieName = '_r' . md5($link.$cur);

        $cnt = (isset($_COOKIE[$cookieName]) ? (int) $_COOKIE[$cookieName] : 0);
		//301 - перемещение на посточнную основу
        // header("HTTP/1.0 400 Bad Request");
        //301 Moved Permanently
        if ($cnt>4) {
            trigger_error('Warning!!! Repeat(5) redirect from ' . $cur. ', to '. $link, E_USER_WARNING);
            die('Нажмите на ссылку, для перехода на страницу <a href="' . $link . '">' . $link . '</a>');
        }

        $cnt++;
        _setcookie($cookieName, $cnt, (time() + 50));

        if ($link === true) {
            $link = $cur;
        }

        if (isAjax()) {
            if ($_CFG['wep']['debugmode'] < 3) $_tpl['redirect'] = $link;
            else $_tpl['redirectConfirm'] = $link;
        } else {
            if ($_CFG['wep']['debugmode'] < 3) {
                if ($NO !== false) header('HTTP/1.1 ' . $NO);
                header("Location: " . $link);
                die($link);
            } else {
                die('Redirect to <a href="' . $link . '">' . $link . '</a> ['.$NO.']');
            }
        }
    }

    static function urlAppend($query, $url = false)
    {
        if (!$url) $url = $_SERVER['REQUEST_URI'];
        if (strpos($url, '?')) $url .= '&' . $query;
        else $url .= '?' . $query;
        return $url;
    }

    /**
	 * Преобразование массива данных в XML формат
	 * @param array $DATA - путь
	 * @param strin $f - название тега (по умолч item)
	 * @return string XML
	 */
	    static function kData2xml($DATA, $f = 'item')
    {
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
                                if (is_array($d)) $value .= self::kData2xml($d, $m);
                                elseif ($m == 'value') $value .= $d;
                                elseif ($m == 'name') $value .= '<name><![CDATA[' . $d . ']]></name>';
                                else $attr .= ' ' . str_replace('#', '', $m) . '="' . $d . '"';
                            }
                        } else $value = $r;
                        $XML .= '<' . $f . $attr . '>' . $value . '</' . $f . ">\n";
                    }
					//$XML = '<'.$f.$attr.'>'.$value.'</'.$f.'>';
                } else {
                    foreach ($DATA as $k => $r) {
                        if (is_array($r)) {
                            $value .= self::kData2xml($r, $k);
                        } elseif ($k == 'value') $value .= $r;
                        elseif ($k == 'name') $value .= '<name><![CDATA[' . $r . ']]></name>';
                        else $attr .= ' ' . str_replace('#', '', $k) . '="' . $r . '"';
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
	    static function _usabilityDate($time, $format = 'Y-m-d H:i')
    {
        global $_CFG;
        $date = getdate($time);
        $de = $_CFG['time'] - $time;
        if ($de < 3600) {
            if ($de < 240) {
                if ($de < 60) $date = 'Минуту назад';
                else $date = ceil($de / 60) . ' минуты назад';
            } else $date = ceil($de / 60) . ' минут назад';
        } elseif ($_CFG['getdate']['year'] == $date['year'] and $_CFG['getdate']['yday'] == $date['yday']) $date = 'Сегодня ' . date('H:i', $time);
        elseif ($_CFG['getdate']['year'] == $date['year'] and $_CFG['getdate']['yday'] - $date['yday'] == 1) $date = 'Вчера ' . date('H:i', $time);
        elseif (strpos($format, '%') !== false) $date = strftime($format, $time); // "%d %B(%b) %A(%a) %Y"
        else $date = self::_date($format, $time);

        return $date;
    }

    static function _date($format = NULL, $time = NULL)
    {
        global $_CFG;
        if (is_null($format)) $format = $_CFG['wep']['dateformat'];
        if (is_null($time)) $time = time();
        $date = date($format, $time);
        if (strpos($format, 'F') !== false) $date = str_replace(array_keys($_CFG['lang']['dateF']), $_CFG['lang']['dateF'], $date);
        if (strpos($format, 'M') !== false) $date = str_replace(array_keys($_CFG['lang']['dateM']), $_CFG['lang']['dateM'], $date);
        return $date;
    }

    static function padeji($txt)
    {
        $lw = mb_substr($txt, -1);
        $ArW = array('а' => 1, '' => 1, '' => 1, '' => 1, '' => 1, '' => 1,);
        return $txt;
    }

    static function numWord($num, $word)
    {
        return $num . ' ' . $word;
    }

    static function downSite($title = false, $text = false)
    {
        global $_CFG;
        header('HTTP/1.1 503 Service Unavailable');
        if ($title) $_CFG["site"]["work_title"] = $title;
        elseif (!isset($_CFG["site"]["work_title"]) or !$_CFG["site"]["work_title"]) $_CFG["site"]["work_title"] = 'Технический перерыв.';

        if ($text) $_CFG["site"]["work_text"] = $text;
        elseif (!isset($_CFG["site"]["work_text"]) or !$_CFG["site"]["work_text"]) $_CFG["site"]["work_text"] = 'Ушёл на базу.';

        if (file_exists($_CFG['_PATH']['controllers'] . '/frontend/work.html')) $html = file_get_contents($_CFG['_PATH']['controllers'] . '/work.html');
        else $html = file_get_contents($_CFG['_PATH']['wep_controllers'] . '/frontend/work.html');
        $html = str_replace('"', '\"', $html);
        eval('$html = "' . $html . '";');
        echo $html;
        exit();
    }

    /**
	 * Постраничная навигация
	 */
	    static public function fPageNav2($_this, $countfield, $param = array())
    {
		//$countfield - бщее число элем-ов
		//$$param - массив данных
		//$_this->messages_on_page - число эл-ов на странице
		//$_this->_pn - № текущей страницы
        $numlist = $_this->numlist; // кличество числе по бокам максимум
        $DATA = array(
            'cnt' => $countfield, 'messages_on_page' => $_this->messages_on_page, 'cntpage' => 0, 'modul' => $_this->_cl, 'reverse' => $_this->reversePageN
        );

		//pagenum
        if (isset($_GET[$_this->_cl . '_mop'])) {
            $_this->messages_on_page = (int)$_GET[$_this->_cl . '_mop'];
            if ($_COOKIE[$_this->_cl . '_mop'] != $_this->messages_on_page) _setcookie($_this->_cl . '_mop', $_this->messages_on_page, $_this->_CFG['remember_expire']);
        } elseif (isset($_COOKIE[$_this->_cl . '_mop'])) $_this->messages_on_page = (int)$_COOKIE[$_this->_cl . '_mop'];
        if (!$_this->messages_on_page) $_this->messages_on_page = 20;

        /*		 * * PAGE NUM REVERSE ** */
        if ($_this->reversePageN) {
            if ($_this->_pn == 0) $_this->_pn = 1;
            else $_this->_pn = floor($countfield / $_this->messages_on_page) - $_this->_pn + 1;
            $DATA['cntpage'] = floor($countfield / $_this->messages_on_page);
            $temp_pn = $_this->_pn;
            $_this->_pn = $DATA['cntpage'] - $_this->_pn + 1;
        } else {
            $DATA['cntpage'] = ceil($countfield / $_this->messages_on_page);
        }

		// Приводим к правильным числам
        if ($_this->_pn > $DATA['cntpage']) $_this->_pn = $DATA['cntpage'];
        if ($_this->_pn < 1) $_this->_pn = 1;
        $DATA['_pn'] = $_this->_pn;

        foreach ($_this->_CFG['enum']['_MOP'] as $k => $r) $DATA['mop'][$k] = array('value' => $r, 'sel' => 0);
        $DATA['mop'][$_this->messages_on_page]['sel'] = 1;

        $flag = false;
        if ($countfield) {
            if ($_this->reversePageN and $countfield >= ($_this->messages_on_page * 2)) $flag = true;
            elseif (!$_this->reversePageN and $countfield > $_this->messages_on_page) $flag = true;
        }

        if ($flag) {
			//$PP[0] - страница не выбрана
			//$PP[1] - первая часть
			//$PP[2] - вторая часть
            if (!isset($param['firstpath']) or !$param['firstpath']) $param['firstpath'] = $_SERVER['REQUEST_URI'];
            $PP = array(0 => $param['firstpath'], 1 => $param['firstpath'], 2 => '');
            if (isset($param['_clp'])) {
                if (count($param['_clp'])) {
                    $temp = $param['_clp'];
                    unset($temp[$_this->_pa]);
                    $PP[0] .= http_build_query($temp) . '&';
                    $PP[1] = $PP[0];
                }
                $PP[1] .= $_this->_pa . '=';
            } else {
                $pregreplPage = '/(.*)_p[0-9]+(.*)/';
                if (!preg_match($pregreplPage, $param['firstpath'], $matches)) {
                    $temp = explode('.html', $param['firstpath']);
                    $PP[1] = $temp[0] . '_p';
                    $PP[2] = '.html' . $temp[1];
                } else {
                    $PP[0] = $matches[1] . $matches[2];
                    $PP[1] = $matches[1] . '_p';
                    $PP[2] = $matches[2];
                }
            }

            $DATA['PP'] = $PP;

            if ($_this->reversePageN) { // обратная нумирация
                /* Собираем массив ссылок */
				                $DATA['link'][$DATA['cntpage']] = $PP[0];
                if (($_this->_pn + $numlist) < $DATA['cntpage'] - 1) {
                    $j = $_this->_pn + $numlist;
                } else $j = $DATA['cntpage'] - 1;
                $vl = $_this->_pn - $numlist;
                if ($vl < 2) $vl = 2;
                for ($i = $j; $i >= $vl; $i--) {
                    $DATA['link'][$i] = $PP[1] . $i . $PP[2];
                }
                $DATA['link'][1] = $PP[1] . '1' . $PP[2];
            } else {
                $DATA['link'][1] = $PP[0];

                if (($_this->_pn - $numlist) > 3) {
                    $j = $_this->_pn - $numlist;
                    $DATA['link'][' ...'] = false;
                } else {
                    $j = 2;
                }

                $vl = $_this->_pn + $numlist;
                if ($vl >= ($DATA['cntpage'] - 2)) $vl = $DATA['cntpage'] - 1;
                for ($i = $j; $i <= $vl; $i++) {
                    $DATA['link'][$i] = $PP[1] . $i . $PP[2];
                }
                if ($vl < $DATA['cntpage'] - 1) $DATA['link']['... '] = false;
                $DATA['link'][$DATA['cntpage']] = $PP[1] . $DATA['cntpage'] . $PP[2];

                /* $DATA['link'][1] = $PP[0];
				  for ($i = 2; $i <= $DATA['cntpage']; $i++) {
				  $DATA['link'][$i] = $PP[1].$i.$PP[2];
				  } */
            }
			//////////////////
        }

        $DATA['start'] = 0;
        if ($_this->reversePageN) {
            if ($_this->_pn == floor($countfield / $_this->messages_on_page)) {
                $_this->messages_on_page = $countfield - $_this->messages_on_page * ($_this->_pn - 1); // правдивый
				//$_this->messages_on_page = $_this->messages_on_page*$_this->_pn-$countfield; // полная запись
            } else $DATA['start'] = $countfield - $_this->messages_on_page * $_this->_pn; // начало отсчета
        } else $DATA['start'] = $_this->messages_on_page * ($_this->_pn - 1); // начало отсчета
        if ($DATA['start'] < 0) $DATA['start'] = 0;
        return $DATA;
    }

    /**
	 * Формат для вывода сообщения в шаблон
	 */
	    static function tplMess($mess = 'errdata', $type = 'error')
    {
        return array('tpl' => '#pg#messages', 'messages' => array(static_main::am($type, $mess)));
    }

    /**
	 * Проверка разрешенных ПХП для вендор
	 */
	    static public function phpAllowVendors($name)
    {
        global $_CFG;
        $name = substr($name, 9);
        if (isset($_CFG['vendors'][$name])) return true;
        return false;
    }

    /**
	 * Проверка для вендор запуск сессии
	 */
	    static public function phpAllowVendorsSession($name)
    {
        global $_CFG;
        $name = substr($name, 9);
        if (isset($_CFG['vendors'][$name]['session']) and $_CFG['vendors'][$name]['session']) return true;
        return false;
    }

    /**
	 * Проверка для вендор отключения автозагрузчика
	 */
	    static public function phpAllowVendorsUnregisterAutoload($name)
    {
        global $_CFG;
        $name = substr($name, 9);
        if (isset($_CFG['vendors'][$name]['unregisterAutoload']) and $_CFG['vendors'][$name]['unregisterAutoload']) return true;
        return false;
    }

    /**
	 * Публикация статичных фаилов
	 */
	    static public function publisher($file, $default = false)
    {
        global $_CFG;

        if (strpos($file, $_CFG['_PATH']['content']) === false) {
            $publish = $_CFG['_PATH']['temp'] . basename($file);
            if (!copy($file, $publish)) return $default;
        } else $publish = $file;

        if (strpos($publish, $_CFG['_PATH']['content']) !== false) $publish = $_CFG['PATH']['content'] . substr($publish, _strlen($_CFG['_PATH']['content']));

        return $publish;
    }

    static public function setExpire($sec)
    {
        global $_CFG;
        $_CFG['header']['expires'] = $sec;
    }

    static public function getExpire()
    {
        global $_CFG;
        return $_CFG['header']['expires'];
    }
}

/* SESSION */

function session_go($force = false)
{ //$force=true - открывает сесиию для не авторизованного пользователя
    if (static_main::_prmUserCheck()) {
        return true;
    }
    global $_CFG, $SESSION_GOGO;
    if (!$_CFG['robot'] and (isset($_COOKIE[$_CFG['session']['name']]) or $force)) {
        if ($_CFG['wep']['sessiontype'] == 1) {
            if (!$SESSION_GOGO) {
                include_once $_CFG['_PATH']['wep_ext'] . 'session.class/session.class.php';
                $SESSION_GOGO = new session_class();
            }
            $SESSION_GOGO->start($force);
        } else {
            session_start();
        }
        return true;
    }
    return false;
}

function _setcookie($name, $value = '', $expire = '', $path = '', $domain = '', $secure = '')
{
    global $_CFG;
    if ($expire === '') {
        $expire = $_CFG['session']['expire'];
    }

    if ($path === '') $path = $_CFG['session']['path'];
    if ($domain === '') $domain = $_CFG['session']['domain'];
    if ($secure === '') $secure = $_CFG['session']['secure'];
    setcookie($name, $value, $expire, $path, $domain, $secure);

    if ($expire > time()) {
        $_COOKIE[$name] = $value;
    } else {
        unset($_COOKIE[$name]);
    }
}

/**
 * Инициализация модулей
 */
function _new_class($name, &$MODUL, $OWNER = NULL, $_forceLoad = false)
{
    global $_CFG;
    $MODUL = NULL;
    if (is_bool($OWNER)) {
        $_forceLoad = $OWNER;
        $OWNER = NULL;
    }
    if (!$_forceLoad) static_main::_prmModulLoad();
    $name = _getExtMod($name);

    if (isset($_CFG['singleton'][$name])) {
        $MODUL = $_CFG['singleton'][$name];
        return true;
    } elseif (is_null($OWNER) and isset($_CFG['modulprm'][$name]) and $_CFG['modulprm'][$name]['pid']) {
		// кастыль: при обращении к дочерним классам , находяться родители и от него дается ссылка на класс.
        _new_class($_CFG['modulprm'][$name]['pid'], $MODUL2);
        $MODUL = $MODUL2->childs[$name];
        return true;
    } else {
        $class_name = $name . "_class";

        if (!class_exists($class_name, false)) {
            if (isset($_CFG['modulprm'][$name]) and $_CFG['modulprm'][$name]['active']) $_forceLoad = true;

            if ($_forceLoad and $file = _modulExists($class_name, $OWNER)) {
                require_once($file);
            }
        }

        if (class_exists($class_name, false)) {
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
            if ($MODUL and is_object($MODUL)) return true;
        } elseif (!isset($_CFG['modulprm'][$name]) or !$_CFG['modulprm'][$name]['active']) return false;
        elseif (isset($_CFG['modulprm'][$name]) and $_CFG['modulprm'][$name]['pid']) {
            $moduls = array($name);
            while ($_CFG['modulprm'][$name]['pid']) {
                $moduls[] = $_CFG['modulprm'][$name]['pid'];
                $name = $_CFG['modulprm'][$name]['pid'];
                if (!$_CFG['modulprm'][$name]['active']) return false;
            }

            $cnt = count($moduls);

            _new_class($moduls[$cnt - 1], $MODUL);

            for ($i = $cnt - 2; $i >= 0; $i--) {
                $MODUL = $MODUL->childs[$moduls[$i]];
            }
            return true;
        } else {
            trigger_error('Can`t init `' . $class_name . '` modul ', E_USER_WARNING);
        }
    }
    return false;
}

function _getChildModul($name, &$MODUL)
{
    global $_CFG;

    static_main::_prmModulLoad();
    if (isset($_CFG['modulprm'][$name]['pid']) && $_CFG['modulprm'][$name]['pid'] != '') {
        $moduls = array($name);
        while (isset($_CFG['modulprm'][$name]['pid']) && $_CFG['modulprm'][$name]['pid'] != '') {
            $moduls[] = $_CFG['modulprm'][$name]['pid'];
            $name = $_CFG['modulprm'][$name]['pid'];
        }

        $cnt = count($moduls);

        _new_class($moduls[$cnt - 1], $MODUL);
        for ($i = $cnt - 2; $i >= 0; $i--) {
            $MODUL = $MODUL->childs[$moduls[$i]];
        }
    } else {
        _new_class($name, $MODUL);
    }
    if ($MODUL) return true;
}

function _getExtMod($name)
{
    global $_CFG;
    if (isset($_CFG['modulprm_ext'][$name]) && isset($_CFG['modulprm'][$name]) && !$_CFG['modulprm'][$name]['active']) $name = $_CFG['modulprm_ext'][$name][0];
    return $name;
}

/**
 * Проверка существ модуля
 *
 * Осторожно! Тут хитрая-оптимизированная логика
 * @global array $_CFG
 * @param string $class_name
 * @return string
 */
function _modulExists($class_name, &$OWNER = NULL)
{
    global $_CFG;
    $class_name = explode('_', $class_name);

    if (isset($_CFG['modulprm'][$class_name[0]])) {
        $file = $_CFG['modulprm'][$class_name[0]]['path'];
        if ($file and file_exists($file)) return $file;
    }

    $file = $_CFG['_PATH']['core'] . $class_name[0] . (isset($class_name[1]) ? '.' . $class_name[1] : '') . '.php';
    if (file_exists($file)) return $file;

    $ret = includeModulFile($class_name[0], $OWNER);
    return $ret['file'];
}

/**
 * Нахождение фаила содержащего класс модуля
 * @Mid - модуль
 */
function includeModulFile($Mid, &$OWN = NULL)
{
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
        if (!is_null($OWN)) {
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
function getLib($name)
{
    global $_CFG;
    $file = $_CFG['_PATH']['controllers'] . 'lib/' . $name . '.php';
    if (file_exists($file)) return $file;
    return $_CFG['_PATH']['wep_controllers'] . 'lib/' . $name . '.php';
}

if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

/*
  точное время в милисекундах
 */

function getmicrotime()
{
    return microtime(true);
//	list($usec, $sec) = explode(" ", microtime());
//	return ((float)$usec + (float)$sec);
}

/*
  Функция SpiderDetect - принимает $_SERVER['HTTP_USER_AGENT'] и возвращает имя кравлера поисковой системы или false.
 */

function SpiderDetect($USER_AGENT = '')
{
    if (!$USER_AGENT) {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
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
        if (stripos($USER_AGENT, $engine[0]) !== false) {
            return $engine[1];
        }
    }

    return '';
}

function _fTestIE()
{
    /* Доп функция проверки типа браузера клиента */
	    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $browserIE = false;
    if (stristr($user_agent, 'MSIE')) $browserIE = true; // IE
    return $browserIE;
}

/********************/

function setTheme($theme)
{
    global $_CFG;
    if (isBackend()) {
        if (!file_exists($_CFG['_PATH']['cdesign'] . $theme)) {
            trigger_error('Theme ' . $_CFG['_PATH']['cdesign'] . $theme . ' not found', E_USER_WARNING);
            return false;
        }
        $_CFG['wep']['design'] = $theme;
    } else {
        if (!file_exists($_CFG['_PATH']['themes'] . $theme)) {
            trigger_error('Theme ' . $_CFG['_PATH']['themes'] . $theme . ' not found', E_USER_WARNING);
            return false;
        }
        $_CFG['site']['theme'] = $theme;
    }
    return true;
}

function getTheme()
{
    /*if(isset($_COOKIE['cdesign']) and $_COOKIE['cdesign'])
		$_design = $_COOKIE['cdesign'];
	elseif(isset($_SESSION['user']['design']) and $_SESSION['user']['design'])
		$_design = $_SESSION['user']['design'];
	else
		$_design = $_CFG['wep']['design'];
	$_design = 'default';*/

	    global $_CFG;
    if (isBackend()) return $_CFG['wep']['design'];
    else return $_CFG['site']['theme'];
}

/**
 * $type = null  return auto path
 * $type = true  return Backend path
 * $type = false  return frontend path
 */


function getPathTheme($type = null)
{
    return getPathThemes($type) . getTheme() . '/';
}

function getPathThemes($type = null)
{
    global $_CFG;
    if (is_null($type)) $type = isBackend();
    if ($type) return $_CFG['_PATH']['cdesign'];
    else return $_CFG['_PATH']['themes'];
}

// путь внешний к теме
function getUrlTheme($type = null)
{
    return getUrlThemes($type) . getTheme() . '/';
}

// путь внешний к папке с темами
function getUrlThemes($type = null)
{
    global $_CFG;
    if (is_null($type)) $type = isBackend();
    if ($type) return $_CFG['PATH']['cdesign'];
    else return $_CFG['PATH']['themes'];
}

/********************/

function setTemplate($template)
{
    global $_CFG;
    $file = getPathTheme() . 'templates/' . $template . '.tpl';
    if (!file_exists($file)) {
        trigger_error('Template ' . $file . ' not found', E_USER_WARNING);
        return false;
    }
    $_CFG['site']['template'] = $template;
    return true;
}

function getTemplate()
{
    global $_CFG;
    return $_CFG['site']['template'];
}

function getPathTemplate()
{
    global $_CFG;
    return getPathTheme() . 'templates/' . getTemplate() . '.tpl';
}

/********************/

function isBackend($val = null)
{
    global $_CFG;
    if ($val === true) $_CFG['_F']['adminpage'] = true;
    return $_CFG['_F']['adminpage'];
}

function isAjax()
{
    global $_CFG;
    return $_CFG['requestType'] === 'ajax';
}

function isProduction()
{
    global $_CFG;
    return $_CFG['site']['production'];
}

function canShowAllInfo()
{
    global $_CFG;
    if ($_CFG['wep']['_showallinfo'] === false) {
        return 0;
    }
    if (isset($_COOKIE[$_CFG['wep']['_showallinfo']]) and $_COOKIE[$_CFG['wep']['_showallinfo']]) {
        return $_COOKIE[$_CFG['wep']['_showallinfo']];
    }
    return 0;
}

function setNeverShowAllInfo()
{
    global $_CFG;
    $_CFG['wep']['_showallinfo'] = false;
}

function setNeverShowError()
{
    global $_CFG;
    $_COOKIE[$_CFG['wep']['_showerror']] = 0;
}

function setOffDebug()
{
    global $_CFG;
    $_CFG['wep']['debugmode'] = 0;
}

function initShowAllInfo()
{
    global $_CFG;
    $sai = $_CFG['wep']['_showallinfo'];
    if (isset($_GET[$sai]) and !$_CFG['robot']) { // and !isset($_COOKIE[$sai])
        if ($_GET[$sai]) _setcookie($sai, $_GET[$sai]);
        else _setcookie($sai, $_GET[$sai], (time() - 5000));
    }
}

/********************/

function _e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_IGNORE, CHARSET);
}

function _strlen($val)
{
    if (function_exists('mb_strlen')) return mb_strlen($val);
    else return strlen($val);
}

function _substr($s, $offset, $len = NULL)
{
    if (is_null($len)) {
        if (function_exists('mb_substr')) return mb_substr($s, $offset);
        else return substr($s, $offset);
    } else {
        if (function_exists('mb_substr')) return mb_substr($s, $offset, $len);
        else return substr($s, $offset, $len);
    }
}

function _strtolower($txt)
{
    if (function_exists('mb_strtolower')) return mb_strtolower($txt);
    else return strtolower($txt);
}

function _strpos($haystack, $needle, $offset = 0)
{
    if (function_exists('mb_strpos')) return mb_strpos($haystack, $needle, $offset);
    else return strpos($haystack, $needle, $offset);
}

function base64encode($txt)
{
    $txt = base64_encode($txt);
    return str_replace(array('+', '/'), array('-', '_'), $txt);
}

function base64decode($txt)
{
    $txt = str_replace(array('-', '_'), array('+', '/'), $txt);
    return base64_decode($txt);
}

// Преобразование типа строки в число
function str2int($string, $concat = true)
{
    if (!$concat) return floatval($string);
    else return floatval(preg_replace('/[^0-9\-]+/', '', $string));
}

function isint($val)
{
    if (!is_string($val) && !is_numeric($val)) return false;
    $res = preg_match_all('/^[0-9]+$/', $val, $matches);
    if ($res == 1) return true;
    return false;
}

function isfloat($val)
{
    $res = preg_match_all('/^[0-9]+(,[0-9]{,6})?$/', $val, $matches);
    if ($res == 1) return true;
    return false;
}

function _chmod($file, $mode = null)
{
    global $_CFG;
    if (is_null($mode)) $mode = $_CFG['wep']['chmod'];
    chmod($file, $mode);
}

function _hasPost($name)
{
    return (isset($_POST[$name]) && $_POST[$name]);
}

function _hasGet($name)
{
    return (isset($_GET[$name]) && $_GET[$name]);
}

/********************/

function setCss($styles, $isAuto = true, $pos = POS_END)
{
    global $_tpl, $_CFG;
    if ($isAuto and !$_CFG['allowAutoIncludeCss']) return false;

    $customTheme = getUrlTheme();

    if (is_string($styles)) $styles = explode('|', trim($styles, '| '));

    if (is_array($styles)) {
        foreach ($styles as $r)
        if ($r) {
            if ($pos == POS_BEGIN) {
                $_tpl['styles'] = array(getUrlCss($r, $customTheme) => 1) + $_tpl['styles'];
            } else {
                $_tpl['styles'][getUrlCss($r, $customTheme)] = 1;
            }
        }
    }
}

/**
 * Helper for setScript
 */
function getUrlCss($r, $customTheme = null)
{
    global $_CFG;

    if ($_CFG['site']['usecdn'] && isset($_CFG['site']['cdn'][$r])) return $_CFG['site']['cdn'][$r];

    if (!$customTheme) $customTheme = getUrlTheme();
    if (strpos($r, '#themes#') !== false) {
        $r = str_replace('#themes#', $customTheme . 'style/', $r);
    } elseif (strpos($r, '//') !== false) {
        return $r;
    } elseif (strpos($r, '/') === 0) {
        $r = $customTheme . 'style' . $r;
    } else {
        $r = $_CFG['_HREF']['_style'] . $r;
    }

    if (substr($r, -4) !== '.css') {
        $r .= '.css';
    }

    return '//' . WEP_BH . $r;
}

function setScript($script, $isAuto = true)
{
    global $_tpl, $_CFG;
    if ($isAuto and !$_CFG['allowAutoIncludeScript']) return false;

    $customTheme = getUrlTheme();

    if (is_string($script)) $script = explode('|', trim($script, '|'));

    if (is_array($script)) {
        foreach ($script as $r)
        if ($r) {
            $_tpl['script'][getUrlScript($r, $customTheme)] = 1;
        }
    }
}

/**
 * Helper for setScript
 */
function getUrlScript($r, $customTheme = null)
{
    global $_CFG;

    if ($_CFG['site']['usecdn'] && isset($_CFG['site']['cdn'][$r])) return $_CFG['site']['cdn'][$r];

    if (!$customTheme) $customTheme = getUrlTheme();
    if (strpos($r, '#themes#') !== false) $r = str_replace('#themes#', $customTheme . 'script/', $r) . '.js';
    elseif (strpos($r, '//') !== false) return $r;
    elseif (strpos($r, '/') === 0) $r = $customTheme . 'script' . $r . '.js';
    else $r = $_CFG['_HREF']['_script'] . $r . '.js';

    return '//' . WEP_BH . $r;
}

/************************/
// TODO CLASS
/*************************/

$_CFG['fileIncludeOption'] = array();
function plugFancybox($init = true)
{
    global $_tpl, $_CFG;
    if (isset($_CFG['fileIncludeOption']['fancybox'])) return false;
    $url = '//' . WEP_BH . $_CFG['PATH']['vendors'] . 'fancyBox/source';

    $_CFG['fileIncludeOption']['fancybox'] = true;
    setScript($url . '/jquery.fancybox.pack.js');
    setCss($url . '/jquery.fancybox.css');
    if ($init) {
        if (!is_string($init)) $init = '.fancyimg';
        $_tpl['onloadArray']['fancybox'] = "jQuery('" . $init . "').fancybox();";
    }
}

function plugControl()
{
    global $_CFG;
    if (isset($_CFG['fileIncludeOption']['fancybox'])) return false;
    $_CFG['fileIncludeOption']['fancybox'] = true;

    setCss('fcontrol');
    setScript('fcontrol');
}

function plugForm()
{
    global $_CFG;
    if (isset($_CFG['fileIncludeOption']['form'])) return false;
    $_CFG['fileIncludeOption']['form'] = true;

    setCss('form');
    setScript('wepform');
}

function plugAjaxForm()
{
    global $_CFG;
    if (isset($_CFG['fileIncludeOption']['ajaxForm'])) return false;
    $_CFG['fileIncludeOption']['ajaxForm'] = true;

    setCss('form');
    setScript('wepform|script.jquery/form');
}

function plugJQueryUI($uiTheme = 'smoothness')
{
    global $_tpl, $_CFG;

    if (isset($_CFG['fileIncludeOption']['jquery-ui'])) return false;
    $_CFG['fileIncludeOption']['jquery-ui'] = true;

    $ui = getUrlScript('script.jquery/jquery-ui');

    if (!isset($_tpl['script'][$ui])) {
        $_tpl['script'][$ui] = array();
        setCss('style.jquery/' . $uiTheme . '/jquery-ui');
    }
}

function plugJQueryUI_datepicker($time = false)
{
    global $_tpl, $_CFG;

    if (isset($_CFG['fileIncludeOption']['datepicker'])) return false;
    $_CFG['fileIncludeOption']['datepicker'] = true;

    $ui = getUrlScript('script.jquery/jquery-ui');

    plugJQueryUI();

    $_tpl['script'][$ui][getUrlScript('script.jquery/jquery.localisation/jquery.ui.datepicker-ru')] = 1;
    if ($time) {
        $_tpl['script'][$ui][getUrlScript('script.jquery/ui-timepicker-addon')] = 1;
        setCss('style.jquery/ui-timepicker-addon');
    }
}

function plugJQueryUI_multiselect($init = true)
{
    global $_tpl, $_CFG;

    if (isset($_CFG['fileIncludeOption']['multiselect'])) return false;
    $_CFG['fileIncludeOption']['multiselect'] = true;

    $ui = getUrlScript('script.jquery/jquery-ui');

    plugJQueryUI();

    setCss('style.jquery/ui-multiselect');

    $_tpl['script'][$ui][getUrlScript('script.jquery/ui-multiselect')] = 1;
    $_tpl['script'][$ui][getUrlScript('script.jquery/jquery.localisation/ui-multiselect-ru')] = 1;

    if ($init) $_tpl['onloadArray']['multiselect'] = 'jQuery(\'select.multiple\').multiselect();';
	//#
	//$_tpl['onload'] .= '$.localise(\'ui-multiselect\', {language: \'ru\', path: \''.$_CFG['_HREF']['_script'].'script.localisation/\'});';
}

function plugQRtip($init = true)
{
    global $_CFG;
    if (isset($_CFG['fileIncludeOption']['qrtip'])) return false;
    $_CFG['fileIncludeOption']['qrtip'] = true;

    setScript('script.jquery/qrtip');
    setCss('style.jquery/qrtip');
    if ($init) {
        global $_tpl;
        $_tpl['onloadArray']['qrtip'] = 'jQuery(\'a\').qr();';
    }
}

function plugMD5()
{
    global $_CFG;
    if (isset($_CFG['fileIncludeOption']['md5'])) return false;
    $_CFG['fileIncludeOption']['md5'] = true;

    setScript('md5');
}

function plugSHL()
{
    global $_CFG;
    if (isset($_CFG['fileIncludeOption']['highlight'])) return false;
    $_CFG['fileIncludeOption']['highlight'] = true;

    setScript('highlight');
}

function plugBootstrapMultiselect($init)
{
    global $_CFG, $_tpl;
    if (isset($_CFG['fileIncludeOption']['BootstrapMultiselect'])) return false;
    $_CFG['fileIncludeOption']['BootstrapMultiselect'] = true;

    $url = '//' . WEP_BH . $_CFG['PATH']['vendors'] . 'bootstrap-multiselect/';

    plugBootstrap();

    setCss($url . 'css/bootstrap-multiselect.css');
    setScript($url . 'js/bootstrap-multiselect.js');

    if ($init) {
        if (!is_string($init)) $init = '.multiselect';
        $_tpl['onloadArray']['plugBootstrapMultiselect'] = 'wep.setEventFilterMultiselect("' . $init . '");';
    }
}

function plugBootstrap()
{
    global $_CFG;
    if (isset($_CFG['fileIncludeOption']['Bootstrap'])) return false;
    $_CFG['fileIncludeOption']['Bootstrap'] = true;

    setCss('bootstrap.css', true, POS_BEGIN);
    setCss('prettify.css', true, POS_BEGIN);
    setScript('bootstrap');
    setScript('prettify');
}

function isDebugMode()
{
    global $_CFG;
    return ($_CFG['wep']['debugmode'] > 2 ? true : false);
}

function is_cron()
{
    return (isset($_SERVER['IS_CRON']));
}

function isWin()
{
    return DIRECTORY_SEPARATOR == '\\';
}

if (!function_exists('array_column')) {
    /*
     * array_column() for PHP 5.4 and lower versions
    */
    function array_column($input, $column_key, $index_key = '')
    {
        if (!is_array($input)) return;
        $results = array();
        if ($column_key === null) {
            if (!is_string($index_key) && !is_int($index_key)) return false;
            foreach ($input as $_v) {
                if (array_key_exists($index_key, $_v)) {
                    $results[$_v[$index_key]] = $_v;
                }
            }
            if (empty($results)) $results = $input;
        } else if (!is_string($column_key) && !is_int($column_key)) {
            return false;
        } else {
            if (!is_string($index_key) && !is_int($index_key)) return false;
            if ($index_key === '') {
                foreach ($input as $_v) {
                    if (is_array($_v) && array_key_exists($column_key, $_v)) {
                        $results[] = $_v[$column_key];
                    }
                }
            } else {
                foreach ($input as $_v) {
                    if (is_array($_v) && array_key_exists($column_key, $_v) && array_key_exists($index_key, $_v)) {
                        $results[$_v[$index_key]] = $_v[$column_key];
                    }
                }
            }
        }
        return $results;
    }
}

function getSiteMapUrl($id = null)
{
    if ($id) {
        global $_CFG;
        return $_SERVER['HTTP_PROTO'] . $_SERVER['HTTP_HOST'] . '/' . $_CFG['PATH']['content'] . 'sitemap/' . md5($_SERVER['HTTP_HOST']) . '.xml.' . $id . '.gz';
    }
    return $_SERVER['HTTP_PROTO'] . $_SERVER['HTTP_HOST'] . '/sitemap.xml';
}

function getSiteMapFile($id = null)
{
    global $_CFG;
    return $_CFG['_PATH']['content'] . 'sitemap/' . md5($_SERVER['HTTP_HOST']) . '.xml' . ($id ? '.' . $id . '.gz' : '');
}

function isSiteMapXml($val = null)
{
    global $IS_SITE_MAP_XML;
    if (!is_null($val)) {
        $IS_SITE_MAP_XML = $val;
    }
    return $IS_SITE_MAP_XML;
}

static_main::autoload_register();

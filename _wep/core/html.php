<?
/*
начальные установки
*/
	$MODUL=NULL;
	if(!isset($_CFG['_PATH']['core'])) die('Can not find config file!');
	$_mctime_start = getmicrotime();// -- PAGE LOAD TIME
	$_time_start = time();
	$GLOBALS['_ERR'] ='';
	$_html='';

	//if(count($_POST)) $_POST = _fCheckVariables($_POST);
	//if(count($_GET)) $_GET = _fCheckVariables($_GET);

/*
Запуск обработчиков и перехватчиков
*/
	set_error_handler('_myErrorHandler', error_reporting());
	ob_start('_obHandler');

/*
Проверка на поискового бота
*/
	$_SERVER['robot']=SpiderDetect($_SERVER['HTTP_USER_AGENT']);

/*
Запуск сесии
*/
	if(isset($_GET['_showallinfo']) and !$_SERVER['robot']) {// and !isset($_COOKIE['_showallinfo'])
		if($_GET['_showallinfo'])
			_setcookie('_showallinfo',$_GET['_showallinfo']);
		else
			_setcookie('_showallinfo',$_GET['_showallinfo'],(time()-5000));
		$_COOKIE['_showallinfo']=$_GET['_showallinfo'];
	}

	if(!$_SERVER['robot'] and (isset($_GET['_showerror']) or $_CFG['_HREF']['arrayHOST'][0]=='i' or $_CFG['_F']['adminpage']) and !isset($_COOKIE['_showerror'])) {
		_setcookie('_showerror',1);
		$_COOKIE['_showerror']=1;
	}
	//else _setcookie('_showerror', '', (time()-5000));

	if (!defined('PHP_VERSION_ID')) {
		$version = explode('.', PHP_VERSION);
		define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
	}

/*
Модуль предстваления
Собираетв едином шаблоне мелкие блоки
*/
	class html
	{
		var $_PATHd; //дисковый путь к дизайну
		var $_design; // название дизайна
		var $_templates ='default';
		var $flag;//упрощённый режим false
		var $xslt;
		var $path = array();

		function __construct($_PATHd='design/',$_design='default',$flag=true) {
			global $_tpl, $_time_start, $_CFG;

			$this->_design = $_design;
			$this->_cDesignPath = $_CFG['_PATH']['cdesign'].$this->_design;
			$this->_templates='default';
			$this->_PATHd = $_SERVER['_DR_'].$_PATHd.$_design.'/';
			$this->flag = $flag;
			$_tpl['design'] = $_PATHd.$_design.'/';
			$_tpl['title'] = $_tpl['time']='';
			$_tpl['script'] = $_tpl['styles'] = array();
		}

		function __destruct() {
			global $_tpl,$_html;
			if($this->flag and file_exists(($this->_PATHd.'templates/'.$this->_templates.'.tpl'))){
				$_html = implode("", file($this->_PATHd.'templates/'.$this->_templates.'.tpl'));
				$_html = str_replace('"', '\"', $_html);
			}elseif($this->flag)
				$_html = 'ERROR: Mising templates file '.$this->_templates.'.tpl';

			if($this->flag){
				headerssent();
			}
		}

		function _itype($val) {
			if(is_bool($val)) return 'boolean';
			if(is_float($val)) return 'float';
			if(is_int($val)) return 'int';
			if(is_numeric($val)) return 'numeric';
			if(is_string($val)) return 'string';
			if(is_array($val)) return 'array';
			if(is_object($val)) return 'object';
			if(is_array($val)) return 'array';
			if(is_null($val)) return 'is_null';
			return 'none';
		}

		function transformPHP (&$data, $transform, $marker='') {
			/*PHP шаблонизатор*/
			if(!$marker) $marker = $transform;
			if(!isset($data[$marker])) { 
				trigger_error('В входных данных шаблона не найдены исходные данные "$data['.$marker.']"', E_USER_WARNING);
				return '';
			}
			$transformpath = $this->_PATHd.'php/'.$transform.'.php';
			if(!file_exists($transformpath)){
				trigger_error('Отсутствует файл шаблона "'.$transformpath.'"', E_USER_WARNING);
				return '';
			}
			include_once($transformpath);
			if(!function_exists('tpl_'.$transform)) {
				trigger_error('Функция "tpl_'.$transform.'" в шаблоне "'.$transformpath.'" не найдена', E_USER_WARNING);
				return '';
			}
			eval('$html =  tpl_'.$transform.'($data['.$marker.']);');
			return $html;
		}

		function transform($xml, $transform) {
			/*XML шаблонизатор*/
			//$xml = preg_replace(array("/[\x1-\x8\x0b\x0c\x0e-\x1f]+/"),'',$xml);
			$transform = $this->_PATHd.'xsl/'.$transform.'.xsl';
			if (!file_exists($transform)) {
				trigger_error("Template $transform not exists", E_USER_WARNING);
				return '';
			}
			if (!$xml) {
				trigger_error("XML empty for template $transform", E_USER_WARNING);
				return '';
			}
			//'design/default/xsl/',  'design/'.$this->_design.'/xsl/',
			$xsl = str_replace(array('\x09'), array(''),file_get_contents($transform));
			$xml = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE fragment [<!ENTITY nbsp "&#160;">]> '.$xml;
			if (extension_loaded('xsl')) {
				if(!$this->_xslt) {
					global $_CFG;
					include_once($_CFG['_PATH']['phpscript'].'/_php4xslt.php');
					$this->_xslt = xslt_create();
				}
				$arguments = array('/_xml' => $xml,'/_xsl' => $xsl);
				$result=xslt_process($this->_xslt, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);
				if (!$result)
				{
					trigger_error('Error in Template `'.$transform.'` E['.xslt_errno($this->_xslt).']:'.xslt_error($this->_xslt).'<br/>
					<div class="spoiler-wrap"><div class="spoiler-head folded clickable" onclick="clickSpoilers(this)">XML</div><div class="spoiler-body" style="display: none;"><pre><code>'.nl2br(htmlspecialchars($xml, ENT_QUOTES,'UTF-8')).'</code></pre></div></div>
					<div class="spoiler-wrap"><div class="spoiler-head folded clickable" onclick="clickSpoilers(this)">XSL</div><div class="spoiler-body" style="display: none;"><pre><code>'.nl2br(htmlspecialchars($xsl, ENT_QUOTES,'UTF-8')).'</code></pre></div></div>', E_USER_WARNING);
					return '';
				}
			}else {
				$xslt = domxml_xslt_stylesheet($xsl);
				$xml = domxml_open_mem($xml);
				$final = $xslt->process($xml);
				$result = $xslt->result_dump_mem($final);
				if (!$result)
				{
					trigger_error('DOMXML - Error in Template `'.$transform.'`<br/>', E_USER_WARNING);
					return '';
				}
				/*
				
					<div class="spoiler-wrap"><div class="spoiler-head folded clickable" onclick="clickSpoilers(this)"> Ошибки XML</div><div class="spoiler-body" style="display: none;"><pre>'.htmlentities($xml, ENT_QUOTES).'</pre></div></div>
					<div class="spoiler-wrap"><div class="spoiler-head folded clickable" onclick="clickSpoilers(this)"> Ошибки XSL</div><div class="spoiler-body" style="display: none;"><pre>'.htmlentities($xsl, ENT_QUOTES).'</pre></div></div>*/
			}
			$pos = strpos($result,'xhtml1-strict.dtd');
			if ($pos === false) return $result;
			else return substr($result,($pos+19));
		}

		function _fTestIE() {
			/*Доп функция проверки типа браузера клиента*/
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
			$browserIE = false;
			if ( stristr($user_agent, 'MSIE') ) $browserIE = true; // IE
			return $browserIE;
		}

	}
	

/*Functions**********************************/

/*
Функция обработки ошибок
*/
	function _myErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)//,$cont
	{
		global $_CFG;
		
		if($_CFG['_error'][$errno]['prior']<4) {// and error_reporting()!=0
			$debug = debugPrint(2);
		
			if (($_CFG['_F']['adminpage'] && $_CFG['wep']['bug_hunter']) || (!$_CFG['_F']['adminpage'] && $_CFG['site']['bug_hunter'])) {
				if (_new_class('bug', $MODUL)) {
					$MODUL->add_bug($errno, $errstr, $errfile, $errline, $debug);				
				}
			}
		
			if ($_CFG['_error'][$errno]['prior'] < 3)
				$errtype = '<b>'.$_CFG['_error'][$errno]['type'].'</b>';
			else
				$errtype = $_CFG['_error'][$errno]['type'];
			
			//$GLOBALS['_ERR'] .='<div style="color:'.$errorcolor[$errno].';">'.$errortype[$errno].' '.$errstr.' , in line '.$errline.' of file <i>'.$errfile.'</i><br/>'.$debug.'</div>'."\n";
			$GLOBALS['_ERR'] .='<div class="spoiler-wrap">
<div onclick="clickSpoilers(this)" class="spoiler-head folded clickable" style="color:'.$_CFG['_error'][$errno]['color'].';">'.$errtype.' '.$errstr.' , in line '.$errline.' of file <i>'.$errfile.'</i> </div>
<div class="spoiler-body" style="background-color: rgb(225, 225, 225);">'.$debug.'</div></div>'."\n";
			
			if($_CFG['_error'][$errno]['prior']==0) {
				$GLOBALS['_ERR'] .="Aborting...<br />\n";
				die();
			}
		}
	}

/*
Функция вывода на экран
*/
	function _obHandler($buffer) {
		global $_tpl,$_html,$_mctime_start,$_CFG;
		$_gerr=6;
		$htmlerr = '';
		$htmlinfo = '';
		//headerssent();

		$temp = fDisplLogs(); // сообщения ядра

		if(($GLOBALS['_ERR']!='' or $temp[1]) and $_COOKIE['_showerror'])
			$htmlerr .='<div style="background: gray;padding:2px 2px 10px 10px;">'.$GLOBALS['_ERR'].$temp[0].'</div>';
		elseif(($GLOBALS['_ERR']!='' or $temp[1]))
			$htmlerr .='<div style="background: gray;padding:2px 2px 10px 10px;">На странице возникла ошибка! Приносим свои извинения за временные неудобства! Неполадки будут исправлены в ближайшее время.</div>';
		
		if((isset($_COOKIE['_showallinfo']) and $_COOKIE['_showallinfo']) or $_CFG['_F']['adminpage']) {
			$included_files = get_included_files();
				$htmlinfo .='<div style="background-color:#6E9EEE;padding:2px;font-weight:bold;font-size:12px;border:solid 1px gray;">time='.substr((getmicrotime()-$_mctime_start),0,6).' | memory='.(int)(memory_get_usage()/1024).'Kb | maxmemory='.(int)(memory_get_peak_usage()/1024).'Kb | query='.count($_CFG['logs']['sql']).' | file include='.count($included_files).'</div>';
			if($_COOKIE['_showallinfo']>1)
				$htmlerr .='<div style="background-color:#E1E1E1;padding:2px;font-style:italic;font-size:10px;border:solid 1px gray;">SQL QUERY<br/>'.implode(';<br/>',$_CFG['logs']['sql']).'</div>';
			if($_COOKIE['_showallinfo']>2) {
				$htmlerr .='<div style="background-color:#E1E1E1;padding:2px;font-style:italic;font-size:10px;border:solid 1px gray;">LOGS <br/>'.$temp[0].'</div>';
				$htmlerr .='<div style="background-color:#E1E1E1;padding:2px;font-style:italic;font-size:10px;border:solid 1px gray;">FILE INCLUDE <br/>'.implode(';<br/>',$included_files).'</div>';
			}
		}
		if(!$_CFG['_F']['adminpage'])
			$_tpl['logs'] .= $htmlinfo.$htmlerr.$buffer;
		else {
			$_tpl['time'] .= $htmlinfo;
			$_tpl['logs'] .= $htmlerr.$buffer;
		}

		if($_html!='') {
			if($_tpl['logs']=='') 
				$_tpl['onload'] .='fShowHide(\'debug_view\');';
			eval('$_html = "'.$_html.'";');
			$page = $_html;
		}else
			$page = $_tpl['logs'];

		return $page;
	}


/*
Ф. вывода заголовков
*/

	function headerssent() {
		if (!headers_sent()){
			setlocale(LC_ALL, 'ru_RU.UTF8');
			//date_default_timezone_set('Asia/Yekaterinburg');
			header("Cache-Control: max-age=0, must-revalidate" );
			header("Last-Modified: ".gmdate("D, d M Y H:i:s", $_time_start)." GMT");
			header("Expires: ".gmdate("D, d M Y H:i:s", $_time_start)." GMT");
			header("Content-type:text/html;charset=utf-8");
			return true;
		}
		return false;
	}

/*
Ф. трасировки ошибок
*/
	function debugPrint($slice=1) {
		$MAXSTRLEN = 256;
		$s = '<div>';
		$traceArr = debug_backtrace();
		$traceArr = array_slice($traceArr,$slice); 
		$cnt = count($traceArr);
		$i=0;
		foreach ($traceArr as $arr) {
			$s .= '<div style="font-size:10px;color:#000;margin:0 0 0 '.(10*$i).'px;border-left:solid 1px #333;border-bottom:solid 1px #233;"><span style="font-size:11px;color:black;">';
			if(isset($arr['line']) and $arr['file'])
				$s .= ' #line '.$arr['line'].' #file: <a href="file:/'.$arr['file'].'" style="color:blue;">'.$arr['file'].'</a>';
			if(isset($arr['class']))
				$s .= ' #class <b>'.$arr['class'].'</b>';
			$s .= '</span>';
			$s .= '<br/>';
			$args = array();
			if(isset($arr['args']))
			foreach($arr['args'] as $v) {
				if (is_null($v)) $args[] = '<b>NULL</b>';
				else if (is_array($v)) $args[] = '<b>Array['.sizeof($v).']</b>';
				else if (is_object($v)) $args[] = '<b>Object:'.get_class($v).'</b>';
				else if (is_bool($v)) $args[] = '<b>'.($v ? 'true' : 'false').'</b>';
				else {
					$v = (string) @$v;
					$str = htmlspecialchars(substr($v,0,$MAXSTRLEN));
					if (strlen($v) > $MAXSTRLEN) $str .= '...';
					$args[] = $str;
				}
			}
			$s .= $arr['function'].'('.implode('<br/>',$args).')';
			$s .= '</div>';
			$i++;
		} 
		$s .= '</div>';
		return $s;
	}
/*
точное время в милисекундах
*/
	function getmicrotime(){
		list($usec, $sec) = explode(" ",microtime()); return ((float)$usec + (float)$sec);
	}

	function _fCheckVariables($data) {
		foreach($data as $k=>$v)
			if(is_array($data[$k])) 
				$data[$k] = _fCheckVariables($data[$k]);
			else
				$data[$k] = addslashes(trim(strip_tags($v)));
		return $data;
	}
/*
Парсер настроек модулей
*/
	function _fParseIni($filename) {
		$dest = $group = "\$data";
		$data = array();
		foreach(file($filename) as $line) {
			$line = trim($line);
			if (preg_match("/^\[(.+)\]( *)/", $line, $regs)) {
				$group = explode(":",$regs[1]);
				$dest ="\$data";
				foreach($group as $key => $grp) $dest .= "[\$group[$key]]";
			}	else {
				if (preg_match("/^(\\\"[^\\\"]*\\\"|[^=]*) *= *(\\\"[^\\\"]*\\\"|.*)$/", $line, $regs)) {
					if ($regs[1][0] == "\"") $regs[1] = substr($regs[1], 1, -1);
					if ($regs[2][0] == "\"") $regs[2] = substr($regs[2], 1, -1);
					if (strpos($regs[2], "|")) $regs[2] = explode("|", $regs[2]);
					eval($dest."[\$regs[1]]=\$regs[2];");
				}
			}
		}
		return $data;
		//return parse_ini_file($filename,true);
	}

/*
Используем эту ф вместо стандартной, для совместимости с UTF-8
*/
	if(function_exists('mb_internal_encoding'))
		mb_internal_encoding($_CFG['wep']['charset']);

	function _strlen($val) {
		if(function_exists('mb_strlen'))
			return mb_strlen($val);
		else return strlen($val);
	}
	function _substr($s, $offset, $len = NULL)
	{
		if($len != NULL) {
			if(function_exists('mb_substr'))
				return mb_substr($s, $offset, $len);
			else
				return substr($s, $offset, $len);
		}else {
			if(function_exists('mb_substr'))
				return mb_substr($s, $offset);
			else
				return substr($s, $offset);
		}
	}
	function _strtolower($txt) {
		if(function_exists('mb_strtolower'))
			return mb_strtolower($txt);
		else
			return strtolower($txt);
	}
	function _mb_strpos($haystack,$needle, $offset=0) {
		if(function_exists('mb_strpos'))
			return mb_strpos($haystack,$needle, $offset);
		else
			return strpos($haystack,$needle, $offset);
	}

/*
Функция SpiderDetect - принимает $_SERVER['HTTP_USER_AGENT'] и возвращает имя кравлера поисковой системы или false.
*/
	function SpiderDetect($USER_AGENT)
	{
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
		 array('YaDirectBot', 'Yandex Direct')
		 );
		 
		 foreach ($engines as $engine)
		 {
			  if (strstr($USER_AGENT, $engine[0]))
			  {
					return $engine[1];
			  }
		 }

		 return '';
	}

/*
Инициализация модулей
*/
	function _new_class($name,&$MODUL,&$OWNER = NULL) {
		global $SQL, $_CFG;
		
		if (isset($_CFG['singleton'][$name])) {
			$MODUL = $_CFG['singleton'][$name];
			return true;
		}
		else {				
			$MODUL=NULL;
			if(!$SQL)  {
				trigger_error("SQL class missing.", E_USER_WARNING);
				return false;
			}
			$clsn = $name."_class";
			try {
				require_once($_CFG['_PATH']['core'].'kernel.extends.php');
					global $OWN_CL;
					
					$OWN_CL = NULL;
					if($OWNER and $OWNER->_cl) {						
						$TEMPO = &$OWNER;
						while($TEMPO->owner and $TEMPO->owner->_cl)
							$TEMPO = &$TEMPO->owner;
						$OWN_CL = $TEMPO->_cl;
					}

				eval('$MODUL = new '.$clsn.'($SQL,$OWNER);');
				
				if ($MODUL->singleton == true)
					$_CFG['singleton'][$name] = &$MODUL;
				
				$OWN_CL = NULL;
				if($MODUL)
					return true;
			}
			catch (Exception $e) {
				trigger_error($e->getMessage(), E_USER_WARNING);
			}
			return false;
		}
	}
/*
Автозагрузка модулей
*/
	function __autoload($class_name){ //автозагрузка модулей
		global $_CFG;
		require_once($_CFG['_PATH']['core'].'kernel.extends.php');
		if($file = _modulExists($class_name))
			require_once($file);
		else
			throw new Exception('Невозможно подключить класс "'.$class_name.'"');
	}

/*
Проверка существ модуля
*/
	function _modulExists($class_name) {
		global $_CFG,$OWN_CL;
		$file = false;
		$classparam = explode('_',$class_name);
		if($OWN_CL)
			$clpath = $OWN_CL.'.class';
		else
			$clpath = $classparam[0].'.class';
		if(!$classparam[1]) $classparam[1] = 'class';
		$clname = $classparam[0].'.'.$classparam[1];

		if(file_exists($_CFG['_PATH']['ext'].$clpath.'/'.$clname.'.php'))
			$file = $_CFG['_PATH']['ext'].$clpath.'/'.$clname.'.php';
		elseif(file_exists($_CFG['_PATH']['extcore'].$clpath.'/'.$clname.'.php'))
			$file = $_CFG['_PATH']['extcore'].$clpath.'/'.$clname.'.php';

		elseif(file_exists($_CFG['_PATH']['ext'].$classparam[0].'.extend'.'/'.$clname.'.php'))
			$file = $_CFG['_PATH']['ext'].$classparam[0].'.extend'.'/'.$clname.'.php';
		elseif(file_exists($_CFG['_PATH']['extcore'].$classparam[0].'.extend'.'/'.$clname.'.php'))
			$file = $_CFG['_PATH']['extcore'].$classparam[0].'.extend'.'/'.$clname.'.php';
		
		return $file;
	}
/*
Вывод названия таблицы у класса , без его подключения,
главное чтобу в модуле не было указано явно свое название табл
*/
	function getTableNameOfClass($name) {
		global $_CFG;
		if(!isset($_CFG['modulprm'])) _prmModulLoad();
		if($_CFG['modulprm'][$name]['tablename'])
			return $_CFG['modulprm'][$name]['tablename'];
		else
			return $_CFG['sql']['dbpref'].$name;
	}

/*
Проверка доступа пол-ля к модулю
*/
	function _prmModul($mn,$param=array()) {
		global $_CFG;
		if(!isset($_CFG['modulprm'])) _prmModulLoad();
		if(!isset($_CFG['modulprm'][$mn])) return false; // отказ, если модуль отключен
		if(isset($_SESSION['user']['level']) and $_SESSION['user']['level']==0) return true; // админу можно всё
		if($_SESSION['user']['level']>=5) return false; //этим всё запрещено
		else {
			if(isset($_CFG['modulprm'][$mn]['access'][0])) return false;
			if(count($param))
				foreach($param as $r)
					if(isset($_CFG['modulprm'][$mn]['access'][$r])) return true;
		}
		return false;
	}

	function _prmModulLoad() { // подгрука данных прав доступа
		global $_CFG;
		if(!isset($_CFG['modulprm'])) {
			if(_new_class('modulprm',$MODULs))
				$_CFG['modulprm'] = $MODULs->userPrm((int)$_SESSION['user']['owner_id']);
		}
	}
/*
Проверка доступа пол-ля по уровню привелегии
*/
	function _prmUserCheck($level=5)
	{
		global $_CFG;
		if(isset($_SESSION['user']['level'])) {
			if($_SESSION['user']['level']<=$level)
				return true;
		}
		return false;
	}
/*
Ф. авторизации пользователя
*/
	function  userAuth($login='',$pass='') {
		global $_CFG,$SQL,$UGROUP;
		session_go(1);
		$result = array('',0);
		if(!isset($_SESSION['user']['id']) or $login) {
			//$SQL->_iFlag = 1; // проверка табл
			if($SQL->_iFlag) _new_class('modulprm',$MODULtemp);
			if($_CFG['wep']['access'] and _new_class('ugroup',$UGROUP)) {
				if(isset($_POST['login']) or $login)
					$result = $UGROUP->childs['users']->authorization($login,$pass);
				else
					$result = $UGROUP->childs['users']->cookieAuthorization();
			}
			elseif($_CFG['wep']['login'] and $_CFG['wep']['password']) {
				$flag = 0;
				if($_COOKIE['remember'] and $_CFG['wep']['login']==substr($_COOKIE['remember'],($pos+1)) and md5($_CFG['wep']['password'])==substr($_COOKIE['remember'],0,$pos))
					$flag = 1;
				elseif($login and $pass and $_CFG['wep']['login']==$login and $_CFG['wep']['password']==$pass)
					$flag = 1;
				if($flag) {
					$_SESSION['user']['name'] = $_CFG['wep']['name'];
					$_SESSION['user']['gname'] = "GOD MODE";
					$_SESSION['user']['id'] = $_CFG['wep']['login'];
					$_SESSION['user']['level']= 0;
					$_SESSION['user']['wep']= 1;
					$_SESSION['user']['design'] = $_CFG['wep']['design'];
					$_SESSION['user']['filesize']= $_CFG['wep']['def_filesize'];
					$_SESSION['FckEditorUserFilesUrl'] = $_CFG['_HREF']['BH'].$_CFG['PATH']['userfile'];
					$_SESSION['FckEditorUserFilesPath'] = $_CFG['_PATH']['path'].$_CFG['PATH']['userfile'];
					if($_POST['remember']=='1')
						_setcookie('remember', md5($_CFG['wep']['password']).'_'.$_CFG['wep']['login'], $_CFG['remember_expire']);
					$result = array($_CFG['_MESS']['authok'],1);
					_setcookie('_showerror',1);
					//$_COOKIE['_showerror']=1;
				}
			}
		}
		else {
			if(!$UGROUP)
				_new_class('ugroup',$UGROUP);
			$result = array($_CFG['_MESS']['authok'],1);
		}
		return $result;
	}

	function  userExit() {
		global $_CFG;
		session_go();
		if(isset($_SESSION))
			$_SESSION = array();
		if(isset($_COOKIE['remember']))
			_setcookie('remember', '', (time()-5000));
		if(isset($_COOKIE[$_CFG['session']['name']]))
			_setcookie($_CFG['session']['name'], '', (time()-5000));
		//_showerror
		//
	}

	function fDisplLogs($type=0){
		global $_CFG;
		//0 - все
		//1 - кроме 'ok'
		//2 - кроме 'ok', 'modify'
		//3 - только 'error'
		$text='';
		$flag=0;
		if(count($_CFG['logs']['mess']))
		{
			foreach($_CFG['logs']['mess'] as $r)
			{
				$c='';
				if($r[1]=='error') {
					$c='red';
					$flag=1;
				}
				elseif($r[1]=='warning' and $type<3) $c='yellow'; 
				elseif($r[1]=='modify' and $type<2) $c='blue';
				elseif($r[1]=='ok' and $type<1) $c='green';
				elseif($type<3) $c='gray'; 
				
				if($c!='')
					$text .='<div style="color:'.$c.';" class="messelem">'.date('H:i:s').' : '.addslashes($r[0]).'</div>';
			}
			return array($text,$flag);
		}
		return array($text,$flag);
	}

?>

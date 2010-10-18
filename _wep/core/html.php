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
	if(isset($_GET['_showallinfo']) and !$_SERVER['robot'] and !isset($_COOKIE['_showallinfo'])) {
		setcookie('_showallinfo',$_GET['_showallinfo'], (time()+86400), '/', $_SERVER['HTTP_HOST2']);
		$_COOKIE['_showallinfo']=$_GET['_showallinfo'];
	}

	if(!$_SERVER['robot'] and (isset($_GET['_showerror']) or strpos($_SERVER['HTTP_HOST'],'.l') or strpos($_SERVER['HTTP_HOST'],'.i')) and !$_COOKIE['_showerror']) {
		setcookie('_showerror',1, (time()+86400), '/', $_SERVER['HTTP_HOST2']);
		$_COOKIE['_showerror']=1;
	}

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

			if (extension_loaded('xsl')){
				include_once($_CFG['_PATH']['phpscript'].'/_php4xslt.php');
				$this->_xslt = xslt_create();
			}
			$this->_design = $_design;
			$this->_cDesignPath = $_CFG['_PATH']['cdesign'].$this->_design;
			$this->_templates='default';
			$this->_PATHd = $_SERVER['_DR_'].'/'.$_PATHd.$_design.'/';
			$this->flag = $flag;

			if($flag){
				headerssent();
				$_tpl['design']='/'.$_PATHd.$_design.'/';
				$_tpl['title']=$_tpl['time']='';
				$_tpl['styles'] ='<link rel="stylesheet" href="'.$_tpl['design'].'style/style.css" type="text/css"/>';
				$_tpl['script'] ='<script type="text/javascript" src="'.$_tpl['design'].'script/script.js"></script>';
			}
		}

		function __destruct() {
			global $_tpl,$_html;
			//$GLOBALS['__post'] = var_export($_POST,true);
			//unset($_SERVER['message']);
			//$GLOBALS['__server'] = var_export($_SERVER,true);
			if($this->flag and file_exists(($this->_PATHd.'templates/'.$this->_templates.'.tpl'))){
				$_html = implode("", file($this->_PATHd.'templates/'.$this->_templates.'.tpl'));
				$_html = str_replace('"', '\"', $_html);
			}
			//print_r('  --- D_HTML');
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
				trigger_error("Marker $marker not exists in data", E_USER_WARNING);
				return '';
			}
			$transformpath = $this->_PATHd.'php/'.$transform.'.php';
			include_once($transformpath);
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
		$errortype = array (
				0					=> '[@]',
				E_ERROR			=> '<b>[Fatal Error]</b>',
				E_WARNING		=> '<b>[Warning]</b>',
				E_PARSE			=> '<b>[Parse Error]</b>',
				E_NOTICE			=> '[Notice]',
				E_CORE_ERROR		=> '<b>[Fatal Core Error]</b>',
				E_CORE_WARNING		=> '<b>[Core Warning]</b>',
				E_COMPILE_ERROR	=> '<b>[Compilation Error]</b>',
				E_COMPILE_WARNING	=> '<b>[Compilation Warning]</b>',
				E_USER_ERROR		=> '<b>[Triggered Error]</b>',
				E_USER_WARNING		=> '<b>[Triggered Warning</b>',
				E_USER_NOTICE		=> '[Triggered Notice]',
				E_STRICT				=> '[Deprecation Notice]',
				E_RECOVERABLE_ERROR	=> '<b>[Catchable Fatal Error]</b>'
		);
		$errorcolor = array (
				0					=> 'black',
				E_ERROR			=> 'red',
				E_WARNING		=> 'yellow',
				E_PARSE			=> 'red',
				E_NOTICE			=> 'black',
				E_CORE_ERROR		=> 'red',
				E_CORE_WARNING		=> 'yellow',
				E_COMPILE_ERROR	=> 'red',
				E_COMPILE_WARNING	=> 'yellow',
				E_USER_ERROR		=> 'red',
				E_USER_WARNING		=> 'yellow',
				E_USER_NOTICE		=> 'black',
				E_STRICT				=> 'pink',
				E_RECOVERABLE_ERROR	=> 'red'
		);

		$prior = array (
				0					=> 6,
				E_ERROR			=> 0,
				E_WARNING		=> 1,
				E_PARSE			=> 0,
				E_NOTICE			=> 5,
				E_CORE_ERROR		=> 0,
				E_CORE_WARNING		=> 1,
				E_COMPILE_ERROR	=> 0,
				E_COMPILE_WARNING	=> 1,
				E_USER_ERROR		=> 0,
				E_USER_WARNING		=> 2,
				E_USER_NOTICE		=> 3,
				E_STRICT				=> 4,
				E_RECOVERABLE_ERROR	=> 0
		);
		$_gerr=6;
		if($prior[$errno]<4) {// and error_reporting()!=0
			$debug = debugPrint(2);
			$GLOBALS['_ERR'] .='<div style="color:'.$errorcolor[$errno].';">'.$errortype[$errno].' '.$errstr.' , in line '.$errline.' of file <i>'.$errfile.'</i><br/>'.$debug.'</div>'."\n";
			if($prior[$errno]<$_gerr) $_gerr=$prior[$errno];
			if($prior[$errno]==0) {
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
		headerssent();

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
			if($_tpl['logs']!='' and $_CFG['_F']['adminpage']) 
				$_tpl['onload'] .='if($(\'#debug_view\').html()!=\'\') fShowDebug(\'debug_view\');';
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
		//return '<pre>'.print_r($traceArr,true).'</pre>';
		$cnt = count($traceArr);
		$i=0;
		foreach ($traceArr as $arr) {
			$s .= '<div style="font-size:10px;color:#000;margin:0 0 0 '.(10*$i).'px;border:solid 1px #333;"><span style="font-size:11px;color:black;border-bottom:solid 1px #333;">';
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
	function _strlen($val) {
		global $_CFG;
		//mb_internal_encoding("UTF-8");
		//return mb_strlen($val);
		//if(mb_internal_encoding()) return mb_strlen($val);
		if($_CFG['sql']['setnames']=='utf8') return preg_match_all('/./u', $val, $tmp);
		else return strlen($val);
	}

/*
Используем эту ф вместо стандартной, для совместимости с UTF-8
*/
	function _substr($s, $offset, $len = 'all')
	{
		if($len==0 and $len != 'all') return '';
		global $_CFG;
		if($_CFG['sql']['setnames']=='utf8') {
			if ($offset<0) $offset = _strlen($s) + $offset;
			if ($len!='all')
			{
			  if ($len<0) $len = _strlen($s) - $offset + $len;
			  $xlen = _strlen($s) - $offset;
			  $len = ($len>$xlen) ? $xlen : $len;
			  preg_match('/^.{' . $offset . '}(.{0,'.$len.'})/us', $s, $tmp);
			}
			else
			{
			  preg_match('/^.{' . $offset . '}(.*)/us', $s, $tmp);
			}
			return (isset($tmp[1])) ? $tmp[1] : false;
		}else
			return substr($s, $offset, $len);
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


/*OLD ADMIN*/
	function fXmlSysconf(){
		global $_CFG;
		$template = array();
		$template['sysconf']['user'] = $_SESSION['user'];
		if($_SESSION['user']['level']<=1) {
			$data = array();
			$dir = dir($_CFG['_PATH']['extcore']);
			while (false !== ($entry = $dir->read())) {
				if ($entry!='.' && $entry!='..' && $pos=strpos($entry, '.class')) {
					$entry = _substr($entry, 0, $pos);
					if($entry!='') {
						if(_prmModul($entry,array(1,2))) {
							_modulprm();
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
		if(isset($_SESSION['user']) and count($_SESSION['user']['weppages'])) {
			foreach($_SESSION['user']['weppages'] as $k=>$r0)
				$template['sysconf']['item'][$k] = $r;
		}
		return $template;
	}

	function fXmlModulslist() {
		global $_CFG;
		$template['modulslist']['modul'] = $_GET['_modul'];
		$template['modulslist']['user'] = $_SESSION['user'];

		$dir = dir($_CFG['_PATH']['ext']);
		while (false !== ($entry = $dir->read())) {
			if ($entry!='.' && $entry!='..' && $pos=strpos($entry, '.class')) {
				$k = _substr($entry, 0, $pos);
				if($k!='') {
					if(_prmModul($k,array(1,2))) {
						_modulprm();
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
/*---------------OLD ADMIN*/


/*
Автозагрузка модулей
*/
	function __autoload($class_name){ //автозагрузка модулей
		global $_CFG;
		require_once($_CFG['_PATH']['core'].'kernel.extends.php');
		$class_name = str_replace('_','.',$class_name);
		if(file_exists($_CFG['_PATH']['ext'].$class_name.'/'.$class_name.'.php'))
			require_once($_CFG['_PATH']['ext'].$class_name.'/'.$class_name.'.php');
		elseif(file_exists($_CFG['_PATH']['extcore'].$class_name.'/'.$class_name.'.php'))
			require_once($_CFG['_PATH']['extcore'].$class_name.'/'.$class_name.'.php');
	}
/*
Инициализация модулей
*/
	function _new_class($name,&$MODUL) {
		global $SQL;
		$MODUL=NULL;
		if(!$SQL)  {
			trigger_error("SQL class missing.", E_USER_WARNING);
			return false;
		}
		if(_modulExists($name)) {
			$clsn = $name."_class";
			eval('$MODUL = new '.$clsn.'($SQL);');
			return true;
		}
		return false;
	}
/*
Проверка существ модуля
*/
	function _modulExists($modul) {
		global $_CFG;
		if(file_exists($_CFG['_PATH']['extcore'].$modul.'.class/'.$modul.'.class.php'))
			return true;
		elseif(file_exists($_CFG['_PATH']['ext'].$modul.'.class/'.$modul.'.class.php'))
			return true;
		else return false;
	}
/*
Вывод названия таблицы у класса , без его подключения,
главное чтобу в модуле не было указано явно свое название табл
*/
	function getTableNameOfClass($name) {
		global $_CFG;
		_modulprm();
		if($_CFG['modulprm'][$name]['tablename'])
			return $_CFG['modulprm'][$name]['tablename'];
		else
			return $_CFG['sql']['dbpref'].$name;
	}

/*
Проверка доступа пол-ля к модулю
*/
	function _prmModul($mn,$param=array()) {
		if(isset($_SESSION['user']['level']) and $_SESSION['user']['level']==0) return true;
		elseif($_SESSION['user']['level']>=5) return false;
		else{
			global $_CFG;
			_modulprm();
			if(isset($_CFG['modulprm'][$mn]['access'][0])) return false;
			if(count($param))
				foreach($param as $k=>$r)
					if(isset($_CFG['modulprm'][$mn]['access'][$r])) return true;
		} 
		return false;
	}

	function _modulprm() {
		global $_CFG;
		if(!isset($_CFG['modulprm'])) {
			if(_new_class('modulprm',$MODULs))
				$_CFG['modulprm'] = $MODULs->userPrm();
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
		if(!isset($_SESSION['user']) or $login) {
			$SQL->_iFlag = 1; // проверка табл
			if($_CFG['wep']['access'] and _new_class('ugroup',$UGROUP)) {
				if($login) {
					$result = $UGROUP->childs['users']->authorization($login,$pass);
				}
				if(!$result[1] and !$login)
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
						setcookie('remember', md5($_CFG['wep']['password']).'_'.$_CFG['wep']['login'], (time()+$_CFG['remember_expire']));
					$result = array($_CFG['_MESS']['authok'],1);
					setcookie('_showerror',1);
					$_COOKIE['_showerror']=1;
				}
			}
		}
		elseif($_SESSION['user']['id'])
			$result = array($_CFG['_MESS']['authok'],1);
		return $result;
	}

	function  userExit() {
		$_SESSION = array();
		setcookie('remember', '', (time()-5000),'/', $_SERVER['HTTP_HOST2']);
		setcookie('wepID', '', (time()-5000),'/', $_SERVER['HTTP_HOST2']);
		//_showerror
		//print_r(session_get_cookie_params());
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

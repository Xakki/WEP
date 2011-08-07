<?

/*
  начальные установки
 */
 global $_CFG;
$MODUL = NULL;
if (!isset($_CFG['_PATH']['core']))
	die('Can not find config file!');
$_mctime_start = getmicrotime(); // -- PAGE LOAD TIME

$_html = '';

//if(count($_POST)) $_POST = _fCheckVariables($_POST);
//if(count($_GET)) $_GET = _fCheckVariables($_GET);

/*
  Запуск обработчиков и перехватчиков
 */
set_error_handler('_myErrorHandler', error_reporting());
ob_start('_obHandler');

if (!isset($_COOKIE['_showallinfo']))
	$_COOKIE['_showallinfo'] = 0;
if (isset($_GET['_showallinfo']) and !$_CFG['robot']) {// and !isset($_COOKIE['_showallinfo'])
	if ($_GET['_showallinfo'])
		_setcookie('_showallinfo', $_GET['_showallinfo']);
	else
		_setcookie('_showallinfo', $_GET['_showallinfo'], (time() - 5000));
	$_COOKIE['_showallinfo'] = $_GET['_showallinfo'];
}
// or $_CFG['_F']['adminpage']
if (!$_CFG['robot'] and (isset($_GET['_showerror']) or $_CFG['_HREF']['arrayHOST'][0] == 'i') and !isset($_COOKIE['_showerror'])) {
	_setcookie('_showerror', 1);
	$_COOKIE['_showerror'] = 1;
}
//else _setcookie('_showerror', '', (time()-5000));


/*
  Модуль предстваления
  Собираетв едином шаблоне мелкие блоки
 */

class html {

	var $_PATHd; //дисковый путь к дизайну
	var $_design; // название дизайна
	var $_templates = 'default';
	var $flag; //упрощённый режим false
	var $xslt;
	var $path = array();

	function __construct($_PATHd='design/', $_design='default', $flag=true) {
		global $_tpl, $_CFG;

		$this->_design = $_design;
		$this->_cDesignPath = $_CFG['_PATH']['cdesign'] . $this->_design;
		$this->_templates = 'default';
		$this->_PATHd = $_SERVER['_DR_'] . $_PATHd . $_design . '/';
		$this->flag = $flag;
		$_tpl['design'] = $_CFG['_HREF']['BH'] . $_PATHd . $_design . '/';
		$_tpl['title'] = $_tpl['time'] = $_tpl['onload'] = $_tpl['logs'] = '';
		$_tpl['script'] = $_tpl['styles'] = array();
		$params = array(
			'obj' => &$this,
			'func' => 'createTemplate',
		);
		observer::register_observer($params, 'shutdown_function');
	}

	function createTemplate() {
		global $_tpl, $_html, $_CFG;
		$file = $this->_PATHd . 'templates/' . $this->_templates . '.tpl';
		if ($this->flag and file_exists($file)) {
			$_html = file_get_contents($file);
			$_html = addcslashes($_html,'"\\');
			include_once($_CFG['_PATH']['core'] . '/includesrc.php');
			fileInclude($_CFG['fileIncludeOption']);
			arraySrcToStr();
		} elseif ($this->flag)
			$_html = 'ERROR: Mising templates file ' . $this->_templates . ' - ' . $file;

		if ($this->flag) {
			headerssent();
		} else {
			/* include_once($_CFG['_PATH']['core'].'/includesrc.php');
			  fileInclude($_CFG['fileIncludeOption']);
			  arraySrcToFunc();
			  $GLOBALS['_RESULT']['eval'] = $_tpl['onload']; */
		}
	}

	function _itype($val) {
		if (is_bool($val))
			return 'boolean';
		if (is_float($val))
			return 'float';
		if (is_int($val))
			return 'int';
		if (is_numeric($val))
			return 'numeric';
		if (is_string($val))
			return 'string';
		if (is_array($val))
			return 'array';
		if (is_object($val))
			return 'object';
		if (is_array($val))
			return 'array';
		if (is_null($val))
			return 'is_null';
		return 'none';
	}

	function transformPHP(&$data, $transform, $marker='') {
		/* PHP шаблонизатор */
		if(is_array($transform)) {
			$transformPath = $transform[1];
			$transform = $transform[0];
		}else
			$transformPath = $this->_PATHd . 'php/';
		if (!$marker)
			$marker = $transform;
		if (!isset($data[$marker])) {
			trigger_error('В входных данных шаблона не найдены исходные данные "$data[' . $marker . ']"', E_USER_WARNING);
			return '';
		}
		$transformpath =  $transformPath. $transform . '.php';
		if (!file_exists($transformpath)) {
			trigger_error('Отсутствует файл шаблона "' . $transformpath . '"', E_USER_WARNING);
			return '';
		}
		include_once($transformpath);
		if (!function_exists('tpl_' . $transform)) {
			trigger_error('Функция "tpl_' . $transform . '" в шаблоне "' . $transformpath . '" не найдена', E_USER_WARNING);
			return '';
		}
		eval('$html =  tpl_' . $transform . '($data["' . $marker . '"]);');
		return $html;
	}

	function transform($xml, $transform) {
		/* XML шаблонизатор */
		//$xml = preg_replace(array("/[\x1-\x8\x0b\x0c\x0e-\x1f]+/"),'',$xml);
		$transform = $this->_PATHd . 'xsl/' . $transform . '.xsl';
		if (!file_exists($transform)) {
			trigger_error("Template $transform not exists", E_USER_WARNING);
			return '';
		}
		if (!$xml) {
			trigger_error("XML empty for template $transform", E_USER_WARNING);
			return '';
		}
		//'design/default/xsl/',  'design/'.$this->_design.'/xsl/',
		$xsl = str_replace(array('\x09'), array(''), file_get_contents($transform));
		$xml = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE fragment [<!ENTITY nbsp "&#160;">]> ' . $xml;
		if (extension_loaded('xsl')) {
			if (!isset($this->_xslt)) {
				global $_CFG;
				include_once($_CFG['_PATH']['phpscript'] . '/_php4xslt.php');
				$this->_xslt = xslt_create();
			}
			$arguments = array('/_xml' => $xml, '/_xsl' => $xsl);
			$result = xslt_process($this->_xslt, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);
			if (!$result) {
				trigger_error('Error in Template `' . $transform . '` E[' . xslt_errno($this->_xslt) . ']:' . xslt_error($this->_xslt) . '<br/>
					<div class="spoiler-wrap"><div class="spoiler-head folded clickable" onclick="bugSpoilers(this)">XML</div><div class="spoiler-body" style="display: none;">' . nl2br(htmlspecialchars($xml, ENT_QUOTES, 'UTF-8')) . '</div></div>
					<div class="spoiler-wrap"><div class="spoiler-head folded clickable" onclick="bugSpoilers(this)">XSL</div><div class="spoiler-body" style="display: none;">' . nl2br(htmlspecialchars($xsl, ENT_QUOTES, 'UTF-8')) . '</div></div>', E_USER_WARNING);
				return '';
			}
		} else {
			$xslt = domxml_xslt_stylesheet($xsl);
			$xml = domxml_open_mem($xml);
			$final = $xslt->process($xml);
			$result = $xslt->result_dump_mem($final);
			if (!$result) {
				trigger_error('DOMXML - Error in Template `' . $transform . '`<br/>', E_USER_WARNING);
				return '';
			}
			/*

			  <div class="spoiler-wrap"><div class="spoiler-head folded clickable" onclick="bugSpoilers(this)"> Ошибки XML</div><div class="spoiler-body" style="display: none;"><pre>'.htmlentities($xml, ENT_QUOTES).'</pre></div></div>
			  <div class="spoiler-wrap"><div class="spoiler-head folded clickable" onclick="bugSpoilers(this)"> Ошибки XSL</div><div class="spoiler-body" style="display: none;"><pre>'.htmlentities($xsl, ENT_QUOTES).'</pre></div></div> */
		}
		$pos = strpos($result, 'xhtml1-strict.dtd');
		if ($pos === false)
			return $result;
		else
			return substr($result, ($pos + 19));
	}

	function _fTestIE() {
		/* Доп функция проверки типа браузера клиента */
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$browserIE = false;
		if (stristr($user_agent, 'MSIE'))
			$browserIE = true; // IE
 return $browserIE;
	}

}

/* Functions********************************* */

/*
  Функция обработки ошибок
 */

function _myErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {//,$cont
	global $_CFG;
	if ($_CFG['wep']['catch_bug']) {

		// Debuger
		// для вывода отладчика для всех типов ошибок , можно отключить это условие
		if (isset($_CFG['wep']['bug_hunter'][$errno]) and $_CFG['_error'][$errno]['debug'])
			$debug = '<div class="spoiler-body" style="background-color: rgb(225, 225, 225);">' . debugPrint(2) . '</div>';
		else
			$debug = '';

		// write bug to DB
		if (isset($_CFG['wep']['bug_hunter'][$errno])) {
			if (_new_class('bug', $MODUL)) {
				$MODUL->add_bug($errno, $errstr, $errfile, $errline, $debug);
			}
		}

		//выделяем жирным шрифтом особо опасные ошибки
		if ($_CFG['_error'][$errno]['prior'] <= 3) {
			$type = 0;
			$errtype = '<b>' . $_CFG['_error'][$errno]['type'] . '</b>';
		} else {
			$type = 1;
			$errtype = $_CFG['_error'][$errno]['type'];
		}

		if (!isset($GLOBALS['_ERR'][$_CFG['wep']['catch_bug']][$type]))
			$GLOBALS['_ERR'][$_CFG['wep']['catch_bug']][$type] = '';

		if ($debug)
			$GLOBALS['_ERR'][$_CFG['wep']['catch_bug']][$type] .='<div class="spoiler-wrap"><div onclick="bugSpoilers(this)" class="spoiler-head folded clickable" style="color:' . $_CFG['_error'][$errno]['color'] . ';">' . $errtype . ' ' . $errstr . ' , in line ' . $errline . ' of file <i>' . $errfile . '</i> </div>' . $debug . '</div>' . "\n";
		else
			$GLOBALS['_ERR'][$_CFG['wep']['catch_bug']][$type] .='<div style="color:' . $_CFG['_error'][$errno]['color'] . ';">' . $errtype . ' ' . $errstr . ' , in line ' . $errline . ' of file <i>' . $errfile . '</i> </div>' . "\n";
		//остановка на фатальной ошибке
		if ($_CFG['_error'][$errno]['prior'] == 0 and $_CFG['wep']['stop_fatal_error']) {
			$GLOBALS['_ERR'][$_CFG['wep']['catch_bug']][$type] .="Aborting...<br />\n";
			die();
		}
	}
}

function startCatchError($param=2) {
	global $_CFG;
	if ($param < 2)
		$param = 2;
	$_CFG['_ctemp' . $param]['catch_bug'] = $_CFG['wep']['catch_bug'];
	$_CFG['_ctemp' . $param]['bug_hunter'] = $_CFG['wep']['bug_hunter'];
	$_CFG['_ctemp' . $param]['stop_fatal_error'] = $_CFG['wep']['stop_fatal_error'];
	$_CFG['wep']['catch_bug'] = $param;
	$_CFG['wep']['bug_hunter'] = array();
	$_CFG['wep']['stop_fatal_error'] = 0;
	return true;
}

function getCatchError($param=2) {
	global $_CFG;
	if ($param < 2)
		$param = 2;
	$_CFG['wep']['catch_bug'] = $_CFG['_ctemp' . $param]['catch_bug'];
	$_CFG['wep']['bug_hunter'] = $_CFG['_ctemp' . $param]['bug_hunter'];
	$_CFG['wep']['stop_fatal_error'] = $_CFG['_ctemp' . $param]['stop_fatal_error'];
	if (isset($GLOBALS['_ERR'][$param])) {
		$return = $GLOBALS['_ERR'][$param];
		unset($GLOBALS['_ERR'][$param]);
	} else
		$return = array(0 => '', 1 => '');
	return $return;
}

/*
  Функция вывода на экран
 */

function _obHandler($buffer) {
	global $_tpl, $_html, $_mctime_start, $_CFG;

	//TODO : Нафига тут хендлер?
	//headerssent();

	$temp = fDisplLogs(); // сообщения ядра
	$htmlinfo = '';
	$notice = '';
	$htmlerr = '';
	foreach ($GLOBALS['_ERR'] as $k => $r) {
		if (isset($r[0]))
			$htmlerr .= $r[0];
		if (isset($r[1])) //нотисы отдельно
			$notice .= $r[1];
	}
	if (($htmlerr != '' or $notice != '' or $temp[1]) and ($_COOKIE['_showerror'] or $_CFG['site']['show_error'] == 2)) {
		$htmlerr = '<div class="bugmain">' . $htmlerr;
		if ($temp[1] and $temp[0])
			$htmlerr .= $temp[0];
		if ($notice)
			$htmlerr .= '<div class="spoiler-wrap"><div onclick="bugSpoilers(this)" class="spoiler-head folded clickable">NOTICE</div><div class="spoiler-body">' . $notice . '</div></div>' . "\n";
	}
	elseif (($htmlerr != '' or $temp[1]) and $_CFG['site']['show_error'] == 1)
		$htmlerr = '<div class="bugmain">На странице возникла ошибка! Приносим свои извинения за временные неудобства! Неполадки будут исправлены в ближайшее время.</div>';

	if ((isset($_COOKIE['_showallinfo']) and $_COOKIE['_showallinfo']) or $_CFG['_F']['adminpage']) {
		$included_files = get_included_files();
		$htmlinfo .='time=' . substr((getmicrotime() - $_mctime_start), 0, 6) . ' | memory=' . (int) (memory_get_usage() / 1024) . 'Kb | maxmemory=' . (int) (memory_get_peak_usage() / 1024) . 'Kb | query=' . count($_CFG['logs']['sql']) . ' | file include=' . count($included_files);
		if ($_COOKIE['_showallinfo'] > 1 and count($_CFG['logs']['sql']) > 0)
			$htmlerr .='<div class="spoiler-wrap"><div onclick="bugSpoilers(this)" class="spoiler-head folded clickable">SQL QUERY</div><div class="spoiler-body">' . implode(';<br/>', $_CFG['logs']['sql']) . '</div></div>';
		if ($_COOKIE['_showallinfo'] > 2) {
			if (!$temp[1] and $temp[0])
				$htmlerr .='<div class="spoiler-wrap"><div onclick="bugSpoilers(this)" class="spoiler-head folded clickable">LOGS</div><div class="spoiler-body">' . $temp[0] . '</div></div>';
			$htmlerr .='<div class="spoiler-wrap"><div onclick="bugSpoilers(this)" class="spoiler-head folded clickable">FILE INCLUDE</div><div class="spoiler-body">' . implode(';<br/>', $included_files) . '</div></div>';
		}
	}
	if ($htmlerr)
		$htmlerr .= '<link type="text/css" href="_design/_style/bug.css" rel="stylesheet"/></div> <script type="text/javascript" src="_design/_script/bug.js"></script>';

	if (!$_CFG['_F']['adminpage'])
		$_tpl['logs'] .= $htmlinfo . $htmlerr;
	else {
		$_tpl['time'] .= $htmlinfo;
		$_tpl['logs'] .= $htmlerr;
	}
	//$_tpl['logs'] = htmlspecialchars($_tpl['logs'], ENT_QUOTES);
	$_tpl['logs'] .= $buffer;

	if ($_html != '') {
		//if ($_tpl['logs'] == '')
		//	$_tpl['onload'] .='fShowHide(\'debug_view\');';
		eval('$_html = "' . $_html . '";');
		if(strpos($_html,'$_tpl')!==false) {
			eval('$_html = "' . addcslashes($_html,'"\\') . '";');
		}
		$page = $_html;
	} else
		$page = $_tpl['logs'];
	return $page;
}

function fDisplLogs($type=0) {
	global $_CFG;
	//0 - все
	//1 - кроме 'ok'
	//2 - кроме 'ok', 'modify'
	//3 - только 'error'
	$text = '';
	$flag = 0;
	if (isset($_CFG['logs']['mess']) and count($_CFG['logs']['mess'])) {
		foreach ($_CFG['logs']['mess'] as $r) {
			$c = '';
			if ($r[1] == 'error') {
				$c = 'red';
				$flag = 1;
			} elseif ($r[1] == 'warning' and $type < 3)
				$c = 'yellow';
			elseif ($r[1] == 'modify' and $type < 2)
				$c = 'blue';
			elseif ($r[1] == 'ok' and $type < 1)
				$c = 'green';
			elseif ($type < 3)
				$c = 'gray';

			if ($c != '')
				$text .='<div style="color:' . $c . ';" class="messelem">' . date('H:i:s') . ' : ' . addslashes($r[0]) . '</div>';
		}
		return array($text, $flag);
	}
	return array($text, $flag);
}

/*
  Ф. вывода заголовков
 */

function headerssent() {
	global $_CFG;
	if (!headers_sent()) {
		setlocale(LC_ALL, 'ru_RU.UTF8');
		//date_default_timezone_set('Asia/Yekaterinburg');
		header("Cache-Control: max-age=0, must-revalidate");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $_CFG['time']) . " GMT");
		header("Expires: " . gmdate("D, d M Y H:i:s", $_CFG['time']) . " GMT");
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
	$traceArr = array_slice($traceArr, $slice);
	$cnt = count($traceArr);
	$i = 0;
	foreach ($traceArr as $arr) {
		$s .= '<div style="font-size:10px;color:#000;margin:0 0 0 ' . (10 * $i) . 'px;border-left:solid 1px #333;border-bottom:solid 1px #233;"><span style="font-size:11px;color:black;">';
		if (isset($arr['line']) and $arr['file'])
			$s .= ' #line ' . $arr['line'] . ' #file: <a href="file:/' . $arr['file'] . '" style="color:blue;">' . $arr['file'] . '</a>';
		if (isset($arr['class']))
			$s .= ' #class <b>' . $arr['class'] . '-></b>';
		$s .= '</span>';
		//$s .= '<br/>';
		$args = array();
		if (isset($arr['args']))
			foreach ($arr['args'] as $v) {
				if (is_null($v))
					$args[] = '<b>NULL</b>';
				else if (is_array($v))
					$args[] = '<b>Array[' . sizeof($v) . ']</b>';
				else if (is_object($v))
					$args[] = '<b>Object:' . get_class($v) . '</b>';
				else if (is_bool($v))
					$args[] = '<b>' . ($v ? 'true' : 'false') . '</b>';
				else {
					$v = (string) @$v;
					$str = htmlspecialchars(substr($v, 0, $MAXSTRLEN));
					if (strlen($v) > $MAXSTRLEN)
						$str .= '...';
					$args[] = $str;
				}
			}
		$s .= $arr['function'] . '(' . implode(',', $args) . ')';
		$s .= '</div>';
		$i++;
	}
	$s .= '</div>';
	return $s;
}


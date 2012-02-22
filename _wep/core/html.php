<?php

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
set_error_handler('_myErrorHandler');
ob_start('_obHandler');
$sai = $_CFG['wep']['_showallinfo'];
if (!isset($_COOKIE[$sai]))
	$_COOKIE[$sai] = 0;
if (isset($_GET[$sai]) and !$_CFG['robot']) {// and !isset($_COOKIE[$sai])
	if ($_GET[$sai])
		_setcookie($sai, $_GET[$sai]);
	else
		_setcookie($sai, $_GET[$sai], (time() - 5000));
	$_COOKIE[$sai] = $_GET[$sai];
}

// or $_CFG['_F']['adminpage']
if(!$_CFG['robot']) {
	$se = $_CFG['wep']['_showerror'];
	if (isset($_GET[$se])) {
		$_COOKIE[$se] = (int)$_GET[$se];
		_setcookie($se, $_COOKIE[$se]);
	}
	/*elseif($_CFG['wep']['debugmode'] and !isset($_COOKIE[$se])) { // для localhost
		$_COOKIE[$se] = 1;
		_setcookie($se, 1);
	}*/
	if(isset($_COOKIE[$se])) {
		$_CFG['wep']['debugmode'] = $_COOKIE[$se];
	}
}
//else _setcookie($se, '', (time()-5000));


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

	function transformPHP(&$data, $transform, $marker='',$_PATHd=false) {
		global $_CFG;
		/* PHP шаблонизатор */
		if(is_array($transform)) {// Старый метод
			$transformPath = $transform[1];
			$transform = $transform[0];
		}
		elseif(strpos($transform,'#')!==false) {
			$marker = $transform;
			$temp = explode('#',substr($transform,1));
			$temp[0] = dirname($_CFG['modulprm'][$temp[0]]['path']).'/templates/';
			$transformPath = $temp[0];
			$transform = $temp[1];
		}
		else {
			if(!$_PATHd)
				$_PATHd = $this->_PATHd;
			$transformPath = $_PATHd . 'php/';
		}
		if (!$marker)
			$marker = $transform;
		if (!isset($data[$marker])) {
			trigger_error('В входных данных шаблона не найдены исходные данные "$data[' . $marker . ']"', E_USER_WARNING);
			return '';
		}
		$transformpath =  $transformPath. $transform . '.php';
		if (!file_exists($transformpath)) {
			trigger_error('Отсутствует файл шаблона `' . $transformpath . '`', E_USER_WARNING);
			return '';
		}
		include_once($transformpath);
		if (!function_exists('tpl_' . $transform)) {
			trigger_error('Функция `tpl_' . $transform . '` в шаблоне `' . $transformpath . '` не найдена', E_USER_WARNING);
			return '';
		}
		eval('$html =  tpl_' . $transform . '($data["' . $marker . '"]);');
		return $html;
	}

	function transform($xml, $transform,$_PATHd=false) {
		if(!$_PATHd)
			$_PATHd = $this->_PATHd;
		/* XML шаблонизатор */
		//$xml = preg_replace(array("/[\x1-\x8\x0b\x0c\x0e-\x1f]+/"),'',$xml);
		$transform =  $_PATHd. 'xsl/' . $transform . '.xsl';
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
				include_once($_CFG['_PATH']['wep_phpscript'] . '/_php4xslt.php');
				$this->_xslt = xslt_create();
			}
			$arguments = array('/_xml' => $xml, '/_xsl' => $xsl);
			$result = xslt_process($this->_xslt, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);
			if (!$result) {
				trigger_error('Error in Template `' . $transform . '` E[' . xslt_errno($this->_xslt) . ']:' . xslt_error($this->_xslt) . '<br/>
					'.static_main::spoilerWrap('XML',nl2br(htmlspecialchars($xml, ENT_QUOTES, 'UTF-8'))).'
					'.static_main::spoilerWrap('XSL',nl2br(htmlspecialchars($xsl, ENT_QUOTES, 'UTF-8'))), E_USER_WARNING);
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
  Функция сбора и обработки ошибок
 */

function _myErrorHandler($errno, $errstr, $errfile, $errline) {//, $errcontext,$cont
	global $_CFG,$BUG;
	if ($_CFG['wep']['catch_bug']) {

		// Debuger
		// для вывода отладчика для всех типов ошибок , можно отключить это условие
		$debug = '';
		if (isset($_CFG['wep']['bug_hunter'][$errno]) and $_CFG['_error'][$errno]['debug']) {
			$debug = debugPrint(2);
		}
			

		$GLOBALS['_ERR'][$_CFG['wep']['catch_bug']][] = array(
			'errno'=>$errno,
			'errstr'=>$errstr, 
			'errfile'=>$errfile, 
			'errline'=>$errline, 
			//'errcontext'=>$errcontext, // Всякие переменные
			'debug'=>$debug,
			'errtype' => $_CFG['_error'][$errno]['type'],
		);
		//остановка на фатальной ошибке
		if ($_CFG['_error'][$errno]['prior'] == 0 and !$_CFG['wep']['debugmode']) {
			die("\n Aborting...<br />\n");
		}
	}
}

function startCatchError($param=2) {
	global $_CFG;
	if ($param < 2)
		$param = 2;
	$_CFG['_ctemp' . $param]['catch_bug'] = $_CFG['wep']['catch_bug'];
	$_CFG['_ctemp' . $param]['bug_hunter'] = $_CFG['wep']['bug_hunter'];
	$_CFG['_ctemp' . $param]['debugmode'] = $_CFG['wep']['debugmode'];
	$_CFG['wep']['catch_bug'] = $param;
	$_CFG['wep']['bug_hunter'] = array ( 0 => '0', 1 => '1', 4 => '4', 16 => '16', 64 => '64', 256 => '256', 4096 => '4096', 2 => '2', 32 => '32', 128 => '128', 512 => '512', 2048 => '2048');
	$_CFG['wep']['debugmode'] = 2;
	return true;
}

function getCatchError($param=2) {
	global $_CFG;
	if ($param < 2)
		$param = 2;
	$_CFG['wep']['catch_bug'] = $_CFG['_ctemp' . $param]['catch_bug'];
	$_CFG['wep']['bug_hunter'] = $_CFG['_ctemp' . $param]['bug_hunter'];
	$_CFG['wep']['debugmode'] = $_CFG['_ctemp' . $param]['debugmode'];
	if (isset($GLOBALS['_ERR'][$param])) {
		$temp = $GLOBALS['_ERR'];
		$GLOBALS['_ERR'] = array($param=>$temp[$param]);
		$return = static_main::showErr();//static_main::showErr() //$GLOBALS['_ERR'][$param];
		unset($temp[$param]);
		$GLOBALS['_ERR'] = $temp;
	} else
		$return = '';
	return $return;
}


/*
  Функция вывода на экран
 */

function _obHandler($buffer) {
	global $_tpl, $_html, $_mctime_start, $_CFG;

	$htmlinfo = '';
	$buffer .= static_main::showErr();

	/*Вывд логов и инфы*/
	if ((isset($_COOKIE[$_CFG['wep']['_showallinfo']]) and $_COOKIE[$_CFG['wep']['_showallinfo']]) or $_CFG['_F']['adminpage']) {
		$included_files = get_included_files();
		$htmlinfo .= ' time=' . substr((getmicrotime() - $_mctime_start), 0, 6) . ' | memory=' . (int) (memory_get_usage() / 1024) . 'Kb | maxmemory=' . (int) (memory_get_peak_usage() / 1024) . 'Kb | query=' . count($_CFG['logs']['sql']) . ' | file include=' . count($included_files).' <br/> ';

		if ($_COOKIE[$_CFG['wep']['_showallinfo']] > 1 and count($_CFG['logs']['sql']) > 0)
			$buffer .= static_main::spoilerWrap('SQL QUERY',implode(';<br/>', $_CFG['logs']['sql']));
		if ($_COOKIE[$_CFG['wep']['_showallinfo']] > 2) {
			$buffer .= static_main::spoilerWrap('FILE INCLUDE',implode(';<br/>', $included_files));
		}
	}

	if ($_CFG['_F']['adminpage']) {
		$_tpl['time'] .= $htmlinfo;
	}else
		$buffer = $htmlinfo . $buffer;
	
	$buffer = trim($buffer);

	if ($_html != '') {
		if ($buffer)
			$buffer = '<link type="text/css" href="/_design/_style/bug.css" rel="stylesheet"/>
				<div id="bugmain">'.$buffer.'</div>';
		$_tpl['logs'] .= $buffer;
		eval('$_html = "' . $_html . '";');
		if(strpos($_html,'$_tpl')!==false) {
			eval('$_html = "' . addcslashes($_html,'"\\') . '";');
		}
		$page = $_html;
	} else {
		$page = $_tpl['logs'].$buffer;
	}
	return $page;
}


/*
  Ф. вывода заголовков
 */

function headerssent() {
	global $_CFG;
	if (!headers_sent()) {
		setlocale(LC_ALL, $_CFG['wep']['setlocale']);
		//date_default_timezone_set('Asia/Yekaterinburg');
		//header("Pragma:");
		header("Content-type: text/html; charset=utf-8");
		header("Cache-Control: public, max-age=20, must-revalidate, post-check=0, pre-check=0");// no-store, no-cache,
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", ($_CFG['time']-20)) . " GMT");
		header("Expires: " . gmdate("D, d M Y H:i:s", ($_CFG['time']+20)) . " GMT");
		header("Access-Control-Allow-Origin: *");
		return true;
	}
	return false;
}

/*
  Ф. трасировки ошибок
 */

function debugPrint($slice=1) {
	$MAXSTRLEN = 256;
	$s = '<div class="xdebug">';
	$traceArr = debug_backtrace();
	$traceArr = array_slice($traceArr, $slice);
	$i = 0;
	foreach ($traceArr as $arr) {
		$s .= '<div class="xdebug-item" style="margin-left:' . (10 * $i) . 'px;"><span>';
		if (isset($arr['line']) and $arr['file'])
			$s .= ' #line ' . $arr['line'] . ' in file: <a href="file:/' . $arr['file'] . '">' . $arr['file'] . '</a> : ';
		if (isset($arr['class']))
			$s .= '#class <b>' . $arr['class'] . '-></b>';
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
		$s .= '<b>'.$arr['function'] . '</b>(' . implode(',', $args) . ')';
		$s .= '</div>';
		$i++;
	}
	$s .= '</div>';
	return $s;
}


<?php

ob_start('wepoutput');
$_mctime_start = getmicrotime();

/*
  Функция вывода на экран
 */
function wepoutput($buffer) 
{
	global $_tpl, $_html, $_CFG;

	/*Вывд логов и инфы*/
	if ((isset($_COOKIE[$_CFG['wep']['_showallinfo']]) and $_COOKIE[$_CFG['wep']['_showallinfo']]) or $_CFG['_F']['adminpage']) 
	{
		$buffer = getLogInfo().$buffer;
	}
	
	$buffer = trim($buffer.static_main::showErr());

	if ($_html != '') {
		if ($buffer)
			$_tpl['logs'] .= '<link type="text/css" href="/_design/_style/bug.css" rel="stylesheet"/>
				<div id="bugmain">'.$buffer.'</div>';

		parseTemplate($_html,$_tpl); // PARSE

	} else {
		$_html = $_tpl['logs'].$buffer;
	}
	return $_html;
}

function getLogInfo()
{
	global $_CFG, $_mctime_start;

	$htmlinfo = '';
	$included_files = get_included_files();
	$htmlinfo .= ' time=' . substr((getmicrotime() - $_mctime_start), 0, 6) . ' | memory=' . (int) (memory_get_usage() / 1024) . 'Kb | maxmemory=' . (int) (memory_get_peak_usage() / 1024) . 'Kb | query=' . count($_CFG['logs']['sql']) . ' | file include=' . count($included_files).' <br/> ';

	if ($_COOKIE[$_CFG['wep']['_showallinfo']] > 1 and count($_CFG['logs']['sql']) > 0)
		$htmlinfo .= static_main::spoilerWrap('SQL QUERY',implode(';<br/>', $_CFG['logs']['sql']));
	if ($_COOKIE[$_CFG['wep']['_showallinfo']] > 2) {
		$htmlinfo .= static_main::spoilerWrap('FILE INCLUDE',implode(';<br/>', $included_files));
	}
	return $htmlinfo;
}

class wephtml 
{
	function __construct() 
	{
		global $_tpl;
		$_tpl['meta'] = $_tpl['logs']=$_tpl['onload']=$_tpl['title']=$_tpl['text']='';
		$_tpl['BH'] = rtrim(MY_BH,'/');// OLD
		$_tpl['THEME'] = getUrlTheme();
		$_tpl['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
		$_tpl['title'] = $_tpl['time'] = $_tpl['onload'] = $_tpl['logs'] = '';
		$_tpl['onload2'] = array();
		$_tpl['script'] = $_tpl['styles'] = array();
		$_tpl['YEAR'] = date('Y');

		$params = array(
			'obj' => &$this,
			'func' => 'createTemplate',
		);
		observer::register_observer($params, 'shutdown_function');
	}

	public function createTemplate() 
	{
		global $_tpl, $_html, $_CFG;
		$file = getPathTemplate();
		if (file_exists($file)) 
		{
			$_html = file_get_contents($file);
			//$_html = addcslashes($_html,'"\\');
			include_once($_CFG['_PATH']['core'] . '/includesrc.php');
			fileInclude($_CFG['fileIncludeOption']);
			arraySrcToStr();
		}
		else
			$_html = 'ERROR: Mising templates file ' . $this->_templates . ' - ' . $file;

		headerssent();
	}

}

/* Functions********************************* */

/*
  Ф. вывода заголовков
 */

function headerssent() {
	global $_CFG;
	if (!headers_sent()) 
	{
		header("Pragma: no-cache");
		header("Content-type: text/html; charset=utf-8");
		header("Cache-Control: public, no-store, no-cache, must-revalidate, post-check=0, pre-check=0");// no-store, no-cache,
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $_CFG['header']['modif']) . " GMT");
		header("Expires: " . gmdate("D, d M Y H:i:s", $_CFG['header']['expires']) . " GMT");
		header("Access-Control-Allow-Origin: *");
		return true;
	}
	return false;
}



function parseTemplate(&$TEXT,&$TPL) 
{
	if(strpos($TEXT,'{#')!==false) { // NEW STANDART
		preg_match_all('/\{\#([A-z0-9_]+)\#\}/u',$TEXT,$temp);
		//return '<pre>'.var_export($temp,true);
		foreach($temp[1] as $k=>$r) {
			if(!isset($TPL[$r]))
				$TPL[$r] = '';
			$TEXT = str_replace($temp[0][$k],$TPL[$r],$TEXT);
		}
	}
	else {
		preg_match_all('/\{\$_tpl\[\'([A-z0-9_]+)\'\]\}/ui',$TEXT,$temp);
		foreach($temp[1] as $k=>$r) {
			if(!isset($TPL[$r]))
				$TPL[$r] = '';
			$TEXT = str_replace($temp[0][$k],$TPL[$r],$TEXT);
		}
	}
	if(strpos($TEXT,'{#')!==false or strpos($TEXT,'$_tpl')!==false) {
		parseTemplate($TEXT,$TPL);
	}
	return true;
}


$MODUL = NULL;
$_html = '';
$_tpl = array();
	
$HTML = new wephtml();
<?php

$WEPOUT = new wephtml();
$isAjax = false;

class wephtml
{
	private $_html = '';
	private $_mctime_start;

	function __construct()
	{
		$this->_mctime_start = getmicrotime();

		ob_start(array(&$this, "obHandler"));

		/*$params = array(
			'obj' => &$this,
			'func' => 'createTemplate',
		);
		observer::register_observer($params, 'shutdown_function');*/
	}

	function __destruct()
	{
		$this->headerssent();
		$this->createTemplate();
		ob_end_flush();
	}

	/*
	  Функция вывода на экран
	 */
	public function obHandler($buffer)
	{
		global $_tpl, $_CFG;
		$_tpl['THEME'] = getUrlTheme();
		/*Вывд логов и инфы*/
		if (canShowAllInfo() or isBackend()) {
			$buffer = $this->getLogInfo() . $buffer;
		}

		$buffer = trim($buffer . static_main::showErr());

		if ($this->_html != '') {
			if ($buffer)
				$_tpl['logs'] .= '<link type="text/css" href="/_design/_style/bug.css" rel="stylesheet"/>
					<div id="bugmain">' . $buffer . '</div>';

			$this->parseTemplate($this->_html, $_tpl); // PARSE

		}
		else {
			$this->_html = $_tpl['logs'] . $buffer;
		}
		return $this->_html;
	}

	public function createTemplate()
	{
		global $_CFG;
		$file = getPathTemplate();
		if (file_exists($file)) {
			$this->_html = file_get_contents($file);
			//$this->_html = addcslashes($this->_html,'"\\');
			include_once($_CFG['_PATH']['core'] . '/includesrc.php');
			fileInclude();
			arraySrcToStr();
		}
		else
			$this->_html = 'ERROR: Mising templates file ' . $this->_templates . ' - ' . $file;


	}

	public function getLogInfo()
	{
		global $_CFG;

		$htmlinfo = '';
		$included_files = get_included_files();
		$htmlinfo .= ' time=' . substr((getmicrotime() - $this->_mctime_start), 0, 6) . ' | memory=' . (int)(memory_get_usage() / 1024) . 'Kb | maxmemory=' . (int)(memory_get_peak_usage() / 1024) . 'Kb | query=' . count($_CFG['logs']['sql']) . ' | file include=' . count($included_files) . ' <br/> ';

		if (canShowAllInfo() > 1 and count($_CFG['logs']['sql']) > 0)
			$htmlinfo .= static_main::spoilerWrap('SQL QUERY', implode(';<br/>', $_CFG['logs']['sql']));
		if (canShowAllInfo() > 2) {
			$htmlinfo .= static_main::spoilerWrap('FILE INCLUDE', implode(';<br/>', $included_files));
		}
		return $htmlinfo;
	}

	/*
	  Ф. вывода заголовков
	 */
	function headerssent()
	{
		global $_CFG;
		if (!headers_sent()) {
            if (isDebugMode()) {
			    header('Pragma: no-cache');
                header('Cache-Control: public, no-store, no-cache, must-revalidate, post-check=0, pre-check=0'); // no-store, no-cache,
            }
            else {
                header('Pragma: cache');
                header('Cache-Control: public, post-check=0, pre-check=0');
            }

			header('Content-type: text/html; charset=utf-8');
			header("Last-Modified: " . gmdate("D, d M Y H:i:s", $_CFG['header']['modif']) . " GMT");
			header("Expires: " . gmdate("D, d M Y H:i:s", (time()+$_CFG['header']['expires'])) . " GMT");
			header('X-Accel-Expires: '.$_CFG['header']['expires']);

			if ($_CFG['site']['origin']) {
				header("Access-Control-Allow-Origin: " . $_CFG['site']['origin']);
			}


			return true;
		}
		return false;
	}

	function parseTemplate(&$TEXT, &$TPL, $i = 0)
	{
		if ($i > 2) return true;
		if (_strpos($TEXT, '{#') !== false) { // NEW STANDART
			preg_match_all('/\{\#([A-z0-9_\-]+)\#\}/u', $TEXT, $temp);
			//return '<pre>'.var_export($temp,true);
			foreach ($temp[1] as $k => $r) {
				if (!isset($TPL[$r]))
					$TPL[$r] = '';
				$TEXT = str_replace($temp[0][$k], $TPL[$r], $TEXT);
			}
		}
		else {
			preg_match_all('/\{\$_tpl\[\'([A-z0-9_\-]+)\'\]\}/ui', $TEXT, $temp);
			foreach ($temp[1] as $k => $r) {
				if (!isset($TPL[$r]))
					$TPL[$r] = '';
				$TEXT = str_replace($temp[0][$k], $TPL[$r], $TEXT);
			}
		}
		if (_strpos($TEXT, '{#') !== false or _strpos($TEXT, '$_tpl') !== false) {
			$i++;
			$this->parseTemplate($TEXT, $TPL, $i);
		}
		return true;
	}

}

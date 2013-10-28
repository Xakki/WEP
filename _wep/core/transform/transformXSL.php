<?php

function transformXSL(&$xml, $transform, $_PATHd = false)
{
	if (!$_PATHd)
		$_PATHd = getPathTheme();
	/* XML шаблонизатор */
	//$xml = preg_replace(array("/[\x1-\x8\x0b\x0c\x0e-\x1f]+/"),'',$xml);
	$transform = $_PATHd . '/xsl/' . $transform . '.xsl';
	if (!file_exists($transform)) {
		trigger_error("Template $transform not exists", E_USER_WARNING);
		return '';
	}
	if (!$xml) {
		trigger_error("XML empty for template $transform", E_USER_WARNING);
		return '';
	}
	$xsl = str_replace(array('\x09'), array(''), file_get_contents($transform));


	$xml = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE fragment [<!ENTITY nbsp "&#160;">]> ' . $xml;
	if (extension_loaded('xsl')) {
		if (!isset($_CFG['_xslt'])) {
			global $_CFG;
			include_once($_CFG['_PATH']['wep_controllers'] . '/lib/_php4xslt.php');
			$_CFG['_xslt'] = xslt_create();
		}
		$arguments = array('/_xml' => $xml, '/_xsl' => $xsl);
		$result = xslt_process($_CFG['_xslt'], 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);
		if (!$result) {
			trigger_error('Error in Template `' . $transform . '` E[' . xslt_errno($_CFG['_xslt']) . ']:' . xslt_error($_CFG['_xslt']) . '<br/>
				' . static_main::spoilerWrap('XML', nl2br(htmlspecialchars($xml, ENT_QUOTES, 'UTF-8'))) . '
				' . static_main::spoilerWrap('XSL', nl2br(htmlspecialchars($xsl, ENT_QUOTES, 'UTF-8'))), E_USER_WARNING);
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

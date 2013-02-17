<?php


function transformPHP($data, $transform=NULL, $marker='',$theme=null) 
{
	
	if(is_null($transform)) {
		$transform = (string)$data;
		$data = array();
	}
	/* PHP шаблонизатор */
	if(is_array($transform)) {// Старый метод
		$transformPath = $transform[1];
		$transform = $transform[0];
	}
	// Ищем шаблон в модуле
	elseif(strpos($transform,'#')!==false) {
		$marker = $transform;
		$temp = explode('#',substr($transform,1));
		global $_CFG;
		$temp[0] = dirname($_CFG['modulprm'][$temp[0]]['path']).'/_design/php/';
		$transformPath = $temp[0];
		$transform = $temp[1];
		if(isset($data[$transform]))
			$marker = $transform;
	}
	else {
		if($theme)
			setTheme($theme);
		$transformPath = getPathTheme() . 'php/';
	}
	if (!$marker)
		$marker = $transform;

	//$MY_THEME = getUrlTheme();
	//$_MY_THEME = getPathTheme();

	if (!isset($data[$marker])) {
		//trigger_error('Внимание! В входных данных шаблона не найден маркер "$data[' . $marker . ']"', E_USER_NOTICE);
		$marker = '$data';
	} else
		$marker = '$data["' . $marker . '"]';

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
	eval('$html =  tpl_' . $transform . '(' . $marker . ');');
	return $html;
}

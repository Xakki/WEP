<?php


function tpl_formtools(&$data)
{
	//print_r($data);exit();
	if (isset($data['_*features*_']) and isset($data['form']['_*features*_'])) {
		trigger_error('Ошибка. Старый формат данных. Атрибут _*features*_ не поддерживается.', E_USER_WARNING);
		return '';
	}

	if (!is_array($data))
		return $data;

	global $_CFG, $_tpl;
	$html = '';

	if (isset($data['reloadPage']) and $data['reloadPage'])
		$_tpl['onload'] .= 'wep.fShowloadReload();';

	if (isset($data['path']) and count($data['path'])) {
		include_once(getPathTheme(true) . '/php/path.php');
		$html = tpl_path($data['path']); // PATH
	}

	if (isset($data['messages']) and count($data['messages'])) {
		include_once(getPathTheme(true) . '/php/messages.php');
		$html .= tpl_messages($data['messages']); // messages
	}

	if (isset($data['isFilter']) and $data['isFilter']) {
		$attr = $data['options'];
		include_once(getPathTheme(true) . '/php/filter.php');
		//$data['filter']['options']['action'] = str_replace('&','&amp;',$_SERVER['REQUEST_URI']);
		$html .= tpl_filter($data);
		//$_tpl['onload'] .= 'wep.jsForm(\'#form_tools_'.$_REQUEST['_func'].'\',{\'insertobj\':\'#tools_block\'});';
		$_tpl['onload'] .= 'wep.form.initForm(\'#' . $attr['name'] . '\', formParam);';
		plugAjaxForm();
	} elseif (isset($data['form']) and count($data['form'])) {
		$attr = $data['options'];
		//$html = '<span class="buttonimg imgdel" style="float: right;" onclick="$(this).parent().hide();">EXIT</span>'.$html;
		include_once(getPathTheme(true) . '/php/form.php');
		$html .= '<form id="' . $attr['name'] . '" method="post" enctype="multipart/form-data" action="' . $attr['action'] . '" class="' . (isset($attr['css']) ? $attr['css'] : 'divform') . '">';
		$html .= tpl_form($data['form']) . '</form>';
		$_tpl['onload'] .= 'wep.form.initForm(\'#' . $attr['name'] . '\', formParam);';
		plugAjaxForm();
	}

	$html .= '';
	return $html;
}



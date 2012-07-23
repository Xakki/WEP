<?php


	function tpl_formtools(&$data) {
		if(!is_array($data))
			return $data;
		global $HTML,$_tpl,$_CFG;
		$html = '';
		$_CFG['fileIncludeOption']['form'] = 1;
		if(isset($data['path']) and count($data['path'])) {
			include_once($HTML->_cDesignPath.'/php/path.php');
			$html = tpl_path($data['path']);// PATH
		}
		if(isset($data['messages']) and count($data['messages'])) {
			include_once($HTML->_cDesignPath.'/php/messages.php');
			$html .= tpl_messages($data['messages']);// messages
		}
		if(isset($data['form']) and count($data['form'])) {
			$html = '<span class="buttonimg imgdel" style="float: right;" onclick="$(this).parent().hide();">EXIT</span>'.$html;
			include_once($HTML->_cDesignPath.'/php/form.php');
			$attr = $data['form']['_*features*_'];
			$html .= '<form id="form_tools_'.$attr['name'].'" method="post" enctype="multipart/form-data" action="'.$attr['action'].'" class="'.(isset($attr['css'])?$attr['css']:'divform').'">';
			$html .= tpl_form($data['form']).'</form>';
		}
		elseif(isset($data['filter']) and count($data['filter'])) {
			include_once($HTML->_cDesignPath.'/php/filter.php');
			$data['filter']['_*features*_']['action'] = str_replace('&','&amp;',$_SERVER['REQUEST_URI']);
			$html .= tpl_filter($data['filter']);
		}

		$html .= '';
		return $html;
	}



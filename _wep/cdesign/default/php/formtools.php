<?
include_once($this->_cDesignPath.'/php/path.php');
include_once($this->_cDesignPath.'/php/form.php');
include_once($this->_cDesignPath.'/php/messages.php');
	function tpl_formtools(&$data) {
		$html = tpl_path($data['path']);// PATH
		$html .= '<span class="bottonimg imgdel" style="float: right;" onclick="$(this).parent().hide();">EXIT</span><div align="center" class="divform">';
		$html .= tpl_messages($data['messages']);// messages
		if(isset($data) and count($data)) {
			$attr = $data['form']['_*features*_'];
			$html .= '<form id="form_tools_'.$attr['name'].'" method="post" enctype="multipart/form-data" action="'.$attr['action'].'" onsubmit="return preSubmitAJAX(this);'.($attr['id']?'" id="'.$attr['name'].'_'.$attr['id']:'').'">';
			$html .= tpl_form($data['form']).'</form>';
		}
		$html .= '</div>';
		return $html;
	}

?>
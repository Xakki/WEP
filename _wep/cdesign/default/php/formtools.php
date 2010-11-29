<?
include_once($this->_cDesignPath.'/php/path.php');
include_once($this->_cDesignPath.'/php/form.php');
include_once($this->_cDesignPath.'/php/messages.php');
	function tpl_formtools(&$data) {
		global $HTML,$_tpl;
		$_tpl['script']['form'] = 1;
		$_tpl['styles']['form'] = 1;

		$html = tpl_path($data['path']);// PATH
		$html .= '<span class="bottonimg imgdel" style="float: right;" onclick="$(this).parent().hide();">EXIT</span><div align="center" class="divform">';
		$html .= tpl_messages($data['messages']);// messages
		if(isset($data['form']) and count($data['form'])) {
			$attr = $data['form']['_*features*_'];
			$html .= '<form id="form_tools_'.$attr['name'].'" method="post" enctype="multipart/form-data" action="'.$attr['action'].'"">';
			$html .= tpl_form($data['form']).'</form>';
		}
		elseif(isset($data['filter']) and count($data['filter'])) {
			include_once($HTML->_cDesignPath.'/php/filter.php');
			$data['filter']['_*features*_']['action'] = str_replace('&','&amp;',$_SERVER['REQUEST_URI']);
			$html .= tpl_filter($data['filter']);
		}
		else
			$html .= ' --- ';
		$html .= '</div>';
		return $html;
	}

?>
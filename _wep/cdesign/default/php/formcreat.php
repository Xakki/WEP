<?
	function tpl_formcreat(&$data) {
		global $HTML;
		if(isset($data['path']) and count($data['path'])) {
			include_once($HTML->_cDesignPath.'/php/path.php');
			$html = tpl_path($data['path'],1);// PATH
		}
		$html .= '<div class="divform'.($data['css']?' '.$data['css']:'').'"';
		if($data['style'])
			$html .= ' style="'.$data['style'].'"';
		$html .= '>';
		if(isset($data['messages']) and count($data['messages'])) {
			include_once($HTML->_cDesignPath.'/php/messages.php');
			$html .= tpl_messages($data['messages']);// messages
		}
		if(isset($data['form']) and count($data['form'])) {
			include_once($HTML->_cDesignPath.'/php/form.php');
			$attr = $data['form']['_*features*_'];
			if (isset($attr['enctype']))
				if ($attr['enctype'] == '')
					$enctype = '';
				else
					$enctype = ' enctype="'.$attr['enctype'].'"';
			else
				$enctype = ' enctype="multipart/form-data"';
			$html .= '<form id="form_'.$attr['name'].'" method="'.$attr['method'].'"'.$enctype.' action="'.$attr['action'].'" '.($attr['onsubmit']?'onsubmit="'.$attr['onsubmit'].'"':'').'>';
			$html .= tpl_form($data['form']).'</form>';
		}
		$html .= '</div>';
		return $html;
	}
//<!--<div class="dscr"><span style="color:#F00">*</span> - обязательно для заполнения</div>
//<div class="dscr"><span style="color:#F00">**</span> - обязательно для заполнения хотябы одно поле</div>-->
?>

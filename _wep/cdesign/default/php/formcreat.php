<?
include_once($this->_cDesignPath.'/php/path.php');
include_once($this->_cDesignPath.'/php/form.php');
include_once($this->_cDesignPath.'/php/messages.php');
	function tpl_formcreat(&$data) {
		$html = tpl_path($data['path'],1);// PATH
		$html .= '<div class="divform'.($data['css']?' '.$data['css']:'').'"';
		if($data['style'])
			$html .= ' style="'.$data['style'].'"';
		$html .= '>';
		$html .= tpl_messages($data['messages']);// messages
		if(isset($data['form']) and count($data['form'])) {
			$attr = $data['form']['_*features*_'];
			$html .= '
<form id="form_'.$attr['name'].'" method="'.$attr['method'].'" enctype="multipart/form-data" action="'.$attr['action'].'" '.($attr['onsubmit']?'onsubmit="'.$attr['onsubmit'].'"':'').'>';
			$html .= tpl_form($data['form']).'</form>';
		}
		$html .= '</div>';
		return $html;
	}
//<!--<div class="dscr"><span style="color:#F00">*</span> - обязательно для заполнения</div>
//<div class="dscr"><span style="color:#F00">**</span> - обязательно для заполнения хотябы одно поле</div>-->
?>
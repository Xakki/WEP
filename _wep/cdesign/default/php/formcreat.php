<?php
	function tpl_formcreat(&$data) {
		global $HTML;
		$texthtml = '';
		$texthtml .= '<div class="divform'.((isset($data['css']) and $data['css'])?' '.$data['css']:'').'"';
		if(isset($data['style']) and $data['style'])
			$texthtml .= ' style="'.$data['style'].'"';
		$texthtml .= '>';
		if(isset($data['messages']) and count($data['messages'])) {
			include_once('messages.php');
			$texthtml .= tpl_messages($data['messages']);// messages
		}
		$flag = 0;
		if(isset($data['form']) and count($data['form'])) {
			include_once('form.php');
			$attr = $data['form']['_*features*_'];
			if (isset($attr['enctype']))
				if ($attr['enctype'] == '')
					$enctype = '';
				else
					$enctype = ' enctype="'.$attr['enctype'].'"';
			else
				$enctype = ' enctype="multipart/form-data"';
			if(!isset($attr['action']))
				$attr['action'] = '';
			$texthtml .= '<form id="form_'.$attr['name'].'" method="'.$attr['method'].'"'.$enctype.' action="'.$attr['action'].'" ';
			if(isset($attr['onsubmit']))
				$texthtml .= 'onsubmit="'.$attr['onsubmit'].'"';
			$texthtml .= '>' . tpl_form($data['form']).'</form>';
			if($attr['id'])
				$flag = 2;
			else
				$flag = 1;
		}
		if(isset($data['path']) and count($data['path'])) {
			include_once('path.php');
			$texthtml = tpl_path($data['path'],$flag).$texthtml;// PATH
		}
		$texthtml .= '</div>';
		return $texthtml;
	}
//<!--<div class="dscr"><span style="color:#F00">*</span> - обязательно для заполнения</div>
//<div class="dscr"><span style="color:#F00">**</span> - обязательно для заполнения хотябы одно поле</div>-->


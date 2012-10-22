<?php
	function tpl_formcreat(&$data) {
		global $HTML,$_tpl,$PGLIST,$_CFG;
		$texthtml = '';
		$texthtml .= '<div class="divform'.((isset($data['css']) and $data['css'])?' '.$data['css']:'').'"';
		if(isset($data['style']) and $data['style'])
			$texthtml .= ' style="'.$data['style'].'"';
		$texthtml .= '>';

		if(!isset($data['DIR']))
			$data['DIR'] = dirname(__FILE__);

		if(isset($data['messages']) and count($data['messages'])) {
			include_once($data['DIR'].'/messages.php');
			$texthtml .= tpl_messages($data['messages']);// messages
		}
		$flag = 0;
		if(isset($data['form']) and count($data['form'])) {
			$attr = $data['form']['_*features*_'];
			$ID = 'form_'.$attr['name'];
			if((isset($_tpl['script']['script.jquery/form']) or isset($_CFG['fileIncludeOption']['jqueryform']) ) and isset($PGLIST->contentID))
				$_tpl['onload'] .= 'wep.form.ajaxForm(\'#'.$ID.'\','.$PGLIST->contentID.');';

			include_once($data['DIR'].'/form.php');
			if (isset($attr['enctype']))
				if ($attr['enctype'] == '')
					$enctype = '';
				else
					$enctype = ' enctype="'.$attr['enctype'].'"';
			else
				$enctype = ' enctype="multipart/form-data"';
			if(!isset($attr['action']))
				$attr['action'] = '';
			if(!isset($attr['method']) or !$attr['method'])
				$attr['method'] = 'POST';
			$texthtml .= '<form id="'.$ID.'" method="'.$attr['method'].'"'.$enctype.' action="'.$attr['action'].'" ';
			if(isset($attr['onsubmit']))
				$texthtml .= 'onsubmit="'.$attr['onsubmit'].'"';
			$texthtml .= '>';

			if(isset($data['formSort']) and count($data['formSort']) and is_array(current($data['formSort']))) {
				$_CFG['fileIncludeOption']['jquery-ui']= true;
				
				$texthtml .= tpl_form($data['form'], $data['formSort']);

				$_tpl['onload'] .= '$("#'.$ID.'").tabs();';
			}
			else
				$texthtml .= tpl_form($data['form']);
			$texthtml .= '</form>';

			if(isset($attr['id']) and $attr['id'])
				$flag = 2;
			else
				$flag = 1;
		}
		if(isset($data['path']) and count($data['path'])) {
			include_once($data['DIR'].'/path.php');
			$texthtml = tpl_path($data['path'],$flag).$texthtml;// PATH
		}
		$texthtml .= '</div>';
		return $texthtml;
	}
//<!--<div class="dscr"><span style="color:#F00">*</span> - обязательно для заполнения</div>
//<div class="dscr"><span style="color:#F00">**</span> - обязательно для заполнения хотябы одно поле</div>-->


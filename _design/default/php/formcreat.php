<?php
	function tpl_formcreat(&$data) {
		global $_tpl,$PGLIST,$_CFG;
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
		if(isset($data['form']) and count($data['form'])) 
		{
			$attr = $data['form']['_*features*_'];
			$ID = 'form_'.$attr['name'];

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
			$texthtml .= '<form id="'.$ID.'" method="'.$attr['method'].'"'.$enctype.' action="'.$attr['action'].'"';
			if(isset($attr['onsubmit']))
				$texthtml .= ' onsubmit="'.$attr['onsubmit'].'"';
			if( isset($PGLIST->contentID) )
				$texthtml .= ' data-cid="'.$PGLIST->contentID.'"';
			$texthtml .= '>';

			if(isset($data['formSort']) and count($data['formSort']) and is_array(current($data['formSort']))) {
				plugJQueryUI();
				$_tpl['onload'] .= '$("#'.$ID.'").tabs();';
				$texthtml .= tpl_form($data['form'], $data['formSort']);
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


		if(isset($data['flag']))
		{
			if($data['flag']==1) {
				$_tpl['onload'] .= '$("#'.$ID.'").trigger(\'success\');';
				//$_tpl['onload'] .= 'clearTimeout(timerid2);wep.fShowload(1,false,result.html,0,\'location.href = location.href;\');';
			}
			elseif($data['flag']==-1) {
				//$_tpl['onload'] = 'GetId("messages").innerHTML=result.html;'.$_tpl['onload'];
				$_tpl['onload'] = 'clearErrorForm("#'.$ID.'"); $("#'.$ID.'").trigger(\'error\'); '.$_tpl['onload'];
				//.'clearTimeout(timerid2);wep.fShowload(1,false,result.html);'
				$texthtml = "<div class='blockhead'>Внимание. Некоректно заполнены поля.</div><div class='hrb'>&#160;</div>".$texthtml;
			}
			else
			{
				//$_tpl['onload'] .= 'clearTimeout(timerid2);wep.fShowload(1,false,result.html);';
			}
			/*if(!isset($_SESSION['user']['id']))
				$_tpl['onload'] .= 'reloadCaptcha(\'captcha\');';*/
		}
		return $texthtml;
	}
//<!--<div class="dscr"><span style="color:#F00">*</span> - обязательно для заполнения</div>
//<div class="dscr"><span style="color:#F00">**</span> - обязательно для заполнения хотябы одно поле</div>-->


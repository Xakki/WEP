<?php

function tpl_billing($data)
{
	global $_tpl, $HTML;
//print_r('<pre>');print_r($_POST);print_r($data);
	// AJAX forma
	$_CFG['fileIncludeOption']['form'] = 1;
	$_CFG['fileIncludeOption']['jqueryform'] = 1;
	$_tpl['onload'] .= 'wep.form.ajaxForm(\'#paymethod\','.$data['#contentID#'].');';
	$_tpl['styles']['../default/_pay/pay'] = 1;

	$html = '<div class="payselect" style="width:340px;margin:10px;">
		<h2>'.$data['#title#'].'</h2>
		<form action="" enctype="multipart/form-data" method="post" id="paymethod">';
		foreach($_POST as $k=>$r) {
			if(!is_array($r))
				$html .= '<input type="hidden" value="'.$r.'" name="'.$k.'">';
			else {
				foreach($r as $ki=>$i)
					$html .= '<input type="hidden" value="'.$i.'" name="'.$k.'['.$ki.']">';
			}
		}

		if(isset($data['messages'])) {
			$html .= $HTML->transformPHP($data['messages'], '#pg#messages');
		}

		if(isset($data['child'])) {
			$html .= '<div class="paymethod">';
			foreach($data['child'] as $r) {
				$html .= '<input class="pay-'.$r['_cl'].'" type="submit" value="'.$r['_cl'].'" name="paymethod" title="'.$r['caption'].'">';
			}
			$html .= '</div>';
		}
		elseif(isset($data['form'])) {
			global $HTML;
			$html .= '<div class="divform">';
			unset($data['form']['_info']);
			$html .= $HTML->transformPHP($data['form'], '#pg#form');
			$html .= '</div>';
		}
		else {
			$html .= 'OK';
		}
		$html .= '</form>
	<p>К оплате: <b>'.$data['summ'].' '.$data['#currency#'].'</b></p>
	<p>'.$data['comm'].'</p>
	</div>';

	return $html;
}

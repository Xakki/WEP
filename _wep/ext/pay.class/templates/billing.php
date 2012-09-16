<?php
/**
* Форма оплаты счета
* 
*/
function tpl_billing($data)
{
	global $_tpl, $HTML;

	// AJAX forma
	$ID = 'form_paymethod';
	$action = '';
	if(isset($data['form']['_*features*_']['action']))
		$action = $data['form']['_*features*_']['action'];

	//$_CFG['fileIncludeOption']['form'] = 1;

	$_tpl['onload'] .= 'wep.form.ajaxForm(\'#'.$ID.'\','.$data['#contentID#'].');';
	$_tpl['styles']['../default/_pay/pay'] = 1;

	$html = '<div class="payselect" style="width:340px;margin:10px auto;">
		<h2>'.$data['#title#'].'</h2>
		<form action="'.$action.'" enctype="multipart/form-data" method="post" id="'.$ID.'">';
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
				/*if(isset($r['_button']))
					$html .= '<span>'.$r['_button'].'</span>';
				else*/
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
			$html .= '';
		}
		$html .= '</form>';
	if(isset($data['#foot#']))
		$html .= '<div class="payselect-foot">'.$data['#foot#'].'</div>';
	$html .= '</div>';

	return $html;
}

<?php

function tpl_billing($data)
{
	global $_tpl;
	$_tpl['styles']['../default/_pay/pay'] = 1;
	$html = '<div class="payselect" style="width:340px;margin:10px;">
		<h2>'.$data['#title#'].'</h2>
		<ul>';
		foreach($data['child'] as $r) {
			$html .= '<li class="pay-'.$r['_cl'].'"><a href="'.$data['#Chref#'].'/'.$r['_cl'].'.html" title="'.$r['caption'].'">'.$r['caption'].'</a></li>';
		}
		$html .= '</ul>
	<p>К оплате: <b>'.$data['summ'].' '.$data['#currency#'].'</b></p>
	<p>'.$data['comm'].'</p>
	</div>';

	return $html;
}

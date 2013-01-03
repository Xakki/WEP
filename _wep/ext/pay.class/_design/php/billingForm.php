<?php
/**
 * Форма выставление счета
 * @type Платежная система
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
function tpl_billingForm($data)
{
	global $_tpl, $HTML;
	//$_CFG['fileIncludeOption']['form'] = 1;
	$_tpl['styles']['/_pay/pay'] = 1;
	$html = '';print_r('<pre>');print_r($data);

	if(isset($data['messages']) and count($data['messages'])) {
		$html .= $HTML->transformPHP($data['messages'], '#pg#messages');
	}

	return $html;
}

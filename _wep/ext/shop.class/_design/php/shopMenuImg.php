<?php
function tpl_shopMenuImg(&$data)
{
	$html = '';
	if (isset($data) and count($data)) {
		setCss('/../_shop/style/shopMenu');

		$html = '<div class="shop-menu">
			' . ($data['#title#'] ? '<h3>' . $data['#title#'] . '</h3>' : '') . '
			';
		$html .= tpl_shopMenuImg_rev($data['#item#'], '', $data['#page#']);
		$html .= '</div>';
	}
	return $html;
}

function tpl_shopMenuImg_rev(&$data, $pref = '', $pgid = 0)
{
	$html = '<ul>';
	foreach ($data as $k => $r) {
		if (isset($r['#item#']) and count($r['#item#'])) {
			$html .= '<li class="sub">';
			//$pref .= ' - ';
			$sub = tpl_shopMenuImg_rev($r['#item#'], $pref, $pgid);
		}
		else {
			$html .= '<li>';
			$sub = '';
		}
		if ($r['img']) {
			$html .= '<img src="' . $r['img'] . '" alt="' . $r['uiname'] . '"/>';
		}
		$html .= '<a href="/' . $pgid . '/' . $r['path'] . '.html" class="' . ($r['#sel#'] ? 'selected' : '') . '">' . $r['uiname'] . '</a>' . $sub . '</li>';
	}
	$html .= '</ul>';
	return $html;
}

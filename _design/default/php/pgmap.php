<?php
function tpl_pgmap(&$data, $attr = '')
{
	$html = '';
	if (isset($data['#item#']) and count($data['#item#'])) {
		$html .= '<ul class="pgmap' . $attr . '">';
		foreach ($data['#item#'] as $k => $r) {
			if (!$r['name']) continue;
			$html .= '<li';
			if (isset($r['hidechild']))
				$html .= ' style="list-style:none inside none;"';
			$html .= '>';
			if (isset($r['sel']) and $r['sel'])
				$r['name'] = '<span>' . $r['name'] . '</span>';
			if (isset($r['hidechild']))
				$html .= '<span class="foldedul clickable" onclick="ulToggle(this,\'unfoldedul\')"></span>';
			$html .= '<a href="' . $r['href'] . '">' . $r['name'] . '</a>';
			if (isset($r['#item#']) and count($r['#item#'])) {
				$html .= tpl_pgmap($r, (isset($r['hidechild']) ? ' sdsd' : ''));
			}
			$html .= '</li>';
		}
		$html .= '</ul>';
	}
	return $html;
}

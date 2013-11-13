<?php
/**
 * Постраничная навигация
 * @type Контент
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
function tpl_pagenum(&$data)
{
	global $_CFG, $_tpl;
	if (!$data or !count($data)) return '<div style="padding-top:40px;"></div>';

	$html = '<div class="pagenum">';
	if (isset($data['link']) and count($data['link'])) {
		$html .= '';
		if ($data['_pn'] == 1) {
			$html .= '<a class="left noactive">←</a>';
        }
		else {
			$html .= '<a class="left" href="' . ($data['_pn'] == 2 ? $data['PP'][0] : ($data['PP'][1] . ($data['_pn'] - 1) . $data['PP'][2]) ). '" onclick="return wep.load_href(this)">←</a>';
        }

		foreach ($data['link'] as $k => $r) {
			if ($k == $data['_pn']) {
				$html .= '<a class="current noactive">' . $k . '</a>';
            }
			elseif (!$r) {
				$html .= '<a class="more noactive">...</a>';
            } else {
				$html .= '<a href="' . $r . '" onclick="return wep.load_href(this)" title="Страница №' . $k . '">' . $k . '</a>';
            }
		}
		if ($data['_pn'] == $data['cntpage']) {
			$html .= '<a class="right noactive">→</a>';
        }
		else {
			$html .= '<a class="right" href="' . $data['PP'][1] . ($data['_pn'] + 1) . $data['PP'][2] . '" onclick="return wep.load_href(this)">→</a>';
        }
	}
	$html .= '</div>';
	return $html;
}

?>



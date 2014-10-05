<?php
/**
 * Список рубрик
 * @type Рубрикатор
 * @ico default.png
 * @tags rubric_class
 * @author Xakki
 * @version 0.1
 * @return string html
 */
function tpl_rubricMain(&$data)
{
	$html = '';
	if (isset($data) and count($data)) {
		$cnt = ceil(count($data['#item#']) / 3);
		$temp = array_chunk($data['#item#'], $cnt, true);
		$html = '
			<div class="wrapper">
				<span class="char">.</span> . ';
		foreach ($temp as $r) {
			$html .= '<span class="rubmain">
						' . tpl_rubricMain_rev($r, '', $data['#page#']) . '
					</span>';
		}
		$html .= ' .
				<span class="char">.</span>
				<span class="eol">.</span>
			</div>';
	}
	return $html;
}

function tpl_rubricMain_rev(&$data, $pref = '', $pgid = 'rubric')
{
	$html = '';
	foreach ($data as $r) {
		//	$html .= '<a href="/'.$r['path'].'/'.$pgid.'.html" class="itemimg"><img src="'.$r['img_catalog'].'" alt="'.$r['name'].'"/></a>';
		$html .= '
			<div class="blockname">
				' . $r['name'] . '
			</div><ul>';
		foreach ($r['#item#'] as $rr) {
			$html .= '<li><a href="/' . $rr['path'] . '/' . $pgid . '.html" class="' . ($rr['#sel#'] ? ' selected' : '') . '">' . $rr['name'] . '' .
				($rr['cnt'] > 0 ? '<span class="minicount">(' . $rr['cnt'] . ')</span>' : '')
				. '</a></li>';
		}
		$html .= '</ul>';
	}
	return $html;
}

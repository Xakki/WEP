<?php
function tpl_adminmenu(&$data)
{
	global $_CFG;
	$html = '';
	$html .= '<a class="am_exit" href="' . $_CFG['PATH']['admin'] . 'logout">Выход</a>';
	$html .= '<a class="am_home" href="/index.html" target="_blank">Главная страница</a>';
	$sys_r = '';
	$sys_m = '';
	$over_m = '';
	$over = '';
	if (is_array($data['item']) and count($data['item'])) {
		foreach ($data['item'] as $k => &$r) {
			$sel = '';
			if ($r['sel'])
				$sel = ' msel';
			if (isset($r['css']) and !isset($r['tablename'])) {
				$over .= '<a class="' . $r['css'] . $sel . '" href="' . $_CFG['PATH']['admin'] . '?_view=list&amp;_modul=' . $k . '">' . $r['name'] . '</a>';
			}
			elseif (!$r['tablename']) continue;
			elseif (
				(isset($_CFG['require_modul'][$k]) and $_CFG['require_modul'][$k]) or
				($r['extend'] and isset($_CFG['require_modul'][$r['extend']]) and $_CFG['require_modul'][$r['extend']])
			) {
				$sys_r .= '<li class="fly"><a class="main down' . $sel . '" href="' . $_CFG['PATH']['admin'] . '?_view=list&amp;_modul=' . $k . '">' . $r['name'] . '</a></li>';
			}
			elseif ($r['typemodul'] == 0) {
				$sys_m .= '<li class="fly"><a class="main down' . $sel . '" href="' . $_CFG['PATH']['admin'] . '?_view=list&amp;_modul=' . $k . '">' . $r['name'] . '</a></li>';
			}
			elseif ($r['typemodul'] == 3) {
				$over_m .= '<li class="fly"><a class="main down' . $sel . '" href="' . $_CFG['PATH']['admin'] . '?_view=list&amp;_modul=' . $k . '">' . $r['name'] . '</a></li>';
			}
		}
		unset($r);
	}
	$html .= '<ul class="nav">';
	if ($sys_r) {
		$html .= '<li class="drop"><a class="main">Главные модули</a><ul>' . $sys_r . '</ul></li>';
	}
	if ($sys_m) {
		$html .= '<li class="drop"><a class="main">Вторичные модули</a><ul>' . $sys_m . '</ul></li>';
	}
	if ($over_m) {
		$html .= '<li class="drop"><a class="main">Модули</a><ul>' . $over_m . '</ul></li>';
	}
	$html .= '</ul>';

	if ($over) {
		$html .= $over;
	}
	$m_ug = $name = _getExtMod('ugroup');
	$m_u = $name = _getExtMod('users');
	$html .= '<a class="am_user" href="' . $_CFG['PATH']['admin'] . 'index.php?_view=list&_modul=' . $m_ug . '&' . $m_ug . '_id=' . $data['user']['gid'] . '&' . $m_ug . '_ch=' . $m_u . '&' . $m_u . '_id=' . $data['user']['id'] . '&_type=update">' . $data['user']['name'] . ' [' . $data['user']['gname'] . ']</a>';

	return $html . '<div class="clk"></div>';
}

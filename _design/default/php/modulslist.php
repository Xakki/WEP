<?php
function tpl_modulslist(&$data)
{
	global $_CFG;
	$html = '';
	if (isset($data['item']) and count($data['item']))
		foreach ($data['item'] as $k => $r)
			$html .= '<div class="modullist' . ($data['modul'] == $k ? ' selected' : '') . '"><a href="' . ADMIN_BH. '/index.php?_view=list&amp;_modul=' . $k . '">' . $r . '</a></div>';
	$html .= '<div class="clk"></div>';
	return $html;
}


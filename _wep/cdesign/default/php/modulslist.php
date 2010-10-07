<?
	function tpl_modulslist(&$data) {
		$html = '';
		foreach($data['item'] as $k=>$r)
			$html .= '<div class="modullist'.($data['modul']==$k?' selected':'').'"><a href="index.php?_view=list&amp;_modul='.$k.'">'.$r.'</a></div>';
		$html .= '<div class="clk"></div>';
		return $html;
	}
?>

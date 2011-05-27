<?
	function tpl_modulslist(&$data) {
		global $_CFG;
			//	print_r($_CFG);
		$html = '';
		if(isset($data['item']) and count($data['item']))
			foreach($data['item'] as $k=>$r)
				$html .= '<div class="modullist'.($data['modul']==$k?' selected':'').'"><a href="'.$_CFG['PATH']['wepname'].'/index.php?_view=list&amp;_modul='.$k.'">'.$r.'</a></div>';
		$html .= '<div class="clk"></div>';
		return $html;
	}


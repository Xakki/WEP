<?
	function tpl_sysconf(&$data) {
		$html = '<a href="login.php?exit=ok"><img src="cdesign/default/img/close48.gif" class="exit" alt="CLOSE"/></a><div class="uname">'.$data['user']['name'].' ['.$data['user']['gname'].']</div>';
		if(is_array($data['item']) and count($data['item']))
			foreach($data['item'] as $k=>$r)
			$html .= '<div class="modullist"><a href="index.php?_view=list&amp;_modul='.$k.'">'.$r.'</a></div>';
		$html .= '<div class="modullist"><a href="../" target="_blank">Главная страница</a></div><div class="clk"></div>';
		return $html;
	}
?>
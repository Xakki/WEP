<?php
	function tpl_sysconf(&$data) {
		global $_CFG;
		$html = '<a href="'.$_CFG['PATH']['wepname'].'/login.php?exit=ok"><img src="'.$_CFG['PATH']['wepname'].'/cdesign/default/img/close48.gif" class="exit" alt="CLOSE"/></a><div class="uname">'.$data['user']['name'].' ['.$data['user']['gname'].']</div>';
		if(is_array($data['item']) and count($data['item']))
			foreach($data['item'] as $k=>$r)
			$html .= '<div class="modullist'.($data['modul']==$k?' selected':'').'"><a href="'.$_CFG['PATH']['wepname'].'/index.php?_view=list&amp;_modul='.$k.'">'.$r.'</a></div>';
		$html .= '<div class="modullist"><a href="index.html" target="_blank">Главная страница</a></div><div class="clk"></div>';
		return $html;
	}

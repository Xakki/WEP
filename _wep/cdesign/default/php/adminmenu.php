<?
	function tpl_adminmenu(&$data) {
		global $_CFG;
		$html = '<ul>';
		$html .= '<li><a href="'.$_CFG['PATH']['wepname'].'/login.php?exit=ok" class="am_exit">Выход</a></li>';
		$html .= '<li><a href="index.html" target="_blank" class="am_home">Главная страница</a></li>';
		$html .= '<li><a href="'.$_CFG['PATH']['wepname'].'/index.php?_view=list&amp;_modul=ugroup" class="am_user">'.$data['user']['name'].' ['.$data['user']['gname'].']</a></li>';
		//$html .= '<div class="uname"></div>';
		//print_r('<pre>');print_r($data['modul']);
		$sys_r = '';
		$sys_m = '';
		$over_m = '';
		$over = '';
		if(is_array($data['item']) and count($data['item']))
			foreach($data['item'] as $k=>&$r) {
				if(!is_array($r)) {
					$over .= '<li><a href="'.$_CFG['PATH']['wepname'].'/index.php?_view=list&amp;_modul='.$k.'">'.$r.'</a></li>';
				}
				elseif(isset($_CFG['require_modul'][$k]) and $_CFG['require_modul'][$k]) {
					//$k = _getExtMod($k);
					$sys_r .= '<li><a href="'.$_CFG['PATH']['wepname'].'/index.php?_view=list&amp;_modul='.$k.'">'.$r['name'].'</a></li>';
				}elseif($r['typemodul']==0) {
					$sys_m .= '<li><a href="'.$_CFG['PATH']['wepname'].'/index.php?_view=list&amp;_modul='.$k.'">'.$r['name'].'</a></li>';
				}elseif($r['typemodul']==3) {
					$over_m .= '<li><a href="'.$_CFG['PATH']['wepname'].'/index.php?_view=list&amp;_modul='.$k.'">'.$r['name'].'</a></li>';
				}
			}
		if($sys_r) {
			$html .= '<li>Главные модули<ul>'.$sys_r.'</ul></li>';
		}
		if($sys_m) {
			$html .= '<li>Вторичные модули<ul>'.$sys_m.'</ul></li>';
		}
		if($over_m) {
			$html .= '<li>Модули<ul>'.$over_m.'</ul></li>';
		}
		if($over) {
			$html .= $over;
		}
			//$html .= '<div class="modullist'.($data['modul']==$k?' selected':'').'"><a href="'.$_CFG['PATH']['wepname'].'/index.php?_view=list&amp;_modul='.$k.'">'.$r.'</a></div>';
		return $html.'</ul><div class="clk"></div>';
	}

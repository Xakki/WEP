<?php

	function tpl_pagenum(&$data) {
		global $_CFG,$_tpl;
		if(!$data or !count($data)) return '';
		$html = '<div class="pagenumcnt">'.$data['cnt'].'&#160;:&#160;&#160;</div>';
		if(isset($data['link']) and count($data['link'])) {
			$_tpl['onload'] .='pagenum_super('.$data['cntpage'].','.$data['_pn'].',\''.$data['modul'].'\','.($data['reverse']?'true':'false').');';
			$html .= '<div class="pagenum">';
			foreach($data['link'] as $k=>$r) {
				if($r['href']=='')
					$html .=  $r['value'];
				elseif($r['href']=='select_page')
					$html .=  '<b>['.$r['value'].']</b>';
				else
					$html .=  '<a href="'.$r['href'].'" onclick="return wep.load_href(this)">'.$r['value'].'</a>';
			}
			$html .= '&#160;</div><div class="ppagenum"></div>';
		}
		$html .= '<select class="mopselect" onchange="setCookie(\''.$data['modul'].'_mop\',this.value,20);window.location.reload();">';
		//,\''.$_CFG['session']['path'].'\',\''.$_CFG['session']['domain'].'\',\''.$_CFG['session']['secure'].'\'
		//JSWin({\'href\':\''.$_CFG['_HREF']['JS'].'?_view=pagenum&_modul='.$data['modul'].'&mop=\'+this.value})
		if(count($data['mop'])) {
			foreach($data['mop'] as $k=>$r) {
				$html .=  '<option value="'.$k.'"'.($r['sel']?' selected="selected"':'').'>'.$r['value'].'</option>';
			}
		}
		$html .= '</select>';
		return $html;
	}
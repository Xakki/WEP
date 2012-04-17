<?php

	function tpl_pagenum(&$data) {
		global $_CFG,$_tpl;
		if(!$data or !count($data)) return '<div style="padding-top:40px;"></div>';

		$html = '<div align="left" class="pagenum">';
		if(isset($data['link']) and count($data['link'])) {
			$html .= 'Страницы:';
			$lastk = 0;
			foreach($data['link'] as $k=>$r) {
				if($k==$data['_pn'])
					$html .=  '<b>['.$k.']</b>';
				elseif(!$r)
					$html .=  '<b>...</b>';
				else
					$html .=  '<a href="'.$r.'" onclick="return wep.load_href(this)" title="Страница №'.$k.'">'.$k.'</a>';
				$lastk = $k;
			}

		}
		$html .= '</div>';
		return $html;
	}
?>



<?php
/**
 * Постраничная навигация
 * @type Контент
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
	function tpl_pagenum(&$data) {
		global $_CFG,$_tpl;
		if(!$data or !count($data)) return '<div style="padding-top:40px;"></div>';

		$html = '<div align="left" class="pagenum">';
		if(isset($data['link']) and count($data['link'])) {
			$html .= '';
			if($data['_pn']==1)
				$html .=  '<span>&lt;&lt;</span>';
			elseif($data['_pn']==2)
				$html .=  '<a href="'.$data['PP'][0].'" onclick="return wep.load_href(this)">&lt;&lt;</a>';
			else
				$html .=  '<a href="'.$data['PP'][1].($data['_pn']-1).$data['PP'][2].'" onclick="return wep.load_href(this)">&lt;&lt;</a>';

			foreach($data['link'] as $k=>$r) {
				if($k==$data['_pn'])
					$html .=  '<b>'.$k.'</b>';
				elseif(!$r)
					$html .=  '<span>...</span>';
				else
					$html .=  '<a href="'.$r.'" onclick="return wep.load_href(this)" title="Страница №'.$k.'">'.$k.'</a>';
			}
			if($data['_pn']==$data['cntpage'])
				$html .=  '<span>&gt;&gt;</span>';
			else
				$html .=  '<a href="'.$data['PP'][1].($data['_pn']+1).$data['PP'][2].'" onclick="return wep.load_href(this)">&gt;&gt;</a>';
		}
		$html .= '</div>';
		return $html;
	}
?>



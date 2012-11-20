<?php
/**
 * Галерея
 * @type Медиа
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
	function tpl_gallery(&$data) {
		global $_tpl, $HTML, $_CFG;
		$_tpl['styles']['/_gallery/style/gallery'] = 1;
		$html = '';
		if(isset($data['#list-gallitem#']) and count($data['#list-gallitem#'])) {
			$_CFG['fileIncludeOption']['fancybox']=1;
			$html .= '
				<div class="dscr">'.$data['#info-gallery#']['dscr'].'</div>
				'.($data['#info-gallery#']['tags']?'<div>Теги: '.$data['#info-gallery#']['tags'].'</div>':'').'
				<ul class="gallitem">';
			foreach($data['#list-gallitem#'] as $k=>$r) {
				$html .= '<li><a href="'.$r['gallimg'].'" class="fancyimg" rel="fancy'.$r['owner_id'].'" title="'.$r['name'].'"><img src="'.$r['gallimg'].'" alt="'.$r['name'].'"/></a>';
			}
			$html .= '</ul>';
		}
		elseif(isset($data['#list-gallery#']) and count($data['#list-gallery#'])) {
			$html .= '<ul class="gallery">';
			foreach($data['#list-gallery#'] as $k=>$r) {
				$html .= '<li><a href="/'.$data['#page#'].'/'.$k.'.html"><img src="'.$data['#temp-gallitem#'][$k]['gallimg'].'" alt="'.$r['name'].'"/></a> <div>'.$r['name'].'</div>';
			}
			$html .= '</ul>';
		}
		return $html;
	}

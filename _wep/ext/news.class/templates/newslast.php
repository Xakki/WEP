<?php
	function tpl_newslast(&$data) {
		$html = '';
		if(isset($data['#item#']) and count($data['#item#'])) {
			$html .= '<div class="newslast">
				<h3>'.$data['#Ctitle#'].'</h3>
					<div class="news-block">';
			foreach($data['#item#'] as $k=>$r) {
				$html .= '<div class="news-block-item">
					<span class="news-date">'.static_main::_date('d F Y',$r['ndate']).'г.</span><span class="news-cat">/ '.$r['name'].'</span>
					<p>'.$r['description'].'</p>
					<a href="'.$data['#page#'].'/'.$r['id'].'.html" title="Читать далее">подробнее...</a>
				</div>';
			}
			$html .= '<a href="'.$data['#page#'].'.html" class="vsmr-news-link" title="'.$data['#Ctitle#'].'"><img src="/_design/default/img/right-b.png" alt="'.$data['#Ctitle#'].'"/></a>
			</div></div>';
		}
		return $html;
	}
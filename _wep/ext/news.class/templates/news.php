<?php
	function tpl_news(&$data) {
		$html = '';
		if(isset($data['#item#']) and count($data['#item#'])) {
			$html .= '<div class="news">';
			foreach($data['#item#'] as $k=>$r) {
				$html .= '<div class="news-item">
						<span class="newsi-date">['.date('Y-m-d',$r['ndate']).']</span>
						<span class="newsi-name"><a href="'.$data['#page#'].'/'.$r['id'].'.html">'.$r['name'].'</a></span>
						<span class="newsi-desc">'.$r['description'].'</span>
					</div>';
			}
			$html .= '</div>';
		} elseif(isset($data[0]) and count($data[0])) {
			$r = $data[0];
				$html .= '<div class="news-item">
						<span class="newsi-date">['.date('Y-m-d',$r['ndate']).']</span>
						<span class="newsi-name"><a href="'.$data['#page#'].'/'.$r['id'].'.html">'.$r['name'].'</a></span>
						<span class="newsi-desc">'.$r['description'].'</span>
					</div>';
		}
		return $html;
	}
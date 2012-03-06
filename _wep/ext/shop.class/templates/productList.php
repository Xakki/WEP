<?php
	function tpl_productList(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			$html = '<div class="prodlist">';
			if(!isset($data['#item#']) or !count($data['#item#'])) {
				$html .= 'нету';
			} else {

				global $_tpl;
				$_tpl['styles']['../default/style/proditem'] = 1;

				foreach($data['#item#'] as $r) {
					$html .= '<div class="proditem">';
					$href = $r['rpath'].'/'.$r['path'].'_'.$r['id'].'.html';
					$html .= '<a href="'.$href.'" class="prodname">'.$r['name'].'</a>';
					$html .= '<a href="'.$href.'" title="'.$r['name'].'" class="prodimg">';
					if(count($r['image']) and $r['image'][0][0]) {
						$html .= '<img src="'.$r['image'][0][0].'" alt="'.$r['name'].'"/>';
					} else
						$html .= '<img src="_design/default/img/cancel.png" alt="'.$r['name'].'"/>';
					$html .= '</a><div class="proddescr">'.$r['descr'].'</div><br/>';
					//$html .= '';
					if(!$r['cost'])
						$r['cost'] = '&#160;';
					else
						$r['cost'] = $r['cost'].' <span>руб.</span>';
					$html .= '<a href="'.$href.'" title="заказать доставку" class="prodcost">'.$r['cost'].'</a>';
					$html .= '</div>';
				}
			}
			$html .= '</div>';
		}
		return $html;
	}


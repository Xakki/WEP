<?php
	function tpl_productLike(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			global $_tpl,$HTML;
			$_tpl['styles']['../'.$HTML->_design.'/_shop/style/product'] = 1;

			if(!isset($data['#item#']) or !count($data['#item#'])) {

			} 
			else {
				$html = '<div class="prodLike">
				<h3>Сопутствующие товары</h3>';
				$PGnum = '';
				if(isset($data['pagenum']) and count($data['pagenum'])) {
					$PGnum = $HTML->transformPHP($data['pagenum'],'#pg#pagenum');
					$html .= $PGnum;
				}

				foreach($data['#item#'] as $r) {
					$html .= '<div class="proditem">';
					$href = $data['#page#'].'/'.$r['rpath'].'/'.$r['path'].'_'.$r['id'].'.html';
					if(isset($data['atarget']))
						$href .= '" target="'.$data['atarget'].'"';
					//$html .= '<a href="'.$href.'" class="prodname">'.$r['name'].'</a>';
					$html .= '<a href="'.$href.'" title="'.$r['name'].'" class="prodimg" target="_blank">';
					if(isset($r['image']) and count($r['image']) and $r['image'][0][1]) {
						$html .= '<img src="/'.$r['image'][0][1].'" alt="'.$r['name'].'"/>';
					} else
						$html .= '<img src="/_design/'.$HTML->_design.'/_shop/img/nofoto.gif" alt="'.$r['name'].'"/>';
					$html .= '</a>';
					//<div class="proddescr">'.$r['descr'].'</div>
					//$html .= '';
					if(!$r['cost'])
						$r['cost'] = '&#160;';
					else
						$r['cost'] = round($r['cost'],2).' <span class="cur">руб.</span>';
					$html .= '<a href="'.$href.'" title="заказать доставку" class="prodcost">'.$r['cost'].'</a>';
					$html .= '</div>';
				}
				$html .= $PGnum;
				$html .= '</div>';
			}
		}
		return $html;
	}


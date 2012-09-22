<?php
	function tpl_productEvent(&$data) {
		$html = '*';
		print_r('<pre>');print_r($data); return $html;

		if(!isset($data['#item#']) or !count($data['#item#'])) 
		{
			$html = $data['#text#']; // Товаров дня нет
		} 
		else {
			global $_CFG, $_tpl,$HTML;
			$_CFG['fileIncludeOption']['fancybox'] = true;
			$_tpl['styles']['../'.$HTML->_design.'/_shop/style/shopEvent'] = 1;
			$_tpl['script']['../'.$HTML->_design.'/_shop/script/shopEvent'] = 1;

			$html = '<div class="productEvent">';

			foreach($data['#item#'] as $r) {
				$href = $data['#page#'].'/'.$r['rpath'].'/'.$r['path'].'_'.$r['id'].'.html';
				//<a href="'.$href.'" class="prodname">'.$r['name'].'</a>
				$html .= '<div class="proditem">
					<div class="prodimg-block">';

				if(isset($r['image']) and count($r['image']) and $r['image'][0][0]) {
					$fimg = array_shift($r['image']);
					$html .= '<a href="/'.$fimg[0].'" title="'.$r['name'].'" class="prodimg-first fancyimg" rel="fancy"><img src="/'.$fimg[1].'" alt="'.$r['name'].'"/></a>';
					foreach($r['image'] as $img) {
						$html .= '<a href="/'.$img[0].'" title="'.$r['name'].'" class="prodimg-over fancyimg" rel="fancy"><img src="/'.$img[1].'" alt="'.$r['name'].'"/></a>';
					}
				} else
					$html .= '<br/><img src="_design/'.$HTML->_design.'/_shop/img/nofoto.gif" alt="'.$r['name'].'" class="prodimg-first"/>';
				$html .= '</div>';

				if(count($data['#prodItem#'])) {
					$html .= '<ul class="prodparam">';
					foreach($data['#prodItem#'] as $kpi=>$rpi) {
						if($r[$kpi] and $rpi) $html .= '<li>'.$rpi.' - '.$r[$kpi];
					}
					$html .= '</ul>';
				}

				if($r['descr'])
					$html .= '<div class="proddescr">'.$r['descr'].'</div>';


				if(!$r['cost'])
					$r['cost'] = 'не указана';
				else {
					$r['cost'] = round($r['cost'],2).' <span class="cur">руб.</span>';
					if(isset($r['sale'])) {
						//if($r['sale']['name'])
						//	$r['cost'] = '<div class="prodsale">'.$r['sale']['name'].'</div>';
						$r['cost'] = '<span class="old">'.$r['old_cost'].'</span> '.$r['cost'];
					}
				}

				/*PRICE BLOCK*/ 
				$html .= '<div class="prodBlock">
					<div class="prodBlock-price">
						<p>Цена: <span class="cost">'.$r['cost'].'</span></p>
						'.(isset($r['sale']['name'])?'<p class="sale ico"><i></i>'.$r['sale']['name'].'</p>':'').'
						'.($data['#shopconfig#']['available']?'<p class="available ico"><i></i>'.$r['#available#'].'</p>':'').'
					</div>';

					if(isset($data['#basket#']) and isset($data['#shopconfig#']['orderset'][1]))
						$html .= '<div class="prodBlock-button prodBlock-basket ico'.(isset($data['#basket#'][$r['id']])?' inbasket':'').'" data-id="'.$r['id'].'"><i></i>
							<span class="put">Положить в корзину</span>
							<span class="go" title="Товар лежит в корзине">Перейти в корзину</span>
						</div>';

					if(isset($data['#shopconfig#']['orderset'][0]))
						$html .= '<div class="prodBlock-button prodBlock-buy1 ico" data-id="'.$r['id'].'"><i></i>Купить в 1 клик</div>';

				$html .= '</div>';

				$html .= '<div class="prodtext">'.$r['text'].'</div>';
				$html .= '<br/><br/></div>';
			}
		}
		$html .= '</div>';

		return $html;
	}


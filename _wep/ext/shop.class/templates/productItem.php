<?php
	function tpl_productItem(&$data) {
		$html = '';

		$html = '<div class="prodpage">';
		if(!isset($data['#item#']) or !count($data['#item#'])) {
			header("HTTP/1.0 404");
			$html .= 'Не верная ссылка, либо товара удален';
		} else {

			global $_CFG, $_tpl,$HTML;
			$_CFG['fileIncludeOption']['fancybox'] = true;
			$_tpl['styles']['../'.$HTML->_design.'/_shop/style/product'] = 1;
			$_tpl['styles']['form'] = 1;
			$_tpl['script']['../'.$HTML->_design.'/_shop/script/shop'] = 1;

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

				$html .= '</div>
				<br/>';

				if($r['descr'])
					$html .= '<div class="proddescr">'.$r['descr'].'</div>';

				if(count($data['#prodItem#'])) {
					$html .= '<ul class="prodparam">';
					foreach($data['#prodItem#'] as $kpi=>$rpi) {
						if($r[$kpi] and $rpi) $html .= '<li>'.$rpi.' - '.$r[$kpi];
					}
					$html .= '</ul>';
				}
				/*PRICE BLOCK END*/ 
				
				/*if(isset($r['sale'])) {
					if($r['sale']['name'])
						$html .= '<div class="prodsale">'.$r['sale']['name'].'</div>';
					$html .= '<div class="prodcost"><span class="old">'.$r['old_cost'].'</span> '.$r['cost'].'</div>';
				} else
					$html .= '<div class="prodcost">'.$r['cost'].'</div>';*/

				/*if(isset($data['#shopconfig#']['orderset'][0]))
					$html .= '<div class="buybutton">
						<a href="##zakaz" alt="Оформить заказ" onclick="return JSWin({\'href\':\''.$_CFG['_HREF']['siteAJAX'].'?_modul=shop&_fn=jsOrder&id='.$r['id'].'\'});">Оформить заказ</a>
					</div>';*/

				/*if(isset($data['#basket#']) and isset($data['#shopconfig#']['orderset'][1])) {
					$html .= '<div class="buybutton'.(isset($data['#basket#'][$r['id']])?' sel':'').'">
						<a href="javascript::void();" data-id="'.$r['id'].'" alt="Убрать товар из корзины" class="dellink">Убрать из корзины</a>
						<a href="javascript::void();" data-id="'.$r['id'].'" alt="Положить товар в корзину" class="addlink">В корзину</a>
						<input type="number" min="1" max="50" value="'.(isset($data['#basket#'][$r['id']])?$data['#basket#'][$r['id']]['count'].'" disabled="disabled':1).'"/>
					</div>';
				}*/


				if(count($r['param'])) {
					$html .= '<h3 class="prodparam-h">ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ</h3><ul class="prodparam">';
					foreach($r['param'] as $d) {
						if($d['value'])
							$html .= '<li>'.$d['name'].' - '.$d['value'].' '.$d['edi'];
					}
					$html .= '</ul>';
				}
				$html .= '<div class="prodtext">'.$r['text'].'</div>';
				$html .= '<br/><br/></div>';
			}
		}
		$html .= '</div>';

		return $html;
	}


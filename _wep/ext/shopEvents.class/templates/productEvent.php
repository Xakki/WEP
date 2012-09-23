<?php
	function tpl_productEvent(&$data) {
		$html = '';
		//print_r('<pre>');print_r($data);return $html;

		if(!isset($data['#item#']) or !count($data['#item#'])) 
		{
			$html = $data['#text#']; // Товаров дня нет
		} 
		else {
			global $_CFG, $_tpl,$HTML;
			$_tpl['styles']['../'.$HTML->_design.'/_shop/style/shopEvents'] = 1;
			//$_tpl['script']['../'.$HTML->_design.'/_shop/script/shopEvents'] = 1;

			$html = '<div class="productEvent">
				<h3>'.$data['#title#'].'</h3>';

			foreach($data['#item#'] as $r) {
				$href = $data['#page#'].'/'.$r['rpath'].'/'.$r['path'].'_'.$r['id'].'.html';
				$html .= '<div class="proditem">
					<div class="prodimg-block">';

				if(isset($r['image']) and count($r['image']) and $r['image'][0][0]) {
					$fimg = array_shift($r['image']);
					$html .= '<a href="/'.$fimg[0].'" title="'.$r['name'].'" class="prodimg-first fancyimg" rel="fancy"><img src="/'.$fimg[1].'" alt="'.$r['name'].'"/></a>';
				} else
					$html .= '<br/><img src="_design/'.$HTML->_design.'/_shop/img/nofoto.gif" alt="'.$r['name'].'" class="prodimg-first"/>';
				$html .= '</div>
				<div class="prodinfo-block">
					<a href="'.$href.'" class="prodname">'.$r['name'].'</a>
					<p class="proddescr">'.$r['descr'].'</p>
					<div class="progress">
						<span>Продано уже 60% товаров</span>
						<div class="bar"></div>
						<span>До окончания продаж</span>
						<div class="timer">07 час. 57 мин. 22 сек.</div>
					</div>
				</div>
				';

				$cur = ' <span class="cur">руб.</span>';
				if(!$r['cost'])
					$r['cost'] = 'не указана';
				else {
					$r['cost'] = round($r['cost'],2);
					if(isset($data['#event#'][$r['id']])) {
						$r['newcost'] = round($data['#event#'][$r['id']]['cost'],2);
						$r['cost'] = '<span class="old">'.$r['cost'].$cur.'</span> 
						<span class="save">экономия '.($r['cost'] - $r['newcost']).$cur .'</span>
						<span class="new">'.$r['newcost'].$cur .'</span>';
					}
				}

				/*PRICE BLOCK*/ 
				$html .= '
					<div class="prodBlock">
						<div class="prodBlock-price">
							<label>Цена:</label> <span class="cost">'.$r['cost'].'</span>
						</div>
						<div class="prodBlock-button prodBlock-buy1 ico" data-id="'.$r['id'].'"><i></i>Купить в 1 клик</div>
					</div>
				</div>';
			}
		}
		$html .= '</div>';

		return $html;
	}


<?php
/**
 * Список товаров в табличном ввиде
 * @type Магазин
 * @tags shopprodlist
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
	function tpl_prodListTable(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			global $_tpl, $HTML, $_CFG;
			$_tpl['styles']['/_shop/style/product'] = 1;

			$html = '<div class="prodListTable">';
			if(!isset($data['#item#']) or !count($data['#item#'])) {
				if(isset($data['#filter#']))
					$html .= '<h3>К сожалению, по вашему запросу товары не найдены</h3>';
				else
					$html .= '<h3>В данной категории товары ещё не добавлены!</h3>';
			} 
			else {

				$PGnum = '';
				if(isset($data['pagenum']) and count($data['pagenum'])) {
					$PGnum = $HTML->transformPHP($data['pagenum'],'#pg#pagenum');
					$html .= $PGnum;
				}
				$html .= '<table cellpadding="0" cellspacing="0"><tr>';
				if(isset($data['#prodListTable#'])) {
					foreach($data['#prodListTable#'] as $cf_k=>$cf_r) {
						$html .= '<th>'.$cf_r;
						if($cf_k=='cost') $html .= ' <span>(руб.)</span>';
					}
				}
				$html .= '</tr>';
				foreach($data['#item#'] as $r) {
					$href = $data['#page#'].'/'.$r['rpath'].'/'.$r['path'].'_'.$r['id'].'.html';
					if(isset($data['atarget']))
						$href .= '" target="'.$data['atarget'].'"';
					if(isset($r['image']) and count($r['image']) and $r['image'][0][1]) {
						$img = $r['image'][0][1];
					} else
						$img = $_CFG['PATH']['themes'].$HTML->_design.'/_shop/img/nofoto.gif';
					$html .= '<tr data-id="'.$r['id'].'" class="'.(isset($data['#basket#'][$r['id']])?'sel':'').'">';
					if(isset($data['#prodListTable#'])) {
						foreach($data['#prodListTable#'] as $cf_k=>$cf_r) {
							if($cf_k=='cost') {
								$html .= '<td>'.($r['cost']?round($r['cost'],2):'&#160;');
							} 
							elseif(strpos($cf_k,'img_product')!==false)
								$html .= '<td><img src="/'.$img.'" alt="'.$r['name'].'"/>';
							elseif($cf_k=='name') {
								$html .= '<td><a href="'.$href.'" title="'.$r['name'].'">'.$r['name'].'</a>';
								if(isset($r['sale']))
									$html .= '<span class="prodlable sale" title="'.$r['sale']['name'].'">&#160;</span>';
							}
							elseif(isset($r[$cf_k]) and $r[$cf_k])
								$html .= '<td>'.$r[$cf_k];
							else
								$html .= '<td> - ';
						}
						if(isset($data['#basket#'])) {
							$html .= '<td class="addbasket">
							<a href="##vkorziny" title="В корзину" class="addlink">
								<img src="/'.$_CFG['PATH']['themes'].$HTML->_design.'/_shop/img/basket-add.png" alt="В корзину"/>
							</a>
							<a href="##vkorziny" title="Перейти в корзину" class="gotobasket">
								<img src="/'.$_CFG['PATH']['themes'].$HTML->_design.'/_shop/img/basket-go.png" alt="В корзину"/>
							</a>
							<input type="hidden" min="1" max="50" value="'.(isset($data['#basket#'][$r['id']])?$data['#basket#'][$r['id']]['count'].'" disabled="disabled':1).'"/>
							';
							/*
							<a href="##vkorziny" title="Удалить из корзины" class="dellink">
								<img src="/'.$_CFG['PATH']['themes'].$HTML->_design.'/_shop/img/basket-del.png" alt="Удалить из корзины"/>
							</a>
							*/
							$_tpl['script']['/_shop/script/shop'] = 1;
						}
					}
					$html .= '</tr>';					
				}
				$html .= '</table>';
				$html .= $PGnum;
			}
			$html .= '</div>';
		}
		return $html;
	}

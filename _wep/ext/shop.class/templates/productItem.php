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

			foreach($data['#item#'] as $r) {
				$href = $data['#page#'].'/'.$r['rpath'].'/'.$r['path'].'_'.$r['id'].'.html';
				$html .= '<div class="proditem">
					<a href="'.$href.'" class="prodname">'.$r['name'].'</a>
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

				if(!$r['cost'])
					$r['cost'] = '&#160;';
				else
					$r['cost'] = $r['cost'].' <span>руб.</span>';
				$html .= '<div class="proddescr">'.$r['descr'].'</div><br/>
					<div class="prodcost">'.$r['cost'].'</div> 
					<div class="buybotton">
						<a href="##zakaz" alt="Оформить заказ" onclick="return JSWin({\'href\':\''.$_CFG['_HREF']['siteAJAX'].'?_modul=shop&_fn=jsOrder&id='.$r['id'].'\'});">Купить</a>
					</div>';
				if($data['#basketEnabled#']) {
					$html .= '<div class="buybotton">
						<a href="##zakaz" alt="Положить покупку в корзину" onclick="return JSWin({\'href\':\''.$_CFG['_HREF']['siteAJAX'].'?_modul=shop&_fn=jsAddBuyItem&id='.$r['id'].'\'});">В корзину</a>
						<input type="number" value="1"/>
					</div>';
				}


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


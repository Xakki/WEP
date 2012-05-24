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
					<div class="prodcost">'.$r['cost'].'</div>';

				if(isset($data['#shopconfig#']['orderset'][0]))
					$html .= '<div class="buybotton">
						<a href="##zakaz" alt="Оформить заказ" onclick="return JSWin({\'href\':\''.$_CFG['_HREF']['siteAJAX'].'?_modul=shop&_fn=jsOrder&id='.$r['id'].'\'});">Оформить заказ</a>
					</div>';

				if(isset($data['#basket#']) and isset($data['#shopconfig#']['orderset'][1])) {
					$html .= '<div class="buybotton'.(isset($data['#basket#'][$r['id']])?' sel':'').'">
						<a href="javascript::void();" data-id="'.$r['id'].'" alt="Убрать товар из корзины" class="dellink">Убрать из корзины</a>
						<a href="javascript::void();" data-id="'.$r['id'].'" alt="Положить товар в корзину" class="addlink">В корзину</a>
						<input type="number" min="1" max="50" value="'.(isset($data['#basket#'][$r['id']])?$data['#basket#'][$r['id']]['count'].'" disabled="disabled':1).'"/>
					</div>';
					$_tpl['script']['../'.$HTML->_design.'/_shop/script/shop'] = 1;
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


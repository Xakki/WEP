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

			foreach($data['#item#'] as $r) {
				$href = $data['#page#'].'/'.$r['rpath'].'/'.$r['path'].'_'.$r['id'].'.html';
				$html .= '<div class="proditem">
					<a href="'.$href.'" class="prodname">'.$r['name'].'</a>
					<div class="prodimg-block">';

				if(count($r['image']) and $r['image'][0][0]) {
					$fimg = array_shift($r['image']);
					$html .= '<a href="/'.$fimg[0].'" title="'.$r['name'].'" class="prodimg-first fancyimg" rel="fancy"><img src="/'.$fimg[1].'" alt="'.$r['name'].'"/></a>';
					foreach($r['image'] as $img) {
						$html .= '<a href="/'.$img[0].'" title="'.$r['name'].'" class="prodimg-over fancyimg" rel="fancy"><img src="/'.$img[1].'" alt="'.$r['name'].'"/></a>';
					}
				} else
					$html .= '<br/><img src="_design/'.$HTML->_design.'/_shop/img/nofoto.gif" alt="'.$r['name'].'"/>';

				if(!$r['cost'])
					$r['cost'] = '&#160;';
				else
					$r['cost'] = $r['cost'].' <span>руб.</span>';

				$html .= '</div>
					<div class="proddescr">'.$r['descr'].'</div><br/>
					<div class="prodcost">'.$r['cost'].'</div> <a href="##zakaz" alt="Подать заявку на покупку" onclick="return JSWin({\'href\':\''.$_CFG['_HREF']['siteAJAX'].'?_modul=shop&_fn=jsOrder&id='.$r['id'].'\'});">Заказать</a>';
				$_tpl['styles']['form'] = 1;

				if(count($r['param'])) {
					$html .= '<h3 class="prodparam-h">ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ</h3><ul class="prodparam">';
					foreach($r['param'] as $d) {
						if($d['value'])
							$html .= '<li>'.$d['name'].' - '.$d['value'].' '.$d['edi'].'</li>';
					}
					$html .= '</ul>';
				}
				$html .= '<div class="prodtext">'.$r['text'].'</div>';
				$html .= '</div>';
			}
		}
		$html .= '</div>';
		return $html;
	}


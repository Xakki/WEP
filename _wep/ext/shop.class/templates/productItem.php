<?php
	function tpl_productItem(&$data) {
		$html = '';
		$html = '<div class="prodpage">';
		if(!isset($data) or !count($data)) {
			$html .= 'нету';
		} else {

			global $_CFG, $_tpl;
			$_CFG['fileIncludeOption']['fancybox'] = true;
			$_tpl['styles']['../default/style/productItem'] = 1;

			foreach($data as $r) {
				$href = $r['rpath'].'/'.$r['path'].'_'.$r['id'].'.html';
				$html .= '<div class="proditem">
					<a href="'.$href.'" class="prodname">'.$r['name'].'</a>
					<div class="prodimg-block">';
				if(count($r['image']) and $r['image'][0][0]) {
					$fimg = array_shift($r['image']);
					$html .= '<a href="/'.$fimg[1].'" title="'.$r['name'].'" class="prodimg-first fancyimg" rel="fancy"><img src="/'.$fimg[0].'" alt="'.$r['name'].'"/></a>';
					foreach($r['image'] as $img) {
						$html .= '<a href="/'.$img[1].'" title="'.$r['name'].'" class="prodimg-over fancyimg" rel="fancy"><img src="/'.$img[0].'" alt="'.$r['name'].'"/></a>';
					}
				} else
					$html .= '<img src="_design/default/img/cancel.png" alt="'.$r['name'].'"/>';

				if(!$r['cost'])
					$r['cost'] = '&#160;';
				else
					$r['cost'] = $r['cost'].' <span>руб.</span>';

				$html .= '</div>
					<div class="proddescr">'.$r['descr'].'</div><br/>
					<div class="prodcost">'.$r['cost'].'</div> <a href="##zakaz" alt="Подать заявку на покупку" onclick="return JSWin({\'href\':\''.$_CFG['_HREF']['siteAJAX'].'?_modul=shop&_fn=jsOrder&id='.$r['id'].'\'});">Заказать</a>';
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


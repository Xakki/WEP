<?php
	function tpl_productitems(&$data) {
		$html = '';

		$html = '<div class="prodpage">';
		if(!isset($data) or !count($data)) {
			$html .= 'нету';
		} else {
			foreach($data as $r) {
				$href = $r['rpath'].'/'.$r['path'].'_'.$r['id'].'.html';
				$html .= '<div class="proditem">
					<a href="'.$href.'" class="prodname">'.$r['name'].'</a>
					<div class="prodimg-block">';
				if(count($r['image']) and $r['image'][0][0]) {
					$fimg = array_shift($r['image']);
					$html .= '<a href="'.$fimg[1].'" title="'.$r['name'].'" class="prodimg-first fancyimg" rel="fancy"><img src="'.$fimg[0].'" alt="'.$r['name'].'"/></a>';
					foreach($r['image'] as $img) {
						$html .= '<a href="'.$img[1].'" title="'.$r['name'].'" class="prodimg-over fancyimg" rel="fancy"><img src="'.$img[0].'" alt="'.$r['name'].'"/></a>';
					}
				} else
					$html .= '<img src="_design/default/img/cancel.png" alt="'.$r['name'].'"/>';
				global $_CFG;

				if(!$r['cost'])
					$r['cost'] = '&#160;';
				else
					$r['cost'] = $r['cost'].' <span>руб.</span>';

				$html .= '</div>
					<div class="prodparam">'.$r['descr'].'</div><br/>
					<div class="prodcost">'.$r['cost'].'</div> <a href="##zakaz" alt="Подать заявку на покупку" onclick="return JSWin({\'href\':\''.$_CFG['_HREF']['siteAJAX'].'?_modul=catalog&_fn=jsOrder&id='.$r['id'].'\'});">Заказать</a>
					<div class="prodtext">'.$r['text'].'</div>
				</div>';
			}
		}
		$html .= '</div>';
		return $html;
	}


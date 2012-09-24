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
				} 
				else
					$html .= '<img src="_design/'.$HTML->_design.'/_shop/img/nofoto.gif" alt="'.$r['name'].'" class="prodimg-first"/>';

				$beginHour = 8;
				$endhour = 20;
				$temeLeft = mktime($endhour,0,0,date('m'),date('d'),date('Y')) - time();
				$pp = floor(10*$temeLeft/( ($endhour-$beginHour)*3600 ) )*10;

				$strTime = '';
				if($temeLeft>3600)
				{
					$temp = floor($temeLeft/3600);
					$temeLeft = ($temeLeft-$temp*3600);
					$strTime .= $temp.' час. ';
				}
				$temp = floor($temeLeft/60);
				$temeLeft = ($temeLeft-$temp*60);
				$strTime .= $temp.' мин. ';
				$strTime .= $temeLeft.' сек.';

				$html .= '</div>
				<div class="prodinfo-block">
					<a href="'.$href.'" class="prodname">'.$r['name'].'</a>
					<p class="proddescr">'.$r['descr'].'</p>
					<div class="progress">
						<span>Продано уже '.(100-$pp).'% товаров</span>
						<div class="bar" style="background-position:'.$pp.'% 0;"></div>
						<span>До окончания продаж</span>
						<div class="timer">'.$strTime.'</div>
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
						<div class="prodBlock-button"><a href="'.$href.'">Купить сейчас</a></div>
					</div>
				</div>';
			}
		}
		$html .= '</div>';

		return $html;
	}


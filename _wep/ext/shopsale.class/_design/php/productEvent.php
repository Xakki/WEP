<?php
/**
 * Спецакции 
 * @type Магазин
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
	function tpl_productEvent(&$data) {
		$html = '';

		if(!isset($data['#item#']) or !count($data['#item#'])) 
		{
			$html = $data['#text#']; // Товаров дня нет
		} 
		else {
			global $_CFG, $_tpl,$HTML;
			$_tpl['styles']['/_shop/style/shopEvents'] = 1;
			//$_tpl['script']['/_shop/script/shopEvents'] = 1;

			$html = '<div class="productEvent">
				<h3>'.$data['#title#'].'</h3>';

			$r = $data['#item#'];

			$href = $data['#page#'].'/'.$r['rpath'].'/'.$r['path'].'_'.$r['id'].'.html';
			$html .= '<div class="proditem">
				<div class="prodimg-block">';

			if(isset($r['image']) and count($r['image']) and $r['image'][0][0]) {
				$fimg = array_shift($r['image']);
				$html .= '<a href="/'.$fimg[0].'" title="'.$r['name'].'" class="prodimg-first fancyimg" rel="fancy"><img src="/'.$fimg[1].'" alt="'.$r['name'].'"/></a>';
			} 
			else
				$html .= '<img src="/'.$_CFG['PATH']['themes'].$HTML->_design.'/_shop/img/nofoto.gif" alt="'.$r['name'].'" class="prodimg-first"/>';

			/*$beginHour = 8;
			$endhour = 20;
			$temeLeft = mktime($endhour,0,0,date('m'),date('d'),date('Y')) - time();
			$pp = floor(10*$temeLeft/( ($endhour-$beginHour)*3600 ) )*10;*/
			$temeLeft = ($r['sale']['periode']-time() );
			$pp = floor(10*$temeLeft/($r['sale']['periode']-$r['sale']['periods'] ) )*10;

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
			$html .= '
				<div class="prodBlock">
					<div class="prodBlock-price">
						<p>Цена: <span class="cost">'.$r['cost'].'</span></p>
						'.(isset($r['sale']['name'])?'<p class="sale ico"><i></i>'.$r['sale']['name'].'</p>':'').'
						'.($data['#shopconfig#']['available']?'<p class="available ico"><i></i>'.$r['#available#'].'</p>':'').'
					</div>
					<div class="prodBlock-button"><a href="'.$href.'">Купить сейчас</a></div>
				</div>
			</div>';

		}
		$html .= '</div>';

		return $html;
	}


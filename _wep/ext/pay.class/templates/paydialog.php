<?php

function tpl_paydialog($data) {
	$html = '';
	$t = 'error';
	if(isset($data['summ']))
		$html .= '<h2>Оплата услуги стоимостью '.$data['summ'].' '.$data['m'].'</h2>';

	if($data['flag']==-1 and ($data['balance']-$data['summ'])<0) {
		$html .= '<div class="alert">Ваш баланс '.round($data['balance'],2).' '.$data['m'].'</div>
		<div class="'.$t.'">'.$data['mess'].'</div>
		<form method="post"><div class="form">';
		$d = abs($data['balance']-$data['summ']);
		$max = 2000;
		if($data['balance']>=0) {
			if($d<=$max) {
				foreach($data['#post#'] as $k=>$r) {
					if($k!='code')
						$html .= '<input type="hidden" value="'.$r.'" name="'.$k.'"/>';
				}
				$html .= '<div class="offlinepay"><input type="submit" value="Оплата при получении наличными '.$d.' '.$data['m'].'" title="При получении заказа, максимум '.$max.' '.$data['m'].'" name="'.$data['code'].'" class="acept"/><br/>Максимальная сумма к оплате наличными '.$max.' '.$data['m'].'</div><br/>';
			}
			else
				$html .= '<div class="offlinepay"><input type="submit" value="Оплата при получении наличными '.$d.' '.$data['m'].'" title="При получении заказа, максимум '.$max.' '.$data['m'].'" disabled="disabled"/><br/>Вы не можете оплатить наличными так как сумма вашего заказа превышает  '.$max.' '.$data['m'].'</div><br/>';
		}

		if($data['#pagemoney#']) 
			$html .= '<input type="submit" value="Пополнить счёт на '.$d.' '.$data['m'].'" onclick="location.href=\''.$data['#pagemoney#'].'?summ='.$d.'\';return false;" class="onlinepay"/>
			<div>Для оплаты онлайн пополните счет</div>';
		$html .= '<br/><input type="submit" value="Вернуться" onclick="location.href=\''.$_SERVER['HTTP_REFERER'].'\';return false;" class="cancel"/>
		</div></form>
		';
	}
	elseif($data['flag']==-1) {
		$html .= '<div>На вашем счету '.round($data['balance'],2).' '.$data['m'].'</div>';
		$html .= '<div>после оплаты останется '.round(($data['balance']-$data['summ']),2).' '.$data['m'].'</div>';
		$html .= '<div class="ok">'.$data['mess'].'</div>
		<form method="post"><div class="form">';
		foreach($data['#post#'] as $k=>$r) {
			if($k!='code')
				$html .= '<input type="hidden" value="'.$r.'" name="'.$k.'"/>';
		}
		$html .= '<input type="submit" value="Оплатить" class="acept" name="'.$data['code'].'"/>';
		$html .= '<input type="submit" value="Вернуться" onclick="location.href=\''.$_SERVER['HTTP_REFERER'].'\';return false;" class="cancel"/>
		</div></form>
		';
	}
	elseif($data['flag']==0) {
		$html .= '<h2 class="messhead error">Ошибка</h2>';
		$html .= '<div class="'.$t.'">'.$data['mess'].'</div>
		<form method="post"><div class="form">';
		$html .= '<input type="submit" value="Вернуться" onclick="location.href=\''.$_SERVER['HTTP_REFERER'].'\';return false;" class="cancel"/>
		</div></form>
		';
	}
	elseif($data['flag']==1) {
		$html .= '<div>Ваш баланс: '.round(($data['balance']-$data['summ']),2).' '.$data['m'].'</div>';
		$html .= '<h2 class="messhead">Оплата прошла успешно</h2>';
		$html .= '<div class="ok">'.$data['mess'].'</div>
		<form method="post"><div class="form">';
		$html .= '<input type="submit" value="Вернуться" onclick="location.href=\''.$_SERVER['HTTP_REFERER'].'\';return false;" class="acept"/>
		</div></form>
		';
	}
	return $html;
}

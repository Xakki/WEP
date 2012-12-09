<?php
/**
 * Форма оплаты счета
 * @type Платежная система
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
function tpl_billing($data)
{
	global $_tpl, $HTML;
	//$_CFG['fileIncludeOption']['form'] = 1;
	$_tpl['styles']['/_pay/pay'] = 1;
	$html = '<div class="payselect">';

	if(isset($data['#title#']) and $data['#title#'])
		$html .= '<h2>'.$data['#title#'].'</h2>';

	if($data['#resFlag#']===-1)
		$data['messages'][] = array('error', 'Ошибка! У вас не достаточно прав доступа, для просмотра этого счета!');

	if($data['#resFlag#']===-2)
		$data['messages'][] = array('error', 'Ошибка! Не коректно введенные данные!');

	if($data['#resFlag#']===-3)
		$data['messages'][] = array('error', 'Произошла ошибка! Обратитесь к администрации за помощью!');

	if(isset($data['comm']) and $data['comm'])
		$data['messages'][] = array('alert', $data['comm']);

	if(isset($data['summ']) and $data['summ'])
		$data['messages'][] = array('alert', 'Сумма - '.$data['summ'].' '.$data['#currency#']);

	if(isset($data['#status#']) and $data['#status#'])
		$data['messages'][] = array('alert', 'Статус - '.$data['#status#']);

	if(isset($data['#payLink#']) and $data['#payLink#'])
		$data['messages'][] = array('payLink', '<a href="'.$data['#payLink#'].'" target="_blank">Оплатить</a>');

	if(isset($data['messages']) and count($data['messages'])) {
		$html .= $HTML->transformPHP($data['messages'], '#pg#messages');
	}

	// выводим форму выбора плат системы ии форму для выбранной плат системы
	if(isset($data['child']) or isset($data['form'])) //#resFlag#==0
	{
		// AJAX forma
		$ID = 'form_paymethod';
		$action = '';
		if(isset($data['form']['_*features*_']['action']))
			$action = $data['form']['_*features*_']['action'];
		else
			$_tpl['onload'] .= 'wep.form.ajaxForm(\'#'.$ID.'\','.$data['#contentID#'].');';

		$html .= '<form action="'.$action.'" enctype="multipart/form-data" method="post" id="'.$ID.'">';
		foreach($_POST as $k=>$r) {
			if(!is_array($r))
				$html .= '<input type="hidden" value="'.$r.'" name="'.$k.'">';
			else {
				foreach($r as $ki=>$i)
					$html .= '<input type="hidden" value="'.$i.'" name="'.$k.'['.$ki.']">';
			}
		}

		if(isset($data['child'])) {
			$html .= '<div class="paymethod">';
			foreach($data['child'] as $r) 
			{
				/*if(isset($r['_button']))
					$html .= '<span>'.$r['_button'].'</span>';
				else*/
				$html .= '<input class="pay-'.$r['_cl'].'" type="submit" value="'.$r['_cl'].'" name="paymethod" title="'.$r['caption'].'">';

			}
			$html .= '</div>';
		}
		elseif(isset($data['form'])) 
		{
			$html .= '<div class="divform">';
			unset($data['form']['_info']);
			$html .= $HTML->transformPHP($data['form'], '#pg#form');
			$html .= '</div>';
		}
		else 
		{
			$html .= '';
		}
		$html .= '</form>';
	}
	// Вывводим статус платежа
	else
	{
		$html .= '<br/><div class="paySpanMess" onclick="window.location.reload();">Обновите страницу, чтобы узнать состояния счёта.</div>';

	}

	print_r('<pre>+++++++++++');print_r($data);

	if(isset($data['#foot#']))
		$html .= '<div class="payselect-foot">'.$data['#foot#'].'</div>';
	$html .= '</div>';

	return $html;
}

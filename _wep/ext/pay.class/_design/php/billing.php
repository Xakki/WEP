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

	$messages = array(
		array('ok', $data['comm']),
		array('ok', 'Сумма - '.$data['summ'].' '.$data['#currency#']),
	);

	$html = '<div class="payselect" style="width:340px;margin:10px auto;">';
	if($data['#title#'])
		$html .= '<h2>'.$data['#title#'].'</h2>';
	// выводим форму выбора плат системы ии форму для выбранной плат системы
	if(isset($data['child']) or isset($data['form'])) 
	{
		$html .= $HTML->transformPHP($messages, '#pg#messages');
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

		if(isset($data['messages'])) {
			$html .= $HTML->transformPHP($data['messages'], '#pg#messages');
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

		$html .= '<h1><a id="gotopay" href="'.$data['#payLink#'].'" target="_blank">Оплатить</a></h1>';

		$messages[] = array('ok', 'Статус - '.$data['#status#']);
		$html .= $HTML->transformPHP($messages, '#pg#messages');
		$html .= '<br/><div class="paySpanMess" onclick="window.location.reload();">Обновите страницу, чтобы узнать состояния счёта.</div>';
		//$_tpl['onload'] .= '$("#gotopay").click();';
	}

	if(isset($data['#foot#']))
		$html .= '<div class="payselect-foot">'.$data['#foot#'].'</div>';
	$html .= '</div>';

	return $html;
}

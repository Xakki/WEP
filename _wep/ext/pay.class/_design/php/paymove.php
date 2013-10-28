<?php
/**
 * OLD - Перевод баланса
 * @type Платежная система
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */

function tpl_paymove($data)
{
	$html = '';
	if ($data['#title#'])
		$html .= '<h3>' . $data['#title#'] . '</h3>';
	if (isset($data['#pay#']['respost'])) {
		if ($data['#pay#']['respost']['flag'] == 1) {
			//global $PGLIST;
			//$PGLIST->pageinfo['template'] = 'waction';
			//$html = transformPHP($DATA['formcreat'],'messages');
			if (isset($_POST['plus']))
				$res = 'Счёт пользователя `' . $data['#pay#']['users'][$_POST['users']]['name'] . '` пополнен!';
			else
				$res = 'Со счёта пользователя успешно сняты средства.';
			$html .= '<div class="messages"><div class="ok">' . $res . '</div></div>';
		} else {
			if ($data['#pay#']['respost']['mess'])
				$res = $data['#pay#']['respost']['mess'];
			else
				$res = 'Ошибка!';
			$html .= '<div class="messages"><div class="error">' . $res . '</div></div>';
		}
	}

	$html .= '<form method="POST">';
	if (isset($data['#pay#']) and count($data['#pay#'])) {
		$html .= '<div>Клиент</div>
			<select name="users"><option value=""> -- </option>';
		foreach ($data['#pay#']['users'] as $k => $r)
			$html .= '<option value="' . $k . '" ' . ((isset($_POST['users']) and $k == $_POST['users']) ? 'selected="selected"' : '') . '>' . $r['gname'] . ' - ' . $r['id'] . ' ' . $r['name'] . '[' . (int)$r['balance'] . 'руб.]</option>';
		$html .= '</select>';
	}
	$html .= '<div>Сумма</div> <input type="text" value="0" name="pay"/>
		<div>Коментарий</div> <input type="text" value="" name="name"/>
		 <input type="hidden" value="' . $data['#id#'] . '" name="paymove"/>
		<div>
			<input type="submit" value="Пополнить счет" name="plus">
			<input type="submit" value="Снять со счёта" name="minus">
		</div>
		</form>';

	return $html;
}

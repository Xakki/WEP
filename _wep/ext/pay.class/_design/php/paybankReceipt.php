<?php
/**
 * Счет оплаты в банке - квитанция
 * @type Платежная система
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */

function tpl_paybankReceipt($data)
{
$rub = (int)$data['#item#']['amount'];
$kop = ($data['#item#']['amount']-$rub)/100;
$kop = ceil($kop);
if($kop<10) $kop = '0'.$kop;
$kop = '<span class="decor">&nbsp;'.$kop.'&nbsp;</span>';
$rub = '<span class="decor">&nbsp;'.$rub.'&nbsp;</span>';

$blankHtml = '
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td align="right"><small><i>Форма № ПД-4</i></small></td>
			</tr>

			<tr>
				<td align="center" style="border-bottom:1pt solid #000000;">'.$data['#config#']['bank_namefirm'].'</td>
			</tr>
			<tr>
				<td align="center"><small>(наименование получателя платежа)</small></td>
			</tr>
		</tbody></table>

		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody>
			<tr>
				<td align="center" style="width:37mm; border-bottom:1pt solid #000000;">'.$data['#config#']['bank_INN'].'/'.$data['#config#']['bank_KPP'].'</td>
				<td style="width:9mm;">&nbsp;</td>
				<td align="center" style="border-bottom:1pt solid #000000;">№ '.$data['#config#']['bank_nomer'].'</td>
			</tr>
			<tr>

				<td align="center"><small>(ИНН получателя платежа)</small></td>
				<td><small>&nbsp;</small></td>
				<td align="center"><small>(номер счета получателя платежа)</small></td>
			</tr>
		</tbody></table>

		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody>
			<tr>
				<td  style="width:3mm;">в&nbsp;</td>
				<td align="center" style="width:73mm; border-bottom:1pt solid #000000;">'.$data['#config#']['bank_namebank'].'</td>
			</tr>
			<tr>

				<td></td>
				<td align="center"><small>(наименование банка получателя платежа)</small></td>
			</tr>
		</tbody></table>

		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody>
			<tr>
				<td align="center" style="width:37mm; border-bottom:1pt solid #000000;">БИК&nbsp;&nbsp;'.$data['#config#']['bank_BIK'].'</td>
				<td style="width:9mm;">&nbsp;</td>
				<td align="center" style="border-bottom:1pt solid #000000;">№ '.$data['#config#']['bank_KC'].'</td>
			</tr>
			<tr>

				<td align="center">&nbsp;</td>
				<td>&nbsp;</td>
				<td align="center"><small>(номер кор./сч. банка получателя платежа)</small></td>
			</tr>
		</tbody></table>

		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td align="center" style="border-bottom:1pt solid #000000;">Оплата заказа № '.$data['#config#']['bank_prefix'].$data['#item#']['owner_id'].' от '.static_main::_date('d F Y', $data['#item#']['mf_timecr']).' г.</td>
			</tr>                                         
			<tr>
				<td align="center"><small>(наименование платежа)</small></td>
			</tr>

		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td width="1%" nowrap="">Ф.И.О. плательщика&nbsp;&nbsp;</td>
				<td align="center" width="100%" style="border-bottom:1pt solid #000000;">'.$data['#item#']['fio'].'</td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">

			<tbody><tr>
				<td width="1%" nowrap="">Адрес плательщика&nbsp;&nbsp;</td>
				<td align="center" width="100%" style="border-bottom:1pt solid #000000;">'.$data['#item#']['address'].'</td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td>Сумма платежа '.$rub.' руб. '.$kop.' коп., без НДС</td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td>Итого&nbsp;&nbsp;'.$rub.' руб. '.$kop.' коп.</td>
				<td align="right">&nbsp;&nbsp;«______»____________ '.date('Y').' г.</td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td><small>С условиями приема указанной в платежном документе суммы, 
				в т.ч. с суммой взимаемой платы за услуги банка, ознакомлен и согласен.&nbsp;&nbsp;&nbsp;<b>Подпись плательщика _________________</b></small><br/><br/></td>
			</tr>

		</tbody></table>
	</td>
';

if($data['#config#']['bank_info'])
	$infoText = $data['#config#']['bank_info'];
else
	$infoText = '
	<br>
	<h1>Внимание! Ваш банк может взимать комиссию.</h1>

	<!-- Условия поставки -->
	<h1><b>Метод оплаты:</b></h1>
	<ol>
	  <li>Распечатайте квитанцию. Если у вас нет принтера, перепишите верхнюю часть квитанции и заполните по этому образцу стандартный бланк квитанции в вашем банке.</li>
	  <li>Вырежьте по контуру квитанцию.</li>
	  <li>Оплатите квитанцию в любом отделении банка, принимающего платежи от частных лиц. <span style="color:red"><b>*</b></span></li>
	  <li>Сохраните квитанцию до подтверждения исполнения заказа.</li>
	  <li>Срок резерва товара - 5 дней.</li>
	</ol>

	<h1><b>Условия поставки:</b> </h1>
	<ul>
	  <li>Отгрузка оплаченного товара производится после подтверждения факта платежа.</li>
	  <li>Идентификация платежа производится по квитанции, поступившей в наш банк.</li>
	</ul>


	<p><b>Примечание:</b>
	'.$data['#config#']['bank_namefirm'].'  не может гарантировать конкретные сроки проведения вашего платежа. За дополнительной информацией о сроках поступления денежных средств в банк получателя, обращайтесь в свой банк.</p>
	  
	<p>
		<b>
			<span style="color:red">*</span> Помните, что банки за проведение платежа взимают небольшую комиссию
		</b>
	</p>
';


$html = '
<style>
	table {
		font-size:4mm;
	}
	small {
		font-size:3mm;
	}
	.decor {
		text-decoration: underline;
	}

	@media print {
		.infotext {
			display:none;
		}
	}
</style>

<table border="0" cellspacing="0" cellpadding="0" style="width:180mm; height:145mm;page-break-after: always;">
<tbody>
	<tr valign="top">
		<td style="width:50mm; height:70mm; border:1pt solid #000000; border-bottom:none; border-right:none;" align="center">
			<b>Извещение</b><br>
			<font style="font-size:53mm">&nbsp;<br></font>
			<b>Кассир</b>
		</td>

		<td style="border:1pt solid #000000; border-bottom:none;" align="center">
			'.$blankHtml.'
		</td>
	</tr>


	<tr valign="top">
		<td style="width:50mm; height:70mm; border:1pt solid #000000; border-right:none;" align="center">
			<b>Извещение</b><br>
			<font style="font-size:53mm">&nbsp;<br></font>
			<b>Кассир</b>
		</td>

		<td style="border:1pt solid #000000; " align="center">
			'.$blankHtml.'
		</td>
	</tr>
</tbody></table>

<div class="infotext">
	'.$infoText.'
</div>
	';

	return $html;
}

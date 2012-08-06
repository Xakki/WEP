<?php

function tpl_paybankReceipt($data)
{
	$html = '

<table border="0" cellspacing="0" cellpadding="0" style="width:180mm; height:145mm;">
<tbody><tr valign="top">
	<td style="width:50mm; height:70mm; border:1pt solid #000000; border-bottom:none; border-right:none;" align="center">
	<b>Извещение</b><br>

	<font style="font-size:53mm">&nbsp;<br></font>
	<b>Кассир</b>
	</td>
	<td style="border:1pt solid #000000; border-bottom:none;" align="center">
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td align="right"><small><i>Форма № ПД-4</i></small></td>
			</tr>

			<tr>
				<td style="border-bottom:1pt solid #000000;">ООО "ВИПМОДА"</td>
			</tr>
			<tr>
				<td align="center"><small>(наименование получателя платежа)</small></td>
			</tr>
		</tbody></table>

		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td style="width:37mm; border-bottom:1pt solid #000000;">5030065759/503001001</td>
				<td style="width:9mm;">&nbsp;</td>
				<td style="border-bottom:1pt solid #000000;">40702810011010000599</td>
			</tr>
			<tr>

				<td align="center"><small>(ИНН получателя платежа)</small></td>
				<td><small>&nbsp;</small></td>
				<td align="center"><small>(номер счета получателя платежа)</small></td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td>в&nbsp;</td>

				<td style="width:73mm; border-bottom:1pt solid #000000;">Центральный филиал АБ "РОССИЯ" П.ГАЗОПРОВОД МОСКОВСКОЙ ОБЛ.</td>
				<td align="right">БИК&nbsp;&nbsp;</td>
				<td style="width:33mm; border-bottom:1pt solid #000000;">044599132</td>
			</tr>
			<tr>

				<td></td>
				<td align="center"><small>(наименование банка получателя платежа)</small></td>
				<td></td>
				<td></td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>

				<td width="1%" nowrap="">Номер кор./сч. банка получателя платежа&nbsp;&nbsp;</td>
				<td width="100%" style="border-bottom:1pt solid #000000;">30101810400000000132</td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td style="border-bottom:1pt solid #000000;">Оплата заказа №</td>

				<td style=" font-weight: bold; font-size: 90%;">без НДС</td>
			</tr>                                         
			<tr>
				<td align="center" colspan="2"><small>(наименование платежа)</small></td>
			</tr>

		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td width="1%" nowrap="">Ф.И.О. плательщика&nbsp;&nbsp;</td>
				<td width="100%" style="border-bottom:1pt solid #000000;">&nbsp;</td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">

			<tbody><tr>
				<td width="1%" nowrap="">Адрес плательщика&nbsp;&nbsp;</td>
				<td width="100%" style="border-bottom:1pt solid #000000;">&nbsp;</td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td>Сумма платежа&nbsp;_____________&nbsp;руб.&nbsp;__&nbsp;коп., без&nbsp;НДС</td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td>Итого&nbsp;&nbsp;_____________&nbsp;руб.&nbsp;____&nbsp;коп.</td>
				<td align="right">&nbsp;&nbsp;«______»________________ 200____ г.</td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td><small>С условиями приема указанной в платежном документе суммы, 
				в т.ч. с суммой взимаемой платы за услуги банка, ознакомлен и согласен.</small></td>
			</tr>

		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td align="right"><b>Подпись плательщика _____________________</b></td>
			</tr>
		</tbody></table>
	</td>
</tr>


<tr valign="top">
	<td style="width:50mm; height:70mm; border:1pt solid #000000; border-right:none;" align="center">
	<b>Извещение</b><br>
	<font style="font-size:53mm">&nbsp;<br></font>
	<b>Кассир</b>
	</td>
	<td style="border:1pt solid #000000;" align="center">

		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td align="right"><small><i>Форма № ПД-4</i></small></td>
			</tr>
			<tr>
				<td style="border-bottom:1pt solid #000000;">ООО "ВИПМОДА"</td>
			</tr>

			<tr>
				<td align="center"><small>(наименование получателя платежа)</small></td>
			</tr>
		</tbody></table>

		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td style="width:37mm; border-bottom:1pt solid #000000;">5030065759/503001001</td>

				<td style="width:9mm;">&nbsp;</td>
				<td style="border-bottom:1pt solid #000000;">40702810011010000599</td>
			</tr>
			<tr>
				<td align="center"><small>(ИНН получателя платежа)</small></td>
				<td><small>&nbsp;</small></td>
				<td align="center"><small>(номер счета получателя платежа)</small></td>

			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td>в&nbsp;</td>
				<td style="width:73mm; border-bottom:1pt solid #000000;">Центральный филиал АБ "РОССИЯ" П.ГАЗОПРОВОД МОСКОВСКОЙ ОБЛ.</td>

				<td align="right">БИК&nbsp;&nbsp;</td>
				<td style="width:33mm; border-bottom:1pt solid #000000;">044599132</td>
			</tr>
			<tr>
				<td></td>
				<td align="center"><small>(наименование банка получателя платежа)</small></td>
				<td></td>

				<td></td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td width="1%" nowrap="">Номер кор./сч. банка получателя платежа&nbsp;&nbsp;</td>
				<td width="100%" style="border-bottom:1pt solid #000000;">30101810400000000132</td>
			</tr>

		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td style="border-bottom:1pt solid #000000;">Оплата заказа №</td>

				<td style="font-weight: bold; font-size: 90%;">без НДС</td>
			</tr>
			<tr>
				<td align="center" colspan="2"><small>(наименование платежа)</small></td>
			</tr>

		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td width="1%" nowrap="">Ф.И.О. плательщика&nbsp;&nbsp;</td>

				<td width="100%" style="border-bottom:1pt solid #000000;">&nbsp;</td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td width="1%" nowrap="">Адрес плательщика&nbsp;&nbsp;</td>
				<td width="100%" style="border-bottom:1pt solid #000000;">&nbsp;</td>

			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td>Сумма платежа&nbsp;<font style="text-decoration:underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font>&nbsp;руб.&nbsp;<font style="text-decoration:underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</font>&nbsp;коп., без НДС</td>
				<td align="right">&nbsp;&nbsp;Сумма платы за услуги&nbsp;&nbsp;____&nbsp;руб.&nbsp;____&nbsp;коп.</td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td>Итого&nbsp;&nbsp;_____________&nbsp;руб.&nbsp;____&nbsp;коп.</td>
				<td align="right">&nbsp;&nbsp;«______»________________ 200____ г.</td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tbody><tr>
				<td><small>С условиями приема указанной в платежном документе суммы, 
				в т.ч. с суммой взимаемой платы за услуги банка, ознакомлен и согласен.</small></td>
			</tr>
		</tbody></table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">

			<tbody><tr>
				<td align="right"><b>Подпись плательщика _____________________</b></td>
			</tr>
		</tbody></table>
	</td>
</tr>
</tbody></table>
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
ООО "ВИПМОДА"  не может гарантировать конкретные сроки проведения вашего платежа. За дополнительной информацией о сроках поступления денежных средств в банк получателя, обращайтесь в свой банк.</p>
  
<p>
	<b>
		<span style="color:red">*</span> Помните, что банки за проведение платежа взимают небольшую комиссию
	</b>
</p>


	';

	return $html;
}

<?php
function costFormat($amount)
{
	$rub = (int)$amount;
	$kop = ($amount-$rub)/100;
	$kop = ceil($kop);
	if($kop<10) $kop = '0'.$kop;
	return $rub.' руб. '.$kop.' коп.';
}

function tpl_paybankBill($data)
{
	$html = '
<style>
body {
	max-width:800px;
}
.schet {
	text-align : center;
	font: 9px Verdana, Geneva, Arial, Helvetica, sans-serif;
	border-collapse: collapse;
	border-top: 1px solid Black;
}
table.schet td {
	height: 20px;
	border: 1px solid Black;
}
.itogo {
	border-top: 2px solid black;
	border-bottom: 2px solid black;
	border-left: none;
	border-right: none;
	width:100%;
	margin:10px 0;
	text-align:right;
}
.itogo td {
	margin:0 10px;
}
.itogo .frst {
	width:80%;
}
</style>

<small>
Внимание! Оплата данного счета означает согласие с условиями постапвки товара. Уведомление об оплате обязательно, в противном случае не гарантируется наличие товара на складе. Срок резервирования товара 7 дней. Товар отпускается по факту прихода денег на р/с Поставщика, самовывозом, при наличии доверенности и паспорта.
</small>
<h4>Внимание! Данный счет может быть оплачен только отдельнысм платежным поручением.</h4>
<p><strong>Поставщик:</strong> '.$data['#config#']['bank_namefirm'].'<br/>
'.$data['#config#']['bank_firmaddress'].'<br/>
'.$data['#config#']['bank_firmcontact'].'<br/>
<strong>Реквизиты поставщика:</strong>
ИНН '.$data['#config#']['bank_INN'].', 
КПП '.$data['#config#']['bank_KPP'].', 
р/с № '.$data['#config#']['bank_nomer'].', <br/>
'.$data['#config#']['bank_namebank'].', <br/>
БИК '.$data['#config#']['bank_BIK'].', <br/>
к/с № '.$data['#config#']['bank_KC'].'
</p>

<h3 align="center">Счет № '.$data['#config#']['bank_prefix'].$data['#item#']['owner_id'].'</h3>
<h3 align="center">От '.static_main::_date('d F Y', $data['#item#']['mf_timecr']).' г.</h3>

<p><strong>Плательщик:</strong> '.$data['#item#']['fio'].', '.$data['#item#']['address'].', '.$data['#item#']['phone'].'</p>

<table width="100%" border="0" cellspacing="1" cellpadding="1" align="center" class="schet">
<tbody><tr>
    <td>№
    <td>Код
    <td>Товар
    <td>Цена
    <td>Кол-во
    <td>Ед.
    <td>Сумма
</tr>';
$json = json_decode($data['#item#']['json_data'],true);
$i =1;

$servis = array();

foreach($json['#list#'] as $v) {
	if($v['count'])
	{
		$html .= '<tr>
		    <td>'.$i.'
		    <td>'.$v['product_id'].'
		    <td align="left">'.$v['product_name'].'
		    <td>'.$v['cost'].'
		    <td>'.$v['count'].'
		    <td>шт.
		    <td>'.($v['cost']*$v['count']).'
		</tr>';
	}
	else
	{
		$servis[] = $v;
	}
	$i++;
}

$html .= '</tbody></table>
<div>
</div>
<table class="itogo" cellspacing="0" cellpadding="0"><tbody>
	';
	foreach ($servis as $value) {
	$html .= '
		<tr>
		    <td class="frst">'.$value['product_name'].':
		    <td>'.costFormat($value['cost']).'
		</tr>';
	}
$html .= '
	<tr>
	    <td class="frst">Итого:
	    <td>'.costFormat($data['#item#']['amount']).'
	</tr>
	<tr>
	    <td class="frst">В том числе НДС
	    <td>0
	</tr>
</tbody></table>

<p>Сумма: '.static_num2rub::run($data['#item#']['amount']).'</p>

<p>Руководитель: _____________/_______________</p>

<p>Бухгалтер: _____________/_______________</p>
	';

	return $html;
}

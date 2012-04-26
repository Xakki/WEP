<?php
$html='';
$_CFG['wep']['_showallinfo'] =0;
$_CFG['wep']['_showerror'] = 0;

require_once($_CFG['_PATH']['core'].'html.php');	/**отправляет header и печатает страничку*/
$_COOKIE[$_CFG['wep']['_showallinfo']] = 0;

header("Content-type: text/xml; charset=utf-8");

if(!_new_class('pg',$PGLIST)) return false;
if(!_new_class('shop',$SHOP)) return false;
$SHOP->simplefCache();
$cat = '';
foreach($SHOP->data2 as $r) {
	$cat .= '
				<category id="'.$r['id'].'"'.($r['parent_id']?' parentId="'.$r['parent_id'].'"':'').'>'.$r['name'].'</category>';
}

$offer = '';
$DATA = $SHOP->childs['product']->qs('*','WHERE active=1');
foreach($DATA as $r) {
	$offer .= '
			<offer id="'.$r['id'].'" type="vendor.model" bid="13" cbid="20" available="'.(!$r['available']?'true':'false').'">
				 <url>'.$_CFG['_HREF']['BH'].$SHOP->data2[$r['shop']]['path'].'/'.$r['path'].'_'.$r['id'].'.html</url>
				 <price>'.$r['cost'].'</price>
				 <currencyId>RUR</currencyId>
				 <categoryId type="Own">'.$r['shop'].'</categoryId>
				 <name>'.htmlspecialchars($r['name'], ENT_QUOTES, $_CFG['wep']['charset']).'</name>
				 <description>'.htmlspecialchars($r['descr'], ENT_QUOTES, $_CFG['wep']['charset']).'</description>';

	if($r['img_product'])
		$offer .= '<picture>'. $_CFG['_HREF']['BH'].$r['img_product']. '</picture>';
	
	/*$offer .= '<delivery>true</delivery>';
	$offer .= '<local_delivery_cost>300</local_delivery_cost>';
	$offer .= '<typePrefix>Принтер</typePrefix>';
	$offer .= '<vendor>НP</vendor>';
	$offer .= '<vendorCode>Q7533A</vendorCode>';
	$offer .= '<model>Color LaserJet 3000</model>';
	$offer .= '<manufacturer_warranty>true</manufacturer_warranty>';
	$offer .= '<country_of_origin>Япония</country_of_origin>';
	//<param name="Максимальный формат">А4</param>
	//<param name="Максимальный формат">А4</param>
	*/
	$offer .= '</offer>';
}


$XML = '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="'.date('Y-m-d h:i').'">
	<shop>
		<name>'.$PGLIST->config['sitename'].'</name>
		<company>'.$PGLIST->config['sitename'].'</company>
		<url>'.$_CFG['_HREF']['BH'].'</url>
		<platform>WEP</platform>
		<version>'.$_CFG['info']['version'].'</version>
		<agency>Xakki</agency>
		<email>'.$_CFG['info']['email'].'</email>

		<currencies>
		<currency id="RUR" rate="1" plus="0"/>
		</currencies>

		<categories>'.$cat.'
		</categories>

		<local_delivery_cost>0</local_delivery_cost>

		<offers>'.$offer.'
		</offers>

	</shop>
</yml_catalog>';

echo $XML;
<?php
	//$keyPG - id страницы
	global $SHOP;
	if(!_new_class('shop',$SHOP)) return false;

	$datamap = array();
	$SHOP->fCache();

	function shopGetMap(&$data,$id,$kPG) {
		global $SHOP;
		$s = array();
		if (isset($data[$id]) and is_array($data[$id]) and count($data[$id]))
			foreach ($data[$id] as $key => $value)
			{
				$s[$key] = $value;
				$s[$key]['href'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$kPG.'/'.$SHOP->data2[$key]['path'].'.html';
				if ($key!=$id and isset($data[$key]) and count($data[$key]) and is_array($data[$key])) {
					$s[$key]['#item#'] = shopGetMap($data,$key,$kPG);
				}
			}

		return $s;
	}

	$datamap = shopGetMap($SHOP->data3,0,$this->getHref($keyPG));
	return $datamap;


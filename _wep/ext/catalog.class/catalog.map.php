<?php
	//$keyPG - id страницы
	global $PRODUCT, $CATALOG, $CITY;
	_new_class('city',$CITY);
	_new_class('product',$PRODUCT);
	_new_class('catalog',$CATALOG);
	$PRODUCT->CATALOG = &$CATALOG;

	$datamap = array();
	if(!$CATALOG->data3) $CATALOG->RubricCache();

	function rubGetMap(&$data,$id,$kPG) {
		global $CATALOG, $CITY;
		$s = array();
		if (isset($data[$id]) and is_array($data[$id]) and count($data[$id]))
			foreach ($data[$id] as $key => $value)
			{
				$s[$key] = $value;
				$s[$key]['href'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$CATALOG->data2[$key]['lname'].'/'.$kPG.'.html';
				if ($key!=$id and count($data[$key]) and is_array($data[$key])) {
					if(!$CITY->id) 
						$s[$key]['hidechild'] =1;
					$s[$key]['#item#'] = rubGetMap($data,$key,$kPG);
				}
			}

		return $s;
	}
	$datamap = rubGetMap($CATALOG->data3,0,$keyPG);
	if(!$CITY->id) 
		$DATA_PG[$keyPG]['hidechild'] =1;
	return $datamap;


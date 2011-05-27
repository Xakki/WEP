<?
	global $CITY;
	if(!$CITY) _new_class('city',$CITY);
	if($CITY->id) 
		$DATA_PG[$keyPG]['hidechild'] =1;
	$datacity = $CITY->cityMap();
	return $datacity;


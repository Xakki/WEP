<?php
global $SITEMAP;
	if(!$SITEMAP) {
		_new_class('city',$CITY);
		$datacity = $CITY->cityMap();
		/*if($CITY->id) 
			$DATA_PG[$keyPG]['name'] = $CITY->name;*/
	}

	return $datacity;


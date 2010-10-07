<?
	global $CITY;

	if(!$CITY) $CITY = new city_class($SQL);
	if(!$CITY->cityPosition())
		return false;
	else
		$PGLIST->pageinfo['path']['index'] = $CITY->name;
	return true;
?>
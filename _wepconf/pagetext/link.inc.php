<?
	global $BANNER;
	if(!$BANNER) $BANNER = new banner_class($SQL);
	$html = $BANNER->gnezdo['top'];
	return '=-'.$html.$BANNER->gnezdo['bottom'].'-=';

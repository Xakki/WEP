<?
	global $BANNER;
	if(!$BANNER) _new_class('banner',$BANNER);
	$html = $BANNER->gnezdo['top'];
	return '=-'.$html.$BANNER->gnezdo['bottom'].'-=';

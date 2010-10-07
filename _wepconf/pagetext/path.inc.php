<?
/*PATH - RSS*/
	global $PGLIST,$CITY;
	$qs=$_SERVER['REQUEST_URI'];
	$qs = substr(strstr($_SERVER['REQUEST_URI'],'.html'),6);
	$rsshref = (($PGLIST->id=='list' and $qs)?str_replace('&','&amp;',$qs).'&amp;':'');
	$rid = (int)$_GET['rubric'];
	if($rsshref=='') $rsshref .= 'rubric='.$rid.'&amp;';
	if($CITY->id) $rsshref .= 'city='.$CITY->id.'&amp;';
	$html = $HTML->transform($PGLIST->get_path(), "path").
		'<a class="pathrightimg rssimg" href="/rss.php?'.$rsshref.'" title="Подписка на RSS"></a>
		<a class=" pathrightimg emailimg" href="subscribe.html?'.$rsshref.'" title="Подписка на Email"></a>';
	//<a href="subscribe.html{req}" style="font-size:12px;color:green;">Подписаться на объвления по заданному поиску</a>
	$_tpl['styles'] .="\n".'<link rel="alternate" href="'.$_CFG['_HREF']['BH'].$rsshref.'" type="application/rss+xml" title="uniRSS"/>'."\n";
/*	PATH - RSS*/
	return $html;
?>
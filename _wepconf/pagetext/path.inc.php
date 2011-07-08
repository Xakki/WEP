<?
/*PATH - RSS*/
	global $PGLIST;
	_new_class('city',$CITY);
	$qs=$_SERVER['REQUEST_URI'];
	$qs = substr(strstr($_SERVER['REQUEST_URI'],'.html'),6);
	$rsshref = (($PGLIST->id=='list' and $qs)?$qs.'&':'');
	if(isset($_GET['rubric']))
		$rid = (int)$_GET['rubric'];
	else
		$rid = 0;
	if($rsshref=='') $rsshref .= 'rubric='.$rid.'&';
	if($CITY->id) $rsshref .= 'city='.$CITY->id.'&';
	$DATA = array('pathPage'=>$PGLIST->get_path());
	$html = $HTML->transformPHP($DATA, "pathPage").
		'<a class="pathrightimg rssimg" href="'.$_CFG['_HREF']['BH'].'rss.php?'.$rsshref.'" title="Подписка на RSS">&#160;</a>
		<a class=" pathrightimg emailimg" href="'.$_CFG['_HREF']['BH'].'subscribe.html?'.$rsshref.'" title="Подписка на Email">&#160;</a>';
	//<a href="subscribe.html{req}" style="font-size:12px;color:green;">Подписаться на объвления по заданному поиску</a>
	$_tpl['styles']['rss.php'] = '<link rel="alternate" href="'.$_CFG['_HREF']['BH'].'rss.php?'.$rsshref.'" type="application/rss+xml" title="uniRSS"/>';
/*	PATH - RSS*/
	return $html;

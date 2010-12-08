<?
	global $RUBRIC,$CITY;
	$html = '<div class="blockhead">'.$CITY->name.'<br/>
	<a class="selectcity" href="city.html" onclick="return JSWin({\'href\':\''.$_CFG['_HREF']['siteJS'].'?_view=city\'})">сменить город</a></div>';
	if(!$RUBRIC) $RUBRIC = new rubric_class($SQL);
	$rid = (int)$_GET['rubric'];
	$RUBRIC->simpleRubricCache();
	$html .= '<div class="blockhead"><a href="http://'.$_SERVER['CITY_HOST'].'/add'.($rid?'_'.$rid:'').'.html">Добавить объявление'.($rid?'<br/>в раздел "'.$RUBRIC->data2[$rid]['name'].'"':'').'</a></div>';

	$html .='<div style="text-align:center;">';
	$DATA = array('menu'=>$PGLIST->getMap(2,1));
	$html .= $HTML->transformPHP($DATA,'menu');
	$html .='</div>';

	return $html;
?>
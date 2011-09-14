<?
	global $RUBRIC,$_tpl;
	_new_class('city',$CITY);
	$tp_bl1 = '<div class="block"><div class="blocktext">';
	$tp_bl2 = '</div></div>';
	$html = $tp_bl1;//<input type="text" value="'.$CITY->name.'">
	$html .= '<div class="blockhead">Выбранный регион<br/>
<form class="searchcity" action="" method="post" name="changecity">
	<div class="form-value ajaxlist">
		<span style="'.($CITY->id?'display: none;':'').'left:auto;font-size: 1.2em;margin:0 0 0 5px;">Вся Россия</span>
		<input type="text" autocomplete="off" class="'.($CITY->id?'accept':'').'" onkeyup="ajaxlist(this,\'cityid\')" onblur="show_hide_label(this,\'cityid\',0)" onfocus="show_hide_label(this,\'cityid\',1)" value="'.($CITY->id?$CITY->name:'').'" name="cityid_2"/>
		<div onblur="chFocusList(1)" onfocus="chFocusList(0)" style="display: none;" id="ajaxlist_cityid"></div>
		<input type="hidden" value="'.$CITY->id.'" name="cityid" onchange="document.forms.changecity.submit();"/>
		<a class="selectcity" href="city.html" onclick="return JSWin({\'href\':\''.$_CFG['_HREF']['siteJS'].'?_view=city\'});">&#160;</a>
	</div>
	<input type="hidden" value="8c16711678f726afe941309172668976" name="hsh_cityid"/>
	<input type="hidden" value="a:4:{s:9:&quot;tablename&quot;;s:4:&quot;city&quot;;s:5:&quot;where&quot;;s:11:&quot;tx.active=1&quot;;s:9:&quot;nameField&quot;;s:72:&quot;IF(tx.region_name_ru!=\'\',concat(tx.name,&quot;, &quot;,tx.region_name_ru),tx.name)&quot;;s:8:&quot;ordfield&quot;;s:40:&quot;tx.parent_id, tx.region_name_ru, tx.name&quot;;}" name="srlz_cityid"/>
</form>
</div>';
	$_tpl['script']['form'] = 1;
	$_tpl['styles']['form'] = 1;

	if($this->id==1) {
		if(!$CITY->id and $CITY->detectcity and count($CITY->detectcity)) {
			$html .= '<div style="text-align:center;font-size:0.8em">Вероятно ваш город <a href="'.$CITY->detectcity['href'].'">'.$CITY->detectcity['name'].'</a></div>';
		}
		if(!$CITY->id) {
			$clause = 'SELECT t1.name,t1.'.($_CFG['site']['rf']?'domen_rf':'domen').' as domen, t1.parent_id,t1.id,sum(t2.cnt) as cnt FROM '.$CITY->tablename.' t1 JOIN countb t2 ON t2.city=t1.id WHERE t1.active=1 and t1.center=1 group by t1.id ORDER BY t1.name';
			$result = $CITY->SQL->execSQL($clause);
			$datacity = array();
			if(!$result->err) {
				while ($row = $result->fetch_array()) {
					//$html .= '<a href="http://'.$row['domen'].'.'.$_SERVER['HTTP_HOST2'].'">'.$row['name'].'('.$row['cnt'].')</a>';
					if(!isset($min)) {
						$min=$max=$row['cnt'];
					}
					if($row['cnt']>$max) $max = $row['cnt'];
					elseif($row['cnt']<$min) $min = $row['cnt'];
					$datacity[] = $row;
				}
				if(count($datacity)) {
					$html .= '<div class="menu22">';
					foreach($datacity as $row)
						$html .= '<a style="font-size:'.(10+round(18*$row['cnt']/$max)).'px;" href="http://'.$row['domen'].'.'.$_SERVER['HTTP_HOST2'].'" title="Всего '.$row['cnt'].' объявлений">'.$row['name'].'</a>';//('.$row['cnt'].')
					$html .= '</div>';
				}
			}
		}
	}
	$html .= $tp_bl2;

	if(!$RUBRIC) _new_class('rubric',$RUBRIC);
	$cntPP = count($_GET['page']);
	if((!isset($_GET['rubric']) or !$_GET['rubric']) and $cntPP>1) {
		$RUBRIC->simpleRubricCache();
		if(isset($RUBRIC->data_path[$_GET['page'][($cntPP-2)]]))
			$rid = $_GET['rubric'] = $RUBRIC->data_path[$_GET['page'][$cntPP-2]];
	}
	if(isset($_GET['rubric']))
		$rid = (int)$_GET['rubric'];
	else
		$rid = 0;
	$RUBRIC->simpleRubricCache();
	$html .= $tp_bl1;
	$html .= '<div class="menu2"><a href="http://'.$_SERVER['CITY_HOST'].'/add'.($rid?'_'.$rid:'').'.html">Подать объявление'.($rid?'<br/>в раздел "'.$RUBRIC->data2[$rid]['name'].'"':'').'</a></div>';
	$html .='<div class="menu2">';
	$DATA = array('#item#'=>$PGLIST->getMap(2,1));
	$DATA = array('menu'=>$DATA);
	$html .= $HTML->transformPHP($DATA,'menu');
	$html .='</div>';
	$html .= $tp_bl2;

	return $html;

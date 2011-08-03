<?
	//Меняем дизайн по погоде
	$m = date('n');
	if($m>10 and $m<4)
		$_tpl['onload'] .= '$.include(\'/_design/default/script/nature.js\',function(){fsnow();});';
	elseif($m>3 and $m<6)
		$_tpl['onload'] .= '$.include(\'/_design/default/script/nature.js\',function(){frain();});';

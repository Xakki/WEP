<?
	if($FUNCPARAM[0] == '') $FUNCPARAM[0] = 1;//- _enum['menu']
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 1;//0 - выводит всё в виде структуры дерева , 1 - выводит все в общем массиве, 2 только начальный уровень от $FUNCPARAM[2]
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = ''; // Показывать меню начиная с уровня ID page {id page, #1 - использовать id первого уровня адреса, #2 итп}
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] = 'menu'; // Шаблон
	if($FUNCPARAM[2] and $FUNCPARAM[2]{0}=='#') {
		$FUNCPARAM[2] = $_GET['page'][(int)substr($FUNCPARAM[2],1)-1];
	}
	$DATA = array('menu'=>$PGLIST->getMap($FUNCPARAM[0],$FUNCPARAM[1],$FUNCPARAM[2]));
	$html .= $HTML->transformPHP($DATA,$FUNCPARAM[3],'menu');

	return $html;

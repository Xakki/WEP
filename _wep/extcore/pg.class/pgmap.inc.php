<?
	$FUNCPARAM = explode('&',$FUNCPARAM);

	$FUNCPARAM[0] = -1;//- _enum['menu']
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 0;//0 - выводит всё в виде структуры дерева , 1 - выводит все в общем массиве, 2 только начальный уровень от $FUNCPARAM[2]
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = ''; // Показывать меню начиная с уровня ID page
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] = 'pgmap'; // Шаблон
	if(!isset($FUNCPARAM[4])) $FUNCPARAM[4] = ''; // CSS

	$html ='<div class="'.$FUNCPARAM[4].'">';
	$DATA = array('pgmap'=>$PGLIST->getMap($FUNCPARAM[0],$FUNCPARAM[1],$FUNCPARAM[2]));
	$html .= $HTML->transformPHP($DATA,$FUNCPARAM[3],'pgmap');
	$html .='</div>';

	return $html;
?>
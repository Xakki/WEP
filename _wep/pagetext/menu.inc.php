<?
	$FUNCPARAM = explode('&',$FUNCPARAM);
	
	if(!$FUNCPARAM[0]) $FUNCPARAM[0] = 1;//- _enum['menu']
	if(!$FUNCPARAM[1]) $FUNCPARAM[1] = 1;//0 выводит всю структуру дерева , 1 только первый уровень
	if(!$FUNCPARAM[2]) $FUNCPARAM[2] = ''; // Показывать меню начиная с уровня ID page
	if(!$FUNCPARAM[3]) $FUNCPARAM[3] = 'menu'; // Шаблон
	if(!$FUNCPARAM[4]) $FUNCPARAM[4] = 'pagemenu'; // CSS

	$html ='<div class="'.$FUNCPARAM[4].'">';
	$DATA = array('menu'=>$PGLIST->getMap($FUNCPARAM[0],$FUNCPARAM[1],$FUNCPARAM[2]));
	$html .= $HTML->transformPHP($DATA,$FUNCPARAM[3],'menu');
	$html .='</div>';

	return $html;
?>
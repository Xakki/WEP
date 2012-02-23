<?php
	if(!$FUNCPARAM[0]) $FUNCPARAM[0] = 'catalogMenu';
	if(!$FUNCPARAM[1]) $FUNCPARAM[1] = 'list';

	$html='';
	_new_class('catalog',$CATALOG);
	$DATA = array($FUNCPARAM[0]=>array('#item#'=>$CATALOG->MainCatalogDisplay(),'pgid'=>$FUNCPARAM[1]));
	$html = $HTML->transformPHP($DATA,$FUNCPARAM[0]);

	return $html;

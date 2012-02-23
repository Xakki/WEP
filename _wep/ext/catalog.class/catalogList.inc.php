<?php
	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) $FUNCPARAM[0] = 'catalog';
	if(!isset($FUNCPARAM[1]) or !$FUNCPARAM[1]) $FUNCPARAM[1] = ''; // rubric
	$filter = array();
	if(!isset($FUNCPARAM[2]) or !$FUNCPARAM[2]) $FUNCPARAM[2] = 0; // rss
	if(!isset($FUNCPARAM[3]) or !$FUNCPARAM[3]) $FUNCPARAM[3] = 't1.mf_timecr'; // сортировка
	if(!isset($FUNCPARAM[4]) or !$FUNCPARAM[4]) $FUNCPARAM[4] = '10'; // LIMIT

	if(!$CATALOG) _new_class('catalog',$CATALOG);

	$CATALOG->simpleCatalogCache();
	$DATA = $CATALOG->childs['product']->fListDisplay($FUNCPARAM[1],$filter,$FUNCPARAM[2],$FUNCPARAM[3],$FUNCPARAM[4]);
	$DATA = array($FUNCPARAM[0]=>$DATA);
	return $HTML->transformPHP($DATA,$FUNCPARAM[0]);
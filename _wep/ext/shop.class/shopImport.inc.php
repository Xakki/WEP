<?php
/**
 * Импорт товаров из XLS
 * @ShowFlexForm false
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */



	if(!_new_class('shop',$SHOP)) return false;

	$DATA = $SHOP->toolsImportXls();

	$html = $HTML->transformPHP($DATA,'#pg#formcreat');

	return $html;

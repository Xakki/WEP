<?php
/**
 * Рубрикатор
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */

	if(!$FUNCPARAM[0]) $FUNCPARAM[0] = '#rubric#rubricMain';
	if(!$FUNCPARAM[1]) $FUNCPARAM[1] = '0';

	// рисуем форму для админки чтобы удобно задавать параметры
	if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон'),
			'1'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'На какую страницу указывать'),
		);
		return $form;
	}

	if(!_new_class('rubric',$RUBRIC)) return false;

	$html='';

	$RUBRIC->fCache();

	if(isset($PGLIST->pageParam[0]) and $PGLIST->pageParam[0] and isset($RUBRIC->data_path[$PGLIST->pageParam[0]]))
		$select = $RUBRIC->data_path[$PGLIST->pageParam[0]];
	else
		$select = 0;

	$DATA = array();
	$DATA['#item#'] = $RUBRIC->fDisplay($select);
	$DATA['#page#'] = $this->getHref($FUNCPARAM[1]);
	$DATA['#title#'] = $Ctitle;

	$html = $HTML->transformPHP($DATA,$FUNCPARAM[0]);

	return $html;

<?php
/**
 * Рубрикатор
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */

if (!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '')
	$FUNCPARAM[0] = '#rubric#rubricMain';
if (!isset($FUNCPARAM[1]))
	$FUNCPARAM[1] = '0';
if (!isset($FUNCPARAM[2]))
	$FUNCPARAM[2] = '0';

	// рисуем форму для админки чтобы удобно задавать параметры
	if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон'),
			'1'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'На какую страницу указывать'),
			'2' => array('type' => 'list', 'listname' => array('class'=>'rubric','is_tree'=>true), 'caption' => 'Начало рубрики'),
		);
		return $form;
	}

	if(!_new_class('rubric',$MODUL)) return false;

	$html='';

	$MODUL->fCache();

	if(isset($PGLIST->pageParam[0]) and $PGLIST->pageParam[0] and isset($MODUL->data_path[$PGLIST->pageParam[0]]))
		$select = $MODUL->data_path[$PGLIST->pageParam[0]];
	else
		$select = 0;

	$DATA = array();
	$DATA['#item#'] = $MODUL->fDisplay($FUNCPARAM[2], $select);
	$DATA['#page#'] = $this->getHref($FUNCPARAM[1]);
	$DATA['#title#'] = $Ctitle;

	$html = $HTML->transformPHP($DATA,$FUNCPARAM[0]);

	return $html;
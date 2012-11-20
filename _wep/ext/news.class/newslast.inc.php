<?php
/**
 * Анонс новостей
 * вывод свежих новостей
 * @ShowFlexForm true
 * @type Новости
 * @ico mixcontent.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

	if(!isset($FUNCPARAM[0])) $FUNCPARAM[0] = '#news#newslast'; // Шаблон
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 0; // Страница с новостями
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = 4; // Лимит
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] = 0; // Категория

	_new_class('news',$NEWS);

	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_enum['newscategory'] = $NEWS->config['category'];

		$form = array(
			'0'=>array('type'=>'list', 'listname'=>array('phptemplates', 'tags'=>'newslast'), 'caption'=>'Шаблон', 'comment'=>$_CFG['lang']['tplComment']),
			'1'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'Основная страница'),
			'2'=>array('type'=>'int', 'caption'=>'Лимит'),
			'3'=>array('type'=>'list','listname'=>'newscategory', 'caption'=>'Категория'),
		);
		return $form;
	}

	$html = '';
	//$FUNCPARAM[0] - limit
	//$FUNCPARAM[1] - php template

	$DATA = $NEWS->fLast($FUNCPARAM[2], array('category'=>$FUNCPARAM[3]));
	$DATA = array(
		'#list#'=>$DATA,
		'#Ctitle#'=>$Ctitle);
	if($FUNCPARAM[2])
		$DATA['#page#'] = $this->getHref($FUNCPARAM[1]);
	$DATA = array($FUNCPARAM[0]=>$DATA);
	$html = $HTML->transformPHP($DATA,$FUNCPARAM[0]);

	return $html;

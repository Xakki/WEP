<?php
/**
 * Новости
 * Список новостей с постраничной навигацией
 * @ShowFlexForm true
 * @type Новости
 * @ico mixcontent.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

	if(!isset($FUNCPARAM[0])) $FUNCPARAM[0] = '#news#news'; // Шаблон
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 5;
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = 3;
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] = '';

	_new_class('news',$NEWS);

	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_enum['newscategory'] = $NEWS->config['category'];

		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
			'1'=>array('type'=>'int','caption'=>'Элементов в списке','comment'=>'для постраничной навигации'),
			'2'=>array('type'=>'int','caption'=>'Ближайшие страницы','comment'=>'для постраничной навигации'),
			'3'=>array('type'=>'list','listname'=>'newscategory', 'caption'=>'Категория'),
		);
		return $form;
	}

	$html = '';
	if(isset($this->pageParam[0]) and $this->pageParam[0]=='tags') {
		// TODO : теги
		$DATA = $NEWS->fItem((int)$this->pageParam[0]);
		$this->pageinfo['path'][] = 'Теги';
	}
	elseif(isset($this->pageParam[0])) {
		$DATA = $NEWS->fItem((int)$this->pageParam[0]);
		$this->pageinfo['path'][] = $DATA[0]['name'];
	} else {
		$NEWS->messages_on_page = $FUNCPARAM[1];
		$NEWS->numlist=$FUNCPARAM[2];
		$DATA = $NEWS->fList(array('category'=>$FUNCPARAM[3]));
	}
	$DATA['#page#'] = $this->getHref();
	$DATA = array($FUNCPARAM[0]=>$DATA);
	$html = $HTML->transformPHP($DATA,$FUNCPARAM[0]);

	return $html;

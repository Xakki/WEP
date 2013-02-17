<?php
/**
 * Пополнение счёта (перевод денег другому пользователю)
 * для админов
 * @ShowFlexForm true
 * @type Pay
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

// Корзина
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '#pay#paymove';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 0;
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = 0;


	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		//$temp = 'ownerlist';
		$this->_enum['typeselp'] = array(
			0=>'Все либо указанные ниже',
			1=>'Запрос подчинённых юзеров',
		);
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
			'1'=>array('type'=>'list','listname'=>'typeselp','caption'=>'Выборка'),
			'2'=>array('type'=>'list','listname'=>array('class'=>'ugroup'),'caption'=>'Только Группа'),
		);
		return $form;
	}
	if($_SESSION['user']['parent_id'])
		return static_main::m('denied');

	_new_class('pay', $PAY);
	$DATA = array();
	$param = array();

	if($FUNCPARAM[1]==1)
		$param['cls'] = ' and t1.parent_id='.$_SESSION['user']['id'];
	
	if($FUNCPARAM[2])
		$param['cls'] = ' and t1.owner_id='.$FUNCPARAM[2];
	if(count($_POST) and $_POST['paymove']==$rowPG['id'])
		$param['POST'] = $_POST;
	$DATA['#pay#'] = $PAY->payMove($param);
	$DATA['#title#'] = $Ctitle;// Заголовок контента
	$DATA['#id#'] = $rowPG['id'];
	$DATA['#pagemenu#'] = $this->getHref();// Адрес тек страницы
	$DATA = array($FUNCPARAM[0]=>$DATA);
	$html .= transformPHP($DATA,$FUNCPARAM[0]);

	return $html;

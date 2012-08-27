<?php
/**
 * Управление заказами
 * @ShowFlexForm true
 * @type Shop
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0])
		$FUNCPARAM[0] = '#shopbasket#basketlist';
	if(!isset($FUNCPARAM[1])) 
		$FUNCPARAM[1] = '';
	if(!isset($FUNCPARAM[2]))
		$FUNCPARAM[2] = '';
	if(!isset($FUNCPARAM[3]))
		$FUNCPARAM[3] = '';

	// рисуем форму для админки чтобы удобно задавать параметры
	if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон "Заказы"', 'mask'=>array('min'=>1)),
			'1'=>array('type'=>'text','caption'=>'Статус'),
			'2'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'Страница каталога', 'mask'=>array('min'=>1)),
			'3'=>array('type'=>'list','listname'=>'ownerlist','caption'=>'Страница пользователей', 'mask'=>array('min'=>1)),
		);
		return $form;
	}

	if(!_new_class('shopbasket',$SHOPBASKET)) return false;
	if(!_new_class('shopdeliver',$SHOPDELIVER)) return false;
	if(!_new_class('pay', $PAY)) return false;
	_new_class('pay', $PAY);

	$_tpl['styles']['../'.$HTML->_design.'/_shop/style/shopBasket'] = 1;
	$_CFG['fileIncludeOption']['form'] = 1;

	$html = '';
	
	$DATA = array('#moder#'=>true);
	$DATA['#page#'] = $Chref;
	$DATA['#curr#'] = $PAY->config['curr'];
	$DATA['#pageCatalog#'] = $this->getHref($FUNCPARAM[2]);
	$DATA['#pageUser#'] = $this->getHref($FUNCPARAM[3]);

	if(isset($this->pageParam[0])) {
		$SHOPBASKET->id = (int)$this->pageParam[0];

		$status = 0;
		if(isset($_POST['status3']))
			$status = 3;
		elseif(isset($_POST['status4']))
			$status = 4;
		elseif(isset($_POST['status5']))
			$status = 5;
		elseif(isset($_POST['status7']))
			$status = 7;

		if($status) {
			if($SHOPBASKET->setStatus($SHOPBASKET->id,$status))
				$DATA['messages'][] = array('ok','Статус успешно изменён!');
			else
				$DATA['messages'][] = array('error','Ошибка смены статуса');
		}

		$DATA['#item#'] = $SHOPBASKET->displayItem($SHOPBASKET->id);
		$this->pageinfo['path'][] = 'Информация о заказе №'.$DATA['#item#']['id'];

		/*if(count($DATA['#item#'])) {
			_new_class('users', $USERS);
			$DATA['#user_list#'] = $USERS->displayList($DATA['#item#'],array('menuitem_cid','creater_id'));
			$list_id = array_keys($DATA['#item#']);
			$DATA['#orderitem_list#'] = $ORDER->childs['orderitem']->displayList(implode(',',$list_id));
			$this->pageinfo['path'][$Chref.'/'.$ORDER->id] = 'Заказ №'.$DATA['#item#'][$ORDER->id]['id'];
		}*/
	}
	else {
		$DATA['#list#'] = $SHOPBASKET->fBasketList($DATA['#moder#'],$FUNCPARAM[1]);
	}

	$html = $HTML->transformPHP($DATA,$FUNCPARAM[0]);


	return $html;
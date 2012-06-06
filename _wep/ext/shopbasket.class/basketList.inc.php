<?php
/**
 * Корзина - оформление заказа
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $html
 */

// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0])
		$FUNCPARAM[0] = '#shopbasket#basketlist';
	if(!isset($FUNCPARAM[1]))
		$FUNCPARAM[1] = '#shopbasket#basketitemlist';
	if(!isset($FUNCPARAM[2]))
		$FUNCPARAM[2] = '';
	if(!isset($FUNCPARAM[3]))
		$FUNCPARAM[3] = '';

	// рисуем форму для админки чтобы удобно задавать параметры
	if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон корзины-заказы', 'mask'=>array('min'=>1)),
			'1' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон корзины-список', 'mask'=>array('min'=>1)),
			'2'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'Страница каталога', 'mask'=>array('min'=>1)),
			'3'=>array('type'=>'list','listname'=>'content','caption'=>'Блок Авторизации', 'mask'=>array('min'=>1)),
		);
		return $form;
	}

	if(!_new_class('shopbasket',$SHOPBASKET)) return false;
	if(!_new_class('shopdeliver',$SHOPDELIVER)) return false;
	if(!_new_class('pay', $PAY)) return false;

	$_tpl['styles']['../'.$HTML->_design.'/_shop/style/shopBasket'] = 1;

	$subMenu = array(
		array('name'=>'Список заказов', 'href'=>$Chref.'.html?basketorder'),
		array('name'=>'Шаг 1: Выбор товаров', 'href'=>$Chref.'.html'),
		array('name'=>'Шаг 2: Подтверждение и оплата'),
		array('name'=>'Завершение'),
	);
	$subK = 1;
	$html = '';

	if(isset($_GET['basketorder']) and $SHOPBASKET->userId()) {
		$subK = 0;
		// STEP 1 
		$DATA = $SHOPBASKET->fBasketList();
		$DATA['#page#'] = $Chref;
		$DATA['#curr#'] = $PAY->config['curr'];
		$html = $HTML->transformPHP($DATA,$FUNCPARAM[0]);
	}
	elseif(isset($_GET['basketpay'])) {
		$subK = 3;
		// Выписывать счёт
		$SHOPBASKET->id = (int)$_GET['basketpay'];
		$BDATA = $SHOPBASKET->_select();
		_new_class('pay', $PAY);
		$_POST['paymethod'] = $BDATA[$SHOPBASKET->id]['paytype'];
		$DATA = $PAY->billingFrom(
			$BDATA[$SHOPBASKET->id]['summ'], // К оплате
			'shop'.$uid, // Ключ
			'Покупка. Заказ №'.$SHOPBASKET->id, // Коммент
			'if(_new_class(\'shopbasket\',$M)){$M->payStatus('.$SHOPBASKET->id.');}', // Исполняемая команда
			$BDATA[$SHOPBASKET->id] // Дополнительные данные (email, phone итп)
		);
		$DATA['#contentID#'] = $PGLIST->contentID;
		$html = $HTML->transformPHP($DATA,'#pay#billing');
	}
	elseif(isset($_GET['typedelivery']) and $SHOPBASKET->getSummOrder()) {
		$subK = 2;
		$uid = $SHOPBASKET->userId();
		if($uid) {
			_new_class('ugroup',$UGROUP);
			if($uid<0) {
				$this->display_inc($FUNCPARAM[3]);
				if($this->pageinfo['template'] != 'waction')
					$_tpl['text'] = '<h3>Для продолжения оформления покупки необходимо авторизоваться</h3><br/>'.$_tpl['text'];
				// Новый пользователь, нужно его зарегить или авторизовать
			} 
			else {
				$DATA = array();
				$DATA['#delivery#'] = $SHOPDELIVER->qs('*','WHERE active=1','id');
				if(isset($DATA['#delivery#'][$_GET['typedelivery']])) {
					$deliveryData = $DATA['#delivery#'][$_GET['typedelivery']];
					//$SHOPBASKET->prm_edit = true;
					$SHOPBASKET->prm_add = true;
					$FORM = $SHOPBASKET->fields_form;
					$FORM['summ']['type'] = 'hidden';
					$FORM['summ']['value'] = $_POST['cost'] = $SHOPBASKET->getSummOrder($deliveryData);
					$FORM['delivertype']['type'] = 'hidden';
					$FORM['delivertype']['value'] = $_POST['delivertype'] = $_GET['typedelivery'];
					$FORM['phone']['value'] = $_SESSION['user']['cf1'];
					$FORM['adress']['value'] = $_SESSION['user']['cf2'];
					$FORM['fio']['value'] = $_SESSION['user']['name'];
					$SHOPBASKET->allowedPay = $deliveryData['paylist'];
					$SHOPBASKET->allowedPay = explode('|',trim($SHOPBASKET->allowedPay,'|'));
					// Убираем ненужные поля
					$norequere = $deliveryData['norequere'];
					$norequere = explode('|',trim($norequere,'|'));
					$FORM = array_diff_key($FORM,array_flip($norequere));

					list($DATA['formcreat'], $this->formFlag) = $SHOPBASKET->_UpdItemModul(array(),$FORM);

					if($SHOPBASKET->id) {
						static_main::redirect($Chref.'.html?basketpay='.$SHOPBASKET->id);
					}
					else {
						unset($DATA['formcreat']['form']['_info']);
						$DATA['formcreat']['form']['sbmt']['value'] = 'Оформить заказ';
						$DATA['formcreat']['messages'][] = static_main::am('alert','Заказа на сумму '.$_POST['cost'].' '.$PAY->config['curr']);
						$DATA['formcreat']['messages'][] = static_main::am('ok','Заполните все необходимые данные');
						$html .= $HTML->transformPHP($DATA,'#pg#formcreat');
					}
					// уже авторизованный пользователь
				} else {
					$html = static_main::m('errdata').static_main::m('feedback');
				}
			}
			
		} 
		else {
			$html = static_main::m('errdata').static_main::m('feedback');
		}
		// STEP 2
	} 
	else {
		// STEP 1 
		$DATA = $SHOPBASKET->fBasketListItem();
		$DATA['#pageCat#'] = $this->getHref($FUNCPARAM[2]);
		$DATA['#page#'] = $this->getHref();
		$DATA['#delivery#'] = $SHOPDELIVER->qs('*','WHERE active=1','id');
		$DATA['#curr#'] = $PAY->config['curr'];
		$html = $HTML->transformPHP($DATA,$FUNCPARAM[1]);
	}

	$subHtml = '<ul class="stepMenu">';
	foreach($subMenu as $k=>$r) {
		$subHtml .= '<li class="'.(isset($r['href'])?'allow':'').($k==$subK?' sel':'').'">';
		if(isset($r['href']) and $r['href'])
			$subHtml .= '<a href="'.$r['href'].'">'.$r['name'].'</a>';
		else
			$subHtml .= $r['name'];
	}
	$subHtml .= '</ul>';

	$_tpl['text'] = $subHtml.$_tpl['text'];

	return $html;

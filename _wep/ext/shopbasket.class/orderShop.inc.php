<?php
/**
 * Корзина - оформление заказа
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
		$FUNCPARAM[1] = '#shopbasket#basketitemlist';
	if(!isset($FUNCPARAM[2]))
		$FUNCPARAM[2] = '';
	if(!isset($FUNCPARAM[3]))
		$FUNCPARAM[3] = '';
	if(!isset($FUNCPARAM[4]))
		$FUNCPARAM[4] = '';

	// рисуем форму для админки чтобы удобно задавать параметры
	if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон корзины-заказы', 'mask'=>array('min'=>1)),
			'1' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон корзины-список', 'mask'=>array('min'=>1)),
			'2'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'Страница каталога', 'mask'=>array('min'=>1)),
			'3'=>array('type'=>'list','listname'=>'content','caption'=>'Блок Авторизации', 'mask'=>array('min'=>1)),
			'4'=>array('type'=>'list','listname'=>'ownerlist','caption'=>'Страница пользователей', 'mask'=>array('min'=>1)),
		);
		return $form;
	}

	if(!_new_class('shopbasket',$SHOPBASKET)) return false;
	if(!_new_class('shopdeliver',$SHOPDELIVER)) return false;
	if(!_new_class('pay', $PAY)) return false;

	$_tpl['styles']['/_shop/style/shopBasket'] = 1;
	$_CFG['fileIncludeOption']['form'] = 1;

	$subMenu = array(
		array('name'=>'Список заказов'),
		array('name'=>'Шаг 1: Выбор товаров', 'href'=>$Chref.'.html'),
		array('name'=>'Шаг 2: Контактные данные'),
		array('name'=>'Шаг 3: Оплата'),
	);

	if(static_main::_prmUserCheck())
		$subMenu[0]['href'] = $Chref.'/orderlist.html';

	$subK = 1;
	$html = '';
	_new_class('pay', $PAY);
	$uid = $SHOPBASKET->userId();
	$DATA = array();

	if(isset($this->pageParam[0]) and $this->pageParam[0]=='orderlist' and static_main::_prmUserCheck()) {
		if(isset($this->pageParam[1])) {
			$SHOPBASKET->id = (int)$this->pageParam[1];
			$DATA['#item#'] = $SHOPBASKET->displayItem($SHOPBASKET->id);
			$DATA['messages'][] = array('info','Информация о заказе №'.$DATA['#item#']['id']);
		}
		else {
			$DATA['#list#'] = $SHOPBASKET->fBasketList();
		}

		$subK = 0;
		// STEP 1 
		$DATA['#orderPage#'] = $Chref;
		$DATA['#page#'] = $Chref.'/orderlist';
		$DATA['#curr#'] = $PAY->config['curr'];
		$DATA['#pageUser#'] = $this->getHref($FUNCPARAM[4]);

		$html = $HTML->transformPHP($DATA,$FUNCPARAM[0]);

	}
	elseif(isset($_GET['basketpay']) and static_main::_prmUserCheck()) 
	{
		// STEP 3
		$subK = 3;
		// Выписывать счёт
		$SHOPBASKET->id = (int)$_GET['basketpay'];
		$IDATA = $SHOPBASKET->fBasketListItem($SHOPBASKET->id);
		if(count($IDATA)) {
			$BDATA = $SHOPBASKET->_select();
			
			$BDATA[$SHOPBASKET->id]['json_data'] = json_encode($IDATA);

			_new_class('pay', $PAY);
			$_POST['paymethod'] = $BDATA[$SHOPBASKET->id]['paytype'];

			$DATA = $PAY->billingFrom(
				$BDATA[$SHOPBASKET->id]['summ'], // К оплате
				$SHOPBASKET->getPayKey(), // Ключ
				'Оформление заказа по счёту №'.$SHOPBASKET->id, // Коммент
				'if(_new_class(\'shopbasket\',$M)){$M->payStatus('.$SHOPBASKET->id.',3);}', // Исполняемая команда
				$BDATA[$SHOPBASKET->id] // Дополнительные данные (email, phone итп)
			);
			$this->formFlag = $DATA['#resFlag#'];
			$DATA['#contentID#'] = $PGLIST->contentID;
			$html = $HTML->transformPHP($DATA,'#pay#billing');

			if(!$BDATA[$SHOPBASKET->id]['pay_id'] and $PAY->id) {
				$SHOPBASKET->_update(array('pay_id'=>$PAY->id));
			}
		} 
		else
		{
			static_main::redirect($Chref.'.html');
		}
	}
	elseif(isset($_GET['typedelivery']) and $SHOPBASKET->getSummOrder()) 
	{
		// STEP 2
		$subK = 2;
		
		if($uid) {
			_new_class('ugroup',$UGROUP);
			if($uid<0) {
				$this->display_inc($FUNCPARAM[3]);
				if($this->pageinfo['template'] != 'waction')
					$_tpl['text'] = '<h3>Для продолжения оформления покупки необходимо авторизоваться</h3><br/>'.$_tpl['text'];
				$uid = $SHOPBASKET->userId();
				if($uid>0)
					static_main::redirect();
				// Новый пользователь, нужно его зарегить или авторизовать
			} 
			else {
				$DATA = array();
				$DATA['#delivery#'] = $SHOPDELIVER->qs('*','WHERE active=1','id');
				if(isset($DATA['#delivery#'][$_GET['typedelivery']])) 
				{
					$deliveryData = $DATA['#delivery#'][$_GET['typedelivery']];
					//$SHOPBASKET->prm_edit = true;
					$SHOPBASKET->prm_add = true;
					$FORM = $SHOPBASKET->fields_form;
					$FORM['summ']['type'] = 'hidden';
					$FORM['summ']['value'] = $_POST['cost'] = $SHOPBASKET->getSummOrder($deliveryData);
					$FORM['delivertype']['type'] = 'hidden';
					$FORM['delivertype']['value'] = $_POST['delivertype'] = $_GET['typedelivery'];
					$FORM['phone']['value'] = $_SESSION['user']['cf1'];
					$FORM['address']['value'] = $_SESSION['user']['cf2'];
					$FORM['fio']['value'] = $_SESSION['user']['name'];
					$deliveryData['paylist'] = trim($deliveryData['paylist'],'|');
					if($deliveryData['paylist'])
						$SHOPBASKET->allowedPay = explode('|',$deliveryData['paylist']);
					// Убираем ненужные поля
					$norequere = $deliveryData['norequere'];
					$norequere = explode('|',trim($norequere,'|'));
					$FORM = array_diff_key($FORM,array_flip($norequere));
					$FORM['paytype']['type'] = 'radio' ;

					$UGROUP->needApplyOfferta($FORM);
	
					list($DATA['formcreat'], $this->formFlag) = $SHOPBASKET->_UpdItemModul(array(),$FORM);

					

					if($SHOPBASKET->id) {
						static_main::redirect($Chref.'.html?basketpay='.$SHOPBASKET->id);
					}
					else {
						unset($DATA['formcreat']['form']['_info']);
						$DATA['formcreat']['form']['sbmt']['value'] = 'Оформить заказ';
						$DATA['formcreat']['messages'][] = static_main::am('alert','Заказ на сумму '.$_POST['cost'].' '.$PAY->config['curr']);
						$DATA['formcreat']['messages'][] = static_main::am('alert','Доставка - '.$deliveryData['name']);
						//$deliveryCost = $SHOPBASKET->orderDeliveryCost();
						//$DATA['formcreat']['messages'][] = static_main::am('alert','Доставка - '.$deliveryData['name'].($deliveryCost>0?', в т.ч. '.$deliveryCost.' '.$PAY->config['curr'].' за доставку':''));
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
		
	} 
	else {
		// STEP 1 
		$DATA = array();
		$DATA['#list#'] = $SHOPBASKET->fBasketListItem();
		$DATA['#pageCat#'] = $this->getHref($FUNCPARAM[2]);
		$DATA['#page#'] = $this->getHref();
		$DATA['#delivery#'] = $SHOPDELIVER->qs('*','WHERE active=1','id');

		$DATA['#curr#'] = $PAY->config['curr'];
		$html = $HTML->transformPHP($DATA,$FUNCPARAM[1]);
	}

	if(!$this->ajaxRequest)
	{
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
	}

	return $html;

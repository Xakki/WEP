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
			'0' => array('type' => 'list', 'listname' => array('phptemplates', 'tags'=>'basketlist'), 'caption' => 'Шаблон корзины-заказы', 'mask'=>array('min'=>1)),
			'1' => array('type' => 'list', 'listname' => array('phptemplates', 'tags'=>'basketitemlist'), 'caption' => 'Шаблон корзины-список', 'mask'=>array('min'=>1)),
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
	$_tpl['styles']['/_pay/pay'] = 1;
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
		$DATA['#pageCatalog#'] = $this->getHref($FUNCPARAM[2]);
		$DATA['#curr#'] = $PAY->config['curr'];
		$DATA['#pageUser#'] = $this->getHref($FUNCPARAM[4]);

		$html = transformPHP($DATA,$FUNCPARAM[0]);

	}
	elseif(isset($_GET['shopBasket']))
	{
		// Редактирование для заказов, если ещё не был выставлен счет

		$SHOPBASKET->prm_edit = true;
		$SHOPBASKET->id = (int)$_GET['shopBasket'];
		$shopbasketData = $SHOPBASKET->_select();
		$DATA = array();

		if(count($shopbasketData) and count($shopbasketData[$SHOPBASKET->id]) and !$shopbasketData[$SHOPBASKET->id]['pay_id'])
		{
			$FORM = array();
			$FORM['fio'] = array('type' => 'text', 'caption' => 'Ваша фамилия и имя', 'mask'=>array('min'=>6));
			$FORM['address'] = array('type' => 'text', 'caption' => 'Адрес доставки', 'mask'=>array('min'=>6));
			$FORM['phone'] = array('type' => 'phone', 'caption' => 'Телефон для связи и оповещения', 'mask'=>array('name'=>'phone3', 'min'=>6));
			$FORM['summ'] = array('type' => 'decimal', 'caption' => 'Сумма', 'readonly'=>1, 'mask'=>array());
			$FORM['delivertype'] = array('type' => 'list', 'listname'=>'delivertype', 'readonly'=>1, 'caption' => 'Тип доставки', 'mask' =>array('min'=>1));
			$FORM['paytype'] = array('type' => 'radio', 'listname' => 'paytype', 'css'=>'paytype', 'caption' => 'Тип платежа', 'mask' =>array('min'=>1));

			$deliveryData = $SHOPDELIVER->qs('*','WHERE active=1 and id='.$shopbasketData[$SHOPBASKET->id]['delivertype'],'id');
			$deliveryData['paylist'] = trim($deliveryData['paylist'],'|');
			if($deliveryData['paylist'])
				$SHOPBASKET->allowedPay = explode('|',$deliveryData['paylist']);
			// Убираем ненужные поля
			$norequere = $deliveryData['norequere'];
			$norequere = explode('|',trim($norequere,'|'));
			$FORM = array_diff_key($FORM,array_flip($norequere));
			$SHOPBASKET->lang['Save and close'] = 'Сохранить и перейти к оплате';
			$SHOPBASKET->lang['update_name'] = 'Редактирование заказа';
			list($DATA['formcreat'], $this->formFlag) = $SHOPBASKET->_UpdItemModul(array(),$FORM);
			if($this->formFlag===1)
			{
				static_main::redirect($Chref.'.html?basketpay='.$SHOPBASKET->id);
			}
		}
		
		if(!isset($DATA['formcreat']))
			$DATA['messages'][] = static_main::am('error','Не верно заданны параметры.');

		$html .= transformPHP($DATA,'#pg#formcreat');
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
			$_POST['pay_modul'] = $BDATA[$SHOPBASKET->id]['paytype'];

			$DATA = $PAY->billingForm(
				array(
					'cost' => $BDATA[$SHOPBASKET->id]['summ'], // К оплате
					'_key' => $SHOPBASKET->getPayKey(), // Ключ
					'name' => 'Оформление заказа по счёту №'.$SHOPBASKET->id , // Коммент
					'_eval' => 'shopbasket:payStatus('.$SHOPBASKET->id.',3)', // Исполняемая команда
					'paylink' => '',
				),
				$BDATA[$SHOPBASKET->id] // Дополнительные данные (email, phone итп)
			);
			$this->formFlag = $DATA['#resFlag#'];

			if($this->formFlag<0)
				$DATA['messages'][] = array('notice','<a href="'.$Chref.'.html?shopBasket='.$SHOPBASKET->id.'">Изменить данные заказа</a>');

			$html = transformPHP($DATA, $DATA['tpl']);

			if(!$BDATA[$SHOPBASKET->id]['pay_id'] and $PAY->id) {
				$SHOPBASKET->_update(array('pay_id'=>$PAY->id));
			}
		} 
		else
		{
			// Заказа с номером № не найден
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
					$SHOPBASKET->prm_add = true;
					$FORM = array();
					$_POST['cost'] = $SHOPBASKET->getSummOrder($deliveryData);
					$FORM['fio'] = array('type' => 'text', 'caption' => 'Ваша фамилия и имя', 'mask'=>array('min'=>6));
					$FORM['address'] = array('type' => 'text', 'caption' => 'Адрес доставки', 'mask'=>array('min'=>6));
					$FORM['phone'] = array('type' => 'phone', 'caption' => 'Телефон для связи и оповещения', 'mask'=>array('name'=>'phone3', 'min'=>6));
					$FORM['summ'] = array('type' => 'hidden', 'readonly'=>1, 'mask'=>array('eval'=>$_POST['cost']));
					$FORM['delivertype'] = array('type' => 'hidden', 'readonly'=>1, 'mask' =>array('min'=>1, 'eval'=>$_GET['typedelivery']));
					$FORM['paytype'] = array('type' => 'radio', 'listname' => 'paytype', 'css'=>'paytype', 'caption' => 'Тип платежа', 'mask' =>array('min'=>1));
					
					$FORM['phone']['value'] = (isset($_COOKIE['phone'])? $_COOKIE['phone'] : $_SESSION['user']['cf1']);
					$FORM['address']['value'] = (isset($_COOKIE['address'])? $_COOKIE['address'] : $_SESSION['user']['cf2']);
					$FORM['fio']['value'] = (isset($_COOKIE['fio'])? $_COOKIE['fio'] : $_SESSION['user']['name']);

					$deliveryData['paylist'] = trim($deliveryData['paylist'],'|');
					if($deliveryData['paylist'])
						$SHOPBASKET->allowedPay = explode('|',$deliveryData['paylist']);
					// Убираем ненужные поля
					$norequere = $deliveryData['norequere'];
					$norequere = explode('|',trim($norequere,'|'));
					$FORM = array_diff_key($FORM,array_flip($norequere));

					$UGROUP->needApplyOfferta($FORM);
	
					list($DATA['formcreat'], $this->formFlag) = $SHOPBASKET->_UpdItemModul(array(),$FORM);

					

					if($SHOPBASKET->id) 
					{
						_setcookie('fio', $_POST['fio']);
						_setcookie('address', $_POST['address']);
						_setcookie('phone', $_POST['phone']);
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

						$html .= transformPHP($DATA,'#pg#formcreat');
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
		$DATA['#pageCatalog#'] = $this->getHref($FUNCPARAM[2]);
		$DATA['#page#'] = $this->getHref();
		$DATA['#delivery#'] = $SHOPDELIVER->qs('*','WHERE active=1','id');

		$DATA['#curr#'] = $PAY->config['curr'];
		$html = transformPHP($DATA,$FUNCPARAM[1]);
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

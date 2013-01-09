<?php
/**
 * Запрос на пополнение баланса
 * Начисление денег на баланс
 * @ShowFlexForm true
 * @type Pay
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '';
	//if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '';


	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content

		$form = array(
			0=>array('type'=>'email','caption'=>'Введите Email получателя, если нужно включить пополнение по запросу')
		);
		return $form;
	}
	$_tpl['styles']['/_pay/pay'] = 1;
	$html = '';


	if(isset($this->pageParam[0])) {
		_new_class('pay', $PAY);
		$_tpl['styles']['form'] = 1;
		if($this->pageParam[0]=='cash' && $FUNCPARAM[0]) 
		{

			$this->pageinfo['path'][$Chref.'/cash'] = 'Заявка на пополнение счета';
			_new_class('mail', $MAIL);
			$res = '';
			if(count($_POST) and $_POST['plus'] and $_POST['pay']) {

			}
			if(!$res) {
				if(!isset($_REQUEST['pay']))
					$_REQUEST['pay'] = '';
				if(!isset($_REQUEST['name']))
					$_REQUEST['name'] = '';

				$formflag = 1;// 0 - показывает форму, 1 - не показывать форму
				$arr = array('mess'=>array(),'vars'=>array());
				$mess = array();
				$param = array();
				$argForm = array(
					'_info' => array('type' => 'info', 'css' => 'caption', 'caption'=>'Заявка на пополнение счёта'),
					'summ' => array('type'=>'int', 'fieldType'=>'int', 'caption'=>'Сумма для пополнения, руб.', 'comment'=>'Минимум 100р', 'mask'=>array('min'=>100)),
					'comment' => array('type'=>'textarea', 'fieldType'=>'text', 'caption'=>'Данные о переводе', 'comment'=>'Укажите подробные данные о переводе когда и как вы перевели средства'),
					'sbmt' => array('type' => 'submit',	'value' => static_main::m('Отправить заявку на пополнение') )
				);
				if(count($_POST) and (isset($_POST['sbmt']) or isset($_POST['sbmt_save']))) 
				{
					$DATA = $_POST;
					$this->kPreFields($DATA,$param,$argForm);
					$arr = $this->fFormCheck($DATA,$param,$argForm);
					$flag=-1;
					if(!count($arr['mess'])) 
					{
						$data = array('from'=>$_SESSION['user']['email'], 'mail_to'=>$FUNCPARAM[0], 'subject'=>'Заявка на пополнение баланса от '.$_SESSION['user']['email']);
						$data['text'] = '<p>Заявка от пользователя '.$_SESSION['user']['name'].'</p>
							<ul>
								<li>ID - '.$_SESSION['user']['id'].'</li>
								<li>ФИО - '.$_SESSION['user']['name'].' '.$_SESSION['user']['io'].'</li>
								<li>Фирма - '.$_SESSION['user']['firma'].'</li>
								<li>Группа - '.$_SESSION['user']['gname'].'</li>
								<li>Email - '.$_SESSION['user']['email'].'</li>
								<li>Текущий баланс - '.round($_SESSION['user']['balance'],2).' руб.</li>
							</ul>
							<p>Заявка на '.(int)$_POST['pay'].' руб.</p>
							<p>Комментарий пользователя: <hr/> <b>'.$_POST['name'].'</b> <hr/> </p>
							<p>Дата заявки '.date('Y-m-d H:i:s').'</p>
							';
						$res = $MAIL->Send($data);

						if($res) 
						{
							//$this->pageinfo['path'][$Chref.'/cash/ok'] = 'Успешно';
							$flag=1;
							$arr['mess'][] = static_main::am('ok','Заявка принята на расмотрение. Вашу заявку рассмотрят в ближайшее время , ответ вы получите на ваш почтовый ящик.');
						} else
							$arr['mess'][] = static_main::am('error','add_err',$_this);

					}
				}
				else 
					$mess = $this->kPreFields($_POST,$param,$argForm);

				$formflag = $this->kFields2Form($param,$argForm);

				$htmlData = array(
					'messages'=>array_merge($mess,$arr['mess']),
					'form'=>($formflag?$argForm:array())
				);
				$html .= $HTML->transformPHP($htmlData,'#pg#formcreat');
			}
		} 
		elseif(isset($PAY->childs[$this->pageParam[0]])) 
		{
			$this->pageinfo['path'][$Chref.'/'.$this->pageParam[0]] = $PAY->childs[$this->pageParam[0]]->caption;
			$comm = 'Пополнение кошелька '.$_SESSION['user']['name'].'['.$_SESSION['user']['email'].'], '.$_CFG['site']['www'];
			list($htmlData,$flag) = $PAY->addMoney($this->pageParam[0],$comm);
			/*if($flag==1)
				$this->pageinfo['path'][$Chref.'/'.$this->pageParam[0].'/ok'] = 'Успешно';*/
			$htmlData = array('formcreat'=>$htmlData);
			$html .= $HTML->transformPHP($htmlData,'#pg#formcreat');

		}
		////
	}
	else {
		$html = '
		<p>Вы можете пополнить свой	счет представленными ниже способами. Внимательно заполните необходимые поля и оплатите в установленный срок (индивидуальный для каждого способа). Вы получите уведомление (на Email или телефон) при изменении статуса счета.</p>
		<div class="paytype">';
		if($FUNCPARAM[0])
			 $html .= '<a class="ico_payusers" href="'.$Chref.'/cash.html" title="Банковский перевод">Перевод наличными</a>';
		_new_class('pay', $PAY);
		if(count($PAY->childs)) {
			if(isset($_GET['summ']) and $_GET['summ'])
				$summ = '?summ='.$_GET['summ'];
			else
				$summ = '';
			foreach($PAY->childs as &$child) {
				$html .= '<a class="ico_'.$child->_cl.'" href="'.$Chref.'/'.$child->_cl.'.html'.$summ.'" title="'.$child->caption.'">'.$child->caption.'</a>';
			}
			unset($child);
		}

		$html .= '</div>';
	}

	return $html;


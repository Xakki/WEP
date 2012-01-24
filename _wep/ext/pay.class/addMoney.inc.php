<?php
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '';


	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content

		$form = array(
			0=>array('type'=>'email','caption'=>'Кому на Email')
		);
		return $form;
	}

	$Chref = $this->getHref();
	$_tpl['styles']['pay'] = '

		div.divform {
			text-align:center;
			margin:0 auto;
			width:300px;
		}
		.divform .form-caption {
			text-align:left;
		}

		.divform .form-value {
		}
		.divform .form-caption input {
			width:50px;
			margin:0 0 0 10px;
			text-align:right;
		}
		.divform .form-value textarea {
			width:300px;
			margin:0 auto;
		}
		.divform .form-value .dscr {
			display:block;
			text-align:justify;
		}

		.payselect {
		}
		.payselect li {
			vertical-align:top;
			text-align: center;
			display:-moz-inline-stack;display:inline-block;_overflow:hidden;*zoom:1;*display:inline;
		}
		.payselect li a {
			font-size:14px;
			height:30px;
			width:130px;
			display:-moz-inline-stack;display:inline-block;_overflow:hidden;*zoom:1;*display:inline;
			border:1px transparent solid;
			background: url(/_wep/ext/pay.class/_design/payuser.png) no-repeat scroll 50% 0 transparent;
			margin:0;
			padding:50px 5px 5px 5px;
			vertical-align:bottom;
			text-align: center;
		}
		.payselect li a:hover {
			border:1px gray solid;
		}
		.payselect li.pay-users a {
		}
		.payselect li.pay-payqiwi a {
			background-image: url(/_wep/ext/pay.class/_design/qiwi/logo.png);
		}
	';

	if(isset($this->pageParam[0])) {
		_new_class('pay', $PAY);

		if($this->pageParam[0]=='cash') {
			$this->pageinfo['path'][$Chref.'/cash'] = 'Заявка на пополнение наличными';
			_new_class('mail', $MAIL);
			$res = '';
			if(count($_POST) and $_POST['plus'] and $_POST['pay']) {
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
				<p>Комментарий пользователя: <b>'.$_POST['name'].'</b></p>
				<p>Дата заявки '.date('Y-m-d H:i:s').'</p>
				';
				$res = $MAIL->Send($data);
				if($res) {
					$html .= '<div class="messages"><div class="ok">Заявка принята на расмотрение. Вашу заявку рассмотрят в ближайшее время , ответ вы получите на ваш почтовый ящик.</div></div>';
					$this->pageinfo['path'][$Chref.'/cash/ok'] = 'Успешно';
				}
			}
			if(!$res) {
				$html .= '<div class="divform">
					<form method="POST">
						<div class="form-caption">Сумма для пополнения <input type="text" value="'.$_REQUEST['pay'].'" name="pay"/> руб.</div>
						<div class="form-caption">Ваш коментарий</div>
						<div class="form-value">
							<textarea name="name">'.$_REQUEST['name'].'</textarea>
							<span class="dscr">Укажите подробные данные о переводе когда и как вы перевели средства</span>
						</div>
						<div class="form-value">
							<input type="submit" value="Отправить заявку на пополнение" name="plus">
						</div>
					</form>
				</div>';
			}
		} 
		elseif(isset($PAY->childs[$this->pageParam[0]])) {
			$this->pageinfo['path'][$Chref.'/'.$this->pageParam[0]] = $PAY->childs[$this->pageParam[0]]->caption;
			$comm = 'Пополнение кошелька '.$_SESSION['user']['name'].'['.$_SESSION['user']['email'].'], '.$_CFG['site']['www'];
			list($htmlData,$flag) = $PAY->addMoney($this->pageParam[0],$comm);
			if($flag==1)
				$this->pageinfo['path'][$Chref.'/'.$this->pageParam[0].'/ok'] = 'Успешно';
			$htmlData = array('formcreat'=>$htmlData);
			$html .= $HTML->transformPHP($htmlData,'formcreat');

		}
		////
	}
	else {
		$html = '<ul class="payselect">
		<li class="pay-users"><a href="'.$Chref.'/cash.html" title="Наличными">Наличными</a></li>';
		_new_class('pay', $PAY);
		if(count($PAY->childs)) {
			foreach($PAY->childs as &$child) {
				$html .= '<li class="pay-'.$child->_cl.'"><a href="'.$Chref.'/'.$child->_cl.'.html" title="'.$child->caption.'">'.$child->caption.'</a></li>';
			}
			unset($child);
		}

		$html .= '</ul>';
	}

	return $html;


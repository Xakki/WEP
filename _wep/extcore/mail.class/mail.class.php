<?
class mail_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->reply=1;
		$this->contenttype= 'text/html';
		$this->uid='';		
		$this->attaches = array();
		$this->caption = 'Почта';
		$this->cf_reinstall = true;
		$this->mf_timecr = true;
		return true;
	}

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();

		$this->_enum['mailengine'] = array(
			0=>'SendMail',
			1=>'phpMailer',
		);
		$this->config['mailengine'] = 0;
		$this->config['mailrobot'] = 'robot@xakki.ru';
		$this->config['fromName'] = '';
		$this->config['PHPMailer_Host'] = 'ssl://smtp.gmail.com:465';
		$this->config['PHPMailer_Username'] = 'usermail@gmail.com';
		$this->config['PHPMailer_Password'] = 'longpassword';
		$this->config['mailtemplate'] = '<html><head><title>%SUBJECT%</title><meta content="text/html;charset=utf-8" http-equiv="Content-Type" /></head><body>%TEXT% %MAILBOTTOM%</body></html>';
		$this->config['mailbottom'] = '<hr/>© 2011 «XAKKI»';

		$this->config_form['mailengine'] = array('type' => 'list', 'listname'=>'mailengine', 'caption' => 'Обработчик почты');
		$this->config_form['mailrobot'] = array('type' => 'text', 'mask' =>array('min'=>1,'name'=>'email'), 'caption' => 'Адрес Робота');
		$this->config_form['fromName'] = array('type' => 'text', 'caption' => 'Имя отправителя (название сайта)');
		$this->config_form['PHPMailer_Host'] = array('type' => 'text', 'caption' => 'PHPMailer_Host', 'mask' =>array('name'=>'all'),'style'=>'background:#30B120;');
		$this->config_form['PHPMailer_Username'] = array('type' => 'text', 'caption' => 'PHPMailer_Username', 'mask' =>array('name'=>'all'),'style'=>'background:#30B120;');
		$this->config_form['PHPMailer_Password'] = array('type' => 'text', 'caption' => 'PHPMailer_Password', 'mask' =>array('name'=>'all'),'style'=>'background:#30B120;');
		$this->config_form['mailtemplate'] = array(
			'type' => 'textarea',
			'caption' => 'Шаблон по умолчанию', 
			'comment' => '%SUBJECT%, %TEXT%, %MAILBOTTOM%');
		$this->config_form['mailbottom'] = array(
			'type' => 'ckedit',
			'caption' => 'Текст прикрепляемый в конце письма', 
			'paramedit'=>array(
				'height'=>350,
				'fullPage'=>'true',
				'toolbarStartupExpanded'=>'false'));
	}

	function _create() {
		parent::_create();

		$this->fields['from'] = array('type' => 'varchar', 'width' =>64, 'attr' => 'NOT NULL');
		$this->fields['subject'] = array('type' => 'varchar', 'width' =>255, 'attr' => 'NOT NULL');
		$this->fields['text'] = array('type' => 'text','attr' => 'NOT NULL');
		$this->fields['from_user'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['mail_to'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['status'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL');

		$this->fields_form['from']= array('type'=>'text','caption'=>'Обратный email адрес','mask'=>array('name'=>'email', 'min' => '4'));
		$this->fields_form['subject']= array('type'=>'text','caption'=>'Тема письма', 'mask'=>array('min' => '4'));
		$this->fields_form['text'] = array(
			'type' => 'ckedit', 
			'caption' => 'Текcт письма', 
			'mask' =>array('name'=>'all','min'=>4,'fview'=>1),
			'paramedit'=>array(
				'height'=>250,
				'toolbarStartupExpanded'=>'false',
				'extraPlugins'=>"'cntlen'",
				'plugins'=>"'button,contextmenu,enterkey,entities,justify,keystrokes,list,pastetext,popup,removeformat,toolbar,undo'"));
		$this->fields_form[$this->mf_createrid] = array(
			'type' => 'list',
			'readonly'=>true,
			'listname'=>array('class'=>'users','nameField'=>'concat(tx.name," [",tx.id,"]")'),
			'caption' => 'От кого', 'mask' => array('usercheck'=>1));
		$this->fields_form['from_user'] = array(
			'type' => 'list', 
			'readonly'=>true,
			'listname'=>array('class'=>'users','nameField'=>'concat(tx.name," [",tx.id,"]")'),
			'caption' => 'Кому', 'mask' => array('usercheck'=>1));
		$this->fields_form['mail_to'] = array('type' => 'text', 'caption' => 'email', 'mask' => array('usercheck'=>1));
		$this->fields_form['mf_timecr'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата создания', 'mask'=>array('usercheck'=>1,'fview'=>2,'sort'=>1));
		$this->fields_form['status'] = array('type' => 'list', 'listname'=>'status', 'caption' => 'Статус сообщения', 'mask' => array('usercheck'=>1));
		
		$this->_enum['status'] = array(
			0 => 'Не отправлялось на email',
			1 => 'Отправлялось на email',
			2 => 'При отправке на email произошли ошибки',
		);

		$this->locallang['default']['_saveclose'] = 'Отправить письмо';
		$this->ordfield = 'mf_timecr DESC';
	}

	function Send($data) {
		$this->__do_hook('Send', $data);

		if(!$data['mail_to']) {
			unset($data['mail_to']);
			$data['status'] = 0;

			$this->fld_data = $data;
			return $this->_add($data);
		}

		if(method_exists($this, 'mailengine'.$this->config['mailengine'])) {
			$send_result = call_user_func(array($this, 'mailengine'.$this->config['mailengine']),$data);
		}
		else {
			trigger_error('Попытка вызвать не существующий метод `mailengine'.$this->config['mailengine'].'` в модуле Mail!', E_USER_ERROR);
			$send_result = false;
		}

		if ($send_result) {
			$data['status'] = 1;
		}
		else {
			$data['status'] = 2;
		}

		$this->_add_item($data);
		return $send_result;
		
	}

	function mailForm($mail_to) {
		global $_MESS;
		$flag=0;// 1 - успешно, 0 - норм, -1  - ошибка
		$formflag = 1;// 0 - показывает форму, 1 - не показывать форму
		$arr = array('mess'=>array(),'vars'=>array());
		$mess = array();

		$param=array('capthaOn'=>1);
		$data = array();

		if(static_main::_prmUserCheck()) {
			$this->fields_form["from"]['default'] = $this->_CFG['userData']['email'];
		}
		else  $mailFrom='';
		if(count($_POST) and $_POST['sbmt']) {
			$this->kPreFields($_POST,$param);
			$arr = $this->fFormCheck($_POST,$param,$this->fields_form);
			$flag=-1;
			if(!count($arr['mess'])) {
				$arr['vars']['mail_to']=$mail_to;
				if($this->Send($arr['vars'])) {
					// иногда сервер говорит что ошибка, а сам всеравно письма отсылает
				} else {
					//$arr['mess'][] = array('name'=>'error', 'value'=>$this->getMess('mailerr'));
					trigger_error('Почта - '.$this->getMess('mailerr'), E_USER_WARNING);
				}
				$flag=1;
				$arr['mess'][] = array('name'=>'ok', 'value'=>$this->getMess('mailok'));
			}
		} else
				$mess = $this->kPreFields($arr['vars'],$param);
		if(isset($this->fields_form['captcha']))
			static_form::setCaptcha();

		if($flag==1)
			$formflag = 0;
		if($formflag) // показывать форму , также если это АЯКС и 
			$formflag = $this->kFields2Form($param);

		static_form::setCaptcha();

		return Array(Array('messages'=>($mess+$arr['mess']), 'form'=>($formflag?$this->form:array())), $flag);

	}

	function mailengine0 ($data) {
		$data['subject'] = substr(htmlspecialchars(trim($data['subject'])), 0, 1000);
		$this->uid = strtoupper(md5(uniqid(time())));
		$subject = '=?utf-8?B?'. base64_encode($data['subject']).'?=';
		$this->config['mailbottom'] = str_replace(array('%host%','%year%'),array($_SERVER['HTTP_HOST'],date('Y')),$this->config['mailbottom']);
		$text = str_replace(array('%SUBJECT%','%TEXT%','%MAILBOTTOM%'),array($data['subject'],trim($data['text']),$this->config['mailbottom']),$this->config['mailtemplate']);
		//$text = substr(trim($data['text']), 0, 1000000).$this->config['mailbottom'] = str_replace('%YEAR%',date('Y'),$this->config['mailbottom']);
		if($data['from']=='')
			$data['from']=$this->config['mailrobot'];
		//if(strlen(ini_get('safe_mode'))< 1){
		@ini_set('sendmail_from', $data['from']);
		@ini_set('sendmail_path', '/usr/sbin/sendmail -t -i -f '.$data['from']);
		 //}
		$header = "MIME-Version: 1.0\r\n";
		$header .= "To: {$data['mail_to']}\r\n";
		$header .= "From: {$data['from']}\r\n";
		$header .= "Bcc: {$data['from']}\r\n"; 
		if($this->reply) $header .= "Reply-To: {$data['from']}\r\n";
		
		if(isset($data['att'])) {
			$header .= "Content-Type: multipart/alternative; boundary={$this->uid}\r\n";
			$header .= "--{$this->uid}\r\n";
		} else {
			$header .= "Content-Type: ".$this->contenttype."; charset=\"utf-8\"\r\n";
		}
		$header .= "Content-Transfer-Encoding: 8bit\r\n";
		$mess = "$text\r\n";
		if(isset($data['att']))
			foreach($data['att'] as $file) {
				$name=basename($file);
				$type="application/octet-stream";
				$content=chunk_split(base64_encode(file_get_contents($file)),76,"\n");
				$header .= "--{$this->uid}\n";
				$header .= "Content-Type: $type; name=\"$name\"\n";
				$header .= "Content-Transfer-Encoding: base64\n";
				$header .= "Content-Disposition: attachment; filename=\"$name\"\n\n";
				$header .= "$content\n";
			}
		if(isset($data['att']))
			$header .= "--{$this->uid}--\r\n";	

		return mail($data['mail_to'], $subject, $mess,$header,'-f'.$data['from']);
	}

	function mailengine1 ($data) {
		include_once(__DIR__.'/phpMailer/class.phpmailer.php');
		if($data['from']=='')
			$data['from']=$this->config['mailrobot'];
		$data['subject'] = substr(htmlspecialchars(trim($data['subject'])), 0, 1000);
		
		$PHPMailer = new PHPMailer();
		$PHPMailer->IsSMTP();
		$PHPMailer->SMTPAuth = true;
		$PHPMailer->CharSet = "utf-8";
		$PHPMailer->Host = $this->config['PHPMailer_Host'];
		$PHPMailer->Username = $this->config['PHPMailer_Username'];
		$PHPMailer->Password = $this->config['PHPMailer_Password'];
		$PHPMailer->SetLanguage('ru');
		$PHPMailer->From = $data['from'];
		if($this->config['fromName'])
			$PHPMailer->FromName =  $this->config['fromName'];//iconv('cp1251','koi8-r','www.apitcomp.ru');
		else
			$PHPMailer->FromName = $_SERVER['HTTP_HOST'];
		$PHPMailer->Subject = $data['subject'];//iconv('cp1251','koi8-r','Новый заказ на кредит');
		
		if(is_array($data['mail_to']))
			foreach ($data['mail_to'] as $email)
			{
				$email = trim($email);
				$PHPMailer->AddAddress($email, "Subscriber");
				//$PHPMailer->AddAddress($email, iconv('cp1251','koi8-r',"Subscriber"));
			}
		else
			$PHPMailer->AddAddress($data['mail_to'], "Subscriber");

		$this->config['mailbottom'] = str_replace(array('%host%','%year%'),array($_SERVER['HTTP_HOST'],date('Y')),$this->config['mailbottom']);
		$PHPMailer->Body = $PHPMailer->AltBody = str_replace(array('%SUBJECT%','%TEXT%','%MAILBOTTOM%'), array($data['subject'],trim($data['text']),$this->config['mailbottom']), $this->config['mailtemplate']);
		//$PHPMailer->Body    = iconv('cp1251','koi8-r//TRANSLIT',$html);
		//$PHPMailer->AltBody = iconv('cp1251','koi8-r//TRANSLIT',$txt);
		if(isset($data['att']))
			foreach($data['att'] as $file) {
				//$type="application/octet-stream";
				$content=chunk_split(base64_encode(),76,"\n");
				$PHPMailer->AddStringAttachment(file_get_contents($file),basename($file));
			}
		if(!$PHPMailer->Send())
		{
			trigger_error($PHPMailer->ErrorInfo, E_USER_WARNING);
			return false;
		}
		return true;
	}

}



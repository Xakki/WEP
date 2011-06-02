<?
class mail_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->reply=1;
		$this->contenttype= 'text/html';
		$this->uid='';		
		$this->attaches = array();
		$this->caption = 'Почта';
		return true;
	}

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();

		$this->config["mailsupport"] = 'xakki@xakki.ru';
		$this->config["mailrobot"] = 'robot@xakki.ru';
		$this->config["mailtemplate"] = '<html><head><title>%SUBJECT%</title><meta content="text/html;charset=utf-8" http-equiv="Content-Type" /></head><body>%TEXT% %MAILBOTTOM%</body></html>';
		$this->config["mailbottom"] = '© 2011 «XAKKI»';

		$this->config_form["mailsupport"] = array("type" => "text", 'mask' =>array('min'=>1,"name"=>'email'), "caption" => "Адрес супорта");
		$this->config_form["mailrobot"] = array("type" => "text", 'mask' =>array('min'=>1,"name"=>'email'), "caption" => "Адрес Робота");
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

		$this->fields_form['from']= array('type'=>'text','caption'=>'Обратный email адрес','mask'=>array('name'=>'email', 'min' => '4'));
		$this->fields_form['subject']= array('type'=>'text','caption'=>'Тема письма', 'mask'=>array('min' => '6'));
		$this->fields_form['text']= array('type'=>'textarea','caption'=>'Текcт письма', 'mask'=>array('min' => '6'));
		
		$this->locallang['default']['_saveclose'] = 'Отправить письмо';
	}

	function Send($data) {
		if(!$data['mailTo']) {
			unset($data['mailTo']);
			$this->fld_data = $data;
			return $this->_add($data);
		}
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
		$header .= "To: {$data['mailTo']}\r\n";
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
		return mail($data['mailTo'], $subject, $mess,$header,'-f'.$data['from']);
	}

	function mailForm($mailTo) {
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
				$arr['vars']['mailTo']=$mailTo;
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
}



<?php
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
		
		$this->_AllowAjaxFn['jsGetUsers'] = true;
		$this->_AllowAjaxFn['jsSendMsg'] = true;		
		$this->_AllowAjaxFn['jsDelMsg'] = true;
		$this->_AllowAjaxFn['jsGetUserData'] = true;
		$this->default_access = '|0|';
		
		return true;
	}

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();

		$this->_enum['mailengine'] = array(
			0=>'SendMail',
			1=>'phpMailer',
		);
		$this->_enum['SMTPSecure'] = array(
			''=>'',
			'ssl'=>'ssl',
			'tls'=>'tls',
		);
		$this->_enum['PHPMailer_Debug'] = array(
			0=>'Отключить',
			1=>'выжные ошибки',
			2=>'все ошибки',
		);
		$this->config['mailengine'] = 0;
		$this->config['mailrobot'] = 'robot@xakki.ru';
		$this->config['fromName'] = '';
		$this->config['PHPMailer_Host'] = 'ssl://smtp.gmail.com:465';
		$this->config['PHPMailer_Username'] = 'usermail@gmail.com';
		$this->config['PHPMailer_Password'] = 'longpassword';
		$this->config['PHPMailer_Debug'] = 0;
		$this->config['PHPMailer_Secure'] = '';
		$this->config['mailtemplate'] = '<html><head><title>%SUBJECT%</title><meta content="text/html;charset=utf-8" http-equiv="Content-Type" /></head><body>%TEXT% %MAILBOTTOM%</body></html>';
		$this->config['mailbottom'] = '<hr/>© 2011 «XAKKI»';

		$this->config_form['mailengine'] = array('type' => 'list', 'listname'=>'mailengine', 'caption' => 'Обработчик почты');
		$this->config_form['mailrobot'] = array('type' => 'text', 'mask' =>array('min'=>1,'name'=>'email'), 'caption' => 'Адрес Робота');
		$this->config_form['fromName'] = array('type' => 'text', 'caption' => 'Имя отправителя (название сайта)');
		$this->config_form['PHPMailer_Host'] = array('type' => 'text', 'caption' => 'PHPMailer_Host', 'mask' =>array('name'=>'all'),'style'=>'background:#30B120;');
		$this->config_form['PHPMailer_Username'] = array('type' => 'text', 'caption' => 'PHPMailer_Username', 'mask' =>array('name'=>'all'),'style'=>'background:#30B120;');
		$this->config_form['PHPMailer_Password'] = array('type' => 'text', 'caption' => 'PHPMailer_Password', 'mask' =>array('name'=>'all'),'style'=>'background:#30B120;');
		$this->config_form['PHPMailer_Debug'] = array('type' => 'list', 'listname'=>'PHPMailer_Debug', 'caption' => 'Дебаг','style'=>'background:#30B120;');
		$this->config_form['PHPMailer_Secure'] = array('type' => 'list', 'listname'=>'SMTPSecure', 'caption' => 'SMTPSecure','style'=>'background:#30B120;');
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
		$this->fields['user_to'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['mail_to'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['status'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL');
		$this->fields['category'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL','default'=>0);

		$this->_enum['status'] = array(
			0 => 'Не отправлялось на email',
			1 => 'Отправлялось на email',
			2 => 'При отправке на email произошли ошибки',
			3 => 'Не отправлялось на email и было прочитано на сайте',
			4 => 'Удалено пользователем',
		);

		$this->_enum['category'] = array(
			0 => '--',
			1 => 'iBug',
			2 => 'feedback',
			3 => 'over',
		);

		$this->locallang['default']['_saveclose'] = 'Отправить письмо';
		$this->ordfield = 'mf_timecr DESC';
	}


	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form['from']= array('type'=>'text','caption'=>'Обратный email адрес','mask'=>array('name'=>'email', 'min' => '4'));
		$this->fields_form[$this->mf_createrid] = array(
			'type' => 'list',
			'readonly'=>true,
			'listname'=>array('class'=>'users','nameField'=>'concat(tx.name," [",tx.id,"]")'),
			'caption' => 'От кого', 'mask' => array('usercheck'=>1));
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
		$this->fields_form['mail_to'] = array('type' => 'text', 'caption' => 'Кому email', 'mask' => array('usercheck'=>1));
		$this->fields_form['user_to'] = array(
			'type' => 'list', 
			'readonly'=>true,
			'listname'=>array('class'=>'users','nameField'=>'concat(tx.name," [",tx.id,"]")'),
			'caption' => 'Кому', 'mask' => array('usercheck'=>1));
		$this->fields_form['mf_timecr'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата создания', 'mask'=>array('usercheck'=>1,'fview'=>2,'sort'=>1));
		$this->fields_form['status'] = array('type' => 'list', 'listname'=>'status', 'caption' => 'Статус', 'mask' => array('usercheck'=>1));
		$this->fields_form['category'] = array('type' => 'list', 'listname'=>'category', 'caption' => 'Категория', 'mask' => array('usercheck'=>1));

		if(isset($this->HOOK['setFieldsForm'])){
			call_user_func($this->HOOK['setFieldsForm'],$this);
		}

	}

	/**
	*
	*
	*/
	function Send($data) {
		$send_result = false;
		$this->__do_hook('Send', $data);
		if(!isset($data['from']) or !$data['from']) {
			if(isset($data['creater_id']) && $data['creater_id'] == -1)
				$data['from']=$this->config['mailrobot'];
			elseif (isset($_SESSION['user']['email']) && $_SESSION['user']['email'])
				$data['from'] = $_SESSION['user']['email'];
		}

		if(!$data['mail_to']) {
			unset($data['mail_to']);
			$data['status'] = 0;
			$send_result = true;
		}
		else {
			if(!$data['from'])
				$data['from'] = 'anonim@'.$_SERVER['HTTP_HOST'];
			if(method_exists($this, 'mailengine'.$this->config['mailengine'])) {
				$send_result = call_user_func(array($this, 'mailengine'.$this->config['mailengine']),$data);
			}
			else {
				trigger_error('Попытка вызвать не существующий метод `mailengine'.$this->config['mailengine'].'` в модуле Mail!', E_USER_ERROR);
			}

			if ($send_result) {
				$data['status'] = 1;
			}
			else {
				$data['status'] = 2;
			}
		}

		$this->_add_item($data);
		return $send_result;
		
	}

	function mailForm($mail_to='',$category=0) {
		global $_MESS;
		$flag=0;// 1 - успешно, 0 - норм, -1  - ошибка
		$formflag = 1;// 0 - показывает форму, 1 - не показывать форму
		$mess = array();
		
		/*if(!$mail_to) {
			$mail_to = $this->config['mailrobot'];
		}*/

		$param=array('capthaOn'=>1);
		$data = array();

		if(count($_POST) and $_POST['sbmt']) {
			$this->kPreFields($_POST,$param);
			if(isset($_SESSION['user']['email']) and $_SESSION['user']['email']) {
				$_POST["from"] = $_SESSION['user']['email'];
			}
			$arr = $this->fFormCheck($_POST,$param,$this->fields_form);
			$flag=-1;
			if(!count($arr['mess'])) {
				$arr['vars']['mail_to']=$mail_to;
				$arr['vars']['category']=$category;
				if($this->Send($arr['vars'])) {
					$flag=1;
					$mess[] = array('name'=>'ok', 'value'=>$this->getMess('mailok'));
					// иногда сервер говорит что ошибка, а сам всеравно письма отсылает
				} else {
					$flag=-1;
					$mess[] = array('name'=>'error', 'value'=>$this->getMess('mailerr'));
				}
			}else
				$mess = $arr['mess'];
		} else {
				$mess = $this->kPreFields($_POST,$param);
		}
		if(isset($_SESSION['user']['email']) and $_SESSION['user']['email'])
			unset($this->fields_form["from"]);
		if(isset($this->fields_form['captcha']))
			static_form::setCaptcha();

		if($flag==1)
			$formflag = 0;
		if($formflag) // показывать форму , также если это АЯКС и 
			$formflag = $this->kFields2Form($param);

		static_form::setCaptcha();

		return Array(Array('messages'=>$mess, 'form'=>($formflag?$this->form:array())), $flag);

	}

	function mailengine0 ($data) {
		$data['subject'] = substr(htmlspecialchars(trim($data['subject'])), 0, 1000);
		$this->uid = strtoupper(md5(uniqid(time())));
		$subject = '=?utf-8?B?'. base64_encode($data['subject']).'?=';
		$this->config['mailbottom'] = str_replace(array('%host%','%year%'),array($_SERVER['HTTP_HOST'],date('Y')),$this->config['mailbottom']);
		$text = str_replace(array('%SUBJECT%','%TEXT%','%MAILBOTTOM%'),array($data['subject'],trim($data['text']),$this->config['mailbottom']),$this->config['mailtemplate']);
		//$text = substr(trim($data['text']), 0, 1000000).$this->config['mailbottom'] = str_replace('%YEAR%',date('Y'),$this->config['mailbottom']);
			
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
		$res = mail($data['mail_to'], $subject, $mess,$header,'-f'.$data['from']);
		if(!$res)
			trigger_error('SENDMAIL: '.$this->getMess('mailerr'), E_USER_WARNING);
		return $res;
	}

	function mailengine1 ($data) {
		include_once(dirname(__FILE__).'/phpMailer/class.phpmailer.php');
		$data['subject'] = substr(htmlspecialchars(trim($data['subject'])), 0, 1000);
		
		$PHPMailer = new PHPMailer();
		$PHPMailer->IsSMTP();
		$PHPMailer->SMTPAuth = true;
		$PHPMailer->CharSet = "utf-8";
		$PHPMailer->Host = $this->config['PHPMailer_Host'];
		$PHPMailer->Username = $this->config['PHPMailer_Username'];
		$PHPMailer->Password = $this->config['PHPMailer_Password'];
		$PHPMailer->SMTPDebug = $this->config['PHPMailer_Debug'];
		$PHPMailer->SMTPSecure = $this->config['PHPMailer_Secure'];
		$PHPMailer->SetLanguage('ru');
		$PHPMailer->From = $data['from'];
		//$PHPMailer->AddReplyTo($data['from']);

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
			trigger_error('PHPMailer: '.$PHPMailer->ErrorInfo, E_USER_WARNING);
			return false;
		}

		return true;
	}
	
	function getMsgCount()
	{
		$data = array();
		
		$result = $this->SQL->execSQL('
			select count(id) as cnt from `'.$this->tablename.'`
			where `mf_timecr`>"'.(time()-24*60*60).'" and user_to="'.$_SESSION['user']['id'].' and status!=4"
		');
		
		if ($row = $result->fetch_array())
		{
			$data['new_msg'] = $row['cnt'];
		}
		
		$result = $this->SQL->execSQL('
			select count(id) as cnt from `'.$this->tablename.'`
			where `creater_id`="-1" and user_to="'.$_SESSION['user']['id'].' and status!=4"
		');
		
		if ($row = $result->fetch_array()) {
			$data['system_msg'] = $row['cnt'];
		}
		
		$result = $this->SQL->execSQL('
			select count(id) as cnt from `'.$this->tablename.'`
			where `creater_id`!="-1" and user_to="'.$_SESSION['user']['id'].' and status!=4"
		');
		
		if ($row = $result->fetch_array())
		{
			$data['private_msg'] = $row['cnt'];
		}
		
		return $data;
	}
	
	function getMsgList($select_type, $items_on_page, $marker, $tab_id)
	{
		global $PGLIST;
		
		$where = array(			
			'`status`!=4',
		);
		switch ($select_type)
		{
			case 'all':
			{
	//			$where[] = '(`user_to`="'.$_SESSION['user']['id'].'" OR `creater_id`="'.$_SESSION['user']['id'].'")';	
				$where[] = '`user_to`="'.$_SESSION['user']['id'].'"';
			}
			break;
				
			case 'new':
			{
				$where[] = '`user_to`="'.$_SESSION['user']['id'].'"';
				$where[] = '`mf_timecr`>"'.(time()-24*60*60).'"';
			}
			break;
		
			case 'private':
			{
				$where[] = '`user_to`="'.$_SESSION['user']['id'].'"';				
				$where[] = '`creater_id`!="-1"';
			}
			break;
		
			case 'system':
			{
				$where[] = '`user_to`="'.$_SESSION['user']['id'].'"';
				$where[] = '`creater_id`="-1"';
			}
			break;
		
			case 'sent':
			{				
				$where[] = '`creater_id`="'.$_SESSION['user']['id'].'"';
			}
			break;
		
			default:// Удаленные
			{
				$where = array(			
					'`status`=4',
				);
			}
		}
		
		if ($items_on_page == 0)
		{
			$limit_str = '';
		}
		else
		{
			if (isset($_GET['_pn']))
			{
				$page = (int)$_GET['_pn'];
				if ($page <= 0)
				{
					$page = 1;
				}
			}
			else
			{
				$page = 1;
			}
			$limit_str = ' limit ' . ($page - 1) . ', '.$items_on_page;
		}
		
		if (empty($where))
		{
			$where_str = '';
		}
		else
		{
			$where_str = ' where ' . implode(' and ', $where);
		}
		
		$data = array();
				
		$result = $this->SQL->execSQL('select * from `'.$this->tablename.'`'.$where_str.' order by `mf_timecr` desc'.$limit_str);
			
		$data['rows'] = array();
		while ($row = $result->fetch_array())
		{
			$data['rows'][] = $row;
			if ($row['creater_id'] != -1)
			{
				if ($row['creater_id'] == $_SESSION['user']['id'])
				{
					$users[$row['user_to']] = true;
				}
				else
				{
					$users[$row['creater_id']] = true;
				}				
			}			
		}
		
		$data['users'] = array(
			-1 => array(
				'name' => 'Системное сообщение',
				'userpic' => 'png',
			),
		);
		if (!empty($users))
		{
			$users = array_keys($users);
			_new_class('ugroup', $UGROUP);
			
			$result = $this->SQL->execSQL('
				select * from `'.$UGROUP->childs['users']->tablename.'` where id in ("'.(implode('", "', $users)).'")
			');
			while ($row = $result->fetch_array())
			{
				$data['users'][$row['id']] = $row;
			}
		}
		
		$data['tab_id'] = $tab_id;
		
		$data['page_nav'] = array(
			'current_page' => $page,
			'href' => '#',			
			'onclick' => 'return wep.privateMsgGetPage(###PAGE_NUM###, \''.$marker.'\', \''.$tab_id.'\', '.$PGLIST->id.')',
		);
		if ($limit_str == '' || empty($data['rows']))
		{
			$data['page_nav']['count_pages'] = count($data['rows']);
		}
		else
		{			
			$result = $this->SQL->execSQL('select count(id) as cnt from `'.$this->tablename.'`'.$where_str);
			if ($row = $result->fetch_array())
			{
				$data['page_nav']['count_pages'] = ceil($row['cnt'] / $items_on_page);
			}
		}	
		
		return $data;
	}
	
	function jsGetUsers()
	{
		$data = array();
		if (isset($_GET['term']))
		{
			_new_class('ugroup', $UGROUP);
			
			$term = mysql_real_escape_string((string)$_GET['term']);
			
			$result = $this->SQL->execSQL('
				select `id`, `userpic`,`name`
				from `'.$UGROUP->childs['users']->tablename.'`
				where name like "%'.$term.'%" and active=1 and id!="'.$_SESSION['user']['id'].'"
			');
		
			$data['users'] = array();
			while ($row = $result->fetch_array())
			{
				$data['users'][] = $row;
			}		
		}		
		else
		{
			$data['error'] = 'Не переданы все необходимые параметры';
		}
		return $data;
	}
	
	function jsSendMsg()
	{
		if ((isset($_POST['msg']) && isset($_POST['user_id'])) || $_POST['user_id'] == 0)
		{
			$msg = mysql_real_escape_string((string)$_POST['msg']);
			$user_id = (int)$_POST['user_id'];
			
			if ($user_id == $_SESSION['user']['id'])
			{
				$result = array(
					'result' => 0,
					'error' => 'Самому себе отправлять сообщения нельзя',
				);
			}
			else
			{
				_new_class('ugroup', $UGROUP);
						
				$sql_result = $this->SQL->execSQL('
					select count(id) as cnt from `'.$UGROUP->childs['users']->tablename.'`
					where id="'.$user_id.'"	AND active=1			
				');

				if ($row = $sql_result->fetch_array())
				{
					if ($row['cnt'] == 1)
					{
						$data = array(
							'subject' => 'Личное сообщение',
							'user_to' => $user_id,
							'text' => $msg,
						);

						if ($this->_add_item($data)) {
							$result = array('result' => 1);
						}
						else {
							$result = array(
								'result' => 0,
								'error' => 'Во время отправки сообщения произошла ошибка, приносим извинения за неудобства',
							);
						}	
					}
					else
					{
						$result = array(
							'result' => 0,
							'error' => 'Пользователь не найден',
						);
					}				
				}
				else
				{
					$result = array(
						'result' => 0,
						'error' => 'Во время отправки сообщения произошла ошибка, приносим извинения за неудобства',
					);
				}
			}	
					
		}
		else
		{
			$result = array(
				'result' => 0,
				'error' => 'Не переданы все необходимые параметры',
			);
		}
		
		return $result;
	}
	
	function jsDelMsg()
	{
		if (isset($_GET['msg_id']))
		{						
			$msg_id = (int)$_GET['msg_id'];
			
			$sql_result = $this->SQL->execSQL('
				select count(id) as cnt from `'.$this->tablename.'`
				where id="'.$msg_id.'"
			');
			
			if ($row = $sql_result->fetch_array())
			{
				if ($row['cnt'] == 1)
				{
					$this->id = $msg_id;
					$data['status'] = 4;

					if ($this->_save_item($data))
					{
						$result = array('result' => 1);
					}
					else
					{
						$result = array(
							'result' => 0,
							'error' => 'Во время удаления сообщения произошли ошибки, приносим извинения за неудобства',
						);
					}
				}
				else
				{
					$result = array(
						'result' => 0,
						'error' => 'Сообщение не найдено',
					);
				}
				
			}
			else
			{
				$result = array(
					'result' => 0,
					'error' => 'Во время удаления сообщения произошли ошибки, приносим извинения за неудобства',
				);
			}
		
			
		}
		else
		{
			$result = array(
				'result' => 0,
				'error' => 'Не переданы все необходимые параметры',
			);
		}
		return $result;			
	
	}
	
	
	function jsGetUserData()
	{
		if (isset($_GET['user_id']))
		{
			$user_id = (int)$_GET['user_id'];
			
			_new_class('ugroup', $UGROUP);
			
			$result = $this->SQL->execSQL('
				select `userpic`,`name`
				from `'.$UGROUP->childs['users']->tablename.'`
				where id="'.$user_id.'"
			');
			
			if ($row = $result->fetch_array())
			{
				$result = array(
					'result' => 1,
					'user_data' => $row,
				);
			}
			else
			{
				$result = array(
					'result' => 0,
					'error' => 'Пользователь не найден',
				);
			}
		}
		else
		{
			$result = array(
				'result' => 0,
				'error' => 'Не переданы все необходимые параметры',
			);
		}
		return $result;
	}

}



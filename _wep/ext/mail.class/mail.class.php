<?php
define('MCAT_DEFAULT',0);
define('MCAT_BUGS',1);
define('MCAT_FEEDBACK',2);
define('MCAT_PAY',3);
define('MCAT_USER',4);
define('MCAT_OVER',10);

define('MAIL_NEW',0);
define('MAIL_OK',1);
define('MAIL_ERROR',2);
define('MAIL_ERROR2',3);

class mail_class extends kernel_extends {

	function _set_features() {
		parent::_set_features();
		$this->ver = '0.0.2';
		$this->reply=1;
		$this->contenttype= 'text/html';
		$this->uid='';		
		$this->attaches = array();
		$this->caption = 'Почта';
		$this->cf_reinstall = true;
		$this->mf_timecr = true;
		$this->default_access = '|9|';
		$this->_AllowAjaxFn['jsGetUsers'] = true;
		$this->_AllowAjaxFn['jsSendMsg'] = true;		
		$this->_AllowAjaxFn['jsDelMsg'] = true;
		$this->_AllowAjaxFn['jsGetUserData'] = true;

		$this->default_access = '|0|';
		$this->lang['add'] = 'Письмо успешно отправлено!';
		$this->lang['add_err'] = 'Ошибка отправки письма! Информация о данной ошибке уже сообщена авминистратору и проблема разрешится в течении суток.';

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
		$this->config['mailcron'] = 0;
		$this->config['mailcronlimit'] = 5;
		$this->config['mailrobot'] = $this->_CFG['site']['email'];
		$this->config['fromName'] = '';
		$this->config['PHPMailer_Host'] = '';
		$this->config['PHPMailer_Username'] = '';
		$this->config['PHPMailer_Password'] = '';
		$this->config['PHPMailer_Debug'] = 0;
		$this->config['PHPMailer_Secure'] = '';
		$this->config['mailtemplate'] = '<html><head><title>%SUBJECT%</title><meta content="text/html;charset=utf-8" http-equiv="Content-Type" /></head><body>%TEXT% %MAILBOTTOM%</body></html>';
		$this->config['mailbottom'] = '<hr/>© '.date('Y').' «'.$this->_CFG['site']['www'].'»';
		$this->config['phpmailer'] = '<h4>Email отправителя %MAILFROM%</h4> <span>Чтобы ответить пользователю, пользуйтесь кнопой "ответить" или копируйте адрес вручную.</span><hr/>';

		$this->config_form['mailcron'] = array('type' => 'checkbox', 'caption' => 'CRON - Отправалять почту');
		$this->config_form['mailcronlimit'] = array('type' => 'text', 'caption' => 'CRON - Limit по отправке писем');
		$this->config_form['mailengine'] = array('type' => 'list', 'listname'=>'mailengine', 'caption' => 'Обработчик почты');
		$this->config_form['mailrobot'] = array('type' => 'text', 'mask' =>array('min'=>1,'name'=>'email'), 'caption' => 'Адрес Робота');
		$this->config_form['fromName'] = array('type' => 'text', 'caption' => 'Имя отправителя (название сайта)');
		$this->config_form['PHPMailer_Host'] = array('type' => 'text', 'caption' => 'PHPMailer_Host', 'comment'=>'ssl://smtp.gmail.com:465', 'mask' =>array('name'=>'all'),'style'=>'background:#30B120;');
		$this->config_form['PHPMailer_Username'] = array('type' => 'text', 'caption' => 'PHPMailer_Username', 'comment'=>'usermail@gmail.com', 'mask' =>array('name'=>'all'),'style'=>'background:#30B120;');
		$this->config_form['PHPMailer_Password'] = array('type' => 'text', 'caption' => 'PHPMailer_Password', 'mask' =>array('name'=>'all'),'style'=>'background:#30B120;');
		$this->config_form['PHPMailer_Debug'] = array('type' => 'list', 'listname'=>'PHPMailer_Debug', 'caption' => 'Дебаг','style'=>'background:#30B120;');
		$this->config_form['PHPMailer_Secure'] = array('type' => 'list', 'listname'=>'SMTPSecure', 'caption' => 'SMTPSecure','style'=>'background:#30B120;');
		$this->config_form['mailtemplate'] = array(
			'type' => 'textarea',
			'caption' => 'Шаблон по умолчанию', 
			'comment' => '%SUBJECT%, %TEXT%, %MAILBOTTOM%');
		$this->config_form['phpmailer'] = array(
			'type' => 'ckedit',
			'caption' => 'Текст прикрепляемый в начале письма c PHPMailer. ', 
			'comment'=>'Если отправка письма с помощью PHPMailer, и обратный адресат отличный от mailrobot',
			'paramedit'=>array(
				'height'=>350,
				'fullPage'=>'true',
				'toolbarStartupExpanded'=>'false'));
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
		$this->fields['bcc'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['comment'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default'=>'');

		$this->_enum['status'] = array(
			MAIL_NEW => 'NEW',
			MAIL_OK => 'ОК',
			MAIL_ERROR => 'Ошибка при отправке',
			MAIL_ERROR2 => 'Ошибка при отправке повторно',
		);

		$this->_enum['category'] = array(
			MCAT_DEFAULT => '--',
			MCAT_BUGS => 'Bugs',
			MCAT_FEEDBACK => 'Обратная связь',
			MCAT_PAY => 'Платежное уведомление',
			MCAT_USER => 'Служба пользователей',
			MCAT_OVER => 'Прочее',
		);

		$this->lang['Save and close'] = 'Отправить письмо';
		$this->ordfield = 'mf_timecr DESC';

		$this->cron['cronSend'] = array('modul'=>$this->_cl,'function'=>'cronSend()','active'=>0,'time'=>300);
			if($this->config['mailcron']) $this->cron[$this->_cl]['active'] = 1;
		$this->cron['cronSendRepeate'] = array('modul'=>$this->_cl,'function'=>'cronSendRepeate()','active'=>0,'time'=>3000);

		$this->index_fields['status'] = 'status';
		$this->index_fields['category'] = 'category';
	}


	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form['from']= array('type'=>'text','caption'=>'Обратный email адрес','mask'=>array('name'=>'email', 'min' => '4'));
		/*$this->fields_form[$this->mf_createrid] = array(
			'type' => 'list',
			'readonly'=>true,
			'listname'=>array('class'=>'users','nameField'=>'concat(tx.name," [",tx.id,"]")'),
			'caption' => 'От кого', 'mask' => array('usercheck'=>1));*/
		$this->fields_form[$this->mf_createrid] = array('type' => 'text', 'readonly'=>1, 'caption' => 'От кого', 'mask'=>array('usercheck'=>1));
		
		$this->fields_form['subject']= array('type'=>'text','caption'=>'Тема письма', 'mask'=>array('min' => '4'));
		$this->fields_form['text'] = array(
			'type' => 'ckedit', 
			'caption' => 'Текcт письма', 
			'mask' =>array('name'=>'all','min'=>4,'fview'=>1),
			'paramedit'=>array(
				'toolbar'=>'Board',
				'height'=>250,
				'toolbarStartupExpanded'=>'false',
				'extraPlugins'=>"'cntlen'"));
		
		if(static_main::_prmUserCheck(1)) {
			$this->fields_form['text']['paramedit'] = array(
				'CKFinder'=>1,
				'toolbarStartupExpanded'=>'false',
				'extraPlugins'=>"'cntlen,syntaxhighlight,timestamp'",
				'toolbar' => 'Page',
			);
		}
		$this->fields_form['mail_to'] = array('type' => 'text', 'caption' => 'Кому email', 'mask' => array('name'=>'email','usercheck'=>1));
		/*$this->fields_form['user_to'] = array(
			'type' => 'list', 
			'readonly'=>true,
			'listname'=>array('class'=>'users','nameField'=>'concat(tx.name," [",tx.id,"]")'),
			'caption' => 'Кому', 'mask' => array('usercheck'=>1));*/
		$this->fields_form['user_to'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Кому', 'mask'=>array('usercheck'=>1));
		
		$this->fields_form['mf_timecr'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата создания', 'mask'=>array('usercheck'=>1,'fview'=>2,'sort'=>1));
		$this->fields_form['status'] = array('type' => 'list', 'listname'=>'status', 'caption' => 'Статус', 'mask' => array('usercheck'=>1));
		$this->fields_form['category'] = array('type' => 'list', 'listname'=>'category', 'caption' => 'Категория', 'mask' => array('usercheck'=>1));
		$this->fields_form['comment']= array('type'=>'text', 'caption'=>'Redirect url', 'readonly'=>1);
	}

	/**
	*
	*
	*/
	function Send($data, $category=0) {
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
			if(!$this->config['mailcron']) {
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
			}else
				$send_result = true;
		}
		$data['category'] = $category;

		$this->_add($data);
		return $send_result;
		
	}

	function mailForm($mail_to='',$category=0) {
		if(!$mail_to) $mail_to = $this->config['mailrobot'];
		$this->formSort = array(
			'from','subject','text','category','mail_to', 'comment'
		);

		$this->getFieldsForm(1);
		$argForm = $this->fields_form;

		$argForm['category']['mask']['evala'] = '"'.$category.'";';
		$argForm['category']['readonly'] = true;
		$argForm['mail_to']['mask']['evala'] = '"'.$mail_to.'";';
		$argForm['mail_to']['readonly'] = true;

		$uri_hash = md5($mail_to.$category);
		if(
				!isset($_COOKIE['ref'.$uri_hash]) 
			or 
				($this->_CFG['returnFormat'] == 'html' and $_SERVER['HTTP_REFERER']  and $_COOKIE['ref'.$uri_hash]!=$_SERVER['HTTP_REFERER'] and strpos($_SERVER['HTTP_REFERER'],$_SERVER['REQUEST_URI'])===false) 
			) {
			_setcookie('ref'.$uri_hash, $_SERVER['HTTP_REFERER'], ($this->_CFG['time']+3600));
		}
		if(isset($_COOKIE['ref'.$uri_hash]))
			$argForm['comment']['mask']['evala'] = '"'.$this->SqlEsc($_COOKIE['ref'.$uri_hash]).'";';

		if(isset($_SESSION['user']['email']) and $_SESSION['user']['email']) {
			$argForm['from']['mask']['evala'] = '"'.$_SESSION['user']['email'].'";';
			$argForm['from']['readonly'] = true;
		}
		if(isset($_GET['subject']) and !count($_POST)) {
			$_POST['subject'] = $_GET['subject']; 
		}
		return $this->_UpdItemModul(array('capthaOn'=>1),$argForm);
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
		//$header .= "To: {$data['mail_to']}\r\n";
		$header .= "From: {$data['from']}\r\n";
		if(isset($data['bcc']) and $data['bcc'])
			$header .= 'Bcc: '.$data['bcc']."\r\n";
		if(isset($data['Reply-To']) and $data['Reply-To'])
			$header .= 'Reply-To: '.$data['reply']."\r\n";
	
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
			trigger_error('SENDMAIL: '.static_main::m('mailerr',$this), E_USER_WARNING);
		return $res;
	}

	/**
	* Отправка писем с помощью PHPMailer
	*/
	function mailengine1 ($data) {
		include_once($this->_CFG['_PATH']['wep_phpscript'] . '/lib/phpMailer/class.phpmailer.php');
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
		if($data['from']!=$this->config['mailrobot']) {
			$this->Sender = $data['Reply-To'] = $data['from'];
			$data['text'] = str_replace('%MAILFROM%',$data['Reply-To'],$this->config['phpmailer']).$data['text'];
			$data['from'] = $this->config['mailrobot'];
		}
		$PHPMailer->From = $data['from'];
		if(isset($data['bcc']) and $data['bcc'])
			$PHPMailer->AddBCC($data['bcc']);
		if(isset($data['Reply-To']) and $data['Reply-To'])
			$PHPMailer->AddReplyTo($data['Reply-To']);

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
		
		if ($row = $result->fetch())
		{
			$data['new_msg'] = $row['cnt'];
		}
		
		$result = $this->SQL->execSQL('
			select count(id) as cnt from `'.$this->tablename.'`
			where `creater_id`="-1" and user_to="'.$_SESSION['user']['id'].' and status!=4"
		');
		
		if ($row = $result->fetch()) {
			$data['system_msg'] = $row['cnt'];
		}
		
		$result = $this->SQL->execSQL('
			select count(id) as cnt from `'.$this->tablename.'`
			where `creater_id`!="-1" and user_to="'.$_SESSION['user']['id'].' and status!=4"
		');
		
		if ($row = $result->fetch())
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
		while ($row = $result->fetch())
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
			while ($row = $result->fetch())
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
			if ($row = $result->fetch())
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
			
			$term = $this->SqlEsc((string)$_GET['term']);
			
			$result = $this->SQL->execSQL('
				select `id`, `userpic`,`name`
				from `'.$UGROUP->childs['users']->tablename.'`
				where name like "%'.$term.'%" and active=1 and id!="'.$_SESSION['user']['id'].'"
			');
		
			$data['users'] = array();
			while ($row = $result->fetch())
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
			$msg = $this->SqlEsc((string)$_POST['msg']);
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

				if ($row = $sql_result->fetch())
				{
					if ($row['cnt'] == 1)
					{
						$data = array(
							'subject' => 'Личное сообщение',
							'user_to' => $user_id,
							'text' => $msg,
						);

						if ($this->_add($data)) {
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
			
			if ($row = $sql_result->fetch())
			{
				if ($row['cnt'] == 1)
				{
					$this->id = $msg_id;
					$data['status'] = 4;

					if ($this->_update($data))
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
			
			if ($row = $result->fetch())
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

	function cronSend() 
	{
		$DAT_LIST = $this->_query('*','WHERE status='.MAIL_NEW.' LIMIT '.$this->config['mailcronlimit'], 'id');
		if(!count($DAT_LIST)) return ' - ';
		$this->_update(array('status'=>MAIL_ERROR),'WHERE id in ('.implode(',', array_keys($DAT_LIST)).')', false);
		foreach($DAT_LIST as $data) 
		{
			if(method_exists($this, 'mailengine'.$this->config['mailengine'])) 
			{
				$send_result = call_user_func(array($this, 'mailengine'.$this->config['mailengine']),$data);
				if ($send_result) 
				{
					$this->_update(array('status'=>MAIL_OK),'WHERE id='.$data['id']);
				}
			}

		}
		return array_keys($DAT_LIST);
	}


	function cronSendRepeate() 
	{
		$DAT_LIST = $this->_query('*','WHERE status = '.MAIL_ERROR.' LIMIT '.$this->config['mailcronlimit'], 'id');
		if(!count($DAT_LIST)) return ' - ';
		$this->_update(array('status'=>MAIL_ERROR2), 'WHERE id in ('.implode(',', array_keys($DAT_LIST)).')', false);
		foreach($DAT_LIST as $data) 
		{
			if(method_exists($this, 'mailengine'.$this->config['mailengine'])) {
				$send_result = call_user_func(array($this, 'mailengine'.$this->config['mailengine']),$data);
				if ($send_result) 
				{
					$this->_update(array('status'=>MAIL_OK),'WHERE id='.$data['id']);
				}
			}

		}
		return array_keys($DAT_LIST);
	}



}



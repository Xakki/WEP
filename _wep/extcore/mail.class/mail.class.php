<?
class mail_class extends kernel_class {

	function _set_features() {
		if (parent::_set_features()) return 1;
		$this->reply=1;
		$this->contenttype= 'text/html';
		$this->uid='';		
		$this->attaches = array();
		$this->caption = 'Почта';
		return 0;
	}

	function _create() {
		parent::_create();

		$this->config['sitename'] = 'New company';

		$this->config_form['sitename'] = array('type' => 'text', 'caption' => 'Название сайта','mask'=>array('max'=>1000));

	}

	function Send($data) {
		$this->uid = strtoupper(md5(uniqid(time())));
		$subject = '=?utf-8?B?'. base64_encode(substr(htmlspecialchars(trim($data['subject'])), 0, 1000)).'?=';
		$text = substr(trim($data['text']), 0, 1000000);
		if($data['from']=='')
			$data['from']='robot@unidoski.ru';
		 //if(strlen(ini_get('safe_mode'))< 1){
			 @ini_set('sendmail_from', $data['from']);
		 //}
		$header  = "From: {$data['from']}\r\n";
		if($this->reply) $header .= "Reply-To: {$data['from']}\r\n";
		
		if(isset($data['att'])) {
			$header .= "MIME-Version: 1.0\r\n";
			$header .= "Content-Type: multipart/alternative; boundary={$this->uid}\r\n";
			$header .= "--{$this->uid}\r\n";
		}
		$header .= "Content-Type: ".$this->contenttype."; charset=\"utf-8\"\r\n";
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
		return mail($data['mailTo'], $subject, $mess,$header);
	}

	function mailForm($name,$mailTo) {
		global $_MESS;
		if(_prmUserCheck()) $mailFrom=$_SESSION['user']['email'];
		else  $mailFrom='';
		$param=array('capthaOn'=>1);
		$this->fields_form["info"]= array("type"=>"info",'caption'=>'Отправка письма '.$name);
		$this->fields_form["from"]= array("type"=>"text",'caption'=>'Обратный email адрес', 'min' => '1','mask'=>array('name'=>'email'),'value'=>$mailFrom);
		$this->fields_form["subject"]= array("type"=>"text",'caption'=>'Тема письма', 'min' => '1');
		$this->fields_form["text"]= array("type"=>"textarea",'caption'=>'Текcт письма', 'min' => '1');

		$flag=0;// 0 - показывает форму, 1 - ок, 2 - еррор
		$arr = array('mess'=>'','vars'=>'');
		$mess='';
		if(count($_POST) and $_POST['sbmt']) {
			$this->kPreFields($_POST,$param);
			$arr = $this->fFormCheck($_POST);
			$flag=2;
			if($arr['mess']==''){
				$arr['vars']['mailTo']=$mailTo;
				if($this->Send($arr['vars'])) {
					$flag=1;
					$arr['mess']  = $_MESS['mailok'];
				} else
					$arr['mess']  = $_MESS['mailerr'];
			}
		} else $mess = $this->kPreFields($_POST,$param);

		$this->form['sbmt']['value']='Отправить письмо';

		$_SESSION['captha'] = rand(10000,99999);
		return Array('<messages>'.$mess.$arr['mess'].'</messages>'.($flag==1?'':$this->kFields2Form()),$flag);
	}
}

?>
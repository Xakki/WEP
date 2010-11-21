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

		$this->fields_form["from"]= array("type"=>"text",'caption'=>'Обратный email адрес','mask'=>array('name'=>'email', 'min' => '4'));
		$this->fields_form["subject"]= array("type"=>"text",'caption'=>'Тема письма', 'mask'=>array('min' => '5'));
		$this->fields_form["text"]= array("type"=>"textarea",'caption'=>'Текcт письма', 'mask'=>array('min' => '5'));

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

	function mailForm($mailTo,$templateSubj='',$templateText='') {
		global $_MESS;
		$flag=0;// 1 - успешно, 0 - норм, -1  - ошибка
		$formflag = 1;// 0 - показывает форму, 1 - не показывать форму
		$arr = array('mess'=>array(),'vars'=>array());
		$mess = array();

		$param=array('capthaOn'=>1);
		$data = array();

		if(_prmUserCheck()) {
			$this->fields_form["from"]['default'] = $_SESSION['user']['email'];
		}
		else  $mailFrom='';
		if(count($_POST) and $_POST['sbmt']) {
			$this->kPreFields($_POST,$param);
			$arr = $this->fFormCheck($_POST,$param,$this->fields_form);
			$flag=-1;
			if(!count($arr['mess'])) {
				$arr['vars']['mailTo']=$mailTo;
				if($templateSubj)
					$arr['vars']['subject'] = str_replace('###TEXT###',$arr['vars']['subject'],$templateSubj);
				if($templateText)
					$arr['vars']['text'] = str_replace('###TEXT###',$arr['vars']['text'],$templateText);
				if($this->Send($arr['vars'])) {
					$flag=1;
					$arr['mess'][] = array('name'=>'ok', 'value'=>$this->getMess('mailok'));
				} else
					$arr['mess'][] = array('name'=>'error', 'value'=>$this->getMess('mailerr'));
			}
		} else
				$mess = $this->kPreFields($arr['vars'],$param);
		$_SESSION['captha'] = rand(10000,99999);

		$this->form['sbmt']['value']='Отправить письмо';
		if($flag==1)
			$formflag = 0;
		if($formflag) // показывать форму , также если это АЯКС и 
			$formflag = $this->kFields2Form($param);

		$this->setCaptcha();

		return Array(Array('messages'=>($mess+$arr['mess']), 'form'=>($formflag?$this->form:array())), $flag);

	}
}

?>
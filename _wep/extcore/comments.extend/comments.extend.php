<?
class comments_extends extends kernel_extends {

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		
		$this->config['treelevel'] = 3;
		$this->config['vote'] = 0;
		$this->config['spamtime'] = 2;
		$this->config['defmax'] = 3;
		$this->config['defumax'] = 15;

		$this->config_form['treelevel'] = array('type' => 'int', 'caption' => 'Макс уровень деревьев','comment'=>'0 - откл ответы на комменты, 1 - только 1 уровень ответов, итд');
		$this->config_form['vote'] = array('type' => 'checkbox', 'caption' => 'Голосование');
		$this->config_form['spamtime'] = array('type' => 'int', 'caption' => 'Часов для спама','comment'=>'Время в течении которого пользователь может подать максимальное число комментов');
		$this->config_form['defmax'] = array('type' => 'int', 'caption' => 'Макс. коммент. не пользов.','comment'=>'Максимум комментов за промежуток времени не авторизованному пользователю');
		$this->config_form['defumax'] = array('type' => 'int', 'caption' => 'Макс. коммент. пользов.','comment'=>'Максимум комментов за промежуток времени авторизованному пользователю по умолчанию если не укзана у группы пользователя');
	}

	function _set_features()
	{
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		$this->mf_ipcreate = true;
		$this->mf_timecr = true;
		$this->messages_on_page = 200;
		$this->locallang['default']['denied_add'] = 'Оставлять комментарий могут только зарегистрированные пользователи!';
		$this->locallang['default']['add'] = 'Комментарий добавлен.';
		$this->locallang['default']['add_name'] = 'Добавить комментарий';
		$this->locallang['default']['_saveclose'] = 'Написать комментарий';
		
		return true;
	}

	function _create()
	{
		if($this->config['treelevel']>0)
			$this->mf_istree = true;
		parent::_create();//$this->config доступен после этой функции

		$this->tablename = 'comments';
		$this->caption = 'Комментарии';

		$this->fields['text'] = array('type' => 'text', 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['vote'] = array('type' => 'int', 'width' => 9, 'attr' => 'NOT NULL', 'default'=>0);

		if(static_main::_prmUserCheck())
			$this->fields_form['name'] = array('type' => 'hidden','disabled'=>1, 'caption' => 'Имя', 'default'=>$this->_CFG['userData']['name'], 'mask'=>array('eval'=>'$this->_CFG["userData"]["name"]'));
		else
			$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Ваше имя', 'mask'=>array('min'=>3));
		/*$this->fields_form[$this->mf_createrid] = array(
			'type' => 'list', 
			'listname'=>array('class'=>'users'),
			'caption' => 'Пользователи',
			'readonly'=>1,
			'mask' =>array('usercheck'=>1));*/
		$this->fields_form['text'] = array('type' => 'textarea', 'caption' => 'Текст','mask' =>array('min'=>5, 'max'=>3000));
		if($this->config['vote'])
			$this->fields_form['vote'] = array('type' => 'int', 'caption' => 'Рейтинг','readonly'=>1);
		$this->fields_form['mf_timecr'] =	array('type' => 'date', 'caption' => 'Дата добавления','readonly'=>1, 'mask'=>array());
		$this->fields_form['mf_ipcreate'] =	array('type' => 'text', 'caption' => 'IP-пользователя','readonly'=>1,'mask' =>array('usercheck'=>1));
		if($this->mf_istree)
			$this->fields_form['parent_id'] = array('type' => 'hidden');
		if($this->owner)
			$this->fields_form['owner_id'] = array('type' => 'hidden');
	}

	public function _UpdItemModul($param) {
		$mess=$this->antiSpam();
		if(!count($mess)) {
			$xml = parent::_UpdItemModul($param);
			return $xml;
		}else
			return array(array('messages'=>$mess),-1);
	}

	private function antiSpam() {
		global $_CFG;
		$mess = array();
		if($this->id) return $mess;
		if(!isset($_SESSION['user']['id']))
			$pb= $this->config['defmax'];
		elseif(!(int)$_SESSION['user']['paramcomment'])
			$pb= $this->config['defumax'];
		else
			$pb= (int)$_SESSION['user']['paramcomment'];

		$time = time();
		$cls ='SELECT count(id) as cnt FROM '.$this->tablename.' WHERE mf_timecr>='.($time-(3600*$this->config['spamtime'])).' and mf_timecr<'.$time;
		if(static_main::_prmUserCheck())
			$cls .= ' and '.$this->mf_createrid.'="'.$_SESSION['user']['id'].'"';
		else
			$cls .= ' and mf_ipcreate=INET_ATON("'.$_SERVER["REMOTE_ADDR"].'")';
		$result = $this->SQL->execSQL($cls);
		if(!$result->err and $row = $result->fetch_array()) {
			if($row['cnt']>=$pb) {
				$mess[] = array('name'=>'error', 'value'=>'Внимание! Вы привысили лимит , допускается комментировать не более '.$pb.' раз в период '.$this->config['spamtime'].' часа.');
				if(!isset($_SESSION['user']['id']))
					$mess[] = array('name'=>'alert', 'value'=>'Зарегистрированные пользователи могут отправлять '.$this->config['defumax'].' и более комментариев в '.$this->config['spamtime'].' часа. Подробности <a href="'.$this->_CFG['_HREF']['BH'].'inform.html">тут</a>.');
				return $mess;
			}
		}
		return $mess;
	}

	function displayData($id,$answerid) {
		$result = $this->SQL->execSQL('SELECT * FROM '.$this->tablename.' WHERE owner_id='.$id);
		if(!$result->err)
			while($row = $result->fetch_array()) {
				$this->data[$row['parent_id']][$row['id']] = $row;
				if($answerid and $row['id']==$answerid)
					$answerid = $row['text'];
			}
		return $answerid;
	}


}


?>

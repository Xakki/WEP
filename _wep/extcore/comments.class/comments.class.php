<?

class comments_class extends kernel_extends {

	protected function _create_conf() {/* CONFIG */
		parent::_create_conf();

		$this->config['treelevel'] = 3;
		$this->config['vote'] = 0;
		$this->config['spamtime'] = 2;
		$this->config['defmax'] = 3;
		$this->config['defumax'] = 15;

		$this->config_form['treelevel'] = array('type' => 'int', 'caption' => 'Макс уровень деревьев', 'comment' => '0 - откл ответы на комменты, 1 - только 1 уровень ответов, итд');
		$this->config_form['vote'] = array('type' => 'checkbox', 'caption' => 'Голосование');
		$this->config_form['spamtime'] = array('type' => 'int', 'caption' => 'Часов для спама', 'comment' => 'Время в течении которого пользователь может подать максимальное число комментов');
		$this->config_form['defmax'] = array('type' => 'int', 'caption' => 'Макс. коммент. не пользов.', 'comment' => 'Максимум комментов за промежуток времени не авторизованному пользователю');
		$this->config_form['defumax'] = array('type' => 'int', 'caption' => 'Макс. коммент. пользов.', 'comment' => 'Максимум комментов за промежуток времени авторизованному пользователю по умолчанию если не укзана у группы пользователя');
	}

	function _set_features() {
		if (!parent::_set_features())
			return false;
		$this->caption = 'Комментарии';
		$this->mf_createrid = true;
		$this->mf_actctrl = true;
		$this->mf_ipcreate = true;
		$this->mf_timecr = true;
		$this->mf_istree = true;
		$this->messages_on_page = 50;
		$this->locallang['default']['denied_add'] = 'Оставлять комментарий могут только зарегистрированные пользователи!';
		$this->locallang['default']['add'] = 'Комментарий добавлен.';
		$this->locallang['default']['add_name'] = '';
		$this->locallang['default']['_saveclose'] = 'Написать комментарий';
		$this->_AllowAjaxFn['addComm'] = true;
		return true;
	}

	function _create() {
		parent::_create(); //$this->config доступен после этой функции

		$this->fields['text'] = array('type' => 'text', 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['modul_id'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['modul'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
		//$this->fields['vote'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);

		/*$this->_setHook['__construct']['ugroup'] = array(
			static_main::relativePath(dirname(__FILE__)).'/comments.hook.php' => 'ugroup_hook__construct',
		);*/
	}

	public function setFieldsForm() {
		parent::setFieldsForm();
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Ваше имя', 'mask' => array('min' => 3));
		/* $this->fields_form[$this->mf_createrid] = array(
		  'type' => 'list',
		  'listname'=>array('class'=>'users'),
		  'caption' => 'Пользователи',
		  'readonly'=>1,
		  'mask' =>array('usercheck'=>1)); */
		$this->fields_form['text'] = array('type' => 'textarea', 'caption' => 'Текст', 'mask' => array('min' => 5, 'max' => 3000));
		//$this->fields_form['vote'] = array('type' => 'int', 'caption' => 'Рейтинг', 'readonly' => 1);
		$this->fields_form['mf_timecr'] = array('type' => 'date', 'caption' => 'Дата добавления', 'readonly' => 1, 'mask' => array());
		$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'caption' => 'IP-пользователя', 'readonly' => 1, 'mask' => array('usercheck' => 1));
		if ($this->mf_istree)
			$this->fields_form['parent_id'] = array('type' => 'hidden');
		return true;
	}

	public function addComm() {
		$res = array('messages'=>'Need login!','status'=>-2);
		if(static_main::_prmModul($this->_cl,array(9))) {
			if(isset($this->_CFG['modulprm'][$_POST['modul']])) {
				global $HTML;
				$HTML = new html('_design/',$this->_CFG['wep']['design'],false);// упрощённый режим

				$DATA = $this->_UpdItemModul(array('ajax'=>1));

				$res = array('messages'=>$HTML->transformPHP($DATA[0],'messages'),'status'=>$DATA[1]);
				if($DATA[1]) 
					$res['data'] = $this->displayItem($this->id);
			}
		}
		return $res;
	}

	public function _UpdItemModul($param) {
		$mess = $this->antiSpam();
		if (!count($mess)) {
			$DATA = parent::_UpdItemModul($param);
			if($DATA[1]) {
				$tt = $this->_CFG['modulprm'][$_POST['modul']]['tablename'];
				$result = $this->SQL->execSQL('Update ' .$tt . ' SET comments_count= comments_count+1 WHERE id=' . (int)$_POST['modul_id'] . '');
			}
			return $DATA;
		}else
			return array(array('messages' => $mess), -1);
	}

	function displayList($param=array()) {
		$data = array();
		_new_class('users',$USERS);
		$result = $this->SQL->execSQL('SELECT t1.*,t2.name as uname,t2.userpic FROM ' . $this->tablename . ' t1 LEFT JOIN '.$USERS->tablename.' t2 ON t1.'.$this->mf_createrid.'=t2.id WHERE t1.active=1 and  t1.modul="' . $param['modul'].'" and t1.modul_id="' . $param['modul_id'].'" ORDER BY '.$this->mf_timecr);
		if (!$result->err)
			while ($row = $result->fetch_array()) {
				$data[$row['parent_id']][$row['id']] = $row;
			}
		return $data;
	}

	function displayItem($id) {
		$data = array();
		_new_class('users',$USERS);
		$result = $this->SQL->execSQL('SELECT t1.*,t2.name as uname,t2.userpic FROM ' . $this->tablename . ' t1 LEFT JOIN '.$USERS->tablename.' t2 ON t1.'.$this->mf_createrid.'=t2.id WHERE t1.active=1 and t1.id="' . $id.'"');
		if (!$result->err)
			if ($row = $result->fetch_array()) {
				return $row;
			}
		return $data;
	}
	private function antiSpam() {
		global $_CFG;
		$mess = array();
		if ($this->id)
			return $mess;
		if (!isset($_SESSION['user']['id']))
			$pb = $this->config['defmax'];
		elseif (!isset($_SESSION['user']['paramcomment']) or !(int) $_SESSION['user']['paramcomment'])
			$pb = $this->config['defumax'];
		else
			$pb= (int) $_SESSION['user']['paramcomment'];

		$time = time();
		$cls = 'SELECT count(id) as cnt FROM ' . $this->tablename . ' WHERE mf_timecr>=' . ($time - (3600 * $this->config['spamtime'])) . ' and mf_timecr<' . $time;
		if (static_main::_prmUserCheck())
			$cls .= ' and ' . $this->mf_createrid . '="' . $_SESSION['user']['id'] . '"';
		else
			$cls .= ' and mf_ipcreate=INET_ATON("' . $_SERVER["REMOTE_ADDR"] . '")';
		if(isset($_POST['modul']))
			$cls .= ' and modul="'.mysql_real_escape_string($_POST['modul']).'"';
		if(isset($_POST['modul_id']))
			$cls .= ' and modul_id="'.(int)$_POST['modul_id'].'"';
		$result = $this->SQL->execSQL($cls);
		if (!$result->err and $row = $result->fetch_array()) {
			if ($row['cnt'] >= $pb) {
				$mess[] = array('name' => 'error', 'value' => 'Внимание! Вы привысили лимит , допускается комментировать не более ' . $pb . ' раз в период ' . $this->config['spamtime'] . ' часа.');
				if (!isset($_SESSION['user']['id']))
					$mess[] = array('name' => 'alert', 'value' => 'Зарегистрированные пользователи могут отправлять ' . $this->config['defumax'] . ' и более комментариев в ' . $this->config['spamtime'] . ' часа. Подробности <a href="' . $this->_CFG['_HREF']['BH'] . 'inform.html">тут</a>.');
				return $mess;
			}
		}
		return $mess;
	}


	public function kPreFields(&$data, &$param) {
		if (isset($data['name']) and (!isset($_COOKIE[$this->_cl . '_f_name']) or $_COOKIE[$this->_cl . '_f_name'] != $data['name'])) {
			_setcookie($this->_cl . '_f_name', $data['name'], $this->_CFG['remember_expire']);
		}
		if (static_main::_prmUserCheck()) {
			$this->fields_form['name'] = array('type' => 'hidden', 'disabled' => 1, 'caption' => 'Имя', 'default' => $_SESSION['user']['name'], 'mask' => array('eval' => '$_SESSION["user"]["name"]'));
			//$data['name'] = $_SESSION['user']['name'];
		} elseif (!isset($data['name']) and isset($_COOKIE[$this->_cl . '_f_name']))
			$data['name'] = $_COOKIE[$this->_cl . '_f_name'];
		$mess = parent::kPreFields($data, $param);
		return $mess;
	}

}


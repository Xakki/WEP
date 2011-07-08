<?
class session_class extends kernel_extends {
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_createrid = 'users_id';
		$this->mf_ipcreate = true;
		$this->prm_add = false;
		//$this->prm_edit = false;
		$this->mf_namefields = false;
		$this->cf_reinstall = true;
		$this->ver = '0.1';
		$this->caption = 'Сессии';
		$this->deadvisits  = 2; // мин число визитов
		$this->deadsession = 1800; //мин сек в течении которго если пользователь не зашел >= $this->deadvisits, то удаляются
		$this->tablename = $this->_CFG['sql']['dbpref'].'_session';
		$this->uip = $_SERVER['REMOTE_ADDR']; 
		$this->_time = time();
		$this->_hash = '';
		//$this->expired = get_cfg_var('session.gc_maxlifetime');
		$this->expired = $this->_CFG['session']['expire'];
	}

	function _create() {
		parent::_create();
		$this->fields['id'] = array('type' => 'int', 'width' =>11, 'attr' => 'unsigned NOT NULL auto_increment');
		$this->fields['sid'] = array('type' => 'varchar', 'width' =>128, 'default' => 'NULL');
		$this->fields['host'] = array('type' => 'varchar', 'width' =>255, 'default' => 'NULL');
		$this->fields['host2'] = array('type' => 'varchar', 'width' =>255, 'default' => 'NULL');
		$this->fields['created'] = array('type' => 'int', 'width' =>11, 'attr' => 'unsigned', 'default'=>'0');
		$this->fields['expired'] = array('type' => 'int', 'width' =>11, 'attr' => 'unsigned', 'default'=>'0');
		$this->fields['modified'] = array('type' => 'int', 'width' =>11, 'attr' => 'unsigned', 'default'=>'0');
		$this->fields['data'] = array('type' => 'text', 'attr' => '');
		$this->fields['useragent'] = array('type' => 'varchar', 'width' =>255, 'default' => '');
		$this->fields['visits'] = array('type' => 'int', 'width' =>8, 'attr' => 'unsigned', 'default'=>'1');
		$this->fields['lastpage'] = array('type' => 'varchar', 'width' =>255, 'default' => '');

		$this->fields_form['created'] = array('type' => 'date', 'readonly' => 1,'caption' => 'Начало сессии');
		$this->fields_form['expired'] = array('type' => 'date', 'readonly' => 1,'caption' => 'Срок истекает');
		$this->fields_form['modified'] = array('type' => 'date', 'readonly' => 1,'caption' => 'Время визита');
		$this->fields_form['users_id'] = array('type' => 'list', 'listname'=>array('class'=>'users'), 'readonly' => 1, 'caption' => 'Юзер');
		$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'IP');
		$this->fields_form['useragent'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'UserAgent');
		$this->fields_form['visits'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Хиты');
		$this->fields_form['lastpage'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Страница');
		$this->fields_form['host'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Host');
		$this->fields_form['data'] = array('type' => 'textarea', 'readonly' => 1, 'caption' => 'Data','mask'=>array('fview'=>1));

		$this->index_fields['sid'] = 'sid';
		$this->unique_fields['sid'] = 'sid';

		if(!session_id()) {
			session_set_save_handler(array(&$this,"open"), array(&$this,"close"), array(&$this,"read"), array(&$this,"write"), array(&$this,"destroy"), array(&$this,"gc"));
			session_start();
			
			$params = array(
				'func' => 'session_write_close',
			);
			
			observer::register_observer($params, 'shutdown_function');
		}
	}

	function __destruct() {
	}

	function open($save_path, $session_name) {
		$this->_save_path    = $save_path;
		$this->_session_name = $session_name; 
		$this->add_query = '';//' AND `domain` = "'.$this->_domain.'"';

		$result = $this->SQL->execSQL('SHOW TABLES LIKE \''.$this->tablename.'\'');// checking table exist
		if ($result->err) echo('Session error');
		//if (!$result->num_rows()) $this->_ddInstall();
		return(true);
	}
	
	function close() 
	{
		return(true);
	}

	function read($sid) 
	{
		$this->sid = $sid;
		$this->data = array('data'=>'');
		$result=$this->SQL->execSQL('SELECT * FROM '.$this->tablename.' WHERE `sid` = "'.$this->sid.'"');//AND `modified` + `expired` > "'.$this->_time.'"
		if(!$result->err and $row = $result->fetch_array()) {
			if(($row['modified']+$row['expired'])>$this->_time) {
				$this->data = $row;
				$this->_hash = md5($this->data['data']);
				$_SESSION = unserialize($this->data['data']);
				$this->data['data'] = session_encode();
			}else
				$this->destroy($sid);
		}
		/*$result = $this->SQL->execSQL('select table_comment from information_schema.`tables` where table_name="'.$this->tablename.'" and table_schema="'.$this->_CFG['sql']['database'].'"');
		if($row = $result->fetch_array())
				$this->table_comment = $row['table_comment'];*/
		//$this->table_comment = $this->ver;

		//if($result->err or count($this->data)!=count($this->fields) or $this->table_comment!=$this->ver) {
		//	$this->_checkmodstruct();
		//}
		return $this->data['data'];
	}

	function write($sid, $sess_data) {
		if($sess_data) {
			$sess_data = serialize($_SESSION);
			$userId = (isset($_SESSION['user']['id'])?$_SESSION['user']['id']:0);
			$tempMD5 = md5($sess_data);
			$sess_data = mysql_real_escape_string($sess_data);
			$lastPage = substr(mysql_real_escape_string($_SERVER['REQUEST_URI']),0,250);
			$host = mysql_real_escape_string($_SERVER['HTTP_HOST']);
			if($this->_hash) {
				$query = 'UPDATE '.$this->tablename.' SET `modified` = "'.$this->_time.'", `users_id`="'.$userId.'", `visits` = (`visits` + 1), `lastpage`= "'.$lastPage.'", `host`="'.$host.'"';
				if($this->_hash != $tempMD5)
					$query .= ' ,`data` = "'.$sess_data.'"';
				$result = $this->SQL->execSQL($query.' WHERE `sid`="'.mysql_real_escape_string($sid).'"');
			} else {
				$result = $this->SQL->execSQL('INSERT INTO '.$this->tablename.' 
(`sid`,`created`,`modified`,`expired`,`data`,`users_id`,`mf_ipcreate`,`useragent`,`lastpage`,`host`,`host2`) values
("'.$sid.'","'.$this->_time.'","'.$this->_time.'","'.$this->expired.'","'.$sess_data.'","'.$userId.'","'.sprintf("%u",ip2long($_SERVER['REMOTE_ADDR'])).'","'.mysql_real_escape_string(substr($_SERVER['HTTP_USER_AGENT'],0,250)).'","'.$lastPage.'","'.$host.'","'.sprintf("%u",ip2long($_SERVER['REMOTE_ADDR'])).'")');
			}
			
		}
		else
			$this->destroy($sid);
		return(true);
	}

	function destroy($sid) {
		$result = $this->SQL->execSQL('DELETE FROM '.$this->tablename.' WHERE `sid`  = "'.mysql_real_escape_string($sid).'"');
		$this->gc();
		return(true); 
	}

	function gc($maxlifetime=0) {
		$result = $this->SQL->execSQL('DELETE FROM '.$this->tablename.' WHERE `modified` + `expired` < '.$this->_time.' OR (`created` + '.$this->deadsession.' < '.$this->_time.' AND `visits` < '.$this->deadvisits.')');
		return(true); 
	}

	function delUser($id) {
		$result = $this->SQL->execSQL('DELETE FROM '.$this->tablename.' WHERE `users_id`  = "'.$id.'"');
	}

	function updateUser($id,&$USERS) {
		$data = array('user'=>$USERS->setUserSession($id));
		$data = serialize($data);
		$result = $this->SQL->execSQL('Update '.$this->tablename.' set `data`="'.mysql_real_escape_string($data).'" WHERE `users_id`  = "'.$id.'"');
	}
}

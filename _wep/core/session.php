<?
class session_gogo {
	function __construct($tablename='_session') {
		global $_CFG,$SQL;
		if(!$SQL) {
			trigger_error('Ошибка запуска сессии. Нет связи с БД.',E_USER_WARNING);
			return (true);
		}
		if(!$_CFG) {
			trigger_error('Ошибка загрузки конфигураций.',E_USER_WARNING);
			return (true);
		}
		$this->fields['id'] = array('type' => 'int', 'width' =>11, 'attr' => 'unsigned NOT NULL auto_increment');
		$this->fields['sid'] = array('type' => 'varchar', 'width' =>128, 'attr' => 'default NULL');
		$this->fields['host'] = array('type' => 'varchar', 'width' =>255, 'attr' => 'default NULL');
		$this->fields['host2'] = array('type' => 'varchar', 'width' =>255, 'attr' => 'default NULL');
		$this->fields['created'] = array('type' => 'int', 'width' =>11, 'attr' => 'unsigned default "0"');
		$this->fields['expired'] = array('type' => 'int', 'width' =>11, 'attr' => 'unsigned default "0"');
		$this->fields['modified'] = array('type' => 'int', 'width' =>11, 'attr' => 'unsigned default "0"');
		$this->fields['data'] = array('type' => 'text', 'attr' => 'default ""');
		$this->fields['users_id'] = array('type' => 'varchar', 'width' =>64, 'attr' => 'default ""');
		$this->fields['ip'] = array('type' => 'varchar', 'width' =>32, 'attr' => 'default ""');
		$this->fields['useragent'] = array('type' => 'varchar', 'width' =>255, 'attr' => 'default ""');
		$this->fields['visits'] = array('type' => 'int', 'width' =>8, 'attr' => 'unsigned default "1"');
		$this->fields['lastpage'] = array('type' => 'varchar', 'width' =>255, 'attr' => 'default ""');

		$this->index_fields['sid'] = 'sid';
		$this->index_fields['users_id'] = 'users_id';
		$this->unique_fields['sid'] = 'sid';

		$this->SQL = &$SQL;
		$this->_CFG = &$_CFG;
		$this->ver = '0.1';
		$this->deadvisits  = 2; // мин число визитов
		$this->deadsession = 1800; //мин сек в течении которго если пользователь не зашел >= $this->deadvisits, то удаляются
		$this->tablename = $_CFG['sql']['dbpref'].$tablename;
		$this->uip = $_SERVER['REMOTE_ADDR']; 
		$this->_time = time();
		//$this->expired = get_cfg_var('session.gc_maxlifetime');
		$this->expired = $_CFG['session']['expire'];
		session_set_save_handler(array(&$this,"open"), array(&$this,"close"), array(&$this,"read"), array(&$this,"write"), array(&$this,"destroy"), array(&$this,"gc"));
		session_start();
		
		$params = array(
			'func' => 'session_write_close',
		);
		
		observer::register_observer($params, 'shutdown_function');
	}

	function __destruct() {
	}

	function open($save_path, $session_name) {
		$this->_save_path    = $save_path;
		$this->_session_name = $session_name; 
		$this->add_query = '';//' AND `domain` = "'.$this->_domain.'"';
		$result = $this->SQL->execSQL('SHOW TABLES LIKE \''.$this->tablename.'\'');// checking table exist
		if ($result->err) echo('Session error');
		//if (!$result->num_rows()) $this->_install();
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
			}
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
			$data = $this->_unserialize($sess_data);
			$sess_data = mysql_escape_string($sess_data);
			$lastPage = mysql_escape_string($_SERVER['REQUEST_URI']);
			$host = mysql_escape_string($_SERVER['HTTP_HOST']);
			$result = $this->SQL->execSQL('INSERT INTO '.$this->tablename.' 
(`sid`,`created`,`modified`,`expired`,`data`,`users_id`,`ip`,`useragent`,`lastpage`,`host`,`host2`) values
("'.$sid.'","'.$this->_time.'","'.$this->_time.'","'.$this->expired.'","'.$sess_data.'","'.$data['user']['id'].'","'.mysql_escape_string($_SERVER["REMOTE_ADDR"]).'","'.mysql_escape_string(substr($_SERVER['HTTP_USER_AGENT'],0,255)).'","'.$lastPage.'","'.$host.'","'.mysql_escape_string($_SERVER['HTTP_HOST2']).'") 
ON DUPLICATE KEY UPDATE `modified` = "'.$this->_time.'", `users_id`="'.$data['user']['id'].'" ,`data` = "'.$sess_data.'", `visits` = (`visits` + 1), `lastpage`= "'.$lastPage.'", `host`="'.$host.'"');
		}
		else
			$this->destroy($sid);
		return(true);
	}

	function destroy($sid) {
		$result = $this->SQL->execSQL('DELETE FROM '.$this->tablename.' WHERE `sid`  = "'.mysql_escape_string($sid).'"');
		return(true); 
	}

	function gc($maxlifetime) {
		$result = $this->SQL->execSQL('DELETE FROM '.$this->tablename.' WHERE `modified` + `expired` < '.$this->_time.' OR (`created` + '.$this->deadsession.' < '.$this->_time.' AND `visits` < '.$this->deadvisits.')');
		return(true); 
	}
/*
	function _checkmodstruct() {
		return include($this->_CFG['_PATH']['core'].'kernel.checkmodstruct.php');
		//return $result->err;
	}

	function _install() {
		return include($this->_CFG['_PATH']['core'].'kernel.install.php');
	}*/

	function _unserialize($serialized_string) {
		$variables = array(  );
		$a = preg_split( "/(\w+)\|/", $serialized_string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		for( $i = 0; $i < count( $a ); $i = $i+2 ) {
			$variables[$a[$i]] = unserialize( $a[$i+1] );
		}
		return( $variables );
	}


}

?>

<?
class bug_class extends kernel_class {

	protected function _create_conf() {
		parent::_create_conf();

		$this->config['act'] = 0;

		$this->config_form['act'] = array('type' => 'checkbox', 'caption' => 'Включить логирование ошибок');
	}

	function _set_features() {
		if (parent::_set_features()) return 1;
//		$this->mf_use_charid = true;
		$this->mf_timecr = true;
		$this->mf_ipcreate = true;
		$this->mf_add = false;
		$this->mf_del = false;
		$this->mf_actctrl = true;
		$this->mf_statistic = false;
		$this->mf_actctrl = true; // поле active
		
		$this->singleton = true;
		
		$this->caption = 'Отладчик';
		$this->ver = '0.0.1';
		return 0;
	}

	function _create() {
		parent::_create();

		# fields
		$this->fields['name'] = array('type' => 'text', 'attr' => 'NOT NULL', 'min' => '1');	
		$this->fields['err_type'] = array('type' => 'int', 'attr' => 'NOT NULL');
		$this->fields['file'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['line'] = array('type' => 'int', 'attr' => 'NOT NULL');
		$this->fields['debug'] = array('type' => 'text', 'attr' => 'NOT NULL');
		$this->fields['href'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['page_id'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields['hash'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields['cnt'] = array('type' => 'int', 'attr' => 'NOT NULL');
		
		$this->unique_fields['hash'] = 'hash';		

		# fields
		$this->fields_form['name'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Ошибка', 'mask'=>array('filter'=>1, 'onetd'=>'Ошибка'));
		$this->fields_form['href'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Страница', 'mask' =>array('filter'=>1));
		$this->fields_form['file'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Фаил', 'mask'=>array('filter'=>1));
		$this->fields_form['line'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Строка', 'mask'=>array('filter'=>1, 'onetd'=>'close'));
		$this->fields_form['debug'] = array('type' => 'textarea', 'readonly'=>1, 'caption' => 'Текст ошибки', 'mask'=>array('fview'=>1,'filter'=>1));
		$this->fields_form['page_id'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'PAGE_ID','mask'=>array('sort'=>1,'filter'=>1));
		$this->fields_form['mf_timecr'] = array('type' => 'date', 'readonly'=>1, 'caption' => 'Дата', 'mask' =>array('sort'=>1,'filter'=>1));
		$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'IP','mask'=>array('sort'=>1,'filter'=>1));
		$this->fields_form['creater_id'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'User','mask'=>array('sort'=>1,'filter'=>1));
		$this->fields_form['cnt'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Повторы', 'mask'=>array('sort'=>1));
		
		foreach ($this->_CFG['_error'] as $k=>$r) {
			$this->_enum['err_type'][$k] = $r['type'];
		}
		
		$this->bugs = array();

		$this->_unique['name'] = 'name';
		
		$this->ordfield = 'active DESC, mf_timecr DESC';
		
		observer::register_observer($this, 'insert2bd', 'shutdown_function');
	}
	
	function insert2bd()
	{
		if (!empty($this->bugs)) {
			if (isset($_SESSION['user']['id']))
				$creater_id = $_SESSION['user']['id'];
			else
				$creater_id = '';
				
			if (isset($_SERVER['REMOTE_ADDR']))
				$mf_ipcreate = ip2long($_SERVER['REMOTE_ADDR']);
			else
				$mf_ipcreate =	0;
			
			foreach ($this->bugs as $r) {
				$r['creater_id'] = $creater_id;
				$r['mf_ipcreate'] = $mf_ipcreate;
				$query_val[] = '("'.implode('","',$r).'")';
			}
			
			$result = $this->SQL->execSQL('INSERT INTO `'.$this->tablename.'` 
			(err_type,name,file,line,debug,href,page_id,hash,mf_timecr,cnt,creater_id,mf_ipcreate) 
			VALUES '.implode(',', $query_val).'
			ON DUPLICATE KEY UPDATE cnt = cnt+1, active=1');
		}
	}
	
	function add_bug($errno, $errstr, $errfile, $errline, $debug) {
		global $PGLIST;
		
		$hash = md5($errno.$errstr.$errfile.$errline.$_SERVER['REQUEST_URI']);
		
		if (!isset($this->bugs[$hash])) {		
						
			$this->bugs[$hash] = array(
				'err_type' => $errno,
				'name' => mysql_real_escape_string($errstr),
				'file' => mysql_real_escape_string($errfile), 
				'line' => mysql_real_escape_string($errline),
				'debug' => mysql_real_escape_string($debug),
				'href' => mysql_real_escape_string($_SERVER['REQUEST_URI']),
				'page_id' => $PGLIST->id,
				'hash' => $hash,
				'mf_timecr' => time(),
				'cnt' => 1,
			);
		}
	}

}

?>

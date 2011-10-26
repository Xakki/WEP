<?php
class bug_class extends kernel_extends {

	protected function _create_conf() {
		parent::_create_conf();
	}

	function _set_features() {
		if (!parent::_set_features()) return false;
//		$this->mf_use_charid = true;
		$this->mf_timecr = true;
		$this->mf_ipcreate = true;
		$this->prm_add = false;
		##$this->prm_del = false;
		$this->mf_actctrl = true;
		$this->mf_statistic = false;
		$this->mf_actctrl = true; // поле active
		$this->cf_reinstall = true;
		
		$this->singleton = true;
		
		$this->caption = 'Отладчик';
		$this->ver = '0.0.1';
		$this->default_access = '|0|';
		return true;
	}

	function _create() {
		parent::_create();

		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'min' => '1');	
		$this->fields['err_type'] = array('type' => 'int', 'width' => 6, 'attr' => 'NOT NULL');
		$this->fields['file'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['line'] = array('type' => 'int', 'width' => 8, 'attr' => 'NOT NULL');
		$this->fields['debug'] = array('type' => 'text', 'attr' => 'NOT NULL');
		$this->fields['href'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['page_id'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields['hash'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields['cnt'] = array('type' => 'int', 'width' => 8, 'attr' => 'NOT NULL');

		$this->unique_fields['hash'] = 'hash';
		//$this->unique_fields['name'] = 'name';

		$this->ordfield = 'active DESC, mf_timecr DESC';
		
		$params = array(
			'obj' => $this,
			'func' => 'push',
		);
		observer::register_observer($params, 'shutdown_function');
	}


	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Ошибка', 'mask'=>array('filter'=>1, 'onetd'=>'Ошибка'));
		$this->fields_form['href'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Страница', 'mask' =>array('filter'=>1));
		$this->fields_form['file'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Файл', 'mask'=>array('filter'=>1));
		$this->fields_form['line'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Строка', 'mask'=>array('filter'=>1, 'onetd'=>'close'));
		$this->fields_form['debug'] = array('type' => 'ckedit', 'caption' => 'Текст ошибки',
			'mask'=>array('fview'=>1,'filter'=>1),
			'paramedit'=>array('toolbarStartupExpanded'=>'false'));
		$this->fields_form['page_id'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'PAGE_ID','mask'=>array('sort'=>1,'filter'=>1));
		$this->fields_form['mf_timecr'] = array('type' => 'date', 'readonly'=>1, 'caption' => 'Дата', 'mask' =>array('sort'=>1,'filter'=>1));
		$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'IP','mask'=>array('sort'=>1,'filter'=>1));
		$this->fields_form[$this->mf_createrid] = array('type' => 'text', 'readonly'=>1, 'caption' => 'User','mask'=>array('sort'=>1,'filter'=>1));
		$this->fields_form['cnt'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Повторы', 'mask'=>array('sort'=>1));
	
		foreach ($this->_CFG['_error'] as $k=>$r) {
			$this->_enum['err_type'][(int)$k] = $r['type'];
		}
	}

	function push() {
		if(count($GLOBALS['_ERR']) and $this->SQL->ready) {
			global $PGLIST;
			$bugs = $query_val = array();
			$keys = false;

			if (isset($_SESSION['user']['id']))
				$creater_id = $_SESSION['user']['id'];
			else
				$creater_id = 0;
				
			if (isset($_SERVER['REMOTE_ADDR']))
				$mf_ipcreate = sprintf("%u",ip2long($_SERVER['REMOTE_ADDR']));
			else
				$mf_ipcreate =	0;

			$href = $this->SqlEsc($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			

			foreach ($GLOBALS['_ERR'] as $err) foreach ($err as $r) {
				if(isset($this->_CFG['wep']['bug_hunter'][$r['errno']])) {
					$hash = md5($r['errno'].$r['errstr'].$r['errfile'].$r['errline'].$_SERVER['REQUEST_URI']);
					if(!isset($query_val[$hash])) {
						$bugs = array(
							'hash' => $hash,
							$this->mf_createrid => $creater_id,
							$this->mf_ipcreate => $mf_ipcreate,
							$this->mf_timecr => $this->_CFG['time'],
							'err_type' => $r['errno'],
							'name' => $this->SqlEsc($r['errstr']),
							'file' => $this->SqlEsc($r['errfile']),
							'line' => $this->SqlEsc($r['errline']),
							'debug' => $this->SqlEsc($r['debug']),
							'href' => $href,
							'cnt' => 1,
						);
						if($this->_CFG['_F']['adminpage'])
							$bugs['page_id'] = ' -Админка- ';
						elseif(isset($PGLIST->id))
							$bugs['page_id'] = $PGLIST->id;

						$query_val[$hash] = '("'.implode('","',$bugs).'")';
					}
				}
			}
			if(count($query_val)) {
				$keys = array_keys($bugs);
				$result = $this->SQL->execSQL('INSERT INTO `'.$this->tablename.'` 
					('.implode(',', $keys).') VALUES '.implode(',', $query_val).'
					ON DUPLICATE KEY UPDATE cnt = cnt+1, active=1');
			}
		}
		return true;
	}

	function insert2bd()
	{
		if (!empty($this->bugs) and isset($this->_CFG['modulprm'][$this->_cl]) and $this->SQL->ready) {
			if (isset($_SESSION['user']['id']))
				$creater_id = $_SESSION['user']['id'];
			else
				$creater_id = 0;
				
			if (isset($_SERVER['REMOTE_ADDR']))
				$mf_ipcreate = sprintf("%u",ip2long($_SERVER['REMOTE_ADDR']));
			else
				$mf_ipcreate =	0;
			$keys = false;
			foreach ($this->bugs as $r) {
				$r[$this->mf_createrid] = $creater_id;
				$r['mf_ipcreate'] = $mf_ipcreate;
				$r['mf_timecr'] = $this->_CFG['time'];
				$r['name'] = mysql_real_escape_string($r['name']);
				$r['file'] = mysql_real_escape_string($r['file']);
				$r['line'] = mysql_real_escape_string($r['line']);
				$r['debug'] = mysql_real_escape_string($r['debug']);
				$r['href'] = mysql_real_escape_string($r['href']);
				$query_val[] = '("'.implode('","',$r).'")';
				if(!$keys)
					$keys = array_keys($r);
			}
			$result = $this->SQL->execSQL('INSERT INTO `'.$this->tablename.'` 
			('.implode(',', $keys).') VALUES '.implode(',', $query_val).'
			ON DUPLICATE KEY UPDATE cnt = cnt+1, active=1');
		}
	}
	
	function add_bug($errno, $errstr, $errfile, $errline, $debug) {
		global $PGLIST;
		
		$hash = md5($errno.$errstr.$errfile.$errline.$_SERVER['REQUEST_URI']);
		
		if (!isset($this->bugs[$hash])) {		
						
			$this->bugs[$hash] = array(
				'err_type' => $errno,
				'name' => $errstr,
				'file' => $errfile, 
				'line' => $errline,
				'debug' => $debug,
				'href' => $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
				'hash' => $hash,
				'cnt' => 1,
				'page_id'=>''
			);
			if($this->_CFG['_F']['adminpage'])
				$this->bugs[$hash]['page_id'] = ' -Админка- ';
			elseif(isset($PGLIST->id))
				$this->bugs[$hash]['page_id'] = $PGLIST->id;
		}else
			$this->bugs[$hash]['cnt'] ++;
	}

}
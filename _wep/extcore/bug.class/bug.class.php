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
		
		$this->ordfield = 'mf_timecr DESC';
	}
	
	function add_bug($errno, $errstr, $errfile, $errline, $debug) {
		global $PGLIST;
		
		$hash = md5($errno.$errstr.$errfile.$errline.$_SERVER['REQUEST_URI']);
		
		if (!isset($this->bugs[$hash])) {
			$this->bugs[$hash] = true;

			$result = $this->SQL->execSQL('SELECT count(id) FROM `'.$this->tablename.'` WHERE hash="'.$hash.'"');
			list($cnt) = $result->fetch_array(MYSQL_NUM);
			if ($cnt == 0) {
				$this->fld_data = array(
					'err_type' => $errno,
					'name' => mysql_real_escape_string($errstr),
					'file' => mysql_real_escape_string($errfile), 
					'line' => mysql_real_escape_string($errline),
					'debug' => mysql_real_escape_string($debug),
					'href' => mysql_real_escape_string($_SERVER['REQUEST_URI']),
					'page_id' => $PGLIST->id,
					'hash' => $hash,
					'cnt' => 1,
				);
				$this->_add();
			}
			else {
				$result = $this->SQL->execSQL('UPDATE `'.$this->tablename.'` SET cnt=cnt+1 WHERE hash="'.$hash.'"');
			}
		}
		
	}

}

?>

<?
		$out = array();
		
		// синонимы для типов полей
		$alias_types = array(
			'TINYINT(1)' => 'BOOL',
		);
		
		// типы полей, число - это значение, которое запишется в базу по умолчанию, если не указывать ширину явно
		// false - означает, что для данного типа поля в mysql ширина не указывается
		$types_width = array(
			'TINYBLOB' => false,
			'TINYTEXT' => false,
			'BLOB' => false,
			'TEXT' => false,
			'MEDIUMBLOB' => false,
			'MEDIUMTEXT' => false,
			'LONGBLOB' => false,
			'LONGTEXT' => false,
			'DATE' => false,
			'DATETIME' => false,
			'TIMESTAMP' => false,
			'TIME' => false,
			'FLOAT' => false,
			'DOUBLE' => false,
			'PRECISION' => false,
			'REAL' => false,

			
			'INT' => 11,
			'INTEGER ' => 11,
			'VARCHAR' => 255,
		);
		
		// типы полей, в которых нет атрибута default
		$types_without_default = array(
			'TINYTEXT' => true,
			'TEXT' => true,
			'MEDIUMTEXT' => true,
			'LONGTEXT' => true,
		);
		
		$result = $this->SQL->execSQL('SHOW TABLES LIKE \''.$this->tablename.'\'');// checking table exist
		if ($result->err) exit($this->getMess('_big_err'));
		if (!$result->num_rows()) {
			if($this->_install())
				return array($this->tablename => array('err'=>$this->getMess('_install_err')));
			return array();
		}
		
		$query = array();
		
		if(isset($this->fields))
			foreach($this->fields as $key => $param) {				
				if (stristr($param['attr'], 'default')) {
					$query[$key][2] = 'Ненужный пар-р default в ключе attr в классе '.get_class($this).' в поле '.$key;
				}
				
				if (
					isset($param['default']) && 
					isset($types_without_default[strtoupper($param['type'])]) && 
					$types_without_default[strtoupper($param['type'])] === true
					) 
				{
//					$mess[] = 'Ненужный пар-р default в классе '.get_class($this).' в поле '.$fldname.' (Для типов полей '.$this->fields[$fldname]['type'].' указывать default необязательно.';
					$query[$key][2] = 'Ненужный пар-р default в классе '.get_class($this).' в поле '.$key.' (Для типов полей '.$param['type'].' указывать default необязательно.';
					unset($this->fields[$key]['default']);
				}
			}

		$result = $this->SQL->execSQL('SHOW COLUMNS FROM `'.$this->tablename.'`');		
		while (list($fldname, $fldtype, $null, $key, $default, $extra) = $result->fetch_array(MYSQL_NUM)) 
		{
			$fldtype = strtoupper($fldtype);
			$null = strtoupper($null);
			$key = strtoupper($key);
			$extra = strtoupper($extra);
			
			if (isset($this->fields[$fldname])) {
				$this->fields[$fldname]['inst'] = '1';
							
				$tmp_type = strtoupper($this->fields[$fldname]['type']);
				if (isset($this->fields[$fldname]['width'])) 
				{
					if (isset($types_width[$tmp_type]) && $types_width[$tmp_type] === false)
					{
//						trigger_error('В классе '.get_class($this).', для поля '.$fldname.' указана ширина. (Для типов полей '.$this->fields[$fldname]['type'].' указывать ширину необязательно.', E_USER_NOTICE);
						unset($this->fields[$fldname]['width']);
					}
				}
				else
				{
					if (isset($types_width[$tmp_type]) && $types_width[$tmp_type] !== false) {
						$this->fields[$fldname]['width'] = $types_width[$tmp_type];
					}
				}
							
				$types = array();
				$types[] = $fldtype;
				if (isset($alias_types[$fldtype]))
					$types[] = $alias_types[$fldtype];
				
				$table_properties = array();
				$table_properties_up_case = array();
				$i = 0;
				foreach ($types as $type)
				{
					$table_properties[$i] = '`'.$fldname.'` '.$type;
		
					if ($type != 'TIMESTAMP') {
						if ($null == 'YES') {
							if (strstr(strtoupper($this->fields[$fldname]['attr']), 'NULL'))
								$table_properties[$i] .= ' NULL';
						}
						else
							$table_properties[$i] .= ' NOT NULL';
						if ($default !== NULL)
							$table_properties[$i] .= ' DEFAULT \''.$default.'\'';
						if ($extra != '')
							$table_properties[$i] .= ' '.$extra;
					}
					$table_properties_up_case[$i] = str_replace(array('"',"'"), array('',''),trim(strtoupper($table_properties[$i])));
					$i++;
				}
							
				if (!in_array(str_replace(array('"',"'"), array('',''),trim(strtoupper($this->_fldformer($fldname, $this->fields[$fldname])))), $table_properties_up_case)) {
					$query[$fldname][0] = 'ALTER TABLE `'.$this->tablename.'` CHANGE `'.$fldname.'` '.$this->_fldformer($fldname, $this->fields[$fldname]);
					$query[$fldname][1] = $table_properties[0];
					
					
//					$query[] = 'ALTER TABLE `'.$this->tablename.'` CHANGE `'.$fldname.'` '.$this->_fldformer($fldname, $this->fields[$fldname]).' ('.$table_properties[0].')';
				}
				
//				if (isset($this->fields[$fldname]['width'])) {
//					if ($this->fields[$fldname]['type'].'('.$this->fields[$fldname]['width'].')' != $type) {
//						$query[] = 'ALTER TABLE `'.$this->tablename.'` CHANGE `'.$fldname.'` `'.$fldname.'` '.$this->fields[$fldname]['type'].'('.$this->fields[$fldname]['width'].') NOT NULL';
//					}
//				}
				
			}
			elseif (isset($this->attaches[$fldname]))
				$this->attaches[$fldname]['inst'] = '1';
			elseif (isset($this->memos[$fldname]))
				$this->memos[$fldname]['inst'] = '1';
			else $query[$fldname][0] = 'ALTER TABLE `'.$this->tablename.'` DROP `'.$fldname.'`';

		}

		if(isset($this->fields))
			foreach($this->fields as $key => $param) {				
				if (!isset($param['inst'])) {
					$query[$key][0] = 'ALTER TABLE `'.$this->tablename.'` ADD '.$this->_fldformer($key, $param);
				}
			}

		if(isset($this->attaches))
			foreach($this->attaches as $key => $param) 
			{
				if (!isset($param['inst'])) 
					$query[$key][0] = 'ALTER TABLE `'.$this->tablename.'` ADD '.$this->_fldformer($key, $this->attprm);
				if ($this->_checkdir($this->getPathForAtt($key))) { 
					$out[$this->tablename]['err'] = $this->getMess('_checkdir error');
					return $out;
				}
			}	

		if(isset($this->memos))
			foreach($this->memos as $key => $param) 
			{
			//	if (!$param['inst']) $query[] = 'ADD '.$this->_fldformer($key, $this->mmoprm);
				if ($this->_checkdir($this->getPathForMemo($key))) {
					$out[$this->tablename]['err'] = $this->getMess('_recheck_err');
					return $out;
				}
			}
		
		if(isset($this->fields['id']) and !isset($this->fields['id']['inst']))
			$query['id::pri'][0] = 'ALTER TABLE `'.$this->tablename.'` ADD PRIMARY KEY(id)';

//		if (count($query))
//		{
//			$this->SQL->execSQL('ALTER TABLE `'.$this->tablename.'` '. implode(',', $query));
//		}
	
		$indexlist = array();
		$result = $this->SQL->execSQL('SHOW INDEX FROM `'.$this->tablename.'`');
		while ($data = $result->fetch_array(MYSQL_NUM)) 
			$indexlist[$data[2]]=$data[2];

//		$query = array();
		if(count($this->index_fields))
			foreach($this->index_fields as $k=>$r)
				if (!isset($indexlist[$k])){
					$query[$k.'::ind'][0] = 'CREATE INDEX `'.$r.'` ON `'.$this->tablename.'` (`'.$k.'`)';
					$indexlist[$k] = $k;
				}

		if ($this->owner && !isset($indexlist[$this->owner_name])) 
			$query[$k.'::ind'][0] = 'CREATE INDEX '.$this->owner_name.' ON `'.$this->tablename.'` ('.$this->owner_name.')';

		if ($this->mf_istree && !isset($indexlist['parent_id']))
			$query[$k.'::ind'][0] = 'CREATE INDEX `parent_id` ON `'.$this->tablename.'` (parent_id)';

		if ($this->mf_actctrl && !isset($indexlist['active']))
			$query[$k.'::ind'][0] = 'CREATE INDEX `active` ON `'.$this->tablename.'` (active)';

		if ($this->mf_ordctrl && !isset($indexlist['ordind']))
			$query[$k.'::ind'][0] = 'CREATE INDEX `ordind` ON `'.$this->tablename.'` (ordind)';
			
		if(isset($this->unique_fields) and count($this->unique_fields)){
			foreach($this->unique_fields as $k=>$r) {
				if (!isset($indexlist[$k])) {
					if(is_array($r)) $r = implode(',',$r);
					$query[$k.'::uniq'][0] = 'ALTER TABLE `'.$this->tablename.'` ADD UNIQUE KEY '.$k.' ('.$r.')';
				}
			}
		}
		
//		if (count($query)) {
//			foreach($query as $rr)
//				$this->SQL->execSQL($rr);
//			$this->SQL->execSQL('OPTIMIZE TABLE `'.$this->tablename.'`');
//		}
//		if(isset($this->_cl))
//			$this->SQL->execSQL('UPDATE `'.$this->_CFG['sql']['dbpref'].'modulprm` SET `ver`="'.$this->ver.'" WHERE `id`="'.$this->_cl.'"');

		$out[$this->tablename]['list_query'] = $query;

		return $out;
//		return true;


?>

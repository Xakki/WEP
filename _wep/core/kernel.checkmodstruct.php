<?
		$result = $this->SQL->execSQL('SHOW TABLES LIKE \''.$this->tablename.'\'');// checking table exist
		if ($result->err) exit('error');
		if (!$result->num_rows()) return $this->_install();

		$result = $this->SQL->execSQL('SHOW COLUMNS FROM `'.$this->tablename.'`');
		$query = array();
		while (list($fldname, $fldtype, $null, $key, $default, $extra) = $result->fetch_array(MYSQL_NUM)) 
		{
			$fldtype = strtoupper($fldtype);
			$null = strtoupper($null);
			$key = strtoupper($key);
			$extra = strtoupper($extra);
			
			if (isset($this->fields[$fldname])) {
				$this->fields[$fldname]['inst'] = '1';
				
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
					'ENUM' => false,
					'SET' => false,
					'DATE' => false,
					'DATETIME' => false,
					'TIMESTAMP' => false,
					'TIME' => false,
					'FLOAT' => false,
					'DOUBLE' => false,
					'PRECISION' => false,
					'REAL' => false,
	//				'DECIMAL' => false,
					
					'INT' => 10,
					'INTEGER ' => 11,
					'VARCHAR' => 255,
				);
				
				if (stristr($this->fields[$fldname]['attr'], 'default'))
					trigger_error('В массиве fields (класс '.get_class($this).' поле '.$fldname.' ) в ключе attr ненужный параметр default');
					
				if (
					isset($this->fields[$fldname]['width']) && 
					isset($types_width[strtoupper($this->fields[$fldname]['type'])]) && 
					$types_width[strtoupper($this->fields[$fldname]['type'])] === false) 
				{
					trigger_error('В классе '.get_class($this).', для поля '.$fldname.' указана ширина. (Для типов полей '.$this->fields[$fldname]['type'].' указывать ширину необязательно.', E_USER_NOTICE);
				}
				
				
				$types = array();
				$types[] = $fldtype;
				if (isset($alias_types[$fldtype]))
					$types[] = $alias_types[$fldtype];
				
				
				$table_properties = array();
				$i = 0;
				foreach ($types as $type)
				{
					$table_properties[$i] = '`'.$fldname.'`';

					if (!isset($this->fields[$fldname]['width'])) {
						$ltmp_pos = strpos($type, '(');
						$rtmp_pos = strpos($type, ')');

						if ($ltmp_pos !== false && $rtmp_pos !== false) {
							$tmp_type = substr($type, 0,$ltmp_pos);
							$tmp_width = substr($type, $ltmp_pos+1, $rtmp_pos-$ltmp_pos-1);
							$tmp_after_width_side = substr($type, $rtmp_pos+1);
							
							if (isset($types_width[$tmp_type]) && $types_width[$tmp_type] == $tmp_width) {
								$type = $tmp_type.$tmp_after_width_side;
							}
						}
					}
					$table_properties[$i] .= ' '.$type;
					
					if ($type != 'TIMESTAMP') {
						if ($null == 'YES')
							$table_properties[$i] .= ' NULL';
						else
							$table_properties[$i] .= ' NOT NULL';
						if ($default !== NULL)
							$table_properties[$i] .= ' DEFAULT \''.$default.'\'';
						if ($extra != '')
							$table_properties[$i] .= ' '.$extra;
					}
					$table_properties[$i] = strtoupper($table_properties[$i]);
					$i++;
				}
				

				if (!in_array(strtoupper($this->_fldformer($fldname, $this->fields[$fldname])), $table_properties)) {
					$query[] = 'ALTER TABLE `'.$this->tablename.'` CHANGE `'.$fldname.'` '.$this->_fldformer($fldname, $this->fields[$fldname]);
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
			else $query[] = 'ALTER TABLE `'.$this->tablename.'` DROP `'.$fldname.'`';

		}

		if(isset($this->fields))
			foreach($this->fields as $key => $param)
				if (!isset($param['inst'])) {
					$query[] = 'ALTER TABLE `'.$this->tablename.'` ADD '.$this->_fldformer($key, $param);
				}

		if(isset($this->attaches))
			foreach($this->attaches as $key => $param) 
			{
				if (!isset($param['inst'])) 
					$query[] = 'ADD '.$this->_fldformer($key, $this->attprm);
				if ($this->_checkdir($this->getPathForAtt($key))) 
					return array('err' => '_checkdir error');
			}	

		if(isset($this->memos))
			foreach($this->memos as $key => $param) 
			{
			//	if (!$param['inst']) $query[] = 'ADD '.$this->_fldformer($key, $this->mmoprm);
				if ($this->_checkdir($this->getPathForMemo($key))) 
					return array('err' => $this->getMess('_recheck_err'));
			}
		
		if(isset($this->fields['id']) and !isset($this->fields['id']['inst']))
			$query[] = 'ALTER TABLE `'.$this->tablename.'` ADD PRIMARY KEY(id)';

		if (count($query))
		{
//			$this->SQL->execSQL('ALTER TABLE `'.$this->tablename.'` '. implode(',', $query));
		}
	
		$indexlist = array();
		$result = $this->SQL->execSQL('SHOW INDEX FROM `'.$this->tablename.'`');
		while ($data = $result->fetch_array(MYSQL_NUM)) 
			$indexlist[$data[2]]=$data[2];

//		$query = array();
		if(count($this->index_fields))
			foreach($this->index_fields as $k=>$r)
				if (!isset($indexlist[$k])){
					$query[] = 'CREATE INDEX `'.$r.'` ON `'.$this->tablename.'` (`'.$k.'`)';
					$indexlist[$k] = $k;
				}

		if ($this->owner && !isset($indexlist[$this->owner_name])) 
			$query[] = 'CREATE INDEX '.$this->owner_name.' ON `'.$this->tablename.'` ('.$this->owner_name.')';

		if ($this->mf_istree && !isset($indexlist['parent_id']))
			$query[] = 'CREATE INDEX `parent_id` ON `'.$this->tablename.'` (parent_id)';

		if ($this->mf_actctrl && !isset($indexlist['active']))
			$query[] = 'CREATE INDEX `active` ON `'.$this->tablename.'` (active)';

		if ($this->mf_ordctrl && !isset($indexlist['ordind']))
			$query[] = 'CREATE INDEX `ordind` ON `'.$this->tablename.'` (ordind)';
		
		if (count($query)) {
//			foreach($query as $rr)
//				$this->SQL->execSQL($rr);
//			$this->SQL->execSQL('OPTIMIZE TABLE `'.$this->tablename.'`');
		}
//		if(isset($this->_cl))
//			$this->SQL->execSQL('UPDATE `'.$this->_CFG['sql']['dbpref'].'modulprm` SET `ver`="'.$this->ver.'" WHERE `id`="'.$this->_cl.'"');



		return array('list_query' => $query);
//		return 0;


?>

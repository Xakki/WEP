<?
		
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
			'FLOAT' => '8,2',
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
		if ($result->err) return array($this->tablename => array(array('err'=>$this->getMess('_big_err'))));
		if (!$result->num_rows()) {
			if (isset($_POST['sbmt'])) {
				if(!$this->_install())
					return array($this->tablename => array(array('err'=>$this->getMess('_install_err'))));
				else
					return array($this->tablename => array(array('ok'=>$this->getMess('_install_ok'))));
			}
			else
				return array($this->tablename => array(array('ok'=>$this->getMess('_install_info'))));
		}
		
		$out = array();
		
		if(isset($this->fields))
			foreach($this->fields as $key => $param) {				
				if (stristr($param['attr'], 'default')) {
					$out[$key]['err'][] = 'Ненужный пар-р default в ключе attr';
				}
				
				if (
					isset($param['default']) && 
					isset($types_without_default[strtoupper($param['type'])]) && 
					$types_without_default[strtoupper($param['type'])] === true
					) 
				{
					$out[$key]['err'][] = 'Ненужный пар-р `default` (Для типов полей '.$param['type'].' указывать `default` необязательно.';
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
						unset($this->fields[$fldname]['width']); // чистим от ненужного парметра
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
						else {
							$table_properties[$i] .= ' NOT NULL';
							//if(!isset($this->fields[$fldname]['default']) and $tmp_type=='VARCHAR')
							//	$this->fields[$fldname]['default'] = '';
						}
						if ($default !== NULL) {
							$table_properties[$i] .= ' DEFAULT \''.$default.'\'';
						}
						if ($extra != '')
							$table_properties[$i] .= ' '.$extra;
					}
					$table_properties_up_case[$i] = str_replace(array('"',"'"), array('',''),trim(strtoupper($table_properties[$i])));
					$i++;
				}
				$temp_fldformer = trim($this->_fldformer($fldname, $this->fields[$fldname]));
				if (isset($this->fields[$fldname]['type']) and !in_array(str_replace(array('"',"'"), array('',''),strtoupper($temp_fldformer)), $table_properties_up_case)) {
					$out[$fldname]['newquery'] = 'ALTER TABLE `'.$this->tablename.'` CHANGE `'.$fldname.'` '.$temp_fldformer;
					$out[$fldname]['oldquery'] = $table_properties[0];
//					$out[] = 'ALTER TABLE `'.$this->tablename.'` CHANGE `'.$fldname.'` '.$this->_fldformer($fldname, $this->fields[$fldname]).' ('.$table_properties[0].')';
				}
				
//				if (isset($this->fields[$fldname]['width'])) {
//					if ($this->fields[$fldname]['type'].'('.$this->fields[$fldname]['width'].')' != $type) {
//						$out[] = 'ALTER TABLE `'.$this->tablename.'` CHANGE `'.$fldname.'` `'.$fldname.'` '.$this->fields[$fldname]['type'].'('.$this->fields[$fldname]['width'].') NOT NULL';
//					}
//				}
				
			}
			elseif (isset($this->attaches[$fldname]))
				$this->attaches[$fldname]['inst'] = '1';
			elseif (isset($this->memos[$fldname]))
				$this->memos[$fldname]['inst'] = '1';
			else $out[$fldname]['newquery'] = 'ALTER TABLE `'.$this->tablename.'` DROP `'.$fldname.'`';

		}

		if(isset($this->fields))
			foreach($this->fields as $key => $param) {		
				if (!isset($param['inst'])) {
					$out[$key]['newquery'] = 'ALTER TABLE `'.$this->tablename.'` ADD '.$this->_fldformer($key, $param);
				}
			}

		if(isset($this->attaches))
			foreach($this->attaches as $key => $param) 
			{
				if (!isset($param['inst'])) 
					$out[$key]['newquery'] = 'ALTER TABLE `'.$this->tablename.'` ADD '.$this->_fldformer($key, $this->attprm);
				if (!$this->_checkdir($this->getPathForAtt($key))) { 
					$out[$key]['err'][] = $this->getMess('_checkdir_error',array($this->getPathForAtt($key)));
				}
				$out['reattach'] = &$this;
			}	

		if(isset($this->memos))
			foreach($this->memos as $key => $param) 
			{
			//	if (!$param['inst']) $out[] = 'ADD '.$this->_fldformer($key, $this->mmoprm);
				if (!$this->_checkdir($this->getPathForMemo($key))) {print_r('******8');
					$out[$key]['err'][] = $this->getMess('_recheck_err');
				}
			}

	
		$indexlist = $uniqlistR = $uniqlist = array();
		$primary = '';
		$result = $this->SQL->execSQL('SHOW INDEX FROM `'.$this->tablename.'`');
		while ($data = $result->fetch_array(MYSQL_NUM)) {
			if($data[2]=='PRIMARY') //только 1 примарикей
				$primary=$data[4];
			elseif(!$data[1]) //!NON_unique
				$uniqlist[$data[2]][$data[4]]=$data[4];
			else
				$indexlist[$data[2]][$data[4]]=$data[4];
		}

		// CREATE PRIMARY KEY
		if(isset($this->fields['id']) and !$primary) {
			$out['id']['index'] = 'ALTER TABLE `'.$this->tablename.'` ADD PRIMARY KEY(id)';
			$primary = 'id';
		}
		// CREATE UNIQ KEY
		$uniqlistR = $uniqlist;
		if(isset($this->unique_fields) and count($this->unique_fields)){
			foreach($this->unique_fields as $k=>$r) {
				if(!is_array($r)) $r = array($r);
				if (!isset($uniqlist[$k])) {// and !isset($uniqlistR[$k])
					foreach($r as $kk=>$rr)
						$uniqlistR[$k][$kk] = $rr;
					$tmp = '';
					if(isset($indexlist[$k]))
						$tmp = 'drop key `'.$k.'`, ';
					if(is_array($r)) $r = implode('`,`',$r);
					$out[$k]['index'] = 'ALTER TABLE `'.$this->tablename.'` '.$tmp.' ADD UNIQUE KEY `'.$k.'` (`'.$r.'`)';

				} else {
					unset($uniqlist[$k]);
				}
			}
		}
		if(count($uniqlist)) {
			foreach($uniqlist as $k=>$r) {
				$out[$k]['index'] = 'ALTER TABLE `'.$this->tablename.'` drop key '.$k.' ';
				unset($uniqlistR[$k]);
			}
		}
		//$uniqlistR - Действующие уник ключи в итоге

		// CREATE INDEX KEY
		if ($this->owner)
			$this->index_fields[$this->owner_name] = $this->owner_name;
		if ($this->mf_istree)
			$this->index_fields['parent_id'] = 'parent_id';
		if ($this->mf_actctrl)
			$this->index_fields['active'] = 'active';
		if ($this->mf_ordctrl)
			$this->index_fields['ordind'] = 'ordind';
		if(count($this->index_fields))
			foreach($this->index_fields as $k=>$r) {
				if (!isset($indexlist[$k]) and !isset($uniqlistR[$k])) {
					if(isset($out[$k]['index']))
						$out[$k]['index'] .= ', add index `'.$k.'` (`'.$r.'`)';
					else
						$out[$k]['index'] = 'ALTER TABLE `'.$this->tablename.'` add index `'.$k.'` (`'.$r.'`)';
				} else {
					unset($indexlist[$k]);
				}
			}
		if(count($indexlist)) {
			foreach($indexlist as $k=>$r) {
				if(isset($out[$k]['index']))
					$out[$k]['index'] = ', drop key '.$k.' ';
				else
					$out[$k]['index'] = 'ALTER TABLE `'.$this->tablename.'` drop key '.$k.' ';
			}
		}

		if(count($out))
			$out = array($this->tablename=>$out);
		if(count($this->childs))
			foreach($this->childs as $k=>&$r) {
				$temp = $r->_checkmodstruct();
				if($temp and count($temp))
					$out = array_merge($out,$temp);
			}
		if (isset($_POST['sbmt'])) {
			$this->SQL->execSQL('OPTIMIZE TABLE `'.$this->tablename.'`');
			if(isset($this->_cl) and $this->_cl!='modulprm' and $this->_cl!='modulgrp') {
				_new_class('modulprm',$MODULPRM,$this->null, true);
				$this->SQL->execSQL('UPDATE `'.$MODULPRM->tablename.'` SET `ver`="'.$this->ver.'" WHERE `id`="'.$this->_cl.'"');
			}
		}
		return $out;
//		return true;


?>

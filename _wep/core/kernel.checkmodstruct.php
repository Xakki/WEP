<?
		$result = $this->SQL->execSQL('SHOW TABLES LIKE \''.$this->tablename.'\'');// checking table exist
		if ($result->err) exit('error');
		if (!$result->num_rows()) return $this->_install();

		$result = $this->SQL->execSQL('SHOW COLUMNS FROM `'.$this->tablename.'`');
		$query = array();
		while (list($fldname) = $result->fetch_array(MYSQL_NUM)) 
		{
			if (isset($this->fields[$fldname])) 
				$this->fields[$fldname]['inst'] = '1';
			elseif (isset($this->attaches[$fldname]))
				$this->attaches[$fldname]['inst'] = '1';
			elseif (isset($this->memos[$fldname]))
				$this->memos[$fldname]['inst'] = '1';
			//else $query[] = 'GDROP '.$fldname;
		}

		if(isset($this->fields))
			foreach($this->fields as $key => $param)
				if (!isset($param['inst'])) {
					$query[] = 'ADD '.$this->_fldformer($key, $param);
				}

		if(isset($this->attaches))
			foreach($this->attaches as $key => $param) 
			{
				if (!isset($param['inst'])) 
					$query[] = 'ADD '.$this->_fldformer($key, $this->attprm);
				if ($this->_checkdir($this->getPathForAtt($key))) 
					return 1;
			}	

		if(isset($this->memos))
			foreach($this->memos as $key => $param) 
			{
			//	if (!$param['inst']) $query[] = 'ADD '.$this->_fldformer($key, $this->mmoprm);
				if ($this->_checkdir($this->getPathForMemo($key))) 
					return 1;
			}
		
		if(isset($this->fields['id']) and !isset($this->fields['id']['inst']))
			$query[] = 'ADD PRIMARY KEY(id)';

		if (count($query))
		{
			$this->SQL->execSQL('ALTER TABLE `'.$this->tablename.'` '. implode(',', $query));
		}
	
		$indexlist = array();
		$result = $this->SQL->execSQL('SHOW INDEX FROM `'.$this->tablename.'`');
		while ($data = $result->fetch_array(MYSQL_NUM)) 
			$indexlist[$data[2]]=$data[2];

		$query = array();
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
			foreach($query as $rr)
				$this->SQL->execSQL($rr);
			$this->SQL->execSQL('OPTIMIZE TABLE `'.$this->tablename.'`');
		}
		if(isset($this->_cl))
			$this->SQL->execSQL('UPDATE `'.$_CFG['sql']['dbpref'].'modulprm` SET `ver`="'.$this->ver.'" WHERE `id`="'.$this->_cl.'"');

		return 0;


?>
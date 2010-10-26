<?
		// contruct of query
		$fld = array();
		if(count($this->fields))
			foreach($this->fields as $key => $param)
				$fld[]= $this->_fldformer($key, $param);
		if(count($this->attaches))
			foreach($this->attaches as $key => $param)
				$fld[]= $this->_fldformer($key, $this->attprm);

/*			foreach($this->memos as $key => $param) 
				$fld[]= $this->_fldformer($key, $this->mmoprm);
*/
		$fld[] = 'PRIMARY KEY(id)';

		if ($this->owner) 
			$fld[] = ($this->owner_unique?'UNIQUE ':'').'KEY('.$this->owner_name.')';

		if ($this->mf_istree) 
			$fld[] = 'KEY(parent_id)';

		if ($this->mf_actctrl) 
			$fld[] = 'KEY(active)';

		if(isset($this->_unique) and count($this->_unique)){
			foreach($this->_unique as $k=>$r) {
				if(is_array($r)) $r = implode(',',$r);
				$fld[] = 'UNIQUE KEY '.$k.' ('.$r.')';
			}
		}

		// to execute query
		$result = $this->SQL->execSQL('CREATE TABLE `'.$this->tablename.'` ('.implode(',',$fld).') ENGINE=MyISAM DEFAULT CHARSET='.$this->_CFG['sql']['setnames'].' COMMENT = "'.$this->ver.'"');
		if($result->err) return $this->_message($result->err);
		$this->_message('Table `'.$this->tablename.'` installed.',3);

		if(isset($this->_cl))
			$this->SQL->execSQL('UPDATE `'.$this->_CFG['sql']['dbpref'].'modulprm` SET `ver`="'.$this->ver.'" WHERE `id`="'.$this->_cl.'"');

		if(count($this->def_records)) $this->_insertDefault();

		return 0;

?>
<?
		$result = $this->SQL->execSQL('SHOW TABLES LIKE \''.$this->tablename.'\'');// checking table exist
		//if($result->err) return array($this->tablename => array(array('err'=>$this->getMess('_big_err'))));
		if($result->num_rows()) {
			return false;
		}

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

		if(isset($this->unique_fields) and count($this->unique_fields)){
			foreach($this->unique_fields as $k=>$r) {
				if(is_array($r)) $r = implode(',',$r);
				$fld[] = 'UNIQUE KEY '.$k.' ('.$r.')';
			}
		}
		if(isset($this->index_fields) and count($this->index_fields)){
			foreach($this->index_fields as $k=>$r) {
				if(!isset($this->unique_fields[$k])) {
					if(is_array($r)) $r = implode(',',$r);
					$fld[] = 'KEY '.$k.' ('.$r.')';
				}
			}
		}
		// to execute query
		$result = $this->SQL->execSQL('CREATE TABLE `'.$this->tablename.'` ('.implode(',',$fld).') ENGINE=MyISAM DEFAULT CHARSET='.$this->_CFG['sql']['setnames'].' COMMENT = "'.$this->ver.'"');
		if($result->err) return false;
		$this->_message('Table `'.$this->tablename.'` installed.',3);
		if(count($this->def_records)) {
			if(!$this->_insertDefault()) {
				$this->SQL->execSQL('DROP TABLE `'.$_this->tablename.'`');
				return false;
			}
		}

		if(isset($this->_cl) and $this->_cl!='modulprm' and $this->_cl!='modulgrp') {
			_new_class('modulprm',$MODULPRM,$this->null, true);
			$this->SQL->execSQL('UPDATE `'.$MODULPRM->tablename.'` SET `ver`="'.$this->ver.'" WHERE `id`="'.$this->_cl.'"');
		}


		return true;

?>

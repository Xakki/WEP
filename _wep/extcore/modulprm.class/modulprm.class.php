<?


class modulprm_class extends kernel_class {

	function _set_features()
	{
		if (!parent::_set_features()) return false;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->mf_use_charid = true;
		$this->mf_istree = true;
		$this->mf_timestamp = true;
		$this->prm_add = false;
		//$this->prm_del = false;
		$this->mf_createrid = false;
		$this->caption = "Модули";
		return true;
	}

	function _create()
	{
		parent::_create();
		
		$this->fields['name'] = array('type' => 'varchar', 'width' => 64,'attr' => 'NOT NULL');
		$this->fields['tablename'] = array('type' => 'varchar', 'width' => 128,'attr' => 'NOT NULL');
		$this->fields['path'] = array('type' => 'varchar', 'width' => 255,'attr' => 'NOT NULL');
		$this->fields['ver'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL');
		$this->fields['typemodul'] = array('type' => 'tinyint', 'width' => 2, 'attr' => 'NOT NULL');

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название');
		$this->fields_form['tablename'] = array('type' => 'text','readonly' => 1, 'caption' => 'Таблица');
		$this->fields_form['path'] = array('type' => 'text','readonly' => 1, 'caption' => 'Путь');
		$this->fields_form['ver'] = array('type' => 'text','readonly' => 1, 'caption' => 'Версия');
		$this->fields_form['typemodul'] = array('type' => 'list', 'listname'=>'typemodul', 'readonly' => 1, 'caption' => 'Описание');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');

		$this->_enum['typemodul'] = array(
			0=>'Системный модуль',
			1=>'Расширенный системный модуль',
			2=>'Модуль',
			5=>'Дочерние модули');

		$this->_enum['modulinc'] = array(
			1=>array('path'=>$this->_CFG['_PATH']['extcore'],'name'=>'WEPext - '),
			3=>array('path'=>$this->_CFG['_PATH']['ext'],'name'=>'EXT - ')
		);

		$this->ordfield = 'typemodul,name';

		$this->create_child('modulgrp');
	}

	public function _checkmodstruct() {
		$check_result = parent::_checkmodstruct();
	

		$this->moduldir = array(); // для детей классов
		$this->def_update_records = array();
		$this->mquery = array();
		$result = $this->SQL->execSQL('SELECT * FROM '.$this->tablename);if ($result->err) return $check_result;
		$this->data = array();
		if(!$result->err)
			while ($row = $result->fetch_array()){
				$this->data[$row['id']] = $row;
			}
		$this->fData = $this->data;
		$dir = dir($this->_CFG['_PATH']['extcore']);
		while (false !== ($entry = $dir->read())) {
			if ($entry[0]!='.' && $entry[0]!='..' && $pos=strpos($entry, '.class')) {
				$entry = substr($entry, 0, $pos);
				if($entry!='' and _modulExists($entry)) {
					$pathm = '1:'.$entry.'.class/'.$entry.'.class.php';
					$this->moduldir[$entry] = '';
					$class_ = NULL;
					if($this->_cl!=$entry) {
						if(_new_class($entry,$class_)) {
							$this->_constr_childs($class_);
							$check_result = array_merge($check_result,$class_->_checkmodstruct());
						}
					} else {
						$class_ = &$this;
						$this->_constr_childs($class_,$pathm);
					}
					if(!isset($this->data[$entry]) and $class_->showinowner) {
						$this->mquery[$entry] = array('id'=>$entry,'name'=>$class_->caption,'parent_id'=>'','tablename'=>$class_->tablename, 'typemodul'=>0,'path'=>$pathm);
					}
					elseif($class_->showinowner) {//if($class_->ver!=$this->data[$entry]['ver'] or $this->_cl==$entry)
						$tmp = $this->data[$entry]; // временная переменная
						if($tmp['parent_id']!='' or $tmp['tablename']!=$class_->tablename or $tmp['typemodul']!='0' or $tmp['path']!=$pathm) {
							// смотрим какие данные нужно менять
							$this->mquery[$entry] = array('id'=>$entry,'parent_id'=>'','tablename'=>$class_->tablename, 'typemodul'=>0,'path'=>$pathm);
						} else
							unset($this->mquery[$entry]);
						unset($this->fData[$entry]); //удаляем, чтоьы потом можно было узнать какие модули отсутствуют
					}
				}
			}
		}
		$dir->close();
		
		$dir = dir($this->_CFG['_PATH']['ext']);
		while (false !== ($entry = $dir->read())) {
			if ($entry[0]!='.' && $entry[0]!='..' && $pos=strpos($entry, '.class')) {
				$entry = substr($entry, 0, $pos);
				if($entry!='' and _modulExists($entry)) { 
					$this->moduldir[$entry] = '';
					$class_ = NULL;
					if(_new_class($entry,$class_)) {
						$pathm = '3:'.$entry.'.class/'.$entry.'.class.php';
						if(!isset($this->data[$entry]) and $class_->showinowner) {
							$this->mquery[$entry] = array('id'=>$entry,'name'=>$class_->caption.' ['.$entry.']', 'parent_id'=>'', 'tablename'=>$class_->tablename, 'typemodul'=>2, 'path'=>$pathm, 'ver'=>$class_->ver);
						}
						elseif($class_->showinowner) { //if($class_->ver!=$this->data[$entry]['ver'])
							$tmp = $this->data[$entry]; // временная переменная
							if($tmp['parent_id']!='' or $tmp['tablename']!=$class_->tablename or $tmp['typemodul']!='2' or $tmp['path']!=$pathm or $tmp['ver']!=$class_->ver) {
								// смотрим какие данные нужно менять
								$this->mquery[$entry] = array('id'=>$entry, 'parent_id'=>'', 'tablename'=>$class_->tablename, 'typemodul'=>2, 'path'=>$pathm, 'ver'=>$class_->ver);
							}else
								unset($this->mquery[$entry]);
							unset($this->fData[$entry]); //удаляем, чтоьы потом можно было узнать какие модули отсутствуют
						} else {
							unset($this->mquery[$entry]);
						}
						$this->_constr_childs($class_,$pathm);
						$check_result = array_merge($check_result,$class_->_checkmodstruct());						
					}
				}
			}
		}
		$dir->close();

		if (isset($_POST['sbmt'])) {
			foreach($this->mquery as $k=>$r) {
				if(!isset($this->data[$k]))
					$q = 'INSERT INTO `'.$this->tablename.'` (`'.implode('`,`', array_keys($r)).'`) VALUES (\''.implode('\',\'', $r).'\')';
				else {
					$q = array();
					foreach($r as $kk=>$rr) {
						if($kk!='id')
							$q[] = '`'.$kk.'`="'.$rr.'"';
					}
					$q = 'UPDATE `'.$this->tablename.'` SET '.implode(', ',$q).' WHERE id="'.$r['id'].'"';
				}
				$result = $this->SQL->execSQL($q);
			}
			$this->def_update_records=array();
			if(count($this->fData)) {
				$result = $this->SQL->execSQL('DELETE FROM `'.$this->tablename.'` WHERE `id` IN ("'.implode('","',array_keys($this->fData)).'")');
			}
		} else {
			if(count($this->fData))
				$check_result[$this->tablename]['']['ok'] = '<span style="color:#4949C9;">Будет удалены записи из табл '.$this->tablename.' ('.implode(',',array_keys($this->fData)).')</span>';
			foreach($this->mquery as $k=>$r)
				$check_result[$this->tablename][$k]['ok'] = '<span style="color:#4949C9;">'.print_r($r,true).'</span>';
		}
		return $check_result;
	}

	function _constr_childs(&$class_,$pathm='') {
		if(count($class_->childs)) {
			foreach($class_->childs as $k=>&$r) {
				$this->moduldir[$k] = $class_->_cl;
				if(!isset($this->data[$k]) and $r->showinowner) {
					$this->mquery[$k] = array('id'=>$k,'name'=>$r->caption.' ['.$k.']','parent_id'=>$class_->_cl,'tablename'=>$r->tablename, 'typemodul'=>5, 'ver'=>$r->ver);
				}
				elseif($r->showinowner) { //if($r->ver!=$this->data[$k]['ver'])
					$tmp = $this->data[$k]; // временная переменная
					if($tmp['parent_id']!=$class_->_cl or $tmp['tablename']!=$r->tablename or $tmp['typemodul']!='5' or $tmp['path']!=$pathm or $tmp['ver']!=$r->ver) {
						// смотрим какие данные нужно менять
						$this->mquery[$k] = array('id'=>$k, 'parent_id'=>$class_->_cl, 'tablename'=>$r->tablename, 'typemodul'=>5, 'ver'=>$r->ver, 'path'=>$pathm);
					} else 
						unset($this->mquery[$k]);
					unset($this->fData[$k]); //удаляем, чтоьы потом можно было узнать какие модули отсутствуют
				}
				$this->_constr_childs($r,$pathm);
			}
		}
		return true;
	}

	function userPrm($ugroup_id=0) {
		$result = $this->SQL->execSQL('SELECT t1.*,t2.access, t2.mname FROM '.$this->tablename.' t1 LEFT Join '.$this->childs['modulgrp']->tablename.' t2 on t2.owner_id=t1.id and t2.ugroup_id='.$ugroup_id.' where t1.active=1 ORDER BY '.$this->ordfield);
		if ($result->err) $this->_message($result->err);
		$this->data = array();
		while ($row = $result->fetch_array()){
			$this->data[$row['id']]['active'] = $row['active'];
			$this->data[$row['id']]['access'] = array_flip(explode('|',trim($row['access'],'|')));
			if($row['mname'])
				$this->data[$row['id']]['name'] = $row['mname'];
			else
				$this->data[$row['id']]['name'] = $row['name'];
			$this->data[$row['id']]['ver'] = $row['ver'];
			$this->data[$row['id']]['typemodul'] = $row['typemodul'];
			$this->data[$row['id']]['path'] = $row['path'];
			$this->data[$row['id']]['tablename'] = $row['tablename'];
		}
	//	print_r($this->data);exit();
		return $this->data;
	}

	function _UpdItemModul($param) {
		$ret = parent::_UpdItemModul($param);
		//if($ret[1]) {
		//	session_unset();
		//}
		return $ret;
	}

}

class modulgrp_class extends kernel_class {

	function _set_features()
	{
		if (!parent::_set_features()) return false;
		$this->mf_timestamp = true;
		$this->prm_add = false;
		$this->prm_del = false;
		$this->mf_createrid = false;
		$this->singleton = false;
		return true;
	}

	function _create()
	{
		parent::_create();
		$this->caption = "Привелегии";

		$this->unique_fields['ou'] = array('owner_id','ugroup_id');
		
		$this->_enum['access'] = array(
			0=>'нет',
			1=>'Чтение (Все)',
			2=>'Чтение (Только свои)',
			3=>'Редактирование (Все)',
			4=>'Редактирование (Только свои)',
			5=>'Удаление (Все)',
			6=>'Удаление (Только свои)',
			7=>'Отключение (Все)',
			8=>'Отключение (Только свои)',
			9=>'Добавление',
			'A'=>'Сортировка',
			'B'=>'Переустановка модуля',
			'C'=>'Переиндексация модуля',
			'D'=>'Настроика модуля',
			'E'=>'Проверка структуры модуля',
			'F'=>'Глобальные настройки сервера'
		);

		//$this->fields['name'] = array('type' => 'varchar', 'width' => 32,'attr' => 'NOT NULL');
		$this->fields['mname'] = array('type' => 'varchar', 'width' => 64,'attr' => 'NOT NULL');
		$this->fields['ugroup_id'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL');
		$this->fields['access'] = array('type' => 'varchar', 'width' => 128,'attr' => 'NOT NULL');

		//$this->fields_form['name'] = array('type' => 'text','readonly' => 1, 'caption' => 'Группа');
		$this->fields_form['owner_id'] = array('type' => 'hidden','readonly' => 1);
		$this->fields_form['ugroup_id'] = array('type' => 'list','readonly' => 1, 'listname'=>array('class'=>'ugroup'), 'caption' => 'Группа');
		$this->fields_form['mname'] = array('type' => 'text', 'caption' => 'СпецНазвание модуля');
		$this->fields_form['access'] = array('type' => 'list','multiple'=>2,'listname'=>'access', 'caption' => 'Права доступа');

	}

	function _checkmodstruct() {
		$check_result = parent::_checkmodstruct();



//		global $UGROUP;
/*		if (isset($check_result['err']))
			return array('err' => $check_result['err']);
			
		if(!$UGROUP)
			if(!_new_class('ugroup',$UGROUP))
				return array('err' => $this->getMess('_recheck_err'));
				
		$q_query =$data=$data_owner=array();
		$result = $this->SQL->execSQL('SELECT * FROM '.$this->tablename);
		if (!$result->err)
			while ($row = $result->fetch_array())
				$data[$row['owner_id']][$row['ugroup_id']] = $row;

		$result = $this->SQL->execSQL('SELECT * FROM '.$UGROUP->tablename.' WHERE level>0');
		if ($result->err) return false;
		$grpdata[0] = array('name'=>'Аноним');
		while ($row = $result->fetch_array())
			$grpdata[$row['id']] = $row;

		$result = $this->SQL->execSQL('SELECT * FROM '.$this->owner->tablename);
		if (!$result->err)
			while ($row = $result->fetch_array())
				$data_owner[$row['id']] = $row['name'];

		foreach($data_owner as $kd=>$rd) {
			foreach($grpdata as $k=>$r){
				if(!isset($data[$kd][$k])) {
					$this->def_records[] = array('owner_id'=>$kd,'ugroup_id'=>$k,'name'=>$r['name']);
				}
			}
			if(isset($data[$kd])) {
				foreach($data[$kd] as $k=>$r){
					if(!isset($grpdata[$k])){
						$q_query[][0] = 'DELETE FROM '.$this->tablename.' WHERE id='.$r['id'].';';
					}elseif($grpdata[$k]['name']!=$r['name'])
						$q_query[][0] = 'UPDATE '.$this->tablename.' SET name="'.$grpdata[$k]['name'].'" WHERE id="'.$r['id'].'" ; ';
				}
			}
		}
		
//		if(count($q_query)) foreach($q_query as $row) $this->SQL->execSQL($row);
		if(count($this->def_records)) {$this->_insertDefault();$this->def_records=array();}
	
		if (!empty($check_result['list']))
			$q_query = array_merge($check_result['list'], $q_query);
	
		return array('list_query' => $q_query);
*/		
		if (isset($_POST['sbmt'])) {
		} else {
		}
		return $check_result;
	}

	function _UpdItemModul($param) {
		$ret = parent::_UpdItemModul($param);
		//if($ret[1]) {
		//	session_unset();
		//}
		return $ret;
	}
}

?>

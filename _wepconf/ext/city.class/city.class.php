<?
class city_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_istree = true;
		//$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->mf_createrid = false;
		$this->singleton = true;
		$this->rplc = array(
			'from'=>array('_','','','',''),
			'to'=>array(' ','\'','.',')','(')
		);
		$this->name = 'Вся Россия';
		$this->id = $this->parent_id = 0;
		$this->citylist = array();
		$this->domen = $this->desc = '';
		$this->detectcity = array();
		return true;
	}

	function _create() {
		parent::_create();
		$this->caption = 'Города';
		$this->ordfield = 'name';

		$this->fields['name'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'default'=>'', 'min' => '1');
		//$this->fields['checked'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL DEFAULT 0');
		$this->fields['cnt'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['region_name'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['region_name_ru'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['city'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['city2'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['domen'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['domen_rf'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['desc'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['center'] = array('type' => 'bool', 'attr' => 'NOT NULL');

		$this->fields_form['id'] = array('type' => 'text', 'caption' => 'ID','readonly'=>1);
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название', 'mask' =>array('min'=>1,'name'=>'all'));
		$this->fields_form['cnt'] = array('type' => 'text', 'caption' => 'Число объяв', 'readonly'=>1);
		$this->fields_form['city'] = array('type' => 'text', 'caption' => 'city');
		$this->fields_form['city2'] = array('type' => 'text', 'caption' => 'city2','comment'=>'0|ufa|yfa|0');
		$this->fields_form['region_name'] = array('type' => 'text', 'caption' => 'region_name', 'mask' =>array('name'=>'all'));
		$this->fields_form['region_name_ru'] = array('type' => 'text', 'caption' => 'region_name_ru', 'mask' =>array('name'=>'all'));
		$this->fields_form['parent_id'] = array('type' => 'list', 'listname'=>'parentlist', 'caption' => 'Родительский регион', 'mask' =>array('fview'=>1));
		//$this->fields_form['checked'] = array('type' => 'checkbox', 'caption' => 'Разрешить для подачи объявления');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Показывать на сайте');
		$this->fields_form['domen'] = array('type' => 'text', 'caption' => 'Domen');
		$this->fields_form['domen_rf'] = array('type' => 'text', 'caption' => 'Domen РФ');
		$this->fields_form['desc'] = array('type' => 'text', 'caption' => 'Описание', 'mask' =>array('name'=>'all'));
		$this->fields_form['center'] = array('type' => 'checkbox', 'caption' => 'Центр');

		$this->index_fields['name']='name';
		$this->index_fields['city']='city';
		$this->index_fields['city2']='city2';
		$this->index_fields['region_name']='region_name';

		$this->unique_fields['domen'] = 'domen';
		$this->unique_fields['domen_rf'] = 'domen_rf';
	}

//update city tt SET tt.region_name_ru=(SELECT t1.`name` FROM city3 t1 WHERE t1.id=tt.parent_id ) WHERE tt.parent_id != 0;
	function cityDisplay() {
		$xml=$regname='';
		$reg = (int)$_GET['region'];
		$this->data=array();
		if($reg) $clause = 'SELECT * FROM '.$this->tablename.' WHERE active=1 and (parent_id ='.$reg.' or id='.$reg.') ORDER BY name';
		else $clause = 'SELECT t1.*,sum(t2.cnt) as ocnt FROM '.$this->tablename.' t1 LEFT JOIN '.$this->tablename.' t2 ON t1.id=t2.parent_id and t2.active=1 WHERE t1.active=1 and (t1.parent_id ='.$reg.' or t1.id='.$reg.') GROUP BY t1.id ORDER BY t1.name';
		$result = $this->SQL->execSQL($clause);
		$cnt =0;
		$city = '';
		$centerlist = array();
		if(!$result->err)
			while ($row = $result->fetch_array()){
				//$this->data[$row['id']] = $row['name'];
				if($this->_CFG['site']['rf']) {
					$dom3 = $row['domen_rf'];
				}else {
					$dom3 = $row['domen'];
				}
				if(!$row['parent_id'] and $row['center'])
					$centerlist .= '<item id="'.$row['id'].'" city="'.$dom3.'" cnt="'.$row['cnt'].'">'.$row['name'].'</item>';
				elseif($row['id']!=$reg) {
					$row['ocnt'] = $row['ocnt']+$row['cnt'];
					$xml .='<item id="'.$row['id'].'" city="'.$dom3.'" cnt="'.$row['ocnt'].'">'.$row['name'].'</item>';
					$cnt +=$row['ocnt'];
				}else {
					$regname = $row['name'];
					$city = $dom3;
				}

		}
		if(!$reg)
			return '<all id="0" cnt="'.$cnt.'" city="" host="'.$_SERVER['HTTP_HOST2'].'">Россия</all><center>'.$centerlist.'</center><noscript>0</noscript>'.$xml;
		else
			return '<all id="'.$reg.'" cnt="'.$cnt.'" city="'.$city.'." host="'.$_SERVER['HTTP_HOST2'].'">'.$regname.'</all><noscript>1</noscript>'.$xml;
	}

	function countBoardOfCity() {
		// одсчет объяв в каждом городе
	}

	function cityPosition() {
		global $_tpl,$PGLIST;
		$flag = false;
		$this->name = 'Вся Россия';
		$this->id = $this->parent_id = 0;
		$this->citylist = array();
		$this->domen = $this->desc = '';
		$this->detectcity = array();

		if(isset($_POST['cityid'])) {
			$flag = $this->citySelect((int)$_POST['cityid']);
			header("HTTP/1.0 301");// перемещение на постоянную основу
			if($flag)
				$loc = 'Location: http://'.$this->domen.'.'.$_SERVER['HTTP_HOST2'].$_SERVER['REQUEST_URI'];
			else
				$loc = 'Location: http://'.$_SERVER['HTTP_HOST2'].$_SERVER['REQUEST_URI'];
			header($loc);
			die($loc);
		}
		elseif(isset($this->_CFG['_HREF']['arrayHOST'][2])) {
			$flag = $this->citySelect($this->_CFG['_HREF']['arrayHOST'][2]);
		}/*elseif(count($PGLIST->pageParam)==2) {
			print_r('<pre>');print_r($PGLIST->pageParam);
			$flag = $this->citySelect($PGLIST->pageParam[0]);
			header("HTTP/1.0 301");// перемещение на постоянную основу
			if($flag)
				$loc = 'Location: http://'.$this->domen.'.'.$_SERVER['HTTP_HOST2'].str_replace('/'.$PGLIST->pageParam[0],'',$_SERVER['REQUEST_URI']);
			else
				$loc = 'Location: http://'.$_SERVER['HTTP_HOST2'].$_SERVER['REQUEST_URI'];
			//header($loc);
			die($loc);
		}*/

		if($this->_CFG['robot']=='' and !$this->id) { //если не робот and !$flag
			$geoloc= array();
			$result = $this->SQL->execSQL('SELECT city,country_code FROM ip_group_city where ip_start<=INET_ATON("'.$_SERVER['REMOTE_ADDR'].'") and city!="" order by ip_start desc limit 1;');
			if(!$result->err)
				if ($geoloc = $result->fetch_array() and $geoloc['country_code']=="RU") {
					if($this->_CFG['site']['rf'])
						$fdomen = 'domen_rf';
					else
						$fdomen = 'domen';
					$clause = 'SELECT id,name,'.$fdomen.' FROM '.$this->tablename.' WHERE  active=1 and (city="'.$geoloc['city'].'" or (city2!="" and city2 LIKE "%|'.$geoloc['city'].'|%")) ORDER BY name LIMIT 1';
					$result = $this->SQL->execSQL($clause);
					if(!$result->err)
						if($row = $result->fetch_array()) {
							//рекомендациия по смене домена города
							$this->detectcity = array('href'=>'http://'.$row[$fdomen].'.'.$_SERVER['HTTP_HOST2'],'name'=>$row['name']);//$row;
						}
			}
		}

		$_SERVER['CITY_HOST'] = ($this->domen!=''?$this->domen.'.':'').$_SERVER['HTTP_HOST2'];

		//if(strpos($_SERVER['REQUEST_URI'],'/'.$_SESSION['domen'].'/')!==0 and $_SESSION['domen']!='' and $_CFG['robot']=='') {
		//	@header('Location: /'.$_SESSION['domen'].strrchr($_SERVER['REQUEST_URI'],'/'));die();
		//}

		return 1;
	}

	function citySelect($city) {
		global $PGLIST;
		$flag=false;
		if($city) {// and $PGLIST->id!='404'
			if($this->_CFG['site']['rf'])
				$fdomen = 'domen_rf';
			else
				$fdomen = 'domen';
			if(strlen($city) ==strlen((int)$city)){
				$city =(int)$city;
				$cls = '(t1.id='.$city.' or t1.parent_id='.$city.')';
			}
			else
				$cls = 't1.'.$fdomen.'="'.$city.'"';
			$clause = 'SELECT t1.center,t1.id,t1.parent_id,t1.name,t1.desc,lower(t1.city) as city,t1.region_name,t2.name as region,t1.'.$fdomen.' as domen FROM '.$this->tablename.' t1 LEFT JOIN  '.$this->tablename.' t2 ON t1.parent_id!=0 and t1.parent_id=t2.id WHERE t1.active=1 and '.$cls.' ORDER BY t1.name';
			$result = $this->SQL->execSQL($clause);
			if(!$result->err) {
				
				while ($row = $result->fetch_array()) {
					$this->citylist[] = $row['id'];
					if((is_int($city) and $row['id']==$city) or (!is_int($city) and $row['domen']==$city)) {
						$this->domen = $row['domen'];
						if($row['region'] and $row['region']!='' and !$row['center'])
							$row['name'] = $row['name'].', '.$row['region'];
						$this->name = $row['name'];
						$this->center = $row['center'];
						$this->id = $row['id'];
						$this->parent_id = $row['parent_id'];
						$this->desc = $row['desc'];
						$flag = true;
					}
				}
			}
		}
		return $flag;
	}

	function cityMap() {
		global $SITEMAP;
		$data=array();
		$clause = 'SELECT name,'.($this->_CFG['site']['rf']?'domen_rf':'domen').' as domen, parent_id,id FROM '.$this->tablename.' WHERE active=1 ORDER BY parent_id,name';
		$result = $this->SQL->execSQL($clause);
		if(!$result->err)
			while ($row = $result->fetch_array()) {
				if(!$row['parent_id']) {
					$data[$row['id']]['name'] = $row['name'];
					if($SITEMAP)
						$data[$row['id']]['href'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$row['domen'].'/index.html';
					else
						$data[$row['id']]['href'] = 'http://'.$row['domen'].'.'.$_SERVER['HTTP_HOST2'];
					if($this->id) $data[$row['id']]['hidechild'] = 1;
				}
				else {
					if($SITEMAP)
						$data[$row['parent_id']]['#item#'][$row['id']] = array('name'=>$row['name'], 'href'=>'http://'.$_SERVER['HTTP_HOST'].'/'.$row['domen'].'/index.html');
					else
						$data[$row['parent_id']]['#item#'][$row['id']] = array('name'=>$row['name'], 'href'=>'http://'.$row['domen'].'.'.$_SERVER['HTTP_HOST2']);
					if($this->id) 
						$data[$row['parent_id']]['hidechild'] =1;
				}
			}
		return $data;
	}
}
/*
UPDATE city SET domen=replace(replace(city," ","_"),"'","") WHERE city!='';
UPDATE city SET domen=replace(replace(region_name," ","_"),"'","") WHERE city='';
UPDATE city SET domen=replace(lower(domen),"_","-");

UPDATE city SET domen_rf=replace(replace(replace(`name`," ","_"),"'",""),'.','') WHERE city!='';
UPDATE city SET domen_rf=replace(replace(replace(replace(`name`," ","_"),"'",""),"(",""),")","") WHERE city='';
UPDATE city SET domen_rf=replace(replace(lower(domen_rf),"_","-"),".","");
*/



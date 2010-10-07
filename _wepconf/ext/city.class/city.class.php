<?
class city_class extends kernel_class {

	function _set_features() {
		if (parent::_set_features()) return 1;
		$this->mf_istree = true;
		//$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->rplc = array(
			'from'=>array('_',''),
			'to'=>array(' ','\'')
		);
		return 0;
	}

	function _create() {
		parent::_create();
		$this->caption = 'Города';
		$this->ordfield = 'name';

		$this->fields["id"] = array("type" => "int unsigned", "attr" => "NOT NULL AUTO_INCREMENT UNIQUE");
		$this->fields['name'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'min' => '1');
		//$this->fields['checked'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL DEFAULT 0');
		$this->fields['cnt'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['region_name'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL');
		$this->fields['region_name_ru'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL');
		$this->fields['city'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL');
		$this->fields['city2'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL');

		$this->fields_form['id'] = array('type' => 'text', 'caption' => 'ID','readonly'=>1);
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название', 'mask' =>array('min'=>1,'name'=>'all'));
		$this->fields_form['cnt'] = array('type' => 'text', 'caption' => 'Число объяв', 'readonly'=>1);
		$this->fields_form['city'] = array('type' => 'text', 'caption' => 'city');
		$this->fields_form['city2'] = array('type' => 'text', 'caption' => 'city2','comment'=>'0|ufa|yfa|0');
		$this->fields_form['region_name'] = array('type' => 'text', 'caption' => 'region_name', 'mask' =>array('name'=>'all'));
		$this->fields_form['region_name_ru'] = array('type' => 'text', 'caption' => 'region_name_ru', 'mask' =>array('name'=>'all'));
		$this->fields_form['parent_id'] = array('type' => 'list', 'listname'=>'parentlist', 'caption' => 'Родительский регион', 'mask' =>array('fview1'=>1));
		//$this->fields_form['checked'] = array('type' => 'checkbox', 'caption' => 'Разрешить для подачи объявления');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Показывать на сайте');

		$this->index_fields['city']='city';
		$this->index_fields['city2']='city2';
		$this->index_fields['region_name']='region_name';

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
		if(!$result->err)
			while ($row = $result->fetch_array()){
				//$this->data[$row['id']] = $row['name'];
				if($row['id']!=$reg) {
					$row['ocnt'] = $row['ocnt']+$row['cnt'];
					$xml .='<item id="'.$row['id'].'" city="'.str_replace($this->rplc['to'],$this->rplc['from'],($row['city']!=''?$row['city']:'all'.$row['region_name'])).'" cnt="'.$row['ocnt'].'">'.$row['name'].'</item>';
					$cnt +=$row['ocnt'];
				}else {
					$regname = $row['name'];
					$city = str_replace($this->rplc['to'],$this->rplc['from'],($row['city']!=''?$row['city']:'all'.$row['region_name']));
				}
		}
		if(!$reg)
			return '<all id="0" cnt="'.$cnt.'" city="" host="'.$_SERVER['HTTP_HOST2'].'">Россия</all><noscript>0</noscript>'.$xml;
		else
			return '<all id="'.$reg.'" cnt="'.$cnt.'" city="'.$city.'." host="'.$_SERVER['HTTP_HOST2'].'">'.$regname.'</all><noscript>1</noscript>'.$xml;
	}

	function countBoardOfCity() {
		// одсчет объяв в каждом городе
	}

	function cityPosition() {
		global $_tpl,$PGLIST;
		$this->id=0;

		if(isset($this->_CFG['_HREF']['arrayHOST'][2])) {
			if(!isset($_COOKIE["domen"]) or $_COOKIE["domen"]!=$this->_CFG['_HREF']['arrayHOST'][2]) {
				$this->citySelect($this->_CFG['_HREF']['arrayHOST'][2]);
			}elseif(isset($_COOKIE["domen"])) {
				$this->name = $_COOKIE["cityname"];
				$this->id = $_COOKIE["cityid"];
				$this->citylist = unserialize(stripslashes($_COOKIE["citylist"]));
				$this->domen= $_COOKIE["domen"];
			}
		}
		elseif($_SERVER['robot']==''){ //если не робот
			$geoloc= array();
			$result = $this->SQL->execSQL('SELECT * FROM ip_group_city where ip_start<=INET_ATON("'.$_SERVER['REMOTE_ADDR'].'") and city!=""  order by ip_start desc limit 1;');
			if(!$result->err)
				if ($geoloc = $result->fetch_array()) {
					$clause = 'SELECT id,name FROM '.$this->tablename.' WHERE (city="'.$geoloc['city'].'" or (city2!="" and city2 LIKE "%|'.$geoloc['city'].'|%")) and active=1 ORDER BY name LIMIT 1';
					$result = $this->SQL->execSQL($clause);
					if(!$result->err)
						if($row = $result->fetch_array()) {
							//рекомендациия по смене домена города
							//$this->citySelect($row['id']);
						}
			}
		}

		if(!$this->id) {
			$this->name = 'Вся Россия';
			$this->id = 0;
			$this->citylist = array();
			$this->domen= '';
		}

		if($_SERVER['robot']=='') {
			setcookie('domen', $this->domen);
			setcookie('cityid', $this->id);
			setcookie('cityname', $this->name);
			setcookie('citylist', serialize($this->citylist));
		}
		$_SERVER['CITY_HOST'] = ($this->domen!=''?$this->domen.'.':'').$_SERVER['HTTP_HOST2'];

		//if(strpos($_SERVER['REQUEST_URI'],'/'.$_SESSION['domen'].'/')!==0 and $_SESSION['domen']!='' and $_SERVER['robot']=='') {
		//	@header('Location: /'.$_SESSION['domen'].strrchr($_SERVER['REQUEST_URI'],'/'));die();
		//}

		return 1;
	}

	function citySelect($city) {
		global $PGLIST;
		$flag=0;
		/*if($PGLIST->id!='404'){
			$_SESSION['cityname']= 'Вся Россия';
			$_SESSION['city']= 0;
			$_SESSION['citylist']= array();
			$_SESSION['domen']= '';
		}*/
		if($city and $PGLIST->id!='404') {
			if(strlen($city) ==strlen((int)$city)){
				$city =(int)$city;
				$cls = '(t1.id='.$city.' or t1.parent_id='.$city.')';
			}
			elseif(strpos($city,'all')===0) {
				$flag=1;
				$city = strtolower(str_replace($this->rplc['from'],$this->rplc['to'],$city));
				$cls = '(replace(t1.region_name,"\'","")="'.substr($city,3).'")';
			}
			else{
				$flag=2;
				$city = strtolower(str_replace($this->rplc['from'],$this->rplc['to'],$city));
				$cls = '(replace(t1.city,"\'","")="'.$city.'")';
			}
			$clause = 'SELECT t1.id,t1.name,lower(t1.city) as city,t1.region_name,t2.name as region FROM '.$this->tablename.' t1 LEFT JOIN  '.$this->tablename.' t2 ON t1.parent_id!=0 and t1.parent_id=t2.id WHERE t1.active=1 and '.$cls.' ORDER BY t1.name';
			$result = $this->SQL->execSQL($clause);
			if(!$result->err) {
				while ($row = $result->fetch_array()) {
					$this->citylist[] = $row['id'];
					if(($flag==1 and 'all'.$row['region_name']==$city and $row['city']=='') or ($flag==2 and $row['city']==$city) or (!$flag and $row['id']==$city)){
						if($row['city']!='') 
							$this->domen = str_replace($this->rplc['to'],$this->rplc['from'],$row['city']);
						else 
							$this->domen= 'all'.str_replace($this->rplc['to'],$this->rplc['from'],$row['region_name']);
						if($row['region'] and $row['region']!='')
							$row['name'] = $row['name'].', '.$row['region'];
						$this->name = $row['name'];
						$this->id = $row['id'];
					}
				}
			}else
				return false;
		}
		return true;
	}
	
}


?>
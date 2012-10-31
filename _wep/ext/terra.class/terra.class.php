<?php
class terra_class extends kernel_extends {

	function _set_features() {
		parent::_set_features();
		$this->mf_istree = true;
		$this->mf_actctrl = true;
		$this->mf_createrid = false;
		$this->_AllowAjaxFn = array(
			'_importKLADR'=>true,
			'_updateDomenName'=>true,
			'_importGeoIP'=>true,
		);
		$this->cron[] = array('modul'=>$this->_cl,'function'=>'_importKLADR()','active'=>0,'time'=>864000);
		$this->cron[] = array('modul'=>$this->_cl,'function'=>'_updateDomenName()','active'=>0,'time'=>864000);
		$this->cron[] = array('modul'=>$this->_cl,'function'=>'_importGeoIP()','active'=>0,'time'=>864000);
	}

	function _create() {
		parent::_create();
		$this->caption = 'Територии';
		$this->ordfield = 'name';

		$this->fields['name'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'default'=>'', 'min' => '1');
		// KLADR base
		$this->fields['code'] = array('type' => 'varchar', 'width' => 14, 'attr' => 'NOT NULL', 'default'=>'');// code
		$this->fields['socr'] = array('type' => 'varchar', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['socr_id'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['socr_name'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['index'] = array('type' => 'int', 'width' =>11, 'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['gninmb'] = array('type' => 'smallint', 'width' => 4, 'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['uno'] = array('type' => 'smallint', 'width' => 4, 'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['ocatd'] = array('type' => 'varchar', 'width' => 12, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['center'] = array('type' => 'tinyint', 'attr' => 'NOT NULL');// status
		//***
		$this->fields['domen'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['domen_rf'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['latitude'] = array('type' => 'float', 'width' => '', 'attr' => 'NOT NULL');
		$this->fields['longitude'] = array('type' => 'float', 'width' => '', 'attr' => 'NOT NULL');
		$this->fields['region_code'] = array('type' => 'tinyint', 'attr' => 'NOT NULL', 'default'=>0);// status
		$this->fields['temp_okryg'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL', 'default'=>'');

		$this->index_fields['name']='name';
		$this->index_fields['index']='index';
		$this->index_fields['domen']='domen';
		$this->index_fields['domen_rf']='domen_rf';
		//$this->unique_fields['domen'] = 'domen';
		//$this->unique_fields['domen_rf'] = 'domen_rf';
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		//$this->fields_form['parent_id'] = array('type' => 'list', 'listname'=>'parentlist', 'caption' => 'Родительский регион', 'mask' =>array('fview'=>1));
		$this->fields_form['id'] = array('type' => 'text', 'caption' => 'ID','readonly'=>1);
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название', 'mask' =>array('min'=>1,'name'=>'all'));
		$this->fields_form['code'] = array('type' => 'text', 'caption' => 'Code', 'readonly'=>1);
		$this->fields_form['socr'] = array('type' => 'text', 'caption' => 'Тип');
		$this->fields_form['socr_name'] = array('type' => 'text', 'caption' => 'Тип');
		$this->fields_form['index'] = array('type' => 'text', 'caption' => 'Индекс', 'readonly'=>1);
		$this->fields_form['gninmb'] = array('type' => 'text', 'caption' => 'gninmb', 'readonly'=>1);
		$this->fields_form['uno'] = array('type' => 'text', 'caption' => 'uno', 'readonly'=>1);
		$this->fields_form['ocatd'] = array('type' => 'text', 'caption' => 'ocatd', 'readonly'=>1);
		$this->fields_form['domen'] = array('type' => 'text', 'caption' => 'Domen');
		$this->fields_form['domen_rf'] = array('type' => 'text', 'caption' => 'Domen РФ');
		$this->fields_form['center'] = array('type' => 'int', 'caption' => 'Центр', 'readonly'=>1);
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Показывать на сайте');

	}

	function _childs() {
		$this->create_child('terrasocr');
		$this->create_child('terraip');
	}

	function geoIP($ip2=NULL) {
		if(is_null($ip2)) $ip2 = $_SERVER['REMOTE_ADDR'];
		$ip = ip2long($ip2);
		// TODO усов. запрос
		$data = $this->childs['terraip']->qs('owner_id','WHERE '.$ip.'>=`ip` and '.$ip.'<=`ip2` LIMIT 1');
		if(count($data)) {
			$data = $this->qs('*','WHERE id='.$data[0]['owner_id']);
			$data[0]['ip'] =  $ip2;
			$pid = $data[0]['parent_id'];
			while($pid) {
				$lst2 = $this->qs('t1.*',' t1 WHERE t1.id="'.$pid.'"');
				$data[0]['parent'][] = $lst2[0];
				$pid = $lst2[0]['parent_id'];
			}
			return $data[0];
		}
		return $data;
	}

	function _importGeoIP() {
		$this->_importGeoIP_geo_files();
		//$this->_importIPCity();
		return true;
	}
	
	function _importIPCity() {
		// TODO : импорт из друго	 базы, пок а нет необходимости
		$data = $this->qs('*','WHERE socr IN ("г","пгт","кп")','parent_id','name');

		foreach($data as $name=>$datarow) {
			$result = $this->SQL->execSQL('SELECT * FROM ip_group_city WHERE city!="" and ru_name="'.$this->SqlEsc($name).'"');
			$IP = array();
			if(!$result->err) {
				while ($row = $result->fetch()) {
					$IP [array_shift(preg_split("/[\s]+/",$row['ru_rname'],-1,PREG_SPLIT_NO_EMPTY))] [] = $row;
				}

			}
		
			if(count($IP)) {
				foreach($datarow as $parent_id=>$terra) {
					$flag = false;
					$nm = $terra['name'];
					$pid = $parent_id;
					$id = $terra['id'];
					while($pid) {
						$lst2 = $this->qs('t1.*',' t1 WHERE t1.id="'.$pid.'"');
						$pid = $lst2[0]['parent_id'];
						$id = $lst2[0]['id'];
						$nm .= ' '.$lst2[0]['name'];
					}
					$nm = mb_convert_case($nm, MB_CASE_LOWER , "UTF-8");
					$nm = preg_replace('/[^А-Яа-я ]+/u','',$nm);
					foreach($IP as $ru_rname=>$IPdata) {
						if(!$ru_rname and !$parent_id) {
							$flag = true;
							// ADD ip
						}
						elseif($ru_rname) {
							$ru_rname = preg_replace('/[^А-Яа-я ]+/u','',$ru_rname);
							if(mb_stripos($nm,$ru_rname)!==false) {
								$flag = true;
								// ADD ip
							}
						}
					}
					if(!$flag){
						print_r('<fieldset><legend>Нет совпадений</legend>'.$name.' / '.$nm.' / '.$parent_id);
						print_r($terra);print_r(' * ');print_r($IP);print_r('</fieldset>');return true;
					}
					//////////////////
				}

			}
			else {
				print_r('<fieldset><legend>Город не определён</legend>'.$name.' / ');print_r('</fieldset>');
				//return true;
			}
		}
		return true;
	}

	/**
	* Импорт Ip адресов в базу из ipgeobase.ru
	* Download from http://ipgeobase.ru/cgi-bin/Archive.cgi
	*/
	function _importGeoIP_geo_files() {
		$ipath = $this->_CFG['_PATH']['temp'];
		$zipFile = file_get_contents('http://ipgeobase.ru/files/db/Main/geo_files.zip');
		if($zipFile) {
			if(!file_put_contents($ipath.'geo_files.zip',$zipFile))
			{
				trigger_error('Ошибка записи фаила', E_USER_WARNING);
				return false;
			}
			static_tools::extractZip($ipath.'geo_files.zip',$ipath.'geo_files/');
		}

		if(!file_exists($ipath.'geo_files/cities.txt') or !file_exists($ipath.'geo_files/cidr_optim.txt'))
			return "Ошибка загрузки: Загрузите фаилы вручную с сайта http://ipgeobase.ru/cgi-bin/Archive.cgi";


		$file = file($ipath.'geo_files/cities.txt');
		$data = array();
		foreach($file as $r) {
			$t = preg_split("/[\t]+/",mb_convert_encoding($r, "UTF-8", "CP-1251"),-1,PREG_SPLIT_NO_EMPTY);
			if(stripos($t[3],'Украина')===false)
				$data[$t[1]][] = $t;
		}

		$file = file($ipath.'geo_files/cidr_optim.txt');
		$dataIP = array();
		foreach($file as $r) {
			$t = preg_split("/[\t]+/",mb_convert_encoding($r, "UTF-8", "CP-1251"),-1,PREG_SPLIT_NO_EMPTY);
			if((int)$t[4]) {
				$dataIP[(int)$t[4]][] = array($t[0],$t[1]); 
			}
		}
		unset($file);
		//print_r('<pre>');
		$this->SQL->_tableClear($this->childs['terraip']->tablename);
		foreach($data as $k=>$r) {
			$lst = $this->qs('t1.*',' t1 WHERE t1.name="'.$this->SqlEsc($k).'" ORDER BY t1.socr_id','parent_id');
			if(is_array($lst) and count($lst)) {
				foreach($r as $i) {
					$s = mb_convert_case($i[2], MB_CASE_LOWER , "UTF-8");
					$s = str_ireplace(array('автономная область','область','край','республика','автономный округ','округ'),'',$s);
					$s = mb_substr(preg_replace('/[^А-Яа-я]+/u','',$s),0,-2);
					$flag = false;
					foreach($lst as $klist=>$rlist) {
						$name = $rlist['name'];
						$pid = $klist;
						$id = $rlist['id'];
						while($pid) {
							$lst2 = $this->qs('t1.*',' t1 WHERE t1.id="'.$pid.'"');
							$pid = $lst2[0]['parent_id'];
							$id = $lst2[0]['id'];
							$name .= $lst2[0]['name'];
						}
						$name = preg_replace('/[^А-Яа-я]+/u','',$name);
						if(mb_stripos($s,'санктпетербу')!==false and mb_stripos($name,'ленинград')!==false) $klist=0;
						if(mb_stripos($s,'московск')!==false and mb_stripos($name,'москва')!==false) $klist=0;
						if(mb_stripos($s,'тыва')!==false and mb_stripos($name,'тыва')!==false) $klist=0;
						if(!$klist or mb_stripos($name,$s)!==false or mb_stripos($s,$name)!==false) {
							$flag = true;
							//print_r('   *MATCH* '.$k);print_r($rlist);print_r($i);print_r($dataIP[$i[0]]);return true;
							$this->id = $id;
							$this->_update(array('temp_okryg'=>$i[3]));
							if(!$rlist['latitude'] and !$rlist['longitude']) {
								$this->id = $rlist['id'];
								$this->_update(array('latitude'=>$i[4],'longitude'=>$i[5]));
							}
							if(isset($dataIP[$i[0]]))
								foreach($dataIP[$i[0]] as $ip)
									$this->_addIP($rlist['id'],$ip[0],$ip[1]);
							continue;
						}
					}
					if(!$flag) {
						//print_r('<fieldset><legend>Регион не определён</legend>'.$s.' / '.$name.' / ');
						//print_r($i);print_r(' * ');print_r($lst);print_r('</fieldset>');
					}
				}
				//return true;
			}
			else {
				//print_r('<fieldset><legend>Нету совпадений в базе </legend>`'.$k.'`   ');print_r($r);print_r('</fieldset>');
			}
		}

		return true;
	}

	private function _addIP($oid,$ip,$ip2=0) {
		//return true;
		// TODO : Доработать алгоритм
		/*
		$s = 'ip>='.$ip.' and ip2<='.$ip.'';
		if($ip2)
			$s .= ' and ip>='.$ip2.' and ip2<='.$ip2.'';
		$tmp = $this->childs['terraip']->qs('*','WHERE '.$s);
		if(!count($tmp))
		*/
		$this->id = $oid;
		$this->childs['terraip']->_add(array('ip'=>$ip,'ip2'=>$ip2));
	}

	function _importKLADR() {exit();

		$this->SQL->_tableClear($this->tablename);
		$this->SQL->_tableClear($this->childs['terrasocr']->tablename);

		//http://xakki.i/_js.php?_template=text&noajax=1&_template=text&_fn=KladrImport&_modul=terra
		$_COOKIE[$this->_CFG['wep']['_showallinfo']] = 1;
		$this->_CFG['wep']['debugmode'] = 2;

		$data = file($this->_CFG['_PATH']['temp'].'kladr/socrbase');
		if(!$data)
		{
			return false;
		}
		$socrbase = array();
		foreach($data as $r) {
			$r = preg_split("/[\t]+/",$r,-1,PREG_SPLIT_NO_EMPTY);
			$upd = array(
				'name'=>mb_convert_encoding($r[2], "UTF-8", "CP-1251"),
				'socr'=>mb_convert_encoding($r[1], "UTF-8", "CP-1251"),
				'level'=>(int)$r[0],
				'kpdt'=>(int)$r[3],
				'flag'=>(int)$r[4]
			);
			$this->childs['terrasocr']->_add($upd);
			$upd['id'] = $this->childs['terrasocr']->id;
			$socrbase[$upd['level']][$upd['socr']] = $upd;
		}

		$data = file($this->_CFG['_PATH']['temp'].'kladr/kladr');
		
		$new = array();
		foreach($data as $k=>$r) {
			$r = preg_split("/[\t]+/",$r,-1,PREG_SPLIT_NO_EMPTY);

			$p1 = substr($r[2],0,2);
			$p2 = substr($r[2],2,3);
			$p3 = substr($r[2],5,3);
			$p4 = substr($r[2],8,3);
			$p5 = substr($r[2],11,2);
			$pid = 0;
			$level=1;
			if($p5>0) {
				$pid = $p1.$p2.$p3.$p4.'00';
				$level=5;
			}
			elseif($p4>0) {
				$pid = $p1.$p2.$p3.'00000';
				$level=4;
			}
			elseif($p3>0) {
				$pid = $p1.$p2.'00000000';
				$level=3;
			}
			elseif($p2>0) {
				$pid = $p1.'00000000000';
				$level=2;
			}
			$new[$pid][$r[2]] = array(
				'name'=>mb_convert_encoding($r[0], "UTF-8", "CP-1251"),
				'socr'=>mb_convert_encoding($r[1], "UTF-8", "CP-1251"),
				'code'=>$r[2],
				'index'=>(int)$r[3],
				'gninmb'=>(int)$r[4],
				'uno'=>(int)($r[5]),
				'ocatd'=>preg_replace('/[^0-9]+/', '', $r[6]),
				'center'=>(int)$r[7],
			);
			$tmp = $socrbase[$level][$new[$pid][$r[2]]['socr']];
			$new[$pid][$r[2]]['socr_id'] = $tmp['id'];
			$new[$pid][$r[2]]['socr_name'] = $tmp['name'];
		}
		unset($data);

		$this->importAdd($new);

		return array('text'=>'*OK*','onload'=>'');
	}

	function importAdd(&$data,$i=0,$pid=0) {
		foreach($data[$i] as $k=>$r) {
			$r['parent_id'] = $pid;
			$this->_add($r);
			if(isset($data[$k])) {
				$this->importAdd($data,$k,$this->id);
			}
		}
	}

	function _updateDomenName() {
		// TODO : uniq domen
		//http://xakki.i/_js.php?_template=text&noajax=1&_template=text&_fn=updRF&_modul=terra
		$res = array('text'=>'','onload'=>'');
		$flag = 1;
		$limit = 400;
		while($flag<50) {
			$data = $this->qs('id,name','WHERE domen="" LIMIT '.$limit);
			if(count($data)) {
				foreach($data as $r) {
					$this->id = $r['id'];
					$r['name'] = strtr($r['name'],array('<br />'=>'-',' '=>'-','_'=>'-',','=>'-','.'=>'-','+'=>'-'));
					$upd = array(
						'domen'=>$this->transliteRuToLat($r['name']),
						'domen_rf'=>mb_strtolower(preg_replace("/[^0-9A-Za-zА-Яа-я\-]+/u",'',$r['name'])),
					);
					$upd['domen_rf'] = $var;
					if(!$upd['domen'] or !$upd['domen_rf']) {
						print_r('<pre>');print_r($r);print_r($upd);
						$res['text'] = '*err*';
						return $res;
					}
					$this->_update($upd);
				}
			} 
			else {
				$flag = 50;
			}
			$flag++;
		}
		$res['text'] = '*OK**'.(count($data));
		if(count($data)==$limit) {
			$res['onload'] = 'window.location.href=location.href;';
		}
		return $res;
	}

}

class terrasocr_class extends kernel_extends {
	function _set_features() {
		parent::_set_features();
		$this->showinowner=false;// не показывать
		$this->mf_ipcreate = false;
		$this->mf_timecr = false;
		$this->caption = 'Сокращенные типы';
		return true;
	}
	function _create() {
		parent::_create();
		$this->index_fields['name'] = 'name';
		$this->index_fields['socr'] = 'socr';
		$this->fields['socr'] =	array('type' => 'varchar', 'width' =>32, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['kpdt'] =	array('type' => 'varchar', 'width' =>6, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['level'] =	array('type' => 'tinyint', 'width' =>3, 'attr' => 'NOT NULL','default'=>0);
		$this->fields['flag'] =	array('type' => 'tinyint', 'width' =>3, 'attr' => 'NOT NULL','default'=>0);
	}
}

class terraip_class extends kernel_extends {
	function _set_features() {
		parent::_set_features();
		$this->mf_namefields = false;
		$this->mf_createrid = false;
		//$this->showinowner=false;// не показывать
		$this->prm_add = false; // добавить в модуле
		$this->prm_del = false; // удалять в модуле
		$this->prm_edit = false; // редактировать в модуле
		$this->caption = 'IP';
		return true;
	}
	function _create() {
		parent::_create();
		$this->fields['ip'] =	array('type' => 'bigint', 'width' =>11, 'attr' => 'NOT NULL','default'=>0);
		$this->fields['ip2'] =	array('type' => 'bigint', 'width' =>11, 'attr' => 'NOT NULL','default'=>0);

		$this->index_fields['ip'] = 'ip';
		$this->index_fields['ip2'] = 'ip2';
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['ip'] = array('type' => 'text','caption' => 'IP','mask' =>array('min'=>1,'filter'=>1));
		$this->fields_form['ip2'] = array('type' => 'text','caption' => 'IP(диапазон)','mask' =>array('min'=>1,'filter'=>1));
	}
}
<?
	function _fXmlModuls(&$_this,$modul){
		$xml = '<moduls'._paramModul($_this).'><name>'.$_this->caption.'</name><msp>1</msp>';
		if(static_main::_prmModul($_this->_cl,array(11)))
			$xml .= '<serv id=\'reinstall\'>'.$_this->_CFG['_MESS']['_reinstall'].'</serv>';
		if(isset($_this->config_form) and count($_this->config_form) and static_main::_prmModul($_this->_cl,array(13)))
			$xml .= '<serv id=\'config\'>'.$_this->_CFG['_MESS']['_config'].'</serv>';
		if($_this->mf_indexing and static_main::_prmModul($_this->_cl,array(12)))
			$xml .= '<serv id=\'reindex\'>'.$_this->_CFG['_MESS']['_reindex'].'</serv>';

		if($_this->mf_use_charid) $owner='';else $owner=0;
		$xml .= _fXmlTreeElem($_this,$modul,$owner);
		return '<modulstree>'.$xml.'</moduls></modulstree>';
	}
	
	function _fXmlModulsTree(&$_this,$modul,$id){
		$xml='';
		if ($_this->mf_istree) {
			$_this->parent_id = $id;
			$xml .= '<moduls '._paramModul($_this,$modul).'><name>'.$_this->caption.'</name>';
			$xml .= fXmlTreeElem($_this,$modul,$id,'tree').'</moduls>';
		}
		foreach($_this->childs as $k=>$r) {
			if($r->showinowner) {
				$_this->childs[$k]->owner->id = $id;
				$xml .= '<moduls'._paramModul($_this->childs[$k],$k).'><name>'.$_this->childs[$k]->caption.'</name>';
				$xml .= _fXmlTreeElem($_this->childs[$k],$modul,$id,'owner').'</moduls>';
			}
		}
		return '<modulstree>'.$xml.'</modulstree>';
	}

	function _fXmlTreeElem(&$_this,$modul,$owner=0,$OP='') {
		$xml='';$count=1;
		$agr='SELECT *';
		$agr .= ', '.$_this->_listnameSQL.' as name';
		$sql_query = ' FROM `'.$_this->tablename.'` WHERE `id` !=\'\'';
		if ($_this->owner and $OP!='tree')
			$sql_query .= ' AND `'.$_this->owner_name.'`=\''.$owner.'\'';
		elseif ($_this->mf_istree)
			$sql_query .= ' AND `parent_id`=\''.$owner.'\'';
		if($_this->_prmModulShow($modul))
			$sql_query .= ' AND `'.$this->mf_createrid.'`=\''.$_SESSION['user']['id'].'\'';

		if($_this->mf_mop) {
			$result = $_this->SQL->execSQL('SELECT count(`id`) as cnt '.$sql_query);
			if ($result->err) 
				return false;
			elseif ($row0 = $result->fetch_array())
				$count = $row0['cnt'];
		}

		if($count) {
			if($_this->ordfield) $sql_query .= ' ORDER BY '.$_this->ordfield;
			if($_this->mf_mop) {
				$pcnt = $_this->messages_on_page*($_this->_pn-1);
				if($pcnt>$count) {
					$_this->_pn=ceil($count/$_this->messages_on_page);
					$pcnt = $_this->messages_on_page*($_this->_pn-1);
				}
				$_this->numlist=100;
				$xml = $_this->fPageNav($count,$_this->_cl.'&amp;').$xml;
				$climit= $pcnt.', '.$_this->messages_on_page;
				$sql_query .= ' LIMIT '.$climit;
			} else
				$count=0;
			
			$result = $_this->SQL->execSQL($agr.$sql_query);
			if ($result->err) return false;
			while ($row = $result->fetch_array()) {
				if(!$_this->mf_mop) $count++;
				$param=' id=\''.$row['id'].'\'';
				if($_this->mf_actctrl) $param .= ' act=\''.$row[$_this->mf_actctrl].'\'';
				if($_this->_prmModulDel(array($row))) $param .= ' del=\'1\'';//проверка на разрешение удал
				$tempname = preg_replace($_this->_CFG['_repl']['name'],'',strip_tags($row['name']));
				if($tempname=='') $tempname = '[Пусто]';
				$xml .='<item'.$param.'>'.$tempname.'</item>';
			}
		}

		$xml .='<cnt>'.$count.'</cnt>';
		return $xml;
	}

	function _paramModul(&$_this){// **** PARAMETR ****//
		$modul = $_this->_cl;
		$param = ' modul=\''.$modul.'\'';
		$param .= ' ownmodul="'.($_this->owner?$_this->owner->_cl:'').'"';

		if ($_this->owner and $_this->owner->id)
			$param .= ' oid=\''.$_this->owner->id.'\'';
		elseif ($_this->mf_istree and $_this->parent_id)
			$param .= ' pid=\''.$_this->parent_id.'\'';

		if($_this->mf_ordctrl and static_main::_prmModul($_this->_cl,array(10))) $param .= ' ord=\'1\'';//проверка на разрешение сортировки
		if($_this->_prmModulAdd()) $param .= ' add=\'1\'';//проверка на разрешение добавления
		if ($_this->mf_istree) $param .= ' child=\'1\'';
		elseif(count($_this->childs)) {
			foreach($_this->childs as $r)
				if($r->showinowner) {
					$param .= ' child=\'1\'';
					break;
				}
		}
		return $param;
	}

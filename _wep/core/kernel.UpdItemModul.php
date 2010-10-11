<?
	//update modul item

		$flag=0;// 1 - успешно, 0 - норм, -1  - ошибка
		$formflag = 1;// 0 - показывает форму, 1 - не показывать форму
		$arr = array('mess'=>array(),'vars'=>array());
		$mess = array();
		if(!count($this->fields_form)) {
			$arr['mess'][] = array('name'=>'error', 'value'=>$this->getMess('nodata'));
			$flag=-1;
		}
		elseif(!empty($this->id) and $this->id) { //EDIT
			$flag=-1;
			$this->listfields = array('*');
			$this->clause = ' WHERE id IN ('.$this->_id_as_string().')';
			if($this->_prmModulShow($this->_cl)) $this->clause .= ' AND creater_id=\''.$_SESSION['user']['id'].'\'';
			$this->_list('id');
			if(count($this->data)==1) {
				if(count($_POST) and $_POST['sbmt']) {
					if(!$this->_prmModulEdit($this->data[$this->id],$param)) {
						$arr['mess'][] = array('name'=>'error', 'value'=>$this->getMess('denied_up'));
						$formflag=0;
					}
					else {
						$mess = $this->kPreFields($_POST,$param);
						$arr = $this->fFormCheck($_POST,$param,$this->fields_form);
						if(!count($arr['mess'])) {
							if(!$rm = $this->_save_item($arr['vars'])) {
								$flag=1;
								$arr['mess'][] = array('name'=>'ok', 'value'=>$this->getMess('update'));
							} else
								$arr['mess'][] = array('name'=>'error', 'value'=>$this->getMess('update_err'));
						}
					}
				}
				else
					$mess = $this->kPreFields($this->data[$this->id],$param);
			} else {
				$arr['mess'][] = array('name'=>'error', 'value'=>$this->getMess('nodata'));
				$flag=1;
			}
		} else { //ADD
			if(!$this->_prmModulAdd($this->_cl)){
				$arr['mess'][] = array('name'=>'error', 'value'=>$this->getMess('denied_add'));
				$formflag=0;
				$flag=-1;
			}
			elseif(count($_POST) and $_POST['sbmt']) {
				$this->kPreFields($_POST,$param);
				$arr = $this->fFormCheck($_POST,$param,$this->fields_form);
				$flag=-1;
				if(!count($arr['mess'])){
					if(!$rm = $this->_add_item($arr['vars'])) {
						$flag=1;
						$arr['mess'][] = array('name'=>'ok', 'value'=>$this->getMess('add'));
					} else
						$arr['mess'][] = array('name'=>'error', 'value'=>$this->getMess('add_err'));
				}
			} 
			else 
				$mess = $this->kPreFields($arr['vars'],$param);
			$this->setCaptcha();
		}

		if($formflag and (!isset($param['ajax']) or $flag==0)) // показывать форму , также если это АЯКС и 
			$formflag = $this->kFields2Form($this->data[$this->id],$param);

		return Array(Array('messages'=>array_merge($mess,$arr['mess']), 'form'=>($formflag?$this->form:array())), $flag);
?>
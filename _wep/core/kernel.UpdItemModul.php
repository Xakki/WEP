<?php
/* _UpdItemModul($param = array(),&$argForm = null) */
	//update modul item

		//$param
		if($this->_CFG['returnFormat'] == 'json') {
			$param['ajax'] = 1;
			$param['errMess'] = 1;
		}
		$flag=0;// 1 - успешно, 0 - норм, -1  - ошибка
		$formflag = 1;// 0 - показывает форму, 1 - не показывать форму
		$arr = array('mess'=>array(),'vars'=>array());
		$mess = array();

		if(!empty($this->id) and $this->id) { //EDIT
			$flag=-1;
			if(!isset($this->data[$this->id]) or count($this->data[$this->id])<count($this->fields)) {
				$listfields = array('*');
				$clause = ' WHERE id IN ('.$this->_id_as_string().')';
				$this->data = $this->_query($listfields,$clause,'id');
			}
			//print($this->SQL->query);
			if(count($this->data)==1) {
				if(!$this->_prmModulShow($this->data,$param)) {
					$arr['mess'][] = static_main::am('error','denied',$this);
					$formflag=0;
				}
				elseif(count($_POST) and (isset($_POST['sbmt']) or isset($_POST['sbmt_save']))) {
					if(!$this->_prmModulEdit($this->data,$param)) {
						$arr['mess'][] = static_main::am('error','denied_up',$this);
						$formflag=0;
					}
					else {
						$DATA = $_POST;
						$mess = $this->kPreFields($DATA,$param,$argForm);
						$arr = $this->fFormCheck($DATA,$param,$argForm);
						if(!count($arr['mess'])) {
							if($rm = $this->_update($arr['vars'])) {
								$flag=1;
								$arr['mess'][] = static_main::am('ok','update',array($this->tablename),$this);
								if($formflag)// кастыль
									$mess = $this->kPreFields($this->data[$this->id],$param,$argForm);
							} else {
								$arr['mess'][] = static_main::am('error','update_err',$this);
							}
						}
					}
				}
				else {
					$flag=0;
					$tempdata = $this->data[$this->id];
					$mess = $this->kPreFields($tempdata,$param,$argForm);
				}
				if(isset($argForm['captcha']))
					static_form::setCaptcha();
			} else {
				$arr['mess'][] = static_main::am('error','nodata',$this);
				$flag=-1;
			}
		} 
		else { //ADD
			if(!$this->_prmModulAdd()){
				$arr['mess'][] = static_main::am('error','denied_add',$this);
				$formflag=0;
				$flag=-1;
			}
			elseif(count($_POST) and (isset($_POST['sbmt']) or isset($_POST['sbmt_save']))) {
				$DATA = $_POST;
				$this->kPreFields($DATA,$param,$argForm);
				$arr = $this->fFormCheck($DATA,$param,$argForm);
				$flag=-1;
				if(!count($arr['mess'])) {
					if($rm = $this->_add($arr['vars'])) {
						$flag=1;
						$arr['mess'][] = static_main::am('ok','add',array($this->tablename),$this);
					} else
						$arr['mess'][] = static_main::am('error','add_err',$this);

				}
			}
			else 
				$mess = $this->kPreFields($_POST,$param,$argForm);
			if(isset($argForm['captcha']))
				static_form::setCaptcha();
		}

		if(isset($param['formflag']))
			$formflag = $param['formflag'];
		elseif($flag==0)
			$formflag = 1;
		elseif(isset($_POST['sbmt']) and $flag==1)
			$formflag = 0;
		elseif(isset($_POST['sbmt_save']))
			$formflag = 1;
		elseif(isset($param['ajax']))
			$formflag = 0;
		if($formflag) // показывать форму
			$formflag = $this->kFields2Form($param,$argForm);

		return Array(
			Array(
				'messages'=>array_merge($mess,$arr['mess']),
				'form'=>($formflag?$argForm:array()),
				'formSort'=> $this->formSort
			), $flag);

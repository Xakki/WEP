<?php
class static_control {
	
	/**
	 * Вывод и обработка данных
	 * @param array $param параметры
	 * $param
	 * - formflag
	 * - ajax
	 * - errMess
	 * - showform
	 * @return array
	 */

	static function _UpdItemModul($_this, $param, $argForm) 
	{
/* _UpdItemModul($param = array(),&$argForm = null) */
	//update modul item

		//$param
		if($_this->_CFG['returnFormat'] == 'json') {
			$param['ajax'] = 1;
			$param['errMess'] = 1;
		}
		$flag=0;// 1 - успешно, 0 - норм, -1  - ошибка
		$formflag = 1;// 0 - показывает форму, 1 - не показывать форму
		$arr = array('mess'=>array(),'vars'=>array());
		$mess = array();

		if(!empty($_this->id) and $_this->id) { //EDIT
			$flag=-1;
			if(!isset($_this->data[$_this->id]) or count($_this->data[$_this->id])<count($_this->fields)) 
			{
				$listfields = array('*');
				$clause = ' WHERE id IN ('.$_this->_id_as_string().')';
				$_this->data = $_this->_query($listfields,$clause,'id');
			}
			//print($_this->SQL->query);
			if(count($_this->data)==1) 
			{
				if(!$_this->_prmModulShow($_this->data,$param)) 
				{
					$arr['mess'][] = static_main::am('error','denied',$_this);
					$formflag=0;
				}
				elseif(count($_POST) and (isset($_POST['sbmt']) or isset($_POST['sbmt_save']))) 
				{
					if(!$_this->_prmModulEdit($_this->data,$param)) 
					{
						$arr['mess'][] = static_main::am('error','denied_up',$_this);
						$formflag=0;
					}
					else {
						$DATA = $_POST;
						$mess = $_this->kPreFields($DATA,$param,$argForm);
						$arr = $_this->fFormCheck($DATA,$param,$argForm);
						if(!count($arr['mess'])) {
							if($rm = $_this->_update($arr['vars'])) {
								$flag=1;
								$arr['mess'][] = static_main::am('ok','update',array($_this->tablename),$_this);
								if($formflag)// кастыль
									$mess = $_this->kPreFields($_this->data[$_this->id],$param,$argForm);
							} else {
								$arr['mess'][] = static_main::am('error','update_err',$_this);
							}
						}
					}
				}
				else 
				{
					$flag=0;
					$tempdata = $_this->data[$_this->id];
					$mess = $_this->kPreFields($tempdata,$param,$argForm);
				}
				if(isset($argForm['captcha']))
					static_form::setCaptcha();
			} else {
				$arr['mess'][] = static_main::am('error','nodata',$_this);
				$flag=-1;
			}
		} 
		else 
		{ 
			//ADD
			if(!$_this->_prmModulAdd())
			{
				$arr['mess'][] = static_main::am('error','denied_add',$_this);
				$formflag=0;
				$flag=-1;
			}
			elseif(count($_POST) and (isset($_POST['sbmt']) or isset($_POST['sbmt_save']))) 
			{
				$DATA = $_POST;
				$_this->kPreFields($DATA,$param,$argForm);
				$arr = $_this->fFormCheck($DATA,$param,$argForm);
				$flag=-1;
				if(!count($arr['mess'])) 
				{
					//print_r('<pre>');print_r($arr);exit();
					if($rm = $_this->_add($arr['vars'])) 
					{
						$flag=1;
						$arr['mess'][] = static_main::am('ok','add',array($_this->tablename),$_this);
					} else
						$arr['mess'][] = static_main::am('error','add_err',$_this);

				}
			}
			else 
				$mess = $_this->kPreFields($_POST,$param,$argForm);
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
			$formflag = $_this->kFields2Form($param,$argForm);

		return Array(
			Array(
				'messages'=>array_merge($mess,$arr['mess']),
				'form'=>($formflag?$argForm:array()),
				'formSort'=> $_this->formSort
			), $flag);
	}
}
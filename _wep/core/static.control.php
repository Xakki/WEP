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
	 * - setAutoSubmit Позволяет автоматический сохранять/добавлять записи, если нет ошибок
	 * - savePost Сохряняет прочие POST данные
	 * @return array
	 */

	static function _UpdItemModul($_this, $param, $argForm) 
	{
/* _UpdItemModul($param = array(),&$argForm = null) */
	//update modul item

		//$param
		if(isAjax()) {
			$param['ajax'] = 1;
			$param['errMess'] = 1;
		}
		$flag=0;// 1 - успешно, 0 - норм, -1  - ошибка
		$formflag = 1;// 0 - показывает форму, 1 - не показывать форму
		$arr = array('mess'=>array(),'vars'=>array());
		$mess = array();

		// Флаг - запускает процесс сохранения или добавления записи
		$submitFlag = 0;
		if(count($_POST) and (isset($_POST['sbmt']) or isset($_POST['sbmt_save'])))
			$submitFlag = $param['setAutoSubmit'] = 1;
		elseif(isset($param['setAutoSubmit']) and $param['setAutoSubmit'])
			$submitFlag = $param['setAutoSubmit'] = 2;

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
				// Проверка привелегий доступа
				if(!$_this->_prmModulShow($_this->data,$param)) 
				{
					$arr['mess'][] = static_main::am('error','denied',$_this);
					$formflag=0;
				}
				elseif($submitFlag) 
				{
					if(!$_this->_prmModulEdit($_this->data,$param)) 
					{
						$arr['mess'][] = static_main::am('error','denied_up',$_this);
						$formflag=0;
					}
					else {
						$DATA = $_POST;
						$mess = $_this->kPreFields($DATA,$param,$argForm); // заполнение формы данными
						$arr = $_this->fFormCheck($DATA,$param,$argForm); // валидация
						if(!count($arr['mess'])) // Если нет сообщений/ошибок то сохраняем обработанные значения
						{
							if($rm = $_this->_update($arr['vars'])) {
								$flag=1;
								$arr['mess'][] = static_main::am('ok','update',array($_this->tablename),$_this);
								if($formflag)// кастыль
									$mess = $_this->kPreFields($_this->data[$_this->id],$param,$argForm);
							} else {
								$arr['mess'][] = static_main::am('error','update_err',$_this);
							}
						}
						elseif($submitFlag===2) //Если в результате автосабмита данные оказались не валидными, то не выводим сообщения
						{
							$arr['mess'] = array();
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
					static_form::setCaptcha($argForm['captcha']['mask']);
			} else {
				$arr['mess'][] = static_main::am('error','nodata',$_this);
				$flag=-1;
			}
		} 
		else 
		{ 
			// Проверка привелегий доступа
			if(!$_this->_prmModulAdd())
			{
				$arr['mess'][] = static_main::am('error','denied_add',$_this);
				$formflag=0;
				$flag=-1;
			}
			elseif($submitFlag) 
			{
				$DATA = $_POST;
				$_this->kPreFields($DATA,$param,$argForm);
				$arr = $_this->fFormCheck($DATA,$param,$argForm);
				$flag=-1;
				if(!count($arr['mess']))  // Если нет сообщений/ошибок то сохраняем обработанные значения
				{
					//print_r('<pre>');print_r($arr);exit();
					if($rm = $_this->_add($arr['vars'])) 
					{
						$flag=1;
						$arr['mess'][] = static_main::am('ok','add',array($_this->tablename),$_this);
					} else
						$arr['mess'][] = static_main::am('error','add_err',$_this);

				}
				elseif($submitFlag===2) //Если в результате автосабмита данные оказались не валидными, то не выводим сообщения
				{
					$arr['mess'] = array();
				}
			}
			else 
				$mess = $_this->kPreFields($_POST,$param,$argForm);
			if(isset($argForm['captcha']))
				static_form::setCaptcha($argForm['captcha']['mask']);
		}

		if(isset($param['showform']) and $param['showform'] and $flag<0)  // Если стоит флаг showform, то отображаем форму если ошибка
			$formflag = 1;
		elseif(isset($param['formflag'])) // если стоит флаг formflag, то всегда покажем форму
			$formflag = $param['formflag'];
		elseif($flag==0) // по умолчанию сразу показываем форму
			$formflag = 1;
		elseif(isset($_POST['sbmt']) and $flag==1) // если успешно выполненно и нажата кнопка "Сохранить"
			$formflag = 0; 
		elseif(isset($_POST['sbmt_save']))
			$formflag = 1;
		elseif(isset($param['ajax'])) // если флаг ajax , то не показывать форму
			$formflag = 0;

		if($formflag) // показывать форму
			$formflag = $_this->kFields2Form($param,$argForm);
		return Array(
			Array(
				'messages'=>array_merge($mess,$arr['mess']),
				'form'=>($formflag?$argForm:array()),
				'formSort'=> $_this->formSort,
				'flag' => $flag,
				'options' => $_this->getFormOptions()
			), $flag);
	}

}
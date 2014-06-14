<?php
class static_control
{

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
		$param['is_submit'] = false;
		//$param
		if (isAjax()) {
			$param['ajax'] = 1;
			$param['errMess'] = 1;
		}
		$flag = FORM_STATUS_DEFAULT; // 1 - успешно, 0 - норм, -1  - ошибка
		$formflag = 1; // 0 - показывает форму, 1 - не показывать форму
		$arr = array('mess' => array(), 'vars' => array());
		$mess = array();

		// Флаг - запускает процесс сохранения или добавления записи
		$submitFlag = static_form::isSubmited($param);

		if (!empty($_this->id) and $_this->id) { //EDIT
			$flag = FORM_STATUS_ERROR;
			if (!isset($_this->data[$_this->id]) or count($_this->data[$_this->id]) < count($_this->fields)) {
				$listfields = array('*');
				$clause = ' WHERE id IN (' . $_this->_id_as_string() . ')';
				$_this->data = $_this->_query($listfields, $clause, 'id');
			}
			//print($_this->SQL->query);
			if (count($_this->data) == 1) {
				// Проверка привелегий доступа на просмотр
				if (!$_this->_prmModulShow($_this->data, $param)) {
					$arr['mess'][] = static_main::am('error', 'denied', $_this);
					$formflag = 0;
				}
				elseif ($submitFlag) {
					// Проверка привелегий доступа на редактирование
					if (!$_this->_prmModulEdit($_this->data, $param)) {
						$arr['mess'][] = static_main::am('error', 'denied_up', $_this);
						$formflag = 0;
					}
					else {
						$DATA = $_POST;
						$param['is_submit'] = true;
						$mess = $_this->kPreFields($DATA, $param, $argForm); // заполнение формы данными
						$arr = $_this->fFormCheck($DATA, $param, $argForm); // валидация
						if (!self::hasErrorMess($arr['mess'])) // Если нет сообщений/ошибок то сохраняем обработанные значения
						{
							if ($rm = $_this->_update($arr['vars'])) {
								$flag = FORM_STATUS_OK;
								$arr['mess'][] = static_main::am('ok', 'update', array($_this->tablename), $_this);
								if ($formflag) // кастыль
								{
									$param['is_submit'] = false;
									$mess = $_this->kPreFields($_this->data[$_this->id], $param, $argForm);
								}
							}
							else {
								$arr['mess'][] = static_main::am('error', 'update_err', $_this);
							}
						}
						elseif ($submitFlag === 2) //Если в результате автосабмита данные оказались не валидными, то не выводим сообщения
						{
                            $flag = FORM_STATUS_DEFAULT;
							$arr['mess'] = array();
						}
					}
				}
				else {
					// Вывод формы с данными из БД
					$flag = FORM_STATUS_DEFAULT;
					$tempdata = $_this->data[$_this->id];
					$mess = $_this->kPreFields($tempdata, $param, $argForm);
				}
				if (isset($argForm['captcha']))
					static_form::setCaptcha($argForm['captcha']['mask']);
			}
			else {
				// Ошибка. Нет данных
				$arr['mess'][] = static_main::am('error', 'nodata', $_this);
				$flag = FORM_STATUS_ERROR;
			}
		}
		else {
			// Проверка привелегий доступа на добавление
			if (!$_this->_prmModulAdd()) {
				$arr['mess'][] = static_main::am('error', 'denied_add', $_this);
				$formflag = 0;
				$flag = FORM_STATUS_ERROR;
			}
			elseif ($submitFlag) {
				$DATA = $_POST;
				$param['is_submit'] = true;
				$_this->kPreFields($DATA, $param, $argForm);
				$arr = $_this->fFormCheck($DATA, $param, $argForm);
				$flag = FORM_STATUS_ERROR;
				if (!self::hasErrorMess($arr['mess'])) // Если нет сообщений/ошибок то сохраняем обработанные значения
				{
					if ($rm = $_this->_add($arr['vars'])) {
						$flag = FORM_STATUS_OK;
						$arr['mess'][] = static_main::am('ok', 'add', array($_this->tablename), $_this);
					}
					else {
						trigger_error('Ошибка добавления в модуле `' . $_this->_cl . '`, параметры <pre>' . print_r($arr, true) . '</pre>', E_USER_WARNING);
						$arr['mess'][] = static_main::am('error', 'add_err', $_this);
					}

				}
				elseif ($submitFlag === 2) //Если в результате автосабмита данные оказались не валидными, то не выводим сообщения
				{
                    $flag = FORM_STATUS_DEFAULT;
					$arr['mess'] = array();
				}
			}
			else
				$mess = $_this->kPreFields($_POST, $param, $argForm);
			if (isset($argForm['captcha']))
				static_form::setCaptcha($argForm['captcha']['mask']);
		}

		if (isset($param['showform']) and $param['showform'] and $flag === FORM_STATUS_ERROR) // Если стоит флаг showform, то отображаем форму если ошибка
			$formflag = 1;
		elseif (isset($param['formflag'])) // если стоит флаг formflag, то всегда покажем форму
			$formflag = $param['formflag'];
		elseif ($flag == FORM_STATUS_DEFAULT) // по умолчанию сразу показываем форму
			$formflag = 1;
		elseif (isset($_POST['sbmt']) and $flag === FORM_STATUS_OK) // если успешно выполненно и нажата кнопка "Сохранить"
			$formflag = 0;
		elseif (isset($_POST['sbmt_save']))
			$formflag = 1;
		elseif (isset($param['ajax'])) // если флаг ajax , то не показывать форму
			$formflag = 0;

		if ($formflag) // показывать форму
			$formflag = $_this->kFields2Form($param, $argForm);

		$options = $_this->getFormOptions();

		return array(
			array(
				'messages' => array_merge($mess, $arr['mess']),
				'form' => ($formflag ? $argForm : array()),
				'formSort' => $_this->formSort,
				'flag' => $flag,
				'options' => $options
			), $flag);
	}


    static function hasErrorMess($mess) {
        foreach($mess as $r) {
            if ($r['name']=='error') {
                return true;
            }
        }
        return false;
    }
}
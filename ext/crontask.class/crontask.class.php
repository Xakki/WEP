<?php

/**
 * Class crontask_class
 * Отложенные задачи
 */
class crontask_class extends kernel_extends
{

	function init()
	{
		parent::init();
		$this->mf_timecr = true;
		$this->mf_timeup = true;
//		$this->mf_ipcreate = true;
		$this->prm_add = false;
		$this->prm_del = false;
		$this->mf_actctrl = true;
		$this->mf_statistic = false;
		$this->cf_reinstall = true;

		$this->caption = 'Task manager';
		$this->ver = '0.0.1';
		$this->default_access = '|0|';
	}

	function _create()
	{
		parent::_create();

		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['func'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['param'] = array('type' => 'text');
		$this->fields['errors'] = array('type' => 'text');

		$this->ordfield = 'mf_timecr DESC';

		$this->cron[] = array('modul' => $this->_cl, 'function' => 'doCronTask()', 'active' => 0, 'time' => 600);

        $this->index_fields['name'] = 'name';


        $this->_enum['active'] = array(
            -2 => 'Ошибка',
            -1 => 'Выполняется',
            0 => 'Отключено',
            1 => 'Включено',
        );

    }


	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Класс', 'mask' => array());
		$this->fields_form['func'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Функция', 'mask' => array());
		$this->fields_form['param'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Параметры', 'mask' => array());
		$this->fields_form['errors'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'Ошибки', 'mask' => array());
		$this->fields_form['mf_timecr'] = array('type' => 'date', 'readonly' => 1, 'caption' => 'Дата', 'mask' => array('sort' => 1, 'filter' => 1));
        $this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');
//		$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'IP', 'mask' => array('sort' => 1, 'filter' => 1));
	}

    /**
     * Создаем задачу
     * @param $name
     * @param $func
     * @param $param
     * @return bool
     */
    public function addCronTask($name, $func, $param) {
        return $this->_add(['name' => $name, 'func' => $func, 'param' => $param]);
    }

    /**
     * Выполняем по крону все задачи
     */
    public function doCronTask()
    {
        while(true) {
            //time break
            $data = $this->_select([ 'where' => 'active=1', 'limit' => 1]);
            if (!count($data)) {
                break;
            }
            $this->id = $data[0]['id'];
            $this->_update(['active' => -1]);
            $err = $this->executeTask($data[0]);
            if(!$err) {
                $this->_delete();
            }
            else {
                $this->_update(['errors' => $err, 'active' => -2]);
            }
        }
    }

    /**
     * Запуск задачи
     * @param $data
     * @return bool
     */
    private function executeTask($data)
    {
        _new_class($data['name'], $class);
        if (!$class) return false;

        if (!$data['param']) {
            $data['param'] = [];
        }
        else {
            $data['param'] = json_decode($data['param'], true);
            if (!is_array($data['param'])) {
                $data['param'] = [];
            }
        }

        if (is_callable([$class, $data['func']])) {
            call_user_func_array([$class, $data['func']], $data['param']);
        } else {
            return false;
        }
        return true;
    }
}
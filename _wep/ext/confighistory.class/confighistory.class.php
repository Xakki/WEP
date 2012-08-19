<?php
class confighistory_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->default_access = '|9|';
		$this->mf_timecr = true; // создать поле хранящее время создания поля

		$this->caption = 'История настроек';
		$this->comment = 'Различные варианты конфигураций модулей';
		$this->ver = '0.1';
		return true;
	}


	protected function _create() {
		parent::_create();
		$this->fields['conf'] = array('type' => 'text', 'attr' => 'NOT NULL');
		$this->fields['modul'] = array('type' => 'varchar', 'width'=>64, 'attr' => 'NOT NULL'); 
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form['name'] = array('type' => 'hidden', 'caption' => 'Название', 'mask'=>array('min'=>3));
		$this->fields_form['modul'] = array('type' => 'list', 'listname'=>'classList', 'caption' => 'Модуль', 'mask'=>array(), 'relationForm'=>'relationFormModul');
		$this->fields_form['conf'] = array('type' => 'hidden', 'caption' => 'Конфиг', 'mask'=>array('min'=>3));
	}

	protected function relationFormModul($val, &$my_fieldsForm) 
	{
		if(_new_class($val, $Modul) and count($Modul->config_form)) {
			if(!$this->id) 
			{
				foreach($Modul->config_form as $k=>$r) {
					if(isset($Modul->config[$k]))
						$my_fieldsForm['config_'.$k] = array(
							'caption'=>$r['caption'],
							'type'=>$r['type'],
							'value'=>$Modul->config[$k],
							'css'=>'addparam');
				}
			}
			else 
			{

			}
		}

	}


	public function fFormCheck(&$data, &$param, &$FORMS) 
	{
		if(_new_class($data['modul'], $Modul) and count($Modul->config_form)) {
			if(!$this->id) 
			{
				$temp = array();
				foreach($Modul->config_form as $k=>$r) 
				{
					if(isset($data['config_'.$k]))
						$temp[$k] = $data['config_'.$k];
				}
				$data['conf'] = json_encode($temp);
			}
			$data['name'] = $Modul->caption;
		}

		$arr =parent::fFormCheck($data,$param,$FORMS);

		return $arr;
	}

	public function _add($data=array(),$flag_select=true) {
		//print_r('<pre>'.htmlspecialchars(print_r($data,true)));return false;
		return parent::_add($data,$flag_select);
	}
	// TODO - сделать inc для применения различных вариантов конфигов

	// TODO - метод добавления - храниение конфига в виде JSON в поле conf
}



<?php
class redirect_class extends kernel_extends {
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_createrid = true;
		$this->mf_ipcreate = true;
		$this->mf_timecr = true;
		$this->prm_add = false;
		$this->prm_edit = false;
		//$this->mf_namefields = false;
		//$this->cf_reinstall = true;
		$this->ver = '0.1';
		$this->caption = 'Редирект';
	}

	function _create() {
		parent::_create();
		$this->fields['name'] = array('type' => 'varchar', 'width' =>255, 'default' => 'NULL');
		$this->fields['referer'] = array('type' => 'varchar', 'width' =>255, 'default' => '');
		$this->fields['useragent'] = array('type' => 'varchar', 'width' =>255, 'default' => '');
		$this->fields['cookies'] = array('type' => 'varchar', 'width' =>255, 'default'=>'');

		$this->ordfield = $this->mf_timecr;

		$this->index_fields['name'] = 'name';
		$this->index_fields['referer'] = 'referer';

		//$this->cron[] = array('modul'=>$this->_cl,'function'=>'gc()','active'=>1,'time'=>86400);
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form['name'] = array('type' => 'text', 'readonly' => 1,'caption' => 'Ссылка');
		$this->fields_form['referer'] = array('type' => 'text', 'readonly' => 1,'caption' => 'Источник');
		$this->fields_form['useragent'] = array('type' => 'textarea', 'readonly' => 1,'caption' => 'Браузер');
		$this->fields_form['cookies'] = array('type' => 'textarea', 'readonly' => 1, 'caption' => 'Куки');
		$this->fields_form['mf_timecr'] = array('type' => 'date', 'readonly' => 1, 'caption' => 'Time');
		$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'IP');
		$this->fields_form['mf_createrid'] = array('type' => 'text', 'readonly' => 1, 'caption' => 'User');
	}

	function addRedirect($name) {
		$data = array('name'=>$name, 'referer'=>$_SERVER['HTTP_REFERER'], 'useragent'=>$_SERVER['HTTP_USER_AGENT'], 'cookies'=>var_export($_COOKIE, true));
		print_r('<pre>');print_r($data);
		//$this->_add($data);
	}
}

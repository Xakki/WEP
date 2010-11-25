<?
class bug_class extends kernel_class {

	protected function _create_conf() {
		parent::_create_conf();

		$this->config['act'] = 1;

		$this->config_form['act'] = array('type' => 'checkbox', 'caption' => 'Включить логирование ошибок');
	}

	function _set_features() {
		if (parent::_set_features()) return 1;
		$this->mf_use_charid = true;
		$this->mf_timecr = true;
		$this->mf_ipcreate = true;
		$this->mf_add = false;
		$this->mf_del = false;
		$this->mf_statistic = false;
		$this->caption = 'Отладчик';
		$this->ver = '0.0.1';
		return 0;
	}

	function _create() {
		parent::_create();

		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['href'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields['text'] = array('type' => 'text', 'attr' => 'NOT NULL');

		# fields
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Name','mask'=>array('sort'=>1,'filter'=>1));
		$this->fields_form['href'] = array('type' => 'text', 'caption' => 'Страница', 'mask' =>array('sort'=>1,'filter'=>1));
		$this->fields_form['href'] = array('type' => 'textarea', 'caption' => 'Текст ошибки', 'mask' =>array('fview'=>1));

		$this->ordfield = 'mf_timecr DESC';
	}

}

?>
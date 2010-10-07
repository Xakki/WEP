<?
class formlist_class extends kernel_class {

	function _set_features() {
		if (parent::_set_features()) return 1;
		$this->mf_actctrl = true;
		return 0;
	}

	function _create() {
		parent::_create();
		$this->caption = 'Списки';

		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название списка');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');

		$this->ordfield = "name";

		$this->create_child('formlistitems');
	}

}

class formlistitems_class extends kernel_class {

	function _set_features() {
		if (parent::_set_features()) return 1;
		$this->mf_actctrl = true;
		$this->mf_istree = true;
		$this->mf_ordctrl = true;
		return 0;
	}

	function _create() {
		parent::_create();
		$this->caption = 'Элементы';

		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['checked'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL DEFAULT 0');

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название');
		$this->fields_form['checked'] = array('type' => 'checkbox', 'caption' => 'Разрешить для подачи объявления');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');
	}

}

?>
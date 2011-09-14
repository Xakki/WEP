<?
class formlist_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		return true;
	}

	function _create() {
		parent::_create();
		$this->caption = 'Списки';

		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название списка');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');

		$this->ordfield = "name";

	}

	function _childs() {
		$this->create_child('formlistitems');
	}
}

class formlistitems_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		$this->mf_istree = true;
		$this->mf_ordctrl = true;
		$this->caption = 'Элементы';
		return true;
	}

	function _create() {
		parent::_create();
		$this->index_fields['name']='name';
		$this->index_fields['checked']='checked';

		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['checked'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['cntdec'] = array('type' => 'int', 'width' => 7, 'attr' => 'NOT NULL', 'default'=>0);
	}

	public function setFieldsForm() {
		parent::setFieldsForm();

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название');
		$this->fields_form['checked'] = array('type' => 'checkbox', 'caption' => 'Разрешить для подачи объявления');
		$this->fields_form['cntdec'] = array('type' => 'int', 'readonly'=>true, 'caption' => 'Число объявлений');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');
	}
}


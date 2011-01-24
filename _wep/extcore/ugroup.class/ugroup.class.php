<?

class ugroup_class extends ugroup_extend
{
	function _set_features() {
		if (!parent::_set_features()) return false;
		return true;
	}

	function _create() {
		parent::_create();
		//$this->create_child("weppages");
	}
}


class users_class extends users_extend
{
	function _set_features() {
		if (!parent::_set_features()) return false;
		return true;
	}

	function _create() {
		parent::_create();
	}
}
/*
class weppages_class extends kernel_class {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = 'Страницы в WEP';
		return true;
	}

	function _create() {
		parent::_create();

		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['href'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL');
		$this->fields['blank'] = array('type' => 'bool', 'attr' => 'NOT NULL DEFAULT 0');

		# fields
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Name');
		$this->fields_form['href'] = array('type' => 'text', 'caption' => 'HREF', 'mask' =>array('name'=>'all'));
		$this->fields_form['blank'] = array('type' => 'checkbox', 'caption' => '_BLANK', 'mask' =>array());
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');
	}
}
*/
?>

<?
class payqiwi_class extends kernel_class {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'QIWI';
		$this->comment = 'Логи платежей и пополнения счетов пользователями';
		return true;
	}

	protected function _create() {
		parent::_create();
		$this->fields['cost'] = array('type' => 'int', 'width' => 11,'attr' => 'NOT NULL'); // в коппейках

		$this->fields_form['cost'] = array('type' => 'text', 'readonly'=>1, 'caption' => 'Цена (руб.)', 'mask'=>array());

	}
}

?>

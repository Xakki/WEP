<?php
/**
* Доставка для интеренет магазина
*
*/
class shopdeliver_class extends kernel_extends {

	/*protected function _create_conf() {
		parent::_create_conf();
		$this->config['orderset'] = array(0 => '0');
		$this->config_form['orderset'] = array('type' => 'list', 'listname'=>'orderset', 'multiple'=>1, 'caption'=>'Варианты заказа товара');
	}*/

	function _set_features() {
		if (!parent::_set_features()) return false;

		$this->ver = '0.0.1';
		$this->caption = 'Магазин - Доставка';
		$this->_dependClass = array('shop','shopbasket');
		$this->mf_actctrl = true;

		$this->_enum['norequere']=array(
			'fio' => 'Фамилия Имя',
			'address' => 'Адрес',
			'phone' => 'Телефон',
		);

		return true;
	}

	protected function _create() {
		parent::_create();

		$this->fields['dscr'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['cost'] = array('type' => 'decimal', 'width' => '10,2', 'attr' => 'NOT NULL', 'default'=>'0.00');
		$this->fields['minsumm'] = array('type' => 'int', 'width' => '11', 'attr' => 'NOT NULL', 'default'=>'0');
		$this->fields['paylist'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL');
		$this->fields['norequere'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL');
		$this->fields['selected'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default'=>'0');

	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название');
		$this->fields_form['dscr'] = array('type' => 'textarea', 'caption' => 'Описание');
		$this->fields_form['cost'] = array('type' => 'text', 'caption' => 'Стоимость');
		$this->fields_form['minsumm'] = array('type' => 'int', 'caption' => 'Бесплатная доставка', 'comment'=>'Минимальная сумма заказа для бесплатной доставки, 0 - отключить эту функцию');
		$this->fields_form['paylist'] = array('type' => 'list', 'listname'=>'paylist', 'multiple'=>2, 'caption' => 'Разрешённые платежи');
		$this->fields_form['norequere'] = array('type' => 'list', 'listname'=>'norequere', 'multiple'=>2, 'caption' => 'Отключенные  поля');
		$this->fields_form['selected'] = array('type' => 'checkbox', 'caption' => 'По умолчанию', 'comment'=>'Доставка выбранная по умолчанию', 'mask' =>array());
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Отображать','default'=>1, 'mask' =>array());

	}

	function _getlist($listname, $value = 0) {
		$data = array();
		if ($listname == 'paylist') {
			_new_class('pay',$PAY);
			$data[''] = 'Все включенные ПС';
			foreach($PAY->childs as &$child) {
				if (isset($child->pay_systems)) {
					$data[$child->_cl] = $child->caption;
				}
			}
			return $data;
		}else
			return parent::_getlist($listname, $value);
	}
}

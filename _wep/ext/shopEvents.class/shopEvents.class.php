<?php
/**
* Скидки для интеренет магазина
*
*/
class shopEvents_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;

		$this->ver = '0.0.1';
		$this->caption = 'Магазин - Товар дня';
		$this->_dependClass = array('shop');
		$this->mf_actctrl = true;
		$this->unique_fields['date'] = 'date';

		return true;
	}

	protected function _create() {
		parent::_create();

		$this->fields['date'] = array('type' => 'int', 'width' => '11', 'attr' => 'NOT NULL');
		$this->fields['cost'] = array('type' => 'decimal', 'width' => '10,2', 'attr' => 'NOT NULL');
		$this->fields['product'] = array('type' => 'int', 'width' => '11', 'attr' => 'NOT NULL');

	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['date'] = array('type' => 'date', 'caption' => 'Период начала', 'mask'=>array('format'=>'Y-m-d', 'min'=>(time()-3600*20)));
		$this->fields_form['product'] = array('type' => 'ajaxlist', 'listname'=>array('class'=>'product', 'nameField'=>'concat(tx.name," [",tx.cost,"]")'), 'caption' => 'Товар', 'mask'=>array('min'=>1));
		$this->fields_form['cost'] = array('type' => 'int', 'caption' => 'Новая цена', 'mask'=>array('min'=>1));
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Отображать','default'=>1, 'mask' =>array());

	}

	function getData() {
		$result = array();
		$DATA = $this->qs('*','WHERE date='.mktime(0, 0, 0, date('m'), date('d'), date('Y')));
		if(count($DATA)) {
			$result['#event#'][$DATA[0]['product']] = $DATA[0];
			_new_class('shop',$SHOP);
			$result['#item#'] = $SHOP->childs['product']->fItem($DATA[0]['product']);
		}
		return $result;
	}

}

<?php
class formlist_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		$this->_AllowAjaxFn = array(
			'AjaxMCBox'=>true,
		);
		return true;
	}

	function _create() {
		parent::_create();
		$this->caption = 'Списки';

		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');

		$this->ordfield = "name";

	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название списка');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');
	}

	function _childs() {
		$this->create_child('formlistitems');
	}

	public function AjaxMCBox() {
		//_modul=formlist&_fn=AjaxMCBox
		global $HTML,$_tpl;
		$RESULT = array('html'=>'', 'html2'=>'', 'text'=>'','onload'=>'');

			$DATA = array();
			$enumlist = array();
			$clause = 'SELECT t1.id,t1.owner_id,t1.parent_id,t1.name,t1.checked,t1.cntdec FROM '.$this->childs['formlistitems']->tablename.' t1 WHERE t1.parent_id='.(int)$_GET['tval'].' and t1.active=1 and t1.cntdec!="" ORDER BY t1.ordind';
			// TODO : для подписки не нужен подзапрос cntdec
			$result = $this->SQL->execSQL($clause);

			if(!$result->err) {
				$templ = array();
				while ($row = $result->fetch()) {
					$enumlist[$row['id']] = array('#id#'=>$row['id'],'#name#'=>$row['name'],'#checked#'=>$row['checked']);
				}
			}
			$DATA['filter'] = array(
				substr($_GET['tname'],0,-2).'_'.$_GET['tval'] => array(
					'caption'=>$_GET['tcap'],
					'type'=>'checkbox',
					'multiple'=>1,
					'value'=>0,
					'css'=>'addparam',
					'valuelist'=>$enumlist,
				)						
			);
			//$_tpl['onload'] .= 'mCBoxVis(\''.$rr.'_'.$rrr.'\');';
			$RESULT['html'] = $HTML->transformPHP($DATA,'filter');

		return $RESULT;
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
		$this->fields['cntdec'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default'=>0);// #22=134#45=33#
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название');
		$this->fields_form['checked'] = array('type' => 'checkbox', 'caption' => 'Разрешить для подачи объявления');
		$this->fields_form['cntdec'] = array('type' => 'text', 'readonly'=>true, 'caption' => 'Число объявлений');
		$this->fields_form["ordind"] = array("type" => "int", "caption" => "Сортировка");
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');
	}
}


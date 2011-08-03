<?

class uniugroup_class extends ugroup_class
{
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->tablename = $this->_CFG['sql']['dbpref'] . 'ugroup';
		$this->caption = 'Группы пользователей';
		return true;
	}
	function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		$this->config['paramupdate'] = 70;
		$this->config_form['paramupdate'] = array('type' => 'int', 'caption' => 'Период обновления для анонимов', 'comment'=>'в часах');
	}
	function _create() {
		parent::_create();
		$this->fields["paramboard"] = array("type" => "int", "width" =>3, "attr" => "NOT NULL",'default'=>'5');
		$this->fields["paramsubsc"] = array("type" => "int", "width" =>3, "attr" => "NOT NULL",'default'=>'1');
		$this->fields["paramupdate"] = array("type" => "int", "width" =>3, "attr" => "NOT NULL",'default'=>'30');

		$this->fields_form["paramboard"] = array("type" => "int", "caption" => "Объявления", "comment" => "Разрешённое число объявлений в день");
		$this->fields_form["paramsubsc"] = array("type" => "int", "caption" => "Подписки", "comment" => "Разрешённое число подписок на объявления");
		$this->fields_form["paramupdate"] = array("type" => "int", "caption" => "Период обновления", "comment" => "Период обновления в часах");
	}

	function _childs() {
		$this->create_child('uniusers');
	}
}


class uniusers_class extends users_class
{
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->fn_login = 'login';//login or email
		$this->tablename = $this->_CFG['sql']['dbpref'] . 'users';
		return true;
	}
	function _create() {
		parent::_create();
		$this->fields['phone'] =  array('type' => 'varchar', 'width' => 127, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['www'] = array('type' => 'varchar', 'width' => 64,'attr' => 'NOT NULL', 'default'=>'');

	}


	public function setFieldsForm() {
		parent::setFieldsForm();
		$this->fields_form['phone'] = array('type' => 'text', 'caption' => 'phone', 'mask'=>array('name'=>'phone'));
		$this->fields_form['www'] = array('type' => 'text', 'caption' => 'WWW', 'mask'=>array('name'=>'www'));
		
		if(!static_main::_prmUserCheck()) {
			$this->formSort = array(
				$this->fn_login,$this->fn_pass,'email',
			);
		} else {
			$this->fields_form['cntdec'] = array(
				'type' => 'list', 
				'listname'=>array('class'=>'board','nameField'=>'count(tx.id)','idField'=>'tx.creater_id','idThis'=>'id','leftJoin'=>''), 
				'readonly'=>1,
				'caption' => 'Объявл.',
				'mask' =>array('usercheck'=>1,'sort'=>''));
			/*$this->formSort = array(
				'userpic',$this->mf_namefields,$this->fn_login,'email','birthday','gender','myplace','showmygaget','aboutme','tumblr','twitter','facebook','lookatme','lastfm','lastfm','flickr','youtube','habrahabr','vkontakte','#over#'
			);*/
		}

	}
}



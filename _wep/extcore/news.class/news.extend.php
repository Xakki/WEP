<?

class news_extend extends kernel_class {

	var $messages_on_page = 20;
	var $type_pref='';
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		//$this->mf_indexing = true;
		$this->ordfield = 'ndate DESC';
		$this->caption = 'НОВОСТИ';
		$this->version = 1.0;
		return true;
	}

	function _create() {
		parent::_create();
	
		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 127, 'attr' => 'NOT NULL', 'min'=>'1');
		$this->fields['description'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['text'] = array('type' => 'text', 'width' => 1024, 'min' => '1');
		$this->fields['ndate'] = array('type' => 'int', 'attr' => 'NOT NULL');
		$this->fields['category'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['href'] = array('type' => 'varchar', 'width' => 127, 'attr' => 'NOT NULL', 'default' => '');

		# attaches
		$this->attaches['i_'.$this->_cl] = array('mime' => array('image/pjpeg'=>'jpg', 'image/jpeg'=>'jpg', 'image/gif'=>'gif', 'image/png'=>'png'), 'thumb'=>array(array('resize', '', '800', '600'),array('resizecrop', 's_', '80', '100')),'maxsize'=>1500);

		# form
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Заголовок новости','comment'=>'Короткий: 3-4 слова(до '.$this->fields['name']['width'].' символов).');
		$this->fields_form['description'] = array('type' => 'textarea', 'caption' => 'Краткий анонс');
		$this->fields_form['text'] = array(
			'type' => 'ckedit', 
			'caption' => 'Текст новости', 
			'mask' =>array('fview'=>1,'name'=>'html','min'=>15,'substr'=>150),
			'paramedit'=>array(
				'toolbar'=>'Full',
				'height'=>300,
				'extraPlugins'=>"'cntlen'")
			);
		$this->fields_form['ndate'] = array('type' => 'date', 'caption' => 'Дата новости', 'comment'=>'Дата публикации новости', 'mask'=>array('evala'=>'time()','sort'=>1),'readonly'=>1);
		$this->fields_form['category'] = array('type' => 'list', 'listname'=>'category','caption' => 'Категория','mask'=>array());
		$this->fields_form['href'] = array('type' => 'text', 'caption' => 'Источник','comment'=>'указать полный адрес','mask'=>array('name'=>'www'));
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Опубликовать', 'comment'=>'Видимость новости на сайте');
		$this->fields_form['i_'.$this->_cl] = array("type"=>"file","caption"=>"Фотография",'del'=>1, 'mask'=>array('fview'=>1,'width'=>80,'height'=>100));
		
		
		$this->_enum['category']=array(
			0=>'--');
	}

	function fLastNews($limit=4)// func display NEWS on INDEX page
	{
		$this->listfields = array('*');
		$this->clause = 'WHERE active=1 ORDER BY ndate DESC,id DESC LIMIT '.$limit;
		$this->_list();
		return $this->data;
	}

	function fMenuNews($group='ndate')// func display NEWS on INDEX page
	{
		$this->listfields = array($group);
		$this->clause = 'WHERE active=1 ORDER BY ndate DESC,id DESC GROUP BY '.$group;
		$this->_list();
		return $this->data;
	}

}

?>

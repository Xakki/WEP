<?php

class gallery_class extends kernel_extends
{

	function _set_features()
	{
		parent::_set_features();
		$this->mf_actctrl = true;
		//$this->mf_indexing = true;
		$this->ordfield = 'ndate DESC';
		$this->caption = 'Галлерея';
		$this->version = 0.1;
		$this->messages_on_page = 10;
		$this->numlist = 10;
		//$this->reversePageN = true;

	}

	/*protected function _create_conf() {
		parent::_create_conf();

		$this->config['category'] = array(
			0 => ' --  ',
			1 => 'Мировые новости',
			2 => 'Спортивные новости'
		);

		$this->config_form['category'] = array('type' => 'text', 'keytype'=>'int', 'multiple'=>3, 'caption' => 'Категории');
	}*/

	function _create()
	{
		parent::_create();

		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 127, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['dscr'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
		//$this->fields['text'] = array('type' => 'text', 'attr' => 'NOT NULL');
		$this->fields['tags'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');

		//$this->_enum['category']=$this->config['category'];

		$this->ordfield = 'id DESC';
	}

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);
		# form
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Заголовок');
		$this->fields_form['dscr'] = array('type' => 'textarea', 'caption' => 'Краткое описание', 'mask' => array('max' => 255));
		/*$this->fields_form['text'] = array(
			'type' => 'ckedit',
			'caption' => 'Текст новости',
			'mask' =>array('fview'=>1,'name'=>'html','min'=>50,'max' => 61000,'substr'=>150),//61000 максимум для поля text, еслу нужно болшье то longtext
			'paramedit'=>array(
				'CKFinder' => array('allowedExtensions'=>''), // разрешаем загрузку любых фаилов
				'toolbar'=>'Full',
				'height'=>300,
				'extraPlugins'=>"'cntlen'")
			);*/

		$this->fields_form['tags'] = array('type' => 'text', 'caption' => 'Теги', 'comment' => 'Через запятую');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Опубликовать', 'comment' => 'Видимость на сайте');
	}

	function _childs()
	{
		parent::_childs();
		$this->create_child('gallitem');
	}

	function mainList()
	{
		if ($this->id) {
			$data['#info-gallery#'] = $this->qs('*', 'WHERE id=' . $this->id . ' and active=1');
			if (count($data['#info-gallery#'])) {
				$data['#info-gallery#'] = current($data['#info-gallery#']);
				$data['#list-gallitem#'] = $this->childs['gallitem']->qs('*', 'WHERE owner_id = ' . $this->id . ' ');
			}
		} else {
			$data['#list-gallery#'] = $this->qs('*', 'WHERE active=1 ORDER BY name', 'id');
			$data['#temp-gallitem#'] = $this->childs['gallitem']->qs('*', 'WHERE owner_id IN (' . implode(',', array_keys($data['#list-gallery#'])) . ') GROUP BY owner_id', 'owner_id');
		}
		return $data;
	}
}


class gallitem_class extends kernel_extends
{

	function _set_features()
	{
		parent::_set_features();
		$this->mf_actctrl = true;
		//$this->mf_indexing = true;
		//$this->ordfield = 'ndate DESC';
		//$this->reversePageN = true;
		$this->caption = 'Фото и видео';
		$this->version = 0.1;
		$this->messages_on_page = 10;
		$this->numlist = 10;
		return true;
	}

	function _create()
	{
		parent::_create();
		# attaches
		$this->attaches['gallimg'] = array('mime' => 'image',
			'thumb' => array(array('type' => 'resize', 'w' => '1024', 'h' => '768'), array('type' => 'resizecrop', 'w' => '80', 'h' => '100', 'pref' => 's_', 'path' => '')), 'maxsize' => 3000, 'path' => '');

		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 127, 'attr' => 'NOT NULL', 'default' => '');
	}

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);
		# form
		$this->fields_form['gallimg'] = array('type' => 'file', 'caption' => 'Фото', 'mask' => array('height' => 100));
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Альтернативный текст');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Опубликовать', 'comment' => 'Видимость');
	}

	function flist($filter = array())
	{ // func display NEWS on INDEX page

		/*$listfields = array('id,text');
		$clause ='WHERE description=""';
		$this->_query($listfields,$clause);
		foreach($this->data as $r) {
			$txt = mb_substr(strip_tags($r['text']),0,500,'UTF-8');
			$this->SQL->execSQL('UPDATE test3_news SET description="'.$this->SqlEsc($txt).'" WHERE id='.$r['id']);
		}*/

		$DATA = array();
		$clause = 'WHERE active=1';
		if (isset($_GET['year']) and (int)$_GET['year']) {
			$clause .= ' and FROM_UNIXTIME(ndate,"%Y")="' . (int)$_GET['year'] . '"';
			global $PGLIST;
			$PGLIST->pageinfo['path']['newsY' . (int)$_GET['year'] . '.html'] = 'Год ' . (int)$_GET['year'];
		}
		if (count($filter)) {
			foreach ($filter as $k => $r) {
				if (isset($this->fields[$k]))
					$clause .= ' and `' . $k . '`=' . $r;
			}
		}
		$this->data = $this->_query('count(id) as cnt', $clause);
		$countfield = $this->data[0]['cnt'];
		if ($countfield) {
			$DATA['pagenum'] = $this->fPageNav($countfield);
			// Начальный отчет элементов на странице
			$DATA['pcnt'] = $DATA['pagenum']['start'];

			$climit = $DATA['pagenum']['start'] . ', ' . $this->messages_on_page;
			/****/
			$clause .= ' ORDER BY ' . $this->ordfield . ' LIMIT ' . $climit;;
			$DATA['pcnt'] = $DATA['pagenum']['start'];
			$DATA['#list#'] = $this->_query('*', $clause);
		}
		return $DATA;
	}

	function fItem($id) // func display NEWS on INDEX page
	{
		$listfields = array('*');
		$clause = 'WHERE active=1 and id=' . $id;
		return $this->_query($listfields, $clause);
	}

	function fLast($limit = 4, $filter = array()) // func display NEWS on INDEX page
	{
		$listfields = array('*');
		$clause = 'WHERE active=1 ';
		if (count($filter)) {
			foreach ($filter as $k => $r) {
				if (isset($this->fields[$k]))
					$clause .= ' and `' . $k . '`=' . $r;
			}
		}
		$clause .= ' ORDER BY ndate DESC,id DESC LIMIT ' . $limit;
		return $this->_query($listfields, $clause);
	}

	function fMenu($group = 'ndate') // func display NEWS on INDEX page
	{
		$listfields = array($group);
		$clause = 'WHERE active=1 ORDER BY ndate DESC,id DESC GROUP BY ' . $group;
		return $this->_query($listfields, $clause);
	}

}



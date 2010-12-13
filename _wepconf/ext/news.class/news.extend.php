<?

class extendnews_class extends kernel_class {

	var $messages_on_page = 20;
	var $type_pref='';
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		//$this->mf_indexing = true;
		$this->ordfield = 'ndate DESC';
		$this->caption = 'НОВОСТИ';
		$this->version = 1.0;
		$this->addform_title = 'Добавить новость';
		$this->editform_title = 'Изменить новость';
		$this->listform_title = 'Список новостей';
		$this->listform_itemcap = 'заголовок';
		return true;
	}

	function _create() {
		parent::_create();

		
		//$this->index_def = array('fields'=>array('name','text'), 'field_index' =>'id', 'scrap'=>'text', 'name'=>'name', 'pref'=>'/'.$this->_cl.'_', 'suff'=>'.html');
	
		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 127, 'attr' => 'NOT NULL', 'min'=>'1');
		$this->fields['description'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['text'] = array('type' => 'text', 'width' => 1024, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['ndate'] = array('type' => 'int', 'attr' => 'NOT NULL');
		$this->fields['category'] = array('type' => 'varchar', 'width' => 32, 'attr' => 'NOT NULL DEFAULT "corp"');
		$this->fields['href'] = array('type' => 'varchar', 'width' => 127, 'attr' => 'NOT NULL');

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
			'corp'=>'Корпоратив',
			'lizing'=>'Лизинг',
			'1c'=>'1С',
			'sapr'=>'САПР');
	}

	function display_index($limit=4)// func display NEWS on INDEX page
	{
		$this->listfields = array('*');
		$this->clause = 'WHERE active=1 ORDER BY ndate DESC,id DESC LIMIT '.$limit;
		$this->_list();
		return $this->full_xml($this->data);
	}

	function full_xml(&$data)
	{
		$xml = "<items name='{$this->caption}'>";
		if(count($data))
			foreach($data as $row)
			{
				$xml .= '<item time=\''.$row['ndate'].'\'> 
				<id>'.$row['id'].'</id>
				<name><![CDATA['.$row['name'].']]></name>
				<date>'.date('Y-m-d',$row['ndate']).'</date>
				<img>'.$row['i_'.$this->_cl].'</img>
				<s_img>/'.$this->_prefixImage($row['i_'.$this->_cl],'s_').'</s_img>
				<text><![CDATA['.$row['text'].']]></text>
				<category>'.$this->_enum['category'][$row['category']].'</category>
				<categoryid>'.$row['category'].'</categoryid>
				<descr><![CDATA['.strip_tags($row['description']).']]></descr>
				<href><![CDATA['.$row['href'].']]></href>
				<active>'.$row['active'].'</active>
				</item>';
			}
		$xml .= '</items>';
		return $xml;
	}

	function fDisplay($clause=array())//  func display News
	{
		global $PGLIST,$time;
		$limit = $xml = '';
		if($this->id) {
			$clause[] = 'id='.$this->id;
		}
		else
		{
			$this->listfields = array('count(id) as cnt');
			$this->clause = 'WHERE '.implode(' AND ',$clause);
			$this->_list();
			$countfield = $this->data[0]['cnt'];
			$pcnt = $this->messages_on_page*($this->_pn-1);
			$xml = $this->fPageNav($this->kernel->pageNumber, $countfield);
			$limit= 'ORDER BY ndate DESC,id DESC LIMIT '.$pcnt.', '.$this->messages_on_page;
		}
			

		$this->listfields = array('*');
		$this->clause = 'WHERE '.implode(' AND ',$clause).' '.$limit;
		$this->_list();

		if($this->id)
		{
			$PGLIST->pageinfo['path']['/'.$this->_cl.'_'.$this->id.'.html'] = $this->data[0]['name'];
			$xml = $this->full_xml($this->data);
		}
		else
		{
			$xml .= $this->full_xml($this->data).'<start>'.$pcnt.'</start>';
		}

	  	return $xml;
	}

}

?>

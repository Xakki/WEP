<?php

class news_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_actctrl = true;
		//$this->mf_indexing = true;
		$this->ordfield = 'ndate DESC';
		$this->caption = 'НОВОСТИ';
		$this->version = 1.0;
		$this->messages_on_page = 10;
		$this->numlist=10;
		$this->reversePageN = true;
		return true;
	}

	protected function _create_conf() {
		parent::_create_conf();

		$this->config['category'] = array(
			0 => ' --  ',
			1 => 'Мировые новости',
			2 => 'Спортивные новости'
		);
                
		$this->config_form['category'] = array('type' => 'textarea', 'caption' => 'Категории');
	}

	function _create() {
		parent::_create();
	
		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 127, 'attr' => 'NOT NULL', 'min'=>'1');
		$this->fields['description'] = array('type' => 'text', 'attr' => 'NOT NULL', 'min' => '100');
		$this->fields['text'] = array('type' => 'text', 'min' => '1');
		$this->fields['ndate'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['category'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['href'] = array('type' => 'varchar', 'width' => 127, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['redirect'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => '1');

		# attaches
		$this->attaches['i_news'] = array('mime' => array('image/pjpeg'=>'jpg', 'image/jpeg'=>'jpg', 'image/gif'=>'gif', 'image/png'=>'png'), 
			'thumb'=>array(array('type'=>'resize', 'w'=>'1024', 'h'=>'768'),array('type'=>'resizecrop', 'w'=>'80', 'h'=>'100', 'pref'=>'s_', 'path'=>'')),'maxsize'=>3000,'path'=>'');
		
		$this->_enum['category']=$this->config['category'];

		$this->ordfield = 'ndate DESC';
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		# form
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Заголовок новости','comment'=>'Короткий: 3-4 слова(до '.$this->fields['name']['width'].' символов).');
		$this->fields_form['description'] = array('type' => 'textarea', 'caption' => 'Краткий анонс','mask' =>array('max' => 500));
		$this->fields_form['text'] = array(
			'type' => 'ckedit', 
			'caption' => 'Текст новости', 
			'mask' =>array('fview'=>1,'name'=>'html','min'=>15,'max' => 20000,'substr'=>150),
			'paramedit'=>array(
				'toolbar'=>'Full',
				'height'=>300,
				'extraPlugins'=>"'cntlen'")
			);
		$this->fields_form['ndate'] = array('type' => 'date', 'caption' => 'Дата новости', 'comment'=>'Дата публикации новости', 'mask'=>array('evala'=>'time()','sort'=>1),'readonly'=>1);
		if(is_array($this->_enum['category']) and count($this->_enum['category']))
			$this->fields_form['category'] = array('type' => 'list', 'listname'=>'category','caption' => 'Категория','mask'=>array());
		$this->fields_form['href'] = array('type' => 'text', 'caption' => 'Источник','comment'=>'указать полный адрес','mask'=>array('name'=>'www'),'style'=>'background-color:#FFC0CB;');
		$this->fields_form['redirect'] = array('type' => 'checkbox', 'caption' => 'Включить редирект','style'=>'background-color:#FFC0CB;');
		$this->fields_form['i_'.$this->_cl] = array("type"=>"file","caption"=>"Фотография",'del'=>1, 'mask'=>array('fview'=>1,'width'=>80,'height'=>100,'fview'=>0));
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Опубликовать', 'comment'=>'Видимость новости на сайте');
	}

	function fNews($filter='')// func display NEWS on INDEX page
	{
		/*$listfields = array('id,text');
		$clause ='WHERE description=""'; 
		$this->_query($listfields,$clause);
		foreach($this->data as $r) {
			$txt = mb_substr(strip_tags($r['text']),0,500,'UTF-8');
			$this->SQL->execSQL('UPDATE test3_news SET description="'.mysql_real_escape_string($txt).'" WHERE id='.$r['id']);
		}*/
 
		$DATA = array();
		$clause = 'WHERE active=1';
		if(isset($_GET['year']) and (int)$_GET['year']) {
			$clause .= ' and FROM_UNIXTIME(ndate,"%Y")="'.(int)$_GET['year'].'"';
			global $PGLIST;
			$PGLIST->pageinfo['path']['newsY'.(int)$_GET['year'].'.html'] = 'Год '.(int)$_GET['year'];	
		}
		$this->data = $this->_query('count(id) as cnt',$clause);
		$countfield = $this->data[0]['cnt'];
		if($countfield){
			/*** PAGE NUM  REVERSE ***/
			if($this->reversePageN) {
				if($this->_pn == 0) 
					$this->_pn = 1;
				else
					$this->_pn = floor($countfield/$this->messages_on_page)-$this->_pn+1;
			}
			/***/
			$DATA['pagenum'] = $this->fPageNav($countfield,'',1);
			$pcnt = 0;
			if($this->reversePageN) {
				if($this->_pn==floor($countfield/$this->messages_on_page)) {
					$this->messages_on_page = $countfield-$this->messages_on_page*($this->_pn-1); // правдивый
					//$this->messages_on_page = $this->messages_on_page*$this->_pn-$countfield; // полная запись
				}
				else
					$pcnt = $countfield-$this->messages_on_page*$this->_pn; // начало отсчета
			}
			else
				$pcnt = $this->messages_on_page*($this->_pn-1); // начало отсчета
			if($pcnt<0)
					$pcnt = 0;
			$climit= $pcnt.', '.$this->messages_on_page;
			/****/
			$clause .= ' ORDER BY '.$this->ordfield.' LIMIT '.$climit;; 
			$DATA['pcnt'] = $pcnt;
			$DATA['#item#'] = $this->_query('*',$clause);
		}
		return $DATA;
	}
	function fNewsItem($id)// func display NEWS on INDEX page
	{
		$listfields = array('id,ndate,name,i_news,text,href,redirect');
		$clause = 'WHERE active=1 and id='.$id;
		return $this->_query($listfields,$clause);
	}

	function fLastNews($limit=4)// func display NEWS on INDEX page
	{
		$listfields = array('*');
		$clause = 'WHERE active=1 ORDER BY ndate DESC,id DESC LIMIT '.$limit;
		return $this->_query($listfields,$clause);
	}

	function fMenuNews($group='ndate')// func display NEWS on INDEX page
	{
		$listfields = array($group);
		$clause = 'WHERE active=1 ORDER BY ndate DESC,id DESC GROUP BY '.$group;
		return $this->_query($listfields,$clause);
	}

}


<?php

class httpproxy_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->ver = '0.0.2';
		$this->mf_actctrl = true;
		$this->mf_timecr = true;
		$this->mf_timeup = true;
		$this->default_access = '|0|';
		$this->messages_on_page = 100;
		$this->unique_fields['name'] = 'name';
		$this->index_fields['use'] = 'use';
		$this->index_fields['err'] = 'err';
		$this->index_fields['time'] = 'time';
		$this->cf_tools = array(
			array('func'=>'loadList','name'=>'Загрузка списка прокси'),
			array('func'=>'clearUse','name'=>'Очистка счётчиков'),
			array('func'=>'FixCapture','name'=>'Исправления'),
		);
		return true;
	}

	function _create() {
		parent::_create();
		$this->caption = 'Http proxy list';

		$this->fields['name'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['port'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['desc'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['use'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0, 'noquote'=>true);
		$this->fields['err'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0, 'noquote'=>true);
		$this->fields['time'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0, 'noquote'=>true);
		$this->fields['timeout'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>60, 'noquote'=>true);
		$this->fields['autoprior'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0, 'noquote'=>true);
		$this->fields['capture'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default'=>0);

		//$this->cron[] = array('modul'=>$this->_cl,'function'=>'setRate()','active'=>1,'time'=>6);
		$this->ordfield = 'mf_timeup DESC';
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'HTTP', 'comment'=>'значение localhost- не используется прокси', 'mask' =>array());
		$this->fields_form['port'] = array('type' => 'int', 'caption' => 'Port', 'mask' =>array());
		$this->fields_form['desc'] = array('type' => 'textarea', 'caption' => 'Описание', 'mask' =>array());
		$this->fields_form['use'] = array('type' => 'int', 'caption' => 'Использовано', 'mask' =>array());
		$this->fields_form['err'] = array('type' => 'int', 'caption' => 'Ошибки', 'mask' =>array());
		$this->fields_form['time'] = array('type' => 'int', 'caption' => 'Время загрузки(сек)', 'mask' =>array());
		$this->fields_form['timeout'] = array('type' => 'int', 'caption' => 'Период(сек)', 'mask' =>array());
		$this->fields_form['autoprior'] = array('type' => 'int', 'caption' => 'Приоритет', 'mask' =>array());
		$this->fields_form['mf_timeup'] = array('type' => 'date', 'caption' => 'Дата', 'mask' =>array());
		$this->fields_form['capture'] = array('type' => 'checkbox', 'caption' => 'Занято', 'mask' =>array());
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл', 'mask' =>array());
	}

	function getProxy() {
		if(!isset($_GET['pos']))
			$_GET['pos'] = rand(0,3);
		$res = '';
		$this->data = $this->_query('*','WHERE `active`=1 and `capture`= 0 and `mf_timeup`<('.time().'-`timeout`) and (`err`<`use` or `use`<1) 
		ORDER BY `use`,`err`,`autoprior`,`mf_timeup`,`time`
		LIMIT '.(int)$_GET['pos'].',1');
		//print_r(' * '.time().' * ');
		if(count($this->data)) {
			$this->id = $this->data[0]['id'];
			if($this->data[0]['name']!='localhost') {
				if($this->data[0]['name'] and $this->data[0]['port'])
					$res = $this->data[0]['name'].':'.$this->data[0]['port'];
				elseif($this->data[0]['name'])
					$res = $this->data[0]['name'];
			}
			$this->_update(array('capture'=>1),false,false);
		}
		return $res;
	}

	function upStatus($time,$err=0,$prior=0) {
		$upd = array('capture'=>0, 'use'=>'`use`+1','time'=>(int)$time,'autoprior'=>$prior);
		if($err or $time>20)
			$upd['err'] = '`err`+'.$err;
		if($this->id) {
			$this->_update($upd,false,false);
		} else
			$this->_update($upd,'`name`="localhost"',false);
	}

	function toolsloadList() {
		global $_tpl;
		$this->form = $mess = array();
		if (!static_main::_prmModul($this->_cl, array(5, 7)))
			$mess[] = static_main::am('error', 'denied', $this);
		elseif (count($_POST) and $_POST['txt']) {
			$data = explode("\n",$_POST['txt']);
			foreach($data as $r) {
				$temp = preg_split("/[\s\t\,\:\;]+/",$r,-1,PREG_SPLIT_NO_EMPTY);
				if(!$temp[1]) $temp[1]='80';
				$AD = array('name'=>$temp[0],'port'=>$temp[1]);
				if(isset($temp[2]))
					$AD['desc'] = implode(" \n",array_slice($temp[2],2));
				if(!$this->_add($AD,false))
					$mess[] = static_main::am('error', 'Прокси '.$temp[0].' уже есть в списке!', $this);
			}
			$mess[] = static_main::am('ok', 'Сделано', $this);
		} else {
			$this->form['_*features*_'] = array('name' => 'loadList', 'action' => str_replace('&', '&amp;', $_SERVER['REQUEST_URI']), 'prevhref' => $_SERVER['HTTP_REFERER']);
			$this->form['_info'] = array(
				'type' => 'info',
				'caption' => '<h2 style="text-align:center;">' . $this->caption . '</h2>');
			$this->form['txt'] = array(
				'type' => 'textarea',
				'caption' => 'Список',
				'mask'=>array('max'=>999999),
			);
			
			$this->form['sbmt'] = array(
				'type' => 'submit',
				'value' => 'Выполнить',
			);
		}
		self::kFields2FormFields($this->form);
		return Array('form' => $this->form, 'messages' => $mess);
	}

	function toolsclearUse() {
		$upd = array(
			'use'=>0, 'err'=>0,  'time'=>0, 'autoprior'=>0,  'capture'=>0, 
		);
		$this->_update($upd,'id',false);

		$mess = array(static_main::am('ok', 'Сделано', $this));

		return Array('form' => array(), 'messages' => $mess);
	}

	function toolsFixCapture() {
		$upd = array(
			'capture'=>0, 
		);
		$this->_update($upd,'capture=1',false);

		$mess = array(static_main::am('ok', 'Сделано', $this));

		return Array('form' => array(), 'messages' => $mess);

	}

//UPDATE wep_httpproxy SET `use`=0,err=0,time=0,autoprior=0
}

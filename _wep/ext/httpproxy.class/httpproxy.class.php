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

		$this->index_fields['timeout'] = 'timeout';
		$this->index_fields['autoprior'] = 'autoprior';
		$this->index_fields['mf_timeup'] = 'mf_timeup';

		$this->cf_tools[] = array('func'=>'loadList','name'=>'Загрузка списка прокси');
		$this->cf_tools[] = array('func'=>'clearUse','name'=>'Очистка счётчиков');
		$this->cf_tools[] = array('func'=>'CheckSite','name'=>'Проверка xakki.ru');
		return true;
	}

	function _create() {
		parent::_create();
		$this->caption = 'Http proxy list';

		$this->fields['name'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['port'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['desc'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default'=>'');
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
		$this->fields_form['timeout'] = array('type' => 'int', 'caption' => 'Период(сек)', 'mask' =>array());
		$this->fields_form['autoprior'] = array('type' => 'int', 'caption' => 'Приоритет', 'mask' =>array());
		$this->fields_form['mf_timeup'] = array('type' => 'date', 'caption' => 'Дата', 'mask' =>array());
		$this->fields_form['capture'] = array('type' => 'checkbox', 'caption' => 'Занято', 'mask' =>array());
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл', 'mask' =>array());
	}

	function _childs() {
		$this->create_child('httpproxycheck');
	}


	function getProxy($domen='') {
		$domen = parse_url($domen);
		$this->domen = $domen['host'];
		if(!isset($_GET['pos']))
			$_GET['pos'] = rand(0,3);
		$res = '';
		$this->data = $this->_query('t1.name,t1.port,t1.id,t2.id as domenid,if(t2.id,1,0) as fl', 
			't1 LEFT JOIN '.$this->childs['httpproxycheck']->tablename.' t2 
				ON t2.name="'.$this->SqlEsc($this->domen).'" and t2.owner_id=t1.id
				WHERE t1.`active`=1 and t1.`capture`= 0 and t1.`mf_timeup`<('.time().'-t1.`timeout`) 
				ORDER BY fl, t1.`autoprior` DESC, t1.`mf_timeup`
				LIMIT '.(int)$_GET['pos'].',1');//,false,false,true
		//`use`,`err`,`autoprior`,`mf_timeup`,`time`
		// and t1.`capture`= 0 and (t2.`err`<t2.`use` or t2.`use`<1)  and t1.`mf_timeup`<('.time().'-t1.`timeout`) 
		//print_r(' * '.time().' * ');
		//,t1.`timeout`

		//print_r('<pre>');print_r($this->data);//exit();

		if(count($this->data)) {
			$this->id = $this->data[0]['id'];
			$this->childs['httpproxycheck']->id = $this->data[0]['domenid'];
			if($this->data[0]['name']!='localhost' and $this->data[0]['name']) {
				if($this->data[0]['name'] and $this->data[0]['port'])
					$res = $this->data[0]['name'].':'.$this->data[0]['port'];
				elseif($this->data[0]['name'])
					$res = $this->data[0]['name'];
			}
			$this->_update(array('capture'=>1),false,false);
			if(!$this->childs['httpproxycheck']->id) {
				$upd = array('name'=>$this->domen, 'err'=>0, 'use'=>0);
				$this->childs['httpproxycheck']->_add($upd,false);
			}
		}
		return $res;
	}

	function upStatus($time,$err=0,$autoprior=0,$lastcode=200) {

		$updCheck = array( 'use'=>'`use`+1', 'time'=>(int)$time, 'lastcode'=>(int)$lastcode);
		$upd = array('capture'=>0, 'autoprior'=>'`autoprior`+1');

		if($err) {
			$updCheck['err'] = '`err`+'.$err;
			$upd['autoprior'] = '`autoprior`-1';
		}

		if($this->id) {
			$this->_update($upd,false,false);
			$this->childs['httpproxycheck']->_update($updCheck,false,false);
		} else {
			$this->id = 1;
			$this->_update($upd,false,false);
		}

		//else $this->_update($upd,'`name`="localhost"',false);
	}



	function getContent($link,$param=array()) {
		/*$param= array();
		$param['COOKIE'] = 'yandex_gid=213;yandexmarket=100,,1,USD,1,,1,0,0,;';
		$param['redirect'] = true;
		$param['TIMEOUT'] = 45;
		$param['find'] = 'искомый обязательный текст';
		*/
		$temp = $this->getProxy($link);
		if($temp) {
			$param['proxy'] = true;
			$param['proxyList'] = array($temp);
		}
		$html = $this->_http($link,$param);

			$err = 0;
			$autoprior=0;

			if($html['info']['http_code']==0) {
				$err = 1;
				$autoprior=999;
			}
			elseif($html['info']['http_code']==403) {
				// or strpos($html['text'],'<td class="headCode">403</td>')!==false
				$err = 1; // ограничение доступа
				$autoprior=403;
			}
			elseif($html['info']['http_code']==302) {
				$err = 1;
				$autoprior=302;
			}
			elseif($html['info']['http_code']!=200) {
				$err = 1;
				$autoprior=200;
			}
			elseif($html['info']['redirect_url']!='') {
				$err = 1;
				$autoprior=70;
				print_r('<p style="color:red;">Редирект <b>'.$html['info']['redirect_url'].'</b></p>');
			}
			elseif($html['text']=='') {
				$err = 1;
				$autoprior=90;
			}
			else {
				preg_match_all('/[А-Яа-яЁё]/u',$html['text'],$matches);
				if(count($matches[0])<5) {
					$err = 1;
					$autoprior=140;
					print_r('<p style="color:red;">Мало букв</p>');
				} elseif(isset($param['find']) and mb_stripos($html['text'],$param['find'])===false) {
					$err = 1;
					$autoprior=120;
					print_r('<p style="color:red;">Не найден текст <b>'.$param['find'].'</b></p>');
				}
			}

			if($err)
				$html['flag'] = 0;
			print_r('  |  err = '.$err);
			print_r('  |  autoprior = '.$autoprior);
			print_r('  |  redirect_url = '.$html['info']['redirect_url']);
			print_r('  |  http_code = '.$html['info']['http_code']);

			$this->upStatus($html['info']['total_time'],$err,$autoprior,$html['info']['http_code']);

		return $html;
	}


	function toolsloadList() {
		global $_tpl;
		$fields_form = $mess = array();
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
			$fields_form['_info'] = array(
				'type' => 'info',
				'caption' => '<h2 style="text-align:center;">' . $this->caption . '</h2>');
			$fields_form['txt'] = array(
				'type' => 'textarea',
				'caption' => 'Список',
				'mask'=>array('max'=>999999),
			);
			
			$fields_form['sbmt'] = array(
				'type' => 'submit',
				'value' => 'Выполнить',
			);
			self::kFields2FormFields($fields_form);
		}
		return Array('form' => $fields_form, 'messages' => $mess);
	}

	function toolsclearUse() {
		global $_tpl;
		$fields_form = $mess = array();
		if (!static_main::_prmModul($this->_cl, array(5, 7)))
			$mess[] = static_main::am('error', 'denied', $this);
		elseif (count($_POST) and $_POST['dsbmt']) {
			$upd = array(
				//'autoprior'=>0,  
				'capture'=>0, 
			);
				//or autoprior!=0
			$this->_update($upd,'capture!=0',false);
			$mess = array(static_main::am('ok', 'Сделано', $this));
		} else {
			$fields_form['_info'] = array(
				'type' => 'info',
				'caption' => '<h2 style="text-align:center;">Обнулить данные?</h2>');
			$fields_form['dsbmt'] = array(
				'type' => 'submit',
				'value' => 'Выполнить',
			);
			self::kFields2FormFields($fields_form);
		}
		return Array('form' => $fields_form, 'messages' => $mess);
	}

	function toolsCheckSite() {
		ini_set("max_execution_time", "3600");
		set_time_limit(3600*24);
		$param= array();
		//yabs-sid=1752422071299616340
		//fuid01=4d76925108fcb0e4.rBvCuQR8PRtqU2cm8eaIj544KRBAQW8cUD0k9yG2dNO2-djVIemWHc4uRIFM9hRcLTiT44tUrwm8hDYbtVhmeQi-p4M2cb698Ih3G-mnz1pHaMGhAR9g6sdmV_a-E8Zs
		//yp=2147483647.ygo.172:213'
		//yandex_gid   213 - москва //172 - уфа
		//yandexmarket   число элем на стр,,1,USD,1,,1(не учитыв регион)-2(учитыв регион),0,0,
		//$param['COOKIE'] = 'yandex_gid=213;yandexmarket=100,,1,USD,1,,1,0,0,;';
		//подделываем юзер-агента
		//$param['redirect'] = true;
		$param['TIMEOUT'] = 10;
		$param['find'] = 'Бортовой';

		for($i=1;$i++;100) {
			$this->getContent('http://xakki.ru',$param);
		}
		$mess = array(static_main::am('ok', 'Сделано', $this));

		return Array('form' => array(), 'messages' => $mess);

	}

//UPDATE wep_httpproxy SET `use`=0,err=0,time=0,autoprior=0
}


class httpproxycheck_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_timecr = false;
		$this->mf_timeup = false;
		$this->mf_actctrl = false;
		$this->default_access = '|0|';
		$this->index_fields['use'] = 'use';
		$this->index_fields['err'] = 'err';
		$this->index_fields['time'] = 'time';
		return true;
	}

	function _create() {
		parent::_create();
		$this->caption = 'Проверенные домены';
		$this->fields['name'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL');
		$this->fields['use'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0, 'noquote'=>true);
		$this->fields['err'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0, 'noquote'=>true);
		$this->fields['lastcode'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['time'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0, 'noquote'=>true);

	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Domen', 'mask' =>array('min'=>4));
		$this->fields_form['use'] = array('type' => 'int', 'caption' => 'Использовано', 'mask' =>array());
		$this->fields_form['err'] = array('type' => 'int', 'caption' => 'Ошибки', 'mask' =>array());
		$this->fields_form['lastcode'] = array('type' => 'int', 'caption' => 'HTTP code', 'mask' =>array());
		$this->fields_form['time'] = array('type' => 'int', 'caption' => 'Время загрузки(сек)', 'mask' =>array());

	}

}
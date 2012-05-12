<?php
class product_class extends kernel_extends {

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		
		$this->config['reversePage'] = 0;
		$this->config['onComm'] = 0;
		$this->config['imageCnt'] = 6;
		$this->config['prodListTable'] = array(
			'id' => '№',
			'img_product' => 'Фото',
			'name' => 'Наименование',
			'descr' => 'Описание',
			'cost' => 'Цена',
		);
		$this->config['prodItem'] = array(
			//'text' => 'Полное описание',
		);

		$this->config_form['reversePage'] = array('type' => 'checkbox', 'caption' => 'Режим постраничной навигации', 'comment'=>'если откл. - прямая нумерация');
		$this->config_form['onComm'] = array('type' => 'list', 'listname'=>'onComm', 'caption' => 'Включить комментарии?');
		$this->config_form['imageCnt'] = array('type' => 'int', 'caption' => 'Число фотографий');
		$this->config_form['prodListTable'] = array('type' => 'list', 'keytype' => 'text', 'listname' => 'fieldslist', 'multiple' => 3, 'caption' => 'Формат вывода шаблона табличного списка', 'mask' => array('maxarr' => 15,'keylist'=>true));
		$this->config_form['prodItem'] = array('type' => 'list', 'keytype' => 'text', 'listname' => 'fieldslist', 'multiple' => 3, 'caption' => 'Формат вывода шаблона табличного списка', 'mask' => array('maxarr' => 15,'keylist'=>true));

		$this->config['cf_fields'] = array(
			'code' => array(
				'type' => 'varchar',
				'width' => 11,
				'attr' => 'NOT NULL',
				'default'=> '',
				'unique' => true,
				'caption' => 'Код',
				'mask'=>array(),//'max'=>8,'maxint'=>20000000
			),
			'model' => array(
				'type' => 'varchar',
				'width' => 32,
				'attr' => 'NOT NULL',
				'default'=> '',
				'unique' => false,
				'caption' => 'Модель',
			),
			'articul' => array(
				'type' => 'varchar',
				'width' => 32,
				'attr' => 'NOT NULL',
				'default'=> '',
				'unique' => false,
				'caption' => 'Артикул',
			),
			'madein' => array(
				'type' => 'varchar',
				'width' => 32,
				'attr' => 'NOT NULL',
				'unique' => false,
				'default'=> '',
				'caption' => 'Страна изготовитель',
			),
		);
	}

	protected function _set_features() {
		if (!parent::_set_features()) return false;
		$this->ver = '0.0.1';
		$this->caption = 'Продкция';
		//$this->mf_statistic = array('Y'=>'count(id)','X'=>'FROM_UNIXTIME(mf_timecr,"%Y-%m")','Yname'=>'Кол','Xname'=>'Дата');//-%d
		$this->messages_on_page = 20;
		//$this->includeJStoWEP = true;
		//$this->includeCSStoWEP = true;
		$this->lang['add'] = 'Продукция добавлена.';
		$this->mf_timecr = true; // создать поле хранящее время создания поля
		$this->mf_timeup = true; // создать поле хранящее время обновления поля
		$this->mf_ipcreate = true;//IP адрес пользователя с котрого была добавлена запись
		$this->mf_actctrl = true;
		$this->owner_name = 'shop';
		$this->_listnameSQL ='name';
		$this->cf_fields = true; // Разрешить добавлять добавлять дополнительные поля в таблицу
		$this->ver = '0.2.3';

		$this->_enum['onComm']=array(
			0=>'Отключить',
			1=>'Включить');

		$this->_enum['available']=array(
			0=>'На складе',
			1=>'Предзаказ',
			2=>'Ожидается поставка',
			3=>'Не доступно для заказа',
		);

		$this->_AllowAjaxFn['AjaxShopParam'] = true;

		return true;
	}

	protected function _create() {
		parent::_create();

		$this->reversePageN = (bool)$this->config['reversePage'];

		$this->index_fields['img_product'] = 'img_product';
		$this->index_fields['name'] = 'name';


		$thumb = array('type'=>'resize', 'w'=>'1024', 'h'=>'768');
		$maxsize = 3000;
		$this->attaches['img_product'] = array('mime' => array('image'), 'thumb'=>array($thumb,array('type'=>'resize', 'w'=>'250', 'h'=>'250', 'pref'=>'s_', 'path'=>'_content/img_product_thumb')),'maxsize'=>$maxsize,'path'=>'');
		if($this->config['imageCnt']>0) {
			for($i = 2; $i <= $this->config['imageCnt']; $i++) {
				$this->attaches['img_product'.$i] = $this->attaches['img_product'];
				$this->attaches['img_product'.$i]['thumb'][1]['path'] = '_content/img_product'.$i.'_thumb';
			}
		}

		$this->fields['name'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		//$this->fields['code'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['descr'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');
		$this->fields['text'] = array('type' => 'text', 'attr' => 'NOT NULL');
		$this->fields['cost'] = array('type' => 'int', 'width' => 10,'attr' => 'NOT NULL','default'=>0);
		$this->fields['cost2'] = array('type' => 'int', 'width' => 10,'attr' => 'NOT NULL','default'=>0);
		$this->fields['statview'] = array('type' => 'int', 'width' => 9, 'attr' => 'NOT NULL','default'=>0);
		$this->fields['path'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL','default'=>'');
		$this->fields['available'] = array('type' => 'tinyint', 'width' => 1,'attr' => 'NOT NULL','default'=>0);
/*YML*/
//vendor Производитель. Не отображается в названии предложения. Необязательный элемент.
//vendorCode Код товара (указывается код производителя).Необязательный элемент.
// country_of_origin страна производитель

// артикул
// модель
		$this->ordfield = 'name DESC';

	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		global $_tpl;
		$_tpl['script']['shop'] = array('/'.static_main::relativePath(dirname(__FILE__)).'/_design/script/shop.js');
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название товара');
		$this->fields_form['shop'] = array(
			'type' => 'list', 
			'listname'=>'ownerlist',
			'caption' => 'Каталог',
			'onchange'=>'productshop(\'shop\')', 
			'mask' =>array('min'=>1));
		$this->fields_form['descr'] = array(
			'type' => 'textarea', 
			'caption' => 'Краткое описание товара', 
			'mask' =>array('name'=>'all','min'=>15),
		);
		$this->fields_form['text'] = array(
			'type' => 'ckedit', 
			'caption' => 'Описание товара', 
			'mask' =>array('name'=>'all','min'=>15, 'fview'=>1),
			'paramedit'=>array(
				'height'=>250,
				'toolbarStartupExpanded'=>'false',
				'extraPlugins'=>"'cntlen'",));
		$this->fields_form['cost'] = array('type' => 'int', 'caption' => 'Цена (руб.)', 'mask'=>array('max'=>8,'maxint'=>20000000));
		$this->fields_form['cost2'] = array('type' => 'int', 'caption' => 'Старая цена (руб.)', 'mask'=>array('max'=>8,'maxint'=>20000000));
		$this->fields_form['img_product'] = array('type'=>'file','caption'=>'Фотография №1','del'=>1, 'mask'=>array('fview'=>1,'width'=>80,'height'=>100), 'comment'=>static_main::m('_file_size').$this->attaches['img_product']['maxsize'].'Kb');
		if($this->config['imageCnt']>0) {
			$fcnt = $this->config['imageCnt'];
			for($i = 2; $i <= $fcnt; $i++) {
				$this->fields_form['img_product'.$i]=$this->fields_form['img_product'];
				$this->fields_form['img_product'.$i]['caption'] = 'Фотография №'.$i;
			}
		}

		$this->fields_form['img_product']['mask']['filter'] = 1;
		$this->fields_form['img_product']['mask']['fview'] = 0;

		if($this->config['onComm']=='1')
			$this->fields_form['on_comm'] = array('type' => 'checkbox', 'caption' => 'Включить отзывы?','mask'=>array('fview'=>1));
		//$this->fields_form['mf_timecr'] = array('type' => 'date','readonly'=>1, 'caption' => 'Дата создания', 'mask'=>array('fview'=>2,'sort'=>1));
		//$this->fields_form['mf_ipcreate'] = array('type' => 'text', 'caption' => 'IP','readonly'=>1, 'mask'=>array('usercheck'=>1,'sort'=>1));
		//$this->fields_form['statview'] = array('type' => 'int', 'caption' => 'Просмотры','readonly'=>1, 'mask' =>array('sort'=>1));
		$this->fields_form['path'] = array('type' => 'hidden', 'caption' => 'Путь','readonly'=>1);
		
		/*Прописываем поля для номинаций*/
		$i = 1;
		while(isset($this->config['nomination'.$i])) {
			if($this->config['nomination'.$i]!='') {
				$this->fields['nomination'.$i] = array('type' => 'int', 'width' => 9, 'attr' => 'NOT NULL','default'=>0);
				$this->fields_form['nomination'.$i] = array('type' => 'int', 'caption' => '!'.$this->config['nomination'.$i],'readonly'=>1, 'mask' =>array('sort'=>1,'usercheck'=>2));//'fview'=>1,
			}
			$i++;
		}

		$this->fields_form['available'] = array('type' => 'list', 'listname'=>'available', 'caption' => 'Наличие','default'=>1, 'mask' =>array());

		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Отображать','default'=>1, 'mask' =>array());

	}

	function _childs() {
		$this->create_child('product_value');
		/*if($this->_CFG['_F']['adminpage']) {
			include_once(dirname(__FILE__).'/childs.include.php');
			$this->create_child('prodvote');
		}
		if($this->config['onComm']) {
			$this->create_child('productcomments');
		}*/
	}

	function _checkmodstruct() {
		return parent::_checkmodstruct();
	}

	public function Formfilter() {// фильтр админки
		$_FILTR = $_SESSION['filter'][$this->_cl];
		$this->fields_form[$this->mf_createrid]['type'] = 'ajaxlist';
		return parent::Formfilter();
	}

	public function kPreFields(&$f_data, &$f_param = array(), &$f_fieldsForm = null) {
		global $_tpl;

		if(!isset($f_data['shop']) and isset($_REQUEST['shop']))
			$f_data['shop'] = (int)$_REQUEST['shop'];

	
		/*if($this->config['imageCnt']>0) {
			$fcnt = $this->config['imageCnt'];
			for($i = 2; $i <= $fcnt; $i++) {
				$this->fields_form['img_product'.$i]['style'] = 'display:none;';
				if($i==2)
					$ni = '';
				else
					$ni = ($i-1);
				if(!isset($this->fields_form['img_product'.$ni]['comment']))
					$this->fields_form['img_product'.$ni]['comment'] = '';
				$this->fields_form['img_product'.$ni]['comment'] .= ' <div class="shownextfoto" onclick="shownextfoto(this,\'img_product'.$i.'\')">Ещё фото</div>';
			}
		}*/
		$mess = parent::kPreFields($f_data, $f_param, $f_fieldsForm);
		
		/*if($this->id){
			$f_fieldsForm['shop']['onchange'] = 'productshop(\'shop\','.$this->id.')';
		}*/

		
		if($f_data['shop'] or $this->owner->id) {
			if($this->owner->id and !$f_data['shop'])
				$f_data['shop'] = $this->owner->id;
			$f_fieldsForm = static_main::insertInArray($f_fieldsForm,'shop',$this->ParamFieldsForm($this->id,$f_data['shop'])); // обработчик параметров рубрики
		}
		unset($f_fieldsForm['text_ckedit']);
		return $mess;
	}


	public function _update($data=array(),$where=false,$flag_select=true) {
		$cls=array();$ct= array();$tmp = array();
		$PARAM = &$this->owner->childs['rubricparam'];

		if(isset($PARAM->data) and is_array($PARAM->data) and count($PARAM->data)){
			foreach($PARAM->data as $k=>$r) {
				if($data['param_'.$k]) {
					$val = $data['param_'.$k];
					$cls['name'.$r['type']]=$val;
					if($r['constrn']) {
						if(isset($this->_enum['fli'.$r['formlist']])) {
							if(isset($this->_enum['fli'.$r['formlist']][$val]))
								$val = $this->_enum['fli'.$r['formlist']][$val];
							elseif(is_array($this->_enum['fli'.$r['formlist']])) {
								foreach($this->_enum['fli'.$r['formlist']] as $kk=>$rr) {
									if(isset($rr[$val])) {
										$val = $rr[$val];
										break;
									}
								}
							}
						}
						if($val) {
							if(is_array($val))
								$val = $val['#name#'];
							$data['name'] .= '/ '.$val;
							if($r['edi'])
								$data['name'] .= ' '.$r['edi'];
						}
					}
				}
			}
		}

		if(isset($data['name']) and (!isset($data['path']) or !$data['path']))
			$data['path'] = $this->transliteRuToLat($data['name']);

		if($ret = parent::_update($data,$where,$flag_select)) {
			if(isset($PARAM->data) and is_array($PARAM->data) and count($PARAM->data)) {
				if(count($cls)) {
					$result=$this->SQL->execSQL('DELETE FROM product_value WHERE owner_id='.$this->id);
					if($result->err) return false;
					$query = 'INSERT into product_value (owner_id,'.implode(',',array_keys($cls)).') values ('.$this->id.',"'.implode('","',$cls).'")';
					$result=$this->SQL->execSQL($query);
					if($result->err) return false;
				}
			}
		}
		return $ret;
	}

	public function _add($data=array(),$flag_select=true) {
		$PARAM = &$this->owner->childs['rubricparam'];
		$cls=array();
		$tmp = array();

		if(isset($PARAM->data) and is_array($PARAM->data) and count($PARAM->data)) {
			foreach($PARAM->data as $k=>$r) {
				if($data['param_'.$k]) {
					$val = $data['param_'.$k];
					$cls['name'.$r['type']] = $this->SqlEsc($val);
					if($r['constrn']) {
						if(isset($this->_enum['fli'.$r['formlist']])) {
							if(isset($this->_enum['fli'.$r['formlist']][$val]))
								$val = $this->_enum['fli'.$r['formlist']][$val];
							elseif(is_array($this->_enum['fli'.$r['formlist']])) {
								foreach($this->_enum['fli'.$r['formlist']] as $kk=>$rr) {
									if(isset($rr[$val])) {
										$val = $rr[$val];
										break;
									}
								}
							}
						}
						if($val) {
							if(is_array($val))
								$val = $val['#name#'];
							$data['name'] .= '/ '.$val;
							if($r['edi'])
								$data['name'] .= ' '.$r['edi'];
						}
					}
				}
			}
		}
		if(!isset($data['path']) or !$data['path'])
			$data['path'] = $this->transliteRuToLat($data['name']);

		if($ret = parent::_add($data,$flag_select)) {
			$temp = $this->childs['product_value']->qs('id', 'WHERE owner_id='.$this->id);
			$tn = $this->childs['product_value']->tablename;
			if(count($cls)) {
				$query = 'INSERT into '.$tn.' (owner_id,'.implode(',',array_keys($cls)).') values ('.$this->id.',"'.implode('","',$cls).'")';
				if(count($temp)) {
					foreach($cls as $ck=>$cr)
						$cls[$ck] = '`'.$ck.'`="'.$cr.'"';
					$query .= ' ON DUPLICATE KEY UPDATE '.implode(', ',$cls);
				}
			} else {
				$query = 'INSERT into '.$tn.' (owner_id) values ('.$this->id.')';
				if(count($temp)) {
					$cls = array();
					foreach($PARAM->data as $k=>$r)
						$cls[] = '`name'.$r['type'].'`=""';
					$query .= ' ON DUPLICATE KEY UPDATE '.implode(', ',$cls);
				}
			}
			$result=$this->SQL->execSQL($query);
			if($result->err) return false;
		}
		return $ret;
	}


	public function ParamFieldsForm($id,$rid,$listclause='') { // форма для редак-добавл объявы и поиска объявы
		//$id - id объявы
		//$rid - id рубрики
		//listclause - подзапрос
		global $_tpl;//в onLoad слайдер
		$FLI = $FMCB = array();
		if(is_array($id))
			$flagNew = 1;// если это для поискового фильтра
		else
			$flagNew = 0;
		if(!$flagNew and $id) {
			$result = $this->SQL->execSQL('SELECT * FROM product_value WHERE owner_id IN ('.$id.')');
			if(!$result->err) {
				if ($row = $result->fetch()){
					$paramdata=$row;
				}
			}
			else {
				return array();
			}
		}

		$rlist = array();
		$temp=$rid;
		$this->owner->simplefCache();
		while(isset($this->owner->data2[$temp])) {
			$rlist[$temp] = $temp;
			$temp=$this->owner->data2[$temp]['parent_id'];
		};

		if(!count($rlist)) return array();

		$PARAM = &$this->owner->childs['rubricparam'];
		$listfields = array('*');
		$cls = 'WHERE owner_id IN ('.implode(',',$rlist).') and active=1 order by ordind';
		$PARAM->data = $PARAM->_query($listfields,$cls,'id');
		if(count($PARAM->data)) {
			$form=array();
			$pdata=array();
			foreach($PARAM->data as $k=>$r) {
				$val='';

				if($flagNew) {
					$val = (isset($id['param_'.$k])?$id['param_'.$k]:'');
				}
				elseif($id) 
					$val=$paramdata['name'.$r['type']];
				
				$type = $PARAM->getTypeForm($r['type']);
				$multiple = 0;
				if($type=='list' and $r['typelist']) {
					if($r['typelist']==1)
						$type = 'ajaxlist';
					elseif($r['typelist']==2 and $flagNew) {
						$type = 'checkbox';
						$multiple = 1;
					}
				}

				$form['param_'.$k] = array(
					'caption'=>$r['name'].($r['edi']!=''?', '.$r['edi']:''),
					'type'=>$type,
					'multiple'=>$multiple,
					'type2'=>$r['type'],
					'value'=>$val,
					'css'=>'addparam');
				
				if($r['def']!='' and !$flagNew) {// and !$val
					if(substr($r['def'],0,5) == 'eval=')
						eval('$form["param_'.$k.'"]["default"] = '.substr($r['def'],5).';');
					else
						$form['param_'.$k]['default'] = $r['def'];
				}

				if($type=='int') {
					$form['param_'.$k]['mask']=array(
						'minint'=>$r['min'],
						'maxint'=>$r['max']);
					if(is_array($id))
						$form['param_'.$k]['value_2']= (isset($id['param_'.$k.'_2'])?$id['param_'.$k.'_2']:'');

					/*if($listclause!='' and ($r['min']>0 or $r['max']==0)) {
						$temcls = 'SELECT min(t2.name'.$k.') as min,max(t2.name'.$k.') as max FROM '.$this->tablename.' t1 
						JOIN product_value t2 ON t2.owner_id=t1.id and t2.owner_id= '.$listclause;
						$result2 = $this->SQL->execSQL($temcls);
						$maxmin = $result2->fetch_array(MYSQL_NUM);
						if($r['min']>0)
							$r['min']=$maxmin[0];
						if($r['max']==0)
							$r['max']=$maxmin[1];
					}*/

					if($form['param_'.$k]['value']=='')
						$form['param_'.$k]['value']=$r['min'];
					if(!isset($form['param_'.$k]['value_2']) or $form['param_'.$k]['value_2']=='')						
						$form['param_'.$k]['value_2']=$r['max'];

					$form['param_'.$k]['mask']['step'] = (int)$r['step'];
				}
				else
					$form['param_'.$k]['mask']=array('min'=>$r['min'],'max'=>$r['max']);

				if($r['mask']) $form['param_'.$k]['mask']['patterns']=array('match'=>$r['mask']);
				if($r['maskn']) $form['param_'.$k]['mask']['patterns']=array('nomatch'=>$r['maskn']);

				if($r['comment']!='') $form['param_'.$k]['comment']=$r['comment'];
				
				if($type=='ajaxlist') {
					$form['param_'.$k]['listname'] = array('tablename'=>'formlistitems','where'=>' tx.checked=1 and tx.active=1 GROUP BY tx.id','ordfield'=>'tx.ordind');
					$form['param_'.$k]['value_2'] = $id['param_'.$k.'_2'];
				}elseif($type=='list') {
					$form['param_'.$k]['listname']='fli'.$r['formlist'];
					$form['param_'.$k]['mask']['begin']=0;
					$FLI[] = $r['formlist'];
				}elseif($type == 'checkbox' and $multiple) {
					$form['param_'.$k]['listname']='fli'.$r['formlist'];
					$form['param_'.$k]['mask']['begin']=0;
					$FLI[] = $r['formlist'];
					$FMCB[$r['formlist']] = $k;// сотавляем массив тех списков которые checkbox и multiple , чтобы скрывать скриптом непопулярные элементы
					if(!is_array($form['param_'.$k]['value']) and $form['param_'.$k]['value']) {
						$form['param_'.$k]['value'] = array($form['param_'.$k]['value']);
					}
					if(is_array($form['param_'.$k]['value']))
					foreach($form['param_'.$k]['value'] as $kkk=>$rrr) {
						if($rrr) $form['param_'.$k.'_'.$rrr] = array();
					}
					$_tpl['onload'] .= 'mCBoxVis('.$k.');';
				}
			}

			if(count($FLI)) {
				$clause = 'SELECT t1.id,t1.owner_id,t1.parent_id,t1.name,t1.checked,t1.cntdec FROM formlistitems t1 WHERE t1.owner_id IN ('.implode(',',$FLI).') and t1.active=1 ORDER BY t1.ordind';
				$result = $this->SQL->execSQL($clause);
				if(!$result->err) {
					$templ = array();
					while ($row = $result->fetch()) {
						if(!isset($this->_enum['fli'.$row['owner_id']][0][0]))//$flagNew and 
							$this->_enum['fli'.$row['owner_id']][0][0] = ' --- ';
						$this->_enum['fli'.$row['owner_id']][$row['parent_id']][$row['id']] = array('#id#'=>$row['id'],'#name#'=>$row['name'],'#checked#'=>$row['checked']);
						if($flagNew and !$row['parent_id'] and isset($FMCB[$row['owner_id']])) {
							$templ[$row['owner_id']][$row['cntdec']][] = $row['id'];
						}
					}
					if($flagNew) {
						/*Ограничение числа вывода элементов checkbox*/
						$out = array();
						foreach($templ as $kk=>$rr) {
							$tempr = array();
							if(count($rr)>1) {
								$rr = array_multisort($rr, SORT_NUMERIC, SORT_DESC);
								foreach($rr as $rrr) {
									$tempr = array_merge($tempr,$rrr);
								}
							}else
								$tempr = current($rr);
							if(count($tempr)>12) {
								$tempr = array_slice($tempr,0,10);
								foreach($tempr as $rrr)
									$out[] = $rrr.':1';
								$_tpl['onload'] .= 'mCBoxShortHide('.$FMCB[$kk].');';
							}
						}
						if(count($out))
							$_tpl['script']['vfmcb'] = 'var vfmcb = {'.implode(',',$out).'};';
						/*Под элементы мульти чекбоксов*/

						foreach($FMCB as $kk=>$rr) {
							if(is_array($form['param_'.$rr]['value'])) { 
							foreach($form['param_'.$rr]['value'] as $kkk=>$rrr) {	
								if(isset($this->_enum[$form['param_'.$rr]['listname']][$rrr]) and is_array($this->_enum[$form['param_'.$rr]['listname']][$rrr])) {
									$form['param_'.$rr.'_'.$rrr] = array(
										'caption'=>$this->_enum[$form['param_'.$rr]['listname']][0][$rrr]['#name#'],//$PARAM->data[$rr]['name'],
										'type'=>'checkbox',
										'multiple'=>1,
										'type2'=>$PARAM->data[$rr]['type'],
										'value'=>(isset($id['param_'.$rr.'_'.$rrr])?$id['param_'.$rr.'_'.$rrr]:''),
										'css'=>'addparam',
										'listname'=>'fli'.$PARAM->data[$rr]['formlist'],
										'mask' => array('begin'=>$rrr),
									);
									$_tpl['onload'] .= 'mCBoxVis(\''.$rr.'_'.$rrr.'\');';
								}
								else
									unset($form['param_'.$rr.'_'.$rrr]);
							}}
							if(count($this->_enum[$form['param_'.$rr]['listname']])>1)
								$_tpl['onload'] .= 'mCBoxCA('.$rr.');';
						}

					}

				}
				else return array();
			}
			return $form;
		}
		return array();
	}

	/**
	* param $Flag - list: список товаров со всеми подкатегориями, listn: список товаров только текущей категории, cnt:  , 
	*
	*/
	public function fList($rid,$filter,$Flag='list',$order='t1.mf_timecr',$limit=0) {
		//$this->owner->data - кэш рубрик
		//$this->owner->data2 - кэш рубрик
		//$PARAM->data
		// if $limit>0 без постранички
		$xml=array();

		$PARAM = &$this->owner->childs['rubricparam'];
		if(isset($PARAM->data)){
			reset($PARAM->data);
			$temp = current($PARAM->data);
		}
		if(!$temp or $temp['owner_id']!=$rid) { // для RSS и рассылки
			$listfields = array('*');
			$cls = 'WHERE owner_id="'.$rid.'" and active=1 order by ordind';
			$PARAM->data = $PARAM->_query($listfields,$cls,'id');
		}
		$clauseF=array();
		$lcnt=4;
		$type='';
		if(count($filter) and isset($filter['sbmt'])) {
			foreach($filter as $k=>$r) {
				if($k=='id' or $k=='shop') continue;
				$tempid = substr($k,6);
				if(isset($PARAM->data[$tempid])) {
					$nameK = $PARAM->data[$tempid]['type'];
					$type = $PARAM->getTypeForm($nameK);
					if($type=='checkbox') {
						if($r!='')
							$clauseF[$k] = 't4.name'.$nameK.'="'.$r.'"';
					}
					elseif($type=='int') {
						$temparr=array();
						$r = (int)$r;
						if($r and (!$this->filter_form[$k]['mask']['minint'] or $this->filter_form[$k]['mask']['minint']<$r))
							$clauseF[$k] = 't4.name'.$nameK.'>='.(int)$r;
						if((int)$filter[$k.'_2'] and (!$this->filter_form[$k]['mask']['maxint'] or $this->filter_form[$k]['mask']['maxint']>(int)$filter[$k.'_2'])) {
							$clauseF[$k.'_2'] = 't4.name'.$nameK.'<='.(int)$filter[$k.'_2'];
						}
					}
					elseif(is_array($r)) {
						if(count($r)) {
							$t3mp = $r;
							foreach($r as $kk=>$rr) {
								if(isset($filter['param_'.$tempid.'_'.$rr]) and count($filter['param_'.$tempid.'_'.$rr])) {
									$t3mp = array_merge($t3mp,$filter['param_'.$tempid.'_'.$rr]);
								}
								elseif(isset($this->_enum[$this->filter_form[$k]['listname']][$rr]) and is_array($this->_enum[$this->filter_form[$k]['listname']][$rr])) {
									foreach($this->_enum[$this->filter_form[$k]['listname']][$rr] as $kkk=>$rrr)
										$t3mp[] = $rrr['#id#'];
								}
							}
							$clauseF[$k] = 't4.name'.$nameK.' IN ("'.implode('","',$t3mp).'")';
						}
					}
					else {
						if($r)
							$clauseF[$k] = 't4.name'.$nameK.'="'.$this->SqlEsc($r).'"';
					}
				}
				//elseif($k=='mf_timecr')
				//	$clauseF[$k] = 't1.mf_timecr<"'.$r.'"';
				elseif(isset($this->fields[$k]) and isset($this->fields_form[$k])) {
					if(isset($filter[$k.'_2']) and $this->fields_form[$k]['type']=='int'){
						$r = (int)$r;
						if($r and (!$this->filter_form[$k]['mask']['minint'] or $this->filter_form[$k]['mask']['minint']<$r))
							$clauseF[$k] = 't1.'.$k.'>='.$r;
						if((int)$filter[$k.'_2'] and (!$this->filter_form[$k]['mask']['maxint'] or $this->filter_form[$k]['mask']['maxint']>(int)$filter[$k.'_2']))
							$clauseF[$k.'_2'] = 't1.'.$k.'<='.(int)$filter[$k.'_2'];
					}
					elseif($this->fields_form[$k]['type']=='list'){
						if(is_array($r) and count($r)) {
							foreach($r as &$ar)
								$ar = $this->SqlEsc($ar);
							unset($ar);
							$clauseF[$k] = 't1.'.$k.' IN ("'.implode('","',$r).'")';
						}
						elseif($r!='')
							$clauseF[$k] = 't1.'.$k.'="'.$this->SqlEsc($r).'"';
					}
					elseif($k=='text' or $k=='descr'){
						if($r!='')
							$clauseF[$k] = 't1.'.$k.' LIKE "%'.$this->SqlEsc($r).'%"';
					}
					elseif($k=='mf_timecr')
						$clauseF[$k] = 't1.mf_timecr>"'.(int)$r.'"';
				}
				elseif($r=='1' and $k=='foto') {
					$temp=array();
					foreach($this->attaches as $tk=>$tr)
						$temp[] = 't1.'.$tk.'!=""';
					$clauseF[$k] = '('.implode(' or ',$temp).')';
				}
			}
			if(count($clauseF)) // Данные по фильтру отправим в шаблон на всякий
				$xml['#filter#'] = $clauseF;
		}

		$clause['from'] = 'FROM '.$this->tablename.' t1 ';//1
		$clause['ljoin'] = ' LEFT JOIN product_value t4 ON t4.owner_id=t1.id ';//2
		$clause['where'] = ' WHERE t1.active=1 ';//4

		$rlist = array();
		if($rid) {
			if(isset($this->owner->data2[$rid]))
				$rlist[$rid] = $this->owner->data2[$rid]['name'];
			if(isset($this->owner->data[$rid]) and $Flag!='listn') {
				foreach($this->owner->data[$rid] as $k=>$r) {
					if(isset($this->owner->data[$k])){
						$rlist = $this->owner->data[$k]+$rlist;
					}else
						$rlist[$k]=$r;						
				}
			}
		}
		if(count($rlist))
			$clause['where'] .= ' and t1.shop IN ('.implode(',',array_keys($rlist)).') ';

		$cls_filtr = '';
		if(count($clauseF)) {
			$clause['where'] .= ' and '.implode(' and ',$clauseF);
			$cls_filtr = $clause['ljoin'];
		}

		$result = $this->SQL->execSQL('SELECT count(DISTINCT t1.id) as cnt '.$clause['from'].$cls_filtr.$clause['where']);
		if(!$result->err and $row = $result->fetch() and $row['cnt']>0) {
			if(is_string($Flag) and $Flag=='cnt') return $row['cnt'];
			if(!$limit) {
				$xml['cnt'] = $row['cnt'];

				$xml['pagenum'] = $this->fPageNav($row['cnt']);
				$pcnt = $xml['pcnt'] = $xml['pagenum']['start'];// Начальный отчет элементов на странице

			}

			$clause['where'] .= ' GROUP BY t1.id ORDER BY '.$order.' DESC';
			if(!$limit)
				$clause['where'] .= ' LIMIT '.$pcnt.', '.$this->messages_on_page;
			else
				$clause['where'] .= ' LIMIT '.$limit;
			$pData = $this->fGetParamproduct('SELECT t4.*, t1.* '.implode(' ',$clause));// retutn $this->data
			if(static_main::_prmUserCheck() and static_main::_prmModul($this->_cl, array(3)))
				$moder = 1;
			else
				$moder = 0;
			foreach($this->data as $k=>$r) {
				$rname=array();
				$temp=$r['shop'];
				while(isset($this->owner->data2[$temp]) and $rid!=$temp) {
					$rname[] = $this->owner->data2[$temp]['name'];
					$temp=$this->owner->data2[$temp]['parent_id'];
				};
				$tempData = $r;
				$tempData['rpath']=$this->owner->data2[$r['shop']]['path'];
				if(count($rname)) 
					$tempData['rname'] = $rname;

				foreach($this->attaches as $tk=>$tr)
					if($r[$tk]!='')
						if($r[$tk]!='' and $file=$this->_get_file($r['id'],$tk,$r[$tk]))
							$tempData['image'][] = array($file, $this->_get_file($r['id'],$tk,$r[$tk],1));

				$xml['#item#'][] = $tempData;
			}

			if(isset($_COOKIE['checkloadfoto']) and $_COOKIE['checkloadfoto']=='0')
				$xml['imcookie'] = 0;
			else
				$xml['imcookie'] = 1;
		}
		return $xml;
	}

	/*public function fDisplayList($limit) {
		$PARAM = &$this->owner->childs['rubricparam'];
		$clause = 'SELECT t1.id,t1.path,t1.name,t1.shop,t1.descr, FROM '.$this->tablename.' t1 WHERE t1.active=1 ';

		$clause .= ' ORDER BY t1.mf_timecr DESC LIMIT '.$limit;
		
		$result = $this->SQL->execSQL($clause);
		$DATA = array();
		$xml='<main>';
		if(!$result->err) {
			while ($r = $result->fetch()) {
				$DATA[$r['id']] = $r;
			}
			if(count($DATA)) {
					foreach($DATA as $r) {
						$xml .='<item>
							<id>'.$r['id'].'</id>
							<path>'.$r['path'].'</path>
							<name>'.$r['name'].'</name>
							<rname>'.$this->owner->data2[$r['shop']]['name'].'</rname>
							<descr><![CDATA['._substr(html_entity_decode(strip_tags($r['descr']),ENT_QUOTES,'UTF-8'),0,200).'...]]></descr>
							<mf_timecr>'.date('Y-m-d',$r['mf_timecr']).'</mf_timecr>';
						$xml .= '<rubpath>/'.$this->owner->data2[$r['shop']]['path'].'</rubpath>';
						$xml .= '</item>';
					}
			}
		}
		$xml .='</main>';


		return $xml;
	}*/

	public function fItem($id) {//$id - число либо массив
		$idt= explode(',',$id);
		$arr_stat=$id=array();
		foreach($idt as $r)//сохр тока уник знач
			$id[(int)$r]=(int)$r;
		$PARAM = &$this->owner->childs['rubricparam'];
		$clause = 'SELECT t3.*, t1.*, GROUP_CONCAT(t2.id,":",t2.name,":",t2.type,":",t2.formlist,":",t2.edi ORDER BY t2.ordind SEPARATOR "|") as param FROM '.$this->tablename.' t1
		LEFT JOIN product_value t3 ON t1.id=t3.owner_id  
		LEFT JOIN '.$PARAM->tablename.' t2 ON t1.shop=t2.owner_id and t2.active 
		WHERE t1.active=1 and t1.id IN ('.implode(',',$id).') 
		GROUP BY t1.id ORDER BY t1.mf_timecr DESC';
		
		$this->fGetParamproduct($clause);
		
		///** Nomination **///
		/*$clause = 'SELECT * FROM product_vote WHERE owner_id IN ('.implode(',',$id).')';
		if(static_main::_prmUserCheck())
			$clause .= ' and '.$this->mf_createrid.'="'.$_SESSION['user']['id'].'"';
		else
			$clause .= ' and mf_ipcreate=INET_ATON("'.$_SERVER["REMOTE_ADDR"].'")';
		$result = $this->SQL->execSQL($clause);
		while($row = $result->fetch()) {
			$this->data[$row['owner_id']]['nomination'][$row['type']] = 1;
		}*/
		///////////
		$xml = $this->fDataCreate(1);

		return $xml;
	}
	
	function fDataCreate($statview=0) {
		$arr_stat = array();
		$this->owner->simplefCache();
		$DATA = array();
		if(static_main::_prmUserCheck() and static_main::_prmModul($this->_cl, array(3)))
			$moder = 1;
		else
			$moder = 0;
		foreach($this->data as $k=>&$r) {
			/*$r['shops']=array();
			$rname=array();
			$temp=$r['shop'];
			while(isset($this->owner->data2[$temp])) {
				$r['shops'][] = array('id'=>$temp, 'name'=>$this->owner->data2[$temp]['name']); // product.inc for path
				$rname[] = $this->owner->data2[$temp]['name'];
				$temp=$this->owner->data2[$temp]['parent_id'];
			}*/
			$r['moder']=$moder;
			$r['rpath']=$this->owner->data2[$r['shop']]['path'];

			foreach($this->attaches as $tk=>$tr)
				if($r[$tk]!='')
					if($r[$tk]!='' and $file=$this->_get_file($r['id'],$tk,$r[$tk]))
						$r['image'][] = array($file, $this->_get_file($r['id'],$tk,$r[$tk],1));

			if(count($r['param']))
				foreach($r['param'] as $pk=>&$pr){
					$pr = array('name'=>$pr[1],'id'=>$pr[0], 'edi'=>$pr[4], 'value'=>$r['name'.$pr[2]]);
				}

			//$r['param'][] = array('name'=>'Цена','id'=>'', 'edi'=>'', 'value'=>($r['cost']?number_format($r['cost'], 0, ',', ' ').' руб.':' - '));

			/*if($this->_CFG['robot']=='' and !isset($_COOKIE['statview_'.$r['id']]) and $statview){
				$arr_stat[]=$r['id'];
				_setcookie('statview_'.$r['id'], 1, (time()+3600*24));
			}*/
		}

		if(count($arr_stat) and $statview){
			//statview
			$this->SQL->execSQL('UPDATE '.$this->tablename.' SET statview=statview+1 WHERE id IN ('.implode(',',$arr_stat).')');
		}
		return $this->data;
	}

	public function fGetParamproduct($clause) {
/*SELECT PARAMETR*/
		$idFL = array();
		$PARAM = &$this->owner->childs['rubricparam'];
		$this->data=$typeclass=$pData=$idList=$idFL=array();
		$result = $this->SQL->execSQL($clause);
		if(!$result->err)
			while ($row = $result->fetch()) {
				if(isset($row['param']) and $row['param']!=''){
					$row['param'] = explode('|',$row['param']);
					foreach($row['param'] as $k=>$r) {
						$r=$row['param'][$k] = explode(':',$r);// параметры поля
						$r[2] = (int)$r[2];
						if($r[2]<10)
							$row['name'.$r[2]] = $this->_CFG['enum']['yesno2'][$r[2]];
						elseif($r[2]>=50 and $r[2]<60) {
							//записываем массив для выборки из списка
							$idFL[(int)$row['name'.$r[2]]][] = array($row['id'],'name'.$r[2]);
							$row['name'.$r[2]] = '';// чтобы не выводить отключенные
						}
						elseif($r[2]>=70 and $r[2]<80){
							$row['name'.$r[2]] = preg_replace("/^http:\/\/(www.)?([0-9A-Za-z\-\.]+)([\/0-9A-Za-z\.\_\=\?]*)$/",'<a href="/_redirect.php?url=\\0"  rel="nofollow">\\1\\2</a>',$row['name'.$r[2]]);
						}							
					}
				}
				$this->data[$row['id']] = $row;
			}
		else return 0;

		if(count($idFL)) {
			$clause = 'SELECT id,name FROM formlistitems
			WHERE id IN ('.implode(',',array_keys($idFL)).')';
			$result = $this->SQL->execSQL($clause);
			if(!$result->err)
				while ($row = $result->fetch()){
					foreach($idFL[$row['id']] as $r)
						$this->data[$r[0]][$r[1]]=$row['name'];
				}
			else return 0;
		}

		return 0;
	}

	/**
	* param $rid - текущая рубрика
	* param $flag - 0:форма для подписки, 1:поиск, 2:поиск и каталог 1го уровня выводить
	* param $page - алиас страницы
	*
	*/
	function productFindForm($rid,$flag=1,$page='catalog') {
		//$this->owner->data - кэш рубрик
		//$this->owner->data2 - кэш рубрик
		//if $flag==0 то это "утановка параметров при подписке"
//$newtime = getmicrotime();

		global $_tpl;
		$filter = $_REQUEST;
		$xml='';
		$datalist = array();

		$this->filter_form=array();


		if($flag) {
			$datalist[$rid] =$this->owner->data2[$rid]['name'];
			if(isset($this->owner->data[$rid])) {
				$datalist += $this->owner->data[$rid];
				//$this->_enum['shops'][$this->owner->data2[$rid]['parent_id']][$rid] = $this->owner->data2[$rid]['name'];
				//$this->_enum['shops'][$rid] =$this->owner->data[$rid];
				foreach($this->owner->data[$rid] as $k=>$r){
					if(isset($this->owner->data[$k])) {
						$datalist += $this->owner->data[$k];
						if($flag==1) {
							$this->_enum['shops'][$k]= array('#name#'=>$r,'#href#'=>$page.'/'.$this->owner->data2[$k]['path'].'.html');	
							foreach($this->owner->data[$k] as $rk=>$rr) {
								$this->_enum['shops'][$k]['#item#'][$rk]= array('#name#'=>$rr,'#href#'=>$page.'/'.$this->owner->data2[$rk]['path'].'.html');
							}
						}
					}elseif($flag==1)
						$this->_enum['shops'][$k]= array('#name#'=>$r,'#href#'=>$page.'/'.$this->owner->data2[$k]['path'].'.html');						
				}
				if($flag==1) {
					$this->filter_form['shopl']= array(
						'type'=>'linklist',
						'caption'=>'Каталог',
						//'onchange'=>'window.location.href=window.location.href.replace(\'_'.$rid.'\',\'_\'+this.value).replace(\'='.$rid.'\', \'=\'+this.value)',
						'valuelist'=>$this->_enum['shops']);
					if(isset($filter['shop']))
						$this->filter_form['shopl']['value']=$filter['shop'];
				}
			}
			$this->filter_form['shop']= array('type'=>'hidden','value'=>$rid);
		}
		else {
			$datalist[$rid] =(int)$filter['shop'];
			$this->_enum['shops'] = &$this->owner->data;
			//$this->_enum['shops'][0] = array(0=>'---')+$this->_enum['shops'][0];
			$this->filter_form['shop']= array(
				'type'=>'list',
				'listname'=>'shops',
				'caption'=>'Каталог',
				'onchange'=>'JSWin({\'href\':\''.$this->_CFG['_HREF']['siteJS'].'?_view2=subscribeparam\',\'data\':{\'shop\':this.value}})');
			if(isset($filter['shop']))
				$this->filter_form['shop']['value']=$filter['shop'];
		}

		$this->filter_form['cost'] = $this->fields_form['cost'];

			$temcls = ' WHERE t1.active=1 and t1.shop IN ('.implode(',',array_keys($datalist)).') ';

			$result2 = $this->SQL->execSQL('SELECT min(t1.cost) as mincost,max(t1.cost) as maxcost FROM '.$this->tablename.' t1 '.$temcls);
			if(!$result2 or !$minmax = $result2->fetch_array(MYSQL_NUM) or !$minmax[1])
				$minmax = array(0,$this->filter_form['cost']['mask']['maxint']);
		$step=$minmax[1]/216;
		if($step<5) $step=1;
		elseif($step==5) $step=5;
		elseif($step<=10) $step=10;
		elseif($step<=50) $step=50;
		elseif($step<=100) $step=100;
		elseif($step<=500) $step=500;
		elseif($step<=1000) $step=1000;
		elseif($step<=5000) $step=5000;
		elseif($step<=10000) $step=10000;
		elseif($step<=50000) $step=50000;
		else $step=100000;
		if($minmax[1]>$this->filter_form['cost']['mask']['maxint'])
			$minmax[1]= $this->filter_form['cost']['mask']['maxint'];
		if(isset($filter['cost'])) {
			$this->filter_form['cost']['value']=$filter['cost'];
			$this->filter_form['cost']['value_2']=$filter['cost_2'];
		}else{
			$this->filter_form['cost']['value']=$minmax[0];
			$this->filter_form['cost']['value_2']=$minmax[1];
		}
		$this->filter_form['cost']['mask']['minint']=$minmax[0];
		$this->filter_form['cost']['mask']['maxint']=$minmax[1];
		$this->filter_form['cost']['mask']['step'] = $step;
		$this->filter_form['text'] = array('type' => 'text','caption' => 'Ключевое слово','mask' =>array('max'=>128),'value'=>(isset($filter['text'])?$filter['text']:''));

		if($rid) {
			//$this->filter_form = static_main::insertInArray($this->filter_form,'shop',$this->ParamFieldsForm($filter,$rid,0,$temcls));
			$temp = $this->ParamFieldsForm($filter,$rid,0,$temcls);
			$this->filter_form += $temp;
		}

		if($flag) {
			/*$this->filter_form[$this->_cl.'_mop'] = array(
				'type' => 'list',
				'listname'=>'_MOP',
				'caption' => 'Объявлений на странице',
				'value'=>$this->messages_on_page);*/
			$this->filter_form['_*features*_']=array('name'=>'paramselect','action'=>'','method'=>'get');
			$this->filter_form['sbmt'] = array('type'=>'submit','value'=>'Поиск');
		}
		else {
			$this->filter_form['_*features*_']=array('name'=>'paramselect','action'=>'','method'=>'GET','onsubmit'=>'return getToText(this)');
			$this->filter_form['sbmt'] = array('type'=>'submit','value'=>'задать параметры');
		}
		$this->kFields2FormFields($this->filter_form);
		/*if($flag)
			$_tpl['onload'] .= '$(\'#form_tools_paramselect div.multiplebox input\').live(\'click\',multiCheckBox); $(\'#form_tools_paramselect input\').live(\'change\',filterChange);';*/
		return $this->filter_form;
	}

	public function AjaxShopParam() {
		global $HTML,$_tpl;
		$RESULT = array('html'=>'', 'html2'=>'', 'text'=>'','onload'=>'');
		$DATA  = array();
		$this->fields_form = array();
		$_GET['_rid']=(int)$_GET['_rid'];
		$_GET['_id']=(int)$_GET['_id'];
		$this->flag_AjaxBoardList = true;

		if($_GET['_rid'] and $form = $this->ParamFieldsForm($_GET['_id'],$_GET['_rid'])) {
			if(count($form) and $this->kFields2FormFields($form)) {
				$DATA['form'] = &$this->form;
				$RESULT['html'] = $HTML->transformPHP($DATA,'#pg#form');
			}
			//$RESULT['onload'] .= 'rclaim(\'type\');';
		}
		else {
			//$RESULT['onload'] .= '';
		}
		$RESULT['onload'] .= $_tpl['onload'];
		return $RESULT;
	}
}



class product_value_class extends kernel_extends {
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->showinowner=false;// не показывать
		$this->mf_createrid = false;
		$this->owner_unique = true; // уникальная запис для одного объявления
		$this->tablename = $this->owner->_cl.'_value';
		return true;
	}

	function _create() {
		parent::_create();
		$this->caption = 'Значения параметров';
		$rubricparam = &$this->owner->owner->childs['rubricparam'];
		foreach($rubricparam->_enum['type'] as $k=>$r) {
			if($k<10)
				$this->fields['name'.$k] =	array('type' => 'tinyint', 'width' =>1, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
			elseif($k<20)
				$this->fields['name'.$k] =	array('type' => 'smallint', 'width' =>4, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
			elseif($k<70)
				$this->fields['name'.$k] =	array('type' => 'int', 'width' =>11, 'attr' => 'UNSIGNED NOT NULL', 'default'=>0);
			elseif($k<73)
				$this->fields['name'.$k] = array('type' => 'varchar', 'width' =>254, 'attr' => 'NOT NULL','default'=>'');
			elseif($k<76)
				$this->fields['name'.$k] = array('type' => 'varchar', 'width' =>128, 'attr' => 'NOT NULL','default'=>'');
			elseif($k<80)
				$this->fields['name'.$k] = array('type' => 'varchar', 'width' =>64, 'attr' => 'NOT NULL','default'=>'');
			elseif($k<90)
				$this->fields['name'.$k] = array('type' => 'float', 'width' =>'11,2', 'attr' => 'NOT NULL','default'=>'0.00');
			else
				$this->fields['name'.$k] = array('type' => 'text', 'width' =>64, 'attr' => 'NOT NULL');
		}
	}

}
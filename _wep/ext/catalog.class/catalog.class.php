<?php
class catalog_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_istree = true;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = 'Каталог';
		$this->data_path = 
			$this->data3 = array();
		$this->_AllowAjaxFn = array('jsOrder'=>true);
		return true;
	}

	function _create() {
		parent::_create();

		$thumb = array('type'=>'resize', 'w'=>'120', 'h'=>'120');
		$maxsize = 3000;
		$this->attaches['img_catalog'] = array('mime' => array('image/pjpeg'=>'jpg', 'image/jpeg'=>'jpg', 'image/gif'=>'gif', 'image/png'=>'png'), 'thumb'=>array($thumb,array('type'=>'resizecrop', 'w'=>'60', 'h'=>'60', 'pref'=>'s_', 'path'=>'')),'maxsize'=>$maxsize,'path'=>'');

		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['lname'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['rname'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['checked'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL','default'=>'0');
		//$this->fields['chtext'] = array('type' => 'text', 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['cnt'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>'0');

		$this->index_fields['name'] = 'name';
		$this->unique_fields['rname'] = 'rname';
		$this->unique_fields['lname'] = 'lname';
		$this->index_fields['checked'] = 'checked';
		$this->index_fields['img_catalog'] = 'img_catalog';
		
		$this->selFields = 't1.id,t1.name,t1.rname,t1.lname,t1.parent_id,t1.img_catalog,t1.ordind,t1.checked,t1.active,t1.cnt';
		//id,name,rname,lname,parent_id,img_catalog,ordind,checked,active
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название каталога');
		$this->fields_form['rname'] = array('type' => 'text', 'caption' => 'Адресс рускими буквами');
		$this->fields_form['lname'] = array('type' => 'text', 'caption' => 'Адресс латинскими буквами');
		$this->fields_form['parent_id'] = array('type' => 'list', 'listname'=>'parentlist', 'caption' => 'Родительский каталог','mask' =>array('fview'=>1));
		$this->fields_form['img_catalog'] = array('type'=>'file','caption'=>'Пиктограмма','del'=>1, 'mask'=>array('fview'=>1,'width'=>80,'height'=>80), 'comment'=>static_main::m('_file_size').$this->attaches['img_catalog']['maxsize'].'Kb');	
		$this->fields_form["ordind"] = array("type" => "int", "caption" => "Сортировка");
		//$this->fields_form["chtext"] = array("type" => "textarea", "caption" => "Текст",'mask' =>array('name'=>'all'));
		//$this->fields_form['checked'] = array('type' => 'checkbox', 'caption' => 'Разрешить для подачи объявления');
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');

	}

	function _childs() {
		$this->create_child('param');
		$this->create_child("countb");
		$this->create_child("product");
	}

	function catalogCache() {
		$this->data2=$this->data=array();
		$clause = 'SELECT '.$this->selFields.' FROM '.$this->tablename.' t1 WHERE t1.active=1 GROUP BY t1.id ORDER BY t1.parent_id,t1.ordind';
		$result = $this->SQL->execSQL($clause);
		if(!$result->err) {
			$ar_last = array();
			while ($row = $result->fetch()){
				$this->data2[$row['id']] = $row;
				$this->data[$row['parent_id']][$row['id']] = $row['name'];
				$this->data3[$row['parent_id']][$row['id']] ['name'] = $row['name'];
				$this->data3[$row['parent_id']][$row['id']] ['img_catalog'] = $this->_get_file($row['id'],'img_catalog',$row['img_catalog']);
				$this->data3[$row['parent_id']][$row['id']] ['s_img_catalog'] = $this->_get_file($row['id'],'img_catalog',$row['img_catalog'],1);
				$this->data3[$row['parent_id']][$row['id']] ['cnt'] = $row['cnt'];
				$this->data3[$row['parent_id']][$row['id']] ['path'] = $row['lname'];
				if($row['parent_id']) {
					if(isset($this->data2[$row['parent_id']])) {
						$tempid = $this->data2[$row['parent_id']]['parent_id'];
						$this->data3[$tempid] [$row['parent_id']] ['cnt'] += (int)$row['cnt'];
					} else
						$ar_last[] = $row;
				}
				$this->data_path[$row['lname']] = $row['id'];
			}
			if(count($ar_last)) {
				foreach($ar_last as $row) {
					if(isset($this->data2[$row['parent_id']])) {
						$tempid = $this->data2[$row['parent_id']]['parent_id'];
						$this->data3[$tempid] [$row['parent_id']] ['cnt'] += (int)$row['cnt'];
					}
				}
			}
		}
		return 0;	
	}

	function simpleCatalogCache() {
		if(isset($this->data2) and count($this->data2)) return 0;
		$this->data2=$this->data=array();
		$clause = 'SELECT '.$this->selFields.' FROM '.$this->tablename.' t1 WHERE t1.active=1 ORDER BY t1.parent_id,t1.ordind';
		$result = $this->SQL->execSQL($clause);
		if(!$result->err)
			while ($row = $result->fetch()){
				$this->data2[$row['id']] = $row;
				$this->data[$row['parent_id']][$row['id']] = $row['name'];
				$this->data_path[$row['lname']] = $row['id'];
		}
		return true;	
	}

	function MainCatalogDisplay() {
		global $PGLIST;
		if(!$this->data3) $this->catalogCache();
		if(isset($PGLIST->pageParam[0]) and $PGLIST->pageParam[0] and isset($this->data_path[$PGLIST->pageParam[0]]))
			$this->id = $this->data_path[$PGLIST->pageParam[0]];
		else
			$this->id = 0;
		return $this->_forlist($this->data3,0,$this->id);
	}

	function getPath($id) {
		global $PGLIST;
		$temp = $id;
		$tpath= array();
		while(isset($this->data2[$temp])) {
			$PGLIST->pageinfo['keywords'] .= ', '.$this->data2[$temp]['name'];
			$tpath[$this->data2[$temp]['lname'].'/'.$PGLIST->getHref()] = array('name'=>$this->data2[$temp]['name']);
			$temp=$this->data2[$temp]['parent_id'];
		}
		if(count($tpath))
			$PGLIST->pageinfo['path']=$PGLIST->pageinfo['path']+array_reverse($tpath);	
	}
	//HOOK
	function sdfs($MAIL) {
		$MAIL->fields_form['from']['caption'] = 'Ваш Email';

		$MAIL->fields_form['p_count'] = array('type'=>'int','caption'=>'Количество', 'mask'=>array('minint' => '1'),'default'=>'1');
		$MAIL->fields_form['p_addr'] = array('type'=>'text','caption'=>'Адрес доставки', 'mask'=>array('min' => '10'),'default'=>'Уфа, ');
		$MAIL->fields_form['p_phone'] = array('type'=>'text','caption'=>'Телефон', 'mask'=>array('min' => '5'),'default'=>'+7','comment'=>'Пример: +7-987-254-00-28, +7-347-298-23-88');
		$MAIL->fields_form['p_comment'] = array('type'=>'textarea','caption'=>'Дополнительная информация', 'mask'=>array('max' => '500'));
	}

	function jsOrder() {
		$html = '';
		$mess = array('error','Ошибка данных!');
		$res = array('html'=>'');
		$PRODUCT = &$this->childs['product'];
		$PRODUCT->id = (int)$_GET['id'];
		if($PRODUCT->id) {
			$data = $PRODUCT->_select();
			if(count($data)) {
				require_once($this->_CFG['_PATH']['core'].'/html.php');
				global $HTML;
				$HTML = new html('_design/','default',false);

				_new_class('mail', $MAIL);
				_new_class('ugroup',$UGROUP);

				$MAIL->HOOK['setFieldsForm'] = array($this,'sdfs');

				$DATA = array();
				$cap = 'Заказ товара №'.$_GET['id'].' ('.$data[$PRODUCT->id]['name'].')';
				if(count($_POST)) {
					$_POST['text'] = 'Товар: '.$data[$PRODUCT->id]['name'].' , #'.$PRODUCT->id.' <br/> 
					Адрес доставки: '.$_POST['p_addr'].' <br/> 
					Телефон: '.$_POST['p_phone'].' <br/> 
					Кол-во: '.$_POST['p_count'].' <br/> 
					Email: '.(isset($_SESSION['user']['email'])?$_SESSION['user']['email']:$_POST['from']).' <br/> 
					Дополнительно: '.$_POST['p_comment'];
					$_POST['subject'] = $cap;
				}else {
				}

				list($DATA['formcreat'],$flag) = $MAIL->mailForm($UGROUP->config['mail_to']);
				if(isset($DATA['formcreat']['form']['text'])) {
					if(isset($DATA['formcreat']['form']['from']))
						$DATA['formcreat']['form']['from']['caption'] = 'Ваш Email';
					unset($DATA['formcreat']['form']['text']);
					unset($DATA['formcreat']['form']['subject']);
					unset($DATA['formcreat']['form']['text_ckedit']);
					unset($DATA['formcreat']['form']['status']);
					unset($DATA['formcreat']['form']['mail_to']);
					unset($DATA['formcreat']['form']['creater_id']);
					unset($DATA['formcreat']['form']['user_to']);
					$DATA['formcreat']['form']['_info']['caption'] = $cap;
				}

				if($flag==1) {
					$DATA['formcreat']['messages'][0]['value'] = 'Ваш заказ принят на расмотрение. В дальнейшем с вами свяжется наш менеджер.';
					//$HTML->_templates = "waction";
					if(isset($DATA['formcreat']['messages']))
						$html = $HTML->transformPHP($DATA['formcreat'],'messages');
				}
				else {
					$html = $HTML->transformPHP($DATA,'formcreat');
					$res['eval'] = '$(\'#form_mail\').submit(function(){ JSWin({\'type\':this}); return false;});';
				}
			}
		}
		if(!$html)
			$html = '<div class="messages"><div class="'.$mess[0].'">'.$mess[1].'</div></div>';
		$res['html'] = $html;
		return $res;
	}
}

class param_class extends kernel_extends {

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->mf_ordctrl = true;
		$this->mf_actctrl = true;
		$this->caption = 'Параметры';
		return true;
	}

	function getTypeForm($type) {
		if($type<10)
			return 'checkbox';
		elseif($type<30)
			return 'int';
		elseif($type<60)
			return 'list';
		else
			return 'text';
	}

	function _create() {
		parent::_create();
		$this->_enum['typelist'] = array(
			0=>'Простой список',
			1=>'AJAX список',
			2=>'CHECKBOX список',
		);
		$this->_enum['type'] = array(
			0=>'CheckBox0',
			1=>'CheckBox1',
			2=>'CheckBox2',
			3=>'CheckBox3',
			4=>'CheckBox4',
			5=>'CheckBox5',
			10=>'Целое(4)0',
			11=>'Целое(4)1',
			12=>'Целое(4)2',
			13=>'Целое(4)3',
			20=>'Целое(11)0',
			21=>'Целое(11)1',
			50=>'Cписок 0',
			51=>'Cписок 1',
			52=>'Cписок 2',
			53=>'Cписок 3',
			54=>'Cписок 4',
			55=>'Cписок 5',
			56=>'Cписок 6',
			57=>'Cписок 7',
			58=>'Cписок 8',
			59=>'Cписок 9',
			70=>'Текст(254)0',
			71=>'Текст(254)1',
			//80=>'Дробное0'
			//90=>'Текст0'
			);

		# fields
		$this->fields['name'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL');
		$this->fields["type"] = array("type" => "tinyint", "width" =>4, "attr" => 'NOT NULL', 'default'=>'0');
		$this->fields["typelist"] = array("type" => "tinyint", "width" =>4, "attr" => 'NOT NULL', 'default'=>'0');
		$this->fields["formlist"] = array("type" => "tinyint", "width" =>4, "attr" => 'NOT NULL', 'default'=>'0');
		$this->fields['constrn'] = array("type" => "tinyint", "width" =>1, "attr" => 'NOT NULL', 'default'=>'0');
		$this->fields['edi'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['def'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields["min"] = array("type" => "int", "width" =>8, "attr" => 'NOT NULL', 'default'=>'0');
		$this->fields["max"] = array("type" => "int", "width" =>8, "attr" => 'NOT NULL', 'default'=>'0');
		$this->fields["step"] = array("type" => "int", "width" =>8, "attr" => 'NOT NULL', 'default'=>'1');
		$this->fields['mask'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['maskn'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default'=>'');
		$this->fields['comment'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default'=>'');

	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
		# fields
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название', 'mask' =>array('min'=>1));
		$this->fields_form["type"] = array("type" => "list", "listname"=>"type", "caption" => "Тип параметра", 'mask'=>array('sort'=>1), 'onchange'=>'if(this.value>=50 &amp;&amp; this.value&lt;60) jQuery(\'#tr_formlist, #tr_typelist\').show(); else jQuery(\'#tr_formlist, #tr_typelist\').hide();');
		$this->fields_form['typelist'] = array('type' => 'list', 'listname'=>'typelist', 'caption' => 'Вид списка', 'style'=>'background:#e1e1e1;');
		$this->fields_form['formlist'] = array("type" => "list",'listname'=>array('tablename'=>'formlist'), 'caption' => 'Список', 'style'=>'background:#e1e1e1;');
		$this->fields_form['constrn'] = array('type' => 'checkbox', 'caption' => 'В имени объявления');
		$this->fields_form['edi'] = array('type' => 'text', 'caption' => 'Ед. измерения');
		$this->fields_form['def'] = array('type' => 'text', 'caption' => 'Значение по умолчанию','comment'=>'Если в начале прописать "eval=", то будет выполнятся команда');
		$this->fields_form['min'] = array('type' => 'int', 'caption' => 'Минимум','comment'=>'Минимум символов или минимальное число, 0 - поле не обязательное');
		$this->fields_form['max'] = array('type' => 'int', 'caption' => 'Максимум','comment'=>'Максимум символов или максимальное число, 0 - максимум соответствует типу');
		$this->fields_form['step'] = array('type' => 'int', 'caption' => 'Шаг','comment'=>'Если "Тип параметра" целое число, нужен шаг для поиска по параметрам');
		$this->fields_form['mask'] = array('type' => 'text', 'caption' => 'Маска(поиск точного соответствия)', 'mask' =>array('name'=>'all'), 'comment'=>'/^(http:\/\/)?([A-Za-zЁёА-Яа-я\.]+\.)?[0-9A-Za-zЁёА-Яа-я\-\_]+\.[A-Za-zЁёА-Яа-я]+[\/0-9A-Za-zЁёА-Яа-я\.\-\_\=\?\&]*$/u');
		$this->fields_form['maskn'] = array('type' => 'text', 'caption' => 'Маска(поик не соответствия)', 'mask' =>array('name'=>'all'), 'comment'=>'/[^0-9A-Za-zЁёА-Яа-я:\/\.\-\_\=\?\&]/u');
		$this->fields_form['comment'] = array('type' => 'text', 'caption' => 'Комменты', 'mask' =>array('name'=>'all'));
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Активность');

	}

	function kPreFields(&$data,&$param) {
		$mess = parent::kPreFields($data,$param);
		if($data['type']<50 or $data['type']>=60) {
			$this->fields_form['typelist']['style'] = $this->fields_form['formlist']['style'] .='display:none;';
		}
		return $mess;
	}

}


class countb_class extends kernel_extends {
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->showinowner=false;// не показывать
		$this->mf_createrid = false;
		$this->mf_namefields = false;
		return true;
	}

	function _create() {
		parent::_create();
		$this->caption = 'Подсчёт';
		$this->fields['city'] = array('type' => 'int', 'width' => 7,'attr' => 'NOT NULL');
		$this->fields['owner_id'] = array('type' => 'int', 'width' => 7,'attr' => 'NOT NULL');
		$this->fields['cnt'] = array('type' => 'int', 'width' => 7,'attr' => 'NOT NULL');

		$this->unique_fields['oc'] = array('owner_id','city');
	}

}

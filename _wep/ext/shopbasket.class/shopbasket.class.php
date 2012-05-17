<?php
/** Корзина для интеренет магазина
*
* Для включения "Корзины" достаточно подключить INC basket(Корзина)
* Варианта заказа : форма письма, онлайн покупка через платежные системы
*
*
*/
class shopbasket_class extends rubric_class {

	/*protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		$this->config['orderset'] = array(0 => '0');
		$this->config_form['orderset'] = array('type' => 'list', 'listname'=>'orderset', 'multiple'=>1, 'caption'=>'Варианты заказа товара');
	}*/

	function _set_features() {
		if (!parent::_set_features()) return false;

		$this->ver = '0.0.1';
		$this->caption = 'Корзина';
		$this->_AllowAjaxFn['jsAddBasket'] = true;
		$this->basketEnabled = false;

		/*$this->_enum['orderset']=array(
			0=>'Письмом',
			1=>'Онлайн оплата');*/

		return true;
	}

	protected function _create() {
		parent::_create();

		$this->fields['id_product'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL');
		$this->fields['count'] = array('type' => 'tinyint', 'width' => 3, 'attr' => 'NOT NULL', 'default'=>1);
		$this->fields['cost_item'] = array('type' => 'float', 'width' => '8,2', 'attr' => 'NOT NULL', 'default'=>'0.00', 'min' => '1');
		$this->fields['cost'] = array('type' => 'float', 'width' => '8,2', 'attr' => 'NOT NULL', 'default'=>'0.00', 'min' => '1');
		$this->fields['statview'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default'=>0);
		$this->fields['checked'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default'=>0);

		//$this->ordfield = 'name DESC';

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


	/*function _childs() {
		parent::_childs();
		$this->create_child('product');
	}*/

	/*function allChangeData($type = '', $data = '') {
		unlink($this->YML_FILE);
		return parent::allChangeData($type, $data);
	}*/

	function jsAddBasket() {
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
				if(!$HTML) $HTML = new html('_design/','default',false);

				_new_class('mail', $MAIL);
				_new_class('ugroup',$UGROUP);

				$MAIL->HOOK['getFieldsForm'] = array($this,'sdfs');

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
						$html = $HTML->transformPHP($DATA['formcreat'],'#pg#messages');
				}
				else {
					$html = $HTML->transformPHP($DATA,'#pg#formcreat');
					$res['eval'] = '$(\'#form_mail\').submit(function(){ JSWin({\'type\':this}); return false;});';
				}
			}
		}
		if(!$html)
			$html = '<div class="messages"><div class="'.$mess[0].'">'.$mess[1].'</div></div>';
		$res['html'] = $html;
		return $res;
	}

	/** Список товаров положенных в корзину
	*
	*
	*/
	function fBasketList() {
		$RESULT = array();
		return $RESULT;
	}

	function fBasketData() {
		$RESULT = array();
		return $RESULT;
	}
}

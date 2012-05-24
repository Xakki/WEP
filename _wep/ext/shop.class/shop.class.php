<?php
/** Каталог товаров (интеренет магазин)
*
* Для включения "Корзины" достаточно подключить INC basket(Корзина)
* Варианта заказа : форма письма, онлайн покупка через платежные системы
*
*
*/
class shop_class extends rubric_class {

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		

		$this->config['orderset'] = array(0 => '0');

		$this->config_form['orderset'] = array('type' => 'list', 'listname'=>'orderset', 'multiple'=>1, 'caption'=>'Варианты заказа товара');
		$this->config_form['yml_info'] = array('type' => 'html', 'value'=>'Ссылка на XML Яндекс.Маркета <b><a href="'.$this->_CFG['_HREF']['BH'].'yml.xml" target="_blank">'.$this->_CFG['_HREF']['BH'].'yml.xml</a></b>');
		//http://help.yandex.ru/partnermarket/?id=1111425
	}

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->ver = '0.0.3';
		$this->caption = 'Магазин - Каталог';
		$this->_AllowAjaxFn['jsOrder'] = true;
		//$this->cf_tools[] = array('func'=>'ImportXls','name'=>'Загрузка прайса');
		$this->YML_FILE = $this->_CFG['_PATH']['content'].'yml.xml';
		$this->basketEnabled = false;

		$this->_enum['orderset']=array(
			0=>'Заказ письмом',
			1=>'В корзину');

		return true;
	}

	/*function _create() {
		parent::_create();
	}

	public function setFieldsForm($form=0) {
		parent::setFieldsForm($form);
	}*/

	function _childs() {
		parent::_childs();
		$this->create_child('product');
	}

	//HOOK
	function sdfs($MAIL) {
		$MAIL->fields_form['from']['caption'] = 'Ваш Email';

		$MAIL->fields_form['p_count'] = array('type'=>'list', 'listname'=>array('count',1,10), 'caption'=>'Количество', 'mask'=>array('minint' => '1','maxint'=>10),'default'=>'1');
		$MAIL->fields_form['p_addr'] = array('type'=>'text','caption'=>'Адрес доставки', 'mask'=>array('min' => '10'),'default'=>'Уфа, ');
		$MAIL->fields_form['p_phone'] = array('type'=>'text','caption'=>'Телефон', 'mask'=>array('min' => '5'),'default'=>'+7','comment'=>'Пример: +7-987-254-00-28, +7-347-298-23-88');
		$MAIL->fields_form['p_comment'] = array('type'=>'textarea','caption'=>'Дополнительная информация', 'mask'=>array('max' => '500'));
	}

	function allChangeData($type = '', $data = '') {
		unlink($this->YML_FILE);
		return parent::allChangeData($type, $data);
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

	function getPath($id, $page, $startId=0) {
		global $_tpl;
		$temp = $id;
		$tpath= array();
		while(isset($this->data2[$temp])) {
			$_tpl['keywords'] .= ', '.$this->data2[$temp]['name'];
			$tpath[$page.'/'.$this->data2[$temp]['path']] = array('name'=>$this->data2[$temp]['name']);
			$temp=$this->data2[$temp]['parent_id'];
			if($startId==$temp) break;
		}
		return array_reverse($tpath);
	}

	function fBasketData() {
		$RESULT = array();
		if(!_new_class('shopbasket',$SHOPBASKET)) return false;
		return $SHOPBASKET->fBasketData();
	}
}

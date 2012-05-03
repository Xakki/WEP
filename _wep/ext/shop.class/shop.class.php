<?php
class shop_class extends rubric_class {

	protected function _create_conf() {/*CONFIG*/
		parent::_create_conf();
		

		//$this->config['yml_info'] = '';

		$this->config_form['yml_info'] = array('type' => 'html', 'value'=>'<h3>Настройка Яндекс.Маркета</h3> Ссылка на XML <b><a href="'.$this->_CFG['_HREF']['BH'].'yml.xml" target="_blank">'.$this->_CFG['_HREF']['BH'].'yml.xml</a></b>');
		//http://help.yandex.ru/partnermarket/?id=1111425
	}

	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->ver = '0.0.2';
		$this->caption = 'Каталог товаров';
		$this->_AllowAjaxFn['jsOrder'] = true;
		$this->cf_tools[] = array('func'=>'ImportXls','name'=>'Загрузка прайса');
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

	function toolsImportXls() {
		global $_tpl;
		$fields_form = $mess = array();
		if (!static_main::_prmModul($this->_cl, array(5, 7)))
			$mess[] = static_main::am('error', 'denied', $this);
		elseif (count($_POST) and $_POST['sbmt']) {
			if($_FILES['xls']['tmp_name']) {
				$DT = $this->dumpXlsData($_FILES['xls']['tmp_name']);
				$this->_tableClear();
				foreach($DT['dataCat'] as $r) {
					$this->_add($r);
				}

				$prodName = array(
					1 => 'id',
					2 => 'code',
					3 => 'name',
					4 => 'model',
					5 => 'articul',
					6 => 'madein',
					7 => 'cost',
					'shop'=>'shop'
				);
				$optName = array(
				);
$cc = 0;

				$this->childs['product']->_tableClear();
				$this->childs['product']->childs['product_value']->_tableClear();
				foreach($DT['dataProd'] as $r) {
					$tmpProd = array();
					foreach($prodName as $kk=>$rr) {
						if(isset($r[$kk]))
							$tmpProd[$rr] = $r[$kk];
					}

					$tmpOpt = array();
					if(count($optName)) {
						foreach($optName as $kk=>$rr) {
							if(isset($r[$kk]))
								$tmpOpt[$rr] = $r[$kk];
						}
					}
					$this->childs['product']->_add($tmpProd);

					if(count($tmpOpt)) {
						$tmpOpt['owner_id'] = $this->childs['product']->id;
						$this->childs['product']->childs['product_value']->_add($tmpOpt);
					}
				}
				$mess[] = static_main::am('ok', 'Сделано', $this);
			}
			else
				$mess[] = static_main::am('ok', 'Фаил не загружен', $this);
		} else {
			$fields_form['_info'] = array(
				'type' => 'info',
				'caption' => '<h2 style="text-align:center;">Импорт товаров из XLS</h2>');
			$fields_form['xls'] = array(
				'type' => 'file',
				'caption' => 'Прайс',
				'comment'=>'В формате xls',
				'mask'=>array(),
			);
			
			$fields_form['sbmt'] = array(
				'type' => 'submit',
				'value' => 'Импортировать',
			);
			self::kFields2FormFields($fields_form);
		}
		return Array('form' => $fields_form, 'messages' => $mess);
	}

	function dumpXlsData($file, $sheet=0) {

		error_reporting(E_ALL ^ E_NOTICE);
		require_once getLib('excel_reader2');
		$dataXLS = new Spreadsheet_Excel_Reader($file);

		$out = array(
			'dataCat'=>array(),
			'dataProd'=>array(),
			'info'=>array(),
		);

		$idCat=0;
		for($row=1;$row<=$dataXLS->rowcount($sheet);$row++) {
			$tmp = array();
			for($col=1;$col<=$dataXLS->colcount($sheet);$col++) {
				// Account for Rowspans/Colspans
				/*$rowspan = $this->rowspan($row,$col,$sheet);
				$colspan = $this->colspan($row,$col,$sheet);
				for($i=0;$i<$rowspan;$i++) {
					for($j=0;$j<$colspan;$j++) {
						if ($i>0 || $j>0) {
							$this->sheets[$sheet]['cellsInfo'][$row+$i][$col+$j]['dontprint']=1;
						}
					}
				}
				if(!$this->sheets[$sheet]['cellsInfo'][$row][$col]['dontprint']) {*/

					$val = trim($dataXLS->val($row,$col,$sheet));
					if ($val!='') { 
						//$val = htmlentities($val,ENT_QUOTES,"WINDOWS-1251");
						$val = mb_convert_encoding($val, 'UTF-8', 'WINDOWS-1251');
						$tmp[$col] = $val;
					}
				//}
			}

			//print_r($tmp);
			$randi = 2;
			if($cnt = count($tmp)) {
				if($cnt==1) {
					if(isset($tmp[1])) {
						$idCat++;
						$name0 = '';
						$name1 =$tmp[1];
						$tmpName = explode('/', $name1);
						if(count($tmpName)>1) {
							$name0 = $tmpName[0];
							$name1 = $tmpName[1];
							//$name1 = str_replace($name0,'',$tmpName[1]);
							if(!isset($out['dataCat'][$name0])) {
								$out['dataCat'][$name0] = array(
									'name'=>$name0,
									'id'=>$idCat,
									'parent_id'=>0
								);
								$idCat++;
							}
							$pid = 0;
							if(isset($out['dataCat'][$name0]))
								$pid = $out['dataCat'][$name0]['id'];

							if(isset($out['dataCat'][$name1])) {
								$name1 .= ' ('.$randi.')';
								$randi++;
							}

							$out['dataCat'][$name1] = array(
								'name'=> $name1,
								'id'=> $idCat,
								'parent_id'=> $pid
							);
						} 
						else {
							if(isset($out['dataCat'][$name1])) {
								$name1 .= ' ('.$randi.')';
								$randi++;
							}
							$out['dataCat'][$name1] = array(
								'name'=>$name1,
								'id'=>$idCat,
								'parent_id'=>0
							);
						}
					}
					else {
						$out['info'][] = current($tmp);
					}
				}
				elseif (isset($tmp[1]) and isset($out['field'])) {
					$tmp['shop'] = $idCat;
					$out['dataProd'][(int)$tmp[1]] = $tmp;
				}
				elseif (isset($tmp[1])) {
					$out['field'] = $tmp;
				}
				else
					$out['info'][] = $tmp;
			}
			//if($row>4) return $out;
		}
		return $out;
	}
}

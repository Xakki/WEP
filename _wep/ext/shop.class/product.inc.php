<?php
/**
 * Страница одного товара
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $result
 */

	if(!isset($FUNCPARAM[0]) and !$FUNCPARAM[0]) $FUNCPARAM[0] = '#shop#productItem';
	//if(!$FUNCPARAM[1]) $FUNCPARAM[1] = '0';

	// рисуем форму для админки чтобы удобно задавать параметры
	if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон'),
			//'1'=>array('type'=>'list','listname'=>'levelmenuinc', 'caption'=>'Страница каталога'),
		);
		return $form;
	}

	if(!_new_class('shop',$SHOP)) return false;
	global $HTML;
	$result = '';

	$PRODUCT = &$SHOP->childs['product'];
	if(isset($_GET['id']) and $id = (int)$_GET['id']) {
		$DATA= array($FUNCPARAM[0]=>$PRODUCT->fItem($id));
		$result = $HTML->transformPHP($DATA,$FUNCPARAM[0]);
		if(isset($PRODUCT->data[$id]) and count($PRODUCT->data[$id])) {
			$PRODUCT->data[$id]['shops'] = array_reverse($PRODUCT->data[$id]['shops']);
			$temp = $this->pageinfo['path'];$tcnt = count($temp);
			$this->pageinfo['path'] = array();
			$c=1;
			foreach($temp as $tk=>$tr) {
				if($c<($tcnt-1))
					$this->pageinfo['path'][$tk] = $tr;
				elseif($c==$tcnt) {
					$this->pageinfo['path'][$tk] = $tr;
					$this->pageinfo['path'][$tk]['name'] = $PRODUCT->data[$id]['name'];
				}
				else {
					foreach($PRODUCT->data[$id]['shops'] as $rr)
						$this->pageinfo['path'][$SHOP->data2[$rr['id']]['path'].'/'.$PGLIST->getHref($tk)] = $rr['name'];
				}
				$c++;
			}
		} else
			header("HTTP/1.0 404");

		/*if(count($PRODUCT->data) and isset($PRODUCT->childs['productcomments']) and ($PRODUCT->config['onComm']=='2' or $PRODUCT->data[$id]['on_comm'])) {

			$MODUL_COMM = &$PRODUCT->childs['productcomments'];
			$_tpl['script']['form'] = 1;
			$_tpl['styles']['form'] = 1;

			$DATA2 = $DATA = array();
			$MODUL_COMM->owner->id = $id;
			$parent_id = 0;
			if(isset($_REQUEST['commanswer']))
				$parent_id= (int)$_REQUEST['commanswer'];

			$parentcomm = $MODUL_COMM->displayData($MODUL_COMM->owner->id,$parent_id);// запрос данных
			$DATA2['comments']['data'] = &$MODUL_COMM->data;
			$DATA2['comments']['headname'] = $MODUL_COMM->caption;
			$DATA2['comments']['modul'] = &$MODUL_COMM->_cl;
			$DATA2['comments']['vote'] = $MODUL_COMM->config['vote'];
			$DATA2['comments']['treelevel'] = $MODUL_COMM->config['treelevel'];

			$result .= $HTML->transformPHP($DATA2,'comments').'<span onclick="loadFormComm(this,'.$MODUL_COMM->owner->id.',\''.$MODUL_COMM->_cl.'\')" class="jshref button_comm">'.$MODUL_COMM->lang['_saveclose'].'</span>';

		}*/

	}else {
		header("HTTP/1.0 404");
		$result = '<div class="divform">	<div class="messages"><div class="error">Ссылка не верна. Вероятно товар был удален с сайта.</div></div></div>';
	}
	return $result;

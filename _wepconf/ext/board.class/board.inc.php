<?php
	global $BOARD, $RUBRIC, $_CFG;
	$html='';
	_new_class('board',$BOARD);
	_new_class('rubric',$RUBRIC);
	$BOARD->RUBRIC = &$RUBRIC;
	
	if(isset($_GET['id']) and isset($_GET['hash']) and $BOARD->id = (int)$_GET['id']) {
		$BOARD->data = $BOARD->_select();
		array_pop($this->pageinfo['path']);
		array_pop($this->pageinfo['path']);

		if(isset($_GET['hash']) and strlen($_GET['hash'])==32 and count($BOARD->data)==1 and (
			$BOARD->data[$BOARD->id]['hash']==$_GET['hash'] or $BOARD->data[$BOARD->id][$BOARD->mf_createrid]==$_SESSION['user']['id'] or static_main::_prmUserCheck(1))) {
			$TEMP_USER_ID = 0;
			if(isset($_SESSION['user']['id'])) $TEMP_USER_ID = $_SESSION['user']['id'];
			$BOARD->data[$BOARD->id][$this->mf_createrid] = $_SESSION['user']['id'] = '--OK--';

			if(isset($_GET['type']) and $_GET['type']=='del') {
				$this->pageinfo['path'][] = 'Удаление обновления';
				if($_POST['delete']) {
					if($BOARD->_delete())
						$html = '<div class="divform">	<div class="messages"><div class="ok">Объявление успешно удалено</div></div></div>';
					else
						$html = '<div class="divform">	<div class="messages"><div class="error">Ошибка удаления</div></div></div>';
				} else{
					unset($BOARD->config['nomination1']);
					$DATA = array('boarditems'=>$BOARD->fDisplay($BOARD->id));
					$html = '
<fieldset><legend>Объявление №'.$BOARD->id.'</legend>'.$HTML->transformPHP($DATA,array('boarditems', __DIR__ . '/templates/')).'</fieldset>
<div class="divform">
	<div class="messages">
		<div class="error">Внимание! Удаление не отвратимо.</div>
	</div>
	<form action="" enctype="multipart/form-data" method="post">
		<div class="div-tr" style="" id="tr_sbmt"><div class="form-submit"><input type="submit" onclick="" class="sbmt" value="Удалить объявление" name="delete"></div></div>
	</form>
</div>
';
				}
			} elseif(isset($_GET['type']) and $_GET['type']=='up') {
				$this->pageinfo['path'][] = 'Обновление даты';
				$forms = '';
				$vars = array('datea'=>time());
				if($_SESSION['user']['paramupdate'])
					$pup = $_SESSION['user']['paramupdate'];
				elseif($this->config['paramupdate'])
					$pup = $this->config['paramupdate'];
				else
					$pup = 70;
				$nn = time() - $BOARD->data[$BOARD->id]['datea'] - (3600*$pup);
				if($nn<0) {
					$nn = abs($nn);
					$day = floor($nn/86400);
					$hour = floor(($nn%86400)/3600);
					$mess = '<div class="ok">Это объявление разрешено обновлять только через '.$day.' д. и '.$hour.' ч.</div>';
				}
				elseif(isset($_POST['update'])) {
					if($BOARD->_save_item($vars))
						$mess = '<div class="ok">Объявление успешно обновлено</div>';
					else
						$mess = '<div class="error">Ошибка удаления</div>';
				} else {
					$mess = '<div class="alert">Разрешается обновлять объявление не чаще чем 1 раз в '.$pup.' ч.</div>';
					if(!isset($_SESSION['user']['paramupdate']))
						$mess .= '<div class="alert">Зарегистрированные пользователи могуть обновлять 1 раз в день</div>';
					$forms = '<form action="" enctype="multipart/form-data" method="post"><div class="div-tr" style="" id="tr_sbmt"><div class="form-submit"><input type="submit" onclick="" class="sbmt" value="Обновить объявление" name="update"></div></div></form>'; 
				}
				unset($BOARD->config['nomination1']);
				$DATA = array('boarditems'=>$BOARD->fDisplay($BOARD->id));
				$html = $HTML->transformPHP($DATA,array('boarditems', __DIR__ . '/templates/'));
				$html = '<fieldset><legend>Объявление №'.$BOARD->id.'/ '.date('Y-m-d H:i:s',$BOARD->data[$BOARD->id]['datea']).'</legend>'.$html.'</fieldset><div class="divform"><div class="messages">'.$mess.'</div>'.$forms.'</div>';

			} else { //edit
				$this->pageinfo['path'][] = 'Редактирование объявления';
				$param = array();
				list($DATA['formcreat'],$flag) = $BOARD->_UpdItemModul($param);
				if(isset($this->pageinfo['script']['script.jquery/form']))
					$_tpl['onload'] .= '$(\'#form_board\').attr(\'action\',\''.$_CFG['_HREF']['siteJS'].'?_view=add\');JSFR(\'#form_board\');';
				if($flag==1) {
					$HTML->_templates = 'waction';
					$html = $HTML->transformPHP($DATA['formcreat'],'messages');
				}
				else {
					$html = $HTML->transformPHP($DATA,'formcreat');
				}
			}
			if($TEMP_USER_ID)
				$_SESSION['user']['id'] = $TEMP_USER_ID;
			else
				unset($_SESSION['user']['id']);
		} else {
			header("HTTP/1.0 404");
			$html = '<div class="divform">	<div class="messages"><div class="error">Сылка не действительна. Скорее всего объявление уже удалено, или вы не являетесь владельцем этого объявления, или неверный формат ссылки.</div></div></div>';
		}
	}
	elseif(isset($_GET['id']) and $id = (int)$_GET['id']) {
		$DATA = array('boarditems'=>$BOARD->fDisplay($id));
		$html = $HTML->transformPHP($DATA,array('boarditems', __DIR__ . '/templates/'));
		if(isset($BOARD->data[$id]) and count($BOARD->data[$id])) {
			$BOARD->data[$id]['rubrics'] = array_reverse($BOARD->data[$id]['rubrics']);
			$temp = $this->pageinfo['path'];$tcnt = count($temp);
			$this->pageinfo['path'] = $kw = array();
			$c=1;
			foreach($temp as $tk=>$tr) {
				if($c<($tcnt-1))
					$this->pageinfo['path'][$tk] = $tr;
				elseif($c==$tcnt) {
					$this->pageinfo['path'][$tk] = $tr;
					$temp = html_entity_decode(static_main::pre_text($BOARD->data[$id]['text'],70),ENT_QUOTES,'UTF-8');
					$temp = str_replace('$','&#036;',$temp);
					$this->pageinfo['path'][$tk]['name'] = $temp;
					$temp = explode(' ',$temp);
					foreach($temp as $k) {
						if(mb_strlen($k)>2)
							$kw[] = $k;
					}
				}
				else {
					foreach($BOARD->data[$id]['rubrics'] as $rr) {
						$this->pageinfo['path'][$BOARD->RUBRIC->data2[$rr['id']]['lname'].'/'.$tk] = $rr['name'];
						$kw[] =$rr['name'];
					}
				}
				$c++;
			}
			$this->pageinfo['keywords'] = implode(',',$kw);
		}else
			header("HTTP/1.0 404");

	} else {
		header("HTTP/1.0 404");
		$html = '<div class="divform">	<div class="messages"><div class="error">Ссылка не верна. Вероятно данное объявление было удалено с сайта пользователем.</div></div></div>';
	}
	return $html;

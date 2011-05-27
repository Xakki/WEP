<?
	global $BOARD, $RUBRIC, $_CFG;
	$html='';
	_new_class('board',$BOARD);
	_new_class('rubric',$RUBRIC);
	$BOARD->RUBRIC = &$RUBRIC;
	
	if(isset($_GET['id']) and isset($_GET['hash']) and $BOARD->id = (int)$_GET['id']) {
		$BOARD->_select();
		array_pop($this->pageinfo['path']);
		array_pop($this->pageinfo['path']);
		if(isset($_GET['hash']) and strlen($_GET['hash'])==32 and count($BOARD->data)==1 and $BOARD->data[$BOARD->id]['hash']==$_GET['hash']) {
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
					$html = '
<fieldset><legend>Объявление №'.$BOARD->id.'</legend>'.$HTML->transform($BOARD->fDisplay($BOARD->id),'boarditems').'</fieldset>
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
				elseif($_POST['update']) {
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
				$html = $HTML->transform($BOARD->fDisplay($BOARD->id),'boarditems');
				$html = '<fieldset><legend>Объявление №'.$BOARD->id.'/ '.date('Y-m-d H:i:s',$BOARD->data[$BOARD->id]['datea']).'</legend>'.$html.'</fieldset><div class="divform"><div class="messages">'.$mess.'</div>'.$forms.'</div>';

			} else { //edit
				$this->pageinfo['path'][] = 'Редактирование объявления';
				$param = array();
				list($DATA['formcreat'],$flag) = $BOARD->_UpdItemModul($param);
				if(isset($this->pageinfo['script']['jquery.form']))
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
		$html = $HTML->transform($BOARD->fDisplay($id),'boarditems');
		if(isset($BOARD->data[$id]) and count($BOARD->data[$id])) {
			$BOARD->data[$id]['rubrics'] = array_reverse($BOARD->data[$id]['rubrics']);
			$temp = $this->pageinfo['path'];$tcnt = count($temp);
			$this->pageinfo['path'] = array();
			$c=1;
			foreach($temp as $tk=>$tr) {
				if($c<($tcnt-1))
					$this->pageinfo['path'][$tk] = $tr;
				elseif($c==$tcnt) {
					$this->pageinfo['path'][$tk] = $tr;
					$temp = strip_tags($BOARD->data[$id]['text']);
					$this->pageinfo['path'][$tk]['name'] = mb_substr($temp,0,64).(mb_strlen($temp)>64?'...':'');
				}
				else {
					foreach($BOARD->data[$id]['rubrics'] as $rr)
						$this->pageinfo['path'][$BOARD->RUBRIC->data2[$rr['id']]['lname'].'/'.$tk] = $rr['name'];
				}
				$c++;
			}
		}else
			header("HTTP/1.0 404");
		if($BOARD->data[$id]['mapx']) {
			//.ru ALOgRE0BAAAAv6zZcQIAjsjexB7rFg3HTA_g1j-coGlstYMAAAAAAAAAAAD2tWiNHDQrFWdJRx7iuVAiNWEmTA==
			//.i AOCoRE0BAAAA88JZPQIANdFMmqSCC13UptUv7elqUYOoyxQAAAAAAAAAAADQ4SW-iwo9kv-xUCuu5MlHifOX8w==
			//унидоски.рф AEBlTE0BAAAAI8CRMQIACx_AbSrbH-5VVjtyAEq4d1AmTZsAAAAAAAAAAABOmdN9uXx5VhMuwpOp8geXLZRLCg==
			$_tpl['script']['api-maps'] = array('http://api-maps.yandex.ru/1.1/index.xml?loadByRequire=1&key=ALOgRE0BAAAAv6zZcQIAjsjexB7rFg3HTA_g1j-coGlstYMAAAAAAAAAAAD2tWiNHDQrFWdJRx7iuVAiNWEmTA==~AOCoRE0BAAAA88JZPQIANdFMmqSCC13UptUv7elqUYOoyxQAAAAAAAAAAADQ4SW-iwo9kv-xUCuu5MlHifOX8w==~AEBlTE0BAAAAI8CRMQIACx_AbSrbH-5VVjtyAEq4d1AmTZsAAAAAAAAAAABOmdN9uXx5VhMuwpOp8geXLZRLCg==');
		}
		if(count($BOARD->data) and isset($BOARD->childs['boardcomments']) and ($BOARD->config['onComm']=='2' or $BOARD->data[$id]['on_comm'])) {

			$MODUL_COMM = &$BOARD->childs['boardcomments'];
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

			$html .= $HTML->transformPHP($DATA2,'comments').'<span onclick="loadFormComm(this,'.$MODUL_COMM->owner->id.',\''.$MODUL_COMM->_cl.'\')" class="jshref button_comm">'.$MODUL_COMM->locallang['default']['_saveclose'].'</span>';

		}
	}else {
		header("HTTP/1.0 404");
		$html = '<div class="divform">	<div class="messages"><div class="error">Ссылка не верна. Вероятно данное объявление было удалено с сайта пользователем.</div></div></div>';
	}
	return $html;

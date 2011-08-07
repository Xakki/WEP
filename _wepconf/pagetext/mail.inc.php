<?
	global $MAIL;
	if(!$MAIL) _new_class('mail',$MAIL);

	if((int)$_GET['id']) {
		global $BOARD;
		if(!$BOARD) _new_class('board',$BOARD);
		$BOARD->id = (int)$_GET['id'];
		$BOARD->data = $BOARD->_select();

		if(!count($BOARD->data) or $BOARD->data[$BOARD->id]['email']=='')
			return '<h2>Объявления либо не существует, либо отсутствует EMAIL</h2>';

		$DATA = array();
		$MAIL->fields_form["subject"]['default'] = 'По поводу вашего объявления с сайта '.$this->config['sitename'];
		$tmp2 = $_CFG['_HREF']['BH'].'board_'.$BOARD->id.'.html?hashedit='.md5($_CFG['wep']['md5'].$BOARD->id.$BOARD->data[$BOARD->id]['mf_ipcreate'].$BOARD->data[$BOARD->id]['email']);
		$MAIL->config["mailbottom"] = '<br/><hr/>
		<div style="text-align: left;color: rgb(81, 81, 81);font-size:70%;">
		<i>Текст письма написан пользователем просматривающего <a href="'.$_CFG['_HREF']['BH'].'/board_'.$BOARD->id.'.html">ваше объявление №'.$BOARD->id.'</a></i><br>
		Вы можете <a href="'.$tmp2.'&amp;optn=off">отключить ваше объявление</a>, если оно больше не актуально.<br>
		Также вы можете <a href="'.$tmp2.'&amp;optn=new">поднять(аналогично добавлению) ваше объявление</a>, если оно ещё актуально для вас или <a href="'.$tmp2.'">отредактировать</a>
		</div>'.$MAIL->config["mailbottom"];
		list($DATA['formcreat'],$flag) = $MAIL->mailForm($BOARD->data[$BOARD->id]['email']);
		if($DATA['formcreat']['form']['_info'])
			$DATA['formcreat']['form']['_info']['caption'] = 'Отправка письма владельцу <a href="/board_'.$BOARD->id.'.html">объявления №'.$BOARD->id.'</a>';
		if(!$flag)
			$DATA['formcreat']['messages'][] = array('name'=>'alert','value'=>'<b style="color:black;">Текст объявления:</b> '.$BOARD->data[$BOARD->id]['text']);
	}
	else {
		if (!isset($FUNCPARAM[0]))
			$FUNCPARAM[0] = '';
		$DATA = array();
		list($DATA['formcreat'],$flag) = $MAIL->mailForm($FUNCPARAM[0]);
		if($DATA['formcreat']['form']['_info'])
			$DATA['formcreat']['form']['_info']['caption'] = 'Отправка письма службе поддержки';

	}
	if($flag==1) {
		$HTML->_templates = "waction";
		$html = $HTML->transformPHP($DATA['formcreat'],'messages');
	}
	else
		$html = $HTML->transformPHP($DATA,'formcreat');
	return $html;


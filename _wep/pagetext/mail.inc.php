<?
	global $MAIL, $BOARD, $PGLIST;
	if(isset($_GET['feedback'])) {
		if(!$MAIL) $MAIL = new mail_class($SQL);
		$DATA = array();
		list($DATA['formcreat'],$flag) = $MAIL->mailForm('службе поддержки',$UGROUP->config["mailto"]);print_r('<pre>');print_r($DATA);
		$html = $HTML->transformPHP($DATA,'formcreat');
	}
	elseif((int)$_GET['id']) {

		if(!$MAIL) $MAIL = new mail_class($SQL);
		if(!$BOARD) $BOARD = new board_class($SQL);
		$BOARD->id = (int)$_GET['id'];
		$BOARD->_select();

		if(!count($BOARD->data) or $BOARD->data[$BOARD->id]['email']=='')
			return '<h2>Обявления либо не существует, либо отсутствует EMAIL</h2>';
		$name='владельцу <a href="/board.html?board='.$BOARD->id.'">объявления №'.$BOARD->id.'</a>';

		$DATA = array();
		list($DATA['formcreat'],$flag) = $MAIL->mailForm($name,$BOARD->data[$BOARD->id]['email']);
		$DATA['formcreat']['messages'][0] = array('name'=>'alert','value'=>'<b style="color:black;">Текст объявления:</b> '.$BOARD->data[$BOARD->id]['text']);
		$html = $HTML->transformPHP($DATA,'formcreat');
	}

	return $html;

?>
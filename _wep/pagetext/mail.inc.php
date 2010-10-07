<?
	global $MAIL, $BOARD, $PGLIST;
	if(isset($_GET['feedback'])) {
		if(!$MAIL) $MAIL = new mail_class($SQL);
		$xml=$MAIL->mailForm('службе поддержки',$UGROUP->config["mailto"]);
		$html = $HTML->transform('<formblock>'.$xml[0].'</formblock>','formcreat');
	}
	elseif((int)$_GET['id']) {

		if(!$MAIL) $MAIL = new mail_class($SQL);
		if(!$BOARD) $BOARD = new board_class($SQL);
		$BOARD->id = (int)$_GET['id'];
		$BOARD->_select();

		if(!count($BOARD->data) or $BOARD->data[$BOARD->id]['email']=='')
			return '<h2>Обявления либо не существует, либо отсутствует EMAIL</h2>';
		$name='владельцу <a href="/board.html?board='.$BOARD->id.'">объявления №'.$BOARD->id.'</a>';

		$xml=$MAIL->mailForm($name,$BOARD->data[$BOARD->id]['email']);
		$html = $HTML->transform('<formblock><messages><ok><![CDATA[<b style="color:black;">Текст объявления:</b> '.$BOARD->data[$BOARD->id]['text'].']]></ok></messages>'.$xml[0].'</formblock>','formcreat');

	}/// else
	///	$html=$PGLIST->display_page();

	return $html;

?>
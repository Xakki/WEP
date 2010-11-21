<?
	global $MAIL;
	if(isset($_GET['feedback'])) {
		if(!$MAIL) $MAIL = new mail_class($SQL);
		$DATA = array();
		list($DATA['formcreat'],$flag) = $MAIL->mailForm($UGROUP->config["mailto"]);
		if($DATA['formcreat']['form']['_info'])
			$DATA['formcreat']['form']['_info']['caption'] = 'Отправка письма службе поддержки';
		$html = $HTML->transformPHP($DATA,'formcreat');
	}
	return $html;

?>
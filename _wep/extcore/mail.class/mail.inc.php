<?
	global $MAIL;
	if(!$MAIL) $MAIL = new mail_class($SQL);

	$DATA = array();
	list($DATA['formcreat'],$flag) = $MAIL->mailForm($MAIL->config["mailsupport"]);
	if($DATA['formcreat']['form']['_info'])
		$DATA['formcreat']['form']['_info']['caption'] = 'Отправка письма службе поддержки';

	if($flag==1) {
		$HTML->_templates = "waction";
		$html = $HTML->transformPHP($DATA['formcreat'],'messages');
	}
	else
		$html = $HTML->transformPHP($DATA,'formcreat');
	return $html;

?>
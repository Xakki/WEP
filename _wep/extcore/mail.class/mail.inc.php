<?
	global $MAIL;
	if(!$MAIL) _new_class('mail', $MAIL)
	if (!isset($FUNCPARAM[0]))
		$FUNCPARAM[0] = '';
	$DATA = array();
	list($DATA['formcreat'],$flag) = $MAIL->mailForm($FUNCPARAM[0]);
	if($DATA['formcreat']['form']['_info'])
		$DATA['formcreat']['form']['_info']['caption'] = 'Отправка письма службе поддержки';

	if($flag==1) {
		$HTML->_templates = "waction";
		$html = $HTML->transformPHP($DATA['formcreat'],'messages');
	}
	else
		$html = $HTML->transformPHP($DATA,'formcreat');
	return $html;


<?

	$HTML = new html($_CFG['PATH']['cdesign'],'default');
	$HTML->_templates = 'install';
	$SQL->_iFlag = 1;
	$_tpl['title'] = '&#160;Установка СМС WebEngineOnPHP '.$_CFG['info']['version'];
	$_tpl['info'] = '';
	$_tpl['text'] = '';
		if(_new_class('modulprm',$MODUL)) {
			$DATA = array();
			$DATA['formtools'] = $MODUL->toolsCheckmodul();
			$_tpl['text'] = $HTML->transformPHP($DATA,'formtools');
		}
		//$_tpl['onload'] .= '$(\'#form_tools_checkmodul\').bind(\'submit\',function(e){return JSFRWin(\'#form_tools_checkmodul\',\'#tools_block\');});';

	if($_CFG['info']['email'])
		$_tpl['contact'] = '<div class="ctd1">e-mail:</div>	<div class="ctd2"><a href="mailto:'.$_CFG['info']['email'].'">'.$_CFG['info']['email'].'</a></div>';
	if($_CFG['info']['icq'])
		$_tpl['contact'] .= '<div class="ctd1">icq:</div><div class="ctd2">'.$_CFG['info']['icq'].'</div>';
	if(isset($_CFG['info']['phone']) and $_CFG['info']['phone'])
		$_tpl['contact'] .= '<div class="ctd1">телефон:</div><div class="ctd2">'.$_CFG['info']['phone'].'</div>';

?>

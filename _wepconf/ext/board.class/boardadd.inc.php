<?
	global $BOARD, $RUBRIC;
	$html='';
	if(!$BOARD) _new_class('board',$BOARD);
	if(!$RUBRIC) _new_class('rubric',$RUBRIC);
	if(!$BOARD->RUBRIC) $BOARD->RUBRIC = &$RUBRIC;

	$DATA  = array();

	list($DATA['formcreat'],$flag) = $BOARD->_UpdItemModul($DATA);
	if(isset($this->pageinfo['script']['script.jquery/form']))
		$_tpl['onload'] .= '$(\'#form_board\').attr(\'action\',\''.$_CFG['_HREF']['siteJS'].'?_view=add\');JSFR(\'#form_board\');';
	if($flag==1) {
		$this->pageinfo['template'] = 'waction';
		$html = $HTML->transformPHP($DATA['formcreat'],'messages');
	}
	else {
		$DATA['formcreat']['messages'][] = array('alert','Советы: <a href="/kak_napisat_obyavlenie.html" target="_blank">Как написать лучшее объявление</a> ');
		$DATA['formcreat']['messages'][] = array('alert','<a href="/terms.html" target="_blank">Условия и правила</a> пользования сайтом.');
		$html = $HTML->transformPHP($DATA,'formcreat');

	}
	if($flag!=1) {
		$html = '<noscript><div class="noscript">Для более удобной работы с сайтом <b>необходимо разрешить браузеру скрипты</b> на этом сайте. <br/>Но даже с выключенным скриптом вы сможете добавить объявление. Для этого:<br/>
	1. - Если не выбран город, то сначала выберите <a href="/city.html">город</a>.<br/>
	2. - Выберите  интересующую вас рубрику на главной странице сайта.<br/>
	3. - Нажмите кнопку <b>Подать объявление в раздел "...название рубрики..."</b> слева на панели и можете добавлять объявление.
	</div></noscript>'.$html;
	}
	return $html;


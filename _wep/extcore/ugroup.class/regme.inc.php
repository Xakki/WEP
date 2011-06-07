<?
	global $_tpl;
	_new_class('ugroup', $UGROUP);
	if(!isset($FUNCPARAM[0])) $FUNCPARAM[0] = 'formcreat'; // шаблон
	$DATA = array();
	if(isset($_GET['confirm'])){
		list($DATA,$flag) = $UGROUP->regConfirm();
		$html = '<a href="/index.html">Обновите страницу</a>';
		$_tpl['logs'] .= '<div id="ajaxload" style="display: block; top: 20%; left: 35%; height: 290px;">
		<div class="layerblock">
			<div onclick="window.location=\'/index.html\'" class="blockclose"></div>
				<div class="blockhead"><a href="/index.html">'.($flag?'Вы успешно авторизованы. ':'').'Обновите страницу</a></div>
					<div class="hrb">&nbsp;</div>
					'.$HTML->transformPHP($DATA,'messages').'
					<div class="clear">&nbsp;</div>
				</div>
			</div>
		</div>
		<div id="ajaxbg" style="opacity: 0.5; display: block;">&nbsp;</div>';
		$_tpl['onload'] .= 'fMessPos();';
	} else {
		list($DATA[$FUNCPARAM[0]],$flag) = $UGROUP->regForm();
		$html = $HTML->transformPHP($DATA,$FUNCPARAM[0]);
	}
	return $html;
	//<div style="width: 800px; height: 90%;">
	//</div>

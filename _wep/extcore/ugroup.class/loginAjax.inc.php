<?

	/*if(isset($ShowFlexForm)) {
		$form = array(
			'tpl'=>array('type'=>'text','caption'=>'PHP Шаблон'),
			'remindpage'=>array('type'=>'text','caption'=>'Страница "Напомнить пароль"'),
			'exception'=>array('type'=>'text','caption'=>'Страница исключение'),
		);
		return $form;
	}
*/
	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) {
		$FUNCPARAM[0] = 'loginAjax';
		$TRFM = array($FUNCPARAM[0],__DIR__.'/templates/'); // Шаблон
	} else 
		$TRFM = $FUNCPARAM[0];
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '/remind.html';
	if(isset($FUNCPARAM[2])) { // страница исключение
		if($this->id==$FUNCPARAM[2]) return '';
	}

	$result = array();
	$mess = $form = '';

	if(isset($_COOKIE['remember']) and !static_main::_prmUserCheck() and $result = static_main::userAuth() and $result[1]) {
		//@header("Location: ".$ref);
		//die();
		$mess=$result[0];
	}

	$DATA = array (
		'mess'=>$mess,
		'result'=>$result,
		'title'=>$rowPG['name'],
		'remindpage'=>$FUNCPARAM[1]
	);


	$DATA = array($FUNCPARAM[0]=>$DATA);
	$html = $HTML->transformPHP($DATA,$TRFM);

	return $html;


<?

	if(isset($ShowFlexForm)) {
		$form = array(
			'tpl'=>array('type'=>'text','caption'=>'PHP Шаблон'),
			'remindpage'=>array('type'=>'text','caption'=>'Страница "Напомнить пароль"'),
		);
		return $form;
	}

	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) { // Шаблон
		$FUNCPARAM[0] = 'login';
		$TRFM = array($FUNCPARAM[0],__DIR__.'/templates/');
	} else 
		$TRFM = $FUNCPARAM[0];
	if(!isset($FUNCPARAM[1])) // страница напоминания пароля
		$FUNCPARAM[1] = '/remind.html';


	$result = array();
	if(isset($_REQUEST['ref']) and $_REQUEST['ref']!='') {
		$ref= $_REQUEST['ref'];
		$pos = strripos($ref, '/');
		$rest = substr($ref, ($pos+1), 5);
		if(!strpos($this->dataCash[$rest]['ugroup'], 'anonim'))
			$ref= $ref;
		else 
			$ref= $_CFG['_HREF']['BH'];
	}
	elseif(isset($_SERVER['HTTP_REFERER']) and $_SERVER['HTTP_REFERER']!='' and strpos($_SERVER['HTTP_REFERER'], '.html')) {
		$ref= $_SERVER['HTTP_REFERER'];
		$pos = strripos($ref, '/');
		$rest = substr($ref, ($pos+1), 5);
		if(!strpos($this->dataCash[$rest]['ugroup'], 'anonim'))
			$ref= $ref;
		else 
			$ref= $_CFG['_HREF']['BH'];
	}
	else 
		$ref= $_CFG['_HREF']['BH'];
	$mess = $form = '';

	if(count($_POST) and isset($_POST['login'])) {
		$result = static_main::userAuth($_POST['login'],$_POST['pass']);
		if($result[1]) {
			//@header("Location: ".$ref);
			//die();
			$mess=$result[0];
		}
	}
	elseif(isset($_REQUEST['exit']) && $_REQUEST['exit']=="ok") {
		static_main::userExit();
		$mess=$_CFG['_MESS']['exitok'];
	}
	elseif(isset($_COOKIE['remember']) and !static_main::_prmUserCheck() and $result = static_main::userAuth() and $result[1]) {
		//@header("Location: ".$ref);
		//die();
		$mess=$result[0];
	}

	$DATA = array(
		'mess'=>$mess,
		'result'=>$result,
		'ref'=>$ref,
		'title'=>$rowPG['name'],
		'remindpage'=>$FUNCPARAM[1]
	);

	$DATA = array($FUNCPARAM[0]=>$DATA);
	$html = $HTML->transformPHP($DATA,$TRFM);

	return $html;


<?
	_new_class('ugroup', $UGROUP);
	if(!isset($FUNCPARAM[0])) $FUNCPARAM[0] = false;// - текущий	 пользователь, цыфра - уровень адреса ID пользователя
	if($FUNCPARAM[0]) {// and $FUNCPARAM[0]{0}=='#'
		$FUNCPARAM[0] = $_GET['page'][(int)substr($FUNCPARAM[0],1)-1];
	}else
		$FUNCPARAM[0] = $_SESSION['user']['id'];
	$DATA = $UGROUP->childs['users']->UserInfo($FUNCPARAM[0]);
	$gadgets = $UGROUP->childs['users']->getGadgets($_SESSION['user']['id']);
	$DATA = array(
		'userinfo'=>
			array(
				'data'=>$DATA,
				'gadgets' => $gadgets,
			)
		);
	$html = $HTML->transformPHP($DATA,'userinfo');
	//TODO : информация о пользователе

	return $html;

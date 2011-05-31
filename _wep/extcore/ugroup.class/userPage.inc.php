<?
	_new_class('ugroup', $UGROUP);
	if($FUNCPARAM[0] == '') $FUNCPARAM[0] = '';// - текущий	 пользователь, цыфра - уровень адреса ID пользователя
	if($FUNCPARAM[0]) {// and $FUNCPARAM[0]{0}=='#'
		$FUNCPARAM[0] = $_GET['page'][(int)substr($FUNCPARAM[0],1)-1];
	}else
		$FUNCPARAM[0] = $_SESSION['user']['id'];
	$DATA = $UGROUP->childs['users']->UserInfo($FUNCPARAM[0]);
	$DATA = array(
		'userinfo'=>
			array(
				'data'=>$DATA
			)
		);
	$html = $HTML->transformPHP($DATA,'userinfo');
	//TODO : информация о пользователе

	return $html;

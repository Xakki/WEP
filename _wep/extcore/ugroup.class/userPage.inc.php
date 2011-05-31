<?
	_new_class('ugroup', $UGROUP);
	if($FUNCPARAM[0] == '') $FUNCPARAM[0] = '';// - текущий	 пользователь, цыфра - уровень адреса ID пользователя
	if($FUNCPARAM[0]) {// and $FUNCPARAM[0]{0}=='#'
		$FUNCPARAM[0] = $_GET['page'][(int)substr($FUNCPARAM[0],1)-1];
	}
	$DATA = array();
	$DATA['userinfo']['data'] = $UGROUP->childs['users']->_query('*','WHERE id='.(int)$FUNCPARAM[0]);
	$html = $HTML->transformPHP($DATA,'userinfo');
	//TODO : информация о пользователе

	return $html;

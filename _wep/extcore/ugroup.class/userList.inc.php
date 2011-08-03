<?
	_new_class('ugroup', $UGROUP);
	$html = '';
	if(isset($this->pageParam[1])) {
		$DATA = $UGROUP->childs['users']->_query('t2.*,t1.*',' t1 LEFT JOIN '.$UGROUP->childs['users']->childs['extuserscontact']->tablename.' t2 ON t1.id=t2.owner_id WHERE t1.active=1 and t1.id='.(int)$this->pageParam[1]);
		$this->pageinfo['path'][''] = $DATA[0]['name'];
		$DATA = array('userinfo'=>array('data'=>$DATA[0],'href'=>$this->getHref()));
		$html = $HTML->transformPHP($DATA,'userinfo');
	} else {
		$DATA = $UGROUP->childs['users']->_query('*','WHERE active=1 ORDER BY name');
		$DATA = array('userlist'=>array('list'=>$DATA,'href'=>$this->getHref()));
		$html = $HTML->transformPHP($DATA,'userlist');
	}
	//TODO : список пользователей

	return $html;

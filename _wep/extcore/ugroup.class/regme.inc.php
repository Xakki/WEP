<?
	global $UGROUP,$USERS;
	if(!$UGROUP) $UGROUP = new ugroup_class($SQL);
	if(!$USERS) $USERS = &$UGROUP->childs['users'];

	if(isset($_GET['confirm'])){
		list($DATA,$flag) = $USERS->regConfirm();
		$html = $HTML->transformPHP($DATA,'messages');
	}else {
		list($DATA['formcreat'],$flag) = $USERS->regForm();
		$html = $HTML->transformPHP($DATA,'formcreat');
	}
	return $html;
?>
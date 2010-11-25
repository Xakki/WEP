<?
	global $UGROUP,$USERS;
	if(!$UGROUP) $UGROUP = new ugroup_class($SQL);
	if(!$USERS) $USERS = &$UGROUP->childs['users'];

	return $USERS->remind();

?>
<?
	global $UGROUP,$USERS;
	if(!$UGROUP) _new_class('ugroup', $UGROUP)
	if(!$USERS) $USERS = &$UGROUP->childs['users'];

	return $USERS->remind();

?>
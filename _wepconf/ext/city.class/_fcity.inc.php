<?
	global $CITY;

	if(!$CITY) _new_class('city',$CITY);
	if(!$CITY->cityPosition())
		return false;
	else
		$PGLIST->pageinfo['path']['index'] = $CITY->name;

	if($CITY->id) {
		$this->pageinfo['keywords'] = $this->pageinfo['keywords'].', '.$CITY->name;
		$this->pageinfo['description'] = $this->pageinfo['description'].',город '.$CITY->name.','.$CITY->desc;
	}
	return true;
?>
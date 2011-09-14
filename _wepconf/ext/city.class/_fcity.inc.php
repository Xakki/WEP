<?
	_new_class('city',$CITY);
	if(!$CITY->cityPosition())
		return false;
	else {
		reset($PGLIST->pageinfo['path']);
		$PGLIST->pageinfo['path'][key($PGLIST->pageinfo['path'])] = $CITY->name;
	}

	if($CITY->id) {
		$this->pageinfo['keywords'] = $this->pageinfo['keywords'].', '.$CITY->name;
		$this->pageinfo['description'] = $this->pageinfo['description'].',город '.$CITY->name.','.$CITY->desc;
	}
	return true;

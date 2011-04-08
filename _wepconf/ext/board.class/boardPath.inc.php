<?

	if(count($_GET['page'])==2 and strlen($_GET['page'][1])>13) {
		$this->id = 'board';
	}
	return true;
?>
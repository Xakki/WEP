<?
	userExit();
	$DATA = array(
		array('value'=>$_CFG['_MESS']['exitok'], 'name'=>'ok')
	);
	$DATA = array('messages'=>$DATA);
	return $HTML->transformPHP($DATA,'messages');
?>
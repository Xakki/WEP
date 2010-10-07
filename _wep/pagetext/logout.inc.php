<?
	unset($_SESSION['user']);
	unset($_SESSION['modulprm']);
	setcookie('remember', '', (time()-1000));
	$DATA = array(
		array('value'=>$_CFG['_MESS']['exitok'], 'name'=>'ok')
	);
	$DATA = array('messages'=>$DATA);
	return $HTML->transformPHP($DATA,'messages');
?>
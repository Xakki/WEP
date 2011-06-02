<?
	if(!isset($FUNCPARAM[0]) or (isset($_REQUEST['exit']) && $_REQUEST['exit']=="ok")) {
		static_main::userExit();
		/*$DATA = array(
			array('value'=>$_CFG['_MESS']['exitok'], 'name'=>'ok')
		);
		$DATA = array('messages'=>$DATA);
		return $HTML->transformPHP($DATA,'messages');*/
		header("Location: ".$_CFG['_HREF']['BH']);
		die();
	}

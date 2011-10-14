<?php
	if(!isset($FUNCPARAM[0]) or (isset($_REQUEST['exit']) && $_REQUEST['exit']=="ok")) {
		static_main::userExit();

		header("Location: ".$_CFG['_HREF']['BH']);
		die();
	}

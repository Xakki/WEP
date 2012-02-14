<?php
/**
 * Выход
 * @ShowFlexForm false
 * @author Xakki
 * @version 0.1 
 */
	if(!isset($FUNCPARAM[0]) or (isset($_REQUEST['exit']) && $_REQUEST['exit']=="ok")) {
		static_main::userExit();

		static_main::redirect($_CFG['_HREF']['BH']);
	}

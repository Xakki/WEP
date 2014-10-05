<?php
/**
 * Выход
 * @ShowFlexForm false
 * @type Служебные
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

if (!isset($FUNCPARAM[0]) or (isset($_REQUEST['exit']) && $_REQUEST['exit'] == "ok")) {
	static_main::userExit();

	static_main::redirect(MY_BH);
}

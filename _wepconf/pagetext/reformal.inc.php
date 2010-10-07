<?
	if(!$HTML->_fTestIE('MSIE 6') and !strpos($_SERVER['HTTP_HOST'],'.l'))
		$_tpl['script'] .='<script type="text/javascript"><!--
reformal_wdg_domain = "unidoski";reformal_wdg_mode = 0;reformal_wdg_title = "УниДоски.ру";reformal_wdg_ltitle = "";
reformal_wdg_lfont = "";reformal_wdg_lsize = "";reformal_wdg_color = "#0763b3";reformal_wdg_bcolor  = "#516683";
reformal_wdg_tcolor = "#FFFFFF";reformal_wdg_align = "right";reformal_wdg_charset = "utf-8";reformal_wdg_waction = 0;
reformal_wdg_vcolor = "#9FCE54";reformal_wdg_cmline = "#E0E0E0";reformal_wdg_glcolor  = "#105895";reformal_wdg_tbcolor  = "#FFFFFF";
//--></script>
<script type="text/javascript" language="JavaScript" src="http://widget.reformal.ru/tab5.js"></script>
<noscript><a href="http://unidoski.reformal.ru">УниДоски.ру feedback </a> <a href="http://reformal.ru"><img src="http://reformal.ru/i/logo.gif" /></a></noscript>';
?>
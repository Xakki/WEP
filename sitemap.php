<?
	$_CFG['_PATH']['path'] = dirname(__FILE__);
	require_once($_CFG['_PATH']['path'].'/_wep/config/config.php');
	require_once($_CFG['_PATH']['core'].'/html.php');	/**отправляет header и печатает страничку*/
	require_once($_CFG['_PATH']['core'].'/sql.php');
	$SQL = new sql();
	$SITEMAP = TRUE;
	$PGLIST = new pg_class($SQL);

	echo $PGLIST->creatSiteMaps();
?>
<?
	global $SAPE;
	$html='';
//sape************************ www.sape.ru
	if($_CFG['info']['onShape']) {
		if (!defined('_SAPE_USER')){
			define('_SAPE_USER', '30470f2f3780b5013ddf3e21b410adbf'); 
		}
		require_once($_SERVER['DOCUMENT_ROOT'].'/'._SAPE_USER.'/sape.php'); 
		$o['charset'] = 'UTF-8';
		if(!$SAPE) $SAPE = new SAPE_client($o);unset($o);
		//global $sape; echo $sape->return_links();
		//$sape_context = new SAPE_context();$text = $sape_context->replace_in_text_segment($text);//В данном фрагменте текста страницы моего сайта я хочу продавать контекстные ссылки
		if($html = $SAPE->return_links() and _strlen($html)>5)
		$html .='<div class="hrb" style="margin-top: 5px;"></div>';
	}
//****************sape 
	return $html;

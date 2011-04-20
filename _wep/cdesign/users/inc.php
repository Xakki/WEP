<?
	if(!$_GET['_modul'] or !(isset($_GET['_view']) or isset($_GET['_type']))) {
		$html = $HTML->transform("<modulsforms></modulsforms>",'modulsforms');
	}
	else {
		if(count($_GET)==2)
			$SQL->_iFlag = TRUE;
		if(!_new_class($_GET['_modul'],$MODUL)) {
			$html = '<div style="color:red;">'.date('H:i:s').' : Модуль '.$_GET['_modul'].' не установлен</div>';
		}
		else {
			$_tpl['onload'] .= '$(\'div.modullist:has(a[href=\\\'index.php?_view=list&_modul='.$_GET['_modul'].'\\\'])\').addClass(\'selected\');';

			if($_GET['_oid']!='') $MODUL->owner_id = $_GET['_oid'];
			if($_GET['_pid']!='') $MODUL->parent_id = $_GET['_pid'];
			if($_GET['_id']!='') $MODUL->id = $_GET['_id'];

			if(static_main::_prmModul($_GET['_modul'],array(1,2))) {

				if($_GET['_view']=='reinstall') {
					$xml = $MODUL->confirmReinstall();
					$html = $HTML->transform($xml[0],'formcreat');
				}
				elseif($_GET['_view']=='config') {
					$xml = $MODUL->_configModul();
					$html = $HTML->transform($xml[0],'formcreat');
				}
				elseif($_GET['_view']=='reindex') {
					if($MODUL->_reindex()) $html = "Ошибка";
					else $html = 'Модуль успешно переиндексирован!';
				}
				elseif($_GET['_view']=='list') {
					$param = array('fhref'=>'_view=list&amp;_modul='.$_GET['_modul'].'&amp;','showform'=>1);

					list($xml,$flag) = $MODUL->super_inc($param,$_GET['_type']);

					if($_GET['_type']=="add" or $_GET['_type']=="edit") {
						if($flag==1) {
							end($HTML->path);prev($HTML->path);
							$_SESSION['mess']=$xml;
							header('Location: '.str_replace("&amp;", "&", key($HTML->path)));
							die();
						}
						else {
							$html = $HTML->transform('<formblock>'._path2xsl($HTML->path).$xml.'</formblock>','formcreat');
						}
					}elseif($flag!=3) {
						end($HTML->path);
						$_SESSION['mess']=$xml;
						header('Location: '.str_replace("&amp;", "&", key($HTML->path)));
						die();
					}else {
						if(!$_SESSION['mess']) $_SESSION['mess']='';
						$html = $HTML->transform('<main>'._path2xsl($HTML->path).$xml.$_SESSION['mess'].'</main>','superlist');
						$_SESSION['mess'] = '';
					}

				}
			}
			else
				$html ='<div style="color:red;">'.date('H:i:s').' : Доступ к модулю '.$_GET['_modul'].' запрещён администратором</div>';
		}
	}
	$_tpl['modulsforms'] = $html;

?>

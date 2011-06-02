<?
	//$FUNCPARAM[0] 

		if(!_new_class('comments',$MODUL)) {
			$html = '<div style="color:red;">'.date('H:i:s').' : Модуль `comments` не установлен</div>';
		}
		else {

			if($_GET['_oid']!='') $MODUL->owner_id = $_GET['_oid'];
			if($_GET['_pid']!='') $MODUL->parent_id = $_GET['_pid'];
			if($_GET['_id']!='') $MODUL->id = $_GET['_id'];

			if(static_main::_prmModul('comments',array(1,2))) {

				$param = array();
				
				list($DATA,$flag) = $MODUL->super_inc($param,$_GET['_type']);

				if($_GET['_type']=="add" or $_GET['_type']=="edit") {
					if($flag==1) {
						end($HTML->path);prev($HTML->path);
						$_SESSION['mess']=$DATA['formcreat']['messages'];
						header('Location: '.str_replace("&amp;", "&", key($HTML->path)));
						die();
					}
					else {
						$DATA['formcreat']['path'] = $HTML->path;
						$html = $HTML->transformPHP($DATA,'formcreat');
					}
				}elseif($flag!=3) {
					end($HTML->path);
					$_SESSION['mess']=$DATA['superlist']['messages'];
					header('Location: '.str_replace("&amp;", "&", key($HTML->path)));
					die();
				}else {
					if(!$_SESSION['mess']) 
						$_SESSION['mess']= array();
					$DATA['superlist']['messages'] += $_SESSION['mess'];
					$DATA['superlist']['path'] = $HTML->path;
					$html = $HTML->transformPHP($DATA,'superlist');
					$_SESSION['mess'] = array();
				}

				//$_tpl['onload'] .= "$('.fancyimg').fancybox();";
			}
			else
				$html ='<div style="color:red;">'.date('H:i:s').' : Доступ к модулю `comments` запрещён администратором</div>';
		}
	return $html;

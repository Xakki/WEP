<?
	$FUNCPARAM = explode('&',$FUNCPARAM);
	//$FUNCPARAM[0] - модуль
	//$FUNCPARAM[1] - php template

		if(!_new_class($FUNCPARAM[0],$MODUL)) {
			$html = '<div style="color:red;">'.date('H:i:s').' : Модуль '.$FUNCPARAM[0].' не установлен</div>';
		}
		else {
			if(!$FUNCPARAM[1]) $FUNCPARAM[1] = 'superlist';
			if($_GET['_oid']!='') $MODUL->owner_id = $_GET['_oid'];
			if($_GET['_pid']!='') $MODUL->parent_id = $_GET['_pid'];
			if($_GET['_id']!='') $MODUL->id = $_GET['_id'];

			if(_prmModul($FUNCPARAM[0],array(1,2))) {
				$MODUL->_clp = '_view=list&_modul='.$MODUL->_cl.'&';
				$param = array('firstpath'=>$this->current_path.'?','phptemplate'=>$FUNCPARAM[1]);
				list($DATA,$flag) = $MODUL->super_inc($param,$_GET['_type']);

				if($_GET['_type']=="add" or $_GET['_type']=="edit") {
					if($flag==1) {
					//	end($HTML->path);prev($HTML->path);
						$countfield = $DATA[$FUNCPARAM[1]]['pagenum']['cnt'];	
						$DATA[$FUNCPARAM[1]]['pagenum'] = $MODUL->fPageNav($countfield, $_SERVER['REQUEST_URI'],1);
						$_SESSION['mess']=$DATA['formcreat']['messages'];
						header('Location: '.str_replace("&amp;", "&", key($HTML->path)));
						die();
					}
					else {
						$DATA['formcreat']['path'] = $HTML->path;
						$html = $HTML->transformPHP($DATA,'formcreat');
					}
				}elseif($flag!=3) {
					//end($HTML->path);
					$_SESSION['mess']=$DATA[$FUNCPARAM[1]]['messages'];
					header('Location: '.str_replace("&amp;", "&", key($HTML->path)));
					die();
				}else {
					if(!$_SESSION['mess']) 
						$_SESSION['mess']= array();
					$DATA[$FUNCPARAM[1]]['messages'] += $_SESSION['mess'];
				//	$DATA[$FUNCPARAM[1]]['path'] = $HTML->path;
					$countfield = $DATA[$FUNCPARAM[1]]['pagenum']['cnt'];	
					$DATA[$FUNCPARAM[1]]['pagenum'] = $MODUL->fPageNav($countfield, $_SERVER['REQUEST_URI'],1);
					$html = $HTML->transformPHP($DATA,$FUNCPARAM[1]);
					$_SESSION['mess'] = array();
				}

			}
			else
				$html ='<div style="color:red;">'.date('H:i:s').' : Доступ к модулю '.$FUNCPARAM[0].' запрещён администратором</div>';
		}
	return $html;
?>
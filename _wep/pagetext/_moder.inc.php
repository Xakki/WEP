<?
	$FUNCPARAM = explode('&',$FUNCPARAM);
	//$FUNCPARAM[0] - модуль

		if(!_new_class($FUNCPARAM[0],$MODUL)) {
			$html = '<div style="color:red;">'.date('H:i:s').' : Модуль '.$FUNCPARAM[0].' не установлен</div>';
		}
		else {

			if($_GET['_oid']!='') $MODUL->owner_id = $_GET['_oid'];
			if($_GET['_pid']!='') $MODUL->parent_id = $_GET['_pid'];
			if($_GET['_id']!='') $MODUL->id = $_GET['_id'];

			if(_prmModul($FUNCPARAM[0],array(1,2))) {

					$param = array('firstpath'=>$this->current_path.'?');//'fhref'=>'_view=list&amp;_modul='.$FUNCPARAM[0].'&amp;'
							
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

//} $tt[$j] = getmicrotime()-$tt[$j]; $summ += $tt[$j]; } print_r('Среднее время = "'.($summ/5).'" ');print_r($tt);
			}
			else
				$html ='<div style="color:red;">'.date('H:i:s').' : Доступ к модулю '.$FUNCPARAM[0].' запрещён администратором</div>';
		}
	return $html;
?>
<?php
	function tpl_menu(&$data) {
		$html = '';
		if(isset($data['#item#']) and count($data['#item#'])) {
			$html .= '<ul class="menu">';
			//$last = count($data);
			foreach($data['#item#'] as $k=>$r) {
				$html .= '<li>';
				$class=array();
				if($r['sel']==2)
					$class[] = 'selected';
				if(isset($r['#item#']) and count($r['#item#'])) {
					$html .= tpl_menu($r);
					$class[] = 'hassub';
				}
				$html .= '<a href="'.$r['href'].'" '.(count($class)?' class="'.implode(' ',$class).'"':'').'>'.$r['name'].'</a>';
				/*if(strpos($r['attr'],'style="'))
					$r['attr'] = str_replace('style="','style="width:'.$prs.'%;',$r['attr']);
				else
					$r['attr'] .= ' style="width:'.$prs.'%;"';*/
				
				$html .= '</li>';
			}
			$html .= '</ul>';
		}
		return $html;
	}

<?php
	function tpl_pathPage(&$data,$form=0) {
		$html = '';
		if(isset($data) and count($data)) {
			$html .= '<div class="path">';
			$last = count($data);
			$i = 1;
			foreach($data as $k=>$r){
				if($i>1) $html .= ' / ';
				if($i!=$last) {  
					$html .= '<a href="'.$r['href'].'">'.$r['name'].'</a>';
				}
				elseif($i==$last)  $html .= '<h1>'.$r['name'].'</h1>';
				$i++;
			}
			$html .= '</div>';
		}
		return $html;
	}

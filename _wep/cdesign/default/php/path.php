<?
	function tpl_path(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			$html .= '<div class="path">';
			$last = count($data);
			$i = 1;
			foreach($data as $k=>$r){
				if($i>1) $html .= ' / ';
				if($i!=$last) {  
					$html .= '<a href="'.$k.'" onclick="return load_href(this)">'.$r.'</a>';
				}
				elseif($i==$last)  $html .= $r.'&#160;<a class="bottonimg imgf5" href="'.$k.'" onclick="return load_href(this)"></a>';
				$i++;
			}
			$html .= '</div>';
		}
		return $html;
	}
?>
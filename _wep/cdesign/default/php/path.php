<?
	function tpl_path(&$data,$form=0) {
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
            if($form) {
              $html .= '&#160;<span class="bottonimg imgf6" onclick="$(\'form input[name=sbmt_save]\').click();" title="сохранить"></span>';
              $html .= '&#160;<span class="bottonimg imgf7" onclick="$(\'form input[name=sbmt]\').click();" title="сохранить и закрыть"></span>';
            }
			$html .= '</div>';
		}
		return $html;
	}
?>
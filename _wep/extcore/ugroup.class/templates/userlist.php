<?
	function tpl_userlist(&$data) {
		$html = '
		<table cellspacing="0" cellpadding="0" class="extuser-list"><tbody>';
		if(isset($data['list']) and count($data['list'])) {
			foreach($data['list'] as $k=>$r){
				if(!$r['userpic'])
					$r['userpic'] = $data['userpic'];
				$html .= '<tr>
					<td><img src="'.$r['userpic'].'" alt=""/> </td>
					<td><a href="'.$data['href'].'/'.$r['id'].'.html">'.$r['name'].'</a></td></tr>';
			}
		}
		return $html.'</tbody></table>';
	}




			
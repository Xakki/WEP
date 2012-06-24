<?php
	function tpl_userChange(&$data) {
		global $_tpl;
		$_tpl['styles']['extuser-list'] = '
			.extuser-list {border:solid gray 1px;}
			.extuser-list th {border:solid gray 1px;}
			.extuser-list td {border:solid gray 1px;}
		';
		$html = '
		<table cellspacing="0" cellpadding="0" class="extuser-list"><tbody>
		<tr>
			<th>Группа
			<th>ID
			<th>Имя
			<th>Email
			<th>Баланс
		</tr>';
		if(isset($data['list']) and count($data['list'])) {
			foreach($data['list'] as $k=>$r){
				if(!$r['userpic'])
					$r['userpic'] = $data['userpic'];
				$html .= '<tr>
					<td>'.$data['owner'][$r['owner_id']]['name'].'
					<td>'.$r['id'].'
					<td><a href="'.$data['href'].'/'.$r['id'].'.html">'.$r['name'].'</a>
					<td>'.$r['email'].'
					<td>'.$r['balance'].'
				</tr>';
			}
		}
		return $html.'</tbody></table>';
	}




			
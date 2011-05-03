<?
	function tpl_userlist(&$data) {
		$html = '
		<div class="extuser-searchbox"><form method="post"><input type="text" name="searchtext"/><input type="submit" value="Поиск"/></form></div>
		<table cellspacing="0" cellpadding="0" class="extuser-list"><tbody><tr>
			<th><a href="'.$data['href'].'/?sort=userpic">Фотография:</a></th>
			<th><a href="'.$data['href'].'/?sort=name">Имя пользователя:</a></th>
			<th><a href="'.$data['href'].'/?sort=karma">Карма:</a></th></tr>';
		if(isset($data['list']) and count($data['list'])) {
			foreach($data['list'] as $k=>$r){
				$html .= '<tr>
					<td><img src="'.$r['userpic'].'" alt=""/> </td>
					<td><a href="'.$data['href'].'/'.$r['id'].'">'.$r['name'].'</a></td>
					<td>'.$data['karma'].'</td></tr>';
			}
		}
		return $html.'</tbody></table>';
	}
?>



			
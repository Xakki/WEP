<?
	function tpl_userinfo(&$data) {
		//print_r('<pre>');print_r($data['data']);
		global $_CFG;
		$html = '<div class="extuser-singleView">';
		if(isset($data['data']) and count($data['data'])) {
			$html .= '<div>Информация о пользователе <b>'.$data['data']['name'].'</b></div>
			<table cellspacing="0" cellpadding="0" class="extuser-info"><tbody><tr>
				<td><img src="'.$data['data']['userpic'].'" alt="" /></td>
				<td>
					<table cellspacing="0" cellpadding="0" class="extuser-list"><tbody>
						<tr><td><b>Имя</b>:</td><td> '.$data['data']['name'].'</td></tr>
						<tr><td><b>Карма</b>:</td><td> '.$data['data']['karma'].'</td></tr>
						<tr><td><b>Козффициент кармы</b>:</td><td> '.$data['data']['karma_ratio'].'</td></tr>
						<tr><td><b>Последний раз был на сайте</b>:</td><td> '.date('Y-m-d H:i:s',$data['data']['lastvisit']).'</td></tr>
						<tr><td><b>Пол</b>:</td><td> '.$_CFG['enum']['gender'][$data['data']['gender']].'</td></tr>
						<tr><td><b>Звание</b>:</td><td></td></tr>
					</tbody></table>
				</td>
			</tr></tbody></table>';
		}
		$html .= '</tbody></table>
		<div class="extuser-menu">
			<a href="userlist/blog/user11/">Посты(0)</a>
			<a href="userlist/kommentarii/user11/">Комментарии(0)</a>
			<a href="userlist/otzyvy/user11/">Отзывы(0)</a>
		</div> </div>';
		return $html;
	}
?>



			
<?php
	function tpl_userinfo(&$data) {
		global $_CFG;
		if(isset($data['data']) and count($data['data'])) {

			$size = @filesize($_CFG['_PATH']['path'].$data['data']['userpic']);
			if(!$data['data']['userpic'] or !$size)
				$data['data']['userpic'] = '/_design/_img/avatar/default.gif';
			else
				$data['data']['userpic'] .= '?'.$size;
			$html = '
			<div class="userinfo">
					<p><span><img src="'.$data['data']['userpic'].'" alt="'.$data['data']['name'].'"/></span></p>
					<p><span>Email: </span><strong>'.$data['data']['email'].'</strong></p>
					<p><span>Имя: </span><strong>'.$data['data']['name'].'</strong></p>
			</div>';

		}



		return $html;
	}




			
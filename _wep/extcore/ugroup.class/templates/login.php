<?php

function tpl_login($data)
{
	global $_CFG;
	$html = '<div class="cform" style="">
			<form action="" method="post">
					<input type="hidden" name="ref" value="'.$data['ref'].'"/>
					<div>Логин:</div><input type="text" name="login" tabindex="1"/>
					<div>Пароль:</div><input type="password" name="pass" tabindex="2"/>
					<div>Запомнить?<input type="checkbox" style="border:medium none; width:30px;" tabindex="3" name="remember" value="1"/></div>
					<input class="submit" type="submit" name="enter" value="Войти" tabindex="4"/>
				</form>
				<a href="'.$_CFG['_HREF']['BH'].'remind.html">Забыли пароль?</a>
			 <div style="clear:both;"></div>
		 </div>';
	if(isset($data['result'][0]) and $data['result'][0])
		$data['mess'] = '<div style="color:red;">'.$data['result'][0].'</div>'.$data['mess'];
	$html = '<div style="height:100%;">
		<div class="messhead" style="text-align: center;">'.$data['mess'].'</div>
		'.$html.'
	</div>';
	return $html;
}
						
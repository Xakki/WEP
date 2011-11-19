<?php

function tpl_login($data)
{
	$form = '';
	if($data['result']<1)
		$form = '<div class="cform" style="">
				<form action="" method="post">
						<input type="hidden" name="ref" value="'.$data['ref'].'"/>
						<div>Логин:</div><input type="text" name="login" tabindex="1"/>
						<div>Пароль:</div><input type="password" name="pass" tabindex="2"/>
						<div>Запомнить?<input type="checkbox" style="border:medium none; width:13px;margin:0 0 0 10px;" tabindex="3" name="remember" value="1"/></div>
						<input class="submit" type="submit" name="enter" value="Войти" tabindex="4"/>
					</form>
					'.($data['remindpage']?'<div><a href="'.$data['remindpage'].'">Забыли пароль?</a></div>':'').'
					'.($data['regpage']?'<div><a href="'.$data['regpage'].'">Не зарегистрированы?</a></div>':'').'
				 <div style="clear:both;"></div>
			 </div>';
	$html = '<div style="height:100%;">
		<div class="messhead" style="text-align: center;">'.$data['mess'].'</div>
		'.$form.'
	</div>';
	return $html;
}
						
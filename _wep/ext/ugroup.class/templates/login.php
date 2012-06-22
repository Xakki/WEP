<?php

function tpl_login($data) {
	global $_tpl;
	$_tpl['styles']['login']=1;
	$form = '';
	if($data['result']<1)
		$form = '<div class="cform" style="">
				<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
						<input type="hidden" name="ref" value="'.$data['ref'].'"/>
						<div>'.$data['#fn_login#'].':</div><input type="text" name="login" tabindex="1"/>
						<div>Пароль:</div><input type="password" name="pass" tabindex="2"/>
						<label style="display:block;">Запомнить?<input type="checkbox" style="border:medium none; width:13px;margin:0 0 0 10px;" tabindex="3" name="remember" value="1"/></label>
						<input type="hidden" name="ref" value="'.$data['ref'].'"/>
						<input class="submit" type="submit" name="enter" value="Войти" tabindex="4"/>
					</form>
					'.($data['remindpage']?'<div><a href="'.$data['remindpage'].'">Забыли пароль?</a></div>':'').'
					'.($data['regpage']?'<div><a href="'.$data['regpage'].'">Не зарегистрированы?</a></div>':'').'
				 <div style="clear:both;"></div>
			 </div>';
	$html = '<div class="loginForm"> '.$data['mess'].' '.$form.' </div>';
	return $html;
}
						
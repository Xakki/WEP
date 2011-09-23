<?php

class loginza_class extends kernel_extends
{
	function _set_features() {
		if (!parent::_set_features()) return false;
		$this->caption = 'Loginza';
		$this->tablename = '';
		return true;
	}

	function setSystemFields() {
		//parent::setSystemFields();
		$this->_setHook['__construct']['users'] = array(
			static_main::relativePath(dirname(__FILE__)).'/hook.php' => '_CHLU',
		);
	}

	function loginzaAuth($regme=false) {
		$UGROUP = NULL;
		_new_class('ugroup',$UGROUP);
		$USERS = $UGROUP->childs['users'];
		$mess = $dt = array();
		$flag = false;
		$authdata = file_get_contents('http://loginza.ru/api/authinfo?token='.$_POST['token']);
		$dt['loginza_data'] = $authdata;
		$authdata = json_decode($authdata,TRUE);
		if(isset($authdata['error_type'])) {
			$mess[] = array('name'=>'error', 'value'=>$authdata['error_type'].':'.$authdata['error_message']); 
		} else {
			$dt['loginza_token'] = $_POST['token'];
			$dt['loginza_provider'] = $authdata['provider'];
			$dt['loginza_login'] = md5($authdata['identity']);

			if($authdata['provider']=='http://openid.yandex.ru/server/') {
				$dt[$USERS->mf_namefields] = substr(substr($authdata['identity'],24),0,-1);
				$dt['email'] = substr(substr($authdata['identity'],24),0,-1).'@ya.ru';
			}
			elseif($authdata['provider']=='http://mail.ru/') {
				$dt[$USERS->mf_namefields] = $authdata['name']['first_name'];
				$temp = substr(substr($authdata['identity'],18),0,-1);
				$temp = explode('/',$temp);
				$dt['email'] = $temp[1].'@'.$temp[0].'.ru';
			}
			/*elseif($authdata['provider']=='http://vkontakte.ru/') {
				$dt[$USERS->mf_namefields] = $authdata['name']['last_name'].' '.$authdata['name']['first_name'];
				$dt['email'] = $authdata['email'];
			}*/
			else {
				if(isset($authdata['email']))
					$dt['email'] = $authdata['email'];
				if(is_array($authdata['name'])) {
					if(isset($authdata['name']['full_name']) and $authdata['name']['full_name'])
						$dt[$USERS->mf_namefields] = $authdata['name']['full_name'];
					elseif(isset($authdata['name']['first_name']) and $authdata['name']['first_name'])
						$dt[$USERS->mf_namefields] = $authdata['name']['first_name'];
				}else 
					$dt[$USERS->mf_namefields] = $authdata['name'];
			}
		}

		if(!count($mess)) {
			/*if(!$dt['email']) {
				$mess[] = array('name'=>'error', 'value'=>'Данный провайдер не сообщил ваш Email, который необходим для авторизации на нашем сайте. Возможно в настройках провайдера вашего аккаунта есть опция позволяющая передавать Email. В любом случае вы можете воспользоваться стандартной регистрацие в нашем сайте , это не займет много времени.');
			} else {*/
				
				$q = 't1 Join '.$USERS->owner->tablename.' t2 on t1.'.$USERS->owner_name.'=t2.id where t1.loginza_login=\''.mysql_real_escape_string($dt['loginza_login']).'\'';
				if(isset($dt['email']) and $dt['email'])
					$q .= ' or t1.email="'.mysql_real_escape_string($dt['email']).'"';
				$data = $USERS->_query('t2.active as gact,t2.name as gname,t1.id,t1.active',$q);
				//print_r($data);
				//print_r('<pre>');print_r($regme);print_r($dt);exit();

				if(count($data)) {
					$data = $data[0];
					if(!$data['active']) {
						$mess[] = array('name'=>'error', 'value'=>'Ваш аккуант отключён администратором.');
					} elseif(!$data['gact']) {
						$mess[] = array('name'=>'error', 'value'=>'Ваша группа отключена администратором.');
					} else {
						$flag = true;
						$USERS->id = $data['id'];
					}
				}
				elseif(!$regme and !isset($_GET['regme'])) {
					global $PGLIST;
					session_go(1);
					$_SESSION['loginza'] = $dt;
				} 
				else {
					if(!$dt[$USERS->mf_namefields]) $dt[$USERS->mf_namefields] = $dt['email'];
					list($flag,$mess) = $this->loginzaReg($dt);
				}

				if($flag) {
					session_go(1);
					$mess[] = array('name'=>'ok', 'value'=>$this->_CFG['_MESS']['authok']);
					$USERS->setUserSession($USERS->id);
					//static_main::_prmModulLoad();
				}

		} 
		return array($flag,$mess);
	}

	function loginzaReg($data) {
		$flag = false;
		_new_class('ugroup',$UGROUP);
		$USERS = $UGROUP->childs['users'];
		$data['owner_id']=$USERS->owner->config['reggroup'];
		$data['active']=1;
		$data['reg_hash'] = 2; // отметка  о том что регестрируются через LOGINZA
		$pass = substr($data['loginza_login'],10);
		$data[$USERS->fn_pass]=md5($USERS->_CFG['wep']['md5'].$pass);
		if($USERS->fn_login!='email') {
			$temp = json_decode($data['loginza_data'],TRUE);
			$identity = '';
			if(isset($temp['nickname'])) {
				$identity = preg_replace("/[^0-9A-Za-z]+/",'',$temp['nickname']);
			}elseif($data['email']) {
				$identity = explode('@',$data['email']);
				$identity = preg_replace("/[^0-9A-Za-z]+/",'',$identity[0]);
			}
			else
				$identity = preg_replace("/[^0-9A-Za-z]+/",'',$temp['identity']);
			$dataquery = $USERS->_query('id','Where '.$USERS->fn_login.' LIKE "'.$identity.'%"',$USERS->fn_login);
			if(count($dataquery)) {
				$identityNew = $identity.date('Y');
				if(isset($dataquery[$identityNew])) {
					$fli = false;$cnt= 1;
					while(!$fli) {
						$identityNew = $identity.$cnt;
						if(isset($dataquery[$identityNew]))
							$cnt++;
						else
							$fli = true;
					}
				}
				$identity = $identityNew;
			}

			$data[$USERS->fn_login] = $identity;
		}
		$USERS->fld_data = $data;
		if($USERS->_add(true)) {
			$USERS->fld_data = array($this->mf_createrid=>$USERS->id);
			$USERS->_update();
			$flag = true;
			$datamail = array();
			global $MAIL;
			if(!$MAIL) _new_class('mail',$MAIL);
			$datamail['creater_id']=0;
			$datamail['mail_to']=$data['email'];
			$datamail['user_to'] = $USERS->id;
			$datamail['subject']='Вы зарегестрированы на сайте '.strtoupper($_SERVER['HTTP_HOST']);
			$datamail['text']=str_replace(array('%pass%','%login%','%host%'),array($pass,$data[$USERS->fn_login],$_SERVER['HTTP_HOST']),$USERS->owner->config['mailinfo']);
			$MAIL->reply = 0;
			if(!$MAIL->Send($datamail)) {
				$mess[] = array('name'=>'error', 'value'=>$MAIL->_CFG['_MESS']['mailerr']);
			}
		} else
			$mess[] = array('name'=>'error', 'value'=>$MAIL->_CFG['_MESS']['regerr']);
		return array($flag,$mess);
	}

}


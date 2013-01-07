
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>WEP - {#BH#}</title>
		<base href="{#BH#}"/>
		<meta name="keywords" content="WEP"/> 
		<meta name="description" content="CMS"/>
		<link rel="SHORTCUT ICON" href="{#BH#}/_design/favicon.ico"/>

        <link rel="stylesheet" href="{#BH#}/_design/_style/loginTpl.css" type="text/css">

		<script src="{#BH#}/_design/_script/jquery.js"></script>
		<script src="{#BH#}/_design/_script/login.js"></script>
        <!--[if lt IE 9]>
          <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    
    <body>
    	{#logs#}
		<div id="formContainer" class="{#flipped#}">
			<form id="login" method="post" action="{#actionLogin#}">
				<input type="hidden" name="ref" value="{#ref#}"/>
				<a href="#" id="flipToRecover" class="flipLink">{#forgot#}</a>
				<input type="text" name="login" id="loginEmail" placeholder="{#loginLabel#}" tabindex="1"/>
				<input type="password" name="pass" id="loginPass" placeholder="{#passLabel#}" tabindex="2"/>
				<label class="remember">{#rememberLabel#}<input type="checkbox" tabindex="3" name="remember" value="1"/></label>
				<input type="submit" name="submit" value="{#loginSubmit#}" tabindex="4"/>
			</form>
			<form id="recover" method="post" action="{#actionRecover#}">
				<input type="hidden" name="ref" value="{#ref#}"/>
				<a href="#" id="flipToLogin" class="flipLink">{#forgot#}</a>
				<input type="text" name="recoverEmail" id="recoverEmail" placeholder="{#forgotLabel#}" tabindex="5"/>
				<input type="submit" name="submit" value="{#forgotSubmit#}" tabindex="6"/>
			</form>
			<div id="popMess">{#popMess#}</div>
			<div id="popMessFlip">{#popMessFlip#}</div>
		</div>

        <footer>
	        <h2>Powered on WEP</h2>
	        <h2><a href="http://red.xakki.ru/projects/wep/wiki" target="_blank">Help & Wiki</a></h2>
            <h2><a href="https://github.com/Xakki/WEP" target="_blank">WEP on GITHUB</a></h2>
        </footer>
           
    </body>
</html>

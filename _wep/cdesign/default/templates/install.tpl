<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
	<head>
		<title>{$_tpl['title']}</title>
		<base href="{$_CFG['_HREF']['BH']}"/>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta name="keywords" content="WEP"/> 
		<meta name="description" content="CMS"/>
		<link rel="SHORTCUT ICON" href="{$_tpl['design']}img/favicon.ico"/>
		<script type="text/javascript" src="_design/_script/jquery.js"></script>
		<script type="text/javascript" src="_design/_script/include.js"></script>
		<script type="text/javascript" src="_design/_script/form.js"></script>
		<script type="text/javascript" src="_design/_script/utils.js"></script>
		<script type="text/javascript">
			function show_fblock(obj,selector) {
				if(jQuery(selector).is(':hidden')) {
					jQuery(selector).show();
					jQuery(obj).addClass('shhide');
				}
				else {
					jQuery(selector).hide();
					jQuery(obj).removeClass('shhide');
				}

			}
		</script>
		<link type="text/css" href="_design/_style/form.css" rel="stylesheet"/>
		<style>
			html, body {
				height:100%;
				width:100%;
				margin:0;
				padding:0;
			}
			.wepmain {
				background-color:#eFeFFF;
				width:800px;
				margin:0 auto;
				position: relative;
			}
			.title {
				font-size:1.4em;
				font-weight:bold;
				text-align:center;
				padding:10px 0;}
			.step {
				font-size:1em;
				padding:5px 0;
			}
				.step .stepitem {
					background-color: #E1E1E1;
					border: 1px solid black;
					display: inline-block;
					height: 42px;
					margin: 0;
					text-align:center;
					font-weight:bold;
					width:24.75%;
				}
				.step .stepitem:hover {
					background-color:gray;
				}
				.step .selstep {
					background-color:#99C6F1;
				}
				.step .stepcomment {
					font-size:0.85em;
					text-align:center;
				}

				.showparam {
					cursor:pointer;
					border-bottom:1px dashed #101796;
					font-size:1.2em;
					color:#000;
					display:inline;
					padding:0;}
				.showparam span.shbg{
					display:inline-block;
					height: 12px;
					width: 19px;
					background:transparent url(/_design/_img/buttonh.png)  no-repeat scroll 0 -98px;}
				.showparam .sh1 {display:inline;}
				.showparam .sh2 {display:none;}

				.shhide span.shbg{
					display:inline-block;
					height:12px;
					width:19px;
					background:transparent url(/_design/_img/buttonh.png)  no-repeat scroll 0 -114px;}
				.shhide .sh1 {display:none;}
				.shhide .sh2 {display:inline;}

				.showparam:hover {
					border-bottom:1px dashed transparent;
				}
				.showparam:hover span.shbg {
					background-url:/_design/_img/button.png;
				}

				.fblock {
					background-color:#99C6F1;
					width:70%;
					margin:0 auto;
				}
			
			.debug_view {}
			.text {
				height:auto;
				margin:10px 0 15px 0;
				}

			.cmsinfo {
				border-top:2px solid black;
				height:35px;
				width:100%;
				background-color:#63A6CC;
			}
			.cmsinfo a {color:#FFF;}
			.cmsinfo .infname {float:left;width:200px;font-weight:bold;font-size:20px;color:#333;padding:2px 0pt 0pt 10px;}
			.cmsinfo .infc {float:right;font-weight:none;font-size:10px;color:#FFF;text-align:left;width:160px;}
			.cmsinfo .infc .ctd1 {width:55px;float:left;clear:left;text-align:right;padding:0 5px 0 0;}
			.cmsinfo .infc .ctd2 {width:100px;float:right;clear:right;text-align:left;}
			.cmsinfo #inftime {font-size:11px;position:absolute;left:210px;bottom:0;}
			.cmsinfo #inftime div {float:left;padding:0 5px 5px;}

		</style>
	</head>
	<body onload="{$_tpl['onload']}">
		<div class='wepmain'>
			<div class="title">{$_tpl['title']}</div>
			<div class="step">{$_tpl['step']}</div>
			<div class="debug_view">{$_tpl['logs']}</div>
			<div class="text">{$_tpl['text']}</div>
			<div class="cmsinfo">
				<div class="infname"><a href="http://xakki.ru">WebEngineOnPHP</a></div>
				<div class="infc">{$_tpl['contact']}</div>
				<div id="inftime">{$_tpl['time']}</div>
			</div>
		</div>
	</body>
</html>

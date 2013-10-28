<?php
/**
 * GEO IP
 * @type Контент
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

$jfunct = false;
_new_class('terra', $TERRA);
$data = $TERRA->geoIP();

$html = '
	<div id="map_canvas" style="width:500px; height:500px;float:right;"></div>
	<div style="height:520px;" id="infoIP">
		<h3>Информация по IP</h3>
		<ul>';
if (count($data)) {
	$html .= '
				<li>IP - ' . $data['ip'] . '</li>
				<li>' . $data['socr_name'] . ' - ' . $data['name'] . '</li>';
	foreach ($data['parent'] as $row) {
		$html .= '<li>' . $row['socr_name'] . ' - ' . $row['name'] . '</li>';
		if ($row['temp_okryg'])
			$html .= '<li>' . $row['temp_okryg'] . '</li>';
	}
	$html .= '
				<li>Индекс - ' . $data['index'] . '</li>
				<li>Широта - ' . $data['latitude'] . '</li>
				<li>Долгота - ' . $data['longitude'] . '</li>
			';
} else {
	$html .= '
				<li>Ваш IP в базе не обнаружен</li>
				<li>IP - ' . $_SERVER['REMOTE_ADDR'] . '</li>
			';
	$jfunct = '
				function setCenter(loc) {
					map.setCenter(loc);
					/// TODO console.log(loc);
				}';
}
$html .= '
		</ul>
		<h3>Информация о браузере</h3>
		<ul>
			<li>Браузер - <script>document.write(window.navigator.userAgent);</script><noscript>Неизвестно</noscript></li>
			<li>Куки (Cookies) - (TODO)Да</li>
			<li>Ява-скрипт (JavaScript) - <script>document.write(\'<span class="green">Работает</span>\');</script><noscript>&lt;span class="red"&gt;Не работает&lt;/span&gt;</noscript></li>
			<li>Аякс (AJAX) - (TODO)Да</li>
			<li>Поддержка сервиса Geolocation - <span id="infoGeolocation">Неизвестно</span></li>
			<li>Операционная система - (TODO)Windows</li>
			<li>Разрешение экрана - <script>res = screen.width + \' на \' + screen.height + \' пикселей\';document.write(res);</script></li>
		</ul>
	</div>

	';
/*
jQuery.each(jQuery.browser, function(i, val) {
$("<div>" + i + " : <span>" + val + "</span>").appendTo(document.body);
});



*/
if (count($data))
	$LatLng = 'var defLatLng = new google.maps.LatLng(' . $data['latitude'] . ', ' . $data['longitude'] . ');';
else
	$LatLng = 'var defLatLng = new google.maps.LatLng(40.69847032728747, -73.9514422416687);';

setScript('http://maps.google.com/maps/api/js?sensor=false');
$_tpl['script']['initialize'] = '
function initialize() {
	var initialLocation;
	' . $LatLng . '
	var browserSupportFlag =  new Boolean();

	var myOptions = {
		zoom: 10,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	$("#infoGeolocation").text("ДА");
	// Try W3C Geolocation (Preferred)
	if(navigator.geolocation) {
		browserSupportFlag = true;
		navigator.geolocation.getCurrentPosition(function(position) {
			initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
			setCenter(initialLocation);
		}, function() {
			handleNoGeolocation(browserSupportFlag);
		});
		// Try Google Gears Geolocation
	} else if (google.gears) {
		browserSupportFlag = true;
		var geo = google.gears.factory.create("beta.geolocation");
		geo.getCurrentPosition(function(position) {
			initialLocation = new google.maps.LatLng(position.latitude,position.longitude);
			setCenter(initialLocation);
		}, function() {
			handleNoGeoLocation(browserSupportFlag);
		});
		// Browser doesnt support Geolocation
	} else {
		browserSupportFlag = false;
		handleNoGeolocation(browserSupportFlag);
	}
  
	function handleNoGeolocation(errorFlag) {
		if (errorFlag == true) {
			$("#infoGeolocation").text("Ошибка в работе сервиса");
		} else {
			$("#infoGeolocation").text("НЕТ");
		}
		initialLocation = defLatLng;
		map.setCenter(initialLocation);
	}
	' . ($jfunct ? $jfunct : '
		function setCenter(loc) {
			map.setCenter(loc);
		}
	') . '
}
  ';
$_tpl['onload'] .= 'initialize();';

return $html;

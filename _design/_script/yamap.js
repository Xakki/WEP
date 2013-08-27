var Yamap = {
	mapx:0,
	mapy:0,
	Zoom:16,
	viewMap:0,
	option: {
		draggable:false
	},
	tools : {
		Search:false,
		Zoom:true,
		ScrollSearch:true,
		Type:true
	}
};
var setMap;//объект карты
var setPlacemark; // Метка
var setToolbar;
var viewMap=0;

function boardOnMap(tp) {
	Yamap.option.draggable=true;
	Yamap.tools.Search = true
	if(tp) Yamap.viewMap = tp;
//	if(jQuery('#boardOnMap').size()==0) {
//		jQuery('body').append('<div id="boardOnMap" style="display:none;"><div id="YMapsID" style="width:700px;height:500px;background-color: white;"></div></div>');
//	}
//	jQuery('#boardOnMap').show().css('height','auto');
    if(jQuery('#YMapsID').size()==0) {
	    wep.staticPopUp('<div id="YMapsID" style="width:700px;height:500px;background-color: white;"></div>');
    }
    else {
        wep.staticOpenPopUp();
    }

	if(!setMap) {
		YMaps.load(initMap);
	}
}

function initMap() {
	//jQuery('body').append('<div id="boardOnMap"><div class="layerblock"><div id="YMapsID" style="width:600px;height:400px;background-color: white;"></div></div></div>');
	// Создает обработчик события window.onLoad
	YMaps.jQuery(function () {
		// Создает экземпляр карты и привязывает его к созданному контейнеру
		if(!setMap) setMap = new YMaps.Map(YMaps.jQuery("#YMapsID")[0]);
		var flag = true;
		// Устанавливает начальные параметры отображения карты: центр карты и коэффициент масштабирования
		if(Yamap.mapx==0) {
			Yamap.mapx = jQuery('#mapx').val();
			Yamap.mapy = jQuery('#mapy').val();
			Yamap.Zoom = 13;
			if(Yamap.mapx==0) {
				if (YMaps.location) {
					Yamap.mapx = YMaps.location.longitude;
					Yamap.mapy = YMaps.location.latitude;
				} else {
					Yamap.mapx = 37.64;
					Yamap.mapy = 55.76;
				}
				flag= false;
			} else
				Yamap.Zoom = 16;
		}

		setMap.setCenter(new YMaps.GeoPoint(Yamap.mapx, Yamap.mapy), Yamap.Zoom);//2ой параметр - ZOOM
		
		if(!setPlacemark) {
			var opt = Yamap.option;
			setPlacemark = new YMaps.Placemark(new YMaps.GeoPoint(Yamap.mapx, Yamap.mapy), opt);
			setPlacemark.name = "Метка";
			if(Yamap.option.draggable) {
				YMaps.Events.observe(setPlacemark, setPlacemark.Events.PositionChange, function (obj,Point) {
					jQuery('#mapx').val(Point.newPoint.__lng);jQuery('#mapy').val(Point.newPoint.__lat);
				}, setMap);
			}
		}
		if(flag) {
			setPlacemark.setGeoPoint(new YMaps.GeoPoint(Yamap.mapx, Yamap.mapy));
			setMap.addOverlay(setPlacemark);
		}

		if(!setToolbar) {
			// Создает панель инструментов без кнопки "Линейка"
			setToolbar = new YMaps.ToolBar([
				new YMaps.ToolBar.MoveButton(), 
				new YMaps.ToolBar.MagnifierButton()
			]);


			//////////
			var button2 = new YMaps.ToolBarToggleButton({ 
				icon: "http://api.yandex.ru/i/maps/icon-fullscreen.png", 
				hint: "Разворачивает карту на весь экран"
			});
			// Если кнопка активна, разворачивает карту на весь экран
			YMaps.Events.observe(button2, button2.Events.Select, function () {
				setSize(1024, 768);
			});
			// Если кнопка неактивна, устанавливает фиксированный размер карты
			YMaps.Events.observe(button2, button2.Events.Deselect, function () {
				setSize(700, 500);
			});
			// Функция устанавливает новые размеры карты
			function setSize (newWidth, newHeight) {
				YMaps.jQuery("#YMapsID").css({
					width: newWidth || "", 
					height: newHeight || ""
				});
				setMap.redraw();
				fMessPos(' #boardOnMap');jQuery('#boardOnMap').css('height','auto');
			}
			setToolbar.add(button2);
			
			if(Yamap.viewMap==0) {
				//////////////
				var button = new YMaps.ToolBarToggleButton({ 
					caption: "Добавить метку", 
					hint: "Добавляет метку в центр карты"
				});
				YMaps.Events.observe(button, button.Events.Select, function () {
					//button.setContent('Удалить метку');
					var center = setMap.getCenter();
					jQuery('#mapx').val(center.__lng);jQuery('#mapy').val(center.__lat);
					setPlacemark.setGeoPoint(center);
					this.addOverlay(setPlacemark);
				}, setMap);
				YMaps.Events.observe(button, button.Events.Deselect, function () {
					this.removeAllOverlays();
				}, setMap);
				setToolbar.add(button);
			}

			// Добавление панели инструментов на карту
			setMap.addControl(setToolbar);
			if(Yamap.tools.Zoom)
				setMap.addControl(new YMaps.Zoom());
			if(Yamap.tools.Type)
				setMap.addControl(new YMaps.TypeControl());
			if(Yamap.tools.Search)
				setMap.addControl(new YMaps.SearchControl());
			if(Yamap.tools.ScrollSearch)
				setMap.enableScrollZoom();
			setMap.enableRightButtonMagnifier();
		}
	});
}
// Функция для отображения результата геокодирования
// Параметр value - адрес объекта для поиска
function showAddress (value) {
	// Удаление предыдущего результата поиска
	map.removeOverlay(geoResult);
	// Запуск процесса геокодирования
	var geocoder = new YMaps.Geocoder(value, {results: 1, boundedBy: map.getBounds()});
	// Создание обработчика для успешного завершения геокодирования
	YMaps.Events.observe(geocoder, geocoder.Events.Load, function () {
		// Если объект был найден, то добавляем его на карту
		// и центрируем карту по области обзора найденного объекта
		if (this.length()) {
			geoResult = this.get(0);
			//map.addOverlay(geoResult);
			map.setBounds(geoResult.getBounds());
		}else {
			alert("Ничего не найдено")
		}
	});

	// Процесс геокодирования завершен неудачно
	YMaps.Events.observe(geocoder, geocoder.Events.Fault, function (geocoder, error) {
		alert("Произошла ошибка: " + error);
	})
}

function delMap() {
	jQuery('#boardOnMap').hide();
	hideBG();
	//setMap.destructor();
}
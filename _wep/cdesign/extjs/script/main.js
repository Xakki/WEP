function setCookie (name, value, expires, path, domain, secure) {
	document.cookie = name + "=" + escape(value) +
	((expires) ? "; expires=" + expires : "") +
	((path) ? "; path=" + path : "") +
	((domain) ? "; domain=" + domain : "") +
	((secure) ? "; secure" : "");
}


Ext.onReady(function() {
	// инициализируем объект wep
	wep.init();

	// вешаем обработчик на все ссылки
	Ext.select('a').on('click', wep.click_handler);
});

wep = {
	version: 0.1
};

Ext.apply(wep, {

	init: function()
	{
		wep.observer = new Ext.util.Observable();
	},
	
	main_cont: 'modulsforms', // id главного контейнера
	edit_form_cont: 'editform', // id контейнера с формой
	breadcrumbs_cont: 'breadcrumbs', // id контейнера с хлебными крошками

	// пути
	path: {
		extjs: '_design/_script/extjs/',
		cscript: '_wep/cdesign/extjs/script/'
	},

	breadcrumbs: {
		path: [],

		// переходит на крошку номер num, если второй пар-р = true, то впередистоящие будут уничтожены
		goTo: function(num, del_next) {
			if (num < 0) {
				num = wep.breadcrumbs.path.length + num;
			}
			if (num >= 0 && wep.breadcrumbs.path.length > num) {
				var bc = wep.breadcrumbs.path[wep.breadcrumbs.active_item];
				if (wep.breadcrumbs.active_item != num && Ext.isObject(bc.onGoOut) && 
					(!(wep.breadcrumbs.active_item > num && del_next == true))
				) {
					bc.onGoOut.handler.apply(bc.onGoOut.scope || window, bc.onGoOut.args);
				}

				bc = wep.breadcrumbs.path[num];

				if (Ext.isObject(bc.onGoTo)) {
					bc.onGoTo.handler.apply(bc.onGoTo.scope || window, bc.onGoTo.args);
				}
				if (del_next == true) {
					for(var i=wep.breadcrumbs.path.length-1; i>num; i--) {
						if (Ext.isDefined(wep.breadcrumbs.path[i].dom_id)) {
							Ext.get(wep.breadcrumbs.path[i].dom_id).update('');
						}
						if (Ext.isDefined(wep.breadcrumbs.path[i].component_id)) {
							Ext.getCmp(wep.breadcrumbs.path[i].component_id).destroy();
						}
						if (Ext.isObject(wep.breadcrumbs.path[i].onDelete)) {
							wep.breadcrumbs.path[i].onDelete.handler.apply(wep.breadcrumbs.path[num].onDelete.scope || window, wep.breadcrumbs.path[num].onDelete.args);
						}
					}
					wep.breadcrumbs.path = wep.breadcrumbs.path.slice(0,num+1);
				}				
			}
			wep.breadcrumbs.active_item = num;

			wep.breadcrumbs.render();
		},

		// добавляет к свойствам крошки номер num все св-ва из объекта obj
		edit: function(obj, num) {
			if (Ext.isDefined(wep.breadcrumbs.path[num])) {
				Ext.apply(wep.breadcrumbs.path[num], obj);
				wep.breadcrumbs.render();
			}

		},

		/* *********************************************
		 * добавляет крошку
		 * obj - объект со след св-вами
		 * title
		 * component_id - id extjs компонента, если передан, то при уничтожении крошки он тоже будет удаляться
		 * dom_id - если передан, то при уничтожении крошки html элемент с id=dom_id будет очищаться
		 * onAdd
		 * OnDelete
		 * ******************************************************/
		add: function(obj, num) {
			if (Ext.isDefined(obj.dom_id)) {
				Ext.get(obj.dom_id).update('');
			}

			if (Ext.isDefined(num))	{
				if (wep.breadcrumbs.path.length > num) {
					for (var i=wep.breadcrumbs.path.length-1; i>=num; i--) {
						if (Ext.isDefined(wep.breadcrumbs.path[i].component_id)) {
							Ext.getCmp(wep.breadcrumbs.path[i].component_id).destroy();
						}

						if (Ext.isObject(wep.breadcrumbs.path[i].onDelete)) {
							wep.breadcrumbs.path[i].onDelete.handler.apply(wep.breadcrumbs.path[num].onDelete.scope || window, wep.breadcrumbs.path[num].onDelete.args);
						}
					}
					
					wep.breadcrumbs.path = wep.breadcrumbs.path.slice(0,num);
				}
			}

			if (Ext.isDefined(obj.component_id)) {
				Ext.each(wep.breadcrumbs.path, function(value, index) {
					if (obj.component_id === value.component_id) {
						for (var i=wep.breadcrumbs.path.length-1; i>=index; i--) {
							if (Ext.isDefined(wep.breadcrumbs.path[i].component_id)) {
								Ext.getCmp(wep.breadcrumbs.path[i].component_id).destroy();
							}

							if (Ext.isObject(wep.breadcrumbs.path[i].onDelete)) {
								wep.breadcrumbs.path[i].onDelete.handler.apply(wep.breadcrumbs.path[num].onDelete.scope || window, wep.breadcrumbs.path[num].onDelete.args);
							}
						}
						wep.breadcrumbs.path = wep.breadcrumbs.path.slice(0,index);
					}
				});
			}

			if (
				Ext.isDefined(wep.breadcrumbs.path[wep.breadcrumbs.active_item]) &&
				Ext.isObject(wep.breadcrumbs.path[wep.breadcrumbs.active_item].onGoOut)
			) {
				var bc = wep.breadcrumbs.path[wep.breadcrumbs.active_item];
				bc.onGoOut.handler.apply(bc.onGoOut.scope || window, bc.onGoOut.args);
			}

			wep.breadcrumbs.path.push(obj);

			if (Ext.isObject(obj.onAdd)) {
				obj.onAdd.handler.apply(obj.onAdd.scope || window, obj.onAdd.args);
			}

			wep.breadcrumbs.active_item = wep.breadcrumbs.path.length-1;

			wep.breadcrumbs.render();
		},
		
		// перерисовка крошек
		render: function() {
			var html = '';
			Ext.each(wep.breadcrumbs.path, function(value, index) {
				if (index == wep.breadcrumbs.active_item) {
					html += '<span class="act">' + value.title + '</span>';
				}
				else {
					html += '<span onclick="wep.breadcrumbs.goTo(' + index + ')">' + value.title + '</span>';
				}
				if (index != wep.breadcrumbs.path.length-1) {
					html += ' :: ';
				}
			});

			Ext.get(wep.breadcrumbs_cont).update(html);
		}

	},
	
	// динамически подключенные файлы
	included_files: {},	

	// подключает css файл
	// id - идентификатор. Если будут будут подключены 2 файла с одним id, то первый удалится
	include_css: function(id, url)
	{
		if (!Ext.isDefined(wep.included_files[url]))
		{
			wep.included_files[url] = true;
			Ext.util.CSS.swapStyleSheet(id, url);
		}
	},
		
	// подключает js файлы
	// fileList - список подключаемых файлов. Может быть строкой или массивом
	// callback - выполнится после того, как файлы подключатся
	// scope - передаваемые в callback пар-ры
	// preserveOrder - если true, файлы будут подгружаться по очереди, по умолчанию false
	include_js: function(fileList, callback, scope, preserveOrder)
	{
		var uniqueFileList = [];

		Ext.each(fileList, function(file, index)
		{
			if (!Ext.isDefined(wep.included_files[file]))
			{
				wep.included_files[file] = true;
				uniqueFileList.push(file);
			}
		});

		if(!Ext.isEmpty(uniqueFileList, true))
		{
			Ext.Loader.load(uniqueFileList, callback, scope, preserveOrder);
		}
		else if (Ext.isFunction(callback))
		{
			var scope = scope || this; 

			callback.call(scope)
		}
	},

	//обрабатывает клики по ссылкам
	click_handler: function(currentnode, clickevent) {
		wep.href = this.href;

		if (this.href.indexOf('_wep/') == -1) {
			return true;
		}

		// меняем стиль для ссылок
		wep.change_menu_item_style(this);

		// парсим url
		wep.GET = {};
		if (wep.href.indexOf('?') != -1)
		{
			var get_matches = [];
			var request = wep.href.split('?',2)[1];
			if (request.indexOf('&') != -1)
			{
				 get_matches = request.split('&');			
			}		
			else
			{
				get_matches[0] = request;
			}
			
			for (var i=0; i<get_matches.length; i++) 
			{
				var params = get_matches[i].split('=');
				wep.GET[params[0]] = params[1];
			}
		}


		if (wep.GET['_view'] == 'list') {
			wep.modul = {
				title: this.innerHTML,
				cn: wep.GET['_modul']
			}

			jsfiles = [
				wep.path['extjs'] + 'ux/CheckColumn.js',

				wep.path['extjs'] + 'ux/MultiSelect.js',
				wep.path['extjs'] + 'ux/ItemSelector.js',

				wep.path['extjs'] + 'ux/treegrid/TreeGridSorter.js',
				wep.path['extjs'] + 'ux/treegrid/TreeGridColumnResizer.js',
				wep.path['extjs'] + 'ux/treegrid/TreeGridNodeUI.js',
				wep.path['extjs'] + 'ux/treegrid/TreeGridLoader.js',
				wep.path['extjs'] + 'ux/treegrid/TreeGridColumns.js',
				wep.path['cscript'] + 'TreeGrid.js'

			];

			wep.include_js(jsfiles, function() {

				Ext.Ajax.request({
					url: '_wep/index.php?_view=listcol&_modul=' + wep.modul.cn,
					success: function(result, textStatus) {

						var data = Ext.util.JSON.decode(result.responseText);

						var columns = data['columns'];
//						var fields = data['fields'];
						var children = data['children'];
						var pagenum = data['pagenum'];

						var tree_id = wep.modul.cn + '_tree';
						var panel_id = wep.modul.cn + '_panel';

						wep.breadcrumbs.add({
							title: wep.modul.title,
							component_id:panel_id,
							dom_id: wep.main_cont,
							onGoTo: {
								handler: function() {
									Ext.getCmp(panel_id).expand();
								}
							},
							onGoOut: {
								handler: function() {
									Ext.getCmp(panel_id).collapse();
								}
							}
						},
						0);

						var tree = new wep.TreeGrid({
							id: tree_id,
							modul: wep.modul.cn,
							add_url: '&_modul=' + wep.modul.cn,
							autoHeight: true,
							autoWidth: true,
							pagenum: pagenum,
							columns: columns,
							children: children,
							requestMethod: 'GET',
							dataUrl: '_wep/index.php?_view=list&_modul=' + wep.modul.cn,
						});

						var panel = new wep.panel({
							id: panel_id,
							title: 'Модуль ' + wep.modul.title,
							renderTo: wep.main_cont,
							items: [
								tree,
								tree.pnav.links,
								tree.pnav.combobox
							]
						});

					},
					failure: function() {
						Ext.Msg.alert('Ошибка', 'Произошла ошибка');
					}
				});
			});	
		}
			
		currentnode.stopEvent(); // чтобы не переходило по ссылке
		return false;
	},

	panel: Ext.extend(Ext.Panel, {
		autoWidth: true,
		autoHeight: true,
		collapsible:true
	}),
	
	form_panel: Ext.extend(Ext.FormPanel, {
		frame: true,
		labelAlign: 'left',
		bodyStyle:'padding:5px',
		layout: 'column',
		initComponent: function(config) {

			Ext.apply(this, config);
			wep.form_panel.superclass.initComponent.call(this);

			Ext.QuickTips.init();
		}
	}),
	
	// меняет стиль ссылок при нажатии на них
	change_menu_item_style: function(obj)
	{
		if (Ext.isDefined(wep.last_checked_link))
		{
			Ext.get(wep.last_checked_link).parent().removeClass('selected');
		}
		wep.last_checked_link = obj;
		Ext.get(obj).parent().addClass('selected');
	}

});

// пустая картинка для верстки
Ext.BLANK_IMAGE_URL = wep.path.extjs + 'resources/images/default/s.gif';

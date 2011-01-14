$(document).ready(function() {
	// вешаем обработчик кликов на все ссылки
	$('a').click(wep.click_handler);
});

Ext.onReady(function() {  
//	Ext.select('a').on('click', wep.click_handler);	
	// инициализируем объект wep
	wep.init();
});


wep = {
	version: 0.1
};

Ext.apply(wep, {

	init: function()
	{
		wep.observer = new Ext.util.Observable();
	},

	// пути
	path: {
		extjs: '_design/_script/extjs/',
		cscript: '_wep/cdesign/extjs/script/'
	},

	// динамически подключенные файлы
	included_files: {},	

	// подключает css файл
	// id - идентификатор. Если будут будут подключены 2 файла с одним id, то первый удалится
	include_css: function(id, url)
	{
		if (wep.included_files[url] == undefined)
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
			if (wep.included_files[file] == undefined)
			{
				wep.included_files[file] = true;
				uniqueFileList.push(file);
			}
		});

		if(!Ext.isEmpty(uniqueFileList, true))
		{
			Ext.Loader.load(uniqueFileList, callback, scope, preserveOrder);
		}
		else if (typeof callback == 'function')
		{
			var scope = scope || this; 

			callback.call(scope)
		}
	},

	// подключает 1 файл
	// при этом код, который идет после не выполнится, пока файл не подключится
	include_file: function(file)
	{
		if (wep.included_files[file] == undefined)
		{
			var matches = file.split('.');
			var code = '';
			var ext = matches[matches.length-1];
			switch (ext)
			{
				case 'js':
					code = '<script type="text/javascript" src="' + file + '"></script>';
					break;
				case 'css':
					code = '<link rel="stylesheet" type="text/css" href="' + file + '" rel="stylesheet" />';
					break;
				default:
					alert('Файлы с расширением ' + ext + ' не подключаются.');
					break;
			}
			if (code != '')
			{
				wep.included_files[file] = true;
				$('head').append(code);
			}
		}
	},

	// подключает файлы, может передаваться объект, массив или строка с адресом файла
	// при этом код, который идет после не выполнится, пока все файлы не подключится
	include: function(files)
	{
		if (Ext.isObject(files) || Ext.isArray(files))
		{
			$.each(files, function(index, file)
			{
				wep.include_file(file);
			});
			
			return true;
		}
		else if (Ext.isString(files))
		{
			wep.include_file(files);
		}
		else
		{
			alert('Передан неверный тип параметра в функцию include');
			return false;
		}
	},

	//обрабатывает клики по ссылкам
	click_handler: function(e) {
		wep.href = this.href;

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
		
		// добавляем в modulsforms дерево элементов
		if (wep.GET['_view'] == 'list')
		{
//			wep.get_tree(wep.GET['_modul'], 'modulsforms');
			
//			wep.get_list(wep.GET['_modul'], 'modulsforms');
	
			wep.get_panel(wep.GET['_modul'], 'modulsforms');	
		}
			
		return false;
	},
	
	panel: Ext.extend(Ext.Panel, {
		itemId: 'modul_panel',
		renderTo: 'modulsforms',
		layout: 'fit',
		initComponent:function(config) {		
			
			var modul = wep.GET['_modul'];
			var id_cont = 'modulsforms';
			
			var cm = new Ext.grid.ColumnModel({
				// specify any defaults for each column
				defaults: {
					sortable: true // columns are not sortable by default           
				},
				columns: this.columns
			});
			
			// create the Data Store
			var store = new Ext.data.Store({
				// destroy the store if the grid is destroyed
				autoDestroy: true,

				// load remote data using HTTP
				url: '_wep/index.php?_view=list&_modul=' + modul,

				reader: new Ext.data.JsonReader({
					fields: this.fields
				}),

				sortInfo: {field:'id', direction:'ASC'}
			});
			
			
			
			
			// manually trigger the data store load
			store.load({
				// store loading is asynchronous, use a load listener or callback to handle results
				callback: function(){
					Ext.Msg.show({
						title: 'Store Load Callback',
						msg: 'store was loaded, data available for processing',
						modal: false,
						icon: Ext.Msg.INFO,
						buttons: Ext.Msg.OK
					});				
				}					
			});
			


			var grid = new Ext.grid.EditorGridPanel({
				store: store,
				cm: cm,
			//	width: '100%',
			//	height: '50%',
				autoExpandColumn: 'id', // column with this id will be expanded
				title: 'Страницы',
				autoHeight : true,
				enableColumnResize: true,
				minColumnWidth : 50,
				enableHdMenu : false
			});
		
		rrr = this;
			
			grid.ddsdsds = 4;
			grid.getSelectionModel().on('selectionchange', function(sel, node) {
				if (node) {

					sel.grid.ownerCt.add({
						region:'center',
						margins:'5 5 5 0',
						cls:'empty',
						bodyStyle:'background:#f1f1f1',
						html:'<br/><br/>&lt;empty center panel&gt;'
					});
					sel.grid.ownerCt.doLayout();
				}
			});
			
			Ext.apply(this, {
				region: 'east',
				split: true,
//				width: 250, // give east and west regions a width
//				minSize: 250,
//				maxSize: 400,
				layout: 'anchor',
				items: [
					{				
						title: 'Модуль ' + modul,
					},
					grid 
				]
			});
					
			Ext.apply(this, config);
			wep.panel.superclass.initComponent.call(this);		
			
			
		},
		
		
	}),
	
	get_panel: function(modul, id_cont) {
		//Очищаем элемент, в котором хотим нарисовать дерево
		Ext.get(id_cont).update('');

		jsfiles = [
			wep.path['extjs'] + 'ux/CheckColumn.js'
		];

		wep.include_js(jsfiles, function() {
	
			Ext.Ajax.request({
				url: '_wep/index.php?_view=listcol&_modul=' + modul,
				success: function(result, textStatus) {
					
					var fm = Ext.form;

					data = Ext.util.JSON.decode(result.responseText);	

					var columns = data['columns'];
					var fields = data['fields'];
					var grid = this.grid;

					Ext.each(columns, function(value, index)
					{
						if (columns[index].editor != undefined && Ext.isString(columns[index].editor))
						{
							eval('columns[index].editor = ' + columns[index].editor + ';');
						}
					});
					
					var panel = new wep.panel({
						items: [{				
							title: 'Модуль ' + modul,
						}],
						columns: columns,
						fields: fields,
						grid: grid,
						obj: this					
					});
				},
				failure: function() {
					Ext.Msg.alert('Ошибка', 'Произошла ошибка');
				}
			});
		});

	},
		
	// получает дерево элементов из модуля modul
	// и помещает их в элемент с id	= id_cont
	get_tree: function(modul, id_cont) 
	{
		//Очищаем элемент, в котором хотим нарисовать дерево
		Ext.get(id_cont).update('');

		// css
		wep.include_css('treegrid', wep.path['extjs'] + 'ux/treegrid/treegrid.css');
		
		// js
		var jsfiles = [
			wep.path['extjs'] + 'ux/treegrid/TreeGridSorter.js',
			wep.path['extjs'] + 'ux/treegrid/TreeGridColumnResizer.js',
			wep.path['extjs'] + 'ux/treegrid/TreeGridNodeUI.js',
			wep.path['extjs'] + 'ux/treegrid/TreeGridLoader.js',
			wep.path['extjs'] + 'ux/treegrid/TreeGridColumns.js',
			
			wep.path['extjs'] + 'ux/treegrid/TreeGrid.js',
		];

		wep.include_js(jsfiles, function()
		{
			Ext.QuickTips.init();


			Ext.Ajax.request({
				url: '_wep/index.php?_view=listcol&_modul=' + modul,
				success: function(result, textStatus) {
					data = Ext.util.JSON.decode(result.responseText);					
					var tree = new Ext.ux.tree.TreeGrid({
						title: 'Модуль ' + modul,
//						width: 500,
						width: 1000,
						height: 300,
						renderTo: Ext.getDom(id_cont),
						enableDD: true,
						columns: data,
						requestMethod: 'GET',
						dataUrl: '_wep/index.php?_view=list&_modul=' + modul
					});	 
					
					tree.getSelectionModel().on('selectionchange', function(sel, node) {
						if (node) {
							alert(node.id);
						}
					});
				},
				failure: function(result, textStatus) {
					Ext.Msg.alert('Ошибка', 'Произошла ошибка');
				}
			});
						
		});
		
	},

	// меняет стиль ссылок при нажатии на них
	change_menu_item_style: function(obj)
	{
		if (wep.last_checked_link != undefined)
		{
			Ext.get(wep.last_checked_link).parent().removeClass('selected');
		}
		wep.last_checked_link = obj;
		Ext.get(obj).parent().addClass('selected');
	},

});


// пустая картинка для верстки
Ext.BLANK_IMAGE_URL = wep.path.extjs + 'resources/images/default/s.gif';


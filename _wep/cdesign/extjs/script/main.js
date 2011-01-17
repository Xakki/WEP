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
	
	// пути
	path: {
		extjs: '_design/_script/extjs/',
		cscript: '_wep/cdesign/extjs/script/'
	},
	
	included_components: {},

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
			wep.modul = {
				title: this.innerHTML,
				cn: wep.GET['_modul']
			}
			
			//Очищаем элемент, в котором хотим нарисовать дерево
			Ext.get(wep.main_cont).update('');
			
			jsfiles = [
				wep.path['extjs'] + 'ux/CheckColumn.js',

				//
				wep.path['extjs'] + 'ux/treegrid/TreeGridSorter.js',
				wep.path['extjs'] + 'ux/treegrid/TreeGridColumnResizer.js',
				wep.path['extjs'] + 'ux/treegrid/TreeGridNodeUI.js',
				wep.path['extjs'] + 'ux/treegrid/TreeGridLoader.js',
				wep.path['extjs'] + 'ux/treegrid/TreeGridColumns.js',

				wep.path['extjs'] + 'ux/treegrid/TreeGrid.js',
			];

			wep.include_js(jsfiles, function() {
	
				Ext.Ajax.request({
					url: '_wep/index.php?_view=listcol&_modul=' + wep.modul.cn,
					success: function(result, textStatus) {
						
						var data = Ext.util.JSON.decode(result.responseText);

						var columns = data['columns'];
						var fields = data['fields'];					
						
						var panel = new wep.panel({
							id: 'main_panel',
							layout: 'accordion',
							renderTo: wep.main_cont
						});

						// удаляем предыдущую форму, если она есть
						var edit_form = Ext.getCmp('edit_form');
						if (Ext.isObject(edit_form))
						{
							Ext.get(wep.edit_form_cont).update('');
							edit_form.destroy();
						}
						
						var grid = new wep.grid({
							id: "modul_grid",
							columns: columns,
							fields: fields,
							title: 'Модуль ' + wep.modul.title,
							hideParent: false,
							url: '_wep/index.php?_view=list&_modul=' + wep.modul.cn
						});
						

						/*
						var tree = new Ext.ux.tree.TreeGrid({
							title: 'Модуль ' + wep.modul.title,
							//						width: 500,
							width: 1000,
							height: 300,
							enableDD: true,
							columns: columns,
							requestMethod: 'GET',
							dataUrl: '_wep/index.php?_view=list&_modul=' + wep.modul.cn
						});

						tree.getSelectionModel().on('selectionchange', function(sel, node) {
							if (node) {
								alert(node.id);
							}
						});
						*/

						
						panel.add(grid);
						panel.doLayout();
						
						
					},
					failure: function() {
						Ext.Msg.alert('Ошибка', 'Произошла ошибка');
					}
				});
			});			
		}
			
		currentnode.stopEvent(); // чтобы не переходило по ссылке
	},
	
	panel: Ext.extend(Ext.Panel, {}),

	grid: Ext.extend(Ext.grid.EditorGridPanel, {
		initComponent: function(config) {	
			
			var columns = this.columns;
			
			
			var store = new Ext.data.Store({
				url: this.url,

				reader: new Ext.data.JsonReader({
					fields: this.fields
				})
			});		
			
			var fm = Ext.form;
			
			Ext.each(columns, function(value, index)
			{	
				var obj = this;
				
				if (Ext.isString(columns[index].editor))
				{
					eval('columns[index].editor = ' + columns[index].editor + ';');
				}
				else if (Ext.isString(columns[index].handler))
				{
					eval('columns[index].handler = ' + columns[index].handler + ';');
				}
			},
			this);

			var cm = new Ext.grid.ColumnModel({
				// specify any defaults for each column
				defaults: {
					sortable: true // columns are not sortable by default           
				},
				columns: columns
			});
			
			function onSave() 
			{
				alert('fdsfd');
			}
			
			function onDelete(btn)
			{
				if (btn == 'yes')
				{
					this.stopEditing();
					var index = this.getSelectionModel().getSelectedCell();
					if (!index) {
						return false;
					}
					var rec = this.store.getAt(index[0]);
					this.store.remove(rec);
				}
				return true;
			}

			function showChildren(grid, child)
			{
				var SelectionModel = grid.getSelectionModel();

				var child = Ext.util.JSON.decode(child);

				var id = SelectionModel.selection.record.data.id;
				
				alert(SelectionModel.selection.record.data.id + " " + child.cl + " " + child.title);

//				http://partner.i/_wep/index.php?_view=list&_modul=pg&pg_id=401

				Ext.Ajax.request({
					url: '_wep/index.php?_view=listcol&_modul=' + wep.modul.cn,
					success: function(result, textStatus) {

						var data = Ext.util.JSON.decode(result.responseText);

						var columns = data['columns'];
						var fields = data['fields'];

						// удаляем предыдущую форму, если она есть
						var edit_form = Ext.getCmp('edit_form');
						if (Ext.isObject(edit_form))
						{
							Ext.get(wep.edit_form_cont).update('');
							edit_form.destroy();
						}

						var child_grid_id = 'child_' + child.cl + '_grid';
						var child_grid = Ext.getCmp(child_grid_id);
						if (Ext.isObject(child_grid))
						{
							child_grid.destroy();
						}

						var url = 'http://partner.i/_wep/index.php?_view=list&_modul=' + wep.modul.cn + '&' + wep.modul.cn + '_id=' + id;

						if (child.cl != wep.modul.cn)
						{
							url += '&' + wep.modul.cn + '_ch=' + child.cl;
						}

						var child_grid = new wep.grid({
							id: child_grid_id,
							columns: columns,
							fields: fields,
							title: 'Подмодуль ' + child.title,
							hideParent: false,
							url: url
						});

						var panel = Ext.getCmp('main_panel');

						panel.add(child_grid);
						panel.doLayout();


					},
					failure: function() {
						Ext.Msg.alert('Ошибка', 'Произошла ошибка');
					}
				});

			}

			function showForm(grid)
			{
				//Очищаем элемент, в котором хотим нарисовать форму
	//			Ext.get(wep.edit_form_cont).update('');
				
				var SelectionModel = grid.getSelectionModel();	
		
		
				Ext.Ajax.request({
					url: '_wep/index.php?_view=list&_modul=' + wep.modul.cn + '&' + wep.modul.cn + '_id=' + SelectionModel.selection.record.data.id + '&_type=edit',
					success: function(result, textStatus) {
						
						data = Ext.util.JSON.decode(result.responseText);	
						
						var items = data;
		
						/////////
						// удалить

						// example of custom renderer function
						function italic(value){
							return '<i>' + value + '</i>';
						}

						// example of custom renderer function
						function change(val){
							if(val > 0){
								return '<span style="color:green;">' + val + '</span>';
							}else if(val < 0){
								return '<span style="color:red;">' + val + '</span>';
							}
							return val;
						}
						// example of custom renderer function
						function pctChange(val){
							if(val > 0){
								return '<span style="color:green;">' + val + '%</span>';
							}else if(val < 0){
								return '<span style="color:red;">' + val + '%</span>';
							}
							return val;
						}

						// render rating as "A", "B" or "C" depending upon numeric value.
						function rating(v) {
							if (v == 0) return "A"
							if (v == 1) return "B"
							if (v == 2) return "C"
						}
						
						// удалить			
						////////
				

						// удаляем предыдущую форму, если она есть
						var edit_form = Ext.getCmp('edit_form');
						if (Ext.isObject(edit_form))
						{
							Ext.get(wep.edit_form_cont).update('');
							edit_form.destroy();
						}

						var form = new wep.form({
							id: 'edit_form',
							title: SelectionModel.selection.record.data.id,
							renderTo: wep.edit_form_cont,

							items: [{
		//						columnWidth: 0.4,
								xtype: 'fieldset',
								labelWidth: 200,
								title:'Редактирование',
								defaults: {width: 500, border:false},    // Default config options for child items
								defaultType: 'textfield',
								autoHeight: true,
								bodyStyle: Ext.isIE ? 'padding:0 0 5px 15px;' : 'padding:10px 15px;',
								border: false,
								style: {
									"margin-left": "10px", // when you add custom margin in IE 6...
									"margin-right": Ext.isIE6 ? (Ext.isStrict ? "-10px" : "-13px") : "0"  // you have to adjust for it somewhere else
								},
								items: items
							}]
						});

	

					}
				});
			}
			
			// загружаются данные в таблицу
			store.load();
			
			Ext.apply(this, {
				xtype: 'grid',
				border: true,
				store: store,
				cm: cm,
				autoHeight : true,
				enableColumnResize: true,
				minColumnWidth : 50,
				enableHdMenu : false

			});

			Ext.apply(this, config);
			wep.grid.superclass.initComponent.call(this);		
		
			this.getSelectionModel().on('selectionchange', function(sel, node) {
				if (node) {
//					alert('fdsfdsfsdfd');
//					this.ownerCt.add({title:'cdcddcd'});
//					this.ownerCt.doLayout();
				}
			},
			this);		
		}
	}),

	form: Ext.extend(Ext.FormPanel, {
		frame: true,
		labelAlign: 'left',
		bodyStyle:'padding:5px',
		layout: 'column'    // Specifies that the items will now be arranged in columns
	}),
	
	// Не задействовано!
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
				url: '_wep/index.php?_view=listcol&_modul=' + modul.cn,
				success: function(result, textStatus) {
					data = Ext.util.JSON.decode(result.responseText);					
					var tree = new Ext.ux.tree.TreeGrid({
						title: 'Модуль ' + modul.title,
//						width: 500,
						width: 1000,
						height: 300,
						renderTo: Ext.getDom(id_cont),
						enableDD: true,
						columns: data,
						requestMethod: 'GET',
						dataUrl: '_wep/index.php?_view=list&_modul=' + modul.cn
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

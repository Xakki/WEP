Ext.onReady(function() {  
	Ext.select('a').on('click', wep.click_handler);
	// инициализируем объект wep
	wep.init();
});

// id главного контейнера
var main_cont = 'modulsforms';
var edit_form_cont = 'editform';


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
			wep.modul = wep.GET['_modul'];
			
			//Очищаем элемент, в котором хотим нарисовать дерево
			Ext.get(main_cont).update('');
			
			jsfiles = [
				wep.path['extjs'] + 'ux/CheckColumn.js'
			];

			wep.include_js(jsfiles, function() {
	
				Ext.Ajax.request({
					url: '_wep/index.php?_view=listcol&_modul=' + wep.modul,
					success: function(result, textStatus) {
						
						data = Ext.util.JSON.decode(result.responseText);	

						var columns = data['columns'];
						var fields = data['fields'];					
						
						var panel = new wep.panel({});	
						var grid = new wep.grid({
							columns: columns,
							fields: fields,
							title: 'Модуль ' + wep.modul,
							hideParent: false,
						});
						
						panel.add(grid);
						panel.doLayout();
						
						
					},
					failure: function() {
						Ext.Msg.alert('Ошибка', 'Произошла ошибка');
					}
				});
			});
						
			
		}
			
		currentnode.stopEvent();
		return false;
	},
	
	form: Ext.extend(Ext.FormPanel, {
		frame: true,
		labelAlign: 'left',
		bodyStyle:'padding:5px',
		renderTo: edit_form_cont,
		layout: 'column',    // Specifies that the items will now be arranged in columns
	}),
	
	grid: Ext.extend(Ext.grid.EditorGridPanel, {
		initComponent: function(config) {	
			
			var columns = this.columns;
			
			
			var store = new Ext.data.Store({
				// destroy the store if the grid is destroyed
				autoDestroy: true,

				// load remote data using HTTP
				url: '_wep/index.php?_view=list&_modul=' + wep.modul,

				reader: new Ext.data.JsonReader({
					fields: this.fields
				}),

				sortInfo: {field:'id', direction:'ASC'}
			});		
			
			var fm = Ext.form;
			
			Ext.each(columns, function(value, index)
			{	
				var obj = this;
				
				if (columns[index].editor != undefined && Ext.isString(columns[index].editor))
				{
					eval('columns[index].editor = ' + columns[index].editor + ';');
				}
				else if (columns[index].handler != undefined && Ext.isString(columns[index].handler))
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
			};
			
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
			};

			function showForm(grid)
			{
				//Очищаем элемент, в котором хотим нарисовать форму
				Ext.get(edit_form_cont).update('');
				
				var SelectionModel = grid.getSelectionModel();
				
				
				
				
// начало				
				
				//   Define the Grid data and create the Grid
    var myData = [
        ['3m Co',71.72,0.02,0.03,'9/1 12:00am'],
        ['Alcoa Inc',29.01,0.42,1.47,'9/1 12:00am'],
        ['Altria Group Inc',83.81,0.28,0.34,'9/1 12:00am'],
        ['American Express Company',52.55,0.01,0.02,'9/1 12:00am'],
        ['American International Group, Inc.',64.13,0.31,0.49,'9/1 12:00am'],
        ['AT&T Inc.',31.61,-0.48,-1.54,'9/1 12:00am'],
        ['Boeing Co.',75.43,0.53,0.71,'9/1 12:00am'],
        ['Caterpillar Inc.',67.27,0.92,1.39,'9/1 12:00am'],
        ['Citigroup, Inc.',49.37,0.02,0.04,'9/1 12:00am'],
        ['E.I. du Pont de Nemours and Company',40.48,0.51,1.28,'9/1 12:00am'],
        ['Exxon Mobil Corp',68.1,-0.43,-0.64,'9/1 12:00am'],
        ['General Electric Company',34.14,-0.08,-0.23,'9/1 12:00am'],
        ['General Motors Corporation',30.27,1.09,3.74,'9/1 12:00am'],
        ['Hewlett-Packard Co.',36.53,-0.03,-0.08,'9/1 12:00am'],
        ['Honeywell Intl Inc',38.77,0.05,0.13,'9/1 12:00am'],
        ['Intel Corporation',19.88,0.31,1.58,'9/1 12:00am'],
        ['International Business Machines',81.41,0.44,0.54,'9/1 12:00am'],
        ['Johnson & Johnson',64.72,0.06,0.09,'9/1 12:00am'],
        ['JP Morgan & Chase & Co',45.73,0.07,0.15,'9/1 12:00am'],
        ['McDonald\'s Corporation',36.76,0.86,2.40,'9/1 12:00am'],
        ['Merck & Co., Inc.',40.96,0.41,1.01,'9/1 12:00am'],
        ['Microsoft Corporation',25.84,0.14,0.54,'9/1 12:00am'],
        ['Pfizer Inc',27.96,0.4,1.45,'9/1 12:00am'],
        ['The Coca-Cola Company',45.07,0.26,0.58,'9/1 12:00am'],
        ['The Home Depot, Inc.',34.64,0.35,1.02,'9/1 12:00am'],
        ['The Procter & Gamble Company',61.91,0.01,0.02,'9/1 12:00am'],
        ['United Technologies Corporation',63.26,0.55,0.88,'9/1 12:00am'],
        ['Verizon Communications',35.57,0.39,1.11,'9/1 12:00am'],
        ['Wal-Mart Stores, Inc.',45.45,0.73,1.63,'9/1 12:00am']
    ];

				
    
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

    
     


// конец				


				var colModel = new Ext.grid.ColumnModel([
					{id:'company',header: "Company", width: 160, sortable: true, locked:false, dataIndex: 'company'},
					{header: "Price", width: 55, sortable: true, renderer: Ext.util.Format.usMoney, dataIndex: 'price'},
					{header: "Change", width: 55, sortable: true, renderer: change, dataIndex: 'change'},
					{header: "% Change", width: 65, sortable: true, renderer: pctChange, dataIndex: 'pctChange'},
					{header: "Last Updated", width: 80, sortable: true, renderer: Ext.util.Format.dateRenderer('m/d/Y'), dataIndex: 'lastChange'},
					{header: "Rating", width: 40, sortable: true, renderer: rating, dataIndex: 'rating'}
				]);
				
				

				var fields = [
					{name: 'company'},
					{name: 'price', type: 'float'},
					{name: 'change', type: 'float'},
					{name: 'pctChange', type: 'float'},
					{name: 'lastChange', type: 'date', dateFormat: 'n/j h:ia'},

					//          Rating dependent upon performance 0 = best, 2 = worst
					{
						name: 'rating', type: 'int', convert: function(v, rec) {
							if (rec[3] < 0) return 2;
							if (rec[3] < 1) return 1;
							return 0;
						}
					}
				];
    
				var store = new Ext.data.Store({
					url: 'form.php',
					reader: new Ext.data.JsonReader({fields: fields})
				});
				store.loadData(myData);


				
				var form = new wep.form({
					title: SelectionModel.selection.record.data.id,
				
					items: [{
						columnWidth: 0.4,
						xtype: 'fieldset',
						labelWidth: 120,
						title:'Company details',
						defaults: {width: 140, border:false},    // Default config options for child items
						defaultType: 'textfield',
						autoHeight: true,
						bodyStyle: Ext.isIE ? 'padding:0 0 5px 15px;' : 'padding:10px 15px;',
						border: false,
						style: {
							"margin-left": "10px", // when you add custom margin in IE 6...
							"margin-right": Ext.isIE6 ? (Ext.isStrict ? "-10px" : "-13px") : "0"  // you have to adjust for it somewhere else
						},
						items: [
							{
								fieldLabel: 'Name',
								name: 'company'
							},{
								fieldLabel: 'Price',
								name: 'price'
							},{
								fieldLabel: '% Change',
								name: 'pctChange'
							},{
								xtype: 'datefield',
								fieldLabel: 'Last Updated',
								name: 'lastChange'
							}, {
								xtype: 'radiogroup',
								columns: 'auto',
								fieldLabel: 'Rating',
								name: 'rating',
								// A radio group: A setValue on any of the following 'radio' inputs using the numeric
								// 'rating' field checks the radio instance which has the matching inputValue.
								items: [{
									inputValue: '0',
									boxLabel: 'A'
								}, {
									inputValue: '1',
									boxLabel: 'B'
								}, {
									inputValue: '2',
									boxLabel: 'C'
								}]
							}
						]
					}],

				
				});
			}
			
			// manually trigger the data store load
			store.load();
			
			Ext.apply(this, {
	//			xtype: 'grid',
				border: true,
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
	
	panel: Ext.extend(Ext.Panel, {
		region:'west',
		itemId: 'modul_panel',
		renderTo: 'modulsforms',
		
		initComponent: function(config) {		
			
			Ext.apply(this, {
				layout: 'accordion',
				margins:'5 0 5 5',
				split:true,
				height: 600,
				region: 'east',
				items: []
			});
					
			Ext.apply(this, config);
			wep.panel.superclass.initComponent.call(this);	
		},
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

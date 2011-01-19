/*!
 * Ext JS Library 3.3.1
 * Copyright(c) 2006-2010 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */
/**
 * @class Ext.ux.tree.TreeGrid
 * @extends Ext.tree.TreePanel
 * 
 * @xtype treegrid
 */
Ext.ux.tree.TreeGrid = Ext.extend(Ext.tree.TreePanel, {
    rootVisible : false,
    useArrows : true,
    lines : false,
    borderWidth : Ext.isBorderBox ? 0 : 2, // the combined left/right border for each cell
    cls : 'x-treegrid',

    columnResize : true,
    enableSort : true,
    reserveScrollOffset : true,
    enableHdMenu : true,
    
    columnsText : 'Columns',

    initComponent : function() {
        if(!this.root) {
            this.root = new Ext.tree.AsyncTreeNode({text: 'Root'});
        }

        // initialize the loader
        var l = this.loader;
        if(!l){
            l = new Ext.ux.tree.TreeGridLoader({
                dataUrl: this.dataUrl,
                requestMethod: this.requestMethod
            });
        }else if(Ext.isObject(l) && !l.load){
            l = new Ext.ux.tree.TreeGridLoader(l);
        }
        this.loader = l;

		this.tbar = this.buildTopToolbar();
     
                            
        Ext.ux.tree.TreeGrid.superclass.initComponent.call(this);


		this.getSelectionModel().on('selectionchange', function(sel, node) {
			if (node) {
				var tbar = this.getTopToolbar();
				tbar.items.items[1].enable();
				tbar.items.items[2].enable();
			}
		},
		this);

   
        this.initColumns();
  
        if(this.enableSort) {
            this.treeGridSorter = new Ext.ux.tree.TreeGridSorter(this, this.enableSort);
        }
        
        if(this.columnResize){
            this.colResizer = new Ext.tree.ColumnResizer(this.columnResize);
            this.colResizer.init(this);
        }
        
        var c = this.columns;
        if(!this.internalTpl){                                
            this.internalTpl = new Ext.XTemplate(
                '<div class="x-grid3-header">',
                    '<div class="x-treegrid-header-inner">',
                        '<div class="x-grid3-header-offset">',
                            '<table style="table-layout: fixed;" cellspacing="0" cellpadding="0" border="0"><colgroup><tpl for="columns"><col /></tpl></colgroup>',
                            '<thead><tr class="x-grid3-hd-row">',
                            '<tpl for="columns">',
                            '<td class="x-grid3-hd x-grid3-cell x-treegrid-hd" style="text-align: {align};" id="', this.id, '-xlhd-{#}">',
                                '<div class="x-grid3-hd-inner x-treegrid-hd-inner" unselectable="on">',
                                     this.enableHdMenu ? '<a class="x-grid3-hd-btn" href="#"></a>' : '',
                                     '{header}<img class="x-grid3-sort-icon" src="', Ext.BLANK_IMAGE_URL, '" />',
                                 '</div>',
                            '</td></tpl>',
                            '</tr></thead>',
                        '</table>',
                    '</div></div>',
                '</div>',
                '<div class="x-treegrid-root-node">',
                    '<table class="x-treegrid-root-table" cellpadding="0" cellspacing="0" style="table-layout: fixed;"></table>',
                '</div>'
            );
        }
        
        if(!this.colgroupTpl) {
            this.colgroupTpl = new Ext.XTemplate(
                '<colgroup><tpl for="columns"><col style="width: {width}px"/></tpl></colgroup>'
            );
        }
		
    },

	buildTopToolbar : function() {
        var tools = [{
            //text: 'Add',
            iconCls: 'silk-icon-add',
            handler: function() {
				this.showForm('add');
			},
            scope: this
        }, {
            //text: 'Add',
            iconCls: 'silk-icon-edit',
            handler: function() {
				this.showForm('edit');
			},
            scope: this,
			disabled: true
        }, {
            //text: 'Add',
            iconCls: 'silk-icon-delete',
            handler: this.onDelete,
            scope: this,
			disabled: true
        }];

		if (!Ext.isEmpty(this.children)) {
			tools.push('Подмодули - ');
			Ext.each(this.children, function(value, index) {
				tools.push({
					text:value.header,
					handler: function() {
						this.showChild(value);
					},
					scope: this
				});
			},
			this)
		}

		return tools;
	},

	showChild : function(child) {
		
		var index = this.getSelectionModel().getSelectedNode();
		if (!index) {
			return false;
		}

		var id = index.id;

		Ext.Ajax.request({
			url: '_wep/index.php?_view=listcol&_modul=' + wep.modul.cn,
			success: function(result, textStatus) {

				var data = Ext.util.JSON.decode(result.responseText);

				var columns = data['columns'];
				var fields = data['fields'];
				var children = data['children'];

				// удаляем предыдущую форму, если она есть
//				var edit_form = Ext.getCmp('edit_form');
//				if (Ext.isObject(edit_form))
//				{
//					Ext.get(wep.edit_form_cont).update('');
//					edit_form.destroy();
//				}

				var child_tree_id = 'child_' + child.cl + '_tree';
//				var child_tree = Ext.getCmp(child_tree_id);
//				if (Ext.isObject(child_tree))
//				{
//					child_tree.destroy();
//				}

				var url = 'http://partner.i/_wep/index.php?_view=list&_modul=' + wep.modul.cn + '&' + wep.modul.cn + '_id=' + id;

				if (child.cl != wep.modul.cn)
				{
					url += '&' + wep.modul.cn + '_ch=' + child.cl;
				}

				/*
				var child_grid = new wep.grid({
					id: child_grid_id,
					columns: columns,
					fields: fields,
					title: 'Подмодуль ' + child.header,
					hideParent: false,
					url: url
				});
				*/

			   Ext.getCmp('main_tree').hide();

			   wep.breadcrumbs.add({title: child.header, component_id: child_tree_id});

			   var child_tree = new Ext.ux.tree.TreeGrid({
					id: child_tree_id,
					title: 'Подмодуль ' + child.header,
					width: 1000,
					height: 300,
					enableDD: true,
					renderTo: wep.main_cont,
					columns: columns,
					children: children,
					requestMethod: 'GET',
					dataUrl: url,
					onDestroy: function() {
						alert('Уничтожается ' + this.title);
					}
				});



			},
			failure: function() {
				Ext.Msg.alert('Ошибка', 'Произошла ошибка');
			}
		});

	},

	onAdd : function(btn, ev) {
		alert(btn);
	},

	onEdit : function(btn, ev) {
		alert('ред');
	},

	onDelete : function(btn, ev) {

		var index = this.getSelectionModel().getSelectedNode();
		if (!index) {
			return false;
		}

		var msg = "Вы действительно хотите удалить данную запись?";
		if (index.attributes.name != undefined)
		{
			msg += " (" + index.attributes.name + ")";
		}
		else if (index.attributes.id != undefined)
		{
			msg += " (" + index.attributes.id + ")";
		}
		
		Ext.Msg.confirm("Удаление записи",  msg, function(btn) {
			if (btn == 'yes')
			{
				Ext.Ajax.request({
					url: '_wep/index.php?_view=list&_modul=' + wep.modul.cn + '&' + wep.modul.cn + '_id=' + index.id + '&_type=del',
					success: function(result, textStatus) {
						index.remove();
						Ext.Msg.alert('Сообщение', 'Удаление произошло успешно');
					}
				});
			}
		}, this);

		return true;
	},

	showForm : function(action)
	{
		if (action == 'add')
		{
			var url = '_wep/index.php?_view=list&_modul=' + wep.modul.cn + '&_type=add';
			var title = 'Добавление новой записи';
		}
		else
		{
			var index = this.getSelectionModel().getSelectedNode();
			if (!index) {
				return false;
			}
			var url = '_wep/index.php?_view=list&_modul=' + wep.modul.cn + '&' + wep.modul.cn + '_id=' + index.id + '&_type=edit'
			var title = 'Редактирование - ';
			if (index.attributes.name)
			{
				title += index.attributes.name;
			}
			else
			{
				title += index.id;
			}

		}

		Ext.Ajax.request({
			url: url,
			success: function(result, textStatus) {

				var data = Ext.util.JSON.decode(result.responseText);

				var items = data;

				Ext.each(items, function(value, index) {
					Ext.iterate(value, function(prop, val) {
						if (!Ext.isEmpty(val))
						{
							if (Ext.isDefined(val.eval))
							{
								eval("value[prop] = " + val.eval + ";");
							}
						}
					});

				});

				// удаляем предыдущую форму, если она есть
				var edit_form_id = 'edit_form';
//				var edit_form = Ext.getCmp(edit_form_id);
//				if (Ext.isObject(edit_form))
//				{
//					Ext.get(wep.edit_form_cont).update('');
//					edit_form.destroy();
//				}

				wep.breadcrumbs.add({title: title, component_id: edit_form_id, dom_id: wep.edit_form_cont});

				var form = new wep.form({
					id: edit_form_id,
					title: title,
					renderTo: wep.edit_form_cont,
					url:'save-form.php',
					buttons: [{
						text: 'Save'
					},{
						text: 'Cancel'
					}],


					items: [{
//						columnWidth: 0.4,
						xtype: 'fieldset',
						labelWidth: 200,
						title:'Редактирование',
						defaults: {width: 500, border:false},    // Default config options for child items
//						defaultType: 'textfieldf',
						autoHeight: true,
						bodyStyle: Ext.isIE ? 'padding:0 0 5px 15px;' : 'padding:10px 15px;',
						border: false,
						style: {
							"margin-left": "10px", // when you add custom margin in IE 6...
							"margin-right": Ext.isIE6 ? (Ext.isStrict ? "-10px" : "-13px") : "0"  // you have to adjust for it somewhere else
						},
						items: items
					}],

					onDestroy: function() {
						alert(title + ' уничтожается');
					}
				});
			}
		});

		return true;
	},

    initColumns : function() {
        var cs = this.columns,
            len = cs.length, 
            columns = [],
            i, c;

        for(i = 0; i < len; i++){
            c = cs[i];
            if(!c.isColumn) {
                c.xtype = c.xtype ? (/^tg/.test(c.xtype) ? c.xtype : 'tg' + c.xtype) : 'tgcolumn';
                c = Ext.create(c);
            }
            c.init(this);
            columns.push(c);
            
            if(this.enableSort !== false && c.sortable !== false) {
                c.sortable = true;
                this.enableSort = true;
            }
        }

        this.columns = columns;
    },

    onRender : function(){
        Ext.tree.TreePanel.superclass.onRender.apply(this, arguments);

        this.el.addClass('x-treegrid');
        
        this.outerCt = this.body.createChild({
            cls:'x-tree-root-ct x-treegrid-ct ' + (this.useArrows ? 'x-tree-arrows' : this.lines ? 'x-tree-lines' : 'x-tree-no-lines')
        });
        
        this.internalTpl.overwrite(this.outerCt, {columns: this.columns});
        
        this.mainHd = Ext.get(this.outerCt.dom.firstChild);
        this.innerHd = Ext.get(this.mainHd.dom.firstChild);
        this.innerBody = Ext.get(this.outerCt.dom.lastChild);
        this.innerCt = Ext.get(this.innerBody.dom.firstChild);
        
        this.colgroupTpl.insertFirst(this.innerCt, {columns: this.columns});
        
        if(this.hideHeaders){
            this.el.child('.x-grid3-header').setDisplayed('none');
        }
        else if(this.enableHdMenu !== false){
            this.hmenu = new Ext.menu.Menu({id: this.id + '-hctx'});
            if(this.enableColumnHide !== false){
                this.colMenu = new Ext.menu.Menu({id: this.id + '-hcols-menu'});
                this.colMenu.on({
                    scope: this,
                    beforeshow: this.beforeColMenuShow,
                    itemclick: this.handleHdMenuClick
                });
                this.hmenu.add({
                    itemId:'columns',
                    hideOnClick: false,
                    text: this.columnsText,
                    menu: this.colMenu,
                    iconCls: 'x-cols-icon'
                });
            }
            this.hmenu.on('itemclick', this.handleHdMenuClick, this);
        }
    },

    setRootNode : function(node){
        node.attributes.uiProvider = Ext.ux.tree.TreeGridRootNodeUI;        
        node = Ext.ux.tree.TreeGrid.superclass.setRootNode.call(this, node);
        if(this.innerCt) {
            this.colgroupTpl.insertFirst(this.innerCt, {columns: this.columns});
        }
        return node;
    },
    
    clearInnerCt : function(){
        if(Ext.isIE){
            var dom = this.innerCt.dom;
            while(dom.firstChild){
                dom.removeChild(dom.firstChild);
            }
        }else{
            Ext.ux.tree.TreeGrid.superclass.clearInnerCt.call(this);
        }
    },
    
    initEvents : function() {
        Ext.ux.tree.TreeGrid.superclass.initEvents.apply(this, arguments);

        this.mon(this.innerBody, 'scroll', this.syncScroll, this);
        this.mon(this.innerHd, 'click', this.handleHdDown, this);
        this.mon(this.mainHd, {
            scope: this,
            mouseover: this.handleHdOver,
            mouseout: this.handleHdOut
        });
    },
    
    onResize : function(w, h) {
        Ext.ux.tree.TreeGrid.superclass.onResize.apply(this, arguments);
        
        var bd = this.innerBody.dom;
        var hd = this.innerHd.dom;

        if(!bd){
            return;
        }

        if(Ext.isNumber(h)){
            bd.style.height = this.body.getHeight(true) - hd.offsetHeight + 'px';
        }

        if(Ext.isNumber(w)){                        
            var sw = Ext.num(this.scrollOffset, Ext.getScrollBarWidth());
            if(this.reserveScrollOffset || ((bd.offsetWidth - bd.clientWidth) > 10)){
                this.setScrollOffset(sw);
            }else{
                var me = this;
                setTimeout(function(){
                    me.setScrollOffset(bd.offsetWidth - bd.clientWidth > 10 ? sw : 0);
                }, 10);
            }
        }
    },

    updateColumnWidths : function() {
        var cols = this.columns,
            colCount = cols.length,
            groups = this.outerCt.query('colgroup'),
            groupCount = groups.length,
            c, g, i, j;

        for(i = 0; i<colCount; i++) {
            c = cols[i];
            for(j = 0; j<groupCount; j++) {
                g = groups[j];
                g.childNodes[i].style.width = (c.hidden ? 0 : c.width) + 'px';
            }
        }
        
        for(i = 0, groups = this.innerHd.query('td'), len = groups.length; i<len; i++) {
            c = Ext.fly(groups[i]);
            if(cols[i] && cols[i].hidden) {
                c.addClass('x-treegrid-hd-hidden');
            }
            else {
                c.removeClass('x-treegrid-hd-hidden');
            }
        }

        var tcw = this.getTotalColumnWidth();                        
        Ext.fly(this.innerHd.dom.firstChild).setWidth(tcw + (this.scrollOffset || 0));
        this.outerCt.select('table').setWidth(tcw);
        this.syncHeaderScroll();    
    },
                    
    getVisibleColumns : function() {
        var columns = [],
            cs = this.columns,
            len = cs.length,
            i;
            
        for(i = 0; i<len; i++) {
            if(!cs[i].hidden) {
                columns.push(cs[i]);
            }
        }        
        return columns;
    },

    getTotalColumnWidth : function() {
        var total = 0;
        for(var i = 0, cs = this.getVisibleColumns(), len = cs.length; i<len; i++) {
            total += cs[i].width;
        }
        return total;
    },

    setScrollOffset : function(scrollOffset) {
        this.scrollOffset = scrollOffset;                        
        this.updateColumnWidths();
    },

    // private
    handleHdDown : function(e, t){
        var hd = e.getTarget('.x-treegrid-hd');

        if(hd && Ext.fly(t).hasClass('x-grid3-hd-btn')){
            var ms = this.hmenu.items,
                cs = this.columns,
                index = this.findHeaderIndex(hd),
                c = cs[index],
                sort = c.sortable;
                
            e.stopEvent();
            Ext.fly(hd).addClass('x-grid3-hd-menu-open');
            this.hdCtxIndex = index;
            
            this.fireEvent('headerbuttonclick', ms, c, hd, index);
            
            this.hmenu.on('hide', function(){
                Ext.fly(hd).removeClass('x-grid3-hd-menu-open');
            }, this, {single:true});
            
            this.hmenu.show(t, 'tl-bl?');
        }
        else if(hd) {
            var index = this.findHeaderIndex(hd);
            this.fireEvent('headerclick', this.columns[index], hd, index);
        }
    },

    // private
    handleHdOver : function(e, t){                    
        var hd = e.getTarget('.x-treegrid-hd');                        
        if(hd && !this.headersDisabled){
            index = this.findHeaderIndex(hd);
            this.activeHdRef = t;
            this.activeHdIndex = index;
            var el = Ext.get(hd);
            this.activeHdRegion = el.getRegion();
            el.addClass('x-grid3-hd-over');
            this.activeHdBtn = el.child('.x-grid3-hd-btn');
            if(this.activeHdBtn){
                this.activeHdBtn.dom.style.height = (hd.firstChild.offsetHeight-1)+'px';
            }
        }
    },
    
    // private
    handleHdOut : function(e, t){
        var hd = e.getTarget('.x-treegrid-hd');
        if(hd && (!Ext.isIE || !e.within(hd, true))){
            this.activeHdRef = null;
            Ext.fly(hd).removeClass('x-grid3-hd-over');
            hd.style.cursor = '';
        }
    },
                    
    findHeaderIndex : function(hd){
        hd = hd.dom || hd;
        var cs = hd.parentNode.childNodes;
        for(var i = 0, c; c = cs[i]; i++){
            if(c == hd){
                return i;
            }
        }
        return -1;
    },
    
    // private
    beforeColMenuShow : function(){
        var cols = this.columns,  
            colCount = cols.length,
            i, c;                        
        this.colMenu.removeAll();                    
        for(i = 1; i < colCount; i++){
            c = cols[i];
            if(c.hideable !== false){
                this.colMenu.add(new Ext.menu.CheckItem({
                    itemId: 'col-' + i,
                    text: c.header,
                    checked: !c.hidden,
                    hideOnClick:false,
                    disabled: c.hideable === false
                }));
            }
        }
    },
                    
    // private
    handleHdMenuClick : function(item){
        var index = this.hdCtxIndex,
            id = item.getItemId();
        
        if(this.fireEvent('headermenuclick', this.columns[index], id, index) !== false) {
            index = id.substr(4);
            if(index > 0 && this.columns[index]) {
                this.setColumnVisible(index, !item.checked);
            }     
        }
        
        return true;
    },
    
    setColumnVisible : function(index, visible) {
        this.columns[index].hidden = !visible;        
        this.updateColumnWidths();
    },

    /**
     * Scrolls the grid to the top
     */
    scrollToTop : function(){
        this.innerBody.dom.scrollTop = 0;
        this.innerBody.dom.scrollLeft = 0;
    },

    // private
    syncScroll : function(){
        this.syncHeaderScroll();
        var mb = this.innerBody.dom;
        this.fireEvent('bodyscroll', mb.scrollLeft, mb.scrollTop);
    },

    // private
    syncHeaderScroll : function(){
        var mb = this.innerBody.dom;
        this.innerHd.dom.scrollLeft = mb.scrollLeft;
        this.innerHd.dom.scrollLeft = mb.scrollLeft; // second time for IE (1/2 time first fails, other browsers ignore)
    },
    
    registerNode : function(n) {
        Ext.ux.tree.TreeGrid.superclass.registerNode.call(this, n);
        if(!n.uiProvider && !n.isRoot && !n.ui.isTreeGridNodeUI) {
            n.ui = new Ext.ux.tree.TreeGridNodeUI(n);
        }
    }
});

Ext.reg('treegrid', Ext.ux.tree.TreeGrid);

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


moduleDetails = 'Редактирование страниц';
var ModelPanel,
    SchemaPanel,
    PluginsPanel,
    PluginsSelectorStore,
    SchemaJSONStore,
    PageSettingsWindow,
    PagesStore,
    apiURL = 'includes/modules/pages/page_manager/api.php',
    pluginsURL = 'includes/modules/pages/page_manager/get_plugins.php';

Ext.MessageBox.progress('Загрузка', 'Загрузка схемы...');

PluginsSelectorStore = new Ext.data.JsonStore({
	proxy: new Ext.data.HttpProxy({
		url: pluginsURL,
		method: 'POST'
	}),
	root: 'plugins',
	fields: ['text', 'id', 'name', 'bd_id', 'settings', 'class']
});

SchemaJSONStore = new Ext.data.JsonStore({
	proxy: new Ext.data.HttpProxy({
		url: apiURL,
		method: 'POST'
	}),
	baseParams: {task: 'GET_PAGE_SCHEMA'},
	root: 'sides',
	fields: ['side', 'visible', 'items', 'p_array']
});

PagesStore = new Ext.data.JsonStore({
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        method: 'POST'
    }),
    baseParams: {task: 'GET_PAGES'},
    root: 'pages',
    fields: ['bd_id', 'title', 'keywords', 'name', 'description']
});

PageSettingsWindow = new Ext.Window({
	id: 'PageSettingsWindow',
	title: 'Настройки страницы',
	closable: false,
	width: 381,
	height: 250,
	plain: true,
	items: new Ext.FormPanel({
            labelAlignL: 'top',
            bodyStyle: 'padding: 5px',
            width: 364,
            labelPad: 30,
            layout: 'form',
            items: [
                    {
                            xtype: 'textfield',
                            fieldLabel: 'Title',
                            id: 'PageTitleField',
                            maxLength: 250,
                            allowBlank: true,
                            width: 214
                    },
                    {
                            xtype: 'textarea',
                            fieldLabel: 'Keywords',
                            id: 'PageKeywordsField',
                            maxLength: 250,
                            width: 214
                    },
                    {
                            xtype: 'textarea',
                            fieldLabel: 'Description',
                            id: 'PageDescriptionField',
                            maxLength: 250,
                            width: 214
                    }
            ],
            buttons: [
                    {
                            text: 'Закрыть',
                            handler: function() {
                                    PageSettingsWindow.hide();
                            }
                    }
            ]
        })
});

SchemaPanel = new Ext.Panel({
	region: 'center',
	collapsed: true,
	layout: 'border',
	border: false,
	items:
		[{
			xtype: 'panel',
			region: 'north',
			id: 'NorthPanel',
			bodyStyle : 'overflow: auto',
			height: 150,
			collapseMode: 'mini',
			split: true,
			tbar: [
				{
					xtype: 'combo',
					editable: false,
					mode: 'remote',
					store: PluginsSelectorStore,
					displayField: 'text',
					triggerAction: 'all',
					selectOnFocus: true
				},
				{
					xtype: 'button',
					iconCls: 'add',
					height: 16,
					handler: addPluginToPanel
				}
			]

		},
		{
			xtype: 'panel',
			id: 'WestPanel',
			region: 'west',
			bodyStyle : 'overflow: auto',
			width: '20%',
			height: '40%',
			collapseMode: 'mini',
			split: true,
			tbar: [
				{
					xtype: 'combo',
					editable: false,
					mode: 'remote',
					store: PluginsSelectorStore,
					displayField: 'text',
					triggerAction: 'all',
					selectOnFocus: true
				},
				{
					xtype: 'button',
					iconCls: 'add',
					height: 16,
					handler: addPluginToPanel
				}
			]
		},
		{
			xtype: 'panel',
			region: 'center',
			id: 'CenterPanel',
			bodyStyle : 'overflow: auto',
			width: '40%',
			height: '40%',
			tbar: [
				{
					xtype: 'combo',
					editable: false,
					mode: 'remote',
					store: PluginsSelectorStore,
					displayField: 'text',
					triggerAction: 'all',
					selectOnFocus: true
				},
				{
					xtype: 'button',
					iconCls: 'add',
					height: 16,
					handler: addPluginToPanel
				}
			]
		},
		{
			xtype: 'panel',
			width: '20%',
			region: 'east',
			id: 'EastPanel',
			height: '40%',
			collapseMode: 'mini',
			bodyStyle : 'overflow: auto',
			split: true,
			tbar: [
				{
					xtype: 'combo',
					editable: false,
					mode: 'remote',
					store: PluginsSelectorStore,
					displayField: 'text',
					triggerAction: 'all',
					selectOnFocus: true
				},
				{
					xtype: 'button',
					iconCls: 'add',
					height: 16,
					handler: addPluginToPanel
				}
			]
		},
		{
			xtype: 'panel',
			region: 'south',
			id: 'SouthPanel',
			height: 150,
			bodyStyle : 'overflow: auto',
			collapseMode: 'mini',
			split: true,
			tbar: [
				{
					xtype: 'combo',
					editable: false,
					mode: 'remote',
					store: PluginsSelectorStore,
					displayField: 'text',
					triggerAction: 'all',
					selectOnFocus: true
				},
				{
					xtype: 'button',
					iconCls: 'add',
					height: 16,
					handler: addPluginToPanel
				}
			]
		}
	]
});

ModelPanel = new Ext.Panel({
    layout: 'border',
    anchor: '100% 100%',
    border: false,
    tbar: [
        {
            xtype: 'combo',
            editable: false,
            id: 'PagesComboBox',
            mode: 'remote',
            store: PagesStore,
            displayField: 'name',
            triggerAction: 'all',
            selectOnFocus: true
        },
        {
            xtype: 'button',
            text: 'Дополнительные настройки страницы',
            iconCls: 'settings',
            handler: displayPageSettings
        },
        {
            xtype: 'button',
            text: 'Сохранить',
            iconCls: 'save',
            handler: savePage
        },
        {
            xtype: 'button',
            text: 'Удалить',
            iconCls: 'remove',
            handler: removePage
        },
        {
            xtype: 'button',
            text: 'Перейти на страницу',
            iconCls: 'link-go',
            handler: function() {
                window.location = '/?page=' + Ext.getCmp('PagesComboBox').getValue();
            }
        },
        {
            xtype: 'button',
            text: 'debug',
            handler: function() {
                console.log(Ext.encode(SideController.sidesArray));
            }
        }
    ],
    items: [SchemaPanel]
});

SchemaJSONStore.on('load', function() {
	Ext.MessageBox.updateProgress(.8);

	SchemaPanel.expand(true);
	SchemaJSONStore.each(function(record) {
		if(record.data.visible == false) {
			switch(record.data.side) {
				case 'header':
					Ext.getCmp('NorthPanel').collapse();
					break;
				case 'left':
					Ext.getCmp('WestPanel').collapse();
					break;
				case 'right':
					Ext.getCmp('EastPanel').collapse();
					break;
				case 'center':
					Ext.getCmp('CenterPanel').collapse();
					break;
				case 'bottom':
					Ext.getCmp('SouthPanel').collapse();
					break;
			}
		}

	});

    Ext.MessageBox.hide();
});

Ext.getCmp('PagesComboBox').on('select', function(c, r, i) {
    Ext.getCmp('NorthPanel').removeAll();
    Ext.getCmp('WestPanel').removeAll();
    Ext.getCmp('EastPanel').removeAll();
    Ext.getCmp('CenterPanel').removeAll();
    Ext.getCmp('SouthPanel').removeAll();

    Ext.getCmp('PageTitleField').setValue(r.data.title);
    Ext.getCmp('PageKeywordsField').setValue(r.data.keywords);
    Ext.getCmp('PageDescriptionField').setValue(r.data.description);

    Ext.MessageBox.progress('Загрузка...', 'Загрузка плагинов...');
    Ext.MessageBox.updateProgress(.3);

    SideController.load_panels(r.data.name);
});

SideController = {
    side: null,
    plugin: null,

    sidesArray: {},

    Side: function(side) {
        this.side = side;
    },

    simplePlugin: function (html) {
        this.plugin = {
            html: html,
            tools: [{
                id:'close',
                handler: function(e, target, panel){
                    var id = panel.arrayID;

                    SideController.side = panel.ownerCt;

                    delete SideController.sidesArray[panel.ownerCt.getId()][id-1];

                    panel.ownerCt.remove(panel, true);
                }
            }],
            cls: 'plugin'
        };

        this.setPlugin();
    },

    setPlugin: function() {

        this.setMainSide();

        this.side.add(this.plugin);
        this.side.doLayout();
    },

    setMainSide: function() {
        var sideName = this.side.getId();

        if(!(sideName in this.sidesArray))
            this.sidesArray[sideName] = [];

        this.plugin.arrayID = this.sidesArray[sideName].push(this.getPluginDbId());
    },

    getPluginDbId: function() {

        var name = (this.plugin.html != undefined) ? this.plugin.html : this.plugin.title;

        return this.getPluginValue('text', name, 'bd_id');

    },

    getPluginValue: function( type, name, return_type) {
        var record_id = PluginsSelectorStore.findExact(type, name);

        if(record_id == -1) return 'undefined';

        var record = PluginsSelectorStore.getAt(record_id);

        return record.data[return_type];
    },

    parentContainerName: function() {
        return this.side.ownerCt.getId();
    },

    load_panels: function(page_id) {
        Ext.Ajax.request({
            url: apiURL,
            params: {
                page: page_id,
                task: 'GET_PLUGINS'
            },
            disableCaching: false,
            success: function(response) {
                Ext.MessageBox.updateProgress(1);
                SideController.sidesArray = {};
                SideController.add_panels(Ext.decode(response.responseText));
                Ext.MessageBox.hide();
                console.log(Ext.encode(SideController.sidesArray));
            },
            failure: function(response) {
                Ext.MessageBox.alert('Ошибка', 'Проверьте подключение.');
            }
        });
    },

    add_panels: function(plugins)
    {
        for(var side in plugins) {
            for(var i = 0; i < plugins[side].length; i++)
            {
                var plugin = {
                    html: this.getPluginValue('bd_id', plugins[side][i], 'text'),
                    tools: [{
                        id:'close',
                        handler: function(e, target, panel){
                            var id = panel.arrayID;

                            SideController.side = panel.ownerCt;

                            delete SideController.sidesArray[panel.ownerCt.getId()][id-1];

                            panel.ownerCt.remove(panel, true);
                        }
                    }],
                    cls: 'plugin'
                };

                SideController.append_plugin_to_panel(side, plugins[side][i], plugin);
            }
        }
    },

    append_plugin_to_panel: function(side, bd_id, plugin)
    {
        if(!(side in SideController.sidesArray))
            SideController.sidesArray[side] = [];
        
        plugin.arrayID = SideController.sidesArray[side].push(bd_id);
        
        Ext.getCmp(side).add(plugin);
        Ext.getCmp(side).doLayout();
    }
}

function addPluginToPanel(e ,t) {
	var side = e.findParentByType('panel'),
		pluginCombo = e.findParentByType('panel').getTopToolbar().items.itemAt(0),
		pluginName = pluginCombo.getValue(),
		plugin = {};

	SideController.Side(side);

    plugin = SideController.simplePlugin(pluginCombo.getValue());

}

function removePage() {

	var pageName = Ext.getCmp('PagesComboBox').getValue();

	if(pageName == '') {
		Ext.MessageBox.alert('Ошибка', 'Введите название страницы!');
		return false;
	}

	Ext.Ajax.request({
		waitMsg: 'Сохраняю...',
		url: apiURL,
		params: {
			task: 'DELETE_PAGE',
			pageName: pageName
		},
		disableCaching: false,
		success: function(response) {
			var result = response.responseText;
			if(result == '1') {
				Ext.MessageBox.alert('Внимание!', 'Страница успешно удалена!');
			}
			else {
				Ext.MessageBox.alert('Ошибка', result);
			}
		},
		failure: function(response) {
			Ext.MessageBox.alert('Ошибка', 'Проверьте подключение.');
		}
	});
}

function savePage() {

	var pageName = Ext.getCmp('PagesComboBox').getValue();

	Ext.Ajax.request({
		waitMsg: 'Сохраняю...',
		url: apiURL,
		params: {
			task: 'SAVE_PAGE',
			pageSchema: Ext.encode(SideController.sidesArray),
			pageName: pageName,
            pageTitle: Ext.getCmp('PageTitleField').getValue(),
            pageKeywords: Ext.getCmp('PageKeywordsField').getValue(),
            pageDescription: Ext.getCmp('PageDescriptionField').getValue()
		},
		disableCaching: false,
		success: function(response) {
			var result = response.responseText;
			if(result == '1') {
				Ext.MessageBox.alert('Сохранено', 'Страница успешно сохранена!');
			}
			else {
				Ext.MessageBox.alert('Ошибка', result);
			}
		},
		failure: function(response) {
			Ext.MessageBox.alert('Ошибка', 'Проверьте подключение.');
		}
	});
}

function displayPageSettings() {

	if(!PageSettingsWindow.isVisible()) {
            PageSettingsWindow.show();
	}
	else {
            PageSettingsWindow.toFront();
	}
}

try {
    Ext.get('PageTitleField').remove();
    Ext.get('PageKeywordsField').remove();
    Ext.get('PageDescriptionField').remove();
}
catch(e) {}

SchemaPanel.on('afterrender', function(e) {
	Ext.MessageBox.updateProgress(.5);
});

PagesStore.load();
PluginsSelectorStore.load();
SchemaJSONStore.load();

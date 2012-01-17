moduleDetails = 'Создание и разметка страницы';
var ModelPanel,
	SchemaPanel,
	PluginsPanel,
	PluginsSelectorStore,
	SchemaJSONStore,
        PageSettingsWindow,
	apiURL = 'includes/modules/pages/add_page/api.php',
	pluginsURL = 'includes/modules/pages/add_page/get_plugins.php';

Ext.MessageBox.progress('Загрузка', 'Загрузка схемы...');

PluginsSelectorStore = new Ext.data.JsonStore({
	proxy: new Ext.data.HttpProxy({
		url: pluginsURL,
		method: 'POST'
	}),
	root: 'plugins',
	fields: ['text', 'id', 'name', 'bd_id', 'container', 'settings', 'class']
});
	
SchemaJSONStore = new Ext.data.JsonStore({
	proxy: new Ext.data.HttpProxy({
		url: apiURL,
		method: 'POST'
	}),
	baseParams: {task: 'GET_PAGE_SCHEMA'},
	root: 'sides',
	fields: ['side', 'visible']
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
            xtype: 'textfield',
            fieldLabel: 'Название страницы',
            width: 300,
            id: 'PageName',
            maskRe: /^[a-zA-Z0-9]+$/,
            allowBlank: false
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
        }
    ],
    items: [SchemaPanel]
});

SideController = {
    side: null,
    plugin: null,
    count: 0,
	
    sidesArray: {},

    pluginsCount: function()
    {
        return this.count;
    },

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
                    SideController.count--;
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
        this.count++;
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
    }
}

function addPluginToPanel(e ,t) {
	var side = e.findParentByType('panel'),
		pluginCombo = e.findParentByType('panel').getTopToolbar().items.itemAt(0),
		plugin = {};

    if(pluginCombo.getValue() == '') return false;
    
	SideController.Side(side);
	
	plugin = SideController.simplePlugin(pluginCombo.getValue());
	
}

function savePage() {
    
	var pageName = Ext.getCmp('PageName').getValue();

    console.log(Ext.encode(SideController.sidesArray));

    if(SideController.pluginsCount() == 0)
    {
        Ext.MessageBox.alert('Ошибка!', 'Добавьте хотя бы один плагин!');
        return false;
    }

    if(SideController.sidesArray)
	if(pageName == '') {
		Ext.MessageBox.alert('Ошибка', 'Введите название страницы!');
		return false;
	}
	
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

SchemaJSONStore.on('load', function() {
	Ext.MessageBox.updateProgress(1);

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

PluginsSelectorStore.load();
SchemaJSONStore.load();



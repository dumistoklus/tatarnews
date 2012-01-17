try {
    Ext.get('EditorWindow').remove();
    Ext.get('AddWindow').remove();
}
catch(e) {}

var FooterMenuDataStore,
FooterMenuColumnModel,
FooterMenuDataGrid,
apiURL = 'includes/modules/articles/footer_menu/api.php',
RowEditor = new Ext.ux.grid.RowEditor({
    saveText: 'Обновить',
    cancelText: 'Отмена'
});

moduleDetails = 'Управление нижним меню';

FooterMenuDataStore =  new Ext.data.Store({
    id: 'FooterMenuDataStore',
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        totalProperty: 'total',
        method: 'POST'
    }),
    baseParams: {
        task: 'GET_FOOTER_MENU'
    },
    reader: new Ext.data.JsonReader({
        root: 'results',
        id: 'id'
    },
    [
    {
        name: 'id',
        type: 'int',
        mapping: 'id'
    },
    {
        name: 'name',
        type: 'string',
        mapping: 'name'
    },
    {
        name: 'link',
        type: 'string',
        mapping: 'link'
    }
    ])

});

FooterMenuColumnModel = new Ext.grid.ColumnModel({
    defaults: {
        sortable: true
    },
    columns: [
    {
        header: '#',
        readonly: true,
        dataIndex: 'id',
        width: 50,
        hidden: true
    },
    {
        header: 'Пункт',
        dataIndex: 'name',
        width: 400
    },
    {
        header: 'Ссылка',
        dataIndex: 'link',
        width: 400
    }
    ]
});

FooterMenuDataGrid = new Ext.grid.EditorGridPanel({
    id: 'FooterMenuGrid',
    store: FooterMenuDataStore,
    cm: FooterMenuColumnModel,
    enableColLock: false,
    anchor: '100% 100%',
    border: false,
    selModel: new Ext.grid.RowSelectionModel({
        singleSelect: false
    }),
    tbar: [
    {
        xtype: 'button',
        iconCls: 'add',
        text: 'Новый пункт',
        handler:  function () {         
          AddWindow.show();
        }
    },{
        xtype: 'button',
        iconCls: 'remove',
        text: 'Удалить',
        handler: deletePunkts
    }
    ],
    listeners: {
        celldblclick: function (t, rIndex, cIndex, e) {

            var data = FooterMenuDataStore.getAt(rIndex);
            
            Ext.getCmp('nameEditPunkt').setValue(data.data.name);
            Ext.getCmp('linkEditPunkt').setValue(data.data.link);

            EditorWindow.show();
        }
    }
});

AddWindow = new Ext.Window({
    id: 'AddPunktWindow',
    title: 'Новый пункт',
    width: 700,
    height: 150,
    layout: "fit",
    modal: true,
    border: false,
    closable: false,
    items: [
    {
        xtype: 'form',
        border: false,
        bodyStyle: 'padding: 15 15',
        items: [
        {
            xtype: 'textfield',
            fieldLabel: 'Пункт',
            id: 'nameNewPunkt',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Ссылка',
            id: 'linkNewPunkt',
            width: 530
        }
        ]
    }
    ],
    buttons: [
    {
        text: 'Сохранить',
        iconCls: 'save',
        handler: function() {
            Ext.Ajax.request({
                waitMsg: 'Подождите...',
                url: apiURL,
                method: 'POST',
                params: {
                    task: 'ADD_PUNKT',
                    name: Ext.getCmp('nameNewPunkt').getValue(),
                    link: Ext.getCmp('linkNewPunkt').getValue()
                },
                success: function(response) {
                    switch(response.responseText) {
                        case '1':
                            Ext.MessageBox.alert('Внимание', 'Пункт успешно создан!');
                            AddWindow.hide();
                            FooterMenuDataStore.reload();
                            break;
                        default:
                            Ext.MessageBox.alert('Внимание', 'Произошла ошибка либо некорректно заполнены поля!');
                    }
                },
                failure: function() {
                    Ext.MessageBox.alert('Внимание', 'Проверьте подключение!');
                }
            });
        }
    },
    {
        text: 'Закрыть',
        handler: function() {
            AddWindow.hide();
        }
    }
    ]
});

EditorWindow = new Ext.Window({
    id: 'EditorPunktWindow',
    title: 'Редактирование пункта меню',
    width: 700,
    height: 150,
    layout: "fit",
    modal: true,
    border: false,
    closable: false,
    items: [
    {
        xtype: 'form',
        border: false,
        bodyStyle: 'padding: 15 15',
        items: [
        {
            xtype: 'textfield',
            fieldLabel: 'Пункт',
            id: 'nameEditPunkt',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Ссылка',
            id: 'linkEditPunkt',
            width: 530
        }
        ]
    }
    ],
    buttons: [
    {
        text: 'Сохранить',
        iconCls: 'save',
        handler: function() {
            Ext.Ajax.request({
                waitMsg: 'Подождите...',
                url: apiURL,
                method: 'POST',
                params: {
                    task: 'EDIT_PUNKT',
                    id: FooterMenuDataGrid.selModel.getSelections()[0].data.id,
                    name: Ext.getCmp('nameEditPunkt').getValue(),
                    link: Ext.getCmp('linkEditPunkt').getValue()
                },
                success: function(response) {
                    switch(response.responseText) {
                        case '1':
                            Ext.MessageBox.alert('Внимание', 'Данные о пункте меню успешно обновлены!');
                            FooterMenuDataStore.reload();
                            break;
                        case '2':
                            Ext.MessageBox.alert('Внимание', 'Вы не внесли измениний в данные!');
                        default:
                            Ext.MessageBox.alert('Внимание', 'Произошла ошибка либо некорректно заполнены поля!');
                    }
                },
                failure: function() {
                    Ext.MessageBox.alert('Внимание', 'Проверьте подключение!');
                }
            });
        }
    },
    {
        text: 'Закрыть',
        handler: function() {
            EditorWindow.hide();
           
        }
    }
    ]
});

function deletePunkts() {
    var countSelectedItems = FooterMenuDataGrid.selModel.getCount();

    if(countSelectedItems == 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранный пункт меню?', confirmRemoveLinkedPunkts);
    }
    else if(countSelectedItems > 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранные пункты меню?', confirmRemoveLinkedPunkts);
    }
    else {
        Ext.MessageBox.alert('Внимание!', 'Выберите хотя бы одну личность!');
    }
}

function confirmRemoveLinkedPunkts(answer) {

    if(answer == 'yes')
    {
        removePunkts();
    }
}

function removePunkts()
{
    var selectedPunkts = FooterMenuDataGrid.selModel.getSelections(),
    selectedPunktsIDs = [],
    countSelectedPunkts = FooterMenuDataGrid.selModel.getCount();

    for(var i = 0; i < countSelectedPunkts; i++) {
        selectedPunktsIDs.push(selectedPunkts[i].json.id);
    }

    selectedPunkts = Ext.encode(selectedPunktsIDs);

    Ext.Ajax.request({
        waitingMsg: 'Пожалуйста, подождите...',
        url: apiURL,
        params: {
            task: 'DELETE_PUNKT',
            punkts_ids: selectedPunkts
        },
        success: function (response) {
            if(response.responseText > 0)
                FooterMenuDataStore.reload();
            else
                Ext.MessageBox.alert('Ошибка!', 'Ошибка при удалении!');
        },
        failure: function (response) {
            Ext.MessageBox.alert('Ошибка!', 'Сбой подключения, попробуйте позже.');
        }
    });
}

FooterMenuDataStore.load();
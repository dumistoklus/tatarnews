var apiURL = 'includes/modules/articles/cats_manager/api.php',
    ManagerPanel,
    CatsStore,
    countOfCatsPerPage = 37,
    ColumnModel,
	RowEditor = new Ext.ux.grid.RowEditor({
        saveText: 'Обновить',
        cancelText: 'Отмена'
    });

CatsStore = new Ext.data.Store({
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        method: 'POST'
    }),
    baseParams: {
        task: 'GET_CATS',
        start: 0,
        limit: countOfCatsPerPage
    },
    reader: new Ext.data.JsonReader({
        root: 'results',
        totalProperty: 'total',
        id: 'id'
    },
    [
        { name: 'id', type: 'int', mapping: 'id' },
        { name: 'name', type: 'string', mapping: 'name' }
    ])
});

ColumnModel = new Ext.grid.ColumnModel({
	defaults: {
		sortable: true
	},
    columns: [
        {
            header: '#',
            dataIndex: 'id',
            readOnly: true,
            width: 50,
            hidden: true
        },
        {
            header: 'Название',
            dataIndex: 'name',
            readOnly: true,
            width: 460,
            editor: new Ext.form.TextField({
                allowBlank: false
            })
        }
    ]
});

ManagerPanel = new Ext.grid.EditorGridPanel({
    store: CatsStore,
    cm: ColumnModel,
    selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
    enableColLock: false,
    anchor: '100% 100%',
    border: false,
    plugins: [RowEditor],
    bbar: new Ext.PagingToolbar({
        pageSize: countOfCatsPerPage,
        store: CatsStore,
        displayInfo: true
    }),
    tbar: [
        {
            text: 'Новая категория',
            iconCls: 'add',
            handler: function() {
                Ext.MessageBox.prompt('Новая категория', 'Введите название новой категории', function(answer, name){
                    if(answer == 'ok') {
                        if(name.length > 3) {
                            Ext.Ajax.request({
                               waitMsg: 'Подождите...',
                               url: apiURL,
                                method: 'POST',
                                params: {
                                    task: 'CREATE_CAT',
                                    name: name
                                },
                                success: function(response) {
                                    switch(response.responseText) {
                                        case '1':
                                            CatsStore.reload();
                                            break;
                                        default:
                                            Ext.MessageBox.alert('Внимание', 'Произошла ошибка!');
                                    }
                                },
                                failure: function() {
                                     Ext.MessageBox.alert('Внимание','Проверьте подключение!');
                                }
                            });
                        }
                        else {
                             Ext.MessageBox.alert('Внимание', 'Название категории должно быть боьше 3 символов!');
                        }
                    }
                });
            }
        },
        {
            text: 'Удалить',
            iconCls: 'remove',
            handler: function() {
                var id = ManagerPanel.selModel.getSelections()[0].data.id;

                Ext.Ajax.request({
                   waitMsg: 'Подождите...',
                   url: apiURL,
                    method: 'POST',
                    params: {
                        task: 'DELETE_CAT',
                        id: id
                    },
                    success: function(response) {
                        switch(response.responseText) {
                            case '1':
                                CatsStore.reload();
                                break;
                            default:
                                Ext.MessageBox.alert('Внимание', 'Произошла ошибка!');
                        }
                    },
                    failure: function() {
                         Ext.MessageBox.alert('Внимание','Проверьте подключение!');
                    }
                });
            }
        }
    ]
});

CatsStore.load();
try {
    Ext.get('AddQuestionWindow').remove();
    Ext.get('EditQuestionWindow').remove();
}
catch(e) {}

var QuestionDataStore,
QuestionColumnModel,
QuestionGrid,
countOfNumberPerPage = 37,
apiURL = 'includes/modules/articles/questions/api.php',
RowEditor = new Ext.ux.grid.RowEditor({
    saveText: 'Обновить',
    cancelText: 'Отмена'
});

moduleDetails = 'Управление вопросами';

QuestionDataStore =  new Ext.data.Store({
    id: 'QuestionDataStore',
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        totalProperty: 'total',
        method: 'POST'
    }),
    baseParams: {
        task: 'GET_QUESTIONS',
        start: 0,
        limit: countOfNumberPerPage
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
        name: 'text',
        type: 'string',
        mapping: 'text'
    },
    {
        name: 'active',
        type: 'string',
        mapping: 'active'
    }
    ])

});

QuestionColumnModel = new Ext.grid.ColumnModel({
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
        header: 'Вопрос',
        dataIndex: 'text',
        width: 700
    },
    {
        header: 'Активен',
        dataIndex: 'active',
        width: 50
    }
    
    ]
});

QuestionGrid = new Ext.grid.EditorGridPanel({
    id: 'QuestionGrid',
    store: QuestionDataStore,
    cm: QuestionColumnModel,
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
        text: 'Новый вопрос',
        handler: function() {
            
            Ext.getCmp('AddQuestionPanel').getForm().reset();
            AddQuestionWindow.show();
        }
    },
    {
        xtype: 'button',
        iconCls: 'edit',
        text: 'Поменять активность',
        handler: change_active
        
    }
    ],
    bbar: new Ext.PagingToolbar({
        id: 'QuestionPagingToolbar',
        pageSize: countOfNumberPerPage,
        store: QuestionDataStore,
        displayInfo: true
    }),
    listeners: {
        celldblclick: function (t, rIndex, cIndex, e) {

            Ext.getCmp('EditQuestionPanel').getForm().reset();
            var data = QuestionDataStore.getAt(rIndex);
            
            Ext.getCmp('textEditQuestion').setValue(data.data.text);
           
            EditQuestionWindow.show();
        }
    }
});

AddQuestionWindow = new Ext.Window({
    id: 'AddQuestionWindow',
    title: 'Новый вопрос',
    width: 700,
    height: 150,
    layout: "fit",
    modal: true,
    border: false,
    closable: false,
    items: [
    {
        xtype: 'form',
        id: 'AddQuestionPanel',
        border: false,
        bodyStyle: 'padding: 15 15',
        items: [
        {
            xtype: 'textfield',
            fieldLabel: 'Вопрос',
            id: 'textNewQuestion',
            width: 530,
            allowBlank: false
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
                    
                    task: 'ADD_QUESTION',
                    text: Ext.getCmp('textNewQuestion').getValue()
          
                },
                success: function(response) {
                    switch(response.responseText) {
                        case '1':
                            Ext.MessageBox.alert('Внимание', 'Вопрос успешно создан!');
                            AddQuestionWindow.hide();
                            QuestionDataStore.reload({
                                params: {
                                    start: Ext.getCmp('QuestionPagingToolbar').cursor,
                                    limit: countOfNumberPerPage
                                }
                            });
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
            AddQuestionWindow.hide();
        }
    }
    ]
});

EditQuestionWindow = new Ext.Window({
    id: 'EditQuestionWindow',
    title: 'Редактирование вопроса',
    width: 700,
    height: 150,
    layout: "fit",
    modal: true,
    border: false,
    closable: false,
    items: [
    {
        xtype: 'form',
        id: 'EditQuestionPanel',
        border: false,
        bodyStyle: 'padding: 15 15',
        items: [
        {
            xtype: 'textfield',
            fieldLabel: 'Вопрос',
            id: 'textEditQuestion',
            width: 530,
            allowBlank: false
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
                    
                    task: 'EDIT_QUESTION',
                    id: QuestionGrid.selModel.getSelections()[0].data.id,
                    text: Ext.getCmp('textEditQuestion').getValue()
          
                },
                success: function(response) {
                    switch(response.responseText) {
                        case '1':
                            Ext.MessageBox.alert('Внимание', 'Вопрос успешно изменён!');
                            EditQuestionWindow.hide();
                            QuestionDataStore.reload({
                                params: {
                                    start: Ext.getCmp('QuestionPagingToolbar').cursor,
                                    limit: countOfNumberPerPage
                                }
                            });
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
            EditQuestionWindow.hide();
        }
    }
    ]
});

function change_active() {
    
    if(QuestionGrid.selModel.getCount() == 0) {
        Ext.MessageBox.alert('Внимание!', 'Вы не выбрали вопроса!');
        return;
    }
    var change_id = QuestionGrid.selModel.getSelections()[0].data.id;
    var change_active = QuestionGrid.selModel.getSelections()[0].data.active;

    Ext.Ajax.request({

        waitingMsg: 'Пожалуйста, подождите...',
        url: apiURL,
        params: {
            task: 'CHAHGE_ACTIVE',
            id: change_id,
            active: change_active
        },
        success: function (response) {
            if(response.responseText > 0) {
                 Ext.MessageBox.alert('Внимание!', 'Вы изменили активность выбранного вопроса!');
                QuestionDataStore.reload({
                    params: {
                        start: Ext.getCmp('QuestionPagingToolbar').cursor,
                        limit: countOfNumberPerPage
                    }
                });
            } else
                Ext.MessageBox.alert('Ошибка!', 'Произошла ошибка!');
        },
        failure: function (response) {
            Ext.MessageBox.alert('Ошибка!', 'Сбой подключения, попробуйте позже.');
        }
    });
}

QuestionDataStore.load();
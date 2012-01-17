var apiURL = 'includes/modules/articles/footer/api.php',
	RowEditor = new Ext.ux.grid.RowEditor({
        saveText: 'Обновить',
        cancelText: 'Отмена'
    });

var FooterMainForm = new Ext.FormPanel({
    bodyStyle: 'padding: 15 15',
    border: false,
    tbar: [
        {
            text: 'Сохранить',
            iconCls: 'save',
            handler: function()
            {
                Ext.Ajax.request({
                    waitMsg: 'Подождите...',
                    url: apiURL,
                    method: 'POST',
                    params: {
                        task: 'CREATE_VOTE',
                        name: Ext.getCmp('VoteName').getValue(),
                        answers: Ext.encode(Ext.pluck(Ext.pluck(Ext.getCmp('VoteAnswers').store.getRange(), 'data'), 'answer')),
                        date_start: Ext.getCmp('DateStart').getValue().format('U'),
                        date_end: Ext.getCmp('DateEnd').getValue().format('U')
                    },
                    success: function(response)
                    {
                        switch(response.responseText)
                        {
                            case '1':
                                Ext.MessageBox.alert('Внимание!', 'Голосование успешно создано!');
                                break;
                            default:
                                Ext.MessageBox.alert('Внимание!', 'Голосование не было создано, произошла ошибка.');
                        }
                    },

                    failure: function(response)
                    {
                        Ext.MessageBox.alert('Внимание!', 'Проверьте подключение!');
                    }
                });
            }
        }
    ],
    items: [
        {
            xtype: 'textfield',
            fieldLabel: 'Вопрос',
            id: 'VoteName',
            width: 203
        },
        {
            xtype: 'editorgrid',
            id: 'VoteAnswers',
            height: 300,
            width: 308,
            tbar: [
                {
                    xtype: 'tbspacer',
                    html: 'Варианты ответов:',
                    width: 100
                },
                {
                    xtype: 'button',
                    iconCls: 'add',
                    text: 'добавить',
                    handler: function()
                    {
                        Ext.Msg.prompt('', 'Введите вариант ответа:', function(btn, text){
                            if (btn == 'ok'){
                                var r = Ext.getCmp('VoteAnswers').store.recordType;
                                var record = new r({ answer: text});
                                Ext.getCmp('VoteAnswers').store.insert(Ext.getCmp('VoteAnswers').store.getCount(), record);
                            }
                        });
                    }
                },
                {
                    xtype: 'button',
                    iconCls: 'remove',
                    text: 'Удалить',
                    handler: function()
                    {
                        var selected = Ext.getCmp('VoteAnswers').selModel.getSelections();
                        Ext.getCmp('VoteAnswers').store.removeAt(Ext.getCmp('VoteAnswers').store.indexOf(selected[0]));
                    }
                }
            ],
            selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
            plugins: [RowEditor],
            cm: new Ext.grid.ColumnModel({
                columns: [
                    {
                        header: 'Ответ',
                        dataIndex: 'answer',
                        width: 300,
                        editor: new Ext.form.TextField({
                            allowBlank: false
                        })
                    }
                ]
            }),
            enableColLock: false,
            store: new Ext.data.ArrayStore({
                id: 'AnswersStore',
                fields: ['answer'],
                idIndex: 0
            })
        },
        {
            xtype: 'tbspacer',
            height: 20
        },
        {
            xtype: 'xdatetime',
            fieldLabel: 'Начальная дата',
            id: 'DateStart'
        },
        {
            xtype: 'xdatetime',
            fieldLabel: 'Конечная дата',
            id: 'DateEnd'
        }
    ]
});
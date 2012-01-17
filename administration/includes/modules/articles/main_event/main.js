var MainEventPanel,
    apiURL = 'includes/modules/articles/main_event/api.php';


MainEventPanel = new Ext.FormPanel({
    bodyStyle: 'padding: 15 15',
    frame: false,
    anchor: '100% 100%',
    items: [
        {
            xtype: 'textfield',
            id: 'ArticleId',
            width: 250,
            fieldLabel: 'ID новости',
            maskRe: /^[0-9]+$/
        },
        {
            xtype: 'textfield',
            id: 'ArticlePreview',
            width: 250,
            fieldLabel: 'Заголовок'
        },
        {
            xtype: 'xdatetime',
            id: 'DateStart',
            fieldLabel: 'Начало показа'
        },
        {
            xtype: 'xdatetime',
            id: 'DateEnd',
            fieldLabel: 'Конец показов'
        }
    ],
    tbar: [
        {
            text: 'Перейти к новости',
            handler: function() {
                window.open('/?page=articles&article_id='+Ext.getCmp('ArticleId').getValue());
            }
        },
        {
            text: 'Сохранить',
            iconCls: 'save',
            handler: function() {
                Ext.MessageBox.confirm('Внимание!', 'Текущее главное событие будет удалено! Продолжить?', function(ans) {
                    if(ans == 'yes') {
                        Ext.Ajax.request({
                            waitMsg: 'Подождите...',
                            url: apiURL,
                            method: 'POST',
                            params: {
                                task: 'SAVE_EVENT',
                                preview: Ext.getCmp('ArticlePreview').getValue(),
                                aid: Ext.getCmp('ArticleId').getValue(),
                                date_start: Ext.getCmp('DateStart').getValue().format('U'),
                                date_end: Ext.getCmp('DateEnd').getValue().format('U')
                            },
                            success: function(response) {
                                switch(response.responseText) {
                                    case '1':
                                        Ext.MessageBox.alert('Внимание!', 'Главное событие обновлено!');
                                        break;
                                    default:
                                        Ext.MessageBox.alert('Внимание!', 'Произошла ошибка!');

                                }
                            },
                            failure: function() {
                                Ext.MessageBox.alert('Внимание!', 'Проверьте подключение!');
                            }
                        });
                    }
                });
            }
        }
    ]

});

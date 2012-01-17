var apiURL = 'includes/modules/articles/news/api.php',
    NewsPanel;

NewsPanel = new Ext.FormPanel({
   bodyStyle: 'padding: 15 15',
   border: false,
   tbar: [
       {
           text: 'Сохранить',
           iconCls: 'save',
           handler: function() {
               Ext.Ajax.request({
                   waitMsg: 'Подождите...',
                   url: apiURL,
                   method: 'POST',
                   params: {
                       task : 'CREATE_NEWS',
                       name: Ext.getCmp('NewsName').getValue(),
                       content: Ext.getCmp('NewsContent').getValue(),
                       date: Ext.getCmp('NewsDate').getValue().format('U')
                   },
                   success: function(response) {
                        switch(response.responseText){
                            case '1':
                                Ext.MessageBox.alert('Внимание!', 'Новость добавлена!');
                                break;
                            default:
                                Ext.MessageBox.alert('Внимание!', 'Произошла ошибка при добавлении новости!');
                        }
                   },
                   failure: function()
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
           fieldLabel: 'Название новости',
           width: 703,
           id: 'NewsName'
       },
       {
           xtype: 'textarea',
           fieldLabel: 'Новость',
           id: 'NewsContent',
           width: 703,
           height: 600
       },
       {
           xtype: 'xdatetime',
           fieldLabel: 'Дата',
           id: 'NewsDate'
       }
   ]
});
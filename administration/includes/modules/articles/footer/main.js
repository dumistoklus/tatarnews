var apiURL = 'includes/modules/articles/footer/api.php';

var FooterDataStore =  new Ext.data.Store({
    id: 'FooterDataStore',
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        method: 'POST'
    }),
    baseParams: {
        task: 'GET_FOOTER'
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
        name: 'address',
        type: 'string',
        mapping: 'address'
    },
    {
        name: 'address_for_mail',
        type: 'string',
        mapping: 'address_for_mail'
    },
    {
        name: 'phone_reception',
        type: 'string',
        mapping: 'phone_reception'
    },
    {
        name: 'phone_correspondent',
        type: 'string',
        mapping: 'phone_correspondent'
    },
    {
        name: 'phone_commercial',
        type: 'string',
        mapping: 'phone_commercial'
    },
    {
        name: 'email',
        type: 'string',
        mapping: 'email'
    },
    {
        name: 'chief_editor',
        type: 'string',
        mapping: 'chief_editor'
    },
    {
        name: 'first_deputy',
        type: 'string',
        mapping: 'first_deputy'
    },
    {
        name: 'secretary',
        type: 'string',
        mapping: 'secretary'
    },
    {
        name: 'deputy',
        type: 'string',
        mapping: 'deputy'
    },
    {
        name: 'text',
        type: 'string',
        mapping: 'text'
    }
    ])

});

var FooterEventPanel = new Ext.FormPanel({
    bodyStyle: 'padding: 15 15',
    frame: false,
    anchor: '100% 100%',
    items: [
    {
        xtype: 'textfield',
        id: 'idFooter',
        width: 600,
        hidden: true
    },
    {
        xtype: 'textfield',
        id: 'addressFooter',
        width: 600,
        fieldLabel: 'Адресc'
    },
    {
        xtype: 'textfield',
        id: 'address_for_mailFooter',
        width: 600,
        fieldLabel: 'Ад. для писем'
    },
    {
        xtype: 'textfield',
        id: 'phone_receptionFooter',
        width: 600,
        fieldLabel: 'Тел. (приёмная)'
    },
    {
        xtype: 'textfield',
        id: 'phone_correspondentFooter',
        width: 600,
        fieldLabel: 'Тел. (корр-ты)'
    },
    {
        xtype: 'textfield',
        id: 'phone_commercialFooter',
        width: 600,
        fieldLabel: 'Тел. (реклама)'
    },
    {
        xtype: 'textfield',
        id: 'emailFooter',
        width: 600,
        fieldLabel: 'Email'
    },
    {
        xtype: 'textfield',
        id: 'chief_editorFooter',
        width: 600,
        fieldLabel: 'Гл. редактор'
    },
    {
        xtype: 'textfield',
        id: 'first_deputyFooter',
        width: 600,
        fieldLabel: 'Первый зам.'
    },
    {
        xtype: 'textfield',
        id: 'secretaryFooter',
        width: 600,
        fieldLabel: 'Ответ. секретарь'
    },
    {
        xtype: 'textfield',
        id: 'deputyFooter',
        width: 600,
        fieldLabel: 'Заместитель'
    },
    {
        xtype: 'textarea',
        id: 'textFooter',
        width: 600,
        height: 100,
        fieldLabel: 'Копирайт'
    }
    ],
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
                            task: 'EDIT_FOOTER',
                            address: Ext.getCmp('addressFooter').getValue(),
                            address_for_mail: Ext.getCmp('address_for_mailFooter').getValue(),
                            phone_reception: Ext.getCmp('phone_receptionFooter').getValue(),
                            phone_correspondent: Ext.getCmp('phone_correspondentFooter').getValue(),
                            phone_commercial: Ext.getCmp('phone_commercialFooter').getValue(),
                            email: Ext.getCmp('emailFooter').getValue(),
                            chief_editor: Ext.getCmp('chief_editorFooter').getValue(),
                            first_deputy: Ext.getCmp('first_deputyFooter').getValue(),
                            secretary: Ext.getCmp('secretaryFooter').getValue(),
                            deputy: Ext.getCmp('deputyFooter').getValue(),
                            text: Ext.getCmp('textFooter').getValue()
                        },
                        success: function(response) {
                            switch(response.responseText) {
                                case '1':
                                    Ext.MessageBox.alert('Внимание!', 'Данные о футере обновлены!');
                                    break;
                                default:
                                    Ext.MessageBox.alert('Внимание!', 'Произошла ошибка либо некорректно заполнены поля!');

                            }
                        },
                        failure: function() {
                            Ext.MessageBox.alert('Внимание!', 'Проверьте подключение!');
                        }
                    });
        }
    }
    ]

});

moduleDetails = 'Управление футером';

FooterDataStore.load();
FooterDataStore.on('load', function() {
var dataF = FooterDataStore.getAt(0);

Ext.getCmp('addressFooter').setValue(dataF.data.address);
Ext.getCmp('address_for_mailFooter').setValue(dataF.data.address_for_mail);
Ext.getCmp('chief_editorFooter').setValue(dataF.data.chief_editor);
Ext.getCmp('deputyFooter').setValue(dataF.data.deputy);
Ext.getCmp('emailFooter').setValue(dataF.data.email);
Ext.getCmp('first_deputyFooter').setValue(dataF.data.first_deputy);
Ext.getCmp('phone_commercialFooter').setValue(dataF.data.phone_commercial);
Ext.getCmp('phone_correspondentFooter').setValue(dataF.data.phone_correspondent);
Ext.getCmp('phone_receptionFooter').setValue(dataF.data.phone_reception);
Ext.getCmp('secretaryFooter').setValue(dataF.data.secretary);
Ext.getCmp('textFooter').setValue(dataF.data.text);
});
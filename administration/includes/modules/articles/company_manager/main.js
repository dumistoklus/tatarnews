try {
    Ext.get('EditorWindow').remove();
    Ext.get('AddWindow').remove();
}
catch(e) {}

var CompanyDataStore,
CompanyColumnModel,
CompanyDataGrid,
countOfNumberPerPage = 37,
apiURL = 'includes/modules/articles/company_manager/api.php',
RowEditor = new Ext.ux.grid.RowEditor({
    saveText: 'Обновить',
    cancelText: 'Отмена'
});

moduleDetails = 'Управление компаниями';

CompanyDataStore =  new Ext.data.Store({
    id: 'CompanyDataStore',
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        totalProperty: 'total',
        method: 'POST'
    }),
    baseParams: {
        task: 'GET_COMPANY',
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
        name: 'name',
        type: 'string',
        mapping: 'name'
    },
    {
        name: 'dob',
        type: 'string',
        format: 'Y-m-d',
        mapping: 'dob'
    },
    {
        name: 'industry',
        type: 'string',
        mapping: 'industry'
    },
    {
        name: 'img',
        type: 'string',
        mapping: 'img'
    },
    {
        name: 'products',
        type: 'string',
        mapping: 'products'
    },
    {
        name: 'revenue',
        type: 'string',
        mapping: 'revenue'
    },
    {
        name: 'profit',
        type: 'string',
        mapping: 'profit'
    },
    {
        name: 'director',
        type: 'string',
        mapping: 'director'
    },
    {
        name: 'number_of_emplayees',
        type: 'string',
        mapping: 'number_of_emplayees'
    },
    {
        name: 'about',
        type: 'string',
        mapping: 'about'
    },
    {
        name: 'history',
        type: 'string',
        mapping: 'history'
    },
    {
        name: 'adress',
        type: 'string',
        mapping: 'adress'
    },
    {
        name: 'phone',
        type: 'string',
        mapping: 'phone'
    },
    {
        name: 'email',
        type: 'string',
        mapping: 'email'
    },
    {
        name: 'site',
        type: 'string',
        mapping: 'site'
    },
    {
        name: 'guide',
        type: 'string',
        mapping: 'guide'
    } 
    ])

});

CompanyColumnModel = new Ext.grid.ColumnModel({
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
        header: 'Название',
        dataIndex: 'name',
        width: 200,
        allowBlank: false
    },
    {
        header: 'Дата создания',
        dataIndex: 'dob',
        format: 'Y-m-d',
        width: 100
    },
    {
        header: 'Отрасль',
        dataIndex: 'industry',
        width: 400
    },
    {
        header: "Логотип",
        width: 60,
        renderer:renderIcon,
        dataIndex: 'img'
    },
    {
        header: 'Продукция',
        dataIndex: 'products',
        width: 200,
        hidden: true
    },
    {
        header: 'Выручка',
        dataIndex: 'revenue',
        width: 200,
        hidden: true
    },
    {
        header: 'Чистая выручка',
        dataIndex: 'profit',
        width: 200,
        hidden: true
    },
    {
        header: 'Руководитель',
        dataIndex: 'director',
        width: 200,
        hidden: true
    },
    {
        header: 'Число сотрудников',
        dataIndex: 'number_of_emplayees',
        width: 200,
        hidden: true
    },
    {
        header: 'Описание',
        dataIndex: 'about',
        width: 200,
        hidden: true
    },
    {
        header: 'История',
        dataIndex: 'history',
        width: 200,
        hidden: true
    },
    {
        header: 'Адресс',
        dataIndex: 'adress',
        width: 200,
        hidden: true
    },
    {
        header: 'Телефон',
        dataIndex: 'phone',
        width: 200,
        hidden: true
    },
    {
        header: 'Электронная почта',
        dataIndex: 'email',
        width: 200,
        hidden: true
    },
    {
        header: 'Cайт',
        dataIndex: 'site',
        width: 200,
        hidden: true
    },
    {
        header: 'Руководство',
        dataIndex: 'guide',
        width: 200,
        hidden: true
    }
    ]
});

CompanyDataGrid = new Ext.grid.EditorGridPanel({
    id: 'CompanyDataGrid',
    store: CompanyDataStore,
    cm: CompanyColumnModel,
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
        text: 'Новая компания',
        handler:  function () {

            Ext.getCmp('AddCompanyPanel').getForm().reset();

            AddWindow.show();
        }
    },{
        xtype: 'button',
        iconCls: 'remove',
        text: 'Удалить',
        handler: deleteCompany
    }
    ],
    bbar: new Ext.PagingToolbar({
        id: 'CompanyPagingToolbar',
        pageSize: countOfNumberPerPage,
        store: CompanyDataStore,
        displayInfo: true
    }),
    listeners: {
        celldblclick: function (t, rIndex, cIndex, e) {

            Ext.getCmp('EditCompanyPanel').getForm().reset();
            var data = CompanyDataStore.getAt(rIndex);
            
            Ext.getCmp('nameEditCompany').setValue(data.data.name);
            if(data.data.dob != '0000-00-00')
                Ext.getCmp('dobEditCompany').setValue(data.data.dob);
            Ext.getCmp('industryEditCompany').setValue(data.data.industry);
            Ext.getCmp('productsEditCompany').setValue(data.data.products);
            Ext.getCmp('revenueEditCompany').setValue(data.data.revenue);
            Ext.getCmp('profitEditCompany').setValue(data.data.profit);
            Ext.getCmp('directorEditCompany').setValue(data.data.director);
            Ext.getCmp('number_of_emplayeesEditCompany').setValue(data.data.number_of_emplayees);
            Ext.getCmp('aboutEditCompany').setValue(data.data.about);
            Ext.getCmp('historyEditCompany').setValue(data.data.history);
            Ext.getCmp('adressEditCompany').setValue(data.data.adress);
            Ext.getCmp('phoneEditCompany').setValue(data.data.phone);
            Ext.getCmp('emailEditCompany').setValue(data.data.email);
            Ext.getCmp('siteEditCompany').setValue(data.data.site);
            Ext.getCmp('guideEditCompany').setValue(data.data.guide);          
            Ext.getCmp('imgEditCompany').setValue(data.data.img);
           
            EditorWindow.show();

            if (data.data.img == '') {
                document.getElementById("companyEditImageBox").src = "/images/company/nophoto.jpg";
            }
            else {
                document.getElementById("companyEditImageBox").src =  "/images/company/"+data.data.img;
            }
        }
    }
});

AddWindow = new Ext.Window({
    id: 'AddCompanyWindow',
    title: 'Новая компания',
    width: 700,
    height: 700,
    layout: "fit",
    modal: true,
    border: false,
    closable: false,
    items: [
    {
        xtype: 'form',
        id: 'AddCompanyPanel',
        border: false,
        bodyStyle: 'padding: 15 15',
        items: [
        {
            xtype: 'textfield',
            fieldLabel: 'Название',
            id: 'nameNewCompany',
            width: 530,
            allowBlank: false
        },
        {
            xtype: 'datefield',
            fieldLabel: 'Дата создания',
            format: 'Y-m-d',
            id: 'dobNewCompany'
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Отрасль',
            id: 'industryNewCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            id: 'imgNewCompany',
            width: 530,
            hidden: true
        },
        {
            xtype: 'box',
            fieldLabel: 'Логотип на данный момент',
            id: 'companyNewImageBox',
            height: 100,
            autoEl:
            {
                tag: 'img',
                src: '/images/company/nophoto.jpg'
            }
        },
        {
            xtype: 'button',
            id: 'ImgCompanyNewSelectBtn',
            fieldLabel: 'Логотип',
            text: 'Выберите файл',
            width: 200,
            handler: function()
            {
                CompanyNewImageSelectorWindow.show();
            }
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Продукция',
            id: 'productsNewCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Выручка',
            id: 'revenueNewCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Чистая прибыль',
            id: 'profitNewCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Руководитель',
            id: 'directorNewCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Число сотруд.',
            id: 'number_of_emplayeesNewCompany',
            width: 530
        },
        {
            xtype: 'textarea',
            fieldLabel: 'Описание',
            id: 'aboutNewCompany',
            height: 100,
            width: 530
        },
        {
            xtype: 'textarea',
            fieldLabel: 'История',
            id: 'historyNewCompany',
            height: 100,
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Телефон',
            id: 'phoneNewCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Адресс',
            id: 'adressNewCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Эл. почта',
            id: 'emailNewCompany',
            width: 530,
            maskRe: /^([-_a-zA-Z0-9@.]{0,100})$/
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Сайт',
            id: 'siteNewCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Руководство',
            id: 'guideNewCompany',
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
                    task: 'ADD_COMPANY',
                    
                    name : Ext.getCmp('nameNewCompany').getValue(),
                    dob : Ext.getCmp('dobNewCompany').getValue(),
                    industry : Ext.getCmp('industryNewCompany').getValue(),
                    products : Ext.getCmp('productsNewCompany').getValue(),
                    revenue : Ext.getCmp('revenueNewCompany').getValue(),
                    profit  : Ext.getCmp('profitNewCompany').getValue(),
                    director : Ext.getCmp('directorNewCompany').getValue(),
                    number_of_emplayees : Ext.getCmp('number_of_emplayeesNewCompany').getValue(),
                    about : Ext.getCmp('aboutNewCompany').getValue(),
                    history : Ext.getCmp('historyNewCompany').getValue(),
                    adress :  Ext.getCmp('adressNewCompany').getValue(),
                    phone : Ext.getCmp('phoneNewCompany').getValue(),
                    email : Ext.getCmp('emailNewCompany').getValue(),
                    site : Ext.getCmp('siteNewCompany').getValue(),
                    guide : Ext.getCmp('guideNewCompany').getValue(),
                    img : Ext.getCmp('imgNewCompany').getValue()
          
                },
                success: function(response) {
                    switch(response.responseText) {
                        case '1':
                            Ext.MessageBox.alert('Внимание', 'Компания успешно создана!');
                            AddWindow.hide();
                            CompanyDataStore.reload({
                                params: {
                                    start: Ext.getCmp('CompanyPagingToolbar').cursor,
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
            AddWindow.hide();
        }
    }
    ]
});


EditorWindow = new Ext.Window({
    id: 'EditorWindow',
    title: 'Редактирование личности',
    width: 700,
    height: 750,
    layout: "fit",
    modal: true,
    border: false,
    closable: false,
    items: [
    {
        xtype: 'form',
        id: 'EditCompanyPanel',
        border: false,
        bodyStyle: 'padding: 15 15',
        items: [
        {
            xtype: 'textfield',
            fieldLabel: 'Название',
            id: 'nameEditCompany',
            width: 530
        },
        {
            xtype: 'datefield',
            fieldLabel: 'Дата создания',
            format: 'Y-m-d',
            id: 'dobEditCompany'
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Отрасль',
            id: 'industryEditCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            id: 'imgEditCompany',
            width: 530,
            hidden: true
        },
        {
            xtype: 'box',
            fieldLabel: 'Логотип на данный момент',
            id: 'companyEditImageBox',
            height: 100,
            autoEl:
            {
                tag: 'img',
                src: '/images/company/nophoto.jpg'
            }
        },
        {
            xtype: 'button',
            id: 'ImgCompanyEditSelectBtn',
            fieldLabel: 'Логотип',
            text: 'Выберите файл',
            width: 200,
            handler: function()
            {
                CompanyEditImageSelectorWindow.show();
            }
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Продукция',
            id: 'productsEditCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Выручка',
            id: 'revenueEditCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Чистая прибыль',
            id: 'profitEditCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Руководитель',
            id: 'directorEditCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Число сотруд.',
            id: 'number_of_emplayeesEditCompany',
            width: 530
        },
        {
            xtype: 'textarea',
            fieldLabel: 'Описание',
            id: 'aboutEditCompany',
            height: 100,
            width: 530
        },
        {
            xtype: 'textarea',
            fieldLabel: 'История',
            id: 'historyEditCompany',
            height: 100,
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Телефон',
            id: 'phoneEditCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Адресс',
            id: 'adressEditCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Эл. почта',
            id: 'emailEditCompany',
            width: 530,
            maskRe: /^([-_a-zA-Z0-9@.]{0,100})$/
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Сайт',
            id: 'siteEditCompany',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Руководство',
            id: 'guideEditCompany',
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
                    task: 'EDIT_COMPANY',
                    id: CompanyDataGrid.selModel.getSelections()[0].data.id,
                    name : Ext.getCmp('nameEditCompany').getValue(),
                    dob : Ext.getCmp('dobEditCompany').getValue(),
                    industry : Ext.getCmp('industryEditCompany').getValue(),
                    products : Ext.getCmp('productsEditCompany').getValue(),
                    revenue : Ext.getCmp('revenueEditCompany').getValue(),
                    profit  : Ext.getCmp('profitEditCompany').getValue(),
                    director : Ext.getCmp('directorEditCompany').getValue(),
                    number_of_emplayees : Ext.getCmp('number_of_emplayeesEditCompany').getValue(),
                    about : Ext.getCmp('aboutEditCompany').getValue(),
                    history : Ext.getCmp('historyEditCompany').getValue(),
                    adress :  Ext.getCmp('adressEditCompany').getValue(),
                    phone : Ext.getCmp('phoneEditCompany').getValue(),
                    email : Ext.getCmp('emailEditCompany').getValue(),
                    site : Ext.getCmp('siteEditCompany').getValue(),
                    guide : Ext.getCmp('guideEditCompany').getValue(),
                    img : Ext.getCmp('imgEditCompany').getValue()
                },
                success: function(response) {
                    switch(response.responseText) {
                        case '1':
                            Ext.MessageBox.alert('Внимание', 'Данные о комапнии успешно обновлены!');
                            CompanyDataStore.reload({
                                params: {
                                    start: Ext.getCmp('CompanyPagingToolbar').cursor,
                                    limit: countOfNumberPerPage
                                }
                            });
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

CompanyNewImageSelectorWindow = new Ext.Window({

    width: 400,
    height: 140,
    layout: "fit",
    title: 'Загрузка изображения',
    id: 'CompanyImageSelectorWindow',
    modal: true,
    border: false,
    closable: false,
    items: [ {
        xtype: 'form',
        id: 'CompanyNewUploadPanel',
        border: false,
        buttonAlign: 'center',
        labelWidth: 80,
        fileUpload: true,
        bodyStyle: 'padding: 10px 10px 0 10px;',
        items: [{
            xtype: 'fileuploadfield',
            emptyText: '',
            fieldLabel: 'Изображение',
            buttonText: 'Выберите файл',
            width: 280,
            name: 'img'
        }],
        buttons: [
        {
            text: 'Загрузить',
            handler: function() {
 
                Ext.getCmp('CompanyNewUploadPanel').getForm().submit({
                    url: apiURL,
                    params: {
                        task: 'UPLOAD_COMPANY_IMAGE'
                    },
                    waitMsg: 'Загрузка....',
                    success: function(form, o) {

                        obj = Ext.util.JSON.decode(o.response.responseText);
                        if (obj.failed == '0' && obj.uploaded != '0') {
                            Ext.Msg.alert('Внимание!', 'Изображение загружено');
                            document.getElementById("companyNewImageBox").src = '/images/company/' + obj.name;
                            Ext.getCmp('imgNewCompany').setValue(obj.name);
                            CompanyNewImageSelectorWindow.hide();
                        } else if (obj.uploaded == '0') {
                            Ext.Msg.alert('Внимание!', 'Изображение не загружено!');
                        } else {
                            Ext.Msg.alert('Внимание!',
                                obj.uploaded + ' files uploaded <br/>' +
                                obj.failed + ' Сбой при загрузке!');
                        }
                        Ext.getCmp('CompanyNewUploadPanel').getForm().reset();
                    }
                });
            }
        },
        {
            text: 'Очистить',
            handler: function() {
                Ext.getCmp('CompanyNewUploadPanel').getForm().reset();
            }
        }
        ]
    }
    ],
    buttons: [
    {
        text: 'Закрыть',
        handler: function() {
            CompanyNewImageSelectorWindow.hide();
        }
    }
    ]
});

CompanyEditImageSelectorWindow = new Ext.Window({

    width: 400,
    height: 140,
    layout: "fit",
    title: 'Загрузка изображения',
    id: 'CompanyEditImageSelectorWindow',
    modal: true,
    border: false,
    closable: false,
    items: [ {
        xtype: 'form',
        id: 'CompanyEditUploadPanel',
        border: false,
        buttonAlign: 'center',
        labelWidth: 80,
        fileUpload: true,
        bodyStyle: 'padding: 10px 10px 0 10px;',
        items: [{
            xtype: 'fileuploadfield',
            emptyText: '',
            fieldLabel: 'Изображение',
            buttonText: 'Выберите файл',
            width: 280,
            name: 'img'
        }],
        buttons: [
        {
            text: 'Загрузить',
            handler: function() {
 
                Ext.getCmp('CompanyEditUploadPanel').getForm().submit({
                    url: apiURL,
                    params: {
                        task: 'UPLOAD_COMPANY_IMAGE'
                    },
                    waitMsg: 'Загрузка....',
                    success: function(form, o) {

                        obj = Ext.util.JSON.decode(o.response.responseText);
                        if (obj.failed == '0' && obj.uploaded != '0') {
                            Ext.Msg.alert('Внимание!', 'Изображение загружено');
                            document.getElementById("companyEditImageBox").src = '/images/company/' + obj.name;
                            Ext.getCmp('imgEditCompany').setValue(obj.name);
                            CompanyEditImageSelectorWindow.hide();
                        } else if (obj.uploaded == '0') {
                            Ext.Msg.alert('Внимание!', 'Изображение не загружено!');
                        } else {
                            Ext.Msg.alert('Внимание!',
                                obj.uploaded + ' files uploaded <br/>' +
                                obj.failed + ' Сбой при загрузке!');
                        }
                        Ext.getCmp('CompanyEditUploadPanel').getForm().reset();
                    }
                });
            }
        },
        {
            text: 'Очистить',
            handler: function() {
                Ext.getCmp('CompanyEditUploadPanel').getForm().reset();
            }
        }
        ]
    }
    ],
    buttons: [
    {
        text: 'Закрыть',
        handler: function() {
            CompanyEditImageSelectorWindow.hide();
        }
    }
    ]
});

function deleteCompany() {

    var countSelectedCompanyItems = CompanyDataGrid.selModel.getCount();

    if(countSelectedCompanyItems == 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранную компанию?', confirmRemoveLinkedCompany);
    }
    else if(countSelectedCompanyItems > 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранные компании?', confirmRemoveLinkedCompany);
    }
    else {
        Ext.MessageBox.alert('Внимание!', 'Выберите хотя бы одну комапнию!');
    }
}

function confirmRemoveLinkedCompany(answer) {

    if(answer == 'yes')
    {
        removeCompany();
    }
}

function removeCompany()
{
    var selectedCompany = CompanyDataGrid.selModel.getSelections(),
    selectedCompanyIDs = [],
    countSelectedCompany = CompanyDataGrid.selModel.getCount();

    for(var i = 0; i < countSelectedCompany; i++) {
        selectedCompanyIDs.push(selectedCompany[i].json.id);
    }

    selectedCompany = Ext.encode(selectedCompanyIDs);
    Ext.Ajax.request({

        waitingMsg: 'Пожалуйста, подождите...',
        url: apiURL,
        params: {
            task: 'DELETE_COMPANY',
            company_ids: selectedCompany
        },
        success: function (response) {
            if(response.responseText > 0)
                CompanyDataStore.reload({
                    params: {
                        start: Ext.getCmp('CompanyPagingToolbar').cursor,
                        limit: countOfNumberPerPage
                    }
                });
            else
                Ext.MessageBox.alert('Ошибка!', 'Ошибка при удалении!');
        },
        failure: function (response) {
            Ext.MessageBox.alert('Ошибка!', 'Сбой подключения, попробуйте позже.');
        }
    });
}

function renderIcon(img) {
    if(img != '')
        return '<img width="50px" src="/images/company/' + img + '">';
}


CompanyDataStore.load();
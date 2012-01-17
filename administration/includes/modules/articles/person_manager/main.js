try {
    Ext.get('EditorWindow').remove();
    Ext.get('AddWindow').remove();
    Ext.get('PersonImageSelectorWindow').remove();
    Ext.get('Person2ArticleAddWindow').remove();
    Ext.get('Person2ArticleWindow').remove();

}
catch(e) {}

var PersonDataStore,
Person2ArticleStore,
Person2ArticleGrid,
PersonColumnModel,
PersonDataGrid,
Person2ArticleWindow,
Person2ArticleAddWindow,
ArticlesAllStore,
id_user = 0,
countOfNumberPerPage = 37,
apiURL = 'includes/modules/articles/person_manager/api.php',
RowEditor = new Ext.ux.grid.RowEditor({
    saveText: 'Обновить',
    cancelText: 'Отмена'
});

moduleDetails = 'Управление личностями';

Person2ArticleStore = new Ext.data.Store({
    id: 'Person2ArticleStore',
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        totalProperty: 'total',
        method: 'POST'
    }),
    baseParams: {
        task: 'GET_ARTICLES',
        id_user: id_user
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
        name: 'header',
        type: 'string',
        mapping: 'header'
    }
    ])
});

ArticlesAllStore = new Ext.data.Store({
    id: 'ArticlesAllStore',
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        totalProperty: 'total',
        method: 'POST'
    }),
    baseParams: {
        task: 'GET_ALL_ARTICLES'
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
        name: 'header',
        type: 'string',
        mapping: 'header'
    }
    ])
});

PersonDataStore =  new Ext.data.Store({
    id: 'PersonDataStore',
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        totalProperty: 'total',
        method: 'POST'
    }),
    baseParams: {
        task: 'GET_PERSON',
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
        name: 'sirname',
        type: 'string',
        mapping: 'sirname'
    },
    {
        name: 'lastname',
        type: 'string',
        mapping: 'lastname'
    },
    {
        name: 'dob',
        type: 'string',
        format: 'Y-m-d',
        mapping: 'dob'
    },
    {
        name: 'pob',
        type: 'string',
        mapping: 'pob'
    },
    {
        name: 'img',
        type: 'string',
        mapping: 'img'
    },
    {
        name: 'education',
        type: 'string',
        mapping: 'education'
    },
    {
        name: 'marital',
        type: 'string',
        mapping: 'marital'
    },
    {
        name: 'scope',
        type: 'string',
        mapping: 'scope'
    },
    {
        name: 'post',
        type: 'string',
        mapping: 'post'
    },
    {
        name: 'job',
        type: 'string',
        mapping: 'job'
    },
    {
        name: 'career',
        type: 'string',
        mapping: 'career'
    },
    {
        name: 'coordinates',
        type: 'string',
        mapping: 'coordinates'
    },
    {
        name: 'phone',
        type: 'string',
        mapping: 'phone'
    },
    {
        name: 'fax',
        type: 'string',
        mapping: 'fax'
    },
    {
        name: 'email',
        type: 'string',
        mapping: 'email'
    },
    {
        name: 'unknown_contact',
        type: 'string',
        mapping: 'unknown_contact'
    }
    ])

});

PersonColumnModel = new Ext.grid.ColumnModel({
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
        header: 'Имя',
        dataIndex: 'name',
        width: 200
    },
    {
        header: 'Фамилия',
        dataIndex: 'sirname',
        width: 200
    },
    {
        header: 'Отчество',
        dataIndex: 'lastname',
        width: 200
    },
    {
        header: 'Дата рождения',
        dataIndex: 'dob',
        format: 'Y-m-d',
        width: 100
    },
    {
        header: "Фото",
        width: 60,
        renderer:renderIcon,
        dataIndex: 'img'
    },
    {
        header: 'Место рорждения',
        id: 'pob',
        width: 150,
        hidden: true
    },
    {
        header: 'Cемейное положение',
        id: 'marital',
        width: 150,
        hidden: true
    },
    {
        header: 'Образование',
        id: 'education',
        width: 250,
        hidden: true
    },
    {
        header: 'Сфера деятельности',
        id: 'scope',
        width: 250,
        hidden: true
    },
    {
        header: 'Должность',
        id: 'post',
        width: 250,
        hidden: true
    },
    {
        header: 'Место работы',
        id: 'job',
        width: 250,
        hidden: true
    },
    {
        header: 'Этапы карьеры',
        id: 'career',
        width: 250,
        hidden: true
    },
    {
        header: 'Координаты',
        id: 'coordinates',
        width: 250,
        hidden: true
    },
    {
        header: 'Телефон',
        id: 'phone',
        width: 250,
        hidden: true
    },
    {
        header: 'Факс',
        id: 'fax',
        width: 250,
        hidden: true
    },
    {
        header: 'Электроння почта',
        id: 'email',
        width: 250,
        hidden: true
    },
    {
        header: 'Неизвестный контакт',
        id: 'unknown_contact',
        width: 250,
        hidden: true
    }
    ]
});

PersonDataGrid = new Ext.grid.EditorGridPanel({
    id: 'PersonGrid',
    store: PersonDataStore,
    cm: PersonColumnModel,
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
        text: 'Новая личность',
        handler:  function () {
          
          Ext.getCmp('AddPersonPanel').getForm().reset();
          
          AddWindow.show();
        }
    },
    {
        xtype: 'button',
        iconCls: 'remove',
        text: 'Удалить',
        handler: deletePerson
    },
    {
        xtype: 'button',
        iconCls: 'edit',
        text: 'Привязать к статье',
        handler:  function () {
            
            if(PersonDataGrid.selModel.getCount() > 0) {
                id_user = PersonDataGrid.selModel.getSelections()[0].data.id;
               
                Person2ArticleStore.reload({
                    params: {
                        task: 'GET_ARTICLES',
                        id_user: id_user
                    }
                });
                Person2ArticleStore.on('load', function() {
                    Person2ArticleWindow.add(Person2ArticleGrid);
                    Person2ArticleWindow.show();
                })
            } else {
                Ext.MessageBox.alert('Внимание', 'Вы не выбрали личность!');
            }
        }
    }
    ],
    bbar: new Ext.PagingToolbar({
        id: 'PersonPagingToolbar',
        pageSize: countOfNumberPerPage,
        store: PersonDataStore,
        displayInfo: true
    }),
    listeners: {
        celldblclick: function (t, rIndex, cIndex, e) {

            var data = PersonDataStore.getAt(rIndex);
            
            Ext.getCmp('EditPersonPanel').getForm().reset();
            
            Ext.getCmp('nameEdit').setValue(data.data.name);
            Ext.getCmp('sirnameEdit').setValue(data.data.sirname);
            Ext.getCmp('lastnameEdit').setValue(data.data.lastname);
            if(data.data.dob != '0000-00-00')
                Ext.getCmp('dobEdit').setValue(data.data.dob);
            Ext.getCmp('pobEdit').setValue(data.data.pob);
            Ext.getCmp('maritalEdit').setValue(data.data.marital);
            Ext.getCmp('educationEdit').setValue(data.data.education);
            Ext.getCmp('scopeEdit').setValue(data.data.scope);
            Ext.getCmp('postEdit').setValue(data.data.post);
            Ext.getCmp('jobEdit').setValue(data.data.job);
            Ext.getCmp('careerEdit').setValue(data.data.career);
            Ext.getCmp('coordinatesEdit').setValue(data.data.coordinates);
            Ext.getCmp('phoneEdit').setValue(data.data.phone);
            Ext.getCmp('faxEdit').setValue(data.data.fax);
            Ext.getCmp('emailEdit').setValue(data.data.email);
            Ext.getCmp('unknown_contactEdit').setValue(data.data.unknown_contact);
            Ext.getCmp('imgEdit').setValue(data.data.img);
            
            EditorWindow.show();

            if (data.data.img == '') {
                document.getElementById("personEditImageBox").src = "/images/persons/nophoto.jpg";
            }
            else {
                document.getElementById("personEditImageBox").src = '/images/persons/' + data.data.img;
            }
        }
    }
});

Person2ArticleWindow = new Ext.Window({
    
    border: false,
    width: 700,
    height: 700,
    title: 'Привязка личностей к статьям',
    closable: false,
    layout: 'fit',
    bodyStyle: {
        'background-color': '#FFFFFF'   
    },
    buttons: [
    {
        text: 'Закрыть',
        handler: function() {
            Person2ArticleWindow.hide();
        }
    }
    ]
})    

Person2ArticleGrid = new Ext.grid.GridPanel({ 
    border:true, 
    store: Person2ArticleStore,        
    cm: new Ext.grid.ColumnModel({ 
        columns: [
        {
            header: '#', 
            dataIndex: 'id',
            hidden: true
        },
        {
            header: 'Статьи связанные с личностью', 
            dataIndex: 'header',
            width: 600
        }
        ],
                                        
        defaultSortable: true   
    }),
    tbar: [
    {
        xtype: 'button',
        iconCls: 'add',
        text: 'Привязать к статье',
        handler:  function () {
            
            Person2ArticleAddWindow.show();

        }
    },
    {
        xtype: 'button',
        iconCls: 'remove',
        text: 'Удалить связь',
        handler: deleteArticle2Person
    }
    ]
}) 

Person2ArticleAddWindow = new Ext.Window({
    id: 'Person2ArticleAddWindow',
    title: 'Добавить связанную статью',
    width: 800,
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
            xtype: 'combo',
            fieldLabel: 'Выберете статью',
            id: 'articleSelect',
            store: ArticlesAllStore,
            width: 600,
            editable: false,
            triggerAction: 'all',
            selectOnFocus: true,
            displayField: 'header',
            valueField: 'id'
        }
        ],
        buttons: [
        {
            text: 'Привязать',
            iconCls: 'save',
            handler: function() {
                var id_personadd = PersonDataGrid.selModel.getSelections()[0].data.id;
  
                Ext.Ajax.request({
                    waitMsg: 'Подождите...',
                    url: apiURL,
                    method: 'POST',
                    params: {
                        task: 'ADD_ARTICLE2PERSON',
                        id_article: Ext.getCmp('articleSelect').getValue(),
                        id_person: id_personadd
                    },
                    success: function(response) {
                        switch(response.responseText) {
                            case '1':
                                Person2ArticleAddWindow.hide();
                                Person2ArticleStore.reload({
                                    params: {
                                        task: 'GET_ARTICLES',
                                        id_user: id_personadd
                                    }
                                });
                                break;
                            case '2':
                                Ext.MessageBox.alert('Внимание', 'Эта статья уже привязана к этой личности!');
                                break;
                            default:
                                Ext.MessageBox.alert('Внимание', 'Произошла ошибка!');
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
                Person2ArticleAddWindow.hide();
            }
        }
        ]
    }
    ]
});

AddWindow = new Ext.Window({
    id: 'AddWindow',
    title: 'Новая личность',
    width: 800,
    height: 700,
    layout: "fit",
    modal: true,
    border: false,
    closable: false,
    items: [
    {
        xtype: 'form',
        id: 'AddPersonPanel',
        border: false,
        bodyStyle: 'padding: 15 15',
        items: [
        {
            xtype: 'textfield',
            fieldLabel: 'Имя',
            id: 'nameNew',
            width: 530,
            allowBlank: false
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Фамилия',
            id: 'sirnameNew',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Отчество',
            id: 'lastnameNew',
            width: 530
        },
        {
            xtype: 'datefield',
            fieldLabel: 'Дата рождения',
            format: 'Y-m-d',
            id: 'dobNew'
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Место рождения',
            id: 'pobNew',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Сем. положение',
            id: 'maritalNew',
            width: 530
        },
        {
            xtype: 'textfield',
            id: 'imgNew',
            width: 530,
            hidden: true
        },
        {
            xtype: 'box',
            fieldLabel: 'Фото на данный момент',
            id: 'personNewImageBox',
            height: 100,
            autoEl:
            {
                tag: 'img',
                src: '/images/persons/nophoto.jpg'
            }
        },
        {
            xtype: 'button',
            id: 'ImgSelectBtnPerson',
            fieldLabel: 'Фото',
            text: 'Выберите файл',
            width: 200,
            handler: function()
            {
                PersonImageSelectorWindow.show();
            }
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Образование',
            id: 'educationNew',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Сфера деят-ти',
            id: 'scopeNew',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Должность',
            id: 'postNew',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Место работы',
            id: 'jobNew',
            width: 530
        },
        {
            xtype: 'textarea',
            fieldLabel: 'Этапы карьеры',
            id: 'careerNew',
            height: 100,
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Координаты',
            id: 'coordinatesNew',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Телефон',
            id: 'phoneNew',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Факс',
            id: 'faxNew',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Эл. почта',
            id: 'emailNew',
            width: 530,
            maskRe: /^([-_a-zA-Z0-9@.]{0,100})$/
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Неизв. контакт',
            id: 'unknown_contactNew',
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
                    task: 'ADD_PERSON',
                    img: Ext.getCmp('imgNew').getValue(),
                    name: Ext.getCmp('nameNew').getValue(),
                    sirname: Ext.getCmp('sirnameNew').getValue(),
                    lastname: Ext.getCmp('lastnameNew').getValue(),
                    dob: Ext.getCmp('dobNew').getValue(),
                    pob: Ext.getCmp('pobNew').getValue(),
                    marital: Ext.getCmp('maritalNew').getValue(),
                    education: Ext.getCmp('educationNew').getValue(),
                    scope: Ext.getCmp('scopeNew').getValue(),
                    post: Ext.getCmp('postNew').getValue(),
                    job: Ext.getCmp('jobNew').getValue(),
                    career: Ext.getCmp('careerNew').getValue(),
                    coordinates: Ext.getCmp('coordinatesNew').getValue(),
                    phone: Ext.getCmp('phoneNew').getValue(),
                    fax: Ext.getCmp('faxNew').getValue(),
                    email: Ext.getCmp('emailNew').getValue(),
                    unknown_contact: Ext.getCmp('unknown_contactNew').getValue()
                },
                success: function(response) {
                    switch(response.responseText) {
                        case '1':
                            Ext.MessageBox.alert('Внимание', 'Личность успешно создана!');
                            AddWindow.hide();
                            PersonDataStore.reload({
                                params: {
                                    start: Ext.getCmp('PersonPagingToolbar').cursor,
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
        id: 'EditPersonPanel',
        border: false,
        bodyStyle: 'padding: 15 15',
        items: [
        {
            xtype: 'textfield',
            fieldLabel: 'Имя',
            id: 'nameEdit',
            width: 530,
            allowBlank: false
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Фамилия',
            id: 'sirnameEdit',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Отчество',
            id: 'lastnameEdit',
            width: 530
        },
        {
            xtype: 'datefield',
            fieldLabel: 'Дата рождения',
            format: 'Y-m-d',
            id: 'dobEdit'
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Место рождения',
            id: 'pobEdit',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Сем. положение',
            id: 'maritalEdit',
            width: 530
        },
        {
            xtype: 'textfield',
            id: 'imgEdit',
            width: 530,
            hidden: true
        },
        {
            xtype: 'box',
            fieldLabel: 'Фото на данный момент',
            id: 'personEditImageBox',
            height: 100,
            autoEl:
            {
                tag: 'img',
                src: '/images/persons/nophoto.jpg'
            }
        },
        {
            xtype: 'button',
            id: 'ImgEditSelectBtnPerson',
            fieldLabel: 'Изображение',
            text: 'Выберите файл',
            width: 200,
            handler: function()
            {
                PersonEditImageSelectorWindow.show();
            }
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Образование',
            id: 'educationEdit',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Сфера деят-ти',
            id: 'scopeEdit',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Должность',
            id: 'postEdit',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Место работы',
            id: 'jobEdit',
            width: 530
        },
        {
            xtype: 'textarea',
            fieldLabel: 'Этапы карьеры',
            id: 'careerEdit',
            height: 100,
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Координаты',
            id: 'coordinatesEdit',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Телефон',
            id: 'phoneEdit',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Факс',
            id: 'faxEdit',
            width: 530
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Эл. почта',
            id: 'emailEdit',
            width: 530,
            maskRe: /^([-_a-zA-Z0-9@.]{0,100})$/
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Неизв. контакт',
            id: 'unknown_contactEdit',
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
                    task: 'EDIT_PERSON',
                    id: PersonDataGrid.selModel.getSelections()[0].data.id,
                    img: Ext.getCmp('imgEdit').getValue(),
                    name: Ext.getCmp('nameEdit').getValue(),
                    sirname: Ext.getCmp('sirnameEdit').getValue(),
                    lastname: Ext.getCmp('lastnameEdit').getValue(),
                    dob: Ext.getCmp('dobEdit').getValue(),
                    pob: Ext.getCmp('pobEdit').getValue(),
                    marital: Ext.getCmp('maritalEdit').getValue(),
                    education: Ext.getCmp('educationEdit').getValue(),
                    scope: Ext.getCmp('scopeEdit').getValue(),
                    post: Ext.getCmp('postEdit').getValue(),
                    job: Ext.getCmp('jobEdit').getValue(),
                    career: Ext.getCmp('careerEdit').getValue(),
                    coordinates: Ext.getCmp('coordinatesEdit').getValue(),
                    phone: Ext.getCmp('phoneEdit').getValue(),
                    fax: Ext.getCmp('faxEdit').getValue(),
                    email: Ext.getCmp('emailEdit').getValue(),
                    unknown_contact: Ext.getCmp('unknown_contactEdit').getValue()
                },
                success: function(response) {
                    switch(response.responseText) {
                        case '1':
                            Ext.MessageBox.alert('Внимание', 'Данные о личности успешно обновлены!');
                            PersonDataStore.reload({
                                params: {
                                    start: Ext.getCmp('PersonPagingToolbar').cursor,
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

PersonEditImageSelectorWindow = new Ext.Window({
    width: 400,
    height: 140,
    layout: "fit",
    title: 'Загрузка изображения',
    id: 'PersonEditImageSelectorWindow',
    modal: true,
    border: false,
    closable: false,
    items: [ {
        xtype: 'form',
        id: 'PersonEditUploadPanel',
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
 
                Ext.getCmp('PersonEditUploadPanel').getForm().submit({
                    url: apiURL,
                    params: {
                        task: 'UPLOAD_PERSON_IMAGE'
                    },
                    waitMsg: 'Загрузка....',
                    success: function(form, o) {

                        obj = Ext.util.JSON.decode(o.response.responseText);
                        if (obj.failed == '0' && obj.uploaded != '0') {
                            Ext.Msg.alert('Внимание!', 'Изображение загружено');
                            document.getElementById("personEditImageBox").src = '/images/persons/' + obj.name;
                            Ext.getCmp('imgEdit').setValue(obj.name);
                            PersonEditImageSelectorWindow.hide();
                        } else if (obj.uploaded == '0') {
                            Ext.Msg.alert('Внимание!', 'Изображение не загружено!');
                        } else {
                            Ext.Msg.alert('Внимание!',
                                obj.uploaded + ' files uploaded <br/>' +
                                obj.failed + ' Сбой при загрузке!');
                        }
                        Ext.getCmp('PersonEditUploadPanel').getForm().reset();
                    }
                });
            }
        },
        {
            text: 'Очистить',
            handler: function() {
                Ext.getCmp('PersonEditUploadPanel').getForm().reset();
            }
        }
        ]
    }
    ],
    buttons: [
    {
        text: 'Закрыть',
        handler: function() {
            PersonEditImageSelectorWindow.hide();
        }
    }
    ]
});


PersonImageSelectorWindow = new Ext.Window({
    width: 400,
    height: 140,
    layout: "fit",
    title: 'Загрузка изображения',
    id: 'PersonImageSelectorWindow',
    modal: true,
    border: false,
    closable: false,
    items: [ {
        xtype: 'form',
        id: 'PersonUploadPanel',
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
 
                Ext.getCmp('PersonUploadPanel').getForm().submit({
                    url: apiURL,
                    params: {
                        task: 'UPLOAD_PERSON_IMAGE'
                    },
                    waitMsg: 'Загрузка....',
                    success: function(form, o) {

                        obj = Ext.util.JSON.decode(o.response.responseText);
                        if (obj.failed == '0' && obj.uploaded != '0') {
                            Ext.Msg.alert('Внимание!', 'Изображение загружено');
                            document.getElementById("personNewImageBox").src = '/images/persons/' + obj.name;
                            Ext.getCmp('imgNew').setValue(obj.name);
                            PersonImageSelectorWindow.hide();
                        } else if (obj.uploaded == '0') {
                            Ext.Msg.alert('Внимание!', 'Изображение не загружено!');
                        } else {
                            Ext.Msg.alert('Внимание!',
                                obj.uploaded + ' files uploaded <br/>' +
                                obj.failed + ' Сбой при загрузке!');
                        }
                        Ext.getCmp('PersonUploadPanel').getForm().reset();
                    }
                });
            }
        },
        {
            text: 'Очистить',
            handler: function() {
                Ext.getCmp('PersonUploadPanel').getForm().reset();
            }
        }
        ]
    }
    ],
    buttons: [
    {
        text: 'Закрыть',
        handler: function() {
            PersonImageSelectorWindow.hide();
        }
    }
    ]
});

function articles2person() {
    var countSelectedItems = PersonDataGrid.selModel.getCount();

    if(countSelectedItems == 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранную личность?', confirmRemoveLinkedPersons);
    }
    else if(countSelectedItems > 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранные личности?', confirmRemoveLinkedPersons);
    }
    else {
        Ext.MessageBox.alert('Внимание!', 'Выберите хотя бы одну личность!');
    }
}

function deletePerson() {
    var countSelectedItems = PersonDataGrid.selModel.getCount();

    if(countSelectedItems == 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранную личность?', confirmRemoveLinkedPersons);
    }
    else if(countSelectedItems > 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранные личности?', confirmRemoveLinkedPersons);
    }
    else {
        Ext.MessageBox.alert('Внимание!', 'Выберите хотя бы одну личность!');
    }
}

function deleteArticle2Person() {

    if(Person2ArticleGrid.selModel.getCount() == 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить связь с выбранной статьёй?', confirmRemoveArticle2Person);
    }
    else {
        Ext.MessageBox.alert('Внимание!', 'Выберите одну статью!');
    }
}

function confirmRemoveLinkedPersons(answer) {

    if(answer == 'yes')
    {
        removePersons();
    }
}

function confirmRemoveArticle2Person(answer) {
    
    if(answer == 'yes')
    {
        removeArticle2Person();
    }
}

function removeArticle2Person() {
        
    var person2A = PersonDataGrid.selModel.getSelections()[0].data.id;
    var article2P = Person2ArticleGrid.selModel.getSelections()[0].data.id;
    
    Ext.Ajax.request({
        waitingMsg: 'Пожалуйста, подождите...',
        url: apiURL,
        params: {
            task: 'DELETE_ARTICLE2PERSON',
            person2A : person2A,
            article2P : article2P
        },
        success: function (response) {
            if(response.responseText > 0) {               
                Person2ArticleStore.reload({
                    params: {
                        task: 'GET_ARTICLES',
                        id_user: person2A
                    }
                });
            } else
                Ext.MessageBox.alert('Ошибка!', 'Ошибка при удалении!');
        },
        failure: function (response) {
            Ext.MessageBox.alert('Ошибка!', 'Сбой подключения, попробуйте позже.');
        }
    });   
}

function removePersons()
{
    var selectedPersons = PersonDataGrid.selModel.getSelections(),
    selectedPersonsIDs = [],
    countSelectedPersons = PersonDataGrid.selModel.getCount();

    for(var i = 0; i < countSelectedPersons; i++) {
        selectedPersonsIDs.push(selectedPersons[i].json.id);
    }

    selectedPersons = Ext.encode(selectedPersonsIDs);
    Ext.Ajax.request({

        waitingMsg: 'Пожалуйста, подождите...',
        url: apiURL,
        params: {
            task: 'DELETE_PERSON',
            archives_ids: selectedPersons
        },
        success: function (response) {
            if(response.responseText > 0)
                PersonDataStore.reload({
                    params: {
                        start: Ext.getCmp('PersonPagingToolbar').cursor,
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
        return '<img width="50px" src="/images/persons/' + img + '">';
}

PersonDataStore.load();
ArticlesAllStore.load();
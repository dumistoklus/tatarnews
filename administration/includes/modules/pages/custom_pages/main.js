try {
    Ext.get('EditCustomPageWindow').remove();
    Ext.get('AddCustomPageWindow').remove();
}
catch(e) {}

var CustomPagesDataStore,
CustomPagesColumnModel,
CustomPagesDataGrid,
countOfNumberPerPage = 37,
apiURL = 'includes/modules/pages/custom_pages/api.php',
RowEditor = new Ext.ux.grid.RowEditor({
    saveText: 'Обновить',
    cancelText: 'Отмена'
});

moduleDetails = 'Управление пользовательскими страницами';

CustomPagesDataStore =  new Ext.data.Store({
    id: 'CustomPagesDataStore',
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        totalProperty: 'total',
        method: 'POST'
    }),
    baseParams: {
        task: 'GET_PAGES',
        start: 0,
        limit: countOfNumberPerPage
    },
    reader: new Ext.data.JsonReader({
        root: 'results',
        id: 'id'
    },
    [
    {
        name: 'page_id',
        type: 'int',
        mapping: 'page_id'
    },
    {
        name: 'title',
        type: 'string',
        mapping: 'title'
    },
    {
        name: 'last_update',
        type: 'string',
        mapping: 'last_update'
    },
    {
        name: 'description',
        type: 'string',
        mapping: 'description'
    },
    {
        name: 'keywords',
        type: 'string',
        mapping: 'keywords'
    },
    {
        name: 'content',
        type: 'string',
        mapping: 'content'
    }
    ])

});

CustomPagesColumnModel = new Ext.grid.ColumnModel({
    defaults: {
        sortable: true
    },
    columns: [
    {
        header: 'Ссылка на страницу',
        readonly: true,
        dataIndex: 'page_id',
        width: 280,
        renderer: renderLink
    },
    {
        header: 'Название',
        dataIndex: 'title',
        width: 600,
        allowBlank: false
    },
    {
        header: 'Редактировалась',
        dataIndex: 'last_update',
        width: 120,
        hidden: true
    },
    {
        header: 'Описание',
        dataIndex: 'description',
        width: 200,
        hidden: true
    },
    {
        header: 'Ключевые слова',
        dataIndex: 'keywords',
        width: 200,
        hidden: true
    },
    {
        header: 'Контент',
        dataIndex: 'content',
        width: 120,
        hidden: true
    }
    ]
});

CustomPagesDataGrid = new Ext.grid.EditorGridPanel({
    id: 'CustomPagesDataGrid',
    store: CustomPagesDataStore,
    cm: CustomPagesColumnModel,
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
        text: 'Новая страница',
        handler:  function () {
            Ext.getCmp('AddCustomPagePanel').getForm().reset();
            AddCustomPageWindow.show();
        }
    },{
        xtype: 'button',
        iconCls: 'remove',
        text: 'Удалить',
        handler: deleteCustomPages
    }
    ],
    bbar: new Ext.PagingToolbar({
        id: 'CustomPagesPagingToolbar',
        pageSize: countOfNumberPerPage,
        store: CustomPagesDataStore,
        displayInfo: true
    }),
    listeners: {
        celldblclick: function (t, rIndex, cIndex, e) {

            Ext.getCmp('EditCustomPagePanel').getForm().reset();
            var data = CustomPagesDataStore.getAt(rIndex);
            Ext.getCmp('page_idEditCP').setValue('/?page=CustomPages&page_id='+data.data.page_id);
            Ext.getCmp('titleEditCP').setValue(data.data.title);
            Ext.getCmp('contentEditCP').setValue(data.data.content);
            Ext.getCmp('descriptionEditCP').setValue(data.data.description);
            Ext.getCmp('keywordsEditCP').setValue(data.data.keywords);
           
            EditCustomPageWindow.show();
        }
    }
});

AddCustomPageWindow = new Ext.Window({
    id: 'AddCustomPageWindow',
    title: 'Новая пользовательская страница',
    width: 750,
    height: 650,
    layout: "fit",
    modal: true,
    border: false,
    closable: false,
    items: [
    {
        xtype: 'form',
        id: 'AddCustomPagePanel',
        border: false,
        bodyStyle: 'padding: 15 15',
        items: [
        {
            xtype: 'textfield',
            fieldLabel: 'Название',
            id: 'titleNewCP',
            width: 600,
            allowBlank: false
        },
        {
            xtype: 'tinymce',
            fieldLabel: 'Контент',
            id: 'contentNewCP',
            allowBlank: false,
            width: 600,
            height: 450,
          /*  tinymceSettings: {
		theme : "advanced",
		plugins: "safari,pagebreak,style,layer,table,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : false,
		extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
	}*/
            tinymceSettings:
            {
                theme: "advanced",
                plugins: "style,layer,advimage,advlink,images,insertdatetime,preview,media,searchreplace,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras",
                theme_advanced_buttons1: "bold,italic,underline,strikethrough,|,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,charmap",
                theme_advanced_buttons2: "pastetext,pasteword,removeformat,|,search,replace,|,bullist,numlist,|,undo,redo,|,link,unlink,images,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                theme_advanced_buttons3: "",
                theme_advanced_toolbar_location: "top",
                theme_advanced_toolbar_align: "left",
                theme_advanced_statusbar_location: "bottom",
                theme_advanced_resizing: false,
		relative_urls : false,
		remove_script_host : true,
                extended_valid_elements: "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],font[face|size|color|style],span[class|align|style]"
            }
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Описание',
            id: 'descriptionNewCP',
            width: 600
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Ключевые слова',
            id: 'keywordsNewCP',
            width: 600
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
                    task: 'ADD_PAGE',

                    title : Ext.getCmp('titleNewCP').getValue(),
                    content : Ext.getCmp('contentNewCP').getValue(),
                    description : Ext.getCmp('descriptionNewCP').getValue(),
                    keywords : Ext.getCmp('keywordsNewCP').getValue()
                },
                success: function(response) {
                    switch(response.responseText) {
                        case '1':
                            Ext.MessageBox.alert('Внимание', 'Страница успешно создана!');
                            AddCustomPageWindow.hide();
                            CustomPagesDataStore.reload({
                                params: {
                                    start: Ext.getCmp('CustomPagesPagingToolbar').cursor,
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
            AddCustomPageWindow.hide();
        }
    }
    ]
});

EditCustomPageWindow = new Ext.Window({
    id: 'EditCustomPageWindow',
    title: 'Редактирование пользовательской страницы',
    width: 750,
    height: 650,
    layout: "fit",
    modal: true,
    border: false,
    closable: false,
    items: [
    {
        xtype: 'form',
        id: 'EditCustomPagePanel',
        border: false,
        bodyStyle: 'padding: 15 15',
        items: [
        {
            xtype: 'textfield',
            fieldLabel: 'Ссылка',
            id: 'page_idEditCP',
            readOnly: true,
            width: 600,
            allowBlank: false
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Название',
            id: 'titleEditCP',
            width: 600,
            allowBlank: false
        },
        {
            xtype: 'tinymce',
            fieldLabel: 'Контент',
            id: 'contentEditCP',
            allowBlank: false,
            width: 600,
            height: 450,
            tinymceSettings:
            {
                theme: "advanced",
                plugins: "style,layer,advimage,advlink,images,insertdatetime,preview,media,searchreplace,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras",
                theme_advanced_buttons1: "bold,italic,underline,strikethrough,|,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,charmap",
                theme_advanced_buttons2: "pastetext,pasteword,removeformat,|,search,replace,|,bullist,numlist,|,undo,redo,|,link,unlink,images,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                theme_advanced_buttons3: "",
                theme_advanced_toolbar_location: "top",
                theme_advanced_toolbar_align: "left",
                theme_advanced_statusbar_location: "bottom",
                theme_advanced_resizing: false,
		relative_urls : false,
		remove_script_host : true,
                extended_valid_elements: "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],font[face|size|color|style],span[class|align|style]"
            }
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Описание',
            id: 'descriptionEditCP',
            width: 600
        },
        {
            xtype: 'textfield',
            fieldLabel: 'Ключевые слова',
            id: 'keywordsEditCP',
            width: 600
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
                    task: 'EDIT_PAGE',
                    
                    page_id: CustomPagesDataGrid.selModel.getSelections()[0].data.page_id,
                    title : Ext.getCmp('titleEditCP').getValue(),
                    content : Ext.getCmp('contentEditCP').getValue(),
                    description : Ext.getCmp('descriptionEditCP').getValue(),
                    keywords : Ext.getCmp('keywordsEditCP').getValue()
                },
                success: function(response) {
                    switch(response.responseText) {
                        case '1':
                            Ext.MessageBox.alert('Внимание', 'Страница успешно изменена!');
                            AddCustomPageWindow.hide();
                            CustomPagesDataStore.reload({
                                params: {
                                    start: Ext.getCmp('CustomPagesPagingToolbar').cursor,
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
            EditCustomPageWindow.hide();
        }
    }
    ]
});

function renderLink(page_id) {
    return '<a href="/?page=CustomPages&page_id='+page_id+'" target="_blank">/?page=CustomPages&page_id='+page_id+'</a>';
}


function deleteCustomPages() {

    var countSelectedCustomPagesItems = CustomPagesDataGrid.selModel.getCount();

    if(countSelectedCustomPagesItems == 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранную страницу?', confirmRemoveLinkedCustomPages);
    }
    else if(countSelectedCustomPagesItems > 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранные страницу?', confirmRemoveLinkedCustomPages);
    }
    else {
        Ext.MessageBox.alert('Внимание!', 'Выберите хотя бы одну страницу!');
    }
}

function confirmRemoveLinkedCustomPages(answer) {

    if(answer == 'yes')
    {
        removeCustomPages();
    }
}

function removeCustomPages()
{
    var selectedCustomPages = CustomPagesDataGrid.selModel.getSelections(),
    selectedCustomPagesIDs = [],
    countSelectedCustomPages = CustomPagesDataGrid.selModel.getCount();

    for(var i = 0; i < countSelectedCustomPages; i++) {
        selectedCustomPagesIDs.push(selectedCustomPages[i].json.page_id);
    }

    selectedCustomPages = Ext.encode(selectedCustomPagesIDs);
    Ext.Ajax.request({

        waitingMsg: 'Пожалуйста, подождите...',
        url: apiURL,
        params: {
            task: 'DELETE_PAGES',
            page_ids: selectedCustomPages
        },
        success: function (response) {
            if(response.responseText > 0)
                CustomPagesDataStore.reload({
                    params: {
                        start: Ext.getCmp('CustomPagesPagingToolbar').cursor,
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

CustomPagesDataStore.load();
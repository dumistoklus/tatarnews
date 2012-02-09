try {
    Ext.get('EditorWindow').remove();
}
catch(e) {}

var ManagerPanel,
    EditorWindow,
    NewsStore,
    ColumnModel,
    countOfNewsPerPage = 37,
    apiURL = 'includes/modules/articles/news_manager/api.php';


NewsStore = new Ext.data.Store({
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        method: 'POST'
    }),
    baseParams: {
        task: 'GET_NEWS',
        start: 0,
        limit: countOfNewsPerPage
    },
    reader: new Ext.data.JsonReader({
        root: 'results',
        totalProperty: 'total',
        id: 'id'
    },
    [
        { name: 'id', type: 'int', mapping: 'id' },
        { name: 'short_news', type: 'string', mapping: 'short_news' },
        { name: 'text', type: 'text', mapping: 'text' },
        { name: 'date', type: 'date', mapping: 'date', dateFormat: 'timestamp'}
    ])
});

EditorWindow = new Ext.Window({
    title: 'Редактирование новости',
    width: 700,
    height: 640,
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
                    xtype: 'textarea',
                    fieldLabel: 'Описание',
                    id: 'NewsPreview',
                    width: 650,
                    height: 100
                },
                {
                    xtype: 'tinymce',
                    fieldLabel: 'Подробно',
                    id: 'NewsText',
                    width: 650,
                    height: 400,
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
                    xtype: 'xdatetime',
                    fieldLabel: 'Время',
                    id: 'NewsDate'
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
                        task: 'EDIT_NEWS',
                        nid: ManagerPanel.selModel.getSelections()[0].data.id,
                        preview: Ext.getCmp('NewsPreview').getValue(),
                        text: Ext.getCmp('NewsText').getValue(),
                        date: Ext.getCmp('NewsDate').getValue().format('U')
                    },
                    success: function(response) {
                        switch(response.responseText) {
                            case '1':
                                Ext.MessageBox.alert('Внимание', 'Новость успешно обновлена!');
                                    
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
                EditorWindow.hide();
            }
        }
    ]
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
            width: 50
        },
        {
            header: 'Описание',
            dataIndex: 'short_news',
            readOnly: true,
            width: 260
        },
        {
            header: 'Подробно',
            dataIndex: 'text',
            readOnly: true,
            width: 400
        },
        {
            header: 'Дата',
            dataIndex: 'date',
            readOnly: true,
            width: 100,
            renderer: function (sTimestamp){
                if (typeof sTimestamp == "string") {
                    sTimestamp = parseInt(sTimestamp);
                }

                var dtDate = new Date(sTimestamp);

                return Ext.util.Format.date(dtDate, 'd.m.Y');
            }
        }
    ]
});

ManagerPanel = new Ext.grid.EditorGridPanel({
    store: NewsStore,
    cm: ColumnModel,
    selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
    enableColLock: false,
    anchor: '100% 100%',
    border: false,
    bbar: new Ext.PagingToolbar({
        id: 'NewsPagingToolbar',
        pageSize: countOfNewsPerPage,
        store: NewsStore,
        displayInfo: true
    }),
    tbar: [
    {
        xtype: 'button',
        iconCls: 'remove',
        text: 'Удалить',
        handler: deleteNews
    }
    ],
    listeners: {
        celldblclick: function (t, rIndex, cIndex, e) {

            var data = NewsStore.getAt(rIndex);

            Ext.getCmp('NewsPreview').setValue(data.data.short_news);
            Ext.getCmp('NewsText').setValue(data.data.text);
            Ext.getCmp('NewsDate').setValue(data.data.date);
            EditorWindow.show();
        }
    }
});

function deleteNews() {

    var countSelectedNewsItems = ManagerPanel.selModel.getCount();
    
    if(countSelectedNewsItems == 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранную новость?', confirmRemoveLinkedNews);
    }
    else if(countSelectedCompanyItems > 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранные новости?', confirmRemoveLinkedNews);
    }
    else {
        Ext.MessageBox.alert('Внимание!', 'Выберите хотя бы одну новость!');
    }
}

function confirmRemoveLinkedNews(answer) {

    if(answer == 'yes')
    {
        removeNews();
    }
}

function removeNews()
{
    var selectedNews = ManagerPanel.selModel.getSelections(),
    selectedNewsIDs = [],
    countSelectedNews = ManagerPanel.selModel.getCount();

    for(var i = 0; i < countSelectedNews; i++) {
        selectedNewsIDs.push(selectedNews[i].json.id);
    }

    selectedNews = Ext.encode(selectedNewsIDs);
    Ext.Ajax.request({

        waitingMsg: 'Пожалуйста, подождите...',
        url: apiURL,
        params: {
            task: 'DELETE_NEWS',
            news_ids: selectedNews
        },
        success: function (response) {
            if(response.responseText > 0)
                NewsStore.reload();
            else
                Ext.MessageBox.alert('Ошибка!', 'Ошибка при удалении!');
        },
        failure: function (response) {
            Ext.MessageBox.alert('Ошибка!', 'Сбой подключения, попробуйте позже.');
        }
    });
}

NewsStore.load();
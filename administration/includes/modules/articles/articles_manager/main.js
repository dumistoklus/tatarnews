try {
    Ext.get('ImageSelectorWindow').remove();
}
catch(e) {}
Ext.DataView.DragSelector = function(cfg){
    cfg = cfg || {};
    var view, proxy, tracker;
    var rs, bodyRegion, dragRegion = new Ext.lib.Region(0,0,0,0);
    var dragSafe = cfg.dragSafe === true;

    this.init = function(dataView){
        view = dataView;
        view.on('render', onRender);
    };

    function fillRegions(){
        rs = [];
        view.all.each(function(el){
            rs[rs.length] = el.getRegion();
        });
        bodyRegion = view.el.getRegion();
    }

    function cancelClick(){
        return false;
    }

    function onBeforeStart(e){
        return !dragSafe || e.target == view.el.dom;
    }

    function onStart(e){
        view.on('containerclick', cancelClick, view, {single:true});
        if(!proxy){
            proxy = view.el.createChild({cls:'x-view-selector'});
        }else{
            if(proxy.dom.parentNode !== view.el.dom){
                view.el.dom.appendChild(proxy.dom);
            }
            proxy.setDisplayed('block');
        }
        fillRegions();
        view.clearSelections();
    }

    function onDrag(e){
        var startXY = tracker.startXY;
        var xy = tracker.getXY();

        var x = Math.min(startXY[0], xy[0]);
        var y = Math.min(startXY[1], xy[1]);
        var w = Math.abs(startXY[0] - xy[0]);
        var h = Math.abs(startXY[1] - xy[1]);

        dragRegion.left = x;
        dragRegion.top = y;
        dragRegion.right = x+w;
        dragRegion.bottom = y+h;

        dragRegion.constrainTo(bodyRegion);
        proxy.setRegion(dragRegion);

        for(var i = 0, len = rs.length; i < len; i++){
            var r = rs[i], sel = dragRegion.intersect(r);
            if(sel && !r.selected){
                r.selected = true;
                view.select(i, true);
            }else if(!sel && r.selected){
                r.selected = false;
                view.deselect(i);
            }
        }
    }

    function onEnd(e){
        if (!Ext.isIE) {
            view.un('containerclick', cancelClick, view);
        }
        if(proxy){
            proxy.setDisplayed(false);
        }
    }

    function onRender(view){
        tracker = new Ext.dd.DragTracker({
            onBeforeStart: onBeforeStart,
            onStart: onStart,
            onDrag: onDrag,
            onEnd: onEnd
        });
        tracker.initEl(view.el);
    }
};

var apiURL = 'includes/modules/articles/articles_manager/api.php',
    ArticlesPanel,
    EditArticlePanel,
    ArticlesListCM,
    ArticlesDataStore,
    countOfNumberPerPage = 38,
    ArticleImage,
    settingsApiURL = 'includes/modules/articles/new_article/api.php';

var EditImagesStore = new Ext.data.JsonStore({
    proxy: new Ext.data.HttpProxy({
        url: settingsApiURL,
        method: 'POST'
    }),
    baseParams: {task: 'GET_IMAGES'},
    root: 'images',
    fields: [
        'name', 'url',
        { name: 'size', type: 'float' },
        { name: 'lastmod', type: 'date', dateFormat: 'timestamp' },
        'thumb_url'
    ]
});

ArticlesDataStore = new Ext.data.Store({
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        method: 'POST'
    }),
    baseParams: {task: "GET_ARTICLES", start: 0, limit: countOfNumberPerPage},
    reader: new Ext.data.JsonReader({
        root: 'results',
        totalProperty: 'total',
        id: 'id'
    },
    [
        {name: 'id', type: 'int', mapping: 'id'},
        {name: 'header', type: 'string', mapping: 'header'},
        {name: 'cat_name', type: 'string', mapping: 'cat_name' },
        {name: 'cat_id', type: 'int', mapping: 'cat_id'},
        {name: 'create_date', type: 'date', mapping: 'create_date', dateFormat: 'timestamp'},
        {name: 'archive_name', type: 'string', mapping: 'archive_name'},
        {name: 'archive_id', type: 'int', mapping: 'archive_id'},
        {name: 'main_event', type: 'bool', mapping: 'main_event'},
        {name: 'third_col', type: 'int', mapping: 'third_col'}
    ])
});

var SelectedArticleStore = new Ext.data.Store({
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        method: 'POST'
    }),
    baseParams: {task: "GET_ARTICLE"},
    reader: new Ext.data.JsonReader({
        root: 'results',
        totalProperty: 'total',
        id: 'id'
    },
    [
        {name: 'id', type: 'int', mapping: 'id'},
        {name: 'header', type: 'string', mapping: 'header'},
        {name: 'lid', type: 'string', mapping: 'lid' },
        {name: 'create_date', type: 'date', mapping: 'create_date', dateFormat: 'timestamp'},
        {name: 'preview', type: 'string', mapping: 'preview' },
        {name: 'content', type: 'string', mapping: 'content' },
        {name: 'image', type: 'string', mapping: 'image' },
        {name: 'source', type: 'string', mapping: 'source' }

    ])
});

ArticlesListCM = new Ext.grid.ColumnModel({
	defaults: {
		sortable: true
	},
	columns: [
		{
			header: '#',
			readOnly: true,
			dataIndex: 'id',
			width: 50
		},
		{
			header: 'Заголовок статьи',
			readOnly: true,
			dataIndex: 'header',
			width: 620
		},
		{
			header: 'Категория',
			readOnly: true,
			dataIndex: 'cat_name',
			width: 100
		},
        {
			header: 'Время создания',
			readOnly: true,
			dataIndex: 'create_date',
            renderer: formatDate,
			width: 100
		},
        {
			header: 'Архив',
			readOnly: true,
			dataIndex: 'archive_name',
			width: 100
		},
        {
            header: '3 колонка',
            readOnly: true,
            dataIndex: 'third_col',
            width: 100
        }
	]
});
var CategoryStore = new Ext.data.Store({
    proxy: new Ext.data.HttpProxy({
        url: settingsApiURL,
        method: 'POST'
    }),
    baseParams: {task: 'GET_CATS'},
    reader: new Ext.data.JsonReader({
        root: 'results',
        totalProperty: 'total',
        id: 'id'
    },
    [
        {name: 'id', type: 'int', mapping: 'id'},
        {name: 'name', type: 'string', mapping: 'name'}
    ]),
    sortInfo: {field: 'name', direction: 'ASC'}
});

var NewsPaperArchiveStore = new Ext.data.Store({
    proxy: new Ext.data.HttpProxy({
        url: settingsApiURL,
        method: 'POST'
    }),
    baseParams: {task: 'GET_ARCHIVE', getAll: true},
    reader: new Ext.data.JsonReader({
        root: 'results',
        totalProperty: 'total',
        id: 'id'
    },
    [
        {name: 'id', type: 'int', mapping: 'id'},
        {name: 'name', type: 'string', mapping: 'name'}
    ]),
    sortInfo: {field: 'id', direction: 'DESC'}
});

var EditImageSelector = new Ext.Window({
    title: 'Изображения',
    width: 700,
    height: 500,
    layout: "fit",
    id: 'EditImageSelector',
    modal: true,
    border: false,
    closable: false,
    items: [
        {
            xtype: 'panel',
            layout: 'border',
            id: 'images-view',
            height: '100%',
            border: false,
            width: 400,
            items: [
                {
                    xtype: 'panel',
                    region: 'center',
                    id: 'DataViewPanel',
                    height: '100%',
                    tbar: [
                        {
                            text: 'Выбрать',
                            iconCls: 'add',
                            handler: function()
                            {
                                var image = Ext.getCmp('EditImagesData').getSelectedRecords();
                                if(image.length > 1)
                                {
                                    Ext.MessageBox.alert("Внимание!", "Можно выбрать только одну фотографию!");
                                }
                                else if(image.length == 1)
                                {
                                    ArticleImage = image[0].data.name;
                                    EditImageSelector.hide();
                                    document.getElementById("ImagePreviewBox").src = "/images/articles_header/"+image[0].data.name;
                                }
                                else {
                                    Ext.MessageBox.alert("Внимание!", "Выберите фотографию!");
                                }
                            }
                        },
                        '->',
                        {
                            text: 'Удалить изображение',
                            iconCls: 'remove',
                            handler: function() {
                                var records = Ext.getCmp('EditImagesData').getSelectedRecords();
                                if (records.length != 0) {
                                    var imgName = '';
                                    for (var i = 0; i < records.length; i++) {
                                        imgName = imgName + records[i].data.name + ';';
                                    }
                                    Ext.Ajax.request({
                                       url: apiURL,
                                       method: 'post',
                                       params: { task: 'DELETE_IMAGE', images: imgName},
                                       success: function() {
                                           EditImagesStore.load();
                                       }
                                    });
                                }
                            }
                        }
                    ],
                    frame: true,
                    items: new Ext.DataView({
                        store: EditImagesStore,
                        autoScroll: true,
                        height: '100%',
                        border: false,
                        id: 'EditImagesData',
                        tpl: new Ext.XTemplate( '<tpl for=".">',
                                                    '<div class="thumb-wrap" id="{name}">',
                                                    '<div class="thumb"><img src="{thumb_url}" title="{name}"></div>',
                                                    '<span class="x-editable">{shortName}</span></div>',
                                                '</tpl>',
                                                '<div class="x-clear"></div>'),
                        multiSelect: true,
                        autoHeight: false,
                        overClass: 'x-view-over',
                        emptyText: 'No images to display',
                        itemSelector: 'div.thumb-wrap',
                        style: 'border:1px solid #99BBE8; border-top-width: 0',

                        plugins: [
                            new Ext.DataView.DragSelector()
                        ],

                        prepareData: function(data){
                            data.shortName = Ext.util.Format.ellipsis(data.name, 15);
                            data.sizeString = Ext.util.Format.fileSize(data.size);
                            data.dateString = data.lastmod.format("m/d/Y g:i a");
                            return data;
                        },

                        listeners: {
                            selectionchange: {
                                fn: function(dv,nodes){
                                    var l = nodes.length;
                                    var s = l != 1 ? 's' : '';
                                    Ext.getCmp('DataViewPanel').setTitle('Simple DataView Gallery ('+l+' image'+s+' selected)');
                                }
                            },
                            click: {
                                fn: function() {
                                    var selNode = Ext.getCmp('EditImagesData').getSelectedRecords();
                                    Ext.getCmp('panelDetail').tpl.overwrite(Ext.getCmp('panelDetail').body, selNode[0].data);
                                }
                            }

                        }
                    })
                },
                {
                    xtype: 'panel',
                    region: 'east',
                    border: false,
                    width: 300,
                    layout: 'border',
                    items: [
                        {
                            xtype: 'panel',
                            region: 'center',
                            title: 'Описание изображения',
                            id: 'panelDetail',
                            tpl: new Ext.XTemplate(
                                '<div class="details">',
                                    '<tpl for=".">',
                                        '<img src="{thumb_url}"><div class="details-info">',
                                        '<br /><b>Image Name:</b>',
                                        '<br /><span>{name}</span>',
                                        '<br /><b>Size:</b>',
                                        '<br /><span>{sizeString}</span>',
                                        '<br /><b>Last Modified:</b>',
                                        '<br /><span>{dateString}</span>',
                                        '<br /><span><a href="{url}" target="_blank">view original</a></span></div>',
                                    '</tpl>',
                                '</div>')
                        },
                        {
                            xtype: 'panel',
                            height: 150,
                            region: 'north',
                            items:
                            [
                                {
                                    xtype: 'form',
                                    title: 'Загрузка изображения',
                                    id: 'UploadPanel',
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
                                        width: 180,
                                        name: 'img'
                                    }],
                                    buttons: [
                                        {
                                            text: 'Загрузить',
                                            handler: function() {
                                                Ext.getCmp('UploadPanel').getForm().submit({
                                                    url: settingsApiURL,
                                                    params: {task: 'UPLOAD_FILE'},
                                                    method: 'POST',
                                                    waitMsg: 'Загрузка....',
                                                    success: function(form, o) {

                                                        obj = Ext.util.JSON.decode(o.response.responseText);
                                                        if (obj.failed == '0' && obj.uploaded != '0') {
                                                            Ext.Msg.alert('Внимание!', 'Изображение загружено');
                                                        } else if (obj.uploaded == '0') {
                                                            Ext.Msg.alert('Внимание!', 'Изображение не загружено!');
                                                        } else {
                                                            Ext.Msg.alert('Внимание!',
                                                                obj.uploaded + ' files uploaded <br/>' +
                                                                obj.failed + ' Сбой при загрузке!');
                                                        }
                                                        Ext.getCmp('UploadPanel').getForm().reset();
                                                        EditImagesStore.load();
                                                    }
                                                });
                                            }
                                        },
                                        {
                                            text: 'Очистить',
                                            handler: function() {
                                                Ext.getCmp('UploadPanel').getForm().reset();
                                            }
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ],
    buttons: [
        {
            text: 'Закрыть',
            handler: function() {
                EditImageSelector.hide();
            }
        }
    ]
});

ArticlesEditorPanel = new Ext.TabPanel({
    activeTab: 0,
    collapsed: true,
    animCollapse: false,
    anchor: '100% -25px',
    listeners: {
        collapse: function(p) {
            if(ArticlesPanel.collapsed){
                ArticlesPanel.expand();
                ArticleImage = '';
                Ext.getCmp('ArticleHeader').setValue("");
                Ext.getCmp('ArticleLid').setValue("");
                Ext.getCmp('ArticlePreview').setValue("");
                Ext.getCmp('ArticleContent').setValue("");
                Ext.getCmp('ArticleSource').setValue("");
                Ext.getCmp('ArticleCat').setValue("");
                Ext.getCmp('ArticleArchive').setValue("");
            }
        }
    },
    items: [
        {
            title: 'Редактирование содержания',
            xtype: 'form',
            border: false,
            bodyStyle: 'overflow-y: auto; padding: 15 15',
            items: [
                {
                    xtype: 'datefield',
                    fieldLabel: 'Дата создания',
                    format: 'Y-m-d',
                    id: 'ArticleDate',
                    width: 100
                },
                {
                    xtype: 'timefield',
                    fieldLabel: 'Время создания',
                    format: 'H:i',
                    id: 'ArticleTime',
                    width: 100
                },
                {
                    xtype:'textfield',
                    fieldLabel: 'Название статьи',
                    width: 700,
                    id: 'ArticleHeader'
                },

                {
                    xtype: 'textarea',
                    fieldLabel: 'Лит',
                    id: 'ArticleLid',
                    width: 700,
                    height: 100
                },
                {
                    xtype: 'tinymce',
                    fieldLabel: 'Краткое описание',
                    id: 'ArticlePreview',
                    width: 700,
                    height: 300,
                    autoHeight: false,
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
                    xtype: 'tinymce',
                    fieldLabel: 'Содержание',
                    id: 'ArticleContent',
                    width: 700,
                    height: 700,
                    bodyStyle: 'margin-bottom: 15px',
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
                }
            ]
        },
        {
            xtype: 'form',
            title: 'Дополнительные настройки',
            id: 'ExtSettings',
            bodyStyle: "padding: 15 15",
            items: [
                {
                    xtype: 'textfield',
                    fieldLabel: 'Источник статьи',
                    id: 'ArticleSource',
                    width: 200
                },
                {
                    xtype: 'button',
                    id: 'ImgSelectBtn',
                    fieldLabel: 'Изображение',
                    text: 'Выберите файл',
                    width: 200,
                    handler: function()
                    {
                        EditImageSelector.show();
                    }
                },
                {
                    xtype: 'combo',
                    fieldLabel: 'Рубрика',
                    id: 'ArticleCat',
                    store: CategoryStore,
                    width: 200,
                    editable: false,
                    triggerAction: 'all',
                    selectOnFocus: true,
                    displayField: 'name',
                    valueField: 'id'
                },
                {
                    xtype: 'combo',
                    fieldLabel: 'Архив',
                    id: 'ArticleArchive',
                    store: NewsPaperArchiveStore,
                    width: 200,
                    editable: false,
                    triggerAction: 'all',
                    selectOnFocus: true,
                    displayField: 'name',
                    valueField: 'id'
                },
                {
                    xtype: 'checkbox',
                    fieldLabel: 'Главное событие',
                    id: 'MainEvent',
                    enabled: false
                },
                {
                    xtype: 'box',
                    id: 'ImagePreviewBox',
                    width: 100,
                    height: 100,
                    cls: 'article-image-preview',
                    autoEl: {tag: 'img', src: '/images/articles_header/nophoto.jpg' }
                }
            ]
        }
    ],
    tbar: [
        {
            text: 'Вернуться к списку статей',
            iconCls: 'edit',
            handler: function() {
                ArticlesEditorPanel.collapse();
            }
        },
        {
            text: 'Сохранить изменения',
            iconCls: 'save',
            handler: function() {
                 Ext.Ajax.request({
                    url: apiURL,
                    method: 'POST',
                    waitMsg: 'Подождите...',
                    params: {
                        task: 'SAVE_ARTICLE',
                        aid: ArticlesPanel.selModel.getSelections()[0].data.id,
                        image: ArticleImage,
                        header: Ext.getCmp('ArticleHeader').getValue(),
                        lid: Ext.getCmp('ArticleLid').getValue(),
                        preview: Ext.getCmp('ArticlePreview').getValue(),
                        created_date: Ext.getCmp('ArticleDate').getValue(),
                        created_time: Ext.getCmp('ArticleTime').getValue(),
                        content: Ext.getCmp('ArticleContent').getValue(),
                        source: Ext.getCmp('ArticleSource').getValue(),
                        cat: Ext.getCmp('ArticleCat').getValue(),
                        archive: Ext.getCmp('ArticleArchive').getValue(),
                        mainevent: Ext.getCmp('MainEvent').getValue()
                    },
                    success: function(response) {
                        switch(response.responseText) {
                            case '1':
                                Ext.MessageBox.alert('Внимание!', 'Статья успешно изменена!');
                                ArticlesDataStore.reload();
                                ArticlesEditorPanel.collapse();
                                break;
                            case 'not_event':
                                Ext.MessageBox.alert('Внимание!', 'Страница сохранена, но главное событие не бло создано!');
                                break;
                            default:
                                Ext.MessageBox.alert('Внимание!', 'Во время изменения произошла ошибка!');
                                break;
                        }
                    },
                    failure: function(response) {
                        Ext.MessageBox.alert('Внимание!', 'Проверьте соединение!');
                    }
                });

            }
        }
    ]
});
ArticlesPanel = new Ext.grid.EditorGridPanel({
    id: 'ArticlesPanel',
    store: ArticlesDataStore,
    cm: ArticlesListCM,
    enableColLock: false,
    anchor: '100% 100%',
    border: false,
    title: 'Список статей',
    selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
    bbar: new Ext.PagingToolbar({
        id: 'ArticlesPagingToolbar',
        pageSize: countOfNumberPerPage,
        store: ArticlesDataStore,
        displayInfo: true
    }),
    tbar: [
    {
        xtype: 'button',
        iconCls: 'remove',
        text: 'Удалить',
        handler: deleteArticle
    },
    {
        xtype: 'button',
        iconCls: 'add',
        text: 'Третья колонка',
        handler: thirdCol
    }
    ],
    listeners: {
        celldblclick: function (t, rIndex, cIndex, e) {

            ArticlesPanel.collapse();
            Ext.MessageBox.progress('Подождите...', 'Загружаются данные статьи');
            Ext.MessageBox.updateProgress(.3);

            var row = ArticlesDataStore.getAt(rIndex);
            SelectedArticleStore.reload({params: {articleId: row.data.id}});

            SelectedArticleStore.on('load', function() {
                Ext.MessageBox.updateProgress(.8);

                var data = SelectedArticleStore.getAt(0);

                Ext.getCmp('ArticleHeader').setValue(data.data.header);
                Ext.getCmp('ArticleLid').setValue(data.data.lid);
                Ext.getCmp('ArticleDate').setValue(data.data.create_date);
                Ext.getCmp('ArticleTime').setValue(data.data.create_date);

                Ext.getCmp('ArticlePreview').setValue(data.data.preview);
                Ext.getCmp('ArticleContent').setValue(data.data.content);

                Ext.getCmp('ArticleSource').setValue(data.data.source);
                Ext.getCmp('ArticleCat').setValue(row.data.cat_id);
                Ext.getCmp('ArticleArchive').setValue(row.data.archive_id);
                ArticlesEditorPanel.activate(1);
                
                if (data.data.image == '') {
                    document.getElementById("ImagePreviewBox").src = "/images/articles_header/nophoto.jpg";
                }
                else {
                    document.getElementById("ImagePreviewBox").src = '/images/articles_header/' + data.data.image;
                }

                Ext.MessageBox.updateProgress(1);
                Ext.MessageBox.hide();
                ArticlesEditorPanel.activate(0);
                ArticlesEditorPanel.expand();
                Ext.getCmp('ArticlePreview').syncSize();
                Ext.getCmp('ArticleContent').syncSize();
            });
        }
    }
});
ArticlesDataStore.load();
EditImagesStore.load();
CategoryStore.load();
NewsPaperArchiveStore.load();

function formatDate(sTimestamp){
    if (typeof sTimestamp == "string") {
        sTimestamp = parseInt(sTimestamp);
    }

    var dtDate = new Date(sTimestamp);

    return Ext.util.Format.date(dtDate, 'd.m.Y');
}

function deleteArticle() {

    var countSelectedArticleItems = ArticlesPanel.selModel.getCount();

    if(countSelectedArticleItems == 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранную статью?', confirmRemoveLinkedArticle);
    }
    else if(countSelectedArticleItems > 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранные статьти?', confirmRemoveLinkedArticle);
    }
    else {
        Ext.MessageBox.alert('Внимание!', 'Выберите хотя бы одну статью!');
    }
}

function confirmRemoveLinkedArticle(answer) {

    if(answer == 'yes')
    {
        removeArticle();
    }
}

function removeArticle()
{
    var selectedArticle = ArticlesPanel.selModel.getSelections(),
    selectedArticleIDs = [],
    countSelectedArticle = ArticlesPanel.selModel.getCount();

    for(var i = 0; i < countSelectedArticle; i++) {
        selectedArticleIDs.push(selectedArticle[i].json.id);
    }

    selectedArticle = Ext.encode(selectedArticleIDs);
    Ext.Ajax.request({

        waitingMsg: 'Пожалуйста, подождите...',
        url: apiURL,
        params: {
            task: 'DELETE_ARTICLE',
            article_ids: selectedArticle
        },
        success: function (response) {
            if(response.responseText > 0)
                ArticlesDataStore.reload({
                    params: {
                        start: Ext.getCmp('ArticlesPagingToolbar').cursor,
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

function thirdCol() {

    var countSelectedArticleItemsTC = ArticlesPanel.selModel.getCount();

    if(countSelectedArticleItemsTC == 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите поместить статью в третью колонку?', confirmThirdCol);
    }
    else if(countSelectedArticleItemsTC > 1) {
        Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранные статьти?', confirmThirdCol);
    }
    else {
        Ext.MessageBox.alert('Внимание!', 'Выберите хотя бы одну статью!');
    }
}

function confirmThirdCol(answer) {

    if(answer == 'yes')
    {
        setThirdCol();
    }
}

function setThirdCol()
{
    var selectedArticleTC = ArticlesPanel.selModel.getSelections(),
        selectedArticleIDsTC = [],
        countSelectedArticleTC = ArticlesPanel.selModel.getCount();

    for(var i = 0; i < countSelectedArticleTC; i++) {
        selectedArticleIDsTC.push(selectedArticleTC[i].json.id);
    }

    selectedArticleTC = Ext.encode(selectedArticleIDsTC);
    Ext.Ajax.request({

        waitingMsg: 'Пожалуйста, подождите...',
        url: apiURL,
        params: {
            task: 'THIRD_COL',
            article_ids: selectedArticleTC
        },
        success: function (response) {
            if(response.responseText > 0)
                ArticlesDataStore.reload({
                    params: {
                        start: Ext.getCmp('ArticlesPagingToolbar').cursor,
                        limit: countOfNumberPerPage
                    }
                });
            else
                Ext.MessageBox.alert('Ошибка!', 'Ошибка при изменении!');
        },
        failure: function (response) {
            Ext.MessageBox.alert('Ошибка!', 'Сбой подключения, попробуйте позже.');
        }
    });
}
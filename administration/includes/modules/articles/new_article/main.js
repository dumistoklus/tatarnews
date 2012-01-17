try {
    Ext.get('SettingsWindow').remove();
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

var mainPanel,
    SettingsWindow,
    ImageSelectorWindow,
    ArticleImage,
    apiURL = 'includes/modules/articles/new_article/api.php';
var CategoryStore = new Ext.data.Store({
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
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
        url: apiURL,
        method: 'POST'
    }),
    baseParams: {task: 'GET_ARCHIVE'},
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

SettingsWindow = new Ext.Window({
    title: "Настройки статьи",
    width: 600,
    height: 500,
    layout: "fit",
    modal: true,
    id: 'SettingsWindow',
    closable: false,

    border: false,
    items: new Ext.FormPanel({
         bodyStyle: "padding: 10 15",
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
                    ImageSelectorWindow.show();
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
                id: 'MainEvent'
            }
        ]
    }),
    buttons: [
        {
            text: "Закрыть",
            handler: function() {
                SettingsWindow.hide();
            }
        }
    ]
});

var ImagesStore = new Ext.data.JsonStore({
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
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



ImageSelectorWindow = new Ext.Window({
    title: 'Изображения',
    width: 700,
    height: 500,
    layout: "fit",
    id: 'ImageSelectorWindow',
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
                                var image = Ext.getCmp('ImagesDataView').getSelectedRecords();
                                if(image.length > 1)
                                {
                                    Ext.MessageBox.alert("Внимание!", "Можно выбрать только одну фотографию!");
                                }
                                else if(image.length == 1)
                                {
                                    ArticleImage = image[0].data.name;
                                    ImageSelectorWindow.hide();
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
                                var records = Ext.getCmp('ImagesDataView').getSelectedRecords();
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
                                           ImagesStore.load();
                                       }
                                    });
                                }
                            }
                        }
                    ],
                    frame: true,
                    items: new Ext.DataView({
                        store: ImagesStore,
                        autoScroll: true,
                        height: '100%',
                        border: false,
                        id: 'ImagesDataView',
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
                                    var selNode = Ext.getCmp('ImagesDataView').getSelectedRecords();
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
                                                    url: apiURL,
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
                                                        ImagesStore.load();
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
                ImageSelectorWindow.hide();
            }
        }
    ]
});

mainPanel = new Ext.FormPanel({
    anchor: '100% 100%',
    border: true,
    bodyStyle: 'overflow-y: auto; padding: 15 15',
    borderTop: 15,
    tbar: [
        {
            xtype: 'button',
            iconCls: 'settings',
            text: 'Настройки статьи',
            handler: function() {
                    SettingsWindow.show();
            }
        },
        {
            xtype: 'button',
            text: 'Сохранить',
            iconCls: 'save',
            handler: function()
            {
                var data_exists = true;
                data_exists = data_exists && (Ext.getCmp('ArticleHeader').getValue() != '');
                data_exists = data_exists && (Ext.getCmp('ArticleLid').getValue() != '');
                data_exists = data_exists && (Ext.getCmp('ArticlePreview').getValue() != '');
                data_exists = data_exists && (Ext.getCmp('ArticleContent').getValue() != '');
                data_exists = data_exists && (Ext.getCmp('ArticleCat').getValue() != '');
                data_exists = data_exists && (Ext.getCmp('ArticleArchive').getValue() != '');

                if(!data_exists) {
                    Ext.MessageBox.alert('Внимание!', 'Пожалуйста, заполните все нужные поля!');
                    return false;
                }

                Ext.Ajax.request({

                    waitingMsg: 'Пожалуйста, подождите...',
                    url: apiURL,
                    params: {
                        task: 'SAVE_ARTICLE',
                        header: Ext.getCmp('ArticleHeader').getValue(),
                        lid: Ext.getCmp('ArticleLid').getValue(),
                        preview: Ext.getCmp('ArticlePreview').getValue(),
                        content: Ext.getCmp('ArticleContent').getValue(),
                        image: ArticleImage,
                        source: Ext.getCmp('ArticleSource').getValue(),
                        cat: Ext.getCmp('ArticleCat').getValue(),
                        date: Ext.getCmp('ArticleDate').getValue().format('U'),
                        archive: Ext.getCmp('ArticleArchive').getValue(),
                        mainevent: Ext.getCmp('MainEvent').getValue()
                    },
                    success: function (response) {
                        switch(response.responseText) {
                            case '1':
                                Ext.MessageBox.alert('Внимание!', 'Статья успешно создана!');
                                break;
                            case 'not_event':
                                Ext.MessageBox.alert('Внимание!', 'Статья успешно создана, но событие не было добавлено!');
                                break;
                            default:
                                Ext.MessageBox.alert('Ошибка!', 'Ошибка при создании статьи!');
                        }
                    },
                    failure: function (response) {
                        Ext.MessageBox.alert('Ошибка!', 'Сбой подключения, попробуйте позже.');
                    }
                });
            }
        }
    ],
    items: [
        {
            xtype: 'xdatetime',
            fieldLabel: 'Дата создания статьи'

            ,timeFormat:'H:i:s'
            ,timeConfig: {
                 altFormats:'H:i:s'
                ,allowBlank:true
            }
            ,dateFormat:'d.n.Y'
            ,dateConfig: {
                 altFormats:'Y-m-d|Y-n-d'
                ,allowBlank:true
            },
            id: 'ArticleDate'
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
            height: 900,
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
});
moduleDetails = "Добавление новой статьи";

ImagesStore.load();
CategoryStore.load();

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
           xtype: 'tinymce',
           fieldLabel: 'Новость',
           id: 'NewsContent',
           width: 703,
           height: 600,
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
           fieldLabel: 'Дата',
           id: 'NewsDate'
       }
   ]
});
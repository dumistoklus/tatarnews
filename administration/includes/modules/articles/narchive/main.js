try {
    Ext.get('NewArchiveWindow').remove();
}
catch(e) {}
var ArchiveDataStore,
    ArchiveColumnModel,
    ArchiveDataGrid,
    countOfNumberPerPage = 37,
    apiURL = 'includes/modules/articles/narchive/api.php',
	RowEditor = new Ext.ux.grid.RowEditor({
        saveText: 'Обновить',
        cancelText: 'Отмена'
    });

moduleDetails = 'Управление архивами номеров';

ArchiveDataStore =  new Ext.data.Store({
    id: 'ArchiveDataStore',
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        method: 'POST'
    }),
    baseParams: {task: 'GET_ARCHIVE', start: 0, limit: countOfNumberPerPage},
    reader: new Ext.data.JsonReader({
        root: 'results',
        totalProperty: 'total',
        id: 'id'
    },
    [
        {name: 'id', type: 'int', mapping: 'id'},
        {name: 'number', type: 'int', mapping: 'number'},
        {name: 'number_total', type: 'int', mapping: 'number_total'},
        {name: 'date_start', type: 'date', mapping: 'date_start', dateFormat: 'timestamp'},
        {name: 'date_end', type: 'date', mapping: 'date_end', dateFormat: 'timestamp'}
    ]),
    sortInfo: {field: 'number_total', direction: 'DESC'}

});

ArchiveColumnModel = new Ext.grid.ColumnModel({
    defaults: {sortable: true},
    columns: [
        {
            header: '#',
            readonly: true,
            dataIndex: 'id',
            width: 50,
            hidden: true
        },
        {
            header: 'Номер в году',
            dataIndex: 'number',
            width: 100,
            editor: new Ext.form.TextField({
                allowBlank: false,
                maxLength: 3,
                maskRe: /([0-9]{0,})/
            })
        },
        {
            header: 'Номер (всего)',
            dataIndex: 'number_total',
            width: 100,
            editor: new Ext.form.TextField({
                allowBlank: false,
                maxLength: 5,
                maskRe: /([0-9]{0,})/
            })
        },
        {
            header: 'Начальная дата',
            dataIndex: 'date_start',
            width: 100,
            renderer: formatDate,
            editor: new Ext.form.DateField({
                allowBlank: false,
                format: 'd.m.Y'
            })
        },
        {
            header: 'Конечная дата',
            dataIndex: 'date_end',
            width: 100,
            renderer: formatDate,
            editor: new Ext.form.DateField({
                allowBlank: false,
                format: 'd.m.Y'
            })
        }
    ]
});

var NewArchiveWindow = new Ext.Window({
    title: 'Новый номер',
    width: 300,
    height: 200,
    layout: 'fit',
    modal: true,
    id: 'NewArchiveWindow',
    border: false,
    closable: false,
    items: [
        {
            xtype: 'form',
            bodyStyle: 'padding: 15 15',
            items: [
                {
                    xtype: 'textfield',
                    width: 150,
                    maskRe: /^[0-9]+$/,
                    id: 'ArchiveNumber',
                    fieldLabel: 'Номер (в году)',
                    value: (function() {
                        var callback = function() {
                            ArchiveDataStore.un('load', callback);
                            var data = ArchiveDataStore.getAt(0).data;
                            Ext.getCmp('ArchiveNumber').value = data.number + 1;
                            Ext.getCmp('ArchiveTotalNumber').value = data.number_total + 1;
                            Ext.getCmp('ArchiveDateStart').setValue(new Date(data.date_end).add(Date.DAY, 1));
                            Ext.getCmp('ArchiveDateEnd').setValue(new Date(data.date_end).add(Date.DAY, 7));
                        };
                        ArchiveDataStore.on('load', callback);
                    })()
                },
                {
                    xtype: 'textfield',
                    width: 150,
                    maskRe: /^[0-9]+$/,
                    fieldLabel: 'Номер (всего)',
                    id: 'ArchiveTotalNumber'
                },
                {
                    xtype: 'datefield',
                    width: 150,
                    fieldLabel: 'Начальная дата',
                    id: 'ArchiveDateStart',
                    format: 'd.m.Y'
                },
                {
                    xtype: 'datefield',
                    width: 150,
                    fieldLabel: 'Конечная дата',
                    id: 'ArchiveDateEnd',
                    format: 'd.m.Y'
                }
            ]
        }
    ],
    buttons: [
        {
            text: 'Сохранить',
            handler: function()
            {
                Ext.Ajax.request({
                    waitMsg: 'Подождите...',
                    url: apiURL,
                    method: 'POST',
                    params: {
                        task: 'NEW_ARCHIVE',
                        number: Ext.getCmp('ArchiveNumber').getValue(),
                        number_total: Ext.getCmp('ArchiveTotalNumber').getValue(),
                        date_start: Ext.getCmp('ArchiveDateStart').getValue().format('U'),
                        date_end: Ext.getCmp('ArchiveDateEnd').getValue().format('U')
                    },
                    success: function(response) {
                        switch(response.responseText) {
                            case '1' : Ext.MessageBox.alert('Внимание!', "Архив успешно добавлен!");
                                       NewArchiveWindow.hide();
                                       ArchiveDataStore.reload();
                            break;

                            default: Ext.MessageBox.alert('Внимание!', "Произошла ошибка при добавлении архива!");
                        }
                    },
                    failure: function() {
                        Ext.MessageBox.alert('Внимание!', "Проверьте подключение!");
                    }

                });
            }
        },
        {
            text: 'Отмена',
            handler: function ()
            {
                NewArchiveWindow.hide();
            }
        }
    ]
});
ArchiveDataGrid = new Ext.grid.EditorGridPanel({
    id: 'ArchiveGrid',
    store: ArchiveDataStore,
    cm: ArchiveColumnModel,
    enableColLock: false,
    anchor: '100% 100%',
    border: false,
    plugins: [RowEditor],
    selModel: new Ext.grid.RowSelectionModel({singleSelect: false}),
    tbar: [
        {
            xtype: 'button',
            iconCls: 'add',
            text: 'Новый номер',
            handler: function() {
                NewArchiveWindow.show();
            }
        },
        {
            xtype: 'button',
            iconCls: 'remove',
            text: 'Удалить',
            handler: confirmRemoveArchives
        }
    ],
    bbar: new Ext.PagingToolbar({
        id: 'ArchivesPagingToolbar',
        pageSize: countOfNumberPerPage,
        store: ArchiveDataStore,
        displayInfo: true
    })
});

function formatDate(sTimestamp){
    if (typeof sTimestamp == "string") {
        sTimestamp = parseInt(sTimestamp);
    }

    var dtDate = new Date(sTimestamp);

    return Ext.util.Format.date(dtDate, 'd.m.Y');

}

function saveArchiveChanges(grid)
{
    Ext.Ajax.request({
        waitMsg: 'Подождите...',
        url: apiURL,
        method: 'POST',
        params: {
            task: 'SAVE_ARCHIVE',
            id: grid.record.data.id,
            number: grid.record.data.number,
            number_total: grid.record.data.number_total,
            date_start: grid.record.data.date_start.format('U'),
            date_end: grid.record.data.date_end.format('U')
        },
        success: function(response) {
            switch(response.responseText) {
                case '1' : ArchiveDataStore.commitChanges();
                break;

                default: Ext.MessageBox.alert('Внимание!', "Произошла ошибка при изменении архива!");
            }
        },
        failure: function() {
            Ext.MessageBox.alert('Внимание!', "Проверьте подключение!");
        }

    });
}

function confirmRemoveArchives() {
	var countSelectedItems = ArchiveDataGrid.selModel.getCount();

	if(countSelectedItems == 1) {
		Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранный архив?', confirmRemoveLinkedArticles);
	}
	else if(countSelectedItems > 1) {
		Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранные архивы?', confirmRemoveLinkedArticles);
	}
	else {
		Ext.MessageBox.alert('Внимание!', 'Выберите хотя бы один архив!');
	}
}

function confirmRemoveLinkedArticles(answer)
{
    if(answer == 'yes')
    {
        Ext.MessageBox.confirm('Внимание!', 'Вы хотите удалить связанные статьи?', removeSelectedArchives);
    }
}

function removeSelectedArchives(answer)
{
    if(answer == 'yes')
    {
        removeArchivesWithLinkedArticles();
    }
    else
    {
        removeArchivesWithoutLinkedArticles();
    }
}

function removeArchivesWithLinkedArticles()
{
    removeArchives('DELETE_ARCHIVES_WITH_LINKED_ARTICLES')
}

function removeArchives(mode)
{
    var selectedArchives = ArchiveDataGrid.selModel.getSelections(),
    selectedArchivesIDs = [],
    countSelectedArchives = ArchiveDataGrid.selModel.getCount();

    for(var i = 0; i < countSelectedArchives; i++) {
        selectedArchivesIDs.push(selectedArchives[i].json.id);
    }

    selectedArchives = Ext.encode(selectedArchivesIDs);
    Ext.Ajax.request({

        waitingMsg: 'Пожалуйста, подождите...',
        url: apiURL,
        params: {
            task: mode,
            archives_ids: selectedArchives
        },
        success: function (response) {
            switch(response.responseText) {
                case '1':
                    ArchiveDataStore.reload({params: {start: Ext.getCmp('ArchivesPagingToolbar').cursor, limit: countOfNumberPerPage}});
                    break;
                default:
                    Ext.MessageBox.alert('Ошибка!', 'Ошибка при удалении!');
            }
        },
        failure: function (response) {
            Ext.MessageBox.alert('Ошибка!', 'Сбой подключения, попробуйте позже.');
        }
    });
}

function removeArchivesWithoutLinkedArticles()
{
    removeArchives('DELETE_WITHOUT_LINKED_ARTICLES');
}

ArchiveDataStore.load();

RowEditor.on({
  scope: this,
  afteredit: function(roweditor) {
        saveArchiveChanges(roweditor)
  }
});

ArchiveDataGrid.on('afteredit', saveArchiveChanges);
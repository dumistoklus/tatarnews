var CommentsDataStore,
CommentsColumnModel,
CommentsGrid,
countOfNumberPerPage = 37,
apiURL = 'includes/modules/articles/comments/api.php',
RowEditor = new Ext.ux.grid.RowEditor({
    saveText: 'Обновить',
    cancelText: 'Отмена'
});

moduleDetails = 'Управление комментариями';

CommentsDataStore =  new Ext.data.Store({
    id: 'CommentsDataStore',
    proxy: new Ext.data.HttpProxy({
        url: apiURL,
        totalProperty: 'total',
        method: 'POST'
    }),
    baseParams: {
        task: 'GET_COMMENTS',
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
        name: 'text',
        type: 'string',
        mapping: 'text'
    },
    {
        name: 'created',
        type: 'string',
        mapping: 'created'
    }, 	
    {
        name: 'user_name',
        type: 'string',
        mapping: 'user_name'
    },
    {
        name: 'isdelete',
        type: 'int',
        mapping: 'isdelete'
    }
    ])

});

CommentsColumnModel = new Ext.grid.ColumnModel({
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
        header: 'Пользователь',
        dataIndex: 'user_name',
        width: 100
    },
    {
        header: 'Комментарий',
        dataIndex: 'text',
        width: 600
    },
    {
        header: 'Дата',
        dataIndex: 'created',
        width: 150
    },
    {
        header: 'Активен',
        dataIndex: 'isdelete',
        width: 50,
        renderer: renderActive
    }    
    ]
});

CommentsGrid = new Ext.grid.EditorGridPanel({
    id: 'CommentsGrid',
    store: CommentsDataStore,
    cm: CommentsColumnModel,
    enableColLock: false,
    anchor: '100% 100%',
    border: false,
    selModel: new Ext.grid.RowSelectionModel({
        singleSelect: false
    }),
    tbar: [
    {
        xtype: 'button',
        iconCls: 'edit',
        text: 'Поменять активность',
        handler: change_active
        
    }
    ],
    bbar: new Ext.PagingToolbar({
        id: 'CommentsPagingToolbar',
        pageSize: countOfNumberPerPage,
        store: CommentsDataStore,
        displayInfo: true
    })
});

function change_active() {
    
    if(CommentsGrid.selModel.getCount() == 0) {
        Ext.MessageBox.alert('Внимание!', 'Вы не выбрали комментарий!');
        return;
    }
    var change_id = CommentsGrid.selModel.getSelections()[0].data.id;
    var change_active = CommentsGrid.selModel.getSelections()[0].data.isdelete;

    Ext.Ajax.request({

        waitingMsg: 'Пожалуйста, подождите...',
        url: apiURL,
        params: {
            task: 'CHAHGE_ACTIVE',
            id: change_id,
            active: change_active
        },
        success: function (response) {
            if(response.responseText > 0) {
                //Ext.MessageBox.alert('Внимание!', 'Вы изменили активность выбранного комментария!');
                CommentsDataStore.reload({
                    params: {
                        start: Ext.getCmp('CommentsPagingToolbar').cursor,
                        limit: countOfNumberPerPage
                    }
                });
            } else
                Ext.MessageBox.alert('Ошибка!', 'Произошла ошибка!');
        },
        failure: function (response) {
            Ext.MessageBox.alert('Ошибка!', 'Сбой подключения, попробуйте позже.');
        }
    });
}

function renderActive(isdelete) {
    if(isdelete == 1)
        return 'Нет';
    else
        return 'Да';
}

CommentsDataStore.load();
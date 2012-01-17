/**
 * 
 */
 
var RightsTable,
	RightsDataStore,
	GroupSelectorStore,
	RightsColumnModel,
	apiURL = 'includes/modules/users/user_groups/api.php',
	countOfRightsPerPage = 38,
	CreateGroupForm, GroupCreateWindow;
	
moduleDetails = 'Редактирование групп';

GroupSelectorStore = new Ext.data.Store({
	id: 'GroupsDataStore',
	proxy: new Ext.data.HttpProxy({
		url: apiURL,
		method: 'POST'		
	}),
	baseParams: {
		task: 'GET_GROUPS'
	},
	reader: new Ext.data.JsonReader({
		root: 'results',
		totalProperty: 'total',
		id: 'id'
	},
	[
		{name: 'id', type: 'int', mapping: 'id'},
		{name: 'group', type: 'string', mapping: 'group'}
	]),
	sortInfo: {field: 'id', direction: 'ASC'}	
});

RightsColumnModel = new Ext.grid.ColumnModel({
	defaults: {
		sortable: true
	},
	columns: [
		{
			header: '#',
			readOnly: true,
			dataIndex: 'id',
			width: 50,
			hidden: true
		},
		{
			header: 'Индекс',
			readOnly: true,
			dataIndex: 'right_index',
			width: 60
		},
		{
			header: 'Описание',
			readOnly: true,
			dataIndex: 'name',
			width: 400
		},

		{
			xtype: 'checkcolumn',
			id: 'EnableRightCheckBox',
			header: 'Включено?',
			dataIndex: 'enable',
			width: 70
		}
	]
});

RightsDataStore = new Ext.data.Store({
	id: 'RightsDataStore',
	proxy: new Ext.data.HttpProxy({
		url: apiURL,
		method: 'POST'		
	}),
	baseParams: {
		task: 'GET_GROUP_RIGHTS',
		group: get_group_id()
	},
	reader: new Ext.data.JsonReader({
		root: 'results',
		totalProperty: 'total',
		id: 'id'
	},
	[
		{name: 'id', type: 'int', mapping: 'id'},
		{name: 'right_index', type: 'string', mapping: 'right_index'},
		{name: 'name', type: 'string', mapping: 'name'},
		{name: 'enable', type: 'bool', mapping: 'enable'}
	]),
	sortInfo: {field: 'id', direction: 'ASC'}	
});

RightsTable = new Ext.grid.EditorGridPanel({
	id: 'RightsTable',
	store: RightsDataStore,
	cm: RightsColumnModel,
	enableColLock: false,
	anchor: '100% 100%',
	border: false,
	
	selModel: new Ext.grid.RowSelectionModel({singleSelect: true}),
	
	tbar: [
		{
			xtype: 'combo',
			id: 'GroupsComboBox',
			mode: 'remote',
			store: GroupSelectorStore,
			displayField: 'group',
			editable: false,
			triggerAction: 'all',
			selectOnFocus: true
		},
		{
			xtype: 'button',
			id: 'CreateGroupButton',
			text: 'Добавить группу',
			iconCls: 'add',
			handler: diplayCreateGroupForm
		},
		{
			xtype: 'button',
			id: 'DeleteGroupButton',
			text: 'Удалить группу',
			iconCls: 'remove',
			handler: confirmRemoveGroup
		}
	],
	bbar: new Ext.PagingToolbar({
		id: 'RightsPaging',
		pageSize: countOfRightsPerPage,
		store: RightsDataStore,
		displayInfo: true
	})
});

CreateGroupForm = new Ext.form.FormPanel({
	labelAlignL: 'top',
	bodyStyle: 'padding: 5px',
	width: 364,
	labelPad: 30,
	layout: 'form',
	items: [
		{
			xtype: 'textfield',
			fieldLabel: 'Название группы',
			id: 'GroupNameField',
			maxLength: 150,
			allowBlank: false,
			width: 214,
			maskRe: /^[a-zA-Z0-9]+$/
		}
	],
	buttons: [
		{
			text: 'Добавить и закрыть',
			handler: createGroup
		
		},
		{
			text: 'Закрыть',
			handler: function() { 
				GroupCreateWindow.hide();
			}
		}
	]	
});

GroupCreateWindow = new Ext.Window({
	id: 'GroupCreateWindow',
	title: 'Добавить группу',
	closable: false,
	width: 381,
	height: 104,
	plain: true,
	items: CreateGroupForm
});

function get_group_id() {
	 if(Ext.getCmp('GroupsComboBox') == undefined) return 0;
	 
	 var combobox = Ext.getCmp('GroupsComboBox'),
	 	 record;
	 record = GroupSelectorStore.find('group', combobox.getValue());
	 record = GroupSelectorStore.getAt(record);
	 
	 return record.get('id');
}

function saveRights(rights_grid) {
	Ext.Ajax.request({
		waitMsg: 'Сохраняю...',
		url: apiURL,
		params: {
			task: 'SAVE_RIGHTS',
			right_id: rights_grid.get('id'),
			group_id: get_group_id(),
			enabled: rights_grid.get('enable')
		},		
		success: function(response) {
			var result = response.responseText;
			if(result == '1') {
				RightsDataStore.commitChanges();
				
			}
			else {
				Ext.MessageBox.alert('Ошибка', result);
			}
		},
		failure: function(response) {
			Ext.MessageBox.alert('Ошибка', 'Проверьте подключение.');
		}
	});	
}

function diplayCreateGroupForm() {
	
	if(!GroupCreateWindow.isVisible()) {		
		
		GroupCreateWindow.show();
		resetGroupForm();
	}
	else {
		GroupCreateWindow.toFront();
	}
}

function resetGroupForm() {
	Ext.getCmp('GroupNameField').setValue('');
}

function createGroup() {
	if(isGroupFormValid()) {
       	Ext.Ajax.request({
       		waitingMsg: 'Пожалуйста, подождите...',
       		url: apiURL,
       		params: {
       			task: 'CREATE_GROUP',
       			name: Ext.getCmp('GroupNameField').getValue()
       		},
       		success: function(response) {
       			switch(response.responseText) {
       				case 'SUCCESS':      					
       					GroupCreateWindow.hide();
       					
       					var new_group = Ext.getCmp('GroupNameField').getValue();
       					GroupSelectorStore.load({
 							callback: function() {
			       					Ext.getCmp('GroupsComboBox').setValue(new_group);
       					       		Ext.getCmp('GroupsComboBox').fireEvent('select');
       					       		Ext.MessageBox.alert('Готово', 'Группа успешно добавлена!');
 							}
						});   					
       					
       					break;
       					
       				default:
       					Ext.MessageBox.alert('Ошибка', 'Во время создания группы произошла ошибка!');
       					break;
       			}
       		},
       		failure: function(response) {
       			Ext.MessageBox.alert('Ошибка', 'Ошибка передачи данных!')
       		}
       	});
	}
	else Ext.MessageBox.alert('Ошибка', 'Введенные данные не корректны!');
}

function isGroupFormValid() {
	return Ext.getCmp('GroupNameField').isValid();
}

function confirmRemoveGroup() {
	
	if(Ext.getCmp('GroupsComboBox').getValue() != '') {
		Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить группу?', deleteGroup);
	}
	else {
		Ext.MessageBox.alert('Внимание!', 'Выберите группу!');
	}
}

function deleteGroup(answer) {
	if(answer == 'yes') {
		Ext.Ajax.request({
		
			waitingMsg: 'Пожалуйста, подождите...',
			url: apiURL,
			params: {
				task: 'DELETE_GROUP',
				group_id: get_group_id()
			},
			success: function (response) {
				switch(response.responseText) {
					case 'SUCCESS':
						
       					GroupSelectorStore.load({
 							callback: function() {
			       					Ext.getCmp('GroupsComboBox').setValue('');
			       					RightsDataStore.removeAll();
			       					
 							}
						});						
						break;
					default:
						Ext.MessageBox.alert('Ошибка!', 'Не удалось удалить группу!');
				}
			},
			failure: function (response) {
				Ext.MessageBox.alert('Ошибка!', 'Сбой подключения, попробуйте позже.');
			}
		});
	}
}

GroupSelectorStore.load();

Ext.getCmp('GroupsComboBox').on('select', function() { 
	RightsDataStore.reload({
		params: {
				group: get_group_id()
			}
	}); 
});

RightsTable.on('afteredit', saveRights);
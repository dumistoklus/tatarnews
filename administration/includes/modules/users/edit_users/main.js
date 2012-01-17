var UsersDataStore,
	UsersColumnModel,
	UsersListingEditorGrid,
	UsersGroupsDataStore,
	UserCreateForm,
	UserCreateWindow,
	countOfUsersPerPage = 38,
	RowEditor = new Ext.ux.grid.RowEditor({
        saveText: 'Обновить',
        cancelText: 'Отмена'
    });

moduleDetails = 'Редактирование пользователей';
    
UsersDataStore = new Ext.data.Store({
	id: 'UsersDataStore',
	proxy: new Ext.data.HttpProxy({
		url: 'includes/modules/users/edit_users/api.php',
		method: 'POST'
	}),
	baseParams: {task: 'LIST', start: 0, limit: countOfUsersPerPage},
	reader: new Ext.data.JsonReader({
		root: 'results',
		totalProperty: 'total',
		id: 'id'
	},
	[
		{name: 'id', type: 'int', mapping:'id'},
		{name: 'nickname', type: 'string', mapping: 'nickname'},
        {name: 'name', type: 'string', mapping: 'name'},
		{name: 'email', type: 'string', mapping: 'email'},
		{name: 'group', type: 'group', mapping: 'group'}
	]),
	sortInfo: {field: 'id', direction: 'ASC'}
});

UsersGroupsDataStore = new Ext.data.Store({
	id: 'UsersGroupsDataStore',
	proxy: new Ext.data.HttpProxy({
		url: 'includes/modules/users/edit_users/api.php',
		method: 'POST'
	}),
	baseParams: {task: 'GET_USERS_GROUPS'},
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

UsersColumnModel = new Ext.grid.ColumnModel({
	defaults: {
		sortable: true
	},
	columns:[
		{
			header: '#',
			readOnly: true,
			dataIndex: 'id',
			width: 50,
			hidden: true
		},
		{
			header: 'Имя пользователя',
			dataIndex: 'nickname',
			width: 150,
			editor: new Ext.form.TextField({
				allowBlank: false,
				maxLength: 40,
	            maskRe: /([a-zA-Z0-9]+)$/
			})
		},
		{
			header: 'ФИО',
			dataIndex: 'name',
			width: 150,
			editor: new Ext.form.TextField({
				allowBlank: false,
				maxLength: 40,
                                maskRe: /([a-zA-Z0-9А-Яа-я\s]+)$/
			})
		},
		{
			header: 'Электронная почта',
			dataIndex: 'email',
			width: 195,
			editor: new Ext.form.TextField({
				allowBlank: false,
				maxLength: 150,
				maskRe: /([-_a-zA-Z0-9@.]+)$/
			})
		},
		{
			header: 'Группа',
			dataIndex: 'group',
			width: 150,
			editor: new Ext.form.ComboBox({
				store: UsersGroupsDataStore,
				displayField: 'group',
                valueField: 'id',
				typeAhead: true,
				mode: 'remote',
				editable: false,
				triggerAction: 'all',
				selectOnFocus: true
			}),
			hidden: false
		}
	]

});

UsersListingEditorGrid = new Ext.grid.EditorGridPanel({
	id: 'UsersListingEditorGrid',
	store: UsersDataStore,
	cm: UsersColumnModel,
	enableColLock: false,
	anchor: "100% 100%",
	border: false,
	plugins: [RowEditor],
	selModel: new Ext.grid.RowSelectionModel({singleSelect: false}),
	tbar: [
		{
			text: 'Добавить пользователя',
			tooltip: 'Добавить пользователя',
			iconCls: 'add',
			handler: displayUserAddForm
		},
		'-',
		{
			text: 'Удалить выбранных пользователей',
			tooltip: 'Используйте CTRL или SHIFT для выбора нескольких пользователей',
			iconCls: 'remove',
			handler: confirmRemoveUsers
		},
		'-',
		{
			text: 'Поиск пользователя',
			tooltip: 'Поиск пользователя',
			iconCls: 'search',
			handler: searchUsers
		}
	],
	bbar: new Ext.PagingToolbar({
		id: 'UsersStorePaging',
		pageSize: countOfUsersPerPage,
		store: UsersDataStore,
		displayInfo: true
	})
});

UserCreateForm = new Ext.FormPanel({
	labelAlignL: 'top',
	bodyStyle: 'padding: 5px',
	width: 364,
	labelPad: 30,
	layout: 'form',
	items: [
		{
			xtype: 'textfield',
			fieldLabel: 'Электронная почта',
			id: 'EmailField',
			maxLength: 150,
			allowBlank: false,
			width: 214,
			maskRe: /^[a-zA-Z0-9\@\.]+$/
		},
		{
			xtype: 'textfield',
			fieldLabel: 'Пароль',
			id: 'PasswordField',
			inputType: 'password',
			minLength: 6,
			maxLength: 150,
			width: 214,
			allowBlank: false
		},
		{
			xtype: 'textfield',
			fieldLabel: 'Повторите пароль',
			id: 'RePasswordField',
			inputType: 'password',
			minLength: 6,
			maxLength: 150,
			width: 214,
			allowBlank: false
		},
		{
			xtype: 'textfield',
			fieldLabel: 'Nickname',
			id: 'NicknameField',
			minLength: 2,
			maxLength: 40,
			allowBlank: false,
			width: 214,
			maskRe: /([a-zA-Z0-9]+)$/
		},
		{
			xtype: 'textfield',
			fieldLabel: 'ФИО',
			id: 'NameField',
			minLength: 2,
			maxLength: 40,
			allowBlank: false,
			width: 214,
			maskRe: /([a-zA-Z0-9А-Яа-я\s]+)$/
		},
		new Ext.form.ComboBox({
			id: 'GroupField',
			fieldLabel: 'Группа',
			store: UsersGroupsDataStore,
			displayField: 'group',
            valueField: 'id',
			typeAhead: true,
			mode: 'remote',
			editable: false,
			triggerAction: 'all',
			allowBlank: false
		})
	],
	buttons: [
		{
			text: 'Добавить и закрыть',
			handler: createUser
		
		},
		{
			text: 'Закрыть',
			handler: function() { 
				UserCreateWindow.hide();
			}
		}
	]
});

UserCreateWindow = new Ext.Window({
	id: 'UserCreateWindow',
	title: 'Добавить пользователя',
	closable: false,
	width: 381,
	height: 270,
	plain: true,
	items: UserCreateForm
});


function saveUserSettings(users_grid) {
	Ext.Ajax.request({
		waitMsg: 'Сохраняю...',
		url: 'includes/modules/users/edit_users/api.php',
		params: {
			task: 'UPDATE_USER_DATA',
			id: users_grid.record.data.id,
			nickname: users_grid.record.data.nickname,
                        name: users_grid.record.data.name,
			email: users_grid.record.data.email,
			group: users_grid.record.data.group
		},
		
		success: function(response) {
			var result = response.responseText;
			if(result == '1') {
                UsersListingEditorGrid.
				UsersDataStore.commitChanges();

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

function displayUserAddForm() {
	if(!UserCreateWindow.isVisible()) {
		resetUserForm();
		UserCreateWindow.show();
	}
	else {
		UserCreateWindow.toFront();
	}
}

function resetUserForm() {
	Ext.getCmp('EmailField').setValue('');
	Ext.getCmp('PasswordField').setValue('');
	Ext.getCmp('NicknameField').setValue('');
	Ext.getCmp('GroupField').setValue('');
	Ext.getCmp('RePasswordField').setValue('');
        Ext.getCmp('NameField').setValue('');
}

function createUser() {
    alert(Ext.getCmp('GroupField').getValue());
	if(isUserFormValid()) {
       	Ext.Ajax.request({
       		waitingMsg: 'Пожалуйста, подождите...',
       		url: 'includes/modules/users/edit_users/api.php',
       		params: {
       			task: 'CREATE_USER',
       			email: Ext.getCmp('EmailField').getValue(),
       			password: Ext.getCmp('PasswordField').getValue(),
       			nickname: Ext.getCmp('NicknameField').getValue(),
       			group: Ext.getCmp('GroupField').getValue(),
                name: Ext.getCmp('NameField').getValue()
       		},
       		success: function(response) {
       			switch(response.responseText) {
       				case 'EMAIL':
       					Ext.MessageBox.alert('Ошибка', 'Введите корректный email!');
       					Ext.getCmp('EmailField').markInvalid('Введите корректный email!');
       					break;
       				case 'PASSWORD':
       					Ext.MessageBox.alert('Ошибка', 'Пароль должен быть больше 6 символов!');
       					Ext.getCmp('PasswordField').markInvalid('Пароль должен быть больше 6 символов!');
       					break;
       				case 'NICKNAME':
       					Ext.MessageBox.alert('Ошибка', 'Введите корректный nickname!');
       					Ext.getCmp('NicknameField').markInvalid('Введите корректный nickname!');
       					break;
       				case 'NAME':
       					Ext.MessageBox.alert('Ошибка', 'Введите корректное ФИО!');
       					Ext.getCmp('NameField').markInvalid('Введите корректное ФИО!');
       					break;
       				case 'SUCCESS':
       					Ext.MessageBox.alert('Готово', 'Пользователь успешно добавлен!');
       					UsersDataStore.reload({params: {start: 0}});
       					UserCreateWindow.hide();
       					break;
       				case 'MAYBE_EMAIL':
       					Ext.MessageBox.alert('Ошибка', 'Возможно пользователь с такими email уже существует!');
						Ext.getCmp('EmailField').markInvalid('Введите другой email');
						break;
       				case 'MAYBE_NICKNAME':
       					Ext.MessageBox.alert('Ошибка', 'Возможно пользователь с такими nickname уже существует!');
						Ext.getCmp('NicknameField').markInvalid('Введите другой nickname');
       					break;
       				default:
       					Ext.MessageBox.alert('Ошибка', 'Во время создания пользователя произошла ошибка!');
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

function isUserFormValid() {
	var data_correct =  (Ext.getCmp('EmailField').isValid() &&
					Ext.getCmp('PasswordField').isValid() &&
					Ext.getCmp('NicknameField').isValid() &&
					Ext.getCmp('GroupField').isValid() &&
					Ext.getCmp('NameField').isValid()
		  			 ),
		correct_password = Ext.getCmp('PasswordField').getValue() == Ext.getCmp('RePasswordField').getValue();
	if(!correct_password) {
		Ext.getCmp('PasswordField').markInvalid();
		Ext.getCmp('RePasswordField').markInvalid('Пароли не совпадают!');
		return false;
	}
	
	return correct_password && data_correct;
}

function confirmRemoveUsers() {
	var countSelectedItems = UsersListingEditorGrid.selModel.getCount();
	
	if(countSelectedItems == 1) {
		Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранного пользователя?', deleteUsers);
	}
	else if(countSelectedItems > 1) {
		Ext.MessageBox.confirm('Внимание!', 'Вы уверены, что хотите удалить выбранных пользователей?', deleteUsers);
	}
	else {
		Ext.MessageBox.alert('Внимание!', 'Выберите хотя бы одного пользователя!');
	}
}

function deleteUsers(answer) {
	if(answer == 'yes') {
		var selectedUsers = UsersListingEditorGrid.selModel.getSelections(),
			selectedUsersIDs = [],
			countSelectedUsers = UsersListingEditorGrid.selModel.getCount();
			
		for(var i = 0; i < countSelectedUsers; i++) {
			selectedUsersIDs.push(selectedUsers[i].json.id);
		}
		
		selectedUsers = Ext.encode(selectedUsersIDs);
		
		Ext.Ajax.request({
		
			waitingMsg: 'Пожалуйста, подождите...',
			url: 'includes/modules/users/edit_users/api.php',
			params: {
				task: 'DELETE_USERS',
				users_ids: selectedUsers
			},
			success: function (response) {
				switch(response.responseText) {
					case '1':
						//UsersDataStore.commitChanges();
						UsersDataStore.reload({params: {start: Ext.getCmp('UsersStorePaging').cursor}});
						break;
					default:
						Ext.MessageBox.alert('Ошибка!', 'Не удалось удалить пользователей!');
				}
			},
			failure: function (response) {
				Ext.MessageBox.alert('Ошибка!', 'Сбой подключения, попробуйте позже.');
			}
		});
	}
}

function searchUsers() {
		
}
	
try {
    Ext.get('EmailField').remove();
    Ext.get('PasswordField').remove();
    Ext.get('NicknameField').remove();
    Ext.get('GroupField').remove();
    Ext.get('RePasswordField').remove();
    Ext.get('NameField').remove();
}
catch(e) {}

UsersDataStore.load();

RowEditor.on({
  scope: this,
  afteredit: function(roweditor, changes, record, rowIndex) {
		saveUserSettings(roweditor);
  }
});

UsersListingEditorGrid.on('afteredit', saveUserSettings);
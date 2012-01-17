/**
 * 
 */

Ext.namespace("Login");

Login.base = function() {
	return {
		init : function() {
			loginForm = new Ext.FormPanel({
				url : './',
				method: "POST",
				standardSubmit: true,
				frame : true,
				items : [{
							xtype : 'textfield',
							name: "email",
							fieldLabel : 'Логин',
							allowBlank : false,
							anchor : '100%'
						}, {
							xtype : 'textfield',
							fieldLabel : 'Пароль',
							name : 'password',
							inputType : 'password',
							anchor : '100%',
							allowBlank : false
						}],

				buttons : [{
							text : 'Войти',
							handler : function() {
								loginForm.getForm().submit();
							}
						}],
				anchor: "100% 100%"
			});
			var container = new Ext.Window({
						title : 'Вход в панель администрирования',
						width : 500,
						height : 130,
						layout : 'anchor',
						items : [loginForm]
					});
			container.show();
		}
	};
}();
<?php
$user = User::get();
if(!defined('success') && !$user->isAdmin()) {
	header("Location: ../../");
	die;
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title id='title'>Администрирование сайта</title>
<link rel="stylesheet" type="text/css"
	href="../css/ext-js/user_css/style.css" />

<link rel="stylesheet" type="text/css"
	href="../css/ext-js/css/ext-all.css" />
<link rel="stylesheet" type="text/css"
	href="../css/ext-js/user_css/RowEditor.css" />
<link rel="stylesheet" type="text/css" href="../css/ext-js/user_css/Portal.css" /> 
<!--  
<link rel="stylesheet" type="text/css"
	href="http://dev.sencha.com/deploy/dev/examples/shared/examples.css" />
	
<link rel="stylesheet" type="text/css"
	href="http://dev.sencha.com/deploy/dev/examples/shared/icons/silk.css" />
-->
<script type="text/javascript" src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/extjs/adapter/ext/ext-base.js"></script>

<script type="text/javascript" src="../js/extjs/ext-all-debug.js"></script>
<script type="text/javascript" src="../js/extjs/plugins/RowEditor.js"></script>
<script type="text/javascript" src="../js/extjs/plugins/CheckColumn.js"></script>

<script type="text/javascript" src="../js/extjs/plugins/Portal.js"></script> 
<script type="text/javascript" src="../js/extjs/plugins/PortalColumn.js"></script> 
<script type="text/javascript" src="../js/extjs/plugins/Portlet.js"></script>

<script type="text/javascript" src="../js/extjs/plugins/tiny_mce.js"></script>
<script type="text/javascript" src="../js/extjs/plugins/Ext.ux.TinyMCE.js"></script>
<script type="text/javascript" src="../js/extjs/plugins/DateTimeField.js"></script>
<script type="text/javascript" src="../js/extjs/plugins/FileUploadField.js"></script>
<script type="text/javascript">
			var moduleDetails = '',detailEl;
			
			function set_module(details) {
	    		if(!detailEl){
	    			var bd = Ext.getCmp('details-panel').body;
	    			bd.update('').setStyle('background','#fff');
	    			detailEl = bd.createChild();
	    		}
	    		
				detailEl.hide().update(details).slideIn('l', {stopFx:true,duration:.2});	
			}
			
        	Ext.namespace("Layout");
			Layout.base = function(){
			    Ext.QuickTips.init();
			    
			    var toolbar = new Ext.Toolbar({
			    	region: "north",
			    	layout: "border",
			    	height: 26,
			    	items: [
                        {
                          region: "west",
                          xtype: 'tbbutton',
                          margins: "2 5 2 0",
                          text: 'На сайт',
                          iconCls: 'arrow-left',
                          handler: function()
                          {
                              document.location = "/";
                          }
                        },
			    		{
			    			region: "east",
			    			xtype: 'tbbutton',
			    			text: "Выйти",
			    			margins: "2 5 2 0",
			    			handler: function(btn) {
			    				document.location ="?logout";
			    			}
			    		},
			    		{
			    			xtype: "tbspacer",
							region: 'center',
							html: "Привет, <?=$user->nickname() ?>!",
							margins: "6 0 2 6"
						}
			    	]
			    });

			    var tree = new Ext.tree.TreePanel({
			        region:'center',
			        useArrows: true,
			        autoScroll: true,
			        animate: true,
			        enableDD: false,
			        containerScroll: true,
			        border: false,
			        anchor: "100% 60%",
			        dataUrl: 'ajax/menu.php',
			        root: {
			            nodeType: 'async',
			            text: 'Настройки',
			            draggable: false,
			            id: 'project'
			        }
			    });

			    var detailsPanel = {
			    		id: 'details-panel',
			            title: 'Описание',
			            border: false,
			            anchor: "100% 40%",
			            bodyStyle: 'padding-bottom:15px;background:#eee;',
			    		autoScroll: true,
			    		html: '<p class="details-info">Приветствую! Выберите нужный пункт меню и приступайте к созданию вашего сайта!</p>'
			        };
				
			   var menu = new Ext.Panel({
			        title: 'Меню',
			        region: "west",
			        width: 250,
			        height: "100%",
			        split: true,
			        collapseMode: "mini",
			        layout: "anchor",
			        items: [tree, detailsPanel]
			    });
			    
			    tree.getRootNode().expand(true);
			    
				var viewport;
				
			    tree.getSelectionModel().on('selectionchange', function(tree, node) {
				    
			    	if(!(node.leaf)){
						return false;
			    	}

		    		set_module('');		
		    		
			    	Ext.Ajax.request({
				    	  waitingMsg: "Загружается модуль...",
			    		  url: 'includes/modules/'+node.id+'/',
			    		  method: 'GET',
			    		  success: function (result, request) {
								var s = document.createElement('script');
								s.id = 'script'+node.id;
								s.type = 'text/javascript'; 
								s.src = 'includes/modules/'+node.id+'/main.js?'+(new Date()).getTime(); 

								var h = document.getElementsByTagName('body')[0],
									loaded = false;
								h.insertBefore(s,h.firstChild);
								
								s.onreadystatechange= function () {     
									
									   if (this.readyState == 'complete' || this.readyState == 'loaded') {     
										  if(!loaded) {
											loaded = true;
										  	content.removeAll();
										  	content.add(eval(result.responseText));											
										  	content.doLayout();	
										  	set_module(moduleDetails);				
										  }					
									   }							
									
								}
																				    				  
								Ext.get(s.id).on('load', function() {
									if(!loaded) {
										loaded = true;
										content.removeAll();
										content.add(eval(result.responseText));
										content.doLayout();
										set_module(moduleDetails);
									}
								});		    		
			    		  },
			    		  failure: function (result, request) {
			    		    	Ext.Msg.alert('Ошибка!', 'Модуль не найден!');
			    		  }
			    		});			    			    	
			    });
				var content = new Ext.Panel({
					region: "center",
					height: '100%',
					layout: "anchor",
					border: false
				});
				
			    return {
			        init: function(){
			        	viewport = new Ext.Viewport({
							layout: "border",
							renderTo: Ext.getBody(),
							items: [toolbar, menu, content]
						});

			        }
			    };
			}();
			
			Ext.ux.TinyMCE.initTinyMCE();
        	Ext.onReady(Layout.base.init, Layout.base);
        </script>

</head>
<body>
</body>

</html>

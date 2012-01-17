(function(){Ext.namespace("Ext.ux");var c=false;var b;var a;Ext.ux.TinyMCE=Ext.extend(Ext.form.Field,{tinymceSettings:null,allowBlank:true,invalidText:"The value in this field is invalid",invalidClass:"invalid-content-body",minLengthText:"The minimum length for this field is {0}",maxLengthText:"The maximum length for this field is {0}",blankText:"This field is required",hideMode:"offsets",defaultAutoCreate:{tag:"textarea",style:"width:1px;height:1px;",autocomplete:"off"},constructor:function(d){var e={tinymceSettings:{accessibility_focus:false}};Ext.apply(e,d);this.addEvents({editorcreated:true});Ext.ux.TinyMCE.superclass.constructor.call(this,e)},initComponent:function(){this.tinymceSettings=this.tinymceSettings||{};Ext.ux.TinyMCE.initTinyMCE({language:this.tinymceSettings.language})},initEvents:function(){this.originalValue=this.getValue()},onRender:function(e,d){Ext.ux.TinyMCE.superclass.onRender.call(this,e,d);if(Ext.type(this.width)=="number"){this.tinymceSettings.width=this.width}if(Ext.type(this.height)=="number"){this.tinymceSettings.height=this.height}this.el.dom.setAttribute("tabIndex",-1);this.el.addClass("x-hidden");this.textareaEl=this.el;var h={overflow:"hidden"};if(Ext.isIE){h["margin-top"]="-1px";h["margin-bottom"]="-1px"}this.wrapEl=this.el.wrap({style:h});this.actionMode="wrapEl";this.positionEl=this.wrapEl;var i=this.getId();this.ed=new tinymce.Editor(i,this.tinymceSettings);var f=new Ext.util.DelayedTask(this.validate,this);this.ed.onKeyPress.add(function(k,j){f.delay(250)}.createDelegate(this));this.ed.onBeforeRenderUI.add(function(k,j){k.controlManager=new a(this,k)}.createDelegate(this));this.ed.onPostRender.add(function(k,j){var m=k.settings;var n=Ext.get(Ext.DomQuery.selectNode("#"+this.ed.id+"_tbl td.mceToolbar"));if(n!=null){var l=n.select("> table.mceToolbar");Ext.DomHelper.append(n,{tag:"div",id:this.ed.id+"_xtbar",style:{overflow:"hidden"}},true).appendChild(l)}k.windowManager=new b({editor:this.ed,manager:this.manager});Ext.get(k.getContentAreaContainer()).addClass("patch-content-body");Ext.Element.fly(m.content_editable?k.getBody():k.getWin()).on("focus",this.onFocus,this);Ext.Element.fly(m.content_editable?k.getBody():k.getWin()).on("blur",this.onBlur,this,this.inEditor&&Ext.isWindows&&Ext.isGecko?{buffer:10}:null)}.createDelegate(this));this.ed.onChange.add(function(k,j){this.fireEvent("change",k,j)}.createDelegate(this));this.ed.render();tinyMCE.add(this.ed);(function g(){if(!this.isVisible()){arguments.callee.defer(50,this);return}var j=this.getSize();this.withEd(function(){this._setEditorSize(j.width,j.height);this.fireEvent("editorcreated")})}).call(this)},getResizeEl:function(){return this.wrapEl},getName:function(){return this.rendered&&this.textareaEl.dom.name?this.textareaEl.dom.name:(this.name||"")},initValue:function(){if(!this.rendered){Ext.ux.TinyMCE.superclass.initValue.call(this)}else{if(this.value!==undefined){this.setValue(this.value)}else{var d=this.textareaEl.value;if(d){this.setValue(d)}}}},beforeDestroy:function(){if(this.ed){tinyMCE.remove(this.ed)}if(this.wrapEl){Ext.destroy(this.wrapEl)}Ext.ux.TinyMCE.superclass.beforeDestroy.call(this)},getRawValue:function(){if(!this.rendered||!this.ed.initialized){return Ext.value(this.value,"")}var d=this.ed.getContent();if(d===this.emptyText){d=""}return d},getValue:function(){if(!this.rendered||!this.ed.initialized){return Ext.value(this.value,"")}var d=this.ed.getContent();if(d===this.emptyText||d===undefined){d=""}return d},setRawValue:function(d){this.value=d;if(this.rendered){this.withEd(function(){this.ed.undoManager.clear();this.ed.setContent(d===null||d===undefined?"":d);this.ed.startContent=this.ed.getContent({format:"raw"})})}},setValue:function(d){this.value=d;if(this.rendered){this.withEd(function(){this.ed.undoManager.clear();this.ed.setContent(d===null||d===undefined?"":d);this.ed.startContent=this.ed.getContent({format:"raw"});this.validate()})}},isDirty:function(){if(this.disabled||!this.rendered){return false}return this.ed&&this.ed.initialized&&this.ed.isDirty()},syncValue:function(){if(this.rendered&&this.ed.initialized){this.ed.save()}},getEd:function(){return this.ed},disable:function(){this.withEd(function(){var d=this.ed.getBody();d=Ext.get(d);if(d.hasClass("mceContentBody")){d.removeClass("mceContentBody");d.addClass("mceNonEditable")}})},enable:function(){this.withEd(function(){var d=this.ed.getBody();d=Ext.get(d);if(d.hasClass("mceNonEditable")){d.removeClass("mceNonEditable");d.addClass("mceContentBody")}})},onResize:function(e,d){if(Ext.type(e)!="number"){e=this.getWidth()}if(Ext.type(d)!="number"){d=this.getHeight()}if(e==0||d==0){return}if(this.rendered&&this.isVisible()){this.withEd(function(){this._setEditorSize(e,d)})}},_setEditorSize:function(d,m){if(!this.ed.theme.AdvancedTheme){return}if(d<100){d=100}if(m<129){m=129}var f=Ext.get(this.ed.id+"_tbl"),i=Ext.get(this.ed.id+"_ifr"),e=Ext.get(this.ed.id+"_xtbar");var o=d;if(f){o=d-f.getFrameWidth("lr")}var j=0;if(e){j=e.getHeight();var l=e.findParent("td",5,true);j+=l.getFrameWidth("tb");e.setWidth(o)}var h=f.child(".mceStatusbar");var n=0;if(h){n+=h.getHeight()}var g=m-j-n;var k=i.findParent("td",5,true);if(k){g-=k.getFrameWidth("tb")}f.setSize(d,m);i.setSize(o,g)},focus:function(e,d){if(d){this.focus.defer(typeof d=="number"?d:10,this,[e,false]);return}this.withEd(function(){this.ed.focus()});return this},processValue:function(d){return Ext.util.Format.stripTags(d)},validateValue:function(d){if(Ext.isFunction(this.validator)){var f=this.validator(d);if(f!==true){this.markInvalid(f);return false}}if(d.length<1||d===this.emptyText){if(this.allowBlank){this.clearInvalid();return true}else{this.markInvalid(this.blankText);return false}}if(d.length<this.minLength){this.markInvalid(String.format(this.minLengthText,this.minLength));return false}if(d.length>this.maxLength){this.markInvalid(String.format(this.maxLengthText,this.maxLength));return false}if(this.vtype){var e=Ext.form.VTypes;if(!e[this.vtype](d,this)){this.markInvalid(this.vtypeText||e[this.vtype+"Text"]);return false}}if(this.regex&&!this.regex.test(d)){this.markInvalid(this.regexText);return false}return true},withEd:function(d){if(!this.ed){this.on("editorcreated",function(){this.withEd(d)},this)}else{if(this.ed.initialized){d.call(this)}else{this.ed.onInit.add(function(){d.defer(10,this)}.createDelegate(this))}}}});Ext.apply(Ext.ux.TinyMCE,{tinymcePlugins:"pagebreak,style,layer,table,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras,template",initTinyMCE:function(e){if(!c){b=Ext.extend(tinymce.WindowManager,{constructor:function(f){b.superclass.constructor.call(this,f.editor);this.manager=f.manager},alert:function(g,f,h){Ext.MessageBox.alert("",g,function(){if(!Ext.isEmpty(f)){f.call(this)}},h)},confirm:function(g,f,h){Ext.MessageBox.confirm("",g,function(i){if(!Ext.isEmpty(f)){f.call(this,i=="yes")}},h)},open:function(f,h){f=f||{};h=h||{};if(!f.type){this.bookmark=this.editor.selection.getBookmark("simple")}f.width=parseInt(f.width||320);f.height=parseInt(f.height||240)+(tinymce.isIE?8:0);f.min_width=parseInt(f.min_width||150);f.min_height=parseInt(f.min_height||100);f.max_width=parseInt(f.max_width||2000);f.max_height=parseInt(f.max_height||2000);f.movable=true;f.resizable=true;h.mce_width=f.width;h.mce_height=f.height;h.mce_inline=true;this.features=f;this.params=h;var g=new Ext.Window({title:f.name,width:f.width,height:f.height,minWidth:f.min_width,minHeight:f.min_height,resizable:true,maximizable:f.maximizable,minimizable:f.minimizable,modal:true,stateful:false,constrain:true,manager:this.manager,layout:"fit",items:[new Ext.BoxComponent({autoEl:{tag:"iframe",src:f.url||f.file},style:"border-width: 0px;"})]});h.mce_window_id=g.getId();g.show(null,function(){if(f.left&&f.top){g.setPagePosition(f.left,f.top)}var i=g.getPosition();f.left=i[0];f.top=i[1];this.onOpen.dispatch(this,f,h)},this);return g},close:function(g){if(!g.tinyMCEPopup||!g.tinyMCEPopup.id){b.superclass.close.call(this,g);return}var f=Ext.getCmp(g.tinyMCEPopup.id);if(f){this.onClose.dispatch(this);f.close()}},setTitle:function(h,g){if(!h.tinyMCEPopup||!h.tinyMCEPopup.id){b.superclass.setTitle.call(this,h,g);return}var f=Ext.getCmp(h.tinyMCEPopup.id);if(f){f.setTitle(g)}},resizeBy:function(g,i,j){var f=Ext.getCmp(j);if(f){var h=f.getSize();f.setSize(h.width+g,h.height+i)}},focus:function(g){var f=Ext.getCmp(g);if(f){f.setActive(true)}}});a=Ext.extend(tinymce.ControlManager,{control:null,constructor:function(h,f,g){this.control=h;a.superclass.constructor.call(this,f,g)},createDropMenu:function(i,g){var f=a.superclass.createDropMenu.call(this,i,g);var h=f.showMenu;f.showMenu=function(j,l,k){h.call(this,j,l,k);Ext.fly("menu_"+this.id).setStyle("z-index",200001)};return f},createColorSplitButton:function(i,g){var f=a.superclass.createColorSplitButton.call(this,i,g);var h=f.showMenu;f.showMenu=function(j,l,k){h.call(this,j,l,k);Ext.fly(this.id+"_menu").setStyle("z-index",200001)};return f}});var d={mode:"none",plugins:Ext.ux.TinyMCE.tinymcePlugins,theme:"advanced"};Ext.apply(d,e);if(!tinymce.dom.Event.domLoaded){tinymce.dom.Event._pageInit()}tinyMCE.init(d);c=true}}});Ext.ComponentMgr.registerType("tinymce",Ext.ux.TinyMCE)})();
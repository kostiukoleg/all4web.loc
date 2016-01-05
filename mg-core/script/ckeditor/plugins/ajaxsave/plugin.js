(function()
{
  var saveCmd =
  {
    modes : { wysiwyg:1, source:1 },
    exec : function( editor )
    {	
	   if(confirm(lang.APPLY_INLINE_EDIT+'?')){
          var id = $(editor.element.$).data('item-id');
          var table = $(editor.element.$).data('table');           
          var field = $(editor.element.$).data('field');
          var content = $(editor.element.$).html();          
          admin.fastSaveField(table,field,id,content);     
        }	
    }
  }
  var pluginName = 'ajaxsave';
  CKEDITOR.plugins.add( pluginName,
  {
     init : function( editor )
     {	 
	
		if($(editor.element.$).attr('contenteditable')=='true'){
			var command = editor.addCommand( pluginName, saveCmd );
			editor.ui.addButton( 'ajaxsave',
			 {
				label : 'Сохранить изменения',
				command : pluginName,
				icon: "plugins/ajaxsave/save-icon.png",
				toolbar: 'saveContent,1',
				contenteditable: false
			 });
	    }
     }
   });
})();
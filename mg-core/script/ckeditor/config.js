 
CKEDITOR.editorConfig = function( config ) {
  var site = admin.SITE.replace(/http(s)?:\/\//, '');
  config.filebrowserUploadUrl = site+'/ajax?mguniqueurl=action/upload';  
	config.toolbarGroups = [   
	
	  { name: 'saveContent' },
		{ name: 'undo', groups: [ 'Source', '-', 'NewPage', 'htmlbuttons', '-', 'Templates' ]},	   	    
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker','ajaxsave' ] },
		{ name: 'links' }, { name: 'colors' },	
		{ name: 'insert' }, { name: 'tools' },{ name: 'htmlbuttons'}, { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },						
		'/',
	 { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },	{ name: 'styles' }, { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
				
	];

  config.extraPlugins = 'ajaxsave';
	config.removeButtons = 'Subscript,Superscript,Source';
	config.format_tags = 'p;h1;h2;h3;pre';
	config.removeDialogTabs = 'image:advanced;link:advanced';
  // добавляем возможность выбрать файл из имеющихся загруженых на серевер
	config.filebrowserBrowseUrl = admin.SITE+'/ajax?mguniqueurl=action/elfinder';
};


 // кастомный метод вызова файлового менеджера
CKEDITOR.on('dialogDefinition', function(event) {
    var editor = event.editor;
    var dialogDefinition = event.data.definition;
    var dialogName = event.data.name;  
    if(dialogName == 'image' || dialogName == 'link'){
    var tabCount = dialogDefinition.contents.length;
    for(var i = 0; i < tabCount; i++) {
        var browseButton = dialogDefinition.contents[i].get('browse');  
        if (browseButton !== null) {
            browseButton.hidden = false;
            browseButton.onClick = function(dialog, i) {
              editor._.filebrowserSe = this;
              // передаем номер отложенной функции для обработки полученного файла из файлового менеджера
              admin.openUploader('uploader.getFileCallbackCKEDITOR',editor._.filebrowserFn);
             // $('.cke_dialog').css('z-index', '90'); 
            }
        }
   }
   }   
});



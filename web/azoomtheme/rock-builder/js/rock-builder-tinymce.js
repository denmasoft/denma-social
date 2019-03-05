(function() {
	var _onclick = function(that,editor){
		console.dir(that);
		switch(that.settings.ev){
			case 'insert' :
			editor.insertContent(that.value());	
			break;
			
			case 'func':
			that.settings.func();
			break;
		}
		return true;
	}
		
	
	
    tinymce.PluginManager.add('rockthemes_plugins', function( editor, url ) {
        editor.addButton( 'rockthemes_plugins', {
			title: 'Shortcodes',
            text: 'Shortcodes',
            type: 'menubutton',
            menu: [
				{
					text: 'Lists (Bullets)',
					ev: 'insert',	
					value: '[rockthemes_list icomoon_icon_class="" icon_color=""]Enter Your List HTML Here...[/rockthemes_list]',
					func: null,
					onclick: function(){_onclick(this,editor);}
				},
				{
					text: 'Dropcaps',
					ev: 'func',	
					value: '',
					func: function(){
						editor.windowManager.open( {
							title: 'Insert Dropcaps',
							body: [{
								type: 'textbox',
								name: 'title',
								label: 'Dropcaps'
							}],
							onsubmit: function( e ) {
								var txt = e.data.title.substr(1),
									f = e.data.title.substr(0,1);
								
								editor.insertContent( '<span class="dropcaps">' + f + '</span>'+txt);
							}
						});	
					},
					onclick: function(){_onclick(this,editor);}
				},
				{
					text: 'Like',
					ev: 'insert',	
					value: '[rockthemes_like id=""]',
					func: null,
					onclick: function(){_onclick(this,editor);}
				},
			]
        });
    });
})();
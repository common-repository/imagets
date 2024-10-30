jQuery(document).ready(function( $ ) {

    var button  = $("#titlewrap");

    button.append("<style>.suggest{position:relative;float:right;margin-top:-40px !important;height:38px !important;border-top-left-radius:0px !important;border-bottom-left-radius:0px !important;vertical-align:middle !important;line-height:34px !important;}.loading-icon{height:9px;display:none;margin-right:5px;}.app-icon{display:inline-block;}</style>");

    button.append('<a href="#TB_inline?width=600&height=550&inlineId=imagets_thickbox" class="suggest button thickbox"><img class="app-icon" src="' + imagets_ajax_script.plugin_url + 'assets/images/icon.png"/><img class="loading-icon" src="' + imagets_ajax_script.plugin_url + 'assets/images/loading.gif"> ImageTS</a>');

});

function imagets_createGallery(data, response){

	if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
		if(jQuery('textarea#content').val().search("gallery")>-1){
		}else{
			jQuery('textarea#content').val(jQuery('textarea#content').val() + '[gallery ids="' + response + '"]');
		}
	} else {
		if(tinyMCE.activeEditor.getContent().search("gallery")>-1){
		}else{
	  		tinyMCE.execCommand('mceInsertRawHTML', false, '[gallery ids="' + response + '"]');
		}
	}

	tb_remove();
}

function imagets_addImages(data){
	if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
	  jQuery('textarea#content').val(jQuery('textarea#content').val() + data);
	} else {
	  tinyMCE.execCommand('mceInsertRawHTML', false, data);
	}
}

function imagets_downloadURI(uri, name) {
    var link = document.createElement("a");
    link.download = name;
    link.href = uri;
    link.click();
}

function imagets_closeThickBox(){
	tb_remove();
}
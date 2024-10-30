function getElement(name){
    return jQuery("*[name=\'" + name + "\']").val();
}

function getCheckbox(name){
    return jQuery("*[name=\'" + name + "\']").prop("checked");
}

function formChanged(){
    if(getCheckbox("watermark_visible")){
        watermark = getElement("watermark");
    }else{
        watermark = null;
    }

    if(getCheckbox("border_visible")){
        border = {color:getElement("border_color"), weight:getElement("border_weight")};
    }else{
        border = null;
    }

    var applyValues = {flip : getCheckbox("flip"), quality : getElement("quality"), layer : getElement("layer"), w_percent : getElement("w_percent"), opacity : getElement("opacity"), watermark : watermark, position : getElement("position"), border : border, crop : [getElement("crop_x"),getElement("crop_y")], width : getElement("width"), height : getElement("height")};

    jQuery.ajax({
        type: "POST",
        url: location.protocol + "//imagets.com/api/c3b177f8db",
        data: JSON.stringify({images: ["http://imagets.com/images/sample.jpg"], apply: applyValues}),
        success: function(data){
            jQuery("#preview-img").attr("src",data[0].src + "?rand" + Math.random(0,5000));
        },
        dataType: "json"
    });
}

jQuery(document).ready(function(){
	jQuery("input[name='watermark']").after("<img class='mini-preview' src='" + jQuery("input[name='watermark']").val() + "'>");jQuery(".mini-preview").attr("src",jQuery("input[name='watermark']").val());jQuery("input[name='watermark']").change(function(){jQuery(".mini-preview").attr("src",jQuery(this).val());});jQuery("input").change(function(){formChanged()});jQuery("select").change(function(){formChanged()});formChanged();
});
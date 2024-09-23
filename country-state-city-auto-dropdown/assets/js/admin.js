jQuery(function($){
 infoTip("select#patch");
function infoTip(div) {
 var tip=$(div).find("option:selected").attr('data-tip');
 $(".select-info-tip").html(tip);
}
$("select#patch").change(function(){
  infoTip($(this));
});
$("#update-patch").click(function(e){
    var optval= $("#patch").val();
    var cnt= $("#patch").find("option:selected").attr('data-country');
    jQuery.ajax({
        url: tc_csca_auto_ajax.ajax_url,
        type: 'post',
        dataType: "json",
        data: { action: "tc_csca_patch_settings",
        nonce_ajax: tc_csca_auto_ajax.nonce,
        value:optval,country:cnt },
        success: function (response) {
          console.log(response);
if(response.message) {
  if($(".tc_response_update").length>0) {
    $(".tc_response_update").remove();
  }
$(".patch-button").append("<div class='tc_response_update'>"+response.message+"</div>");
setTimeout(function(){$(".tc_response_update").fadeOut().remove();},5000)
  }
}
    });
});
});
jQuery(document).ready(function($){

    // 1) Intercept paste en #mspEditor
    $("#mspEditor").on("paste", function(e){
        e.preventDefault();
        var cd = (e.originalEvent || e).clipboardData;
        var htmlData = cd.getData("text/html");
        if (!htmlData) {
            htmlData = cd.getData("text/plain");
        }
        document.execCommand("insertHTML", false, htmlData);
    });

    // 2) Botón Save
    $("#mspBtnSave").on("click", function(){
        let raw = $("#mspEditor").html();
        $.post(mspData.ajaxUrl, {
            action: "msp_save_signature",
            mspNonce: mspData.mspNonce,
            signature: encodeURIComponent(raw)
        }, function(response){
            if (response.success) {
                alert("Signature saved successfully.");
            } else {
                alert("Error saving signature: " + response.data);
            }
        });
    });

    // 3) Botón Remove
    $("#mspBtnRemove").on("click", function(){
        $.post(mspData.ajaxUrl, {
            action: "msp_delete_signature",
            mspNonce: mspData.mspNonce
        }, function(response){
            if (response.success) {
                $("#mspEditor").html("");
                alert("Signature removed successfully.");
            } else {
                alert("Error removing signature: " + response.data);
            }
        });
    });
});
/* Author: Dylan Swartz

*/

$("#createButton").click(function() {
    // make the request via ajax
    var postData = $("#createForm").serialize();

    $.ajax({
        type: "POST",
        url: "process.php?p=create",
        data:  postData,
        success: function(data){
            $("#feedback").html(data);
            $("#feedback").fadeIn('slow');
            $("#createForm #databaseName").val('');
            setTimeout(function() { $('#feedback').fadeOut(); }, 3000);
        }
    });

    return false;
});























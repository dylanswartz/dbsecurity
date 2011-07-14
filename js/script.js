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
        }
    });

    return false;
});























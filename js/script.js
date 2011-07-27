/* Author: Dylan Swartz

*/

$("#createButton").click(function() {
    // make the request via ajax
    var postData = $("#createForm").serialize();

    $.ajax({
        type: "POST",
        url: "process.php?p=create",
        data:  postData,
        dataType : 'json',
        success: function(data){
           // $("#feedback").html(data.msg);
            $("#feedback")
                                    .removeClass()
                                    .addClass((data.error !== true) ? 'good' : 'bad')
                                    .text(data.msg)
                                    .fadeIn('slow');

            $("#createForm #databaseName").val('');

            setTimeout(function() { $('#feedback').fadeOut(); }, 3000);
        }
    });

    return false;
});























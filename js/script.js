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
            $("#createForm #databaseName").val('');

            $("#feedback")
                                    .removeClass()
                                    .addClass((data.error !== true) ? 'good' : 'bad')
                                    .text(data.msg)
                                    .fadeIn('slow');

            setTimeout(function() { $('#feedback').fadeOut(); }, 3000);
        }
    });

    return false;
});























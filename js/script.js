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

// All the jQuery goodness inside this .ready function
// will execute when the page is fully loaded
$(document).ready(function() {
    //$("#stargate").load("create.php");

   if ($("#manageCanvas").length) {

       $.get("process.php?p=loadmanage",
       function(data){

                $("#manageCanvas").html("Weeeee!")
                                                  .wrapInner("<form>");

                $("#feedback")
                                        .removeClass()
                                        .addClass((data.error !== true) ? 'good' : 'bad')
                                        .text(data.msg)
                                        .fadeIn('slow');

                setTimeout(function() { $('#feedback').fadeOut(); }, 10000);
            },
            "json");
   }

});



















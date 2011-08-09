<?php
require_once("config.php");

//--------------------------------------------------------------------------------------------------
// This function returns a JSON object containing
// a feedback message and a value of "good" or  "bad".
// @input - $message: a string decribing how the script ended
// @input - [$error]: true, if the script ended in error
// @return - a JSON object with status and message properties
function feedback($message, $error = false) {
    $return['msg'] = $message;
    $return['error'] = $error;

    return json_encode($return);
}
//--------------------------------------------------------------------------------------------------

//--------------------------------------------------------------------------------------------------
// This function selects all of the database owned by a specific
// user. If no user is provided, it will get it from
//  $_SERVER['PHP_AUTH_USER'];
//--------------------------------------------------------------------------------------------------
function getDatabases($user = false) {
    if (!$user) {
        $user = $_SERVER['PHP_AUTH_USER'];
    }
    $message   = "BOOM! We got your databases. ;)";
    $errorFlag = false;     // assume no errors
    //
    // connect to the database
    $con = mysql_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD);
    if (!$con) {
        $errorFlag = true;
        // kill everything
        //die("Error connecting to database. " . mysql_error());
       $message ="Failed to connect to database.";
    }
    mysql_select_db(DB_NAME, $con);

    $query = "SELECT * FROM database_list WHERE creator = '$user'";
    $result = mysql_query($query);

    if (mysql_num_rows($result) < 1) {
        $errorFlag = true;
        $message ="Errr...You don't have any databases. How sad. ='[";
    } else {
        while ($row = mysql_fetch_assoc($result)) {
            
        }
    }
    return $message;
}
//--------------------------------------------------------------------------------------------------

?>

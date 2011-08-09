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



?>

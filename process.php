<?php
    include('config.php');

    // This function returns a JSON object containing a feedback
    // message and a value of "good" or "bad".
    // @input - $message: a string decribing how the script ended
    // @input - $error: a optional boolen value indication if the script ended in error
    // @return - a JSON encoded object with status and message properties
    function feedback($message, $error = false) {
        $return['msg'] = $message;
        $return['status'] = $error;

       return json_encode($return);
    }

    function getUserId() {
        return 1;
    }

    function clean($value) {
        return $value;
    }

    $errorFlag = false;     // assume no errors
    // connect to the database
    $con = mysql_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD);
    if (!$con) {
        $errorFlag = true;
        // kill everything
        //die("Error connecting to database. " . mysql_error());
        echo feedback("Failed to connect to database.", $errorFlag);
    } else {
        mysql_select_db(DB_NAME, $con);
    }

     // get the action to preform
    $p = clean($_GET["p"]);

    // determine what to do with said action
    switch ($p) {
        case "create":
            // get necessary data
            $userId = getUserId();
            $databaseName = clean($_POST["databaseName"]);
            $jobType = "create";

            // send a create job into the queue
            $query = "INSERT INTO jobs (userId, databaseName, job)
                             VALUES ('$userId', '$databaseName', '$jobType')";

            $result =mysql_query($query);

            if (!$result) {
                $errorFlag = true;
                // kill everything
                //die("Error updating databse." . mysql_error());
                echo feedback("Failed to connect to database.", $errorFlag);
            }

            //echo "$databaseName is now scheduled to be created!";
            if (!$errorFlag) 
                echo feedback("$databaseName is now scheduled to be created!");
             else
                echo feedback("An unknown error has occured.", $errorFlag);
            break;
        default:
            // show main "create form"
            break;
    }

    mysql_close($con);
?>

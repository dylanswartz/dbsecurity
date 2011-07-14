<?php
    function errorDetected() {

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
        die("Error connecting to database.");
    } else {
        mysql_select_db("my_db", $con);
    }

     // get the action to preform
    $p = clean($_GET["p"]);

    // determine what to do with said action
    switch ($p) {
        case "create":
            // get necessary data
            $userId = getUserId();
            $databaseName = clean($_POST["databaseName"]);
            $jobType = "create"

            // send a create job into the queue
            $query = "INSERT INTO jobs (
                            userId,
                            databaseName,
                            jobType)
                            VAULES (
                            )"
            break;
        default:
            // show main "create form"
            break;
    }

    mysql_close($con);
?>

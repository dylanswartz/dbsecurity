<?php
    //-------------------------------------------------------------------------------------------------------------------------------
    function getUser() {
        return $_SERVER["PHP_AUTH_USER"];
    }
    //-------------------------------------------------------------------------------------------------------------------------------

    //-------------------------------------------------------------------------------------------------------------------------------
    function clean($value) {
        return $value;
    }
    //-------------------------------------------------------------------------------------------------------------------------------

    require_once('php/functions.php');
    require_once('php/config.php');

    $errorFlag = false;     // assume no errors
    // connect to the database
    $con = mysql_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD);
    if (!$con) {
        $errorFlag = true;
        // kill everything
        //die("Error connecting to database. " . mysql_error());
        die(feedback("Failed to connect to database.", $errorFlag));
    } 
    mysql_select_db(DB_NAME, $con);

     // get the action to preform
    $p = clean($_GET["p"]);

    // determine what to do with said action
    switch ($p) {
        case "create":
            // get necessary data
            $user = getUser();
            $databaseName = clean($_POST["databaseName"]);
            $jobType = "create";

            // send a create job into the queue
            $query = "INSERT INTO jobs (user, databaseName, job)
                             VALUES ('$user', '$databaseName', '$jobType')";

            $result =mysql_query($query);

            if (!$result) {
                $errorFlag = true;
                // kill everything
                //die("Error updating databse." . mysql_error());
                die(feedback("Failed to connect to database.", $errorFlag));
            }

            //echo "$databaseName is now scheduled to be created!";
            if (!$errorFlag)
                echo feedback("$databaseName is now scheduled to be created!");
             else
                echo feedback("An unknown error has occured.", $errorFlag);
            break;
       case "loadmanage":
            $return['msg'] = "An error occured! Oh, noes!";
            $return['error'] = true;
            $userDatabases = array();
            $user = getUser();
            $query = "SELECT * FROM database_list WHERE creator = '$user'";
            $result = mysql_query($query);

            if (mysql_num_rows($result) < 1) {
                $errorFlag = true;
                $message ="Errr...You don't have any databases. How sad. ='[";
            } else {
                while ($row = mysql_fetch_assoc($result)) {
                    $userDatabases[] = $row;
                    $return['error'] = false;
                    $return['msg'] = "Yuuss! We found your databases! :D";
                }
            }
            $return['records'] = $userDatabases;
            echo json_encode($return);
            break;
        default:
            // show main "create form"
            break;
    }
    mysql_close($con);
    
?>

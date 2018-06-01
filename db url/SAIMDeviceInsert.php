<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));    
    
    
    if (isset ($_POST["deviceID"]) && isset($_POST["deviceToken"]) && isset ($_POST["remark"]))
    {
        $deviceID = $_POST["deviceID"];
        $deviceToken = $_POST["deviceToken"];
        $remark = $_POST["remark"];
    }
    else
    {
        $deviceID = 0;
        $deviceToken = "";
        $remark = "";
    }
    
    
    {
        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        
        
        // Set autocommit to off
        mysqli_autocommit($con,FALSE);
        writeToLog("set auto commit to off");
        
        
        //query statement
        $sql = "insert into `Device` (`DeviceToken`, `Remark`) values('$deviceToken','$remark')";
        $res = mysqli_query($con,$sql);
        if(!$res)
        {
            $error = "query fail, sql: " . $sql . ", modified device: " . $deviceToken . " error: " . mysqli_error($con);
            writeToLog($error);
            
            
            putAlertToDevice($_POST["modifiedUser"],$_POST["modifiedDeviceToken"]);
            $response = array('status' => $error);
            echo json_encode($response);
            exit();
        }
        else
        {
            writeToLog("query success, sql: " . $sql . ", modified device: " . $deviceToken);
            
            $returnID = mysqli_insert_id($con);
        }
    }
    
    
    
    //do script successful
    writeToLog("query commit, file: " . basename(__FILE__));
    mysqli_commit($con);
    mysqli_close($con);
    $response = array('status' => '1', 'sql' => $sql, 'returnID' => $returnID);


    echo json_encode($response);
    exit();
?>

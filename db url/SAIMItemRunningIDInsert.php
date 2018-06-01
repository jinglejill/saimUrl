<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    if (isset ($_POST["countQR"])
        )
    {
        $countQR = $_POST["countQR"];
    }
    else
    {
        $countQR = 0;
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
        $sql = "INSERT INTO `itemRunningID`(`ModifiedDate`) VALUES (now())";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        $itemRunningID = mysqli_insert_id($con);
        $lastItemRunningID = intval($itemRunningID)+intval($countQR)-1;
        
        
        if(intval($countQR) > 1)
        {
            //query statement
            $sql = "INSERT INTO `itemRunningID`(`RunningID`) VALUES ($lastItemRunningID)";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
            $itemRunningID = mysqli_insert_id($con);
        }
        
    }
    
    
    
    //do script successful
    writeToLog("query commit, file: " . basename(__FILE__));
    mysqli_commit($con);
    mysqli_close($con);
    $response = array('status' => '1', 'sql' => $sql, 'ID' => $itemRunningID);
    
    
    echo json_encode($response);
    exit();
?>

<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    if (isset ($_POST["pushSyncID"])
        )
    {
        $pushSyncID = $_POST["pushSyncID"];
    }
    else
    {
        $pushSyncID = -1;
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
        //ไม่ต้อง push notification เพราะใน app ไม่มีเรียกใช้ column ที่ update นี้
        $sql = "UPDATE `pushSync` SET `TimeSynced` = now() WHERE `PushSyncID` = $pushSyncID";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
    }
    
    
  
    //do script successful
    writeToLog("query commit, file: " . basename(__FILE__));
    mysqli_commit($con);
    mysqli_close($con);
    $response = array('status' => '1', 'sql' => $sql);

    
    echo json_encode($response);
    exit();
?>

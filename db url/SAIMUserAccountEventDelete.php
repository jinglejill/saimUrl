<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (isset ($_POST["userAccountID"])){
        $userAccountID = $_POST["userAccountID"];
    } else {
        $userAccountID = 0;
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
        
        
        //select row ที่ delete ขึ้นมาเก็บไว้ก่อน
        $sql = "select * from UserAccountEvent where `UserAccountID`= $userAccountID";
        $selectedRow = getSelectedRow($sql);
        
        
        //query statement
        $sql = "delete from `UserAccountEvent` where userAccountEventID in (select userAccountEventID from (select userAccountEventID from UserAccountEvent where `UserAccountID`= $userAccountID order by userAccountEventID) tempTable)";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        //broadcast ไป device token อื่น
        $type = 'tUserAccountEvent';
        $action = 'd';
        $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        //-----
        
    }
    
    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));
    $response = array('status' => '1', 'sql' => $sql);
    
    
    echo json_encode($response);
    exit();
?>

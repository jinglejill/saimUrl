<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    if (isset ($_POST["cashAllocationID"]) &&
        isset ($_POST["eventID"]) &&
        isset ($_POST["cashChanges"]) &&
        isset ($_POST["cashTransfer"]) &&
        isset ($_POST["inputDate"])
        ){
        
        $cashAllocationID = $_POST["cashAllocationID"];
        $eventID = $_POST["eventID"];
        $cashChanges = $_POST["cashChanges"];
        $cashTransfer = $_POST["cashTransfer"];
        $inputDate = $_POST["inputDate"];
        
    } else {
        $cashAllocationID = 0;
        $eventID = 0;
        $cashChanges = 0;
        $cashTransfer = 0;
        $inputDate = "";
        
    }
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    //query statement
    $sql = "UPDATE `cashallocation` SET `CashChanges`=$cashChanges,`CashTransfer`=$cashTransfer WHERE `EventID`=$eventID and `InputDate`='$inputDate'";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }

    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select * from cashallocation where `EventID`=$eventID and `InputDate`='$inputDate'";
    $selectedRow = getSelectedRow($sql);
    
    
    //broadcast ไป device token อื่น
    $type = 'tCashAllocation';
    $action = 'u';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
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

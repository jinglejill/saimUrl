<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    if (isset ($_POST["cashAllocationID"]) &&
        isset ($_POST["eventID"]) &&
        isset ($_POST["cashChanges"]) &&
        isset ($_POST["cashTransfer"]) &&
        isset ($_POST["inputDate"])
        )
    {
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
        $inputDate = '';
    }
    $modifiedUser = $_POST["modifiedUser"];
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    //query statement
    $sql = "INSERT INTO CashAllocation(EventID, CashChanges, CashTransfer, InputDate) VALUES ('$eventID', '$cashChanges', '$cashTransfer', '$inputDate')";
    $ret = doQueryTask2($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //insert ผ่าน
    $newID = mysqli_insert_id($con);
    
    
    
    //device ตัวเอง ลบแล้ว insert
    //sync generated id back to app
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select $cashAllocationID as CashAllocationID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'tCashAllocation';
    $action = 'd';
    $ret = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $cashAllocationID = $newID;
    $sql = "select *, 1 IdInserted from CashAllocation where CashAllocationID = '$cashAllocationID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'tCashAllocation';
    $action = 'i';
    $ret = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //****device อื่น insert
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from CashAllocation where CashAllocationID = '$cashAllocationID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'tCashAllocation';
    $action = 'i';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    
    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));
    $response = array('status' => '1', 'sql' => $sql);
    
    
    echo json_encode($response);
    exit();
?>

<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (isset ($_POST["eventID"]) && isset ($_POST["location"]) && isset ($_POST["remark"]) && isset ($_POST["periodFrom"]) && isset ($_POST["periodTo"]) && isset ($_POST["productSalesSetID"])){
        $eventID = $_POST["eventID"];
        $location = $_POST["location"];
        $remark = $_POST["remark"];
        $periodFrom = $_POST["periodFrom"];
        $periodTo = $_POST["periodTo"];
        $productSalesSetID = $_POST["productSalesSetID"];
    } else {
        $eventID = 0;
        $location = "-";
        $remark = "-";
        $periodFrom = "00/00/00";
        $periodTo = "00/00/00";
        $productSalesSetID = -1;
    }
    $modifiedUser = $_POST["modifiedUser"];
    
    
    
    //query statement
    $sql = "INSERT INTO Event(Location, Remark, PeriodFrom, PeriodTo, ProductSalesSetID) VALUES ('$location', '$remark', '$periodFrom', '$periodTo', '$productSalesSetID')";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //insert ผ่าน
    $newID = mysqli_insert_id($con);
    
    
    
//    //device ตัวเอง ลบแล้ว insert
//    //sync generated id back to app
//    //select row ที่แก้ไข ขึ้นมาเก็บไว้
//    $sql = "select $eventID as EventID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
//    $selectedRow = getSelectedRow($sql);
//
//
//
//    //broadcast ไป device token ตัวเอง
//    $type = 'tEvent';
//    $action = 'd';
//    $ret = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
//    if($ret != "")
//    {
//        mysqli_rollback($con);
//        putAlertToDevice($_POST["modifiedUser"]);
//        echo json_encode($ret);
//        exit();
//    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $eventID = $newID;
//    $sql = "select *, 1 IdInserted from Event where EventID = '$eventID'";
//    $selectedRow = getSelectedRow($sql);
//
//
//
//    //broadcast ไป device token ตัวเอง
//    $type = 'tEvent';
//    $action = 'i';
//    $ret = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
//    if($ret != "")
//    {
//        mysqli_rollback($con);
//        putAlertToDevice($_POST["modifiedUser"]);
//        echo json_encode($ret);
//        exit();
//    }
    
    
    
    //****device อื่น insert
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from Event where EventID = '$eventID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'tEvent';
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
  
    
    /* execute multi query */
    $dataJson = executeMultiQueryArray($sql);
    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));
    $response = array('status' => '1', 'sql' => $sql, 'tableName' => 'Event', dataJson => $dataJson);


    echo json_encode($response);
    exit();
?>

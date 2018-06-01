<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    

    
    //ถ้า switch มาใช้เครื่องนี้ ให้ push notification ไปหา device อันก่อนหน้าว่า you have login in another device และ unwind to หน้า sign in

    if (isset ($_POST["username"]) && isset ($_POST["deviceToken"])){
        $username = $_POST["username"];
        $deviceToken = $_POST["deviceToken"];
    } else {
        $username = "jinglejill@hotmail.com";//"-";
        $deviceToken = "-";
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    $sql = "select * from `UserAccount` where `Username` = '$username'";
    $selectedRow = getSelectedRow($sql);
    $deviceTokenOld = $selectedRow[0]["DeviceToken"];
    
    //query statement
    $sql = "update `UserAccount` set `DeviceToken` = '$deviceToken' where `Username` = '$username'";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select `UserAccountID`, Username, Password, `DeviceToken`, `ModifiedDate` from UserAccount where Username = '$username'";
    $selectedRow = getSelectedRow($sql);
    
    
    //broadcast ไป device token อื่น
    $type = 'tUserAccount';
    $action = 'u';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    
    $type = 'usernameconflict';
    $action = '-';
    $data = '-';
    
    
    //query statement, push to only old token device
    $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$deviceTokenOld','$type','$action','$data',now())";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    $pushSyncID = mysqli_insert_id($con);
    sendPushNotificationToDevice($deviceTokenOld);

    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));
    $response = array('status' => '1', 'sql' => $sql);
    
    
    echo json_encode($response);
    exit();
?>

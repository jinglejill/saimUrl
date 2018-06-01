<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    //ถ้า switch มาใช้เครื่องนี้ ให้ push notification ไปหา device อันก่อนหน้าว่า you have login in another device และ unwind to หน้า sign in

    if (isset ($_POST["settingID"]) && isset ($_POST["value"])){
        $settingIDAdminDeviceToken = $_POST["settingID"];
        $deviceToken = $_POST["value"];
    } else {
        $settingIDAdminDeviceToken = "8";//"-";
        $deviceToken = "40d0dcd2571eae1d2230b750d1e6b1da7b58dd09a1c4e0ea3340985fd1eba6b5";//ipad
        $_POST["modifiedUser"] = 'jinglejill@hotmail.com';
        $_POST["modifiedDeviceToken"] = "40d0dcd2571eae1d2230b750d1e6b1da7b58dd09a1c4e0ea3340985fd1eba6b5";
    }
    
    
    $sql = "select * from `Setting` where `SettingID` = '$settingIDAdminDeviceToken'";
    $selectedRow = getSelectedRow($sql);
    $deviceTokenOld = $selectedRow[0]["Value"];
    
    
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
        $sql = "update `Setting` set `Value` = '$deviceToken' where `SettingID` = '$settingIDAdminDeviceToken'";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from Setting where SettingID = '$settingIDAdminDeviceToken'";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tSetting';
        $action = 'u';
        $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        
        //-----
        
        
        
        $type = 'adminconflict';
        $action = '-';
        $data = '';
        
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
        
        
        //send push notification ไปหา devicetokenold
        $paramBody = array(
                           'badge' => 0
//                           'type' => $type,
//                           'pushSyncID' => strval($pushSyncID)
                           );
        sendPushNotification($deviceTokenOld, $paramBody);
        
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

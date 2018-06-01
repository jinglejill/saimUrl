<?php
    include_once('./dbConnect.php');
    setConnectionValue(trim($_POST['dbName']));
    writeToLog("file: " . basename(__FILE__));
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    
    $dbName = trim($_POST['dbName']);
    $target_path = './' . $dbName . '/Uploads/';
    writeToLog("target_path: " .  $target_path);
    
    if (!file_exists($target_path)) {
        mkdir($target_path, 0777, true);
    }
    
    
    
    $target_path = $target_path . basename( $_FILES['userfile']['name'] );
    
    if(move_uploaded_file($_FILES['userfile']['tmp_name'], $target_path))
    {
        writeToLog("upload success");
        
        
        //successful move
        $response = array('status' => 'successful');
    }
    else
    {
        $error = "upload fail, not uploaded because of error #" .$_FILES["file"]["error"] . ", modified user: " . $user;
        writeToLog($error);
        
        //unsuccessful move
        $response = array('status' => 'fail');
        
        
        //--------- ใช้สำหรับกรณี หน้าที่เรียกใช้ homemodel back ออกจากหน้าตัวเองไปแล้ว
        // Set autocommit to on
        mysqli_autocommit($con,TRUE);
        writeToLog("set auto commit to on");
        
        
        //alert query fail-> please check recent transactions again
        $type = 'alertUploadPhotoFail';
        $action = '';
        
        
        
        $deviceToken = getDeviceTokenFromUsername($user);
        $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$deviceToken','$type','$action','',now())";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            echo json_encode($ret);
            exit();
        }
        
        
        $pushSyncID = mysqli_insert_id($con);
        writeToLog('pushsyncid: '.$pushSyncID);
        $paramBody = array(
                           'badge' => 0
                           //                               'type' => $type,
                           //                               'pushSyncID' => strval($pushSyncID)
                           );
        sendPushNotification($deviceToken, $paramBody);
        //----------
    }
    
    
    echo json_encode($response);
    ?>

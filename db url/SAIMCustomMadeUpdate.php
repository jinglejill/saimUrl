<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    
    
    if(isset($_POST["customMadeID"]) &&
       isset($_POST["size"]) &&
       isset($_POST["toe"]) &&
       isset($_POST["body"]) &&
       isset($_POST["accessory"]) &&
       isset($_POST["remark"])
       )
    {
        $customMadeID = $_POST["customMadeID"];
        $size = $_POST["size"];
        $toe = $_POST["toe"];
        $body = $_POST["body"];
        $accessory = $_POST["accessory"];
        $remark = $_POST["remark"];
    }
    else
    {
        $customMadeID = 0;
        $size = "-1";
        $toe = "-1";
        $body = "-1";
        $accessory = "-1";
        $remark = "-1";
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
        $sql = "UPDATE `custommade` SET Size='$size', Toe='$toe', Body='$body', Accessory='$accessory', remark='$remark' WHERE CustomMadeID = $customMadeID";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from custommade where CustomMadeID = $customMadeID";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tCustomMade';
        $action = 'u';
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

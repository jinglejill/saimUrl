<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    if (isset ($_POST["rewardProgramID"]) &&
        isset ($_POST["type"]) &&
        isset ($_POST["dateStart"]) &&
        isset ($_POST["dateEnd"]) &&
        isset ($_POST["salesSpent"]) &&
        isset ($_POST["receivePoint"]) &&
        isset ($_POST["pointSpent"]) &&
        isset ($_POST["discountType"]) &&
        isset ($_POST["discountAmount"])
        )
    {
        $rewardProgramID = $_POST["rewardProgramID"];
        $type = $_POST["type"];
        $dateStart = $_POST["dateStart"];
        $dateEnd = $_POST["dateEnd"];
        $salesSpent = $_POST["salesSpent"];
        $receivePoint = $_POST["receivePoint"];
        $pointSpent = $_POST["pointSpent"];
        $discountType = $_POST["discountType"];
        $discountAmount = $_POST["discountAmount"];
        
    } else {
        $rewardProgramID = 0;
        $type = -2;
        $dateStart = '2017-01-01';
        $dateEnd = '2017-01-01';
        $salesSpent = -1;
        $receivePoint = -1;
        $pointSpent = -1;
        $discountType = -1;
        $discountAmount = -1;
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
    $sql = "INSERT INTO RewardProgram(Type, DateStart, DateEnd, SalesSpent, ReceivePoint, PointSpent, DiscountType, DiscountAmount) VALUES ('$type', '$dateStart', '$dateEnd', '$salesSpent', '$receivePoint', '$pointSpent', '$discountType', '$discountAmount')";
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
    
    
    
    //device ตัวเอง ลบแล้ว insert
    //sync generated id back to app
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select $rewardProgramID as RewardProgramID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'tRewardProgram';
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
    $rewardProgramID = $newID;
    $sql = "select *, 1 IdInserted from RewardProgram where RewardProgramID = '$rewardProgramID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'tRewardProgram';
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
    $sql = "select *, 1 IdInserted from RewardProgram where RewardProgramID = '$rewardProgramID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'tRewardProgram';
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

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
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    //query statement
    $sql = "UPDATE `rewardprogram` SET `Type`=$type,`DateStart`='$dateStart',`DateEnd`='$dateEnd',`SalesSpent`=$salesSpent,`ReceivePoint`=$receivePoint,`PointSpent`=$pointSpent,`DiscountType`=$discountType,`DiscountAmount`=$discountAmount WHERE RewardProgramID = $rewardProgramID";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }

    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select * from rewardprogram where `RewardProgramID`='$rewardProgramID'";
    $selectedRow = getSelectedRow($sql);
    
    
    //broadcast ไป device token อื่น
    $type = 'tRewardProgram';
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

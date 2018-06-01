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
        $sql = "update `Event` set `Location` = '$location', `Remark` = '$remark',`PeriodFrom` = '$periodFrom',`PeriodTo` = '$periodTo',`ProductSalesSetID` = $productSalesSetID where `EventID` = $eventID";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from Event where `EventID` = $eventID";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tEvent';
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

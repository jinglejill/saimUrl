<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (
        isset ($_POST["runningSetNo"]) &&
        isset ($_POST["productID"]) &&
        isset ($_POST["productCode"]) &&
        isset ($_POST["compareStatus"]) &&
        isset ($_POST["compareStatusRemark"])
        )
    {
        $runningSetNo = $_POST["runningSetNo"];
        $productID = $_POST["productID"];
        $productCode = $_POST["productCode"];
        $compareStatus = $_POST["compareStatus"];
        $compareStatusRemark = $_POST["compareStatusRemark"];
    }
    else
    {
        $runningSetNo = 0;
        $productID = '';
        $productCode = '';
        $compareStatus = '';
        $compareStatusRemark = '';
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
        $sql = "INSERT INTO `compareinventory`(`RunningSetNo`, `ProductID`,`ProductCode`, `CompareStatus`, `CompareStatusRemark`) values($runningSetNo, '$productID','$productCode', '$compareStatus', '$compareStatusRemark')";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        $compareInventoryID = mysqli_insert_id($con);
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from compareinventory where `CompareInventoryID` = $compareInventoryID";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tCompareInventory';
        $action = 'i';
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

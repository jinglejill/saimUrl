
<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    if(isset($_POST["runningAccountReceiptHistory"]))
    {
        $runningAccountReceiptHistory = $_POST["runningAccountReceiptHistory"];
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    
    //-----accountInventory
    //select row ที่ delete ขึ้นมาเก็บไว้
    $sql = "select * from accountInventory where runningAccountReceiptHistory = $runningAccountReceiptHistory";
    $selectedRow = getSelectedRow($sql);
    
    
    //query statement
    $sql = "DELETE FROM `accountInventory` where runningAccountReceiptHistory = $runningAccountReceiptHistory";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    //broadcast ไป device token อื่น
    $type = 'tAccountInventory';
    $action = 'd';
    $ret = doPushNotificationTaskAsLog($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    

    
    //-----accountMapping
    //select row ที่ delete ขึ้นมาเก็บไว้
    $sql = "select * from accountInventory where runningAccountReceiptHistory = $runningAccountReceiptHistory";
    $selectedRow = getSelectedRow($sql);
    
    
    //query statement
    $sql = "DELETE FROM `accountMapping` where runningAccountReceiptHistory = $runningAccountReceiptHistory";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    //broadcast ไป device token อื่น
    $type = 'tAccountMapping';
    $action = 'd';
    $ret = doPushNotificationTaskAsLog($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //-----accountReceiptProductItem
    //select row ที่ delete ขึ้นมาเก็บไว้
    $sql = "select * FROM `accountReceipt` where runningAccountReceiptHistory = $runningAccountReceiptHistory";
    $selectedRow = getSelectedRow($sql);
    $countSelectedRow = sizeof($selectedRow);
    
    if($countSelectedRow > 0)
    {
        $accountReceiptID = $selectedRow[0]["AccountReceiptID"];
        $sql = "select * FROM `accountReceiptProductItem` where (accountReceiptID = $accountReceiptID)";
        for($i=1; $i<$countSelectedRow; $i++)
        {
            $accountReceiptID = $selectedRow[$i]["AccountReceiptID"];
            $sql .= " or (accountReceiptID = $accountReceiptID)";
        }
    }
    $selectedRow = getSelectedRow($sql);
    
    
    //query statement
    $sql = str_replace('select * FROM `accountReceiptProductItem`','DELETE FROM `accountReceiptProductItem`',$sql);
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    //broadcast ไป device token อื่น
    $type = 'tAccountReceiptProductItem';
    $action = 'd';
    $ret = doPushNotificationTaskAsLog($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //-----accountReceipt
    //select row ที่ delete ขึ้นมาเก็บไว้
    $sql = "select * FROM `accountReceipt` where runningAccountReceiptHistory = $runningAccountReceiptHistory";
    $selectedRow = getSelectedRow($sql);
    
    
    //query statement
    $sql = "DELETE FROM `accountReceipt` where runningAccountReceiptHistory = $runningAccountReceiptHistory";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    //broadcast ไป device token อื่น
    $type = 'tAccountReceipt';
    $action = 'd';
    $ret = doPushNotificationTaskAsLog($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    //do script successful
    // ไม่ sync เพราะโหลดใหม่ตลอด ไม่ได้ใช้ Shared model
//    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    writeToLog("query commit, file: " . basename(__FILE__));
    mysqli_commit($con);
    mysqli_close($con);
    $response = array('status' => '1', 'sql' => $sql);
    
    
    echo json_encode($response);
    exit();
?>

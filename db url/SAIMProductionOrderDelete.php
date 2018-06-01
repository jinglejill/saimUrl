
<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    if(isset($_POST["productionOrderID"]))
    {
        $productionOrderID = $_POST["productionOrderID"];
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    //select row ที่ delete ขึ้นมาเก็บไว้
    $sql = "select * from productionOrder where productionOrderID = $productionOrderID";
    $selectedRow = getSelectedRow($sql);
    
    
    //query statement
    $sql = "DELETE FROM `productionOrder` where productionOrderID = $productionOrderID";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    //broadcast ไป device token อื่น
    $type = 'tProductionOrder';
    $action = 'd';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //do script successful
//    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    writeToLog("query commit, file: " . basename(__FILE__));
    mysqli_commit($con);
    mysqli_close($con);
    $response = array('status' => '1', 'sql' => $sql);
    
    
    echo json_encode($response);
    exit();
?>

<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (isset ($_POST["eventDestination"]) &&
        isset ($_POST["eventSource"])
        ){
        $eventDestination = $_POST["eventDestination"];
        $eventSource = $_POST["eventSource"];
    } else {
        $eventDestination = 999999;
        $eventSource = 999999;
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
    $sql = "INSERT INTO `transferhistory`(`EventID`,`EventIDDestination`,`TransferDate`) VALUES ($eventSource,$eventDestination,now())";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    $transferHistoryID = mysqli_insert_id($con);
    
    

    //query statement
    $sql = "INSERT INTO `producttransfer`(`ProductID`, `ProductCode`, `ProductCategory2`, `ProductCategory1`, `ProductName`, `Color`, `Size`, `ManufacturingDate`, `TransferHistoryID`, `Remark`) select `ProductID`, `ProductCode`, `ProductCategory2`, `ProductCategory1`, `ProductName`, `Color`, `Size`, `ManufacturingDate`, $transferHistoryID, `Remark` from product where `EventID`=$eventSource and product.Status = 'I'";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select * from product where `EventID`=$eventSource and product.Status = 'I'";
    $selectedRow = getSelectedRow($sql);
    
    
    //query statement
    $sql = "UPDATE `product` SET `EventID`=$eventDestination WHERE `ProductID` in (select productID from (select productID from product where `EventID`=$eventSource and product.Status = 'I' order by productID) tempTable)";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $countSelectedRow = sizeof($selectedRow);
    if($countSelectedRow > 0)
    {
        $sql = "select * from product where `ProductID` in (";
        for($i=0; $i<$countSelectedRow; $i++)
        {
            $productIDEdit = $selectedRow[$i]["ProductID"];
            $sql .= "'$productIDEdit',";
        }
        $sql = substr($sql,0,strlen($sql)-1);
        $sql = $sql . ")";
    }
    $selectedRow = getSelectedRow($sql);
    
    
    //broadcast ไป device token อื่น
    $type = 'tProduct';
    $action = 'u';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));
    $response = array('status' => '1', 'sql' => $sql);
    
  
    echo json_encode($response);
    exit();
?>

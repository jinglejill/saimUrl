<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    

    $countProductDelete = $_POST["countProductDelete"];
    $countProduct = $_POST["countProduct"];
    for($i=0; $i<$countProductDelete; $i++)
    {
        $productDeleteID[$i] = $_POST["productDeleteID".sprintf("%02d", $i)];
        $productID[$i] = $_POST["productID".sprintf("%02d", $i)];
    }
    for($i=0; $i<$countProduct; $i++)
    {
        $productIDMain[$i] = $_POST["productIDMain".sprintf("%02d", $i)];
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
    //productdelete คือ log ของการ delete
    $productDeleteIDList = array();
    for($i=0; $i<$countProductDelete; $i++)
    {
        $sql = "INSERT INTO `productdelete`(`ProductID`, `ProductCategory2`, `ProductCategory1`, `ProductName`, `Color`, `Size`, `ManufacturingDate`) select `ProductID`, `ProductCategory2`, `ProductCategory1`, `ProductName`, `Color`, `Size`, `ManufacturingDate` from Product where `ProductID`= '$productID[$i]'";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        $productDeleteID = mysqli_insert_id($con);
        array_push($productDeleteIDList,$productDeleteID);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        
        
        //delete
        //**********sync device token ตัวเอง delete old id
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select $productDeleteID[$i] as ProductDeleteID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'tProductDelete';
        $action = 'd';
        $ret = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            //                mysqli_rollback($con);
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    //insert ตัวเอง  และ device อื่น
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select * from productDelete where `ProductDeleteID` in ('$productDeleteIDList[0]'";
    for($i=1; $i<sizeof($productDeleteIDList); $i++)
    {
        $sql .= ",'$productDeleteIDList[$i]'";
    }
    $sql .= ")";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'tProductDelete';
    $action = 'i';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    $ret2 = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "" || $ret2 != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    //-----
    
    
    
    //select row ที่ delete ขึ้นมาเก็บไว้ก่อน
    $sql = "select * from Product where `ProductID` in ('$productIDMain[0]'";
    for($i=1; $i<$countProduct; $i++)
    {
        $sql .= ",'$productID[$i]'";
    }
    $sql .= ")";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //query statement
    $sql = "delete from `Product` where `ProductID` in ('$productIDMain[0]'";
    for($i=1; $i<$countProduct; $i++)
    {
        $sql .= ",'$productIDMain[$i]'";
    }
    $sql .= ")";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    
    //broadcast ไป device token อื่น
    $type = 'tProduct';
    $action = 'd';
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
    sendPushNotificationToAllDevices();
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));    
    $response = array('status' => '1', 'sql' => $sql);
    
    
    echo json_encode($response);
    exit();
?>

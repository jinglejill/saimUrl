<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    if (isset ($_POST["countProduct"]))
    {
        $countProduct = $_POST["countProduct"];
        for($i=0; $i<$countProduct; $i++)
        {
            $productMainID[$i] = $_POST["productMainID".sprintf("%02d", $i)];
            $status[$i] = $_POST["status".sprintf("%02d", $i)];
        }
    }
    if (isset ($_POST["countReceiptProductItem"]))
    {
        $countReceiptProductItem = $_POST["countReceiptProductItem"];
        for($i=0; $i<$countReceiptProductItem; $i++)
        {
            $receiptProductItemID[$i] = $_POST["receiptProductItemID".sprintf("%02d", $i)];
            $preOrderEventID[$i] = $_POST["preOrderEventID".sprintf("%02d", $i)];
            $productID[$i] = $_POST["productID".sprintf("%02d", $i)];
        }
    }
    if (isset($_POST["countPreOrderEventIDHistory"]))
    {
        $countPreOrderEventIDHistory = $_POST["countPreOrderEventIDHistory"];
        for($i=0; $i<$countPreOrderEventIDHistory; $i++)
        {
            $preOrderEventIDHistoryID[$i] = $_POST["preOrderEventIDHistoryID".sprintf("%02d", $i)];
            $receiptProductItemIDPreHis[$i] = $_POST["receiptProductItemIDPreHis".sprintf("%02d", $i)];
            $preOrderEventIDPreHis[$i] = $_POST["preOrderEventIDPreHis".sprintf("%02d", $i)];
        }
    }
    

    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    //product
    for($i=0; $i<$countProduct; $i++)
    {
        //query statement
        $sql = "update Product set status = '$status[$i]' where ProductID = '$productMainID[$i]'";
        $ret = doQueryTask2($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
    }
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select * from Product where `ProductID` in ('$productMainID[0]'";
    for($i=1; $i<$countProduct; $i++)
    {
        $sql .= ",'$productMainID[$i]'";
    }
    $sql .= ")";
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
    
    
    
    
    
    
    //receiptproductitem
    for($i=0; $i<$countReceiptProductItem; $i++)
    {
        //query statement
        $sql = "update ReceiptProductItem set PreOrderEventID = $preOrderEventID[$i], ProductID='$productID[$i]' where ReceiptProductItemID = $receiptProductItemID[$i]";
        $ret = doQueryTask2($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
    }
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select * from ReceiptProductItem where `ReceiptProductItemID` in ('$receiptProductItemID[0]'";
    for($i=1; $i<$countReceiptProductItem; $i++)
    {
        $sql .= ",'$receiptProductItemID[$i]'";
    }
    $sql .= ")";
    $selectedRow = getSelectedRow($sql);
    
    
    //broadcast ไป device token อื่น
    $type = 'tReceiptProductItem';
    $action = 'u';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    if($countPreOrderEventIDHistory > 0)
    {
        for($k=0; $k<$countPreOrderEventIDHistory; $k++)
        {
            //query statement
            $sql = "INSERT INTO PreOrderEventIDHistory(ReceiptProductItemID, PreOrderEventID) VALUES ('$receiptProductItemIDPreHis[$k]', '$preOrderEventIDPreHis[$k]')";
            $ret = doQueryTask2($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
            
            
            //insert ผ่าน
            $newID = mysqli_insert_id($con);
            
            
            
            //**********sync device token ตัวเอง delete old id and insert newID
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $sql = "select $preOrderEventIDHistoryID[$k] as PreOrderEventIDHistoryID, 1 as ReplaceSelf, '$modifiedUser[$k]' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'tPreOrderEventIDHistory';
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
            $preOrderEventIDHistoryID[$k] = $newID;
            $sql = "select *, 1 IdInserted from PreOrderEventIDHistory where PreOrderEventIDHistoryID = '$preOrderEventIDHistoryID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'tPreOrderEventIDHistory';
            $action = 'i';
            $ret = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
            if($ret != "")
            {
                mysqli_rollback($con);
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
        }
        
        
        
        //**********sync device token อื่น
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from PreOrderEventIDHistory where PreOrderEventIDHistoryID in ('$preOrderEventIDHistoryID[0]'";
        for($i=1; $i<$countPreOrderEventIDHistory; $i++)
        {
            $sql .= ",'$preOrderEventIDHistoryID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'tPreOrderEventIDHistory';
        $action = 'i';
        $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
    }
    
    
    

    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));
    $response = array('status' => '1', 'sql' => $sql);    
    
    
    echo json_encode($response);
    exit();
?>

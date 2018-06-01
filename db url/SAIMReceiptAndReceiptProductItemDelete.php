<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (isset ($_POST["countProduct"]))
    {
        $countProduct = $_POST["countProduct"];
        $countCustomMade = $_POST["countCustomMade"];
        $countReceiptProductItem = $_POST["countReceiptProductItem"];
        
        for($i=0; $i<$countProduct; $i++)
        {
            $productID[$i] = $_POST["productID".sprintf("%02d", $i)];
        }
        for($i=0; $i<$countCustomMade; $i++)
        {
            $customMadeID[$i] = $_POST["customMadeID".sprintf("%02d", $i)];
        }
        for($i=0; $i<$countReceiptProductItem; $i++)
        {
            $receiptProductItemID[$i] = $_POST["receiptProductItemID".sprintf("%02d", $i)];
        }
        
        $receiptID = $_POST["receiptID"];
        $customerReceiptID = $_POST["customerReceiptID"];
    }


    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    if($countProduct > 0)
    {
        for($i=0; $i<$countProduct; $i++)
        {
            //query statement
            $sql = "update product set Status = 'I', Remark = '' where ProductID = $productID[$i]";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
        }
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from product where `ProductID` in ('$productID[0]'";
        for($i=1; $i<$countProduct; $i++)
        {
            $sql .= ",'$productID[$i]'";
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
        
        //-----
    }
    
    
    
    if($countCustomMade > 0)
    {
        //select row ที่ delete ขึ้นมาเก็บไว้
        $sql = "select * from custommade where `customMadeID` in ('$customMadeID[0]'";
        for($i=1; $i<$countCustomMade; $i++)
        {
            $sql .= ",'$customMadeID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        for($i=0; $i<$countCustomMade; $i++)
        {
            //query statement
            $sql = "delete from custommade where customMadeID = $customMadeID[$i]";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
        }
        
        
        //broadcast ไป device token อื่น
        $type = 'tCustomMade';
        $action = 'd';
        $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        //----
        
    }
    
    
    //preordereventidhistory
    //select row ที่ delete ขึ้นมาเก็บไว้
    $sql = "select * from preordereventidhistory where `ReceiptProductItemID` in ('$receiptProductItemID[0]'";
    for($i=1; $i<$countProduct; $i++)
    {
        $sql .= ",'$receiptProductItemID[$i]'";
    }
    $sql .= ")";
    $selectedRow = getSelectedRow($sql);
    
    
    for($i=0; $i<$countReceiptProductItem; $i++)
    {
        //query statement
        $sql = "delete from preordereventidhistory where ReceiptProductItemID = $receiptProductItemID[$i]";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
    }
    
    
    //broadcast ไป device token อื่น
    $type = 'tPreOrderEventIDHistory';
    $action = 'd';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }    
    //----
    
    
    
    
    //select row ที่ delete ขึ้นมาเก็บไว้
    $sql = "select * from receiptproductitem where `ReceiptProductItemID` in ('$receiptProductItemID[0]'";
    for($i=1; $i<$countProduct; $i++)
    {
        $sql .= ",'$receiptProductItemID[$i]'";
    }
    $sql .= ")";
    $selectedRow = getSelectedRow($sql);
    
    
    for($i=0; $i<$countReceiptProductItem; $i++)
    {
        //query statement
        $sql = "delete from receiptproductitem where ReceiptProductItemID = $receiptProductItemID[$i]";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
    }
    
    
    //broadcast ไป device token อื่น
    $type = 'tReceiptProductItem';
    $action = 'd';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    //----
    
    
    
    //select row ที่ delete ขึ้นมาเก็บไว้
    $sql = "select * from receipt where `ReceiptID` = '$receiptID'";
    $selectedRow = getSelectedRow($sql);
    
    
    //query statement
    $sql = "delete from `receipt` where receiptID = $receiptID";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //broadcast ไป device token อื่น
    $type = 'tReceipt';
    $action = 'd';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    //----
    
    
    
    //select row ที่ delete ขึ้นมาเก็บไว้
    $sql = "select * from rewardPoint where `ReceiptID` = $receiptID";
    $selectedRow = getSelectedRow($sql);
    
    
    //query statement
    $sql = "delete from `rewardPoint` where receiptID = $receiptID";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //broadcast ไป device token อื่น
    $type = 'tRewardPoint';
    $action = 'd';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    //----
    
    
    
    //select row ที่ delete ขึ้นมาเก็บไว้
    $sql = "select * from customerReceipt where `customerReceiptID` = '$customerReceiptID'";
    $selectedRow = getSelectedRow($sql);
    
    
    //query statement
    $sql = "delete from `customerReceipt` where customerReceiptID = $customerReceiptID";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //broadcast ไป device token อื่น
    $type = 'tCustomerReceipt';
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
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));
    $response = array('status' => '1', 'sql' => $sql);
    
    echo json_encode($response);
    exit();
?>

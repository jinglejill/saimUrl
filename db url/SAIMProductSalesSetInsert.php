<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (isset ($_POST["productSalesSetName"]) &&
        isset ($_POST["productSalesSetID"])
        )
    {
        $productSalesSetName = $_POST["productSalesSetName"];
        $productSalesSetIDSource = $_POST["productSalesSetID"];
        
    } else {
        $productSalesSetName = '-';
        $productSalesSetIDSource = 0;
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
        
        
        
        $sql = "INSERT INTO `productsalesset`(`ProductSalesSetName`) VALUES ('$productSalesSetName')";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        $id = mysqli_insert_id($con);
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from productsalesset where ProductSalesSetID = $id";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tProductSalesSet';
        $action = 'i';
        $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        //-----------
        
        
        //query statement
        $sql = "insert into productsales (`ProductSalesSetID`, `ProductNameID`, `Color`, `Size`, `Price`, `Detail`, `PercentDiscountMember`, `PercentDiscountFlag`, `PercentDiscount`, `PricePromotion`, `ShippingFee`, `ImageDefault`, `ImageID`, `Cost`) select $id, `ProductNameID`, `Color`, `Size`, `Price`, `Detail`, `PercentDiscountMember`, `PercentDiscountFlag`, `PercentDiscount`, `PricePromotion`, `ShippingFee`, `ImageDefault`, `ImageID`, `Cost` from productsales where ProductSalesSetID = $productSalesSetIDSource";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from productsales where ProductSalesSetID = $id";
        $selectedRow = getSelectedRow($sql);
        $data = $selectedRow;
        
        
        //broadcast ไป device token อื่น
        $type = 'tProductSales';
        $action = 'i';
        $selectedRow = $sql;
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
    $response = array('status' => '1', 'sql' => $sql, 'data' => $data);
    
    
    echo json_encode($response);
    exit();
    ?>

<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    printAllPost();


    if(isset($_POST["countProductSales"]))
    {
        $countProductSales = $_POST["countProductSales"];
        for($i=0; $i<$countProductSales; $i++)
        {
            $productSalesID[$i] = $_POST["productSalesID".sprintf("%02d", $i)];
            $productSalesSetID[$i] = $_POST["productSalesSetID".sprintf("%02d", $i)];
            $productNameID[$i] = $_POST["productNameID".sprintf("%02d", $i)];
            $color[$i] = $_POST["color".sprintf("%02d", $i)];
            $size[$i] = $_POST["size".sprintf("%02d", $i)];
            $price[$i] = $_POST["price".sprintf("%02d", $i)];
            $detail[$i] = $_POST["detail".sprintf("%02d", $i)];
            $percentDiscountMember[$i] = $_POST["percentDiscountMember".sprintf("%02d", $i)];
            $percentDiscountFlag[$i] = $_POST["percentDiscountFlag".sprintf("%02d", $i)];
            $percentDiscount[$i] = $_POST["percentDiscount".sprintf("%02d", $i)];
            $pricePromotion[$i] = $_POST["pricePromotion".sprintf("%02d", $i)];
            $shippingFee[$i] = $_POST["shippingFee".sprintf("%02d", $i)];
            $imageDefault[$i] = $_POST["imageDefault".sprintf("%02d", $i)];
            $imageID[$i] = $_POST["imageID".sprintf("%02d", $i)];
            $cost[$i] = $_POST["cost".sprintf("%02d", $i)];
        }
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
    //        $sql = "INSERT INTO `productsales`(`ProductSalesID`, `ProductSalesSetID`, `ProductNameID`, `Color`, `Size`, `Price`, `Detail`, `PercentDiscountMember`, `PercentDiscountFlag`, `PercentDiscount`, `PricePromotion`, `ShippingFee`, `ImageDefault`, `ImageID`, `Cost`) VALUES ($productSalesID[0], $productSalesSetID[0], $productNameID[0], '$color[0]', '$size[0]', $price[0], '$detail[0]', $percentDiscountMember[0], $percentDiscountFlag[0], $percentDiscount[0], $pricePromotion[0], $shippingFee[0], '$imageDefault[0]', $imageID[0], $cost[0])";
    //        $sql = "INSERT INTO `productsales`(`ProductSalesSetID`, `ProductNameID`, `Color`, `Size`, `Price`, `Detail`, `PercentDiscountMember`, `PercentDiscountFlag`, `PercentDiscount`, `PricePromotion`, `ShippingFee`, `ImageDefault`, `ImageID`, `Cost`) VALUES ( $productSalesSetID[0], $productNameID[0], '$color[0]', '$size[0]', $price[0], '$detail[0]', $percentDiscountMember[0], $percentDiscountFlag[0], $percentDiscount[0], $pricePromotion[0], $shippingFee[0], '$imageDefault[0]', $imageID[0], $cost[0])";
    
    $newIDList = array();
    for($i=0; $i<$countProductSales; $i++)
    {
        //            $sql .= ", ($productSalesID[$i], $productSalesSetID[$i], $productNameID[$i], '$color[$i]', '$size[$i]', $price[$i], '$detail[$i]', $percentDiscountMember[$i], $percentDiscountFlag[$i], $percentDiscount[$i], $pricePromotion[$i], $shippingFee[$i], '$imageDefault[$i]', $imageID[$i], $cost[$i])";
        $sql = "INSERT INTO `productsales`(`ProductSalesSetID`, `ProductNameID`, `Color`, `Size`, `Price`, `Detail`, `PercentDiscountMember`, `PercentDiscountFlag`, `PercentDiscount`, `PricePromotion`, `ShippingFee`, `ImageDefault`, `ImageID`, `Cost`) VALUES ($productSalesSetID[$i], $productNameID[$i], '$color[$i]', '$size[$i]', $price[$i], '$detail[$i]', $percentDiscountMember[$i], $percentDiscountFlag[$i], $percentDiscount[$i], $pricePromotion[$i], $shippingFee[$i], '$imageDefault[$i]', $imageID[$i], $cost[$i])";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        $newID = mysqli_insert_id($con);
        array_push($newIDList,$newID);
    }
    
    
    
    
    //        for($i=0; $i<$countProductSales; $i++)
    //        {
    //            $sql = "select $productSalesID[$i] as ProductSalesID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
    //            $selectedRow = getSelectedRow($sql);
    //
    //
    //
    //            //broadcast ไป device token ตัวเอง
    //            $type = 'tProductSales';
    //            $action = 'd';
    //            $ret = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    //            if($ret != "")
    //            {
    //                mysqli_rollback($con);
    //                putAlertToDevice($_POST["modifiedUser"]);
    //                echo json_encode($ret);
    //                exit();
    //            }
    //        }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select * from productsales where ProductSalesID in ($newIDList[0]";
    for($i=1; $i<$countProductSales; $i++)
    {
        $sql .= ",$newIDList[$i]";
    }
    $sql .= ")";
    $selectedRow = getSelectedRow($sql);
    $dataJson = executeMultiQueryArray($sql);
    
    
    //broadcast ไป device token อื่น
    $type = 'tProductSales';
    $action = 'i';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    //        $ret2 = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    //-----
    
    
    
    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));    
    $response = array('status' => '1', 'sql' => $sql, 'dataJson'=>$dataJson, 'tableName' => 'ProductSales');
    

    echo json_encode($response);
    exit();
?>

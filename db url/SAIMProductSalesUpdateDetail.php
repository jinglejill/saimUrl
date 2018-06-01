<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (isset ($_POST["countProductSales"]))
    {
        $countProductSales = $_POST["countProductSales"];
        $price = $_POST["price"];
        $pricePromotion = $_POST["pricePromotion"];
        $detail = $_POST["detail"];
        $imageDefault = $_POST["imageDefault"];
        
        
        for($i=0; $i<$countProductSales; $i++)
        {
            $productSalesID[$i] = $_POST["productSalesID".sprintf("%02d", $i)];
        }
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
        $sql = "UPDATE `productsales` SET Price=$price, PricePromotion=$pricePromotion, Detail='$detail', ImageDefault='$imageDefault' WHERE ProductSalesID in ($productSalesID[0]";
        for($i=1; $i<$countProductSales; $i++)
        {
            $sql .= ", '$productSalesID[$i]'";
        }
        $sql .= ")";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        $wherePart = substr($sql,strpos($sql,'WHERE'),strlen($sql)-strpos($sql,'WHERE')+1);
        
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from productsales " . $wherePart;//where ProductSalesID = $productSalesID";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tProductSales';
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

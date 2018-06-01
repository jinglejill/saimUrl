<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));    
    
    
    if (isset ($_POST["countProductionOrder"]))
    {
        
        $countProductionOrder = $_POST["countProductionOrder"];
        for($i=0; $i<$countProductionOrder; $i++)
        {
            $productionOrderID[$i] = $_POST["productionOrderID".sprintf("%02d", $i)];
            $runningPoNo[$i] = $_POST["runningPoNo".sprintf("%02d", $i)];
            $productNameID[$i] = $_POST["productNameID".sprintf("%02d", $i)];
            $color[$i] = $_POST["color".sprintf("%02d", $i)];
            $size[$i] = $_POST["size".sprintf("%02d", $i)];
            $quantity[$i] = $_POST["quantity".sprintf("%02d", $i)];
            $status[$i] = $_POST["status".sprintf("%02d", $i)];
            $orderDeliverDate[$i] = $_POST["orderDeliverDate".sprintf("%02d", $i)];
        }
    }
    $eventID = $_POST["eventID"];
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    $sql = "select ifnull(max(ProductionOrderID),0) MaxProductionOrderID from `ProductionOrder`";
    $selectedRow = getSelectedRow($sql);
    $maxProductionOrderID = $selectedRow[0]["MaxProductionOrderID"];
    $startProductionOrderID = $maxProductionOrderID+1;
    
    
    $sql = "select ifnull(max(RunningPoNo),0) MaxRunningPoNo from `ProductionOrder`";
    $selectedRow = getSelectedRow($sql);
    $maxRunningPoNo = $selectedRow[0]["MaxRunningPoNo"];
    $nextRunningPoNo = $maxRunningPoNo+1;
    
    
    $sql = "INSERT INTO `productionorder`(`ProductionOrderID`,`RunningPoNo`, `ProductNameID`, `Color`, `Size`, `Quantity`, `Status`, `OrderDeliverDate`) values($startProductionOrderID,$nextRunningPoNo,$productNameID[0],'$color[0]','$size[0]',$quantity[0],$status[0],'$orderDeliverDate[0]')";
    for($i=1; $i<$countProductionOrder; $i++)
    {
        //query statement
        $sql .= ",($startProductionOrderID+$i,$nextRunningPoNo,$productNameID[$i],'$color[$i]','$size[$i]',$quantity[$i],$status[$i],'$orderDeliverDate[$i]')";
    }
    
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    
    //add to main stock
    if($eventID == 0)
    {
        $startID = $startProductionOrderID;
        $endID = $startProductionOrderID + $countProductionOrder - 1;
        
        
        $k = 0;
        $productID = array();
        $sql = "select `ProductCategory2`, `ProductCategory1`, productname.Code,`Color`, `Size`, date_format(`OrderDeliverDate`,'%Y-%m-%d') OrderDeliverDate, Quantity from productionorder LEFT JOIN productname ON productionorder.ProductNameID = productname.ProductNameID where productionOrderID between $startID and $endID;";
        $selectedRow = getSelectedRow($sql);
        if(sizeof($selectedRow)>0)
        {
            //productrunningid
            $sql2 = "INSERT INTO `productrunning`(`ModifiedDate`) VALUES (now())";
            $ret = doQueryTask($con,$sql2,$_POST["modifiedUser"]);
            $newID = mysqli_insert_id($con);
            $newID = sprintf("%06d", $newID);
            $productID[$k] = $newID;
            
            $sql = "insert into  `product` (`ProductID`, `ProductCode`, `ProductCategory2`, `ProductCategory1`, `ProductName`, `Color`, `Size`, `ManufacturingDate`, `Status`, `Remark`, `EventID`) values ('" . $productID[$k] . "','','" . $selectedRow[0]["ProductCategory2"] . "','" . $selectedRow[0]["ProductCategory1"] . "','" . $selectedRow[0]["Code"] . "','" . $selectedRow[0]["Color"] . "','" . $selectedRow[0]["Size"] . "','" . $selectedRow[0]["OrderDeliverDate"] . "','I','','0')";
            $k++;
            for($i=1; $i<intval($selectedRow[0]["Quantity"]); $i++)
            {
                //            $productID = sprintf("%06d", $nextProductID++);
                
                
                //productrunningid
                $sql2 = "INSERT INTO `productrunning`(`ModifiedDate`) VALUES (now())";
                $ret = doQueryTask($con,$sql2,$_POST["modifiedUser"]);
                $newID = mysqli_insert_id($con);
                $newID = sprintf("%06d", $newID);
                $productID[$k] = $newID;
                
                $sql .= ",('" . $productID[$k] . "','','" . $selectedRow[0]["ProductCategory2"] . "','" . $selectedRow[0]["ProductCategory1"] . "','" . $selectedRow[0]["Code"] . "','" . $selectedRow[0]["Color"] . "','" . $selectedRow[0]["Size"] . "','" . $selectedRow[0]["OrderDeliverDate"] . "','I','','0')";
                $k++;
            }
            
            
            for($i=1; $i<sizeof($selectedRow); $i++)
            {
                for($j=0; $j<intval($selectedRow[$i]["Quantity"]); $j++)
                {
                    //productrunningid
                    $sql2 = "INSERT INTO `productrunning`(`ModifiedDate`) VALUES (now())";
                    $ret = doQueryTask($con,$sql2,$_POST["modifiedUser"]);
                    $newID = mysqli_insert_id($con);
                    $newID = sprintf("%06d", $newID);
                    $productID[$k] = $newID;
                    
                    
                    $sql .= ",('" . $productID[$k] . "','','" . $selectedRow[$i]["ProductCategory2"] . "','" . $selectedRow[$i]["ProductCategory1"] . "','" . $selectedRow[$i]["Code"] . "','" . $selectedRow[$i]["Color"] . "','" . $selectedRow[$i]["Size"] . "','" . $selectedRow[$i]["OrderDeliverDate"] . "','I','','0')";
                    $k++;
                }
            }
        }
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        
        //**********sync device token อื่น และตัวเอง
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from Product where ProductID in ('$productID[0]'";
        for($i=1; $i<$k; $i++)
        {
            $sql .= ",'$productID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'tProduct';
        $action = 'i';
        $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        $ret2 = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "" || $ret2 != "")
        {
            mysqli_rollback($con);
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        //------
    }
    else
    {
        $productIDList = array();
        for($i=0; $i<$countProductionOrder; $i++)
        {
            $sql = "select product.* from product left join productname on product.productCategory2 = productname.productCategory2 and product.productCategory1 = productname.productCategory1 and product.productname = productname.code where product.eventID = 0 and product.status = 'I' and productname.productnameid = $productNameID[$i] and product.color = '$color[$i]' and product.size = '$size[$i]'";
            $selectedRow = getSelectedRow($sql);
            for($j=0; $j<$quantity[$i]; $j++)
            {
                $productIDUpdate = $selectedRow[$j]['ProductID'];
                array_push($productIDList,$productIDUpdate);
                $sql = "update product set eventID = $eventID where productID = '$productIDUpdate'";
                $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
                if($ret != "")
                {
                    putAlertToDevice($_POST["modifiedUser"]);
                    echo json_encode($ret);
                    exit();
                }
            }
        }
        
        
        
        if(sizeof($productIDList)>0)
        {
            $sql = "select * from product where productID in ('$productIDList[0]'";
            for($j=1; $j<sizeof($productIDList); $j++)
            {
                $sql .= ",'$productIDList[$j]'";
            }
            $sql .= ");";
            $selectedRow = getSelectedRow($sql);
            
            
            //broadcast ไป device token อื่น
            $type = 'tProduct';
            $action = 'u';
            $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
            $ret2 = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
            if($ret != "" || $ret2 != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
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

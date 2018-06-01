

<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    printAllPost();

    
    
    $modifiedUser = $_POST["modifiedUser"];
    $receiptID = $_POST["receiptID"];
    $eventID = $_POST["eventID"];
    $channel = $_POST["channel"];
    $payPrice = $_POST["payPrice"];
    $paymentMethod = $_POST["paymentMethod"];
    $creditAmount = $_POST["creditAmount"];
    $cashAmount = $_POST["cashAmount"];
    $cashReceive = $_POST["cashReceive"];
    $remark = $_POST["remark"];
    $discount = $_POST["discount"];
    $discountValue = $_POST["discountValue"];
    $discountPercent = $_POST["discountPercent"];
    $discountReason = $_POST["discountReason"];
    

    $customerReceiptID = $_POST["customerReceiptID"];
    $receiptID = $_POST["receiptID"];
    $trackingNo = $_POST["trackingNo"];
    $postCustomerID = $_POST["postCustomerID"];


    if (isset ($_POST["countPostCustomer"]))
    {
        $countPostCustomer = $_POST["countPostCustomer"];
        if($countPostCustomer == 1)
        {
            $postCustomerID = $_POST["postCustomerID"];
            $customerID = $_POST["customerID"];
            $firstName = $_POST["firstName"];
            $street1 = $_POST["street1"];
            $postcode = $_POST["postcode"];
            $country = $_POST["country"];
            $telephone = $_POST["telephone"];
            $lineID = $_POST["lineID"];
            $facebookID = $_POST["facebookID"];
            $emailAddress = $_POST["emailAddress"];
            $taxCustomerName = $_POST["taxCustomerName"];
            $taxCustomerAddress = $_POST["taxCustomerAddress"];
            $taxNo = $_POST["taxNo"];
            $other = $_POST["other"];
        }
    }
    
    if (isset($_POST["countRewardPoint"]))
    {
        $countRewardPoint = $_POST["countRewardPoint"];
        for($i=0; $i<$countRewardPoint; $i++)
        {
            $rewardPointID[$i] = $_POST["rewardPointID".sprintf("%02d", $i)];
            $customerIDReward[$i] = $_POST["customerIDReward".sprintf("%02d", $i)];
//            $receiptIDReward[$i] = $_POST["customerIDReward".sprintf("%02d", $i)];
            $point[$i] = $_POST["point".sprintf("%02d", $i)];
            $statusReward[$i] = $_POST["statusReward".sprintf("%02d", $i)];
        }
    }
    if (isset($_POST["countProduct"]))
    {
        $countProduct = $_POST["countProduct"];
        for($i=0; $i<$countProduct; $i++)
        {
            $productIDMain[$i] = $_POST["productIDMain".sprintf("%02d", $i)];
            $status[$i] = $_POST["status".sprintf("%02d", $i)];
        }
    }
    if (isset($_POST["countCustomMade"]))
    {
        $countCustomMade = $_POST["countCustomMade"];
        for($i=0; $i<$countCustomMade; $i++)
        {
            $customMadeID[$i] = $_POST["customMadeID".sprintf("%02d", $i)];
            $productCategory2[$i] = $_POST["productCategory2".sprintf("%02d", $i)];
            $productCategory1[$i] = $_POST["productCategory1".sprintf("%02d", $i)];
            $productName[$i] = $_POST["productName".sprintf("%02d", $i)];
            $size[$i] = $_POST["size".sprintf("%02d", $i)];
            $toe[$i] = $_POST["toe".sprintf("%02d", $i)];
            $body[$i] = $_POST["body".sprintf("%02d", $i)];
            $accessory[$i] = $_POST["accessory".sprintf("%02d", $i)];
            $remarkCustomMade[$i] = $_POST["remarkCustomMade".sprintf("%02d", $i)];
            $productIDPost[$i] = $_POST["productIDPost".sprintf("%02d", $i)];
        }
    }
    if (isset($_POST["countReceiptProductItem"]))
    {
        $countReceiptProductItem = $_POST["countReceiptProductItem"];
        for($i=0; $i<$countReceiptProductItem; $i++)
        {
            $receiptProductItemID[$i] = $_POST["receiptProductItemID".sprintf("%02d", $i)];
            $productType[$i] = $_POST["productType".sprintf("%02d", $i)];
            $preOrderEventID[$i] = $_POST["preOrderEventID".sprintf("%02d", $i)];
            $productID[$i] = $_POST["productID".sprintf("%02d", $i)];
            $priceSales[$i] = $_POST["priceSales".sprintf("%02d", $i)];
            $customMadeIn[$i] = $_POST["customMadeIn".sprintf("%02d", $i)];
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
    if($countProduct > 0)
    {
        for($i=0; $i<$countProduct; $i++)
        {
            //query statement
            $sql = "update product set Status = '$status[$i]' where ProductID = '$productIDMain[$i]'";
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
        $sql = "select * from product where `ProductID` in ('$productIDMain[0]'";
        for($i=1; $i<$countProduct; $i++)
        {
            $sql .= ",'$productIDMain[$i]'";
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
    }
    
    //----
    
    
    
    //custom made
    $customMadeOldNew = array();
    
    
    if($countCustomMade > 0)
    {
        for($k=0; $k<$countCustomMade; $k++)
        {
            //query statement
            $sql = "INSERT INTO CustomMade(ProductCategory2, ProductCategory1, ProductName, Size, Toe, Body, Accessory, Remark, ProductIDPost) VALUES ('$productCategory2[$k]', '$productCategory1[$k]', '$productName[$k]', '$size[$k]', '$toe[$k]', '$body[$k]', '$accessory[$k]', '$remarkCustomMade[$k]', '$productIDPost[$k]')";
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
            $sql = "select $customMadeID[$k] as CustomMadeID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'tCustomMade';
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
            $customMadeOldNew[$customMadeID[$k]] = $newID;
            $customMadeID[$k] = $newID;
            $sql = "select *, 1 IdInserted from CustomMade where CustomMadeID = '$customMadeID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'tCustomMade';
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
        $sql = "select *, 1 IdInserted from CustomMade where CustomMadeID in ('$customMadeID[0]'";
        for($i=1; $i<$countCustomMade; $i++)
        {
            $sql .= ",'$customMadeID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'tCustomMade';
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
    //----
    
    
    
    $salesUser = $_POST["modifiedUser"];
    //receipt
    //query statement
    $sql = "INSERT INTO Receipt(EventID, Channel, PayPrice, PaymentMethod, CreditAmount, CashAmount, CashReceive, Remark, Discount, DiscountValue, DiscountPercent, DiscountReason, ReceiptDate, SalesUser) VALUES ('$eventID', '$channel', '$payPrice', '$paymentMethod', '$creditAmount', '$cashAmount', '$cashReceive', '$remark', '$discount', '$discountValue', '$discountPercent', '$discountReason', now(), '$salesUser')";
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
    
    
    
    //device ตัวเอง ลบแล้ว insert
    //sync generated id back to app
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select $receiptID as ReceiptID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'tReceipt';
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
    $receiptID = $newID;
    $sql = "select *, 1 IdInserted from Receipt where ReceiptID = '$receiptID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'tReceipt';
    $action = 'i';
    $ret = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //****device อื่น insert
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from Receipt where ReceiptID = '$receiptID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'tReceipt';
    $action = 'i';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    //----
    
    
    
    //receiptproductitem
    $receiptProductItemOldNew = array();
    if($countReceiptProductItem > 0)
    {
        for($k=0; $k<$countReceiptProductItem; $k++)
        {
            //query statement
            if($productType[$k] == 'C')
            {
                $productID[$k] = $customMadeOldNew[$productID[$k]];
            }
            $sql = "INSERT INTO ReceiptProductItem(ReceiptID, ProductType, PreOrderEventID, ProductID, PriceSales, CustomMadeIn) VALUES ('$receiptID', '$productType[$k]', '$preOrderEventID[$k]', '$productID[$k]', '$priceSales[$k]', '$customMadeIn[$k]')";
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
            $sql = "select $receiptProductItemID[$k] as ReceiptProductItemID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'tReceiptProductItem';
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
            $receiptProductItemOldNew[$receiptProductItemID[$k]] = $newID;
            $receiptProductItemID[$k] = $newID;
            $sql = "select *, 1 IdInserted from ReceiptProductItem where ReceiptProductItemID = '$receiptProductItemID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'tReceiptProductItem';
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
        $sql = "select *, 1 IdInserted from ReceiptProductItem where ReceiptProductItemID in ('$receiptProductItemID[0]'";
        for($i=1; $i<$countReceiptProductItem; $i++)
        {
            $sql .= ",'$receiptProductItemID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'tReceiptProductItem';
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
    //-----
    
    
    
    
    //PreOrderEventIDHistory
    if($countPreOrderEventIDHistory > 0)
    {
        for($k=0; $k<$countPreOrderEventIDHistory; $k++)
        {
            //query statement
            writeToLog("test " . $receiptProductItemIDPreHis[$k]);
            $receiptProductItemIDPreHis[$k] = $receiptProductItemOldNew[$receiptProductItemIDPreHis[$k]];
            writeToLog("test " . $receiptProductItemIDPreHis[$k]);
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
            $sql = "select $preOrderEventIDHistoryID[$k] as PreOrderEventIDHistoryID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
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
    //-----
    
    
    
    
    
    //customerid
    if(isset($_POST["customerID"]) && $customerID == 0)
    {
        $sql = "INSERT INTO `customer`(`ModifiedDate`) VALUES (now())";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        $customerID = mysqli_insert_id($con);
    }
    //-----
    
    
    
    //rewardpoint
    if($countRewardPoint > 0)
    {
        for($k=0; $k<$countRewardPoint; $k++)
        {
            if($customerIDReward[$k] == 0)
            {
                $customerIDReward[$k] = $customerID;
            }
            
            //query statement
            $sql = "INSERT INTO RewardPoint(CustomerID, ReceiptID, Point, Status) VALUES ('$customerIDReward[$k]', '$receiptID[$k]', '$point[$k]', '$statusReward[$k]')";
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
            $sql = "select $rewardPointID[$k] as RewardPointID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'tRewardPoint';
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
            $rewardPointID[$k] = $newID;
            $sql = "select *, 1 IdInserted from RewardPoint where RewardPointID = '$rewardPointID[$k]'";
            $selectedRow = getSelectedRow($sql);
            
            
            
            //broadcast ไป device token ตัวเอง
            $type = 'tRewardPoint';
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
        $sql = "select *, 1 IdInserted from RewardPoint where RewardPointID in ('$rewardPointID[0]'";
        for($i=1; $i<$countRewardPoint; $i++)
        {
            $sql .= ",'$rewardPointID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'tRewardPoint';
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
    //-----
    

    
    
    
    //postcustomer
    if($countPostCustomer > 0)
    {
        //query statement
        $sql = "INSERT INTO PostCustomer(CustomerID, FirstName, Street1, Postcode, Country, Telephone, LineID, FacebookID, EmailAddress, TaxCustomerName, TaxCustomerAddress, TaxNo, Other) VALUES ('$customerID', '$firstName', '$street1', '$postcode', '$country', '$telephone', '$lineID', '$facebookID', '$emailAddress', '$taxCustomerName', '$taxCustomerAddress', '$taxNo', '$other')";
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
        
        
        
        //device ตัวเอง ลบแล้ว insert
        //sync generated id back to app
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select $postCustomerID as PostCustomerID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'tPostCustomer';
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
        $postCustomerID = $newID;
        $sql = "select *, 1 IdInserted from PostCustomer where PostCustomerID = '$postCustomerID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token ตัวเอง
        $type = 'tPostCustomer';
        $action = 'i';
        $ret = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            mysqli_rollback($con);
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        //****device อื่น insert
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select *, 1 IdInserted from PostCustomer where PostCustomerID = '$postCustomerID'";
        $selectedRow = getSelectedRow($sql);
        
        
        
        //broadcast ไป device token อื่น
        $type = 'tPostCustomer';
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
    //----
    
    
    
    //customerreceipt
    //query statement
    $sql = "INSERT INTO CustomerReceipt(ReceiptID, TrackingNo, PostCustomerID) VALUES ('$receiptID', '$trackingNo', '$postCustomerID')";
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
    
    
    
    //device ตัวเอง ลบแล้ว insert
    //sync generated id back to app
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select $customerReceiptID as CustomerReceiptID, 1 as ReplaceSelf, '$modifiedUser' as ModifiedUser";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'tCustomerReceipt';
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
    $customerReceiptID = $newID;
    $sql = "select *, 1 IdInserted from CustomerReceipt where CustomerReceiptID = '$customerReceiptID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token ตัวเอง
    $type = 'tCustomerReceipt';
    $action = 'i';
    $ret = doPushNotificationTaskToDevice($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //****device อื่น insert
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select *, 1 IdInserted from CustomerReceipt where CustomerReceiptID = '$customerReceiptID'";
    $selectedRow = getSelectedRow($sql);
    
    
    
    //broadcast ไป device token อื่น
    $type = 'tCustomerReceipt';
    $action = 'i';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        mysqli_rollback($con);
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    
    //do script successful
    mysqli_commit($con);
    
    
    //update ตัวเอง สำหรับกรณี insert duplicate และ update IDInserted, update คนอื่น สำหรับกรณี sync ให้ข้อมูล update เหมือนกันหมด
    sendPushNotificationToAllDevices($_POST["modifiedDeviceToken"]);
    
    
    //send badge
    $badge = $countProduct+$countCustomMade;
    $ret = updateCountNotSeen($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$badge);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    //-----
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    $response = array('status' => '1', 'sql' => $sql);
    
    mysqli_close($con);
    
    
    echo json_encode($response);
    exit();
?>

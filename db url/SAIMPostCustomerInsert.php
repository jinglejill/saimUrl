<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (isset ($_POST["postCustomerID"]) &&
        isset ($_POST["customerID"]) &&
        isset ($_POST["firstName"]) &&
        isset ($_POST["street1"]) &&
        isset ($_POST["postcode"]) &&
        isset ($_POST["country"]) &&
        isset ($_POST["telephone"]) &&
        isset ($_POST["lineID"]) &&
        isset ($_POST["facebookID"]) &&
        isset ($_POST["emailAddress"]) &&
        isset ($_POST["other"]) &&
        isset ($_POST["taxCustomerName"]) &&
        isset ($_POST["taxCustomerAddress"]) &&
        isset ($_POST["taxNo"]) &&
        isset ($_POST["receiptID"])
        )
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
        $other = $_POST["other"];
        $taxCustomerName = $_POST["taxCustomerName"];
        $taxCustomerAddress = $_POST["taxCustomerAddress"];
        $taxNo = $_POST["taxNo"];
        $receiptID = $_POST["receiptID"];
    } else {
        $postCustomerID = 0;
        $customerID = -1;
        $firstName = "";
        $street1 = "";
        $postcode = "";
        $country = "";
        $telephone = "";
        $lineID = "";
        $facebookID = "";
        $emailAddress = "";
        $other = "";
        $taxCustomerName = "";
        $taxCustomerAddress = "";
        $taxNo = "";
        $receiptID = "";
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
    
    
    if($customerID == 0)
    {
        $sql = "select ifnull(max(customerID),0) MaxCustomerID from postCustomer";
        $selectedRow = getSelectedRow($sql);
        $maxCustomerID = $selectedRow[0]['MaxCustomerID'];
        $customerID = $maxCustomerID+1;
    }
    
    
    
    
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
    //-----
    
    
    
    
    //update customerReceipt
    //query statement
    $sql = "update customerreceipt set PostCustomerID = $postCustomerID where ReceiptID = $receiptID";
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    //select row ที่แก้ไข ขึ้นมาเก็บไว้
    $sql = "select * from customerreceipt where ReceiptID = $receiptID";
    $selectedRow = getSelectedRow($sql);
    
    
    //broadcast ไป device token อื่น
    $type = 'tCustomerReceipt';
    $action = 'u';
    $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
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

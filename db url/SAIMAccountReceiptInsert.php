<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));    
    
    
    if (isset ($_POST["countAccountInventory"]))
    {
        $countAccountInventory = $_POST["countAccountInventory"];
        for($i=0; $i<$countAccountInventory; $i++)
        {
            $accountInventoryID[$i] = $_POST["accountInventoryID".sprintf("%03d", $i)];
            $productNameID[$i] = $_POST["productNameID".sprintf("%03d", $i)];
            $quantity[$i] = $_POST["quantity".sprintf("%03d", $i)];
            $status[$i] = $_POST["status".sprintf("%03d", $i)];
            $inOutDate[$i] = $_POST["inOutDate".sprintf("%03d", $i)];
        }
    }
    
    
    //$runningAccountReceiptHistory ใช้ร่วมกัน 3 table
    if (isset ($_POST["countAccountReceipt"]))
    {
        $countAccountReceipt = $_POST["countAccountReceipt"];
        for($i=0; $i<$countAccountReceipt; $i++)
        {
            $accountReceiptID[$i] = $_POST["accountReceiptID".sprintf("%03d", $i)];
            $receiptID[$i] = $_POST["receiptID".sprintf("%03d", $i)];
            $receiptDiscount[$i] = $_POST["receiptDiscount".sprintf("%03d", $i)];
            $runningAccountReceiptHistory[$i] = $_POST["runningAccountReceiptHistory".sprintf("%03d", $i)];
            $runningReceiptNo[$i] = $_POST["runningReceiptNo".sprintf("%03d", $i)];
            $accountReceiptHistoryDate[$i] = $_POST["accountReceiptHistoryDate".sprintf("%03d", $i)];
            $receiptNo[$i] = $_POST["receiptNo".sprintf("%03d", $i)];
            $receiptDate[$i] = $_POST["receiptDate".sprintf("%03d", $i)];
        }
    }
    
    //@"%@&accRecProItmID%03ld=%ld&accRecID%03ld=%ld&proNmeID%03ld=%ld&qty%03ld=%f&amtPerUnt%03ld=%f",
    if (isset ($_POST["countAccountReceiptProductItem"]))
    {
        $countAccountReceiptProductItem = $_POST["countAccountReceiptProductItem"];
        for($i=0; $i<$countAccountReceiptProductItem; $i++)
        {
            $accRecProItmID[$i] = $_POST["accRecProItmID".sprintf("%03d", $i)];
            $accRecID[$i] = $_POST["accRecID".sprintf("%03d", $i)];
            $proNmeID[$i] = $_POST["proNmeID".sprintf("%03d", $i)];
            $qty[$i] = $_POST["qty".sprintf("%03d", $i)];
            $amtPerUnt[$i] = $_POST["amtPerUnt".sprintf("%03d", $i)];
        }
    }
    
    //@"%@&recID%03ld=%ld&recProItm%03ld=%ld"
    if (isset ($_POST["countAccountMapping"]))
    {
        $countAccountMapping = $_POST["countAccountMapping"];
        for($i=0; $i<$countAccountMapping; $i++)
        {
            $recID[$i] = $_POST["recID".sprintf("%03d", $i)];
            $recProItm[$i] = $_POST["recProItm".sprintf("%03d", $i)];
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
    
    
    
    $sql = "SELECT ifnull(max(RunningReceiptNo),0) MaxRunningReceiptNo FROM `AccountReceipt` WHERE date_format(receiptdate,'%Y-%m') = date_format('$receiptDate[0]','%Y-%m');";
    $selectedRow = getSelectedRow($sql);
    $maxRunningReceiptNo = $selectedRow[0]["MaxRunningReceiptNo"];
    
    
    $sql = "select ifnull(max(RunningAccountReceiptHistory),0) MaxRunningAccountReceiptHistory from `AccountReceipt`;";
    $selectedRow = getSelectedRow($sql);
    $maxRunningAccountReceiptHistory = $selectedRow[0]["MaxRunningAccountReceiptHistory"];
    
    
    
    $sql = "INSERT INTO `accountreceipt`(`AccountReceiptID`, `ReceiptID`, `ReceiptDiscount`, `RunningAccountReceiptHistory`, `RunningReceiptNo`,`AccountReceiptHistoryDate`, `ReceiptNo`, `ReceiptDate`) VALUES ($accountReceiptID[0],$receiptID[0],$receiptDiscount[0],($maxRunningAccountReceiptHistory+1),($maxRunningReceiptNo+1+0),'$accountReceiptHistoryDate[0]','$receiptNo[0]','$receiptDate[0]')";
    for($i=1; $i<$countAccountReceipt; $i++)
    {
        //query statement
        $sql .= ",($accountReceiptID[$i],$receiptID[$i],$receiptDiscount[$i],($maxRunningAccountReceiptHistory+1),($maxRunningReceiptNo+1+$i),'$accountReceiptHistoryDate[$i]','$receiptNo[$i]','$receiptDate[$i]')";
    }
    
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    $sql = "update accountreceipt LEFT JOIN customerreceipt ON accountreceipt.ReceiptID = customerreceipt.ReceiptID LEFT JOIN postcustomer ON customerreceipt.PostCustomerID = postcustomer.PostCustomerID set accountreceipt.TaxCustomerName = ifnull(postcustomer.TaxCustomerName,''), accountreceipt.TaxCustomerAddress = ifnull(postcustomer.TaxCustomerAddress,''), accountreceipt.TaxNo = ifnull(postcustomer.TaxNo,'') where (AccountReceiptID = $accountReceiptID[0])";
    for($i=1; $i<$countAccountReceipt; $i++)
    {
        $sql .= " or (AccountReceiptID = $accountReceiptID[$i])";
    }
    
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    
    $sql = "INSERT INTO `accountreceiptproductitem`(`AccountReceiptProductItemID`, `AccountReceiptID`, `ProductNameID`, `Quantity`, `AmountPerUnit`) VALUES ($accRecProItmID[0],$accRecID[0],$proNmeID[0],$qty[0],$amtPerUnt[0])";
    for($i=1; $i<$countAccountReceiptProductItem; $i++)
    {
        //query statement
        $sql .= ",($accRecProItmID[$i],$accRecID[$i],$proNmeID[$i],$qty[$i],$amtPerUnt[$i])";
    }
    
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    $recID[$i] = $_POST["recID".sprintf("%03d", $i)];
    $recProItm[$i] = $_POST["recProItm".sprintf("%03d", $i)];
    $sql = "INSERT INTO `accountmapping`(`ReceiptID`, `ReceiptProductItemID`, `RunningAccountReceiptHistory`) VALUES ($recID[0],$recProItm[0],($maxRunningAccountReceiptHistory+1))";
    for($i=1; $i<$countAccountMapping; $i++)
    {
        //query statement
        $sql .= ",($recID[$i],$recProItm[$i],($maxRunningAccountReceiptHistory+1))";
    }
    
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    
    $sql = "INSERT INTO `accountinventory`(`AccountInventoryID`, `ProductNameID`, `Quantity`, `Status`, `InOutDate`, `RunningAccountReceiptHistory`) values($accountInventoryID[0],$productNameID[0],$quantity[0],$status[0],'$inOutDate[0]',($maxRunningAccountReceiptHistory+1))";
    for($i=1; $i<$countAccountInventory; $i++)
    {
        //query statement
        $sql .= ",($accountInventoryID[$i],$productNameID[$i],$quantity[$i],$status[$i],'$inOutDate[$i]',($maxRunningAccountReceiptHistory+1))";
    }
    
    $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
    if($ret != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    //-----
    
    
    //do script successful    
    writeToLog("query commit, file: " . basename(__FILE__));
    mysqli_commit($con);
    mysqli_close($con);
    $response = array('status' => '1', 'sql' => $sql);


    echo json_encode($response);
    exit();
?>

<?php
    include_once('dbConnect.php');
//    setConnectionValue('MINIMALIST_TEST');
    setConnectionValue($_POST['dbName']);
    ini_set("memory_limit","50M");
    writeToLog("file: " . basename(__FILE__));
    
    
   if(isset($_POST["customerID"]))
    {
        $customerID = $_POST["customerID"];
    }
    else
    {
       $customerID = 3425;
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    $sql = "SELECT receipt.* FROM `Receipt` LEFT JOIN customerreceipt ON receipt.ReceiptID = customerreceipt.ReceiptID LEFT JOIN postcustomer ON customerreceipt.PostCustomerID = postcustomer.PostCustomerID WHERE postcustomer.CustomerID = $customerID;";
    $sql .= "select ReceiptID,CustomerID,PriceSales,ProductName,Color,Size from (select `receiptproductitem`.`ReceiptProductItemID`,`receiptproductitem`.`ReceiptID` AS `ReceiptID`, `postcustomer`.`CustomerID` AS `CustomerID`, `receiptproductitem`.`PriceSales` AS `PriceSales` from `receiptproductitem` left join `receiptitemproducttype` on`receiptproductitem`.`ReceiptProductItemID` = `receiptitemproducttype`.`ReceiptProductItemID` left join `receipt` on `receiptproductitem`.`ReceiptID` = `receipt`.`ReceiptID` left join `customerreceipt` on `receipt`.`ReceiptID` = `customerreceipt`.`ReceiptID` left join `postcustomer` on `customerreceipt`.`PostCustomerID` = `postcustomer`.`PostCustomerID` WHERE postcustomer.CustomerID = $customerID)tblA LEFT JOIN (select tblReceiptProductItemID.ReceiptProductItemID, productname.Name AS `ProductName`, (case when ((`productalltypes`.`ProductType` = ('I' )) and (`productname`.`Code` = '00')) then `custommade`.`Body` when (`productalltypes`.`ProductType` = ('C' )) then `productalltypes`.`Color` else `color`.`Name` end) AS `Color`, (case when ((`productalltypes`.`ProductType` = ('I' )) and (`productname`.`Code` = '00')) then `custommade`.`Size` when (`productalltypes`.`ProductType` = ('C' )) then `productalltypes`.`Size` else `productsize`.`SizeLabel` end) AS `Size` from (SELECT receiptproductitem.ReceiptProductItemID,receiptproductitem.ProductID FROM postcustomer LEFT JOIN customerreceipt ON postcustomer.PostCustomerID = customerreceipt.PostCustomerID LEFT JOIN receiptproductitem ON customerreceipt.ReceiptID = receiptproductitem.ReceiptID WHERE postcustomer.CustomerID = $customerID)tblReceiptProductItemID left join `receiptitemproducttype` on tblReceiptProductItemID.`ReceiptProductItemID` = `receiptitemproducttype`.`ReceiptProductItemID` left join `productalltypes` on lpad(`tblReceiptProductItemID`.`ProductID`,6,'0') = `productalltypes`.`ID` and `receiptitemproducttype`.`PRODUCTTYPE` = `productalltypes`.`ProductType` left join `productname` on `productalltypes`.`ProductCategory2` = `productname`.`ProductCategory2` and `productalltypes`.`ProductCategory1` = `productname`.`ProductCategory1` and `productalltypes`.`ProductName` = `productname`.`Code` left join `color` on `productalltypes`.`Color` = `color`.`Code` left join `productsize` on `productalltypes`.`Size` = `productsize`.`Code` left join `custommade` on `tblReceiptProductItemID`.`ProductID` = `custommade`.`ProductIDPost`)tblB ON tblA.ReceiptProductItemID = tblB.ReceiptProductItemID;";
//    $sql .= "select ReceiptID,CustomerID,PriceSales,ProductName,Color,Size from (select `receiptproductitem`.`ReceiptProductItemID`,`receiptproductitem`.`ReceiptID` AS `ReceiptID`, `postcustomer`.`CustomerID` AS `CustomerID`, `receiptproductitem`.`PriceSales` AS `PriceSales` from `receiptproductitem` left join `receiptitemproducttype` on`receiptproductitem`.`ReceiptProductItemID` = `receiptitemproducttype`.`ReceiptProductItemID` left join `receipt` on `receiptproductitem`.`ReceiptID` = `receipt`.`ReceiptID` left join `customerreceipt` on `receipt`.`ReceiptID` = `customerreceipt`.`ReceiptID` left join `postcustomer` on `customerreceipt`.`PostCustomerID` = `postcustomer`.`PostCustomerID` WHERE postcustomer.CustomerID = $customerID)tblA LEFT JOIN (select tblReceiptProductItemID.ReceiptProductItemID, productname.Name AS `ProductName`, (case when ((`productalltypes`.`ProductType` = ('I' collate utf8mb4_general_ci)) and (`productname`.`Code` = '00')) then `custommade`.`Body` when (`productalltypes`.`ProductType` = ('C' collate utf8mb4_general_ci)) then `productalltypes`.`Color` else `color`.`Name` end) AS `Color`, (case when ((`productalltypes`.`ProductType` = ('I' collate utf8mb4_general_ci)) and (`productname`.`Code` = '00')) then `custommade`.`Size` when (`productalltypes`.`ProductType` = ('C' collate utf8mb4_general_ci)) then `productalltypes`.`Size` else `productsize`.`SizeLabel` end) AS `Size` from (SELECT receiptproductitem.ReceiptProductItemID,receiptproductitem.ProductID FROM postcustomer LEFT JOIN customerreceipt ON postcustomer.PostCustomerID = customerreceipt.PostCustomerID LEFT JOIN receiptproductitem ON customerreceipt.ReceiptID = receiptproductitem.ReceiptID WHERE postcustomer.CustomerID = $customerID)tblReceiptProductItemID left join `receiptitemproducttype` on tblReceiptProductItemID.`ReceiptProductItemID` = `receiptitemproducttype`.`ReceiptProductItemID` left join `productalltypes` on lpad(`tblReceiptProductItemID`.`ProductID`,6,'0') = `productalltypes`.`ID` and `receiptitemproducttype`.`PRODUCTTYPE` = `productalltypes`.`ProductType` left join `productname` on `productalltypes`.`ProductCategory2` = `productname`.`ProductCategory2` and `productalltypes`.`ProductCategory1` = `productname`.`ProductCategory1` and `productalltypes`.`ProductName` = `productname`.`Code` left join `color` on `productalltypes`.`Color` = `color`.`Code` left join `productsize` on `productalltypes`.`Size` = `productsize`.`Code` left join `custommade` on `tblReceiptProductItemID`.`ProductID` = `custommade`.`ProductIDPost`)tblB ON tblA.ReceiptProductItemID = tblB.ReceiptProductItemID;";
    writeToLog("ReceiptByMemberGet sql: " . $sql);
    
    /* execute multi query */
    if (mysqli_multi_query($con, $sql)) {
        $arrOfTableArray = array();
        $resultArray = array();
        do {
            /* store first result set */
            if ($result = mysqli_store_result($con)) {
                while ($row = mysqli_fetch_object($result)) {
                    array_push($resultArray, $row);
                }
                array_push($arrOfTableArray,$resultArray);
                $resultArray = [];
                mysqli_free_result($result);
            }
            if(!mysqli_more_results($con))
            {
                break;
            }
        } while (mysqli_next_result($con));
        
        echo json_encode($arrOfTableArray);
    }
    
    
    // Close connections
    mysqli_close($con);
?>

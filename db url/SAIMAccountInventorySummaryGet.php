<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    ini_set("memory_limit","50M");
    writeToLog("file: " . basename(__FILE__));
    
    
    if(isset($_POST["dateFrom"]) && isset($_POST["dateTo"])
       )
    {        
        $dateFrom = $_POST["dateFrom"];
        $dateTo = $_POST["dateTo"];
    }
    else
    {
        $dateFrom = '2017-02-06';
        $dateTo = '2017-02-10';
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    $sql .= "SELECT ProductNameID,ProductName,sum(Quantity) as Quantity,sum(SalesQuantity) as SalesQuantity from (select case ProductNameID when 35 then 64 when 62 then 45 when 16 then 5 else ProductNameID end as ProductNameID ,case ProductNameID when 35 then 'Spencer' when 62 then 'Rebecca' when 16 then 'Audrey' else ProductName end as ProductName,Quantity,SalesQuantity from (select ifnull(ProductNameID,SalesProductNameID) ProductNameID,ifnull(ProductName,SalesProductName) ProductName, ifnull(Quantity,0) Quantity, ifnull(SalesQuantity,0) SalesQuantity from (select a.*,b.* from (select ProductNameID,ProductName,sum(quantity) Quantity from (select productname.ProductNameID,productname.Name ProductName, sum(accountInventory.quantity*accountInventory.status) Quantity from accountInventory left join productname on accountInventory.productnameid = productname.productnameid where status = 1 and date_format(accountinventory.InOutDate,'%Y-%m-%d') < '$dateFrom' group by productname.productnameid,productname.Name union select productname.ProductNameID,productname.Name ProductName, sum(accountInventory.quantity*accountInventory.status) Quantity from accountInventory left join productname on accountInventory.productnameid = productname.productnameid where status = -1 and date_format(accountinventory.InOutDate,'%Y-%m-%d') <= '$dateTo' group by productname.productnameid,productname.Name) a group by ProductNameID,ProductName) a left join (SELECT productname.ProductNameID SalesProductNameID,productname.Name SalesProductName,count(productname.ProductNameID) SalesQuantity FROM `receipt` left join receiptproductitem ON receipt.ReceiptID = receiptproductitem.ReceiptID LEFT JOIN receiptitemproducttype ON receiptproductitem.ReceiptProductItemID=receiptitemproducttype.ReceiptProductItemID left join productalltypes on lpad(receiptproductitem.ProductID,6,'0') = productalltypes.ID and receiptitemproducttype.ProductType = productalltypes.ProductType LEFT JOIN productname ON productalltypes.ProductCategory2 = productname.ProductCategory2 AND productalltypes.ProductCategory1 = productname.ProductCategory1 AND productalltypes.ProductName = productname.Code LEFT JOIN accountmapping ON receiptproductitem.ReceiptProductItemID = accountmapping.ReceiptProductItemID WHERE date_format(receiptdate,'%Y-%m-%d') BETWEEN '$dateFrom' and '$dateTo' and receipt.payprice != 0 and accountmapping.ReceiptProductItemID is null GROUP BY productname.ProductNameID,productname.Name) b on a.ProductNameID = b.SalesProductNameID UNION select a.*,b.* from (select ProductNameID,ProductName,sum(quantity) Quantity from (select productname.ProductNameID,productname.Name ProductName, sum(accountInventory.quantity*accountInventory.status) Quantity from accountInventory left join productname on accountInventory.productnameid = productname.productnameid where status = 1 and date_format(accountinventory.InOutDate,'%Y-%m-%d') < '$dateFrom' group by productname.productnameid,productname.Name union select productname.ProductNameID,productname.Name ProductName, sum(accountInventory.quantity*accountInventory.status) Quantity from accountInventory left join productname on accountInventory.productnameid = productname.productnameid where status = -1 and date_format(accountinventory.InOutDate,'%Y-%m-%d') <= '$dateTo' group by productname.productnameid,productname.Name) a group by ProductNameID,ProductName) a right join (SELECT productname.ProductNameID SalesProductNameID,productname.Name SalesProductName,count(productname.ProductNameID) SalesQuantity FROM `receipt` left join receiptproductitem ON receipt.ReceiptID = receiptproductitem.ReceiptID LEFT JOIN receiptitemproducttype ON receiptproductitem.ReceiptProductItemID=receiptitemproducttype.ReceiptProductItemID left join productalltypes on lpad(receiptproductitem.ProductID,6,'0') = productalltypes.ID and receiptitemproducttype.ProductType = productalltypes.ProductType LEFT JOIN productname ON productalltypes.ProductCategory2 = productname.ProductCategory2 AND productalltypes.ProductCategory1 = productname.ProductCategory1 AND productalltypes.ProductName = productname.Code LEFT JOIN accountmapping ON receiptproductitem.ReceiptProductItemID = accountmapping.ReceiptProductItemID WHERE date_format(receiptdate,'%Y-%m-%d') BETWEEN '$dateFrom' and '$dateTo' and receipt.payprice != 0 and accountmapping.ReceiptProductItemID is null GROUP BY productname.ProductNameID,productname.Name) b on a.ProductNameID = b.SalesProductNameID where b.salesQuantity != 0) c)d)e GROUP by productnameid,productname;";
    $sql .= "SELECT receipt.DiscountValue+receipt.PayPrice/(1-receipt.DiscountPercent/100)*receipt.DiscountPercent/100 as ReceiptDiscount, case receipt.PaymentMethod when 'CA' then 0 when 'CC' then 1 when 'BO' then 1 end as IsCredit, ifnull(postcustomer.TaxCustomerName,'')TaxCustomerName, receiptProductItem.ReceiptProductItemID,receipt.ReceiptID,receipt.ReceiptDate, case productname.ProductNameID when 35 then 64 when 62 then 45 when 16 then 5 else productname.ProductNameID end as ProductNameID,case ProductNameID when 35 then 'Spencer' when 62 then 'Rebecca' when 16 then 'Audrey' else ProductName.Name end as ProductName, case ifnull(postcustomer.TaxCustomerName,'') when '' then productname.AccountPrice else  receiptProductItem.PriceSales end as PriceSales FROM `receipt` left join receiptproductitem ON receipt.ReceiptID = receiptproductitem.ReceiptID LEFT JOIN receiptitemproducttype ON receiptproductitem.ReceiptProductItemID=receiptitemproducttype.ReceiptProductItemID left join productalltypes on lpad(receiptproductitem.ProductID,6,'0') = productalltypes.ID and receiptitemproducttype.ProductType = productalltypes.ProductType LEFT JOIN productname ON productalltypes.ProductCategory2 = productname.ProductCategory2 AND productalltypes.ProductCategory1 = productname.ProductCategory1 AND productalltypes.ProductName = productname.Code LEFT JOIN accountmapping ON receiptproductitem.ReceiptProductItemID = accountmapping.ReceiptProductItemID left join customerreceipt ON receipt.ReceiptID = customerreceipt.ReceiptID LEFT JOIN postcustomer ON customerreceipt.PostCustomerID = postcustomer.PostCustomerID WHERE date_format(receiptdate,'%Y-%m-%d') BETWEEN '$dateFrom' and '$dateTo' and receipt.payprice != 0 and accountmapping.ReceiptProductItemID is null and ReceiptProductItem.producttype not in ('B','D','E','F');";
    writeToLog("accountInventorySummary sql: " . $sql);
    
    
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

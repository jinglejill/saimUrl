<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    ini_set("memory_limit","50M");
    writeToLog("file: " . basename(__FILE__));
    
    
    if(isset($_POST["productCategory2"]) && isset($_POST["dateIn"])
       )
    {
        $productCategory2 = $_POST["productCategory2"];
        $dateIn = $_POST["dateIn"];
    }
    else
    {
        $productCategory2 = '00';
        $dateIn = '2017-02-06';
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    $sql = "select rr.AccountInventoryID,rr.ProductName, rr.ProductNameID, rr.InOutDate,rr.Quantity, case rr.QuantityLeft >= rr.CumulativeSum when 1 then 0 else 1 end as Used from (SELECT t.AccountInventoryID,t.productCategory2,t.ProductName, t.ProductNameID, t.InOutDate, t.Quantity, (SELECT SUM(x.Quantity) FROM (select @i:=@i+1 AS iterator, ss.* from (SELECT accountinventory.*,productname.ProductCategory2,productname.name ProductName FROM accountinventory LEFT JOIN productname ON accountinventory.ProductNameID=productname.ProductNameID where accountinventory.status=1 ORDER BY ProductName.name,InOutDate desc,ModifiedDate desc, accountinventoryID desc) ss, (SELECT @i:=0) AS r) x WHERE x.ProductNameID = t.ProductNameID and x.iterator <= t.iterator) AS CumulativeSum, (SELECT sum(Quantity*Status) Quantity FROM accountinventory where accountinventory.ProductNameID = t.ProductNameID) as QuantityLeft FROM (select @i:=@i+1 AS iterator, ss.* from (SELECT accountinventory.*,productname.ProductCategory2,productname.name ProductName FROM accountinventory LEFT JOIN productname ON accountinventory.ProductNameID=productname.ProductNameID where accountinventory.status=1  ORDER BY ProductName.name,InOutDate desc,ModifiedDate desc, accountinventoryID desc) ss, (SELECT @i:=0) AS r) t ORDER BY t.iterator) rr WHERE rr.productCategory2 = '$productCategory2' and rr.inoutdate >= '$dateIn' order by rr.productname,rr.inoutdate desc;";
    
    writeToLog("accountInventoryAdded sql: " . $sql);
    
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

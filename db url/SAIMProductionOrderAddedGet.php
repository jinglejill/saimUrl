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
    
    
    $sql = "SELECT productionorder.ProductionOrderID, productionorder.RunningPoNo, productname.ProductNameID, productname.Name ProductName,color.Code Color,color.Name ColorName,productsize.Code Size,productsize.SizeLabel SizeName, date_format(productionorder.OrderDeliverDate,'%Y-%m-%d') OrderDeliverDate, productionorder.Quantity,tblRemaining.QuantityRemaining FROM `productionorder` LEFT JOIN productname ON productionorder.ProductNameID=productname.ProductNameID LEFT JOIN color ON productionorder.Color = color.Code LEFT JOIN productsize ON productionorder.Size = productsize.Code LEFT JOIN (select productionorder.RunningPoNo,productionorder.ProductNameID,productionorder.Color,productionorder.Size, sum(Quantity*Status) QuantityRemaining FROM productionorder GROUP BY productionorder.RunningPoNo,productionorder.ProductNameID,productionorder.Color,productionorder.Size) tblRemaining ON productionorder.RunningPoNo=tblRemaining.RunningPoNo AND productionorder.ProductNameID=tblRemaining.productNameID and productionorder.Color=tblRemaining.Color and productionorder.Size=tblRemaining.Size WHERE ProductName.productCategory2 = '$productCategory2' and productionorder.OrderDeliverDate >= '$dateIn' and productionorder.Status = 1 ORDER BY productionorder.RunningPoNo desc, productname.Name,color.Name,productsize.SizeOrder;";
    
    writeToLog("productionOrderAdded sql: " . $sql);
    
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

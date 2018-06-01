setConnectionValue($_GET['dbName']);
writeToLog("file: " . basename(__FILE__));
printAllPost();


if(isset($_GET["countProductSales"]))
{
    $countProductSales = $_GET["countProductSales"];
    for($i=0; $i<$countProductSales; $i++)
    {
        $productSalesID[$i] = $_GET["productSalesID".sprintf("%02d", $i)];
        $productSalesSetID[$i] = $_GET["productSalesSetID".sprintf("%02d", $i)];
        $productNameID[$i] = $_GET["productNameID".sprintf("%02d", $i)];
        $color[$i] = $_GET["color".sprintf("%02d", $i)];
        $size[$i] = $_GET["size".sprintf("%02d", $i)];
        $price[$i] = $_GET["price".sprintf("%02d", $i)];
        $detail[$i] = $_GET["detail".sprintf("%02d", $i)];
        $percentDiscountMember[$i] = $_GET["percentDiscountMember".sprintf("%02d", $i)];
        $percentDiscountFlag[$i] = $_GET["percentDiscountFlag".sprintf("%02d", $i)];
        $percentDiscount[$i] = $_GET["percentDiscount".sprintf("%02d", $i)];
        $pricePromotion[$i] = $_GET["pricePromotion".sprintf("%02d", $i)];
        $shippingFee[$i] = $_GET["shippingFee".sprintf("%02d", $i)];
        $imageDefault[$i] = $_GET["imageDefault".sprintf("%02d", $i)];
        $imageID[$i] = $_GET["imageID".sprintf("%02d", $i)];
        $cost[$i] = $_GET["cost".sprintf("%02d", $i)];
    }
}
$modifiedUser = $_GET["modifiedUser"];

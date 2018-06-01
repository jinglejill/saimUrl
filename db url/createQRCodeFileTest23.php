<?php
    
    
    //    /** Error reporting */
        error_reporting(E_ALL);
    
    /** Include path **/
    ini_set('include_path', ini_get('include_path').';../Classes/');
    
    /** PHPExcel */
    include 'PHPExcel.php';
    
    /** PHPExcel_Writer_Excel2007 */
//    include 'PHPExcel/Writer/Excel2007.php';
//    include 'Excel2007.php';
    
    include_once('SAIM/dbConnect.php');
    
    setConnectionValue('MINIMALIST');
    ?>
<html>
<head>

<STYLE TYPE="text/css">
<!--
TD{font-family: Tahoma; font-size: 10pt;}
--->
</STYLE>
<script type="text/javascript">

function productNameChanged(obj){
    var sizeArrCat2Cat1ProductCode = document.getElementById('sizeArrCat2Cat1ProductCode').value;
    var strCat2Cat1ProductCode = document.getElementById('strCat2Cat1ProductCode').value;
    var rowCount = 10;
    var k = obj.name.substring(11,13);//productName1 ****
    
    
    //color
    for(var j=0; j<sizeArrCat2Cat1ProductCode; j++)
    {
        var colorID = 'color';
        colorID = colorID.concat(k);
        colorID = colorID.concat(strCat2Cat1ProductCode.substring(0+6*j,6+6*j));
        document.getElementById(colorID).style.display = 'none';
    }
    var colorID = 'color';
    colorID = colorID.concat(k);
    colorID = colorID.concat(obj.value);//010101
    document.getElementById(colorID).style.display = 'inline';
    
    
    
    //size
    for(var j=0; j<sizeArrCat2Cat1ProductCode; j++)
    {
        var sizeID = 'size';
        sizeID = sizeID.concat(k);
        sizeID = sizeID.concat(strCat2Cat1ProductCode.substring(0+6*j,6+6*j));
        document.getElementById(sizeID).style.display = 'none';
    }
    var sizeID = 'size';
    sizeID = sizeID.concat(k);
    sizeID = sizeID.concat(obj.value);//010101
    document.getElementById(sizeID).style.display = 'inline';
}

function resetForm()
{
    rowCount = 10;
    var id;
    id = 'productName';
    for(var i=1; i<=rowCount; i++)
    {
        var j = ("0" + i).slice(-2);
        document.getElementById(id.concat(j)).selectedIndex = 0;
    }
    
    id = 'color';
    var sizeArrCat2Cat1ProductCode = document.getElementById('sizeArrCat2Cat1ProductCode').value;
    var strCat2Cat1ProductCode = document.getElementById('strCat2Cat1ProductCode').value;
    for(var i=1; i<=rowCount; i++)
    {
        for(var j=0; j<sizeArrCat2Cat1ProductCode; j++)
        {
            var colorID = 'color';
            colorID = colorID.concat(("0" + i).slice(-2));
            colorID = colorID.concat(strCat2Cat1ProductCode.substring(0+6*j,6+6*j));
            document.getElementById(colorID).selectedIndex = 0;
        }
    }
    
    id = 'size';
    //    var sizeArrCat2Cat1ProductCode = document.getElementById('sizeArrCat2Cat1ProductCode').value;
    //    var strCat2Cat1ProductCode = document.getElementById('strCat2Cat1ProductCode').value;
    for(var i=1; i<=rowCount; i++)
    {
        for(var j=0; j<sizeArrCat2Cat1ProductCode; j++)
        {
            var sizeID = 'size';
            sizeID = sizeID.concat(("0" + i).slice(-2));
            sizeID = sizeID.concat(strCat2Cat1ProductCode.substring(0+6*j,6+6*j));
            document.getElementById(sizeID).selectedIndex = 0;
        }
    }
    
    id = 'manufacturingDate';
    for(var i=1; i<=rowCount; i++)
    {
        var j = ("0" + i).slice(-2);
        document.getElementById(id.concat(j)).value = '';
    }
    
    id = 'quantity';
    for(var i=1; i<=rowCount; i++)
    {
        var j = ("0" + i).slice(-2);
        document.getElementById(id.concat(j)).value = '';
    }
}

</script>
</head>
<body>
<form id="myForm" action="createQRCodeFileTest23.php" method="post">
<?php
    function validateDate($date)
    {
        $d = DateTime::createFromFormat('Ymd', $date);
        return $d && $d->format('Ymd') == $date;
    }

    $rowCount = 10;
    
    //product name
    $i = 0;
    $arrProductCode = [];
    $arrProductName = [];
    $sql2 = "SELECT * from (SELECT concat(productname.ProductCategory2,productname.ProductCategory1,productname.Code) as Cat2Cat1ProductName, productname.Name AS ProductName FROM `productname` WHERE productname.ProductNameID not in (1,2,13,24,4,42,41,14)) Cat2Cat1ProductName ORDER BY Cat2Cat1ProductName.ProductName";
    $result2 = mysqli_query($con2, $sql2);
    while ($row2 = mysqli_fetch_array($result2)) {
        $arrProductCode[$i] = $row2['Cat2Cat1ProductName'];
        $arrProductName[$row2['Cat2Cat1ProductName']] = $row2['ProductName'];
        $i++;
    }
    mysqli_free_result($result2);
    
    
    //color
    $i = 0;
    $j = 0;
    $strCat2Cat1ProductCode = '';
    $arrCat2Cat1ProductCode = [];
    $previousCat2Cat1ProductCode = '';
    $arrColorCode = [];
    $arrColor = [];
    $sql2 = "SELECT distinct concat(productname.ProductCategory2,productname.ProductCategory1,productname.Code) as Cat2Cat1ProductCode,productname.ProductNameID,color.Code as ColorCode, productname.Name as ProductName,color.Name as Color FROM `productsales` left join productname ON productsales.ProductNameID=productname.ProductNameID LEFT JOIN color ON productsales.Color = color.Code where productsales.ProductSalesSetID = 0 and productsales.ProductNameID not in (1,2,13,24,4,42,41,14) ORDER BY productname.Name,color.Name";
    $result2 = mysqli_query($con2, $sql2);
    while ($row2 = mysqli_fetch_array($result2)) {
        if($previousCat2Cat1ProductCode != $row2['Cat2Cat1ProductCode']){
            $i = 0;
            $previousCat2Cat1ProductCode = $row2['Cat2Cat1ProductCode'];
            $arrCat2Cat1ProductCode[$j] = $row2['Cat2Cat1ProductCode'];
            $strCat2Cat1ProductCode = $strCat2Cat1ProductCode . str_pad($row2['Cat2Cat1ProductCode'],6,'0',STR_PAD_LEFT);
            $j++;
        }
        
        $arrColorCode[$previousCat2Cat1ProductCode][$i] = $row2['ColorCode'];
        $arrColor[$row2['ColorCode']] = $row2['Color'];
        $i++;
    }
    $sizeArrCat2Cat1ProductCode = sizeof($arrCat2Cat1ProductCode);
    mysqli_free_result($result2);
    
    
    
    //size new
    $i = 0;
    $j = 0;
    $previousCat2Cat1ProductCode = '';
    $arrSizeID = [];
    $arrSizeLabel = [];
    $sql2 = "SELECT distinct concat(productname.ProductCategory2,productname.ProductCategory1,productname.Code) as Cat2Cat1ProductCode,productname.ProductNameID ,productsize.Code, productname.Name as ProductName ,productsize.SizeLabel FROM `productsales` left join productname ON productsales.ProductNameID=productname.ProductNameID LEFT join productsize on productsales.Size = productsize.Code where productsales.ProductSalesSetID = 0 and productsales.ProductNameID not in (1,2,13,24,4,42,41,14) ORDER BY productname.Name,productsize.SizeOrder";
    $result2 = mysqli_query($con2, $sql2);
    while ($row2 = mysqli_fetch_array($result2)) {
        if($previousCat2Cat1ProductCode != $row2['Cat2Cat1ProductCode']){
            $i = 0;
            $previousCat2Cat1ProductCode = $row2['Cat2Cat1ProductCode'];
            $j++;
        }
        $arrSizeID[$previousCat2Cat1ProductCode][$i] = $row2['Code'];
        $arrSizeLabel[$row2['Code']] = $row2['SizeLabel'];
        $i++;
    }
    mysqli_free_result($result2);
    
    
    
    
    //price
    $arrProductPrice = [];
    $sql2 = "SELECT productname.Name as ProductName,color.Name as Color,productsize.SizeLabel, productsales.Price from productsales left join productname on productsales.ProductNameID=productname.ProductNameID LEFT JOIN color ON productsales.Color=color.Code LEFT JOIN productsize on productsales.Size = productsize.Code where productsalessetid = 0 and productsales.ProductNameID not in (1,2,13,24,4,42,41,14)  ORDER BY productname.Name, color.Name, productsize.SizeLabel";
    $result2 = mysqli_query($con2, $sql2);
    while ($row2 = mysqli_fetch_array($result2)) {
        $arrProductPrice[$row2['ProductName']][$row2['Color']][$row2['SizeLabel']] = $row2['Price'];
    }
    mysqli_free_result($result2);
    
    
    $sql2 = "SELECT case when max(RunningID) is null then 0 else max(RunningID) end as RunningID FROM `itemrunningid`";
    $result2 = mysqli_query($con2, $sql2);
    $row2 = mysqli_fetch_array($result2);
    $maxProductID = $row2['RunningID'];
    mysqli_free_result($result2);

    
    $validate = 1;
    if($_POST["create"]){        
        $arrUseRow = [];
        $i = 0;
        for($k=1; $k<=$rowCount; $k++)
        {
            $r02 = str_pad($k,2,'0',STR_PAD_LEFT);
            if($_POST["quantity".$r02] != ""){
                $arrUseRow[$i] = $r02;
                $i++;
            }
        }
        
        if(sizeof($arrUseRow) == 0){
            echo "<table><tr><td><font color='red'>Please input quantity</font></td></tr></table>";
            $validate = 0;
        }else{
            
            //validate mfd
            for($i=0; $i<sizeof($arrUseRow); $i++)
            {
                if(!$_POST["manufacturingDate".$arrUseRow[$i]]){
                    echo "<table><tr><td><font color='red'>(No: ".$arrUseRow[$i].") Please input MFD</font></td></tr></table>";
                    $validate = 0;
                }else if(!validateDate($_POST["manufacturingDate".$arrUseRow[$i]])){
                    echo "<table><tr><td><font color='red'>(No: ".$arrUseRow[$i].") Incorrect MFD format</font></td></tr></table>";
                    $validate = 0;
                }
            }
        }
    }
    if($validate == 1 && $_POST["create"])
    {
        $startingID = $maxProductID+1;
        $cellValue = [];
        $step = 1;
        for($k=1; $k<=$rowCount; $k++)
        {
            $r02 = str_pad($k,2,'0',STR_PAD_LEFT);
            $quantity = intval($_POST["quantity".$r02]);
            for($i=1; $i<=$quantity; $i++)
            {
                $qrCodeFormat = "SAIM Minimalist\n%s\nEnd";
                $code = $_POST["productName".$r02].$_POST["color".$r02.$_POST["productName".$r02]].str_pad($_POST["size".$r02.$_POST["productName".$r02]],2,'0',STR_PAD_LEFT).$_POST["manufacturingDate".$r02] . sprintf("%06d", $maxProductID + $i);
                
                $fullCode = sprintf($qrCodeFormat,$code);
                
                $cellValue[$step+$i]['A'] = $fullCode;
                $cellValue[$step+$i]['B'] = $arrProductName[$_POST["productName".$r02]];
                $cellValue[$step+$i]['C'] = $arrColor[$_POST["color".$r02.$_POST["productName".$r02]]];
                $cellValue[$step+$i]['D'] = $arrSizeLabel[$_POST["size".$r02.$_POST["productName".$r02]]];//$arrSize[$_POST["size".$r02]];
                $cellValue[$step+$i]['E'] = $arrProductPrice[$arrProductName[$_POST["productName".$r02]]][$arrColor[$_POST["color".$r02.$_POST["productName".$r02]]]][$arrSizeLabel[$_POST["size".$r02.$_POST["productName".$r02]]]];
                if($i == $quantity){
                    $maxProductID = $maxProductID + $i;
                    $step = $step+$i;
                }
            }
        }
        
        $sql2 = "";
        for($j=$startingID; $j<=$maxProductID; $j++)
        {
            $sql2 .= "INSERT INTO `itemrunningid`( `RunningID`) VALUES (".$j.");";
        }
        
        $result2 = mysqli_multi_query($con2,$sql2);
        if(result2)
        {
            //success            
//            $objPHPExcel = new PHPExcel();
//            echo 'inside result2 after new phpexcel ';
//            $objPHPExcel->getProperties()->setCreator("Thidaporn Kijkamjai");
//            $objPHPExcel->getProperties()->setLastModifiedBy("Minimalist");
//            $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
//            $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
//            $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
//            echo 'inside result2 before getactivesheet ';
//            $objPHPExcel->setActiveSheetIndex(0);
//            $objPHPExcel->getActiveSheet()->SetCellValue('A1','Code');
//            $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Style');
//            $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Color');
//            $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Size');
//            $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Price');
//            
//            echo 'inside result2 before setcolumn dimension ';
//            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
//            
//            $objPHPExcel->getActiveSheet()->setTitle('Product item');
            
            
            echo "<table>";
            echo "<tr>";
            echo "<td>" . 'Code' . "</td>";
            echo "<td>" . 'Style' . "</td>";
            echo "<td>" . 'Color' . "</td>";
            echo "<td>" . 'Size' . "</td>";
            echo "<td>" . 'Price' . "</td>";
            echo "</tr>";
            for($j=2; $j<=$step; $j++)
            {
                echo "<tr>";
                echo "<td>" . $cellValue[$j]['A'] . "</td>";
                echo "<td>" . $cellValue[$j]['B'] . "</td>";
                echo "<td>" . $cellValue[$j]['C'] . "</td>";
                echo "<td>" . $cellValue[$j]['D'] . "</td>";
                $priceValue = number_format($cellValue[$j]['E']). " Baht";
                echo "<td>" . $priceValue . "</td>";
                echo "</tr>";
//                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$j, $cellValue[$j]['A']);
//                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$j, $cellValue[$j]['B']);
//                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$j, $cellValue[$j]['C']);
//                                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$j, $cellValue[$j]['D']);
//                $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$j, $cellValue[$j]['D'], PHPExcel_Cell_DataType::TYPE_STRING);
//                $priceValue = number_format($cellValue[$j]['E']). " Baht";
//                $objPHPExcel->getActiveSheet()->getStyle('E'.$j)->getNumberFormat()->setFormatCode('###,###,###');
//                $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$j, $priceValue, PHPExcel_Cell_DataType::TYPE_STRING);
            }
            echo "</table>";
            
            $fileName = 'ProductItem'.rand(10,99).'.xls';
//            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
//            $objWriter->save($fileName);
            echo "<table><tr id='trCreateFileSuccess'><td><font color='blue'>Create file success</font></td></tr></table>";
            ?>

<input type="button" id ="download" value="download" name="download" onclick="document.getElementById('trCreateFileSuccess').style.display = 'none';document.getElementById('download').style.display = 'none';resetForm();productNameChanged(document.getElementById('productName01'));location.href='<?php echo $fileName;?>'">
<?php
    }
    else
    {
        echo "<table><tr id='trCreateFileSuccess'><td><font color='red'>Error occurs, please try again</font></td></tr></table>";
    }
    }
    
    
    echo "<table><tr><td align='center' style='text-decoration: underline;'>No.</td><td align='center' style='text-decoration: underline;'>Style</td><td align='center' style='text-decoration: underline;'>Color</td><td align='center' style='text-decoration: underline;'>Size</td><td align='center' style='text-decoration: underline;'>MFD</td><td align='center' style='text-decoration: underline;'>Quantity</td></tr>";
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    if($_POST["create"])
    {
        for($k=1; $k<=$rowCount; $k++)
        {
            $r02 = str_pad($k,2,'0',STR_PAD_LEFT);
            echo "<tr>";
            
            
            //productname
            echo "<td valign='top'>".$k."</td>";
            echo "<td valign='top'><select id='productName".$r02."' name='productName".$r02."' style='width:120' onchange='productNameChanged(this)'>";
            $countRow = 0;
            for($j=0; $j<sizeof($arrProductName); $j++)
            {
                $value = $arrProductCode[$j];
                $text = $arrProductName[$value];
                if($value == $_POST['productName'.$r02]){
                    echo "<option value='".$value."' selected>".$text."</option>";
                }else{
                    echo "<option value='".$value."'>".$text."</option>";
                }
                $countRow++;
            }
            echo "</select></td>";
            
            
            //color
            echo "<td valign='top'>";
            for($l=0; $l<sizeof($arrCat2Cat1ProductCode); $l++)
            {
                $cp06 = str_pad($arrCat2Cat1ProductCode[$l],6,'0',STR_PAD_LEFT);
                if($_POST['productName'.$r02] == $cp06){
                    echo "<select id='color".$r02.$cp06."' name='color".$r02.$cp06."' style='width:120'>";
                }
                else{
                    echo "<select id='color".$r02.$cp06."' name='color".$r02.$cp06."' style='width:120;display:none'>";
                }
                for($j=0; $j<sizeof($arrColorCode[$arrCat2Cat1ProductCode[$l]]); $j++)
                {
                    $value = $arrColorCode[$arrCat2Cat1ProductCode[$l]][$j];
                    $text = $arrColor[$value];
                    if($value == $_POST['color'.$r02.$cp06]){
                        echo "<option value='".$value."' selected>".$text."</option>";
                    }else{
                        echo "<option value='".$value."'>".$text."</option>";
                    }
                    $countRow++;
                }
                echo "</select>";
            }
            echo "</td>";
            
            
            
            //size new
            echo "<td valign='top'>";
            for($l=0; $l<sizeof($arrCat2Cat1ProductCode); $l++)
            {
                $cp06 = str_pad($arrCat2Cat1ProductCode[$l],6,'0',STR_PAD_LEFT);
                //
                if($_POST['productName'.$r02] == $cp06){
                    echo "<select id='size".$r02.$cp06."' name='size".$r02.$cp06."' style='width:120'>";
                }
                else{
                    echo "<select id='size".$r02.$cp06."' name='size".$r02.$cp06."' style='width:120;display:none'>";
                }
                for($j=0; $j<sizeof($arrSizeID[$arrCat2Cat1ProductCode[$l]]); $j++)
                {
                    $value = $arrSizeID[$arrCat2Cat1ProductCode[$l]][$j];
                    $text = $arrSizeLabel[$value];
                    if($value == $_POST['size'.$r02.$cp06]){
                        echo "<option value='".$value."' selected>".$text."</option>";
                    }else{
                        echo "<option value='".$value."'>".$text."</option>";
                    }
                    $countRow++;
                }
                echo "</select>";
            }
            echo "</td>";
            
            
            echo '<td valign="top"><input type="date" id="manufacturingDate'.$r02.'" name="manufacturingDate'.$r02.'" width="150" maxlength="8" value="'.$_POST['manufacturingDate'.$r02].'"></td>';
            echo '<td valign="top"><input type="number" min="0" id="quantity'.$r02.'" name="quantity'.$r02.'" width="150" pattern="^[0-9]" step="1" value="'.$_POST['quantity'.$r02].'" onkeypress="return event.charCode >= 48 && event.charCode <= 57"></td>';
            
            echo "</tr>";
        }
    }
    else
    {
        for($k=1; $k<=$rowCount; $k++)
        {
            $r02 = str_pad($k,2,'0',STR_PAD_LEFT);
            echo "<tr>";
            echo "<td valign='top'>".$k."</td>";
            
            
            //productname
            echo "<td valign='top'><select id='productName".$r02."' name='productName".$r02."' style='width:120' onchange='productNameChanged(this)'>";
            $countRow = 0;
            for($j=0; $j<sizeof($arrProductName); $j++)
            {
                $value = $arrProductCode[$j];
                $text = $arrProductName[$value];
                if($countRow == 0){
                    echo "<option value='".$value."' selected>".$text."</option>";
                }else{
                    echo "<option value='".$value."'>".$text."</option>";
                }
                $countRow++;
            }
            echo "</select></td>";
            
            
            
            //color
            echo "<td valign='top'>";
            for($l=0; $l<sizeof($arrCat2Cat1ProductCode); $l++)
            {
                $cp06 = str_pad($arrCat2Cat1ProductCode[$l],6,'0',STR_PAD_LEFT);
                if($l==0){
                    echo "<select id='color".$r02.$cp06."' name='color".$r02.$cp06."' style='width:120'>";
                }else{
                    echo "<select id='color".$r02.$cp06."' name='color".$r02.$cp06."' style='width:120;display:none' >";
                }
                
                for($j=0; $j<sizeof($arrColorCode[$arrCat2Cat1ProductCode[$l]]); $j++)
                {
                    $value = $arrColorCode[$arrCat2Cat1ProductCode[$l]][$j];
                    $text = $arrColor[$value];
                    if($countRow == 0){
                        echo "<option value='".$value."' selected>".$text."</option>";
                    }else{
                        echo "<option value='".$value."'>".$text."</option>";
                    }
                    $countRow++;
                }
                echo "</select>";
            }
            echo "</td>";
            
            
            
            //size new
            echo "<td valign='top'>";
            for($l=0; $l<sizeof($arrCat2Cat1ProductCode); $l++)
            {
                $cp06 = str_pad($arrCat2Cat1ProductCode[$l],6,'0',STR_PAD_LEFT);
                if($l==0){
                    echo "<select id='size".$r02.$cp06."' name='size".$r02.$cp06."' style='width:120'>";
                }else{
                    echo "<select id='size".$r02.$cp06."' name='size".$r02.$cp06."' style='width:120;display:none' >";
                }
                
                for($j=0; $j<sizeof($arrSizeID[$arrCat2Cat1ProductCode[$l]]); $j++)
                {
                    $value = $arrSizeID[$arrCat2Cat1ProductCode[$l]][$j];
                    $text = $arrSizeLabel[$value];
                    if($countRow == 0){
                        echo "<option value='".$value."' selected>".$text."</option>";
                    }else{
                        echo "<option value='".$value."'>".$text."</option>";
                    }
                    $countRow++;
                }
                echo "</select>";
            }
            echo "</td>";
            
            
            echo '<td valign="top"><input type="date" id="manufacturingDate'.$r02.'" name="manufacturingDate'.$r02.'" width="150" maxlength="8" value=""></td>';
            echo '<td valign="top"><input type="number" min="0" id="quantity'.$r02.'" name="quantity'.$r02.'" width="150" pattern="^[0-9]" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57"></td>';
            
            
            echo "</tr>";
        }
    }
    
    echo "</table>";
    echo '<td valign="top"><input type="hidden" id="sizeArrCat2Cat1ProductCode" value="'.$sizeArrCat2Cat1ProductCode.'"></td>';
    echo '<td valign="top"><input type="hidden" id="strCat2Cat1ProductCode" value="'.$strCat2Cat1ProductCode.'"></td>';
    // Close connections
    mysqli_close($con);
    ?>
<tr><td>
<input type="submit" value="Create QR Code File" name="create" >
</td></tr>
</table>
</form>
</body>
</html>

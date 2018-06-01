<?php
    /** Include path **/
    ini_set('include_path', ini_get('include_path').';./../../Classes/');
    
    /** PHPExcel */
    include './../PHPExcel.php';
    
    /** PHPExcel_Writer_Excel2007 */
    include './../PHPExcel/Writer/Excel2007.php';
    
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    if(isset($_POST["periodFrom"]) &&
       isset($_POST["periodTo"]) &&
       isset($_POST["eventID"])
       )
    {
        $periodFrom = $_POST["periodFrom"];
        $periodTo = $_POST["periodTo"];
        $eventID = $_POST["eventID"];
    }
    else
    {
        $periodFrom = "2016-07-08";
        $periodTo = "2016-07-09";
        $eventID = "120";
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    if($eventID == '')//all event
    {
        $sql = "select receipt.receiptdate as Date, event.Location, event.PeriodFrom, event.PeriodTo, receiptProductItem.PriceSales as Amount,case receiptitemproducttype.ProductType when 'C' THEN 'Custom Made' ELSE 'Inventory' end as ProductType, productname.Name as Style,productalltypes.Toe, productalltypes.Body, productalltypes.Accessory, productalltypes.Remark, concat(`FirstName`) as Name, concat(`Street1`,' ', `Postcode`,' ', `Country`) as Address, `Telephone`, LineID,case when productalltypes.ProductType = 'I' COLLATE utf8_general_ci and productname.code = '00' then custommade.body when productalltypes.ProductType = 'C' COLLATE utf8_general_ci THEN productalltypes.Color ELSE color.Name end as Color,case when productalltypes.ProductType = 'I' COLLATE utf8_general_ci and productname.code = '00' then custommade.size when productalltypes.ProductType = 'C' COLLATE utf8_general_ci THEN productalltypes.Size ELSE productsize.SizeLabel end as Size from receiptproductitem LEFT JOIN receiptitemproducttype ON receiptproductitem.ReceiptProductItemID=receiptitemproducttype.ReceiptProductItemID LEFT JOIN receipt on receiptproductitem.ReceiptID = receipt.ReceiptID left join productalltypes on lpad(receiptproductitem.ProductID,6,'0') = productalltypes.ID and receiptitemproducttype.ProductType = productalltypes.ProductType LEFT JOIN event ON receipt.EventID = event.EventID left join productname on productalltypes.ProductCategory2 = productname.ProductCategory2 AND productalltypes.ProductCategory1 = productname.ProductCategory1 AND productalltypes.ProductName = productname.Code LEFT JOIN color ON productalltypes.Color = color.Code LEFT JOIN customerreceipt ON receipt.ReceiptID = customerreceipt.ReceiptID LEFT JOIN postcustomer ON customerreceipt.PostCustomerID = postcustomer.PostCustomerID LEFT JOIN productsize ON productalltypes.Size = productsize.Code LEFT JOIN custommade ON receiptProductItem.productid = custommade.productidpost where receipt.receiptdate between '$periodFrom' and '$periodTo'";
    }
    else//selected event
    {
        $sql = "select receipt.receiptdate as Date, event.Location, event.PeriodFrom, event.PeriodTo, receiptProductItem.PriceSales as Amount,case receiptitemproducttype.ProductType when 'C' THEN 'Custom Made' when 'B' THEN 'Custom Made' ELSE 'Inventory' end as ProductType, productname.Name as Style, case when productalltypes.ProductType = 'I' COLLATE utf8_general_ci and productname.code = '00' then custommade.body when productalltypes.ProductType = 'C' COLLATE utf8_general_ci THEN productalltypes.Color ELSE color.Name end as Color, case when productalltypes.ProductType = 'I' COLLATE utf8_general_ci and productname.code = '00' then custommade.size when productalltypes.ProductType = 'C' COLLATE utf8_general_ci THEN productalltypes.Size ELSE productsize.SizeLabel end as Size,productalltypes.Toe, productalltypes.Body, productalltypes.Accessory, productalltypes.Remark, concat(`FirstName`) as Name, concat(`Street1`,' ', `Postcode`,' ', `Country`) as Address, `Telephone`, LineID from receiptproductitem LEFT JOIN receiptitemproducttype ON receiptproductitem.ReceiptProductItemID=receiptitemproducttype.ReceiptProductItemID LEFT JOIN receipt on receiptproductitem.ReceiptID = receipt.ReceiptID left join productalltypes on lpad(receiptproductitem.ProductID,6,'0') = productalltypes.ID and receiptitemproducttype.ProductType = productalltypes.ProductType LEFT JOIN event ON receipt.EventID = event.EventID left join productname on productalltypes.ProductCategory2 = productname.ProductCategory2 AND productalltypes.ProductCategory1 = productname.ProductCategory1 AND productalltypes.ProductName = productname.Code LEFT JOIN color ON productalltypes.Color = color.Code LEFT JOIN customerreceipt ON receipt.ReceiptID = customerreceipt.ReceiptID LEFT JOIN postcustomer ON customerreceipt.PostCustomerID = postcustomer.PostCustomerID LEFT JOIN productsize ON productalltypes.Size = productsize.Code LEFT JOIN custommade ON receiptProductItem.productid = custommade.productidpost where receipt.EventID = $eventID and receipt.receiptdate between '$periodFrom' and '$periodTo'";
    }
    
    
    writeToLog($sql);

    
    // Check if there are results
    if ($result = mysqli_query($con, $sql))
    {
        writeToLog("generate sales all events -> query success");
        // If so, then create a results array and a temporary one
        // to hold the data
        $resultArray = array();
        $tempArray = array();
        
        // Loop through each row in the result set
        while($row = $result->fetch_object())
        {
            // Add each row into our results array
            $tempArray = $row;
            array_push($resultArray, $tempArray);
        }
        
        // Finally, encode the array to JSON and output the results
        
        //success
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Thidaporn Kijkamjai");
        $objPHPExcel->getProperties()->setLastModifiedBy("Minimalist");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
        
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1','Date');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Event');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Period From');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Period To');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Amount (Baht)');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Product Type');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Style');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Color');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Size');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Toe');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Body');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Accessory');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'Remark');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'Name');
        $objPHPExcel->getActiveSheet()->SetCellValue('O1', 'Address');
        $objPHPExcel->getActiveSheet()->SetCellValue('P1', 'Telephone');
        $objPHPExcel->getActiveSheet()->SetCellValue('Q1', 'Line ID');
        
        //        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        
        $objPHPExcel->getActiveSheet()->setTitle('Report');
        for($j=2; $j<=sizeof($resultArray)+1; $j++)
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$j, $resultArray[$j-2]->Date);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$j, $resultArray[$j-2]->Location);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$j, $resultArray[$j-2]->PeriodFrom);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$j, $resultArray[$j-2]->PeriodTo);
            $priceValue = number_format($resultArray[$j-2]->Amount);
            $objPHPExcel->getActiveSheet()->getStyle('E'.$j)->getNumberFormat()->setFormatCode('###,###,###');
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$j, $priceValue, PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$j, $resultArray[$j-2]->ProductType);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$j, $resultArray[$j-2]->Style);
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$j, $resultArray[$j-2]->Color);
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$j, $resultArray[$j-2]->Size);
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$j, $resultArray[$j-2]->Toe);
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$j, $resultArray[$j-2]->Body);
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$j, $resultArray[$j-2]->Accessory);
            $objPHPExcel->getActiveSheet()->SetCellValue('M'.$j, $resultArray[$j-2]->Remark);
            $objPHPExcel->getActiveSheet()->SetCellValue('N'.$j, $resultArray[$j-2]->Name);
            $objPHPExcel->getActiveSheet()->SetCellValue('O'.$j, $resultArray[$j-2]->Address);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('P'.$j, $resultArray[$j-2]->Telephone, PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$j, $resultArray[$j-2]->LineID);
        }
        
        foreach(range('A','Q') as $columnID) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
            ->setAutoSize(true);
        }
        $fileName = 'Report_' . date('Y-m-d_His').'.xls';
        $filePath = './' . $_POST['dbName'] . '/SalesFiles/';
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }
        $filePath = $filePath . $fileName;
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($filePath);
        
        
        $response = array('status' => '1', 'fileName' => $fileName);
    }
    else
    {
        printf("error: %s\n", mysqli_error($con));
        $response = array('status' => 'generate sales fail');
    }
    
    // Close connections
    mysqli_close($con);
    
    
    echo json_encode($response);
    exit();
    ?>
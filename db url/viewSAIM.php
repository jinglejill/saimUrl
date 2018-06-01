CREATE ALGORITHM=UNDEFINED DEFINER=`localhost`@`%` SQL SECURITY DEFINER VIEW `compareinventoryhistory15`  AS  select `compareinventoryhistory`.`CompareInventoryHistoryID` AS `CompareInventoryHistoryID`,`compareinventoryhistory`.`EventID` AS `EventID`,`compareinventoryhistory`.`ModifiedDate` AS `ModifiedDate` from `compareinventoryhistory` order by `compareinventoryhistory`.`CompareInventoryHistoryID` desc limit 8 ;



CREATE ALGORITHM=UNDEFINED DEFINER=`localhost`@`%` SQL SECURITY DEFINER VIEW `receiptproductitem15days`  AS  select `receiptproductitem`.`ReceiptProductItemID` AS `ReceiptProductItemID`,`receiptproductitem`.`ReceiptID` AS `ReceiptID`,`receiptproductitem`.`ProductType` AS `ProductType`,`receiptproductitem`.`ProductID` AS `ProductID`,`receiptproductitem`.`PriceSales` AS `PriceSales`,`receiptproductitem`.`CustomMadeIn` AS `CustomMadeIn`,`receiptproductitem`.`ModifiedDate` AS `ModifiedDate` from `receiptproductitem` where (`receiptproductitem`.`ModifiedDate` >= (select concat(year((now() - interval 8 day)),'-',lpad(month((now() - interval 8 day)),2,'0'),'-01 00:00:00'))) ;



CREATE ALGORITHM=UNDEFINED DEFINER=`localhost`@`%` SQL SECURITY DEFINER VIEW `productalltypes`  AS  select lpad(cast(`custommade`.`CustomMadeID` as char(6) charset utf8),6,'0') AS `ID`,`custommade`.`ProductCategory2` AS `ProductCategory2`,`custommade`.`ProductCategory1` AS `ProductCategory1`,`custommade`.`ProductName` AS `ProductName`,`custommade`.`Size` AS `Size`,`custommade`.`Body` AS `Color`,`custommade`.`Toe` AS `Toe`,`custommade`.`Body` AS `Body`,`custommade`.`Accessory` AS `Accessory`,`custommade`.`Remark` AS `Remark`,'C' AS `ProductType` from `custommade` union select cast(`product`.`ProductID` as char(6) charset utf8) AS `CONVERT(product.ProductID,char(6))`,`product`.`ProductCategory2` AS `ProductCategory2`,`product`.`ProductCategory1` AS `productcategory1`,`product`.`ProductName` AS `ProductName`,`product`.`Size` AS `Size`,`product`.`Color` AS `Color`,'' AS `Toe`,'' AS `Body`,'' AS `Accessory`,'' AS `Remark`,'I' AS `ProductType` from `product` ;




CREATE ALGORITHM=UNDEFINED DEFINER=`localhost`@`%` SQL SECURITY DEFINER VIEW `receiptitemproducttype`  AS  select `receiptproductitem`.`ReceiptProductItemID` AS `ReceiptProductItemID`,(case `receiptproductitem`.`ProductType` when 'I' then 'I' when 'A' then 'I' when 'P' then 'I' when 'D' then 'I' when 'S' then 'I' when 'R' then 'I' when 'E' then 'C' when 'F' then 'I' when 'C' then 'C' when 'B' then 'C' end) AS `PRODUCTTYPE` from `receiptproductitem` ;







``receiptproductitem15days``
-`receiptproductitem`
-`receipt
-product



`compareinventoryhistory15`
-`compareinventoryhistory`
-`compareinventory





future concern
-cashallocation
-`custommade`
-customerreceipt
-postcustomer
-productdelete
-productsales
-productcost
-eventcost
-event









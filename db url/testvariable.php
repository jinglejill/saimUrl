<?php
    include_once('dbConnect.php');
    setConnectionValue('MINIMALIST_TEST');

    $paramBody = array(
                       'badge' => 1
                       );
    sendPushNotification('e2534a5610db439126f9d2d62c150e66897521bb3c7c19adfa106ccadc806140', $paramBody);
    
    
    
    
    $paramBody = array(
                       'badge' => 0
                       );
    sendPushNotification('e2534a5610db439126f9d2d62c150e66897521bb3c7c19adfa106ccadc806140', $paramBody);
    

    
//    $customMadeID[$i] = 123;
//    $j = 1;
//    
//    $sql = "INSERT INTO `custommade`(`CustomMadeID`, `ProductCategory2`, `ProductCategory1`, `ProductName`, `Size`, `Toe`, `Body`, `Accessory`, `Remark`, `ProductIDPost`) VALUES ($customMadeID[$i])+$j})";
//    echo $sql;
    
    
    
//    echo mysqli_affected_rows($con);
    
?>

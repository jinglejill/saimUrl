<?php
    include_once('./dbConnect.php');
//    setConnectionValue($_POST['dbName']);
    setConnectionValue('MINIMALISTNEW');
    ini_set("memory_limit","80M");
    writeToLog("file: " . basename(__FILE__));
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    //check expired date
    $sql = "SELECT * FROM Setting where enumKey = 'expiredDate'";
    $selectedRow = getSelectedRow($sql);
    $expiredDate = date('Y-m-d H:i:s',strtotime($selectedRow[0]["Value"]));
    $currentDate = date('Y-m-d H:i:s');
    
    if($currentDate >= $expiredDate)
    {
        $arrOfTableArray = array();
        echo json_encode($arrOfTableArray);
        mysqli_close($con);
        exit();
    }
    
    

    $sql = "SELECT 'ส่งตาม';";
    
    
    
    
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
        
        echo json_encode($arrOfTableArray,JSON_UNESCAPED_UNICODE);
    }

    
    // Close connections
    mysqli_close($con);
    ?>

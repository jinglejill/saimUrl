<?php
    include_once('dbConnect.php');
//    setConnectionValue("MINIMALIST_TEST");
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    {
        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        
        
        //update countnotseen = 0 ไม่ต้อง push เพราะใน app ไม่มีเรียกใช้ column countNotSeen
        //query statement
        $sql = "update useraccount set countnotseen = 0 where username = '" . $_POST["modifiedUser"] . "'";
        $res = mysqli_query($con,$sql);
        if(!$res)
        {
            $error = "query fail, sql: " . $sql . ", modified user: " . $user . " error: " . mysqli_error($con);
            writeToLog($error);
            $response = array('status' => $error);
            continue;
        }
        else
        {
            writeToLog("query success, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"]);
            $response = array('status' => '1', 'sql' => $sql);
            
        }
    }
    if($error != "")
    {
        putAlertToDevice($_POST["modifiedUser"]);
    }


    mysqli_close($con);
    echo json_encode($response);
    exit();
?>

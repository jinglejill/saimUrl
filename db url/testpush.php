<?php
    include_once('dbConnect.php');
    setConnectionValue("DOROTA");
    writeToLog("testpush.php");
    $arrBody = array(
                      'alert' => 'test jill push'//ข้อความ
                      ,'sound' => 'default'//,//เสียงแจ้งเตือน
//                      ,'badge' => 3 //ขึ้นข้อความตัวเลขที่ไม่ได้อ่าน
                      );
    sendTestApplePushNotification('af23c2d1861c7dd22e4e909fcef283e2e927cca486b497930f0c680211991e1a',$arrBody);
?>

//<table><tr><td style="text-align: center;border: 1px solid black; padding-left: 10px;padding-right: 10px; border-radius: 15px;">x</td></tr></table>

<?php
    require 'phpqrcode/qrlib.php';
    
//    QRcode::png('code data text', 'filename.png'); // creates file
    QRcode::png('SAIM Minimalist\n010107023820150901000391\nEnd'); // creates code image and outputs it directly into browser

?>
<?php

// Put your device token here (without spaces):
$deviceToken = '57004ba00d4d633cfd1671e3c59b4fa480a076a233f54982cf18041b01310606';//jill saim
$deviceToken = 'ab2e1e008147073b73e88e1211ae1f4bbb3e52e61e27fae9a149f7f4083fb068';//iphone black saim_test
$deviceToken = '7ee39fe1bc51538830ece5494973be0f23b4ec9f17a27ddb63824cdbb8f21240';//jill saim_test
//$deviceToken = '6541f69cd948f244772e3ac55bdf1ccf6376517b4d57b16c2f92317449fe6c55';//ipad jill saim_test
// Put your private key's passphrase here:
$passphrase = 'pushchat';

////////////////////////////////////////////////////////////////////////////////

$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// Open a connection to the APNS server
$fp = stream_socket_client(
  'ssl://gateway.sandbox.push.apple.com:2195', $err,
  $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

if (!$fp)
  exit("Failed to connect: $err $errstr" . PHP_EOL);

echo 'Connected to APNS' . PHP_EOL;

// Create the payload body
    $body['aps'] = array(
                         'alert' => 'jill test',
                         'sound' => 'default',
//                         'link_url' => $url,
                         );
//$body['aps'] = array(
//  'content-available' => '1',
//  );

// Encode the payload as JSON
$payload = json_encode($body);

// Build the binary notification
$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// Send it to the server
$result = fwrite($fp, $msg, strlen($msg));

if (!$result)
  echo 'Message not delivered' . PHP_EOL;
else
  echo 'Message successfully delivered' . PHP_EOL;

// Close the connection to the server
fclose($fp);

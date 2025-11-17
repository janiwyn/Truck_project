<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);

use model\Auth\Environment;
include_once "include/autoloader.php";
include_once "include/functions.php";
(new Environment('.env'))->load();
 $request=get_request_name(getenv('URI_DEPTH'));
//  echo"<pre>";
//  print_r($request);
//  exit;
ob_start();  // Start output buffering
include_once "sys/route_capture.php";
include_once "strict.php";
include_once 'App.php'; 
include_once "sys/route_clock.php";
$output = ob_get_contents();  // Get the contents of the output buffer
ob_end_clean();  // Clean (erase) the output buffer and turn off output buffering

if (empty($output)) {
    include_once "api/404.php";
}else{
    echo $output;
}
?>
<?php

use model\Logs\Log;
use sys\__model\RouteAccess;

/**
 * Include routes that can never be restricted to avoid gettig locked out
 */
$exceptions=[
    "login",
];
if(!in_array($request, $exceptions)):

    $NewAccess = new RouteAccess;
    //First check if token is provided
    if($NewAccess->__getBearerToken()){
        $NewAccess->__set_system_user("Checking route restriction: " . $request);
        if(!$NewAccess->__restrict_user($request)):

            terminate($NewAccess->Error);
    
        endif;
    }else{
        http_response_code(200);
    }

endif;


/**
 * RESTRICT ACCESS: 
 * USED FOR EMERGENCE PURPOSES ONLY - EG. DURING SYSTEM UPDATE
 * Allowed IPs are included in the .env file variable WHITE_IPS, these shall be comma delimited
 * If WHITE_IPS list is not empty the system access is automatically restricted to that particular list, all requests from other IPs shall be dropped.
 */

 $white_ip_list = getenv("WHITE_IPS");
 if($white_ip_list):
    $NewLog = new Log;
    $NewLog->__log_custom_file("White list access from " . $white_ip_list, "white_access.log");
    //convert to array
    $ip_array = explode(",", $white_ip_list);
    $requesting_ip = getIPAddress();
    if(!in_array($requesting_ip, $ip_array)):
        $error = "System maintenance is still in progress. Please try again later.";
        $info = array(
            'status' => "Fail",
            'message'=> $error,
            'details' => [
                "message" => $error,
                "content" => $requesting_ip
            ]
        );
        $NewLog->__log_custom_file("Denying access: " . $requesting_ip, "white_access.log");

        print_r(json_encode($info));
        exit;
    endif;
    $NewLog->__log_custom_file("Allowing access: " . $requesting_ip, "white_access.log");

 endif;
?>
<?php

switch($request):
           
    case "login"://login route
        include_once "api/login.php";//Login Endpoint
        break;
     case "user/role/list"://user role route
        include_once "api/list_roles.php";//User role endpoint
        break;
    
     case "verify_otp":
        include_once "api/verify_otp.php";//User otp endpoint
        break;
     case "resend_otp":
        include_once "api/resend_otp.php";//User role endpoint
        break;
      case "create_user":
        include_once "api/create_user.php";
        break;
      case "view_profile":
        include_once "api/view_pofile.php";
        break;
       case "update_profile":
        include_once "api/update_profile.php";
        break;
      case "update_photo":
        include_once "api/update_photo.php";
        break;
endswitch;

?>
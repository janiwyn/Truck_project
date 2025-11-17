<?php

switch($request):
           
    case "create_notification":
        include_once "api/Notification/create_notification.php";
        break;

     case "get_user_notification":
        include_once "api/Notification/get_user_notification.php";
        break;
    case "mark_as_read":
        include_once "api/Notification/mark_as_read.php";
        break;
endswitch;

?>
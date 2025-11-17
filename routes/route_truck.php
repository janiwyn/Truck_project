<?php

switch($request):
           
    case "create_truck":
        include_once "api/Truck/create_truck.php";
        break;

     case "list_truck":
        include_once "api/Truck/list_truck.php";
        break;
    case "list_driver_truck":
        include_once "api/Truck/list_driver_truck.php";
        break;
endswitch;

?>
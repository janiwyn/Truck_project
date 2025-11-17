<?php

switch($request):
           
    case "create_booking":
        include_once "api/Booking/create_booking.php";
        break;

     case "list_booking":
        include_once "api/Booking/list_booking.php";
        break;

    case "calculate_price":
        include_once "api/Booking/calculate_price.php";
        break;

     case "select_truck":
        include_once "api/Booking/select_truck.php";
        break;

    case "user_history":
        include_once "api/Booking/user_history.php";
        break;
        
    case "select_place":
        include_once "api/Booking/search_place.php";
        break;
endswitch;

?>
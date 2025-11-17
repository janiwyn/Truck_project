<?php

use model\Booking\booking;


require_api_headers();
$data = json_decode(file_get_contents("php://input"));

$NewRequest = new Booking;
$NewRequest->__set_system_user("Listing all bookings");
$result = $NewRequest->__list_bookings();
$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

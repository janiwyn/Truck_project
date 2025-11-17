<?php

use model\Booking\booking;

require_api_headers();
$data=json_decode(file_get_contents("php://input"));
require_api_data($data, ['user_id', 'driver_id', 'pickup_text', 'dropoff_text', 'truck_type']);


$NewRequest=new Booking;
$result=$NewRequest->__create_booking(
    clean($data->user_id),
     clean($data->driver_id),
     clean($data->pickup_text),
     clean($data->dropoff_text),
     clean($data->truck_type)
    );

$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

?>
<?php

use model\Booking\booking;

require_api_headers();
$data=json_decode(file_get_contents("php://input"));
require_api_data($data, ['truck_type', 'distance_km']);


$NewRequest=new Booking;
$result=$NewRequest->__calculate_price(
    clean($data->truck_type),
     clean($data->distance_km)
    );

$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

?>
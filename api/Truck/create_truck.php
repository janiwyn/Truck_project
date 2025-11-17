<?php

use model\Truck\truck;

require_api_headers();
$data=json_decode(file_get_contents("php://input"));
require_api_data($data, ['driver_id', 'truck_name', 'truck_number_plate','capacity_tons', 'permit_number','license_image', 'permit_image', 'status']);


$NewRequest=new Truck;
$result=$NewRequest->__create_truck(
     clean($data->driver_id),
     clean($data->truck_name),
     clean($data->truck_number_plate),
     clean($data->capacity_tons),
     clean($data->permit_number),
     clean($data->license_image),
     clean($data->permit_image),
     clean($data->status)

    );

$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

?>
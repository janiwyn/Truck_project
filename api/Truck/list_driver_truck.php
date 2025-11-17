<?php

use model\Truck\truck;


require_api_headers();
$data = json_decode(file_get_contents("php://input"));

$NewRequest = new Truck;
$NewRequest->__set_system_user("Listing all trucks");
$result = $NewRequest->__list_trucks();
$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

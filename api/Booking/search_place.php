<?php

use model\Booking\booking;

require_api_headers();
$data=json_decode(file_get_contents("php://input"));
require_api_data($data, ['query']);


$NewRequest=new Booking;
$result=$NewRequest->__search_place(
    clean($data->query),
    );

$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

?>
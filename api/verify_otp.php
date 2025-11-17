<?php

use model\User\User;

require_api_headers();
$data=json_decode(file_get_contents("php://input"));
require_api_data($data, ['username', 'otp']);


$NewRequest=new User;
$result=$NewRequest->__verify_otp(clean($data->username), clean($data->otp));

$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

?>
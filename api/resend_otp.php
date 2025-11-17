<?php

use model\User\User;

require_api_headers();
$data=json_decode(file_get_contents("php://input"));
require_api_data($data, ['username']);


$NewRequest=new User;
$result=$NewRequest->__resend_otp(clean($data->username));

$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

?>
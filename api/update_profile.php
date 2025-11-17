<?php

use model\User\User;

require_api_headers();
$data=json_decode(file_get_contents("php://input"));
require_api_data($data, ['user_id', 'first_name', 'last_name', 'username', 'email', 'phone']);


$NewRequest=new User;
$result=$NewRequest->__update_profile(
    clean($data->user_id),
     clean($data->first_name),
     clean($data->last_name),
     clean($data->username),
     clean($data->email), 
     clean($data->phone)
    );

$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

?>
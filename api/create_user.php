<?php

use model\User\User;

require_api_headers();
$data=json_decode(file_get_contents("php://input"));
require_api_data($data, ['first_name', 'last_name', 'username','email', 'phone', 'password', 'role_id']);


$NewRequest=new User;
$result=$NewRequest->__create(
    clean($data->first_name),
     clean($data->last_name),
      clean($data->username),
      clean($data->email),
      clean($data->phone),
       clean($data->password),
        clean($data->role_id)
     );

$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

?>
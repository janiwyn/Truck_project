<?php

use model\User\Role;

require_api_headers();
$data=json_decode(file_get_contents("php://input"));

$NewRequest=new Role;
$NewRequest->__set_system_user("Listing user roles");
$result=$NewRequest->__list_roles();

$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

?>
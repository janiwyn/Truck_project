<?php

use model\User\User;

require_api_headers();
$data=json_decode(file_get_contents("php://input"));
require_api_data($data, ['user_id', 'photo_path']);


$NewRequest=new User;
$result=$NewRequest->__update_photo(
    clean($data->user_id),
     clean($data->photo_path)
    );

$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

?>
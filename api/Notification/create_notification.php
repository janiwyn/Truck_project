<?php

use model\Notification\notification;

require_api_headers();
$data=json_decode(file_get_contents("php://input"));
require_api_data($data, ['user_id', 'title','message', 'type']);


$NewRequest=new Notification;
$result=$NewRequest->__create_notification(
    clean($data->user_id),
     clean($data->title),
      clean($data->message),
      clean($data->type)
     );

$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

?>
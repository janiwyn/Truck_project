<?php

use model\Notification\notification;

require_api_headers();
$data=json_decode(file_get_contents("php://input"));
require_api_data($data, ['notification_id']);


$NewRequest=new Notification;
$result=$NewRequest->__mark_as_read(
    clean($data->notification_id)
     );

$info = format_api_return_data($result, $NewRequest);

//make json
print_r(json_encode($info));

?>
<?php
    require_api_headers();
    $data=json_decode(file_get_contents("php://input"));
    
  
        $info=array(
            'status' => "Fail",
            'details' => array(
                "message"=>"Request not found",
                "content"=>""
                )
        );
    

    print_r(json_encode($info));



?>
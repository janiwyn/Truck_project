<?php
namespace sys\__model;
use model\App;
use sys\store\AppData;

/**
 * Performance measure
 * - Controls when the user should access a restricted route
 */
class RouteAccess extends App{

    private $TableName = "tbl_perform_route_access";


    public function __construct()
    {
        $this->__init();
    }


    public function __restrict_user($route){
        if($this->SystemUser<=0):
            $this->Error="System user not specified";
            return true;
        endif;

        //check if route is restricted
        $NewRoute = new RestrictedRoute;
        if(!$route_info = $NewRoute->__route_is_restricted($route)):
            $this->Error="Route not restricted";
            return true;
        endif;

        if($access_id = $this->__user_is_restricted($route_info['id'])):
            //Update
            return $this->__user_has_access($access_id, $route_info['interval']);
        else:
            //create
            return $this->__create_access($route_info['id']);
        endif;
    }



    private function __create_access($route_id){

        $data=[
            "route_id"=>$route_id,
            "access_token"=>gen_uuid(),
            "user_id"=>$this->SystemUser
        ];

        return AppData::__create($this->TableName, $data);
    }


    private function __update_access($access_id){

        $data=[
            "access_token"=>gen_uuid()
        ];

        return AppData::__update($this->TableName, $data, $access_id);
    }



    private function __user_is_restricted($route_id){

        $query="SELECT `$this->TableName`.`id` FROM `$this->TableName`";
        $query.=" WHERE `$this->TableName`.`route_id`='$route_id' AND `$this->TableName`.`user_id`='$this->SystemUser' LIMIT 1";
        $result = AppData::__execute($query);
        if($result->num_rows == 0):
            $this->Error="User is not restricted route";
            return false;
        endif;

        return $result->fetch_object()->id;

    }

    private function __user_has_access(int $access_id, int $route_interval){
        
        $query="SELECT " . sql_seconds_timer("updated_at");
        $query.=" FROM `$this->TableName` WHERE `id`='$access_id' LIMIT 1";
        $result = AppData::__execute($query);
        if($result->num_rows==0):
            $this->Error="Access Id not found";
            return false;
        endif;

        if($result->fetch_object()->timer < $route_interval):
            $this->Error="User not allowed at this time. Please try again in a bit";
            return false;
        endif;

        return $this->__update_access($access_id);
    }


    private function __init()
    {
      if (!AppData::__table_exists($this->TableName)):
        new RestrictedRoute;
        $query = "CREATE TABLE `$this->TableName` (";
        $query .= "`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
        $query .= "`route_id` INT(11) NOT NULL,";
        $query .= "`user_id` INT(11) NOT NULL,";
        $query .= "`access_token` VARCHAR(50) NOT NULL,";//In seconds
        $query .= "`created_at` timestamp NOT NULL DEFAULT current_timestamp(),";
        $query .= "`updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),";
        $query .= " CONSTRAINT FK_route_has_restriction FOREIGN KEY(`route_id`) REFERENCES `tbl_perform_restricted_route`(`id`),";
        $query .= " CONSTRAINT FK_user_has_restriction FOREIGN KEY(`user_id`) REFERENCES `tbl_user`(`id`)";
        $query .= ") ENGINE=InnoDB";
        AppData::__execute($query);
      endif;
    }

}

?>
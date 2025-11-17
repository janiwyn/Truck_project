<?php
namespace sys\__model;
use model\App;
use sys\store\AppData;

/**
 * Performance measure
 * - Stores routes with restricted access for performance reasons
 */
class RestrictedRoute extends App{

    private $TableName = "tbl_perform_restricted_route";


    public function __construct()
    {
        $this->__init();
    }


    /**
     * @route_name e.g. user/total
     * @interval: For how long in seconds
     */
    public function __restrict_route(string $route_name, int $interval){

        if($this->__route_is_restricted($route_name)):
            $this->Error="Route is already restricted";
            return false;
        endif;

        $data = [
            "route"=>$route_name,
            "access_interval"=>$interval
        ];

        if(AppData::__create($this->TableName, $data)):
            $this->Success="Route successfully restricted!";
            return true;
        endif;

        $this->Error="Something went wrong restricting route";
        return false;
    }


    public function __route_is_restricted(string $route_name){

        if(!$objR = AppData::__get_row($this->TableName, $route_name, "route")):
            return false;
        endif;

        return $this->__std_data_format($objR);
    }


    private function __std_data_format($data){

        $data = (object) $data;

        return [
            "id"=>$data->id,
            "route"=>$data->route,
            "interval"=>$data->access_interval
        ];
    }


    private function __init()
    {
      if (!AppData::__table_exists($this->TableName)):
        $query = "CREATE TABLE `$this->TableName` (";
        $query .= "`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
        $query .= "`route` VARCHAR(255) NOT NULL UNIQUE,";
        $query .= "`access_interval` INT(11) NOT NULL,";//In seconds
        $query .= "`created_at` timestamp NOT NULL DEFAULT current_timestamp(),";
        $query .= "`updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()";
        $query .= ") ENGINE=InnoDB";
        AppData::__execute($query);
      endif;
    }

}

?>
<?php
namespace model\Truck;

use model\App;
use sys\store\AppData;

class truck extends App
{

    private $TableName = "tbl_trucks";
    private bool $UserIsActivated = false;

    public function __construct()
    {
        parent::__construct();
        $this->__initialize();
    }
  
 public function __create_truck($driver_id,$truck_name, $truck_number_plate, $capacity_tons, $permit_number, $license_image,$permit_image,$status = 'available'){
    $data = [
        'driver_id' => $driver_id,
        'truck_name' => $truck_name,
        'truck_number_plate' => $truck_number_plate,
        'capacity_tons' => $capacity_tons,
        'permit_number' => $permit_number,
        'license_image' => $license_image,
        'permit_image' => $permit_image,
        'status' => $status
    ];
    if($result = AppData::__create($this->TableName, $data)) :
        $this->Success = "Truck added successfully";
        return $result;
    endif;
    $this->Error = "Failed to add truck";
    return false;
 }

 public function __list_trucks(){
    $sql = "SELECT * FROM `this->TableName` WHERE id > 0";
    $res = AppData::__execute($sql);

    if($res->num_rows == 0) :
        $this->Error = "No trucks found";
        return false;
    endif;

    $list = [];
    while($row = $res->fetch_assoc()) :
        $list[] = $row;
    endwhile;
    return $list;
 }

 public function __list_driver_trucks($driver_id){
    $sql = "SELECT * FROM `this->TableName` WHERE driver_id = '$driver_id' ORDER BY created_at DESC";
    $res = AppData::__execute($sql);

    if($res->num_rows == 0) :
        $this->Error = "This driver has no registered trucks";
        return false;
    endif;

    $list = [];
    while($row=$res->fetch_assoc()) :
        $list[] = $row;
    endwhile;

    return $list;

 }

 
 

 
    private function __initialize()
    {
        if (!AppData::__table_exists($this->TableName)) {
            $query = "CREATE TABLE `$this->TableName` (";
            $query .= "`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
            $query .= "`driver_id` INT(11) NULL DEFAULT NULL,";
            $query .= "`truck_name` VARCHAR(255) NOT NULL,";
            $query .= "`truck_number_plate` VARCHAR(255) NOT NULL UNIQUE,";
            $query .= "`capacity_tons` DECIMAL(5,2) NOT NULL,";
            $query .= "`permit_number` VARCHAR(255) NOT NULL,";
            $query .= "`license_image` VARCHAR(255) NOT NULL,";
            $query .= "`permit_image` VARCHAR(255) NOT NULL,";
            $query .= "`status` ENUM('available','on_trip','maintenance','inactive') DEFAULT 'available',";
            $query .= " `created_at` timestamp NOT NULL DEFAULT current_timestamp(),";
            $query .= " `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),";
            $query .= "CONSTRAINT FOREIGN KEY (`driver_id`) REFERENCES `tbl_user`(`id`)";
            $query .= ") ENGINE=InnoDB";
            AppData::__execute($query);


            //create default user
             $this->__create_truck("1", "Isuzu", "UA345C", "60", "67ou67", "avatar2", "yu7655", "available");
        }
    }



}

?>
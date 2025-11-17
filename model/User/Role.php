<?php
namespace model\User;

use model\App;
use sys\store\AppData;
use sys\store\DataStore;

class Role extends App
{

    
    private $TableName="tbl_role";


    public function __construct() {
        $this->__initialize();
        parent::__construct();
    }


    public function __create_role($role_name)
    {
        
        #check for name
        if(AppData::__get_row($this->TableName, $role_name, "role_name")):
       
            $this->Error = "Sorry! Role name already exists";
            return false;
        endif;


        $data = array(
            "role_name" => $role_name
        );

        $created = AppData::__create($this->TableName, $data);
        if ($created) {
            $this->Success = "New role has been created successfully!";
            return true;
        }

        $this->Error = "Failed to create a role";
        return false;
    }


    public function __get_role_info($role_id)
    {
       
        $objRole = AppData::__get_row($this->TableName, $role_id);
        if(!$objRole):
            $this->Error = "Sorry! This role does not exist";
            return false;
        endif;
       
       return $this->__std_data_format($objRole);
    }



    public function __list_roles()
    {
        $query = "SELECT*FROM `tbl_role`";
        $result = AppData::__execute($query);
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = $this->__std_data_format($row);
        }
        return $list;

        
    }




    public function __update_role()
    {
        
    }


    public function __delete_role()
    {
        
    }



    private function __std_data_format($data)
    {
        $data = (object) $data;
        return[
            "role_id" => $data->id,
            "role_name" => $data->role_name,
            "created_at" => date_formats($data->created_at),
            "status" => $data->status
        ];

    }


    private function __initialize()
    {

        if (!AppData::__table_exists($this->TableName)):
            $query = "CREATE TABLE `$this->TableName` (";
            $query .= "`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
            $query .= "`role_name` VARCHAR(255) NOT NULL DEFAULT 'NULL',";
            $query .= "`status` INT(1) NOT NULL DEFAULT 1,";
            $query .= "`created_at` timestamp NOT NULL DEFAULT current_timestamp(),";
            $query .= "`updated_at` timestamp NOT NULL DEFAULT current_timestamp()";
            $query .= ") ENGINE=InnoDB";
            AppData::__execute($query);

            $this->__create_role("User");
            $this->__create_role("Driver");
        
        endif;
    }
    
}

?>

<?php 
namespace model\Logs;

use model\App;
use model\User\User;
use sys\store\AppData;

class OnlineLog extends App
{

    private $TableName="tbl_online_log";
    private $OnlineId;
    private $LastSeen;

    public function __construct()
    {
        parent::__construct();
        $this->__init();
    }
    
    public function __log_online($device=null)
    {
        $user_id = $this->SystemUser;

        $NewUser = new User;
        if(!$NewUser->__get_user_info($user_id)):
            $this->Error="SUSP001";
            return false;
        endif;
        if($NewUser->IsActive*1<=0):
            $this->Error="SUSP001";
            return false;
        endif;

        if($this->__get_user_record($user_id)):

            $data=array(
                "access_code"=>gen_uuid(),
                "current_device"=>$device
            );

            if(mysqli_update($this->TableName, $data, $this->OnlineId)):
                $this->Success="User online record updated successfully!";
                return true;
            endif;
            $this->Error="Failed to update user online record";
            return false;
            
        else:
            if($this->__create($user_id, $device)):
                $this->Success="User online record initiated";
                return true;
            endif;
            $this->Error="Something went wrong creating user online record";
            return false;
        endif;
    }


    public function __user_is_online($user_id)
    {
        $query="SELECT `id`, TIMESTAMPDIFF(MINUTE, `updated_at`, NOW()) AS `timer`, `updated_at`";
        $query.=" FROM `$this->TableName` WHERE `user_id`='$user_id'";
        $query.=" AND TIMESTAMPDIFF(MINUTE, `updated_at`, NOW())<2";
        $result = AppData::__execute($query);
        if($result->num_rows==0):
            $this->Error="User is offline";
            return false;
        endif;
        $this->Success="User is online";
        return true;
    }


    public function __determine_last_seen($user_id)
    {
        $objOnline = AppData::__get_row($this->TableName, $user_id, "user_id");
        if(!$objOnline):
            $this->LastSeen = false;
            return $this;
        endif;

        $this->LastSeen = $objOnline->updated_at;
        return $this;

    }


    private function __get_user_record($user_id)
    {
        if(!$objResult = AppData::__get_row($this->TableName, $user_id, "user_id")):
            $this->Error="User has no online record";
            return false;
        endif;
        $this->OnlineId=$objResult->id;
        return [
            "id"=>$objResult->id,
            "user_id"=>$objResult->user_id,
            "access_code"=>$objResult->access_code,
            "current_device"=>$objResult->current_device,
            "created_at"=>date_formats($objResult->created_at),
            "updated_at"=>date_formats($objResult->updated_at)
        ];
    }


    private function __create($user_id, $device)
    {
        $data=[
            "user_id"=>$user_id,
            "access_code"=>gen_uuid(),
            "current_device"=>$device
        ];

        return AppData::__create($this->TableName, $data);
    }

    public function __get_last_seen()
    {
        return $this->LastSeen;
    }

    private function __init()
    {
      if (!AppData::__table_exists($this->TableName)):
        $query = "CREATE TABLE `$this->TableName` (";
        $query .= "`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
        $query .= "`user_id` INT(11) NOT NULL,";
        $query .= "`access_code` VARCHAR(255) NOT NULL,";
        $query .= "`current_device` VARCHAR(255) DEFAULT NULL,";
        $query .= "`lat` DECIMAL(10,6) DEFAULT NULL,";
        $query .= "`lng` DECIMAL(10,6) DEFAULT NULL,";
        $query .= "`created_at` timestamp NOT NULL DEFAULT current_timestamp(),";
        $query .= "`updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),";
        $query .=" CONSTRAINT FK_user_is_online FOREIGN KEY(`user_id`) REFERENCES `tbl_user`(`id`)";
        $query .= ") ENGINE=InnoDB";
        AppData::__execute($query);
      endif;
    }


   
    
}//ends class

    ?>
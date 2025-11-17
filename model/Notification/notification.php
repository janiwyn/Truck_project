<?php
namespace model\Notification;

use model\App;
use sys\store\AppData;

class notification extends App
{

    private $TableName = "tbl_notification";
    private bool $UserIsActivated = false;

    public function __construct()
    {
        parent::__construct();
        $this->__initialize();
    }
  
 public function __create_notification($user_id,$title, $message, $type){
    $data = [
        'user_id' => $user_id,
        'title' => $title,
        'message' => $message,
        'type' => $type
    ];
    if($result = AppData::__create($this->TableName, $data)) :
        $this->Success = "Notifications successfully";
        return $result;
    endif;
    $this->Error = "Notification failed";
    return false;
 }


 public function __get_user_notifications($user_id){
    $sql = "SELECT * FROM `this->TableName` WHERE user_id = '$user_id' ORDER BY created_at DESC";
    $res = AppData::__execute($sql);

    if($res->num_rows == 0) :
        $this->Error = "No notification found";
        return false;
    endif;

    $list = [];
    while($row=$res->fetch_assoc()) :
        $list[] = $row;
    endwhile;

    return $list;

 }
   public function __mark_as_read($notification_id)
    {
        $sql = "
            UPDATE $this->TableName 
            SET is_read = 1 
            WHERE id = '$notification_id'
        ";

        return AppData::__execute($sql);
    }

 
 

 
    private function __initialize()
    {
        if (!AppData::__table_exists($this->TableName)) {
            $query = "CREATE TABLE `$this->TableName` (";
            $query .= "`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
            $query .= "`user_id` INT(11) NULL DEFAULT NULL,";
            $query .= "`title` VARCHAR(255) NOT NULL,";
            $query .= "`message` TEXT NOT NULL,";
            $query .= "`type` VARCHAR(50) NOT NULL,";
            $query .= "`is_read` TINYINT(1) DEFAULT 0,";
            $query .= " `created_at` timestamp NOT NULL DEFAULT current_timestamp(),";
            $query .= " `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),";
            $query .= "CONSTRAINT FOREIGN KEY (`user_id`) REFERENCES `tbl_user`(`id`)";
            $query .= ") ENGINE=InnoDB";
            AppData::__execute($query);


            //create default user
             $this->__create_notification("1", "completed the trip", "Thanks very much for supporting", "completed");
        }
    }



}

?>
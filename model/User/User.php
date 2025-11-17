<?php
namespace model\User;

use model\Auth\JWT;
use sys\store\AppData;

class User extends Role
{

    private $TableName = "tbl_user";
    private bool $UserIsActivated = false;

    public function __construct()
    {
        parent::__construct();
        $this->__initialize();
    }

    public function __login($username, $password)
    {
            $password = $this->__secure($password);
            $result=AppData::__select_fields()->__from_table($this->TableName)->__where(["username"=>$username, "password"=>$password])->__fetch();
            if ($result->num_rows > 0) {

                $row = $result->fetch_assoc();
                extract($row);
                if($row['is_verified'] == 0){
                    $this->Error = "Please verify your account first using the OTP sent to you";
                    return false;
                }
                $payload = array(
                    "data" => array(
                        "iat" => time(),
                        "exp" => time() + (60),
                        "user_id" => $id,
                        "role_id" => $role_id,
                        "username" => $username,
                    )
                );

                $token = JWT::encode($payload, getenv('JWT_SECRET'));
                $this->Success = "Login successful";
                $this->__update_token($id, $token);
                return $token;
            }


            
            $this->Error = "Enter correct username and password";
            return false;
       
    }


    public function __create($first_name, $last_name, $username, $email,$phone, $password, $role_id)
    {
        $otp = rand(100000, 999999); // 6-digits OTP
        $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));
        $data=[
            "first_name"=>$first_name,
            "last_name"=>$last_name,
            "username"=>$username,
            "email"=>$email,
            "phone"=>$phone,
            "password"=>$this->__secure($password),
            "role_id"=>$role_id,
            "otp" => $otp,
            "otp_expires_at"=> $expires,
            "is_verified" => 0
        ];

        return AppData::__create($this->TableName, $data);
    }
    private function __send_otp($email, $otp){
        $subject = "Your Verfication Code";
        $message = "Your OTP code is $otp. It expires in 10 minutes.";
        $headers = "From: truck@gmail.com";

        mail($email, $subject, $message, $headers);
    }
    // function to verify OTP
    public function __verify_otp($username, $otp){
        $result = AppData::__select_fields()
        ->__from_table($this->TableName)
        ->__where(["username" => $username, "otp"=>$otp])
        ->__fetch();

        if($result->num_rows == 0){
            $this->Error = "Invalid OTP";
            return false;
        }
        $row = $result->fetch_assoc();
        // check if OTP expired
        if(strtotime($row['otp_expires_at']) < time()){
            $this->Error = "OTP expired";
            return false;
        }
        // mark user as verfied
        $data =[
            "is_verified" => 1,
            "otp"=> null,
            "otp_expires_at"=>null
        ];
        AppData::__update($this->TableName, $data,$row['id']);
        $this->Success = "Account verified successfully!";
        return true;
    }

    public function __resend_otp($username){
        $otp = rand(100000, 999999);
    $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $data = [
        "otp" => $otp,
        "otp_expires_at" => $expires
    ];

    AppData::__update($this->TableName, $data, ["username" => $username]);
    $this->__send_otp($username, $otp);

    $this->Success = "A new OTP has been sent to your email/phone.";
    return true;
    }

    // user profile 
      public function __view_profile($user_id)
    {
        $user = AppData::__get_row($this->TableName, $user_id);
        if (!$user) {
            $this->Error = "User not found.";
            return false;
        }

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'email' => $user->email ?? null,
            'phone' => $user->phone,
            'photo' => $user->photo,
            'role_id' => $user->role_id,
        ];
    }
// updating the profile 
public function __update_profile($user_id, $first_name, $last_name, $username, $email, $phone)
{
    // prepare the data explicitly
    $update_data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'username' => $username,
        'email' => $email,
        'phone'=> $phone
    ];

    $result = AppData::__update($this->TableName, $update_data, $user_id);
    if ($result) {
        $this->Success = "Profile updated successfully.";
        return true;
    }

    $this->Error = "Failed to update profile.";
    return false;
}
// upload aprofile photo
         public function __update_photo($user_id, $photo_path)
    {
        $data = ['photo' => $photo_path];
        $result = AppData::__update($this->TableName, $data, $user_id);
        if ($result) {
            $this->Success = "Photo updated successfully.";
            return true;
        }
        $this->Error = "Failed to update photo.";
        return false;
    }



    private function __secure($key)
    {
        return md5(sha1($key));
    }


    private function __update_token($user_id, $token)
    {
        $data = [
            "token" => $token
        ];

        return AppData::__update($this->TableName, $data, $user_id);
    }



    public function __get_user_token($user_id)
    {
        if (!$objUser = AppData::__get_row($this->TableName, $user_id)) :
            $this->Error = "User does not exist!";
            return false;
        endif;

        return $objUser->token;
    }


    public function __get_user_info($id)
    {
        if(!$objUser = AppData::__get_row($this->TableName, $id)):
            $this->Error="User not found";
            return false;
        endif;
        return $this->__std_data_format($objUser);
    }


    public function __user_is_activated()
    {
        return $this->UserIsActivated;
    }


    private function __std_data_format($data){
        $data = (object) $data;
        $this->UserIsActivated = $data->is_active*1==1?true:false;
        return [
            "id"=>$data->id,
            "first_name"=>$data->first_name,
            "last_name"=>$data->last_name,
            "username"=>$data->username,
            "role_id"=>$data->role_id
        ];
    }


    private function __initialize()
    {
        if (!AppData::__table_exists($this->TableName)) {
            $query = "CREATE TABLE `$this->TableName` (";
            $query .= "`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
            $query .= "`first_name` VARCHAR(255) NOT NULL,";
            $query .= "`last_name` VARCHAR(255) NOT NULL,";
            $query .= "`username` VARCHAR(255) NOT NULL,";
            $query .= "`email` VARCHAR(255) NOT NULL,";
            $query .= "`phone` INT(20) NOT NULL,";
            $query .= "`password` VARCHAR(255) NOT NULL DEFAULT 1234,";
            $query .= " `role_id` INT(11) NOT NULL,";
            $query .= "`photo` VARCHAR(255) NOT NULL DEFAULT 'avatar.png',";
            $query .= " `is_active` INT(1) NOT NULL DEFAULT 1,";
            $query .= " `is_secure` INT(1) NOT NULL DEFAULT 0,";
            $query .= "`token` VARCHAR(500) NULL,";
            $query .= "`otp` VARCHAR(500) NULL,";
            $query .= "`otp_expires_at` DATETIME NULL,";
            $query .= "`is_verified` BOOLEAN DEFAULT 0,";
            $query .= " `created_at` timestamp NOT NULL DEFAULT current_timestamp(),";
            $query .= " `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),";
            $query .= " CONSTRAINT `FK_user_has_role` FOREIGN KEY(`role_id`) REFERENCES `tbl_role`(`id`)";
            $query .= ") ENGINE=InnoDB";
            AppData::__execute($query);


            //create default user
           // $this->__create("System", "Admin", "root", "Admin", "1");
            //$this->__create("Sam", "Sith","Sam", "12345", "2");
           // $this->__create("John", "Sun","John", "123456", "3");
          $this->__create("Jones", "Soa","Jones","john@gmail.com", "07564368", "123456", "3");


        }
    }



}

?>
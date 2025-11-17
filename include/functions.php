<?php

use sys\store\AppData;

function requires_db_initialization()
{
    if(getenv('DATABASE_INIT')=="OFF"):
        return false;
    endif;
    return true;
}

function field_exists($tab, $field)
{
    if(!requires_db_initialization()):
        return true;
    endif;
    $db=Database::getInstance();
    $mysqli=$db->getConnection();
    $query="SHOW COLUMNS FROM `$tab` LIKE '$field'";
    $result=$mysqli->query($query);
    echo $mysqli->error;
    $exists=($result->num_rows)?true:false;
    return $exists;
}

function table_exists($table)
{

    if(!requires_db_initialization()):
        return true;
    endif;

	$db=Database::getInstance();
    $mysqli=$db->getConnection();
    // (new Environment('.env'))->load();//loads from index folder
	$query_check="SELECT*FROM information_schema.tables WHERE";
	$query_check.=" table_schema = '". getenv('DATABASE_NAME') ."' AND table_name = '$table'";
	$query_check.=" LIMIT 1;";
	$result_check=$mysqli->query($query_check);
	$num_check=$result_check->num_rows;
	$bol=$num_check>0 ? true : false;
	return $bol;
}


function table_constraint_exists($table, $constraint)
{
    if(!requires_db_initialization()):
        return;
    endif;

	$db=Database::getInstance();
    $mysqli=$db->getConnection();
    $query_check="SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME";
    $query_check.=" FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE";
    $query_check.=" WHERE REFERENCED_TABLE_SCHEMA IS NOT NULL AND TABLE_SCHEMA='" . getenv('DATABASE_NAME') . "'";
    $query_check.=" AND TABLE_NAME='$table' ";
    $query_check.=" AND CONSTRAINT_NAME='$constraint'";
    $result_check=$mysqli->query($query_check);
	$num_check=$result_check->num_rows;
	$bol=$num_check>0 ? true : false;
	return $bol;
}


function clean($string)
{
    $string = (string) $string;
    $db=Database::getInstance();
    $mysqli=$db->getConnection();
    $string=trim($string);
    $string=mysqli_real_escape_string($mysqli, $string);
    return $string;
}

function len($string)
{
    $string = (string) $string;
    $string=trim($string);
    if(strlen($string)>0)
    {
     return false; 
    }
    return true;
}

function capitalize($stg){
		
	return ucwords(strtolower($stg));

}


function initials($str) 
{
		$str=trim($str);
    $ret = "";
    foreach (explode(' ', $str) as $word)
	if(isset($word[0])){
        $ret .= strtoupper($word[0]);
 
	}
	return $ret;
}



function stroked_date_format($date){
    return date('m/d/Y', strtotime($date));
}

function picker_format($date){
	return date('d M Y', strtotime($date));
}

function db_date($date_db){
	$date_db=date('Y-m-d', strtotime($date_db));
	return $date_db;
}

function db_date_time($date_db){
	$date_db=date('Y-m-d H:i:s', strtotime($date_db));
	return $date_db;
}

function user_date($date_user){
	$date_user=date('D, d M Y', strtotime($date_user));
	if($date_user=="Thursday, 01-Jan-1970"){
		$date_user="Day-Month-Year";
	}
	return $date_user;
}

function redirect_to($new_page){
	header("Location: $new_page");
	return $new_page;
}


function cut_words($limit, $words)
{
    $paragraph=$words;
    $lim=$limit;
    $short_words=substr($paragraph, 0, $lim);
    
    if((strlen($short_words))<(strlen($paragraph))){
        $short_words=$short_words . "...";
    }else{
        $short_words=$short_words;
    }
    
   return $short_words;
}
  


function user_date_time($date_user_time){
	$date_user=date('d M Y', strtotime($date_user_time));
	$time_user=date('h:i a', strtotime($date_user_time));
	return $date_user . " at " . $time_user;
}

function short_date($date_user){
	$date_user=date('d M Y', strtotime($date_user));
	if($date_user=="Thursday, 01-Jan-1970"){
		$date_user="Day-Month-Year";
	}
	return $date_user;
}


function user_time($user_time){
	if($user_time=="00:00:00" || $user_time==NULL){ 
		return "--:--"; 
		}
	
	$time_user=date('h:i a', strtotime($user_time));
	return $time_user;
}


function tell_when($date_added){ 

    $query="SELECT TIMESTAMPDIFF(DAY, '$date_added', NOW()) AS `days`, CURDATE() AS `today`";
    $result=mysqli_work($query);
    $row=$result->fetch_assoc();
    $days=$row['days'];
    if($row['today']===db_date($date_added)){
        return "Today at " . user_time($date_added);
    }elseif($days<=1 && $days>-1){
        return "Yesterday at " . user_time($date_added);
    }elseif($days>1 && $days<=4){
        return $days . " days back at " . user_time($date_added);
    }else{
        return user_date_time($date_added);
    }

}


function get_time_difference($start, $end, $conv=0)
{
    $uts['start']      =    strtotime( $start );
    $uts['end']        =    strtotime( $end );
    if( $uts['start']!==-1 && $uts['end']!==-1 )
    {
        if( $uts['end'] >= $uts['start'] )
        {
            $diff    =    $uts['end'] - $uts['start'];
            if( $days=intval((floor($diff/86400))) )
                $diff = $diff % 86400;
            if( $hours=intval((floor($diff/3600))) )
                $diff = $diff % 3600;
            if( $minutes=intval((floor($diff/60))) )
                $diff = $diff % 60;
            $diff    =    intval( $diff ); 
				if($conv==0){
            return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
				}elseif($conv==10){
					$addition=($hours*60)+$minutes+$diff;
					return $addition;
				}
				else{
					$addition=($hours*60)+$minutes;
					return $addition;
				}
		}
        else
        {
            trigger_error( "Ending date/time is earlier than the start date/time", E_USER_WARNING );
        }
    }
    else
    {
        trigger_error( "Invalid date/time data detected", E_USER_WARNING );
    }
    return( false );
}


function today_plus_days($days){
    $NewDate=Date('y-m-d', strtotime("+".$days." days"));
    return $NewDate;
}//ends today_plus_days

function today_minus_days($days){
    $NewDate=Date('y-m-d', strtotime("-".$days." days"));
    return $NewDate;
}//ends today_plus_days

function date_plus_days($old_date, $days){
    $NewDate=Date($old_date, strtotime("+".$days." days"));
    return $NewDate;
}//ends today_plus_days

function vo_no($no, $length=6){
	if(is_numeric($no))
	{
		return str_pad($no, $length, 0, 0);
	}
	return $no;
	
}




function plural($word, $num, $new=0){
	
	if($num>1 || $num==0){
		$formed=$word . "s";
	}else{
	 	$formed=$word;
	}
		if($new==0){
			return $formed;
		}
		if($num>1){
			return $formed . " are ";
		}
		return $formed . " is ";
}

function alpha_num($len=5)
{
 
    $permitted_chars = '0123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
    $gene=substr(str_shuffle($permitted_chars), 0, $len);
    return $gene;

}

        function auth_code($len=6)
        {
        
            $permitted_chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
            $gene=substr(str_shuffle($permitted_chars), 0, $len);
            return $gene;

        }

        function auto_number($size=4)
        {
        
            $permitted_chars = '0123456789';
            $gene=substr(str_shuffle($permitted_chars), 0, $size);
            return $gene;

        }


        function mysqli_insert($table, $data)
        {  
            $db=Database::getInstance();
            $mysqli=$db->getConnection();
            #Get array keys
            $keys=array_keys($data);
            $values=[];
            $fields=[];
            foreach($keys as $key=>$field)
            {
                $fields[]="`".$field . "`";

                
                if($data[$field]!="NOW()")
                {
                    
                    $values[]=len($data[$field])?'NULL':"'".$data[$field] . "'";

                }else{
                    $values[]=len($data[$field])?'NULL':$data[$field];
                }
            }
            $query="INSERT INTO `$table`";
            $query.="(";
            $query.=implode(", ", $keys);
            $query.=")VALUES(";
            $query.=implode(", ", $values);
            $query.=")";
            $result=$mysqli->query($query);
            echo $mysqli->error;
            $id=$mysqli->insert_id;
            if($mysqli->affected_rows>0)
            {
                return $id;
            }
            return false;
        }

        function mysqli_update($table, $data, $id)
        { 
            $db=Database::getInstance();
            $mysqli=$db->getConnection();
            $query="UPDATE `$table` SET ";
            $records=[];
            foreach($data as $field=>$value)
            {
                if($data[$field]!="NOW()")
                {
                    $records[]=len($value)?"`$field`=NULL":"`$field`='$value'";
                }else{
                    $records[]=len($value)?"`$field`=NULL":"`$field`=$data[$field]";
 
                }
            }
            $query.=implode(", ", $records);
            $query.=" WHERE `id`='$id'";

            $result=$mysqli->query($query);
            echo $mysqli->error;
            if($mysqli->affected_rows>0)
            {
                return true;
            }
            return false;
        }

        

        function mysqli_work($query)
        { 
            $db=Database::getInstance();
            $mysqli=$db->getConnection();
            $result=$mysqli->query($query);
            echo $mysqli->error;
            return $result;
        }

        function mysqli_row($query, $field)
        { 
           $result=mysqli_work($query);
           if($result->num_rows==0)
           {
            return false;
           }
           $row=$result->fetch_assoc();
           return $row[$field];
        }

        function format_phone_number($number)
        {
            if($number=="NULL" || len($number)){
                return false;//invalid
            }

            $number=str_replace("+", "", $number);//remove the + at the start of the number
            
            $non_space=explode(" ", $number);
            $number_pieces=array();
            
            foreach($non_space as $key=>$value){
                $number_pieces[]=trim($value);
            }

            $number=implode("",$number_pieces);
            
            $non_slash=explode("/", $number);
            $number_pieces=array();
            
            foreach($non_slash as $key=>$value)
            {
                $number_pieces[]=trim($value);
            }
            
            $number=$number_pieces[0];
            $num=substr($number, 0, 1);
            if($num=="0")
            {
                $new="256" . substr($number, 1);
                //return $new;
            }
            else{
                $new=$number;
            }
            if(strlen($new)!=12)
            {
                return false;
            }
            return $new;
        }


        function format_help_line_number($number)
        {
            if($number=="NULL" || len($number)){
                return false;//invalid
            }

            $number=str_replace("+", "", $number);//remove the + at the start of the number
            
            $non_space=explode(" ", $number);
            $number_pieces=array();
            
            foreach($non_space as $key=>$value){
                $number_pieces[]=trim($value);
            }

            $number=implode($number_pieces);
            
            $non_slash=explode("/", $number);
            $number_pieces=array();
            
            foreach($non_slash as $key=>$value)
            {
                $number_pieces[]=trim($value);
            }
            
            $number=$number_pieces[0];
            $num=substr($number, 0, 1);
            if($num=="0")
            {
                $new="0" . substr($number, 1);
                //return $new;
            }
            else{
                $new=$number;
            }
            if(strlen($new)!=10)
            {
                return false;
            }
            return $new;
        }

        function acc_creator()
        {
            $length=5;
            srand((float)microtime()*1000000);
            $number='';
            for($i=0; $i<$length; $i++)
            {
                $number.=rand(0,9);
            }
            return $number;
        }

        function now_plus_reset($days=1){
            $NewDate=Date('Y-m-d H:i:s', strtotime("+".$days." days"));
            return $NewDate;
        }//ends today_plus_days


      
        function gen_uuid() {
            return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        
                // 16 bits for "time_mid"
                mt_rand( 0, 0xffff ),
        
                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand( 0, 0x0fff ) | 0x4000,
        
                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand( 0, 0x3fff ) | 0x8000,
        
                // 48 bits for "node"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
            );
        }


        function require_api_data($data, $fields=[])
        {
            foreach($fields as $key=>$field)
            {
                if(!isset($data->$field))
                {
                    $info=array(
                        'status' => 'Fail',
                        'details' => array("message"=>"Parameter " . $field . " is not specified", "content"=>false)
                    );
                    print_r(json_encode($info));
                
                    exit;
                }
            }
        }



        function require_api_headers(): void
        {
            // header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Credentials: true');
            // header('Content-Type: application/json');
            // header('Access-Control-Allow-Methods: POST');
            // header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
            header('Authorization: Bearer ');

            header('Access-Control-Allow-Origin: *');
            header("Access-Control-Allow-Methods: POST");
            header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
            header('Content-Type: application/json');

            $method = $_SERVER['REQUEST_METHOD'];

            if ($method == "OPTIONS") {
                header('Access-Control-Allow-Origin: *');
                header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
                header("HTTP/1.1 200 OK");
                die();
            }
        }

        function import($file_path)
        {
            #try first directory
            $path="../".$file_path;
        
            if(file_exists($path))
            {
                return include_once($path);
            }else{
                $path="../../".$file_path;
                return include_once($path);
            }
        }


        function projectImport($file_path)
        {
             #try first directory
            include_once(dirname(__DIR__). "/". $file_path);
             
        }

        function mysqli_delete($table, $id)
        {  
            $db=Database::getInstance();
            $mysqli=$db->getConnection();
            $query="DELETE FROM `$table` WHERE `id`='$id'";
            $result=$mysqli->query($query);
            if($mysqli->affected_rows>0)
            {
                return true;
            }
            return false;
        }


        function format_vehicle_number($number)
        {
            if($number=="NULL" || len($number)){
                return false;//invalid
            }
            $non_space=explode(" ", $number);
            $number_pieces=array();
            
            foreach($non_space as $key=>$value){
                $number_pieces[]=trim($value);
            }
            $number=implode("", $number_pieces);
            $number=strtoupper($number);
          
            return $number;
        }

        function date_formats($field, $blank=false)
        {
            if(!$blank)
            {
                return array(
                    "long_date"=>user_date_time($field),
                    "short_date"=>picker_format($field),
                    "when"=>tell_when($field),
                    "time"=>user_time($field),
                    "date" => tell_when_no_time($field),
                    "weekday" => weekday($field),
                    "db"=>db_date($field)
                );
            }else{
                return array(
                    "long_date"=>"--:--",
                    "short_date"=>"--:--",
                    "when"=>"--:--",
                    "time"=>"--:--",
                    "date"=>"--:--",
                    "weekday"=>"-",
                    "db"=>"--:--"
                );
            }
           
        }

        /**
         * Returns different number formats eg. 004, 4,000
         * - Make sure the passed data is int
         */
        function int_format($int, $key="total")
        {
            $int = floor($int*1);
            return array(
                "$key"=>$int,
                "$key"."_c"=>number_format($int, 0),
                "$key"."_p"=>strlen($int)>3?number_format($int) : vo_no($int, 2)
            );
        }


        function mysqli_work_update($query)
        { 
            $db=Database::getInstance();
            $mysqli=$db->getConnection();
            $mysqli->query($query);
            echo $mysqli->error;
            if($mysqli->affected_rows>0)
            {
                return true;
            }

            return false;
        }


        function mysqli_work_insert($query)
        { 
            $db=Database::getInstance();
            $mysqli=$db->getConnection();
            $result=$mysqli->query($query);
            echo $mysqli->error;
            $id=$mysqli->insert_id;
            if($mysqli->affected_rows>0)
            {
                return $id;
            }
            return false;
        }

        function row_exists($table, $id, $field='id')
        {
            $db=Database::getInstance();
            $mysqli=$db->getConnection();
            $query_check="SELECT*FROM `$table` WHERE";
            $query_check.=" `$field` = '$id'";
            $result_check=$mysqli->query($query_check);
            $num_check=$result_check->num_rows;
            return $num_check>0 ? true : false;
        }

        function require_api_export_headers(): void
        {
            header('Content-Type: application/xls');
            header('Content-Disposition: attachment; filename = report.xls');
        }


        function phone_notify_user($message, $number, $head=1)
        {

            if($head>0)
            {
                $message="MULTIPLEX: " . $message;
            }
           

            
             $message=urlencode($message);
            $url = "https://api.africastalking.com/version1/messaging";
            $auth="50492dfbe1f6c66e1f1caa3af2cf9e51d8c2ffc32a2086337533d62c32d74935";
           
            $data ="username=streetparking2020&to=".$number . "&message=".$message;
             
            //$json = json_encode($data);
            $headers = array();
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = 'apiKey:'. $auth;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); // Do not send to screen
            curl_setopt($ch, CURLOPT_USERAGENT, 'MULTIPLEX');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            $response=curl_exec($ch);
            curl_close($ch);
            //if($response)
           // $response=json_decode($response);
            return $response;
            
        }

        function sql_today($field)
        {
            return " DATE($field)=CURDATE()";
        }

        function sql_this_month($field)
        {
            $query=" MONTH($field)=MONTH(CURDATE())";
            return $query.=" AND YEAR($field)=YEAR(CURDATE())";
        }

        function sql_this_week($field)
        {
            $query=" YEARWEEK($field)=YEARWEEK(NOW())";
            return $query;

        }

        function sql_minute_timer($field)
        {
            return "TIMESTAMPDIFF(MINUTE, $field, NOW()) AS `timer`";
        }


        function sql_last_month($field)
        {

            $query=" YEAR($field) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
            return $query.=" AND MONTH($field) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
        }


        function sql_this_year($field)
        {

            return " YEAR($field) = YEAR(CURRENT_DATE)";
        }


        function send_mail($RecipientName, $RecipientAddress, $Subject="MULTIPLEX", $Message="Body text", $auto_path="")
        {
            require $auto_path . 'mail/PHPMailerAutoload.php';
            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Mailer = "smtp";
            $mail->Host = "mail.thrivetecug.com";
            $mail->Port = "26"; // 8025, 587 and 25 can also be used. Use Port 465 for SSL.
            $mail->SMTPAuth = true;
            //$mail->SMTPSecure = "ssl";
            $mail->Username = "support@thrivetecug.com";
            $mail->Password = "Support@2020";
            $mail->SMTPDebug  = false;         
            // $mail->SMTPOptions = array(
            //     'ssl' => array(
            //         'verify_peer' => false,
            //         'verify_peer_name' => false,
            //         'allow_self_signed' => true
            //     )
            // );
            
            $mail->FromName = $RecipientName;
            foreach($RecipientAddress as $key=>$value):
                $mail->From = "support@thrivetecug.com";
                $mail->AddAddress($value, $RecipientName);
            endforeach;
            
            
            $mail->isHTML(true); 

            $mail->AddReplyTo("andrizar2@gmail.com", "MULTIPLEX");
            $mail->Subject = $Subject;
            $mail->Body = $Message;
            $mail->WordWrap = 50;

            if(!$mail->Send()) {
            //echo 'Email was not sent.';
            //echo 'Mailer error: ' . $mail->ErrorInfo;
            return false;
            } else {
            //echo 'Email has been sent.';
            return true;
        }
    
    }

    function ref_generator($pre="R")
    {
        $str=$pre . date("ymdhis") . alpha_num(2);
        return $str;
    }

#METHOD SPLITS  NOTIFICATION CONFIGURATIONS
function app_initials($str) 
{
	$str=trim($str);
    $ret = "";
    foreach (explode('_', $str) as $word)
	if(isset($word[0])){

       $ret .= strtoupper($word[0]);
 
    }
	return $ret;
}



function user_dated($date_user_time)
{
    $date_user = date('d M Y', strtotime($date_user_time));
    $time_user = date('h:i a', strtotime($date_user_time));
    return $date_user;
}


function tell_when_no_time($date_added)
{
    // $today = date('Y-m-d H:i:s');
    // $days = get_time_difference(db_date($date_added), $today);
    // $days = $days['days'];

    $query="SELECT TIMESTAMPDIFF(DAY, '$date_added', NOW()) AS `days`, CURDATE() AS `today`";
    $result=mysqli_work($query);
    $row=$result->fetch_assoc();
    $days=$row['days'];
    if($row['today']===db_date($date_added)){
        return "Today";
    }
     elseif ($days == 1) {
        return "Yesterday";
    } elseif ($days > 1 && $days <= 4) {
        return $days . " days back";
    } else {
        return user_dated($date_added);
    }
}


function validEmail($email)
{
    // First, we check that there's one @ symbol, and that the lengths are right
    if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/", $email)) {
        // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
        return false;
    }
    // Split it into sections to make it easier to read or navigate
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
        if (!preg_match("/^(([A-Za-z0-9!#$%&'*+\/=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[$i])) {
            return false;
        }
    }
    if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
        $domain_array = explode(".", $email_array[1]);
        if (sizeof($domain_array) < 2) {
            return false; // Not enough parts to domain
        }
        for ($i = 0; $i < sizeof($domain_array); $i++) {
            if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/", $domain_array[$i])) {
                return false;
            }
        }
    }

    return $email;
}

function format_phone_search($number)
{
    $num=substr($number, 0, 1);
    if($num=="0")
    {
        $new="256" . substr($number, 1);
        //return $new;
    }
    else{
        $new=$number;
    }

    return $new;
}

function validPassword($password)
{
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);
    
    if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
      return false;
    }

    return $password;
}


function secure_url($string)
{
    //$string=urlencode($string);
    return $string;
}


function post_data_to_url($url, $data)
{
           
               
            $json = json_encode($data);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); // Do not send to screen
            curl_setopt($ch, CURLOPT_USERAGENT, 'QUICKPOST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            $response=curl_exec($ch);
            curl_close($ch);
            //if($response)
           // $response=json_decode($response);
            return $response;
}


function get_request_name($uri_depth=0)
    {
        $url=$_SERVER['REQUEST_URI'];
        $clean_url=explode("?", $url);
        $url=$clean_url[0];
        $request = explode("/", $url);
        $parts=[];
        foreach($request as $key=>$value)
        {
            if($key>$uri_depth)
            {
                $parts[]=$value;
            }
        }
        $request=implode("/", $parts);
        return $request;
    }



    function get_request_params()
    {
        $url=$_SERVER['REQUEST_URI'];
        $request = explode("?", $url);
        if(isset($request[1]))
        {
            return $request[1];
        }
        return false;
    }



    function format_api_return_data($result, $Request)
    {
        if($result)
        {
            $info=[
                'status' => "OK",
                'message'=>$Request->Success,
                'details' =>$result
            ];
        }else{
            $info=array(
                'status' => "Fail",
                'message'=>$Request->Error,
                'details' => $result
                
            );
        }

        return $info;
    }

    /** RETURNS CALLING CLASS IN A CALLED CLASS */
    function get_caller_class() {
    
        //get the trace
        $trace = debug_backtrace();
        
        // Get the class that is asking for who awoke it
        $class = ( isset( $trace[1]['class'] ) ? $trace[1]['class'] : NULL );
    
        // +1 to i cos we have to account for calling this function
        for ( $i=1; $i<count( $trace ); $i++ ) {
            if ( isset( $trace[$i] ) && isset( $trace[$i]['class'] ) ) // is it set?
                 if ( $class != $trace[$i]['class'] ) // is it a different class
                     return $trace[$i]['class'];
        }
    }




    function weekday($date_user_time){
        return date('l', strtotime($date_user_time));

    }

    function clean_amount($amount)
    {
       return str_replace(',', '', $amount);
    }



    function convert_minutes($minutes)
    {
        $minutes = $minutes*1;
        $hrs = floor($minutes/60);
        $min = $minutes - ($hrs*60);
        if($minutes>60)
        {
            $time =  $hrs . " " . plural("hr", $hrs) . " " . $min . " " . plural("hr", $min);
        }else{
            $time = $minutes . " " . plural("min", $minutes);
        }

        return $time;
    }


    function convert_to_24_hour_clock($time)
    {
        $dateTime = new DateTime($time);
        $timeIn24HourFormat = $dateTime->format('H:i');
        return $timeIn24HourFormat;
    }


    /** Assigns value if the expected  variable is set otherwsise default_value is assigned
     * - Cleans input
    */
    function default_input(object $object, $variable, $default_value="")
    {
        $value = isset($object->$variable)?clean($object->$variable): $default_value;
        return $value;
    }



    function getTheDay($date)
    {
        $curr_date=strtotime(date("Y-m-d H:i:s"));
        $the_date=strtotime($date);
        $diff=floor(($curr_date-$the_date)/(60*60*24));
        switch($diff)
        {
            case 0:
                return "Today";
                break;
            case 1:
                return "Yesterday";
                break;
            default:
                return $diff." Days ago";
        }
    }



    
    function identify_mobile_provider($MobileSubscriber){
    
        $mtn=[
            "25677",
            "25678",
            "25630",
            "25631",
            "25632",
            "25633",
            "25634",
            "25635",
            "25636",
            "25637",
            "25638",
            "25639",
            "25676"
        ];

        if(!format_phone_number($MobileSubscriber)):
            return false;
        endif;
        $identifier=substr($MobileSubscriber, 0, 5);
        $provider=false;
        if($identifier==="25670" || $identifier==="25675" || $identifier==="25674")
        {
            $provider="AIRTEL";
        }
        elseif(in_array($identifier, $mtn))
        {
            $provider="MTN";
        }
        return $provider;

    }


    function print_ussd_string($string_value){

        $response="responseString=". secure_url($string_value) . "&action=end";
        print_r($response);
        exit;
    }


    /** 
     * Prints output while continuing to process
     */
    function print_in_execution($str) {
       
       
        // Buffer all upcoming output...
        ob_start();

        // Send your response.
        print_r($str);

        // Get the size of the output.
        $size = ob_get_length();

        // Disable compression (in case content length is compressed).
        header("Content-Encoding: none");

        // Set the content length of the response.
        header("Content-Length: {$size}");

        // Close the connection.
        header("Connection: close");

        // Flush all output.
        ob_end_flush();
        @ob_flush();
        flush();

        // Close current session (if it exists).
        // if(session_id()) session_write_close();
       
    
          // Start your background work here.
      
    }


     /** 
     * Returns output while continuing to process
     */
    function return_in_execution($str) {
       
       
        // Buffer all upcoming output...
        ob_start();

        // Send your response.
        print_r($str);

        // Get the size of the output.
        $size = ob_get_length();

        // Disable compression (in case content length is compressed).
        header("Content-Encoding: none");

        // Set the content length of the response.
        header("Content-Length: {$size}");

        // Close the connection.
        header("Connection: close");

        // Flush all output.
        ob_end_flush();
        @ob_flush();
        flush();

        // Close current session (if it exists).
        if(session_id()) session_write_close();
       
    
          // Start your background work here.
      
    }

    function determine_age($date_added){ 

        $query="SELECT TIMESTAMPDIFF(MONTH, '$date_added', NOW()) AS `months`, CURDATE() AS `today`";
        $result=AppData::__execute($query);
        $row=$result->fetch_assoc();
        $months=$row['months']*1;
        $age = "New!";
        if($months < 12){
            $age = $months . plural(" month", $months);
        }else{
            $age = round($months/12, 1);
            $age = $age . plural(" year", $age);
        }
       
        return $age;
    
    }


    function format_large_number($number) {
        $suffixes = array('', 'k', 'm', 'b', 't', 'z'); // Suffixes for thousand, million, billion, trillion, etc.
        $suffixIndex = 0;
      
        while ($number >= 1000 && $suffixIndex < count($suffixes) - 1) {
          $number /= 1000;
          $suffixIndex++;
        }
      
        return round($number, 1) . $suffixes[$suffixIndex];
    }

    

function generateISO8583Message() {
    // Define characters that can be used in ISO 8583 message
    $characters = '0123456789';
    
    // Initialize an empty message
    $message = '';

    // Generate 12 random characters
    for ($i = 0; $i < 12; $i++) {
        $message .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $message;
}



function post_tokenized_data_to_url($data, $token, $url)
{
    $json = json_encode($data);
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = "Authorization: Bearer " . $token; // Prepare the authorisation token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Inject the token into the header
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Do not send to screen curl_setopt($ch, CURLOPT_USERAGENT, 'QUICKPOST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($response);
    return $response;
}


function getIPAddress() {  
    //whether ip is from the share internet  
     if(!empty($_SERVER['HTTP_CLIENT_IP'])) {  
                $ip = $_SERVER['HTTP_CLIENT_IP'];  
        }  
    //whether ip is from the proxy  
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];  
     }  
   //whether ip is from the remote address  
    else{  
             $ip = $_SERVER['REMOTE_ADDR'];  
     }  
     return $ip;  
}  



function sql_seconds_timer($field)
{
    return "TIMESTAMPDIFF(SECOND, $field, NOW()) AS `timer`";
}


/**
 * Used in conditions where a query has dynamic conditions in the where clause.
 * - Advantage: It eliminates redudant where clauses e.g id > 0
 * - Implementation
 * - Condition structure: [['condition'=>'date >=', value='CURDATE()', type='string'], [...]]
 */
function filter_query(array $conditions){

    $n=0;
    $query = "";
    foreach($conditions as $key=>$field){

        if($field['type']==='int_null'):
            $hypothesis = !is_null($field['value']);
        else:
            $hypothesis = $field['value'];
        endif; 
        
        if($hypothesis){
            if($n==0):
                $query.=" WHERE ".  $field['condition'] . " ";
                if($field['type']==='date'){
                    $query.= "'" . $field['value'] . "'";
                }else{
                    $query.= "'" . $field['value'] . "'";
                }
            else:
                $query.=" AND ".  $field['condition'] . " ";
                if($field['type']==='date'){
                    $query.= "'" . $field['value'] . "'";
                }else{
                    $query.= "'". $field['value'] . "'";
                }
            endif;
          $n++;
        }
       
       
    }

    return $query;
}

function terminate($message, $details=false){
  http_response_code(200);
  require_api_headers();
  $err = [ "status" => "Fail",
            "message" => $message,
            "details" => [
                "message" => $message,
                "content" => $details
            ]
        ];
    print_r(json_encode($err));
    exit;
}


?>

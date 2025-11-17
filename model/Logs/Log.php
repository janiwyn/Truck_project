<?php

namespace model\Logs;

use model\App;
use sys\store\AppData;

class Log extends App
{

    private $TableName = "tbl_user_access_log";
    private $Enabled = true;

    public function __log_user_access($username, $description = "System access")
    {
       
       /** UNCOMMENT IF YOU WANT TO SAVE THE LOGS INTO THE DATABASE */
       /*
        
            $data=array(
                        "username"=>$username,
                        "activity"=>$description
                    );
                    AppData::__create($this->TableName, $data);

        **/
      

        if ($this->Enabled) :
            $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            if (!is_dir("log")) {
                mkdir('log', 0777, true);
            }
            $log = date("Y-m-d H:i:s") . " " . $username . ": " . $description . " via {$url}\n";
            $fp = fopen('log/access.log', 'a'); //opens file in append mode.
            fwrite($fp, $log);
            fclose($fp);
        endif;
    }

    public function __log_jobs($log)
    {
        $fp = fopen('log/crontab.log', 'a'); //opens file in append mode.
        fwrite($fp, $log);
        fclose($fp);
    
    }

    public function __log_custom_file($log, $file_name)
    {

        if (!is_dir("log")) {
            mkdir('log', 0777, true);
        }

        $log =  date("Y-m-d H:i:s") . " " . getIPAddress() . " => " . $log . "\n===========\n";
        $fp = fopen('log/' . $file_name, 'a'); //opens file in append mode.
        fwrite($fp, $log);
        fclose($fp);
    
    }

}//ends class

<?php
    
namespace model\DataFiles;

use Exception;
use model\App;

class DataFile extends App{

    function __upload_csv($dataFile)
    {
       
        $file=$dataFile;
        $file_parts=explode(",", $file);
        $file_part=$file_parts[1];
        $file_info=$file_parts[0];
        $file_info_parts=explode(";", $file_info);
        $file_info_parts=explode(":", $file_info_parts[0]);
        $photo_info_parts=explode("/", $file_info_parts[1]);
        $file_extension=strtolower($photo_info_parts[1]);
        $extensions=array("vnd.ms-excel", "csv");
        if(!in_array($file_extension, $extensions))
        {
            $this->Error="Invalid csv file - " . $file_extension;
            return false;
        }

       

        $new_file_name=clean("file_". alpha_num() . time() . ".csv");
       
        if(!file_put_contents($this->UploadsDir. $new_file_name, base64_decode($file_part))):
       
            throw new Exception("Error uploading csv file ", 500);
            $this->Error="Check file permissions or ensure that " . $this->UploadsDir . " exists";
            return false;
        endif;
        
        return $new_file_name;
       
        
    }//ends upload file function
}

?>
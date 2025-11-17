<?php

namespace sys\store;

use Database;
use model\App;
// use model\Auth\Environment;


/** DATEBASE CRUD */
class DataStore extends App{

    private $Connection;
    private $DatabaseName;


    public function __construct()
    {

        $db=Database::getInstance();
        $mysqli=$db->getConnection();
        $this->Connection=$mysqli;
        $this->DatabaseName= getenv('DATABASE_NAME');
        
    }


    /** INSERT
     * - Creates a record in the database
    */
    public function __create($table, $data)
    {  
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
        $mysqli=$this->Connection;
        $result=$mysqli->query($query);
        $this->Error=$mysqli->error;
        echo $this->Error;
        $id=$mysqli->insert_id;
        if($mysqli->affected_rows>0)
        {
            return $id;
        }
        return false;
    }

    /** RETURNS AN OBJECT OF SINGLE ROW */
    public function __get_row($table, $id, $field="id")
    {
        $query="SELECT*FROM `$table` WHERE `$field`='$id' LIMIT 1";
        $result = $this->__execute($query);
        if($result->num_rows==0):
            $this->Error="Record not found";
            return false;
        endif;
        return $result->fetch_object();
    }

    /** RETURNS A MYSQL_RESULT  */
    public function __get_rows($table)
    {
        $query="SELECT*FROM `$table`";
        $result = $this->__execute($query);
        if($result->num_rows==0):
            $this->Error="Records not found";
            return false;
        endif;
        return $result;
    }

    /** Runs a sql query and returns a result
     * - @return mysqli_result
     */
    public function __execute($query)
    {
        $mysqli=$this->Connection;
        $result=$mysqli->query($query);
        $this->Error = $mysqli->error;
        return $result;
    }

/** 
 * Query argument must return a single row 
 * - @returns [object]
 * */
    public function __execute_row($query)
    {
        $result = $this->__execute($query);
        if($result->num_rows==0):
            $this->Error="Query didn't return nothing";
            return false;
        endif;
        return $result->fetch_object();
    }

    public function __table_exists($table)
    {
        if(!requires_db_initialization()):
            return true;
        endif;
       
        $mysqli=$this->Connection;
        $query_check="SELECT*FROM information_schema.tables WHERE";
        $query_check.=" table_schema = '". $this->DatabaseName ."' AND table_name = '$table'";
        $query_check.=" LIMIT 1;";
        $result_check=$mysqli->query($query_check);
        $num_check=$result_check->num_rows;
        $bol=$num_check>0 ? true : false;
        return $bol;
    }


    public function __create_table($table_name, $query)
    {
        $q_parts =explode(" ", $query);
        $q_parts[1] = "TABLE IF NOT EXISTS ";
        $query=implode(" ", $q_parts);

        if(!requires_db_initialization()):
            return false;
        endif;
        if($this->__execute($query)):
            return true;
        endif;

        return false;
    }

   
    public function __filter_rows($table, $where=[], $options="")
    {
        $query="SELECT * FROM `$table` WHERE `id`>0";
        foreach($where as $key=>$value):
            $query.=" AND `" . $key . "`= '$value'";
        endforeach;
        $query.=$options;
        return $this->__execute($query);
    }


    public function __delete($table, $id)
    {  

        $mysqli=$this->Connection;
        $query="DELETE FROM `$table` WHERE `id`='$id'";
        $result=$mysqli->query($query);
        if($mysqli->affected_rows>0)
        {
            return true;
        }
        return false;
    }



    /**
     * Check if update target record already exists before update
     * - table: Table Name
     * - Constraint_id: The currently edited record id value
     * - Field: Target field
     * - needle: Target value
     */
    public function __target_exists($table, $constraint_id, $field, $needle)
    {
        $query="SELECT `id` FROM `$table` WHERE `$field`='$needle'";
        $query.=" AND `id`!='$constraint_id'";
        $result = mysqli_work($query);
        if($result->num_rows>0):
          $this->Success="Record already exists";
          return true;
        endif;
        return false;

    }


    public function __update($table, $data, $id, $base_field="id")
    { 
       
        $mysqli=$this->Connection;
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
        $query.=" WHERE `$base_field`='$id'";
        $result=$mysqli->query($query);
        $this->Error=$mysqli->error;
        if($mysqli->affected_rows>0)
        {
            return true;
        }
        return false;
    }


    function __field_exists($tab, $field)
    {
        if(!requires_db_initialization()):
            return true;
        endif;
        $mysqli=$this->Connection;
        $query="SHOW COLUMNS FROM `$tab` LIKE '$field'";
        $result=$mysqli->query($query);
        echo $mysqli->error;
        $exists=($result->num_rows)?true:false;
        return $exists;
    }


}





?>
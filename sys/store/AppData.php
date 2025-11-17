<?php

namespace sys\store;

use Database;
use model\App;
use model\Auth\Environment;


/** DATEBASE CRUD */
class AppData extends App{

    public static $Connection;
    private static $DatabaseName;
    private static $Fields;
    private static $Query;
    private static $ConnectionError;
    private static $UniqueField = "ID";


    public function __construct(){

        $db=Database::getInstance();
        $mysqli=$db->getConnection();
        self::$Connection=$mysqli;     
    }


    /** INSERT
     * - Creates a record in the database
    */
    public static function __create($table, $data)
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
        
        self::__execute($query);
        $mysqli=self::$Connection;
        self::__set_error($mysqli->error);
        $id=$mysqli->insert_id;
        if($mysqli->affected_rows>0)
        {
            return $id;
        }
        return false;
    }

    /** RETURNS AN OBJECT OF SINGLE ROW */
    public static function __get_row($table, $id, $field="id")
    {
        $key = self::$UniqueField;

        if(is_array($id)):

            $query="SELECT*FROM `$table` WHERE ";
            $records=[];
            foreach($id as $field=>$value):
            
                if(strpos($value, '`') !== false):
    
                    $records[]="`$field`=$value";
                else:
                    $records[]="`$field`='$value'";
                endif;
                
            endforeach;
            $query.=implode("AND ", $records) . " ORDER BY `$key` DESC LIMIT 1";

        else:
         $query="SELECT*FROM `$table` WHERE `$field`='$id' ORDER BY `$key` DESC LIMIT 1";
        endif;
        
        $result = self::__execute($query);
        if($result->num_rows==0):
            self::__set_error("Record not found");
            return false;
        endif;
        return $result->fetch_object();
        
    }

    /** RETURNS A MYSQL_RESULT  */
    public static function __get_rows($table)
    {
        $query="SELECT*FROM `$table`";
        $result = self::__execute($query);
        if($result->num_rows==0):
            self::__set_error("Records not found");
            return false;
        endif;
        return $result;
    }

    /** Runs a sql query and returns a result
     * - @return mysqli_result
     */
    public static function __execute($query)
    {        new self;
        $mysqli=self::$Connection;
        $result=$mysqli->query($query);
        self::__set_error($mysqli->error);
        return $result;
    }

    private static function __set_error($error)
    {
        self::$ConnectionError=$error;
    }


    public static function __get_connection_error()
    {
        return self::$ConnectionError;
    }

    public static function __table_exists($table)
    {
        if(!requires_db_initialization()):
            return true;
        endif;
        // new self;
        // (new Environment('.env'))->load();//loads from index folder
        self::$DatabaseName= getenv('DATABASE_NAME');
        
        $query_check="SELECT*FROM information_schema.tables WHERE";
        $query_check.=" table_schema = '". self::$DatabaseName ."' AND table_name = '$table'";
        $query_check.=" LIMIT 1;";
        $result_check=self::__execute($query_check);
        $num_check=$result_check->num_rows;
        $bol=$num_check>0 ? true : false;
        return $bol;
    }




    public static function __create_table($table_name, $query)
    {
        if(!requires_db_initialization()):
            return false;
        endif;

        if(!self::__table_exists($table_name)):
            self::__execute($query);
            return true;
        endif;

        return false;
    }



   
    public static function __filter_rows($table, $where=[], $options="")
    {
        $query="SELECT * FROM `$table` WHERE `id`>0";
        foreach($where as $key=>$value):
            $query.=" AND `" . $key . "`= '$value'";
        endforeach;
        $query.=$options;
        return self::__execute($query);
    }


    public static function __delete($table, $id)
    {  

        $query="DELETE FROM `$table` WHERE `id`='$id'";
        self::__execute($query);
        $mysqli=self::$Connection;
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
    public static function __target_exists($table, $constraint_id, $field, $needle)
    {
        $query="SELECT `id` FROM `$table` WHERE `$field`='$needle'";
        $query.=" AND `id`!='$constraint_id'";
        $result = mysqli_work($query);
        if($result->num_rows>0):
          self::__set_error("Record already exists");
          return true;
        endif;
        return false;

    }


    public static function __update($table, $data, $id, $base_field="id")
    { 
       
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

        $result=self::__execute($query);
        $mysqli=self::$Connection;
        if($mysqli->affected_rows>0)
        {
            return true;
        }
        return false;
    }

    public static function __field_exists($tab, $field)
    {
        if(!requires_db_initialization()):
            return true;
        endif;
        $query="SHOW COLUMNS FROM `$tab` LIKE '$field'";
        $result=self::__execute($query);
        $exists=($result->num_rows)?true:false;
        return $exists;
    }



    public static function __select_fields(array $fields=[])
    {
        self::__clear_query();
        $fil=[];
        foreach($fields as $field):
            $fil[]= "`$field`";
        endforeach;
        self::$Fields = empty($fil)?["*"]:$fil;//select all is no field is provided
        self::__set_query( "SELECT " . implode(", ", self::$Fields));
        return new self;
    }

    function __from_table(String $TableName)
    {
        $this->__set_query(self::$Query . " FROM " . $TableName);
        return $this;
    }


    private static function __set_query($query)
    {
        self::$Query = $query;
    }

    public function __where(array $conditions, string $custom_conditions="")
    {
        $n=0;
        $query="";
        foreach($conditions as $key=>$value):

            $n++;
            if($n==1):
                $query.=" WHERE `" . $key . "`= '$value'";
            else:
            $query.=" AND `" . $key . "`= '$value'";
            endif;
        endforeach;
            $query.=" " . $custom_conditions;
        $this->__set_query(self::$Query . " " . $query);
        return $this;
    }

    public function __set_options(string $options)
    {
        $this->__set_query(self::$Query . " " . $options);
        return $this;
    }


    public static function __update_table(String $TableName)
    {
        self::__clear_query();
        self::__set_query(self::$Query . "UPDATE `{$TableName}` SET ");
        return new self;
    }

    private static function __clear_query()
    {
        self::__set_query("");
    }

    public function __set_fields(array $new_field_Values)
    {
        $n=0;
        $query="";
        $records=[];
        foreach($new_field_Values as $field=>$value)
        {
            if(strpos($value, '`') !== false):

                $records[]="`$field`=$value";
            else:
                $records[]="`$field`='$value'";
            endif;
            
        }
        $query.=implode(", ", $records);
        $this->__set_query(self::$Query . " " . $query);
        return $this;
    }



    public function __fetch()
    {
        return $this->__execute(self::$Query);
    }

    public function __fetch_update()
    {
        $this->__execute(self::$Query);
        if(self::$Connection->affected_rows>0):
            return true;
        endif;
        return false;
    }


    public static function __init_enabled()
    {
        if(!requires_db_initialization()):
            return false;
        endif;
        return true;
    }



/**
 *  Returns a particular field value from mysql table
 **/     
public static function __get_field($table, $field, $id, $ref_field="ID")
{ 
   new self;
   $id = self::$UniqueField;
   $query="SELECT `$field` FROM `$table` WHERE `$ref_field`='$id'";
   $query .=" ORDER BY `$table`.`$id` LIMIT 1";
   $mysqli=self::$Connection;
   $result=$mysqli->query($query);
   self::__set_error($mysqli->error);
   if($result->num_rows==0)
   {
       return false;
   }
   return $result->fetch_object()->$field;

}


/**
 * Used with any query string passed as a second param to speed 
 * up data fetching from mysql table
 */
public static function __optimal_search($base_table, $query, $LIMIT=1)
{
    $id = self::$UniqueField;

    $query .=" ORDER BY `$base_table`.`$id` LIMIT $LIMIT";
    $result = self::__execute($query);
    if($result->num_rows>0):
        return $result;
    endif;
    return false;
}


/**
 * Used with __fetch_fields() to speed up data fetching from mysql table
 */
public function __optimize_query($id="id", $LIMIT=1)
{

    $query=" ORDER BY $id DESC LIMIT $LIMIT";
    $this->__set_query(self::$Query . " " . $query);
    return $this;
}





}


?>
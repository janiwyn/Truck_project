<?php

namespace model;

use Exception;
use model\Auth\JWT;
use model\Logs\Log;
use model\User\User;

class App
{

    public $Error;
    public $Success;
    public $SystemUser;
    public $IsActive;
    public $SearchLimit = 5;
    public $ItemsPerPage = 15;
    public $PageStart;
    public $PageStop;
    public $CurrentPageNumber = 0;
    public $TotalRecords;
    public $TotalPages;
    public $ListOfPages = [];
    private $PageNavItems = 8;

    public $secret;
    public $ProjectUrl;  
    public $UUID4;
   

    public $UploadsDir = "uploads/";
    

    public function __construct()
    {
        $this->UUID4 = gen_uuid();
        $this->secret = getenv("JWT_SECRET");
        $this->ProjectUrl = getenv('PROJECT_URL');
    }


    public function __paginate()
    {
        $this->PageStart = ($this->CurrentPageNumber - 1) * $this->ItemsPerPage;
        $this->TotalPages = ceil($this->TotalRecords / $this->ItemsPerPage);
        $start = 1;
        if ($this->PageNavItems > $this->TotalPages) {
            $this->PageNavItems = $this->TotalPages;
        }
        if ($this->CurrentPageNumber < $this->PageNavItems) {
            $start = 1;
        } else {

            if ($this->PageNavItems < $this->TotalPages) {
                $this->PageNavItems++;
            }

            $start = $start > 1 ? $this->CurrentPageNumber - 1 : 1;
        }
        if ($this->CurrentPageNumber == $this->PageNavItems) {
            if ($this->PageNavItems < $this->TotalPages) {
                $start = $start > 1 ? $start - 1 : 1;
            }
        }

        if ($this->TotalPages == 1) {
            $start = 1;
        }

        for ($i = $start; $i <= $this->PageNavItems; $i++) {
            $this->ListOfPages[] = $i;
        }
    }


    public function __validateToken()
    {
        try {
            $payload = $this->__getBearerToken();
            if (!$token = JWT::decode($payload, $this->secret, ['HS256'])) :
                http_response_code(404);
                $this->Error = "Sorry! Signature verification failed";
                return false;
            endif;

            $query = "SELECT * FROM `tbl_user` WHERE `id`='$this->SystemUser'";
            $result = mysqli_work($query);
            if ($result->num_rows == 0) {
                // set response code
                http_response_code(404);
                $this->Error = "Sorry! This user does not exist";
                return false;
            }
            $is_active = $this->IsActive;

            if ($is_active == 0) {
                // set response code
                http_response_code(400);
                $this->Error = "Sorry! This user has been deactivated. Contact Administration";
                return false;
            }
            $row = $result->fetch_assoc();
            extract($row);

        } catch (Exception $e) {
            // set response code
            http_response_code(300);
            $this->Error = "Access token missing or Access token processing failed";
            // $e->getMessage();
            return false;
        }
    }


    /*** Get hearder Authorization ***/
    public function __getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions
            // (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }


    /*** get access token from header ***/
    public function __getBearerToken()
    {
        $headers = $this->__getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        // set response code
        http_response_code(404);
        $this->Error = "Access Token Not found";
        return false;
    }


    public function __set_system_user($activity = "Unspecified activity"): void
    {
        $this->secret = getenv("JWT_SECRET");
        $payload = $this->__getBearerToken();
        if (!$payload) {
            http_response_code(404);
            echo "System access denied for user";
            exit;
        }
        if (!$token = JWT::decode($payload, $this->secret, ['HS256'])) :
            http_response_code(404);
            echo "System access denied: Signature verification failed";
            exit;
        endif;

        $this->SystemUser = $token->data->user_id * 1;
        #Restrict access
        if ($activity != "ONLINE") :
            $NewUser = new User;
            $NewUser->__get_user_info($this->SystemUser);
            if (!$NewUser->__user_is_activated()):
                http_response_code(404);
                echo "Unable to autheticate user, please contact system admin.";
                exit;
            endif;
        endif;




        /**
         * VALIDATING TOKEN TO ENSURE SINGLE LOGIN INSTANCE
         * - checks the submitted token against the stored token, if they are different the user is logged out.
         * - To logout the user, the user is temporarily suspended and reactivated again
         * - This however will logout all the logged in instances of the user
         */
        $NewUser = new User;
        $user_token = $NewUser->__get_user_token($this->SystemUser);
        if ($payload !== $user_token) : (new Log)->__log_user_access($token->data->username, "Malicious activity detected. Logging out user instances - Suspicious token: {$payload} vs {$user_token}");
            //suspend user
            //$NewUser->__force_logout($this->SystemUser);
        endif;



        (new Log)->__log_user_access($token->data->username, $activity);
    }


    public function __get_system_user_info(): void
    {
        $payload = $this->__getBearerToken();
        $token = JWT::decode($payload, $this->secret, ['HS256']);
        $this->SystemUser = $token->data->user_id * 1;
    }


    /**
     * USED WHEN REQUIRING DATABASE VALUES
     */
    function __require_parameters(
        /**
         * Associative array
         */
        $fields
    ) {
        foreach ($fields as $key => $value) {
            if (len($value)) {
                $this->Error = "Sorry! " . $key . " is required.";
                return false;
            }
        }

        return true;
    }

  

    /** Extracts "Success" and "Fail" alerts from an object and returns the data from that object execution. 
     * 
     */
    public function __localize_object(object $object, $result)
    {
        if ($result) :

            $this->Success = $object->Success;
            return $result;
        endif;

        $this->Error = $object->Error;
        $result;
    }

    /** Copies the global variable values from the current object to the specified object */
    public function __clone_globals(object $object)
    {
        $object->CurrentPageNumber = $this->CurrentPageNumber;
        $object->SystemUser = $this->SystemUser;
    }



    /**
     * Returns an array of pagination data including
     * - count
     * - items_per_page
     * - return_record_count
     * - total_pages
     * - offset_count
     * - current_page
     * - list_of_pages
     * - list=>[]
     */

     public function __get_pagination_data($current_return_count){
        
        return array("meta"=>[
            "count"=>$this->TotalRecords,
            "items_per_page"=>$this->ItemsPerPage,
            "return_record_count"=>$current_return_count,
            "total_pages"=>$this->TotalPages,
            "offset_count"=>$this->PageStart,
            "current_page"=>$this->CurrentPageNumber,
            "list_of_pages"=>$this->ListOfPages
        ], "list"=>[]);
    }



}

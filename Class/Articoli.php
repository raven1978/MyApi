<?php

require_once './Class/Request_Base.php';

class Articoli extends Request_Base {

    public function __construct($resource, $db_conn, $token) {

        parent::__construct($resource, $db_conn, $token);
        
        $this->response["payload"] = "";
    }
    
    public function getAll() 
    {
        $this->response["type"] = __FUNCTION__;
        
        $result = $this->db_conn->query("Select * From Articoli");
        
        $this->response["payload"] = $result;
        
        return $this->response;
    }
    
    public function get()
    {
        $this->response["type"] = __FUNCTION__;
        
        if($this->checkTokenValidity())
        {
            //Valid Token
            $field_name = "";
            if(is_numeric($this->payload))
            {      
                $field_name = "id";
            }
            else
            {
                $field_name = "descrizione";
            }
            
            $params = array($field_name => $this->payload);
            
            $result = $this->db_conn->queryBind("SELECT * FROM articoli WHERE $field_name= :$field_name", $params);
            
            if(isset($result[0]))
            {
                $result = $result[0];
                
                $this->response["payload"] = $result;
            }
        }
        else 
        {
            $this->response["status"] = 0;
            $this->response["message"] = USER_TOKEN_EXPIRED;
        }
        
        return $this->response;
    }

}

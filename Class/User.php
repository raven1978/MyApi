<?php

require_once './Class/Request_Base.php';

class User extends Request_Base{

    public function __construct($resource, $db_conn, $token) {
        
        parent::__construct($resource, $db_conn, $token);
        
    }
    
    public function login() 
    {

        $this->response["type"] = __FUNCTION__;
        
        if (isset($this->payload->username) && isset($this->payload->password)) 
        {
//            $this->response["payload"]=$this->db_conn->query("Select * From users");
            
            $params = array("username" => $this->payload->username, "password" => md5($this->payload->password));
            
//            $username = $this->payload->username;
//            $password = $this->payload->password;
            
            
            //gererare il token e mettere nel payload il token e semmai ne json principale mettere anche il type(login)
            $result = $this->db_conn->queryBind("SELECT users.id AS userid, session.id AS sessionid, users.status, session.token, session.token_date FROM users LEFT JOIN session ON users.id = session.id WHERE username = :username AND password = :password", $params);
            
            $result = $result[0];
            
//            echo "<pre>";
//            print_r($result);
//            echo "</pre>";
//            die();
            
            if($result['status'] === USER_ACTIVE)
            {
                $token = $this->generateToken();
                $token_date = date("Y-m-d H:i:s");
                $user_id = $result['userid'];
                
                $params = array(
                    "sessionid" => (int) $user_id,
                    "token" => $token,
                    "token_date" => $token_date
                );

                if(!isset($result['sessionid']) || $result['sessionid'] == '')
                {
                    //Insert new session     
                    $result = $this->db_conn->queryBind("INSERT INTO session (id, token, token_date) VALUES (:sessionid, :token, :token_date)", $params);
 
                }
                else
                {
                    //Update old session
                    $result = $this->db_conn->queryBind("UPDATE session SET token = :token, token_date = :token_date WHERE id = :sessionid", $params);
  
                }
                
                $this->response["id"] = $user_id;
                $this->response["token"] = $token;
                $this->response["token_expire"] = TOKEN_TTL;
                

            }
            else 
            {
                $this->response["status"] = 0;
                $this->response["message"] = MSG_USER_NOT_ACTIVE;
            }
        } 
        else 
        {
            $this->response["status"] = 0;
            $this->response["message"] = MSG_INVALID_LOGIN_REQUEST;
        }
        
        return $this->response;
    }
    
    public function subscribe()
    {
        $this->response["type"] = __FUNCTION__;
        
        if (isset($this->payload->username) && isset($this->payload->password) && isset($this->payload->age))
        {
            $params = array("username" => $this->payload->username, "password" => md5($this->payload->password), "age" => $this->payload->age, "status" => "active");
            
            $result = $this->db_conn->queryBind("INSERT INTO users (username, password, age, status) VALUES (:username, :password, :age, :status)", $params);

            if(isset($result['status']) && $result['status'] ===0)
            {
                $this->response["status"] = 0;
                $this->response["message"] = MSG_SUBSCRIPTION_FAILED;
            }
        }
        
        return $this->response;
    }
        
    public function unsubscribe()
    {
        $this->response["type"] = __FUNCTION__;
        
        if($this->checkTokenValidity())
        {
            //Valid Token
            $params = array("id" => $this->payload->id);
            
            $result = $this->db_conn->queryBind("DELETE FROM users WHERE id= :id", $params);
            $result = $this->db_conn->queryBind("DELETE FROM session WHERE id= :id", $params);
        }
        else 
        {
            $this->response["status"] = 0;
            $this->response["message"] = USER_TOKEN_EXPIRED;
        }
        
        return $this->response;
    }
    
}

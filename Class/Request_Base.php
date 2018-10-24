<?php

require_once './define.php';

require_once './Class/Connect.php';

class Request_Base {
    
    protected $resource;
    
    protected $db_conn;
    
    protected $token;
    
    protected $response;
    
    protected $payload;


    public function __construct($resource, $db_conn, $token) {
        $this->resource = $resource;
        
        $this->db_conn = $db_conn;
        
        $this->token = $token;
        
        $this->payload = "";
        
        $this->response = array("type" => "", "status" => 1, "message" => MSG_REQUEST_SUCCESS);
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    protected function checkTokenValidity()
    {
        
        if($this->token != "" && strlen($this->token) == TOKEN_LEN )
        {
            $params = array("token" => $this->token);

            $result = $this->db_conn->queryBind("SELECT id, token_date FROM session WHERE session.token = :token", $params);

            if(!empty($result) && isset($result[0]))
            {
                $token_date = strtotime($result[0]['token_date']);

                $token_date += TOKEN_TTL;

                $token_date = date("Y-m-d H:i:s", $token_date);

                if (date("Y-m-d H:i:s") <= $token_date) {
                    //Token NOT Expired
                    return $result[0]['id'];
                }
            }
        }
        
        return 0;
    }

    protected function generateToken()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < TOKEN_LEN; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

<?php

require_once './define.php';

require_once './Class/Connect.php';

require_once './Class/Request_Base.php';

/**
 * Description of Controller
 *
 * @author fabrizio.conti
 */

function autoload($class)
{
    if(file_exists("./Class/$class.php"))
    {
        include "./Class/$class.php";
    }
}

class Controller {

    public $request_uri;
    public $db_conn;
    public $token;
    private $response;
    private $request_method;
    private $headers;

    public function __construct($headers = array(), $request_method, $request_uri, $db_conn) {
        spl_autoload_register('autoload');

        $this->headers = $headers;

        $this->token = $this->getHeadersValue("Authorization");

        $this->request_uri = $request_uri;

        $this->request_method = $request_method;

        $this->db_conn = $db_conn;

        $this->response = array("type" => "", "status" => 1, "message" => MSG_REQUEST_SUCCESS);
    }

    public function analyzeRequest() {
        $class = ucfirst(strtolower($this->request_uri[0]));
        $method = "";
        $resource = "";
        $client = "";

        $payload = "";

        if (class_exists($class)) {
            switch ($this->request_method) {
                case "POST":
                case "DELETE": 
                case "PUT": 
                    {
                        $payload = json_decode(file_get_contents('php://input'));

                        if ($payload == "") {
                            $this->response["status"] = 0;
                            $this->response["message"] = MSG_INVALID_INPUT_JSON;

                            return;
                        }

                        if (isset($this->request_uri[1]) && $this->request_uri[1] != "") {
                            $method = strtolower($this->request_uri[1]);
                        }

                        if (isset($this->request_uri[2]) && $this->request_uri[2] != "") {
                            $resource = strtolower($this->request_uri[2]);
                        }
                    }
                    break;
                case "GET": 
                    {
                    
                        $method = "getAll";
                    
                        if (isset($this->request_uri[1]) && $this->request_uri[1] != "") 
                        {
                            $payload = strtolower($this->request_uri[1]);
                            
                            $method = "get";
                        } 
                    }
                    break;

                default:
                    break;
            }
            
            $client = new $class($resource, $this->db_conn, $this->token);

            if (method_exists($client, $method)) {
                $client->setPayload($payload);
                $this->response = $client->$method();
            } else {
                $this->response["status"] = 0;
                $this->response["message"] = MSG_INVALID_SERVICE;
            }
        } else {
            $this->response["type"] = $class;
            $this->response["status"] = 0;
            $this->response["message"] = MSG_REQUEST_INVALID;
        }
    }

    public function getResponse() {
        return json_encode($this->response);
    }

    private function getHeadersValue($elem) {
        if (isset($this->headers) && !empty($this->headers) && isset($this->headers[$elem])) {
            return $this->headers[$elem];
        }

        return "";
    }

}

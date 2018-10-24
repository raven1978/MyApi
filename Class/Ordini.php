<?php

require_once './Class/Request_Base.php';

class Ordini extends Request_Base {
    
    private $validationResult;
    
    private $listaArticoli;
    
    public function __construct($resource, $db_conn, $token) {

        parent::__construct($resource, $db_conn, $token);
        
        $this->response["payload"] = "";
        
        $this->validationResult = true;
        
        $this->listaArticoli = [];
    }
    
    private function validateRequest($value, $key)
    {
        if(!is_numeric($value) || !in_array(strtolower($key), $this->listaArticoli))
        {
            $this->validationResult = false;
        }
    }
    
    public function delete()
    {
        $this->response["type"] = __FUNCTION__;
        
        $user_id = $this->checkTokenValidity();
        
        if($user_id)
        {
            $params = array(
                "id_user" => (int) $user_id,
                "id" => (int) $this->payload->id
            );
            
            $result = $this->db_conn->queryBind("SELECT * FROM ordini WHERE id = :id AND id_user = :id_user", $params);
            
            if (!empty($result)) 
            {
                $result = $this->db_conn->queryBind("DELETE FROM ordini WHERE id = :id AND id_user = :id_user", $params);
            }
            else
            {
                $this->response["status"] = 0;
                $this->response["message"] = MSG_ORDER_NOT_FOUND;
            }
        } else {
            $this->response["status"] = 0;
            $this->response["message"] = USER_TOKEN_EXPIRED;
        }

        return $this->response;
        
    }

    public function update()
    {
        $this->response["type"] = __FUNCTION__;
        
        $user_id = $this->checkTokenValidity();
        
        if ($user_id) 
        {
            $result = $this->db_conn->query("Select distinct descrizione As nome, id, prezzo From articoli");
            
            $lista_prezzi = array();
            
            foreach ($result as $key => $value) {
                $this->listaArticoli[$value['id']] = strtolower($value['nome']);
                
                $lista_prezzi[$value['id']] = $value['prezzo'];
            }
            
        } else {
            $this->response["status"] = 0;
            $this->response["message"] = USER_TOKEN_EXPIRED;
        }
    }
    
    public function submit()
    {
        $this->response["type"] = __FUNCTION__;
        
        $user_id = $this->checkTokenValidity();
        
        if ($user_id) {
            $result = $this->db_conn->query("Select distinct descrizione As nome, id, prezzo From articoli");
            
            $lista_prezzi = array();
            
            foreach ($result as $key => $value) {
                $this->listaArticoli[$value['id']] = strtolower($value['nome']);
                
                $lista_prezzi[$value['id']] = $value['prezzo'];
            }
            
            array_walk($this->payload, array($this, 'validateRequest'));

            if ($this->validationResult) {
                //Storicizzazione ordine

                $date = date("Y-m-d H:i:s");
                
                $params = array(
                    "id_user" => (int) $user_id,
                    "stato_ordine" => "pending",
                    "data" => $date
                );
            
                $result = $this->db_conn->queryBind("INSERT INTO ordini (id_user, stato_ordine, data) VALUES (:id_user, :stato_ordine, :data)", $params);
            
                $result = $this->db_conn->query("SELECT LAST_INSERT_ID()");
                
                $order_id = $result[0]["LAST_INSERT_ID()"];
                
                $flipped = array_flip($this->listaArticoli);
                
                $price = 0;
                
                foreach ($this->payload as $key => $value) 
                {
                    $order_param = array(
                        "id_ordine" => $order_id,
                        "id_articolo" => $flipped[strtolower($key)],
                        "quantita" => $value
                        );
                    
                        $result = $this->db_conn->queryBind("INSERT INTO dettaglio_ordini (id_ordine, id_articolo, quantita) VALUES (:id_ordine, :id_articolo, :quantita)", $order_param);
                        
                        $price += $value * ($lista_prezzi[$flipped[strtolower($key)]]);
                }
                
                $result = $this->db_conn->query("UPDATE ordini SET prezzo = $price WHERE id = $order_id");
                
                $this->response["payload"] = array("id_ordine" => (int)$order_id, "prezzo totale" => $price);

            } else {
                $this->response["status"] = 0;

                $this->response["message"] = MSG_INVALID_ORDER;
            }
        } else {
            $this->response["status"] = 0;
            $this->response["message"] = USER_TOKEN_EXPIRED;
        }



        return $this->response;
    }

}

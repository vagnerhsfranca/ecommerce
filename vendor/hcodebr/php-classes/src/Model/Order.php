<?php

namespace Hcode\Model;

use Hcode\DB\Sql;

class Order extends Model
{

    const ERROR = "orderError";
    const SUCCESS = "orderSucess";

    public function save()
    {
        $connection = new Sql();
        
        $this->setidorder(null);
        var_dump($this);

        $results = $connection->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)",
        [
            ":idorder" => $this->getidorder(),
            ":idcart" => $this->getidcart(),
            ":iduser" => $this->getiduser(),
            ":idstatus" => $this->getidstatus(),
            ":idaddress" => $this->getidaddress(),
            ":vltotal" => $this->getvltotal()
        ]);

        if(count($results) > 0){
            $this->setData($results[0]);
        }


    }

    public function getById($idorder)
    {
        $connection = new Sql();

        $results = $connection->select("SELECT * FROM tb_orders a 
        INNER JOIN  tb_ordersstatus b USING (idstatus)
        INNER JOIN tb_carts c USING (idcart)
        INNER JOIN tb_users d ON a.iduser = d.iduser
        INNER JOIN tb_addresses e USING (idaddress)
        INNER JOIN tb_persons f ON d.idperson = f.idperson
        WHERE idorder = :idorder",
        [
            ":idorder" => $idorder
        ]);

        if(count($results) > 0){
            $this->setData($results[0]);
        }
    }

    public static function listAll()
    {
        $connection = new Sql();

        return $connection->select("SELECT * FROM tb_orders a 
        INNER JOIN  tb_ordersstatus b USING (idstatus)
        INNER JOIN tb_carts c USING (idcart)
        INNER JOIN tb_users d ON a.iduser = d.iduser
        INNER JOIN tb_addresses e USING (idaddress)
        INNER JOIN tb_persons f ON d.idperson = f.idperson
        ORDER BY a.dtregister DESC");
    }

    public function delete()
    {
        $connection = new Sql();

        $connection->query("DELETE FROM tb_orders WHERE idorder = :idorder",
        [
            ":idorder" => $this->getidorder()
        ]);
    }

    public static function setMsgSucess($msg)
    {
        $_SESSION[Order::SUCCESS] = $msg;
    }

    public static function getMsgSucess()
    {
        $msg = (isset($_SESSION[Order::SUCCESS])) ? $_SESSION[Order::SUCCESS] : ""; 
        
        Order::clearMsgSucess();

        return $msg;
    }

    public static function clearMsgSucess()
    {
        $_SESSION[Order::SUCCESS] = NULL;
    }

    public static function setMsgError($msg)
    {
        $_SESSION[Order::ERROR] = $msg;
    }

    public static function getMsgError()
    {
        $msg = (isset($_SESSION[Order::ERROR])) ? $_SESSION[Order::ERROR] : ""; 
        
        Order::clearMsgError();

        return $msg;
    }

    public static function clearMsgError()
    {
        $_SESSION[Order::ERROR] = NULL;
    }

    public static function getOrderPage($page = 1, $itemsPerPage = 18)
    {
        $start = ($page - 1) * $itemsPerPage;

        $connection = new Sql();

        $results  = $connection->select(
            "SELECT SQL_CALC_FOUND_ROWS * FROM tb_orders a 
            INNER JOIN  tb_ordersstatus b USING (idstatus)
            INNER JOIN tb_carts c USING (idcart)
            INNER JOIN tb_users d ON a.iduser = d.iduser
            INNER JOIN tb_addresses e USING (idaddress)
            INNER JOIN tb_persons f ON d.idperson = f.idperson
            ORDER BY a.dtregister DESC
            LIMIT $start, $itemsPerPage");

        $resultTotal = $connection->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            "data" => $results,
            "total" => (int)$resultTotal[0]["nrtotal"],
            "pages" => ceil((int)$resultTotal[0]["nrtotal"] / $itemsPerPage)
        ];
        
    }
    
    public static function getOrderPageSearch($search, $page = 1, $itemsPerPage = 18)
    {
        $start = ($page - 1) * $itemsPerPage;

        $connection = new Sql();

        $results  = $connection->select(
            "SELECT SQL_CALC_FOUND_ROWS * FROM tb_orders a  
            INNER JOIN  tb_ordersstatus b USING (idstatus)
            INNER JOIN tb_carts c USING (idcart)
            INNER JOIN tb_users d ON a.iduser = d.iduser
            INNER JOIN tb_addresses e USING (idaddress)
            INNER JOIN tb_persons f ON d.idperson = f.idperson
            WHERE f.desperson LIKE :search 
            OR b.desstatus LIKE :search
            ORDER BY a.dtregister DESC
            LIMIT $start, $itemsPerPage", [
                ":search" => "%".$search."%"
            ]);

        $resultTotal = $connection->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            "data" => $results,
            "total" => (int)$resultTotal[0]["nrtotal"],
            "pages" => ceil((int)$resultTotal[0]["nrtotal"] / $itemsPerPage)
        ];
        
    }
}
?>
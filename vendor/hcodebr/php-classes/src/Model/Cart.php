<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model\User;

class Cart extends Model
{
    const SESSION = "Cart";
    const SESSION_ERROR = "CartError";

    public function save()
    {
        $connection = new Sql();

        $results = $connection->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)",
        [
            ":idcart" => $this->getidcart(),
            ":dessessionid" => $this->getdessessionid(),
            ":iduser" => $this->getiduser(),
            ":deszipcode" => $this->getdeszipcode(),
            ":vlfreight" => $this->getvlfreight(),
            ":nrdays" => $this->getnrdays()
        ]);

        $this->setData($results[0]);
    }

    public static function getFromSession()
    {
        $cart = new Cart();

        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]["idcart"] > 0){
            
            $cart->findById((int)$_SESSION[Cart::SESSION]["idcart"]);
        
        } else {

            $cart->findBySessionId();

            if(!(int)$cart->getidcart() > 0){
                $data = [
                    "dessessionid" => session_id()
                ];

                if(User::checkLogin(false) === true){
                    $user = User::userInSession();

                    $data["iduser"] = $user->getiduser();
                }

                $cart->setData($data);

                $cart->save();

                $cart->setInSession();
            }
        }

        return $cart;
    }

    public function setInSession()
    {
        $_SESSION[Cart::SESSION] = $this->getValues();
    }

    public function findById(int $idcart)
    {
        $connection = new Sql();

        $results = $connection->select("SELECT * FROM tb_carts WHERE idcart = :idcart",
        [
            ":idcart" => $idcart
        ]);

        if(count($results) > 0){
            $this->setData($results[0]);
        }
    }
    
    public function findBySessionId()
    {
        $connection = new Sql();

        $results = $connection->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid",
        [
            ":dessessionid" => session_id()
        ]);

        if(count($results) > 0){
            $this->setData($results[0]);
        }
    }

    public function addProduct(Product $product)
    {
        $connection = new Sql();

        $connection->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)",
        [
            ":idcart" => $this->getidcart(),
            ":idproduct" => $product->getidproduct()
        ]);

        $this->getCalculateTotal();
    }

    public function removeProduct(Product $product, $all = false)
    {
        $connection = new Sql();
        
        if($all){
            $connection->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL",
            [
                ":idcart" => $this->getidcart(),
                ":idproduct" => $product->getidproduct()
            ]);
        }else{
            $connection->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1",
            [
                ":idcart" => $this->getidcart(),
                ":idproduct" => $product->getidproduct()
            ]);
        }

        $this->getCalculateTotal();
    }

    public function getProducts()
    {
        $connection = new Sql();

        $results = $connection->select(
            "SELECT b.idproduct, b.desproduct, b.vlprice, vlwidth, b.vllength, b.vlweight, b.desurl,
            COUNT(*) AS nrqtd,
            SUM(b.vlprice) AS vltotal 
            FROM tb_cartsproducts a 
            INNER JOIN tb_products b 
            ON a.idproduct = b.idproduct 
            WHERE a.idcart = :idcart
            AND a.dtremoved IS NULL 
            GROUP BY b.idproduct, b.desproduct, b.vlprice, vlwidth, b.vllength, b.vlweight, desurl
            ORDER BY b.desproduct",
            [
                ":idcart" => $this->getidcart()
        ]);

        return Product::checkList($results);
    }

    public function getProductsTotal()
    {
        $connection = new Sql();

        $results = $connection->select(
            "SELECT SUM(vlprice) AS vlprice,  SUM(vlwidth) AS vlwidth,  SUM(vlheight) AS vlheight,  SUM(vllength) AS vllength,  SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
            FROM tb_products a
            INNER JOIN tb_cartsproducts b
            ON a.idproduct = b.idproduct
            WHERE b.idcart = :idcart AND dtremoved IS NULL", 
            [
                ":idcart" => $this->getidcart()
        ]);

        if(count($results) > 0){
            return $results[0];
        }else{
            return [];
        }
        
    }

    public function setFreight($nrzipcode)
    {
        $nrzipcode = str_replace("-", "", $nrzipcode);

        $total = $this->getProductsTotal();

        if($total["nrqtd"] > 0){
            
            if($total["vlheight"] < 2){
                $total["vlheight"] = 2;
            }

            if($total["vllength"] < 16){
                $total["vllength"] = 16;
            }

            $query = http_build_query([
                "nCdEmpresa" => "",
                "sDsSenha" => "",
                "nCdServico" => "40010",
                "sCepOrigem" => "50721110",
                "sCepDestino" => $nrzipcode,
                "nVlPeso" => $total["vlweight"],
                "nCdFormato" => 1,
                "nVlComprimento" => $total["vllength"],
                "nVlAltura" => $total["vlheight"],
                "nVlLargura" => $total["vlwidth"],
                "nVlDiametro" => "0",
                "sCdMaoPropria" => "S",
                "nVlValorDeclarado" => $total["vlprice"],
                "sCdAvisoRecebimento" => "S"
            ]);


            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$query);
            
            $result = $xml->Servicos->cServico;

            if($result->MsgErro != ""){
                Cart::setMsgErro($result->MsgErro);
            }else{
                Cart::clearMsgErro();
            }

            $this->setnrdays($result->PrazoEntrega);
            $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
            $this->setdeszipcode($nrzipcode);

            $this->save();

            return $result;

        }else{

        }
    }

    public static function formatValueToDecimal($value):float
    {
        $value = str_replace(".", "", $value);
        $value = str_replace(",", ".", $value);

        return $value;
    }

    public static function setMsgErro($msg)
    {
        $_SESSION[Cart::SESSION_ERROR] = $msg;
    }

    public static function getMsgErro()
    {
        $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : ""; 
        
        Cart::clearMsgErro();

        return $msg;
    }

    public static function clearMsgErro()
    {
        $_SESSION[Cart::SESSION_ERROR] = NULL;
    }

    public function updateFreight()
    {
        if($this->getdeszipcode() != ""){

            $this->setFreight($this->getdeszipcode());
            
        }
    }

    public function getCalculateTotal()
    {
        $total = $this->getProductsTotal();

        $this->setvlsubtotal($total["vlprice"]);
        $this->setvltotal($total["vlprice"] + $this->getvlfreight());
        
        $this->updateFreight();
    }

    public function getValues()
    {
        $this->getCalculateTotal();

        return parent::getValues();
    }
}
?>
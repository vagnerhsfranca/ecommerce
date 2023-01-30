<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;

class Address extends Model
{
    const ERROR = "addressError";

    public static function getCEP($nrcep)
    {
        $nrcep = str_replace("-", "", $nrcep);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "viacep.com.br/ws/$nrcep/json/");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $data = json_decode(curl_exec($curl), true);

        curl_close($curl);

        return $data;
    }

    function loadFromCEP($nrcep)
    {
        $data = Address::getCEP($nrcep);

        #Se(existir logradouro e ele for diferente de vazio)
        if(isset($data["logradouro"]) &&  $data["logradouro"]){
            $this->setdesaddress($data["logradouro"]);
            $this->setdescomplement($data["complemento"]);
            $this->setdesdistrict($data["bairro"]);
            $this->setdescity($data["localidade"]);
            $this->setdesstate($data["uf"]);
            $this->setdeszipcode(str_replace("-", "", $data["cep"]));
            $this->setdescountry("Brasil");
        }
    }

    public function save()
    {
        $connection = new Sql();

        $results = $connection->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :desnumber, :descomplement, :descity,:desstate, :descountry, :deszipcode, :desdistrict)", 
        array(
            ":idaddress" => $this->getidaddress(),
            ":desnumber" => $this->getdesnumber(),
            ":desaddress" => $this->getdesaddress(),
            ":descomplement" => $this->getdescomplement(),
            ":descity" => $this->getdescity(),
            ":desstate" => $this->getdesstate(),
            ":descountry" => $this->getdescountry(),
            ":deszipcode" => $this->getdeszipcode(),
            ":desdistrict" => $this->getdesdistrict(),
            ":idperson" => $this->getidperson()
        ));
    
        if(count($results) > 0){
            $this->setData($results[0]);
        }

    }

    public static function setMsgError($msg)
    {
        $_SESSION[Address::ERROR] = $msg;
    }

    public static function getMsgError()
    {
        $msg = (isset($_SESSION[Address::ERROR])) ? $_SESSION[Address::ERROR] : ""; 
        
        Address::clearMsgError();

        return $msg;
    }

    public static function clearMsgError()
    {
        $_SESSION[Address::ERROR] = NULL;
    }
}

?>
<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model\Mailer;

class User extends Model
{
    const SESSION = "userInSession";
    const SECRET = "HcodePhp7_secret";
    const SECRET_IV = "HcodePhp7_Secret_IV";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSucesss";

    public static function userInSession()
    {
        $user = new User();

        if(isset($_SESSION[User::SESSION]) && $_SESSION[User::SESSION]["iduser"] > 0) {
            $user->setData($_SESSION[User::SESSION]);
        }

        return $user;
    }

    public static function checkLogin($inadmin = true)
    {
        if(!isset($_SESSION[User::SESSION]) || !$_SESSION[User::SESSION] 
        || !(int)$_SESSION[User::SESSION]["iduser"] > 0){
            # Não está logado
            return false;
        } else {
            # Logado e tem acesso a administração
            if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === $inadmin){
                return true;
            # Logado, mas sem acesso a administração    
            }else if($inadmin === false){
                return true;
            }else{
                return false;
            }
        }
    }

    public static function login($login, $password)
    {
        $connection = new Sql();
        $results = $connection->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE deslogin = :deslogin", array(
            ":deslogin" => $login
        ));

        if(count($results) === 0)
        {
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }

        $data = $results[0];

        #var_dump($data);
        #var_dump(password_verify($password, $data["despassword"]));
        #exit;

        if(password_verify($password, $data["despassword"]))
        {
            $user = new User();
            
            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;

        }else{
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }
    }

    public static function verifyLogin($inadmin = true)
    {
        if(!User::checkLogin($inadmin))
        {
            if($inadmin){
                header("Location: /admin/login");
            } else{
                header("Location: /login");
            }
            
            exit;
        }
    }

    public static function logout()
    {
        unset($_SESSION[User::SESSION]);
    }

    public static function listAll()
    {
        $connection = new Sql();

        return $connection->select("SELECT * FROM tb_users u INNER JOIN tb_persons p USING(idperson) 
            ORDER BY p.desperson");    
    }

    public function save()
    {
        $connection = new Sql();

        $results = $connection->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
        array(
            ":desperson" => $this->getdesperson(),
            ":deslogin" => $this->getdeslogin(), 
            ":despassword" => User::getPasswordHash($this->getdespassword()), 
            ":desemail" => $this->getdesemail(), 
            ":nrphone" => $this->getnrphone(), 
            ":inadmin" => $this->getinadmin()
        ));

        $this->setData($results[0]);
    }

    public function findById($iduser)
    {
        $connection = new Sql();
        
        $results = $connection->select("SELECT * FROM tb_users u INNER JOIN tb_persons p USING(idperson) WHERE u.iduser = :iduser;", array(
            ":iduser" => $iduser
        ));
        
        $this->setData($results[0]);
    }

    public function update()
    {
        $connection = new Sql();

        $results = $connection->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
        array(
            ":iduser" => $this->getiduser(),
            ":desperson" => $this->getdesperson(),
            ":deslogin" => $this->getdeslogin(), 
            ":despassword" => User::getPasswordHash($this->getdespassword()), 
            ":desemail" => $this->getdesemail(), 
            ":nrphone" => $this->getnrphone(), 
            ":inadmin" => $this->getinadmin()
        ));
        
        $this->setData($results[0]);
    }

    public function delete()
    {
        $connection = new Sql();

        $connection->select("CALL sp_users_delete (:iduser)",
        array(
            ":iduser" => $this->getiduser()
        ));
    }

    public static function getForgotEmail($email, $inadmin = true)
    {
        $connection = new Sql();

        $results = $connection->select("SELECT * FROM tb_persons p INNER JOIN tb_users u USING (idperson) WHERE P.desemail = :email", 
        array(
            ":email" => $email
        ));

        if(count($results) === 0){
            throw new \Exception("Não foi possível recuperar a senha.");
        }else{
            $data = $results[0];

            $results02 = $connection->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)",
            array(
                ":iduser" => $data["iduser"],
                ":desip" => $_SERVER["REMOTE_ADDR"]
            ));

            if(count($results02) === 0){
                throw new \Exception("Não foi possível recuperar a senha.");
            }else{
                $dataRecovery = $results02[0];

                $code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

                $code = base64_encode($code);

                if($inadmin === true){
                    $link = "http://www.ecommerce.com.br/admin/forgot/reset?code=$code";
                }else{
                    $link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
                }

                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha da Vagner França Store", "forgot", 
                array(
                    "name" => $data["desperson"],
                    "link" => $link
                ));

                $mailer->send();

                return $link;
            }
        }
    }

    public static function validForgotDecrypt($code)
    {
        $code = base64_decode($code);

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

        $connection = new Sql();

        $results = $connection->select(
        "SELECT *
        FROM tb_userspasswordsrecoveries a
        INNER JOIN tb_users b USING(iduser)
        INNER JOIN tb_persons c USING(idperson)
        WHERE
            a.idrecovery = :idrecovery
            AND
            a.dtrecovery IS NULL
            AND
            DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
        ", array(
            ":idrecovery"=>$idrecovery
        ));

        if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else
		{
			return $results[0];
		}
    }
    
    public static function setFogotUsed($idrecovery)
	{

		$connection = new Sql();

		$connection->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));

	}

	public function setPassword($password)
	{

		$connection = new Sql();

		$connection->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));

	}

    public static function setMsgErro($msg)
    {
        $_SESSION[USER::ERROR] = $msg;
    }

    public static function getMsgErro()
    {
        $msg = (isset($_SESSION[USER::ERROR])) ? $_SESSION[USER::ERROR] : ""; 
        
        User::clearMsgErro();

        return $msg;
    }

    public static function clearMsgErro()
    {
        $_SESSION[User::ERROR] = NULL;
    }

    public static function getPasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT, [
            "cost" => 12
        ]);
    }

    public static function setRegisterError($msg)
    {
        $_SESSION[User::ERROR_REGISTER] = $msg;
    }

    public static function getRegisterError()
    {
        $error = isset($_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

        User::clearRegisterError();
        
        return $error;
    }

    public static function clearRegisterError()
    {
        $_SESSION[User::ERROR_REGISTER] = NULL;
    }

    public static function checkExistLogin($desemail)
    {
        $connection = new Sql();
        $results = $connection->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE deslogin = :desemail OR desemail = :desemail", array(
            ":desemail" => $desemail
        ));

        return (count($results) > 0);
    }

    public static function setMsgSucess($msg)
    {
        $_SESSION[USER::SUCCESS] = $msg;
    }

    public static function getMsgSucess()
    {
        $msg = (isset($_SESSION[USER::SUCCESS])) ? $_SESSION[USER::SUCCESS] : ""; 
        
        User::clearMsgSucess();

        return $msg;
    }

    public static function clearMsgSucess()
    {
        $_SESSION[User::SUCCESS] = NULL;
    }

    public function getOrders()
    {
        $connection = new Sql();

        $results = $connection->select("SELECT * FROM tb_orders a 
        INNER JOIN  tb_ordersstatus b USING (idstatus)
        INNER JOIN tb_carts c USING (idcart)
        INNER JOIN tb_users d ON a.iduser = d.iduser
        INNER JOIN tb_addresses e USING (idaddress)
        INNER JOIN tb_persons f ON d.idperson = f.idperson
        WHERE a.iduser = :iduser",
        [
            ":iduser" => $this->getiduser()
        ]);

        return $results;
    }

    public static function getUsersPage($page = 1, $itemsPerPage = 18)
    {
        $start = ($page - 1) * $itemsPerPage;

        $connection = new Sql();

        $results  = $connection->select(
            "SELECT SQL_CALC_FOUND_ROWS * FROM tb_users a 
            INNER JOIN tb_persons b USING(idperson)
            ORDER BY b.desperson
            LIMIT $start, $itemsPerPage");

        $resultTotal = $connection->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            "data" => $results,
            "total" => (int)$resultTotal[0]["nrtotal"],
            "pages" => ceil((int)$resultTotal[0]["nrtotal"] / $itemsPerPage)
        ];
        
    }
    
    public static function getUsersPageSearch($search, $page = 1, $itemsPerPage = 18)
    {
        $start = ($page - 1) * $itemsPerPage;

        $connection = new Sql();

        $results  = $connection->select(
            "SELECT SQL_CALC_FOUND_ROWS * FROM tb_users a 
            INNER JOIN tb_persons b USING(idperson)
            WHERE a.deslogin LIKE :search
            OR b.desperson LIKE :search
            OR b.desemail LIKE :search
            ORDER BY b.desperson
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
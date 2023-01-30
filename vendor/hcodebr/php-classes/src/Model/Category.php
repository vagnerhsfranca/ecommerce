<?php

namespace Hcode\Model;

use Hcode\DB\Sql;

class Category extends Model
{

    public static function listAll()
    {
        $connection = new Sql();

        return $connection->select("SELECT * FROM tb_categories ORDER BY descategory");    
    }

    public function save()
    {
        $connection = new Sql();

        $results = $connection->select("CALL sp_categories_save(:idcategory, :descategory)", 
        array(
            ":idcategory" => $this->getidcategory(),
            ":descategory" => $this->getdescategory()
        ));

        $this->setData($results[0]);

        Category::fileUpdate();
    }

    public function findById($idcategory)
    {
        $connection = new Sql();
        
        $results = $connection->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory;", array(
            ":idcategory" => $idcategory
        ));

        $this->setData($results[0]);
    }

    public function delete()
    {
        $connection = new Sql();

        $connection->select("DELETE FROM tb_categories WHERE idcategory = :idcategory",
        array(
            ":idcategory" => $this->getidcategory()
        ));

        Category::fileUpdate();
    }

    public static function fileUpdate()
    {
        $categories = Category::listAll();

        $html = [];

        foreach($categories as $row){
            array_push($html, '<li><a href="/categories/' . $row['idcategory'] . '">'.$row['descategory'].'</a></li>');
        }

        file_put_contents($_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR .         "categories-menu.html", implode('', $html));
    }

    public function getProducts($related = true)
    {
        $connection = new Sql();

        if($related === true){
            return $connection->select("SELECT * FROM tb_products 
            WHERE idproduct IN (
                SELECT a.idproduct FROM tb_products a 
                INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct 
                WHERE b.idcategory = :idcategory
            );", 
            [
                ":idcategory" => $this->getidcategory()
            ]);
        }else{
            return $connection->select("SELECT * FROM tb_products 
            WHERE idproduct NOT IN (
                SELECT a.idproduct FROM tb_products a 
                INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct 
                WHERE b.idcategory = :idcategory
            );", 
            [
                ":idcategory" => $this->getidcategory()
            ]);
        }
    }

    public function addProduct(Product $product)
    {
        $connection = new Sql();

        $connection->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)",[
            ":idcategory" => $this->getidcategory(),
            ":idproduct" => $product->getidproduct()
        ]);
    }

    public function removeProduct(Product $product)
    {
        $connection = new Sql();

        $connection->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct =  :idproduct",[
            ":idcategory" => $this->getidcategory(),
            ":idproduct" => $product->getidproduct()
        ]);
    }

    public static function getCategoryPage($page = 1, $itemsPerPage = 18)
    {
        $start = ($page - 1) * $itemsPerPage;

        $connection = new Sql();

        $results  = $connection->select(
            "SELECT SQL_CALC_FOUND_ROWS * FROM tb_categories
            ORDER BY descategory
            LIMIT $start, $itemsPerPage");

        $resultTotal = $connection->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            "data" => $results,
            "total" => (int)$resultTotal[0]["nrtotal"],
            "pages" => ceil((int)$resultTotal[0]["nrtotal"] / $itemsPerPage)
        ];
        
    }
    
    public static function getCategoryPageSearch($search, $page = 1, $itemsPerPage = 18)
    {
        $start = ($page - 1) * $itemsPerPage;

        $connection = new Sql();

        $results  = $connection->select(
            "SELECT SQL_CALC_FOUND_ROWS * FROM tb_categories
            WHERE descategory LIKE :search
            ORDER BY descategory
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
<?php

namespace Hcode\Model;
use Hcode\DB\Sql;

class OrderStatus extends Model
{

    const EM_ABERTO = 1;
    const AGUARDANDO_PAGAMENTO = 2;
    const PAGO = 3;
    const ENTREGUE = 4;
    
    public static function listAll()
    {
        $connection = new Sql();

        return $connection->select("SELECT * FROM tb_ordersstatus");
    }
}
?>
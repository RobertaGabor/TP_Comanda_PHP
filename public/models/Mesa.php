<?php


class mesa
{
    public $id;
    public $codigo;
    public $estado;
    public $fecha_de_alta;
    public $fecha_de_baja;
    public $fecha_de_modificacion;

    public static function constructAux($id,$codigo,$estado,$fecha_alta,$fecha_baja,$fecha_mod)
    {
        $msa=new Mesa();
        $msa->id=$id;
        $msa->codigo=$codigo;
        $msa->estado=$estado;
        $msa->fecha_de_alta=$fecha_alta;
        $msa->fecha_de_modificacion=$fecha_mod;
        $msa->fecha_de_baja=$fecha_baja;

        return $msa;
    }
    public function modificarEstadoMesa()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $fecha = new DateTime(date("d-m-Y"));
        
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET estado = :estado , fecha_de_modificacion= :fecha_de_modificacion WHERE codigo = :codigo");
        $consulta->bindValue(':fecha_de_modificacion', date_format($fecha, 'Y-m-d H:i:s'), PDO::PARAM_STR);
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado',$this->estado, PDO::PARAM_STR);
        $consulta->execute();
    }
    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo, estado, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }
    public static function obtenerPorEstado($estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo, estado, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM mesas WHERE estado=:estado");
        $consulta->bindValue(':estado',$estado,PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }
    public static function obtenerMesa($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo, estado, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM mesas WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }
    public function crearMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigo, estado, fecha_de_alta) VALUES (:codigo, :estado, :fecha_de_alta)");
        
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_de_alta', $this->fecha_de_alta, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    public static function borrarMesa($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET estado= :estado, fecha_de_baja = :fecha_de_baja WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y H:i:s"));
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':estado', "Cerrada", PDO::PARAM_STR);
        $consulta->bindValue(':fecha_de_baja', date_format($fecha, 'Y-m-d H:i:s'),PDO::PARAM_STR);
        $consulta->execute();
    }
    public static function obtenerMesaPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo, estado, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM mesas WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }
    public function AsignarEstado($numero)
    {
        $estado=Mesa::getEstado($numero);
        $this->estado=$estado;
    }
    private static function getEstado($estado){
            switch($estado){
                case 1:
                    $nuevo = "Sin pedir";
                    break;
                case 2:
                    $nuevo = "Con cliente esperando pedido";                  
                    break;
                case 3:
                    $nuevo = "Con cliente comiendo";
                    break;
                case 4:
                    $nuevo = "Con cliente pagando";
                    break;
                case 5:
                    $nuevo= "Lista para cerrar";
                    break;
                case 6:
                    $nuevo= "Cerrada";
                    break;
            }
        
        return $nuevo;
    }
    public static function MasUsada()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_mesa FROM clientes GROUP BY codigo_mesa ORDER BY count(*) DESC");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Cliente');
    }
    public static function MasFacturo()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_mesa FROM pedidos p INNER JOIN factura f on p.id=f.id_pedido GROUP BY f.id_pedido ORDER BY sum(f.total) DESC");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }
    public static function MayorGasto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_mesa FROM pedidos p INNER JOIN factura f on p.id=f.id_pedido GROUP BY f.id_pedido ORDER BY max(f.total) DESC");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }
    public static function MejorPuntuacion()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_mesa FROM clientes c INNER JOIN encuesta e on c.id=e.id_cliente GROUP BY e.id_cliente ORDER BY sum(e.Mesa) DESC");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }
}


?>
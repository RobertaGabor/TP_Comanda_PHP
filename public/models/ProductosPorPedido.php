<?php

class ProductosPorPedido{

    public $id;	
    public $id_pedido;	
    public $id_producto;	
    public $cantidad;	
    public $sector;	
    public $estado;	
    public $duracion_estimada;	
    public $fecha_de_alta;	
    public $fecha_de_baja;	
    public $fecha_de_modificacion;

    public static function borrarProductoPedido($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos_porpedido SET estado= :estado, fecha_de_baja = :fecha_de_baja WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y H:i:s"));
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':estado', "Cancelado", PDO::PARAM_STR);
        $consulta->bindValue(':fecha_de_baja', date_format($fecha, 'Y-m-d H:i:s'),PDO::PARAM_STR);
        $consulta->execute();
    }

    public function crearProductoPorPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos_porpedido (id_pedido, id_producto, cantidad, sector, estado, fecha_de_alta) VALUES (:id_pedido, :id_producto, :cantidad, :sector, :estado, :fecha_de_alta)");
        
        $consulta->bindValue(':id_pedido', $this->id_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':id_producto', $this->id_producto, PDO::PARAM_INT);
        $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_INT);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_de_alta', $this->fecha_de_alta, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    
    public static function obtenerProductosPorPedido($id_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, id_pedido, id_producto, cantidad,	sector,	estado,	duracion_estimada, fecha_de_alta, fecha_de_baja, fecha_de_modificacion FROM productos_porpedido WHERE id_pedido = :id_pedido");
        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'ProductosPorPedido');
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, id_pedido, id_producto, cantidad,	sector,	estado,	duracion_estimada, fecha_de_alta, fecha_de_baja, fecha_de_modificacion FROM productos_porpedido");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'ProductosPorPedido');
    }
    public static function obtenerPendientes()
    {
        $estado="Pendiente";
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, id_pedido, id_producto, cantidad,	sector,	estado,	duracion_estimada, fecha_de_alta, fecha_de_baja, fecha_de_modificacion FROM productos_porpedido WHERE estado=:estado");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);

        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'ProductosPorPedido');
    }
    public static function obtenerProductoId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, id_pedido, id_producto, cantidad,	sector,	estado,	duracion_estimada, fecha_de_alta, fecha_de_baja, fecha_de_modificacion FROM productos_porpedido WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('ProductosPorPedido');
    }
    public function modificarProducto()
    {

        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $fecha = new DateTime(date("Y-m-d H:i:s"));
        
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos_porpedido SET duracion_estimada=:duracion_estimada, estado = :estado , fecha_de_modificacion= :fecha_de_modificacion WHERE id = :id");
        $consulta->bindValue(':fecha_de_modificacion', date_format($fecha, 'Y-m-d H:i:s'),PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':duracion_estimada', $this->duracion_estimada, PDO::PARAM_INT);
        $consulta->execute();
        
    }
    public function modificarCantidad()
    {

        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $fecha = new DateTime(date("Y-m-d H:i:s"));
        
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos_porpedido SET cantidad = :cantidad , fecha_de_modificacion= :fecha_de_modificacion WHERE id = :id");
        $consulta->bindValue(':fecha_de_modificacion', date_format($fecha, 'Y-m-d H:i:s'),PDO::PARAM_STR);
        $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_INT);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
        
    }
    public static function MasVendido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT sum(cantidad),id_producto FROM productos_porpedido GROUP BY id_producto ORDER BY SUM(cantidad) DESC");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'ProductosPorPedido');
    }
}

?>
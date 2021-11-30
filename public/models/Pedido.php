<?php

class Pedido
{
    public $id;
    public $codigo_mesa;
    public $codigo;
    public $estado;
    public $foto;
    public $duracion_estimada;
    public $duracion_final;
    public $fecha_de_alta;
    public $fecha_de_modificacion;
    public $fecha_de_baja;

    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (codigo_mesa, codigo, estado, fecha_de_alta) VALUES (:codigo_mesa, :codigo, :estado, :fecha_de_alta)");
        
        $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_de_alta', $this->fecha_de_alta, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id,codigo_mesa,duracion_estimada, duracion_final, codigo, estado, foto, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }
    public static function obtenerPorFechas($fechaA,$fechaB)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id,codigo_mesa,duracion_estimada, duracion_final, codigo, estado, foto, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM pedidos WHERE fecha_de_alta BETWEEN :fechaA and :fechaB");
        $consulta->bindValue(':fechaA', $fechaA, PDO::PARAM_STR);
        $consulta->bindValue(':fechaB', $fechaB, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }
    public static function obtenerPendientes()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id,codigo_mesa,duracion_estimada, duracion_final, codigo, estado, foto, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM pedidos WHERE estado= :estado");
        $consulta->bindValue(':estado', "Pendiente", PDO::PARAM_STR);

        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerListos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id,codigo_mesa,duracion_estimada, duracion_final, codigo, estado, foto, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM pedidos WHERE estado= :estado");
        $consulta->bindValue(':estado', "Listo", PDO::PARAM_STR);

        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedidoId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id,codigo_mesa,duracion_estimada, duracion_final, codigo, estado, foto, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }
    public static function obtenerPedidoCodigo($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_mesa, codigo,duracion_estimada, duracion_final, estado, foto, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM pedidos WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }
   
    public function modificarEstadoPedido()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $fecha = new DateTime(date("Y-m-d H:i:s"));
        
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado , fecha_de_modificacion= :fecha_de_modificacion WHERE id = :id");
        $consulta->bindValue(':fecha_de_modificacion', date_format($fecha, 'Y-m-d H:i:s'),PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }
    public function agregarFotoPedido()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $fecha = new DateTime(date("Y-m-d H:i:s"));
        
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET foto = :foto , fecha_de_modificacion= :fecha_de_modificacion WHERE id = :id");
        $consulta->bindValue(':fecha_de_modificacion', date_format($fecha, 'Y-m-d H:i:s'),PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public function modificarTiempoPedido()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $fecha = new DateTime(date("Y-m-d H:i:s"));
        
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET duracion_estimada = :duracion_estimada , fecha_de_modificacion= :fecha_de_modificacion WHERE id = :id");
        $consulta->bindValue(':fecha_de_modificacion', date_format($fecha, 'Y-m-d H:i:s'),PDO::PARAM_STR);
        $consulta->bindValue(':duracion_estimada', $this->duracion_estimada, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public function AsignoTiempoFinal()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $fecha = new DateTime(date("Y-m-d H:i:s"));
        
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET duracion_final = :duracion_final , fecha_de_modificacion= :fecha_de_modificacion WHERE id = :id");
        $consulta->bindValue(':fecha_de_modificacion', date_format($fecha, 'Y-m-d H:i:s'),PDO::PARAM_STR);
        $consulta->bindValue(':duracion_final', $this->duracion_final, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarPedido($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado= :estado, fecha_de_baja = :fecha_de_baja WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y H:i:s"));
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':estado', "Cancelado", PDO::PARAM_STR);
        $consulta->bindValue(':fecha_de_baja', date_format($fecha, 'Y-m-d H:i:s'),PDO::PARAM_STR);
        $consulta->execute();
    }
    public static function obtenerMesaPorPedido($id_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_mesa FROM pedidos WHERE id = :id_pedido");
        $consulta->bindValue(':id_pedido', $id_pedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function obtenerTiempoPorPedido($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT sum(duracion_estimada) FROM productos_porpedido WHERE id_pedido = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_NUM);
    }
    public static function traerPrecioTotal($id_pedido)
    {
      $total=0;
      $productosPedidos=ProductosPorPedido::obtenerProductosPorPedido($id_pedido);
      foreach ($productosPedidos as $key) {
        $producto=Producto::obtenerProducto($key->id_producto);
        $total+=$key->cantidad*$producto->precio;
      }
      return $total;
    }
    public static function NoEntregadosATiempo()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id,codigo_mesa,duracion_estimada, duracion_final, codigo, estado, foto, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM pedidos WHERE duracion_estimada<duracion_final");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }
    public static function Cancelados()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id,codigo_mesa,duracion_estimada, duracion_final, codigo, estado, foto, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM pedidos WHERE estado=:estado");
        $consulta->bindValue(':estado',"Cancelado", PDO::PARAM_STR);

        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function getEstado($num)
    {
        switch($num)
        {
            case 1:
                return "Pendiente";
            case 2:
                return "Listo";
                break;
            case 3:
                return "Entregado";
                break;
        }
    }
}


?>
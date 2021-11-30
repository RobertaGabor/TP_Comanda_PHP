<?php

class Producto
{
    public $id;
    public $nombre;
    public $precio;
    public $sector;
    public $fecha_de_alta;
    public $fecha_de_baja;
    public $fecha_de_modificacion;

    public static function constructAux($id,$nombre,$precio,$sector,$fecha_alta,$fecha_baja,$fecha_mod)
    {
        $prod=new Producto();
        $prod->id=$id;
        $prod->nombre=$nombre;
        $prod->precio=$precio;
        $prod->sector=$sector;
        $prod->fecha_de_alta=$fecha_alta;
        $prod->fecha_de_modificacion=$fecha_mod;
        $prod->fecha_de_baja=$fecha_baja;

        return $prod;
    }
    public function crearProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre, precio, sector, fecha_de_alta) VALUES (:nombre, :precio, :sector, :fecha_de_alta)");
        

        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_de_alta', $this->fecha_de_alta, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, precio, sector, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM productos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerProducto($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, precio, sector, fecha_de_alta, fecha_de_modificacion, fecha_de_baja  FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }


    public function modificarPrecioProducto()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $fecha = new DateTime(date("Y-m-d H:i:s"));
        
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET precio = :precio , fecha_de_modificacion= :fecha_de_modificacion WHERE id = :id");
        $consulta->bindValue(':fecha_de_modificacion', date_format($fecha, 'Y-m-d H:i:s'),PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarProducto($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET fecha_de_baja = :fecha_de_baja WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_de_baja', date_format($fecha, 'Y-m-d H:i:s'),PDO::PARAM_STR);
        $consulta->execute();
    }

}


?>
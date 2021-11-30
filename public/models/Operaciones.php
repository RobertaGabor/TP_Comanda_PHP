<?php
require_once './models/Usuario.php';

class Operacion
{
    public $id;
    public $id_usuario;
    public $sector;
    public $operacion;
    public $fecha;


    public static function auxConstruct($id_usuario,$sector,$operacion)
    {
        $new=new Operacion();
        $new->id_usuario=$id_usuario;
        $new->sector=Usuario::getSector($sector);
        $new->operacion=$operacion;
        $new->fecha=date("Y-m-d h:i:s");
        return $new;
    }
    public function cargarUna()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO operaciones (id_usuario,sector,operacion, fecha) VALUES (:id_usuario,:sector,:operacion, :fecha)");
        
        $consulta->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':operacion', $this->operacion, PDO::PARAM_STR);
        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function CantidadOperacionPorSector($sector)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT count(*) FROM operaciones WHERE sector = :sector");
        $consulta->bindValue(':sector', $sector, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_NUM);
    }
    public static function TraerConsulta_CantidadOperacionPorSectorListadas($sector)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, id_usuario,sector,operacion, fecha FROM operaciones WHERE sector=:sector");
        $consulta->bindValue(':sector', $sector, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Operacion');
    }
    public static function CantidadOperacionPorEmpleado($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, id_usuario,sector,operacion, fecha FROM operaciones WHERE id_usuario=:id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Operacion');
    }
    
}


?>
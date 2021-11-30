<?php

class Encuesta{

    public $id;
    public $id_cliente;
    public $Mesa;
    public $Mozo;
    public $Restaurante;
    public $Experiencia;
    public $Cocinero;
    public $fecha;

    public function crearEncuesta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO encuesta (id_cliente,Mesa,Mozo,Restaurante,Experiencia,Cocinero,fecha) VALUES (:id_cliente,:Mesa,:Mozo,:Restaurante,:Experiencia,:Cocinero,:fecha)");

        $consulta->bindValue(':Experiencia', $this->Experiencia, PDO::PARAM_STR);
        $consulta->bindValue(':id_cliente', $this->id_cliente, PDO::PARAM_INT);
        $consulta->bindValue(':Mesa', $this->Mesa, PDO::PARAM_INT);
        $consulta->bindValue(':Mozo', $this->Mozo, PDO::PARAM_INT);
        $consulta->bindValue(':Cocinero', $this->Cocinero, PDO::PARAM_INT);
        $consulta->bindValue(':Restaurante', $this->Restaurante, PDO::PARAM_INT);
        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

} 

?>
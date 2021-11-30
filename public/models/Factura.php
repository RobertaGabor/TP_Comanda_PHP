<?php

class Factura{

    public $id;
    public $id_pedido;
    public $fecha;
    public $total;

    public function crearFactura()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO factura (id_pedido, total, fecha) VALUES (:id_pedido, :total, :fecha)");
        
        $consulta->bindValue(':id_pedido', $this->id_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':total', $this->total, PDO::PARAM_STR);
        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function FacturadoEntreFechas($dateA,$dateB)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, id_pedido, total, fecha FROM factura WHERE fecha BETWEEN :dateA and :dateB");
        $consulta->bindValue(':dateA', $dateA,PDO::PARAM_STR);
        $consulta->bindValue(':dateB', $dateB,PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Factura');
    }


}



?>
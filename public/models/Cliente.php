<?php
include_once("./TCPDF/tcpdf.php");
class Cliente extends TCPDF
{
    public $id;
    public $nombre;
    public $apellido;
    public $fecha_de_alta;
    public $fecha_de_baja;
    public $fecha_de_modificacion;
    public $codigo_mesa;

    public static function PDFClientes($todos)
    {
      $pdf = new TCPDF('P', 'cm','letter');
      $pdf->SetAuthor("Comanda", true);
      $pdf->SetFont('', '', 6);
      $pdf->SetTitle("Documento de clientes", true);
      $pdf->AddPage();
  
      foreach($todos as $key){
        $pdf->Cell(0, 1, "id: " . $key->id . " - " . "nombre: " .$key->nombre . " - " . "apellido: " . $key->apellido . " - " . "mesa: " .  $key->codigo_mesa . " - " . "Fecha de alta: " . $key->fecha_de_alta . " - " . "Fecha de baja: " . $key->fecha_de_baja . " - " . "Fecha de modificacion: " . $key->fecha_de_modificacion, 0, 1);
      }

      return $pdf;
    }

    public function crearCliente()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO clientes (nombre, apellido, fecha_de_alta) VALUES (:nombre, :apellido, :fecha_de_alta)");

        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_de_alta', $this->fecha_de_alta, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    public static function obtenerCliente($nombre)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, apellido, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM clientes WHERE nombre = :nombre");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Cliente');
    }
    public static function obtenerClientePorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, apellido, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM clientes WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Cliente');
    }
    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, apellido, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM clientes");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Cliente');
    }
    public static function borrarCliente($nombre)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE clientes SET fecha_de_baja = :fecha_de_baja WHERE nombre = :nombre");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_de_baja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }

    public function DarCodigoMesa($mesa)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE clientes SET codigo_mesa = :codigo_mesa WHERE id = :id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':codigo_mesa', $mesa, PDO::PARAM_INT);
        $consulta->execute();
    }
}



?>
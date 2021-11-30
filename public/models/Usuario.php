<?php

class Usuario
{
    public $id;
    public $usuario;
    public $clave;
    public $nombre;
    public $apellido;
    public $tipo;
    public $estado;
    public $fecha_de_alta;
    public $fecha_de_modificacion;
    public $fecha_de_baja;

    public static function constructAux($id,$usuario,$clave,$nombre,$apellido,$tipo,$estado,$fecha_alta,$fecha_mod,$fecha_baja)
    {
        $usr=new Usuario();
        $usr->id=$id;
        $usr->usuario=$usuario;
        $usr->clave=$clave;
        $usr->nombre=$nombre;
        $usr->apellido=$apellido;
        $usr->tipo=$tipo;
        $usr->estado=$estado;
        $usr->fecha_de_alta=$fecha_alta;
        $usr->fecha_de_modificacion=$fecha_mod;
        $usr->fecha_de_baja=$fecha_baja;


        return $usr;
    }
    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (usuario, clave, nombre, apellido, tipo, estado, fecha_de_alta) VALUES (:usuario, :clave, :nombre, :apellido, :tipo, :estado, :fecha_de_alta)");
        
        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);
        $consulta->bindValue(':clave', $claveHash);

        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_de_alta', $this->fecha_de_alta, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave, nombre, apellido, tipo, estado, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerUsuario($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave, nombre, apellido, tipo, estado, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM usuarios WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

   
    public function modificarUsuario()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $fecha = new DateTime(date("d-m-Y"));
        
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET clave = :clave , fecha_de_modificacion= :fecha_de_modificacion WHERE usuario = :usuario");
        $consulta->bindValue(':fecha_de_modificacion', date_format($fecha, 'Y-m-d H:i:s'), PDO::PARAM_STR);
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $claveHash=password_hash($this->clave, PASSWORD_DEFAULT);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->execute();
    }

    public function modificarEstadoUsuario()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $fecha = new DateTime(date("d-m-Y"));

        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET estado = :estado, fecha_de_modificacion= :fecha_de_modificacion WHERE usuario = :usuario");
        $consulta->bindValue(':fecha_de_modificacion', date_format($fecha, 'Y-m-d H:i:s'),PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function borrarUsuario($usuario)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fecha_de_baja = :fecha_de_baja WHERE usuario = :usuario");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_de_baja', date_format($fecha, 'Y-m-d H:i:s'),PDO::PARAM_STR);
        $consulta->execute();
    }

    public function Equals($user)
    {
        #el $this esta sin hashear el $user esta hasheado(from db)

        if($this->usuario==$user->usuario && password_verify($this->clave,$user->clave))
        {
            return true;
        }
        return false;
    }

    public function loginBool()
    {
        $user=Usuario::obtenerUsuario($this->usuario);
        if($user!=null)
        {
            if($this->Equals($user))
            {
                return 1;
            }
            else
            {
                return 0;
            }
        }
        else
        {
            return -1;
        }
    }

    public static function getSector($tipo)
    {
        switch($tipo)
        {
          case "bartender":
            $sector="barra tragos";
            break;
          case "cocinero":
            $sector="cocina";
            break;
          case "cervecero":
            $sector="barra cerveza";
            break;
          case "mozo":
            $sector="mozo";
            break;
          case "socio":
            $sector="socio";
            break;
        }
        return $sector;
    }
    public static function FechaDeIngreso($dateA,$dateB)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave, nombre, apellido, tipo, estado, fecha_de_alta, fecha_de_modificacion, fecha_de_baja FROM usuarios WHERE fecha_de_alta BETWEEN :dateA and :dateB");
        $consulta->bindValue(':dateA', $dateA,PDO::PARAM_STR);
        $consulta->bindValue(':dateB', $dateB,PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

}
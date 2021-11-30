<?php
require_once './models/Usuario.php';
require_once './models/Archivos.php';
require_once './interfaces/IApiUsable.php';
include_once './middlewares/AutentificadorJWT.php';
require_once './models/Operaciones.php';


class UsuarioController extends Usuario implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        #compilo datos
        $fecha_de_alta= date_format(new DateTime(date("d-m-Y")), 'Y-m-d H:i:s');
        $nombre=$parametros['nombre'];
        $apellido=$parametros['apellido'];
        $tipo=$parametros['tipo'];
        $estado="Activo";
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];

        if(isset($nombre)&&$nombre!=null&&isset($apellido)&&$apellido!=null&&isset($tipo)&&$tipo!=null&&isset($estado)&&$estado!=null&&isset($usuario)&&$usuario!=null&&isset($clave)&&$clave!=null)
        {
            #Creamos el usuario
            $usr = new Usuario();
            $usr->usuario = $usuario;
            $usr->clave = $clave;
            $usr->nombre=$nombre;
            $usr->apellido=$apellido;
            $usr->tipo=$tipo;
            $usr->estado=$estado;
            $usr->fecha_de_alta=$fecha_de_alta;


            #lo guardo en la base
            if($usr!=null)
            {
              try
              {
                $usr->crearUsuario();
              
                $userF=Archivos::findUserHeader($request);
                
                $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Usuario dado de alta");
                $op->CargarUna();
                $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
              }
              catch(e)
              {
                $payload = json_encode(array("mensaje" => "Usuario no se pudo crear"));
              }
              
              
            }
            else
            {
              $payload = json_encode(array("mensaje" => "Usuario no se pudo crear"));
            }
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Missing fields"));
        }
      

        

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por nombre de usuario
        $usr = $args['usuario'];
        $usuario = Usuario::obtenerUsuario($usr);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
     
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function SuspenderUno($request, $response, $args)
    {
      $usuario=$args['usuario'];
      $usuario = Usuario::obtenerUsuario($usuario);
      

      if($usuario!=null)
      {
        $usuario->estado="Suspendido";
        $usuario->modificarEstadoUsuario();
        $userF=Archivos::findUserHeader($request);
            
        $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Usuario Suspendido");
        $op->CargarUna();
        $payload = json_encode(array("mensaje" => "Usuario dado de baja con exito"));
      }
      else
      {
        $payload = json_encode(array("mensaje" => "Usuario no se ha podido encontrar"));      
      }


      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
    public function ReactivarUno($request, $response, $args)
    {
      $usuario=$args['usuario'];
      $usuario = Usuario::obtenerUsuario($usuario);

      if($usuario!=null)
      {
        $usuario->estado="Activo";


        if($usuario!=null)
        {
          $usuario->modificarEstadoUsuario();
          $userF=Archivos::findUserHeader($request);
              
          $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Usuario reactivado");
          $op->CargarUna();
          $payload = json_encode(array("mensaje" => "Usuario dado de alta con exito"));
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Usuario no se ha podido dar de alta con exito"));
        
        }        
      }
      else
      {
        $payload = json_encode(array("mensaje" => "Usuario no se ha podido encontrar"));
      }


      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function Logearse($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $nombre = $parametros['usuario'];
      $clave = $parametros['clave'];

      /*pregunto si hay algun usuario con ese nombre y clave*/
      $usr = new Usuario();

      if($usr!=null&&isset($nombre)&&$nombre!=null&&isset($clave)&&$clave!=null)
      {
        
        $usr->clave=$clave;
        $usr->usuario=$nombre; 

        $returned=$usr->loginBool();
        if($returned==1)
        {
          $usr=Usuario::obtenerUsuario($nombre);
          if($usr->estado=="Activo")
          {
            $usr->clave="";
            $token = AutentificadorJWT::CrearToken(array($usr));
            $mensaje = json_encode(array('jwt' => $token));            
          }
          else
          {
            $mensaje="Empleado dado de baja";
          }

        }
        elseif ($returned==0) {
          $mensaje="Contraseña incorrecta";
        }
        else
        {
          $mensaje="No se encuentra registrado";
        }

      }
      else
      {
        $mensaje="Missing fields";
      }
      $payload = $mensaje;
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['usuario'];
        $clave_vieja = $parametros['clave_vieja'];
        $clave_nueva = $parametros['clave_nueva'];

        if(isset($nombre)&&$nombre!=null&&isset($clave_nueva)&&$clave_nueva!=null&&isset($clave_vieja)&&$clave_vieja!=null)
        {
          $usr = new Usuario();
          $usr->usuario = $nombre;
          $usr->clave = $clave_vieja; 
  
          $aux=Usuario::obtenerUsuario($nombre);
          if($aux!=null)
          {
            #si existe un usuario con ese usuario y clave que lo cambie --> MIDDLEWARE?
            $usr->clave=$clave_nueva; 
            $usr->modificarUsuario();
            $userF=Archivos::findUserHeader($request);
                
            $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Usuario modificado");
            $op->CargarUna();
    
            $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));            
          }
          else
          {
            $payload = json_encode(array("mensaje" => "Usuario no se encuentra"));
          }
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Missing fields"));
        }
      

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody(); #xq se pasa por raw
        $usuario = $parametros['usuario'];
       
        if(isset($usuario)&&$usuario!=null)
        {
          $aux=Usuario::obtenerUsuario($usuario);
          if($aux!=null)
          {
            Usuario::borrarUsuario($usuario);
            $userF=Archivos::findUserHeader($request);
                
            $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Usuario dado de baja");
            $op->CargarUna();
            $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));             
          }
          else
          {
            $payload = json_encode(array("mensaje" => "Usuario no se encuentra"));
          }
         
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Missing fields"));
        }


        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function GuardarCSV($request, $response, $args)
    {
      $todos=Usuario::obtenerTodos();
      Archivos::GuardarCSV($todos,dirname(__DIR__,1) . "\CSV\Usuarios.csv");

      $payload = json_encode(array("mensaje" => "CSV Guardado con exito"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
    public function LeerCSV($request, $response, $args)
    {
      $todos=null;
      $todos=Archivos::LeerCSV(dirname(__DIR__,1) . "\CSV\Usuarios.csv","Usuario");

      $payload = json_encode(array("CSV:" => $todos));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
}
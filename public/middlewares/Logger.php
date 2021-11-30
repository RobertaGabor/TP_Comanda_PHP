<?php
use Slim\Psr7\Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
include_once "./models/Usuario.php";
include_once "./models/Cliente.php";
include_once './middlewares/AutentificadorJWT.php';
include_once "./models/ProductosPorPedido.php";

class Logger
{
    public static function comprobarTipoMozo(Request $request,RequestHandler $handler)
    {

          $response= new Response;
          $respuesta="";  
          
          $header = $request->getHeaderLine('Authorization');
          
  
          try 
          {
            $token = trim(explode("Bearer", $header)[1]);
            $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
            AutentificadorJWT::VerificarToken($token); #tira error y va a catch si no esta
            
            $usuario=AutentificadorJWT::obtenerData($token);    
  
            $tipo=$usuario[0]->tipo;
            
  
            if($tipo=="mozo") 
            {
   
              $response=$handler->handle($request);
  
            }
            else
            {
              $respuesta="Debe ser mozo";
            }
          
          
          
          } catch (Exception $e) {
              $respuesta = json_encode(array('error' => $e->getMessage()));
          }
  
  
              
   
  
          $response->getBody()->write($respuesta);   
          return $response;
      
    }
    public static function comprobarTipoUsuario(Request $request,RequestHandler $handler)
    {

          $response= new Response;
          $respuesta="";  
          
          $header = $request->getHeaderLine('Authorization');
          
  
          try 
          {
            $token = trim(explode("Bearer", $header)[1]);
            $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
            AutentificadorJWT::VerificarToken($token); #tira error y va a catch si no esta
            
            $usuario=AutentificadorJWT::obtenerData($token);    
  
            $tipo=$usuario[0]->tipo;
            
  
            if($tipo=="mozo"||$tipo=="socio"||$tipo=="bartender"||$tipo=="cocinero"||$tipo=="cervecero") 
            {
   
              $response=$handler->handle($request);
  
            }
            else
            {
              $respuesta="Debe ser del personal";
            }
          
          
          
          } catch (Exception $e) {
              $respuesta = json_encode(array('error' => $e->getMessage()));
          }
  
  
              
   
  
          $response->getBody()->write($respuesta);   
          return $response;
      
    }
    public static function comprobarTipoSocioMW(Request $request,RequestHandler $handler)
    {
        $response= new Response;
        $respuesta="";  
        
        $header = $request->getHeaderLine('Authorization');
        

        try 
        {
          $token = trim(explode("Bearer", $header)[1]);
          $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
          AutentificadorJWT::VerificarToken($token); #tira error y va a catch si no esta
          
          $usuario=AutentificadorJWT::obtenerData($token);    

          $tipo=$usuario[0]->tipo;
          

          if($tipo=="socio") 
          {
 
            $response=$handler->handle($request);

          }
          else
          {
            $respuesta="Debe ser socio";
          }
        
        
        
        } catch (Exception $e) {
            $respuesta = json_encode(array('error' => $e->getMessage()));
        }


            
 

        $response->getBody()->write($respuesta);   
        return $response;
    }
    public static function comprobarTipoSocioMozoMW(Request $request,RequestHandler $handler)
    {
        $response= new Response;
        $respuesta="";  
        
        $header = $request->getHeaderLine('Authorization');
        

        try 
        {
          $token = trim(explode("Bearer", $header)[1]);
          $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
          AutentificadorJWT::VerificarToken($token); #tira error y va a catch si no esta
          
          $usuario=AutentificadorJWT::obtenerData($token);    

          $tipo=$usuario[0]->tipo;
          

          if($tipo=="socio" || $tipo=="mozo") 
          {
 
            $response=$handler->handle($request);

          }
          else
          {
            $respuesta="Debe ser socio o mozo";
          }
        
        
        
        } catch (Exception $e) {
            $respuesta = json_encode(array('error' => $e->getMessage()));
        }


            
 

        $response->getBody()->write($respuesta);   
        return $response;
    }
    public static function comprobarSectorMW(Request $request,RequestHandler $handler)
    {
        $response= new Response;
        $respuesta="";  
        
        $header = $request->getHeaderLine('Authorization');
        $parametros = $request->getParsedBody();
        $id_p=$parametros["id"];
        $producto=ProductosPorPedido::obtenerProductoId($id_p);
        $sector_p=$producto->sector;
        
        try 
        {
          $token = trim(explode("Bearer", $header)[1]);
          $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
          AutentificadorJWT::VerificarToken($token); #tira error y va a catch si no esta
          
          $usuario=AutentificadorJWT::obtenerData($token);    
          $tipo=$usuario[0]->tipo;
          $sector=$tipo;
          
          #$tipo=="mozo"||$tipo=="socio"||$tipo=="bartender"||$tipo=="cocinero"||$tipo=="cervecero
          #$sector=Usuario::getSector($tipo);
        
          if($sector_p==$sector)
          {
            $response=$handler->handle($request);
          }
          else
          {
            $respuesta=json_encode(array('error' => "Debe pertener al mismo sector del producto"));
          }
          
        
        } catch (Exception $e) {
            $respuesta = json_encode(array('error' => $e->getMessage()));
        }


            
 

        $response->getBody()->write($respuesta);   
        return $response;
    }
    public static function comprobarExistenciaDeUsuario(Request $request,RequestHandler $handler)
    {
        $response= new Response;
        $respuesta="";
 
        $header = $request->getHeaderLine('Authorization');
        
   
        try 
        {
          $token = trim(explode("Bearer", $header)[1]);
          $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
          AutentificadorJWT::VerificarToken($token);
              
          $params=$request->getParsedBody();
          $clave_vieja=$params["clave_vieja"];
          $clave_nueva=$params["clave_nueva"];
          $usuario=$params["usuario"];
          
          if(isset($clave_vieja)&&$clave_vieja!=null&&isset($clave_nueva)&&$clave_nueva!=null&&isset($usuario)&&$usuario!=null)
          {
              $usr=new Usuario();
              $usr->clave=$clave_vieja;
              $usr->usuario=$usuario;
              if($usr->loginBool()==1)
              {
                $response=$handler->handle($request);
              }
              else
              {
                $respuesta="Error de credenciales, no puede modificar al usuario";
              }
  
  
          }
          else
          {
              $respuesta="Faltan credenciales";
          }
        
        
        
        } catch (Exception $e) {
            $respuesta = json_encode(array('error' => $e->getMessage()));
        }


            
 

        $response->getBody()->write($respuesta);   
        return $response;
    }

    public static function comprobarEmpleadoTokenMW(Request $request,RequestHandler $handler)
    {

          $response= new Response;
          $respuesta="";  
          
          $header = $request->getHeaderLine('Authorization');
          
  
          try 
          {
            $token = trim(explode("Bearer", $header)[1]);
            $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
            AutentificadorJWT::VerificarToken($token); 
            

            $response=$handler->handle($request);

          } catch (Exception $e) {
              $respuesta = json_encode(array('error' => $e->getMessage()));
          }
  
  
              
   
  
          $response->getBody()->write($respuesta);   
          return $response;
      
    }

}
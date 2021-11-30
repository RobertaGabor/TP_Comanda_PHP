<?php
require_once './models/Cliente.php';
require_once './models/Mesa.php';
require_once './models/Encuesta.php';
require_once './interfaces/IApiUsable.php';

class ClienteController extends Cliente 
{
    public function CrearPdf($request, $response, $args)
    {
      $todos=Cliente::obtenerTodos();
      $pdf=Cliente::PDFClientes($todos);
      $pdf->Output($_SERVER['DOCUMENT_ROOT'] . "/PDFClientes.pdf",'F');
      
      $userF=Archivos::findUserHeader($request);
            
      $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Crea pdf de clientes");
      $op->CargarUna();
      $payload = json_encode(array("mensaje" => "PDF GENERADO"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        #compilo datos
        $fecha_de_alta= date_format(new DateTime(date("d-m-Y")), 'Y-m-d H:i:s');
        $nombre=$parametros['nombre'];
        $apellido=$parametros['apellido'];

        if(isset($nombre)&&$nombre!=null&&isset($apellido)&&$apellido!=null)
        {
            #Creamos el cliente
            $clt = new Cliente();
            $clt->nombre=$nombre;
            $clt->apellido=$apellido;
            $clt->fecha_de_alta=$fecha_de_alta;


            #lo guardo en la base
            if($clt!=null)
            {
              try
              {
                $clt->crearCliente();
                $userF=Archivos::findUserHeader($request);
                
                $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Cliente dado de alta");
                $op->CargarUna();
                $payload = json_encode(array("mensaje" => "Cliente creado con exito"));
              }
              catch(e)
              {
                $payload = json_encode(array("mensaje" => "Cliente no se pudo crear"));
              }
              
              
            }
            else
            {
              $payload = json_encode(array("mensaje" => "Cliente no se pudo crear"));
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
    public function AsignarMesa($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $codigo_mesa=$parametros["mesa"];
      $id_cliente=$parametros['cliente'];
      $cliente=Cliente::obtenerClientePorId($id_cliente);
      
      $mesa=Mesa::obtenerMesa($codigo_mesa);

      if($mesa!=null)
      {
        if($mesa->fecha_de_baja==null&&$mesa->estado=="Cerrada")
        {
          $cliente->darCodigoMesa($codigo_mesa);
          $mesa->AsignarEstado(1);
          $mesa->modificarEstadoMesa();
          $userF=Archivos::findUserHeader($request);
              
          $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Asigna mesa a cliente");
          $op->CargarUna();
          $payload = json_encode(array("mensaje" => "Mesa asignada"));          
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Mesa no disponible"));
        }

      }
      else
      {
        $payload = json_encode(array("mensaje" => "Mesa no se pudo asignar"));
      }
      

     
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
    public function TraerUno($request, $response, $args)
    {
        $clt = $args['nombre'];
        $cliente = Cliente::obtenerCliente($clt);
        $payload = json_encode($cliente);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
     
        $lista = Cliente::obtenerTodos();
        $payload = json_encode(array("listaClientes" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody(); #xq se pasa por raw
        $nombre = $parametros['nombre'];
       
        if($nombre!=null&&isset($nombre))
        {
          $aux=Cliente::obtenerCliente($nombre);
          if($aux!=null)
          {
            Cliente::borrarCliente($nombre);
            $userF=Archivos::findUserHeader($request);
                
            $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Cliente dado de baja");
            $op->CargarUna();
            $payload = json_encode(array("mensaje" => "Cliente borrado con exito"));             
          }
          else
          {
            $payload = json_encode(array("mensaje" => "Cliente no encontrado")); 
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
    public function Encuesta($request, $response, $args)
    {
      $clt = $args['id_cliente'];
      $parametros = $request->getParsedBody();
      $mesapto=$parametros["mesa"];
      $cocineropto=$parametros["cocinero"];
      $restaurantepto=$parametros["restaurante"];
      $mozopto=$parametros["mozo"];
      $experiencia=$parametros["experiencia"];
      $fecha=date("Y-m-d");

      if(isset($mesapto)&&$mesapto!=null&&isset($cocineropto)&&$cocineropto!=null&&isset($restaurantepto)&&$restaurantepto!=null&&isset($mozopto)&&$mozopto!=null&&isset($experiencia)&&$experiencia!=null)
      {
        $encuesta= new Encuesta();
        $encuesta->id_cliente=$clt;
        $encuesta->Mesa=$mesapto;
        $encuesta->Cocinero=$cocineropto;
        $encuesta->Restaurante=$restaurantepto;
        $encuesta->Mozo=$mozopto;
        $encuesta->Experiencia=$experiencia;
        $encuesta->fecha=$fecha;
        if($encuesta!=null)
        {
          $encuesta->crearEncuesta();
          $payload = json_encode(array("mensaje" => "Encuesta contestada"));
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Error al enviar la encuesta"));
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
}

?>
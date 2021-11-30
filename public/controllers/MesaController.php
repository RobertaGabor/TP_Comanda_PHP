<?php
require_once './models/Mesa.php';
require_once './models/Pedido.php';
require_once './models/Factura.php';
require_once './models/Archivos.php';
require_once './models/Operaciones.php';
require_once './interfaces/IApiUsable.php';


class MesaController{



    public function TraerTodos($request, $response, $args)
    {
     
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public function TraerUno($request, $response, $args)
    {
        $mesaId = $args['id'];
        $mesa = Mesa::obtenerMesaPorId($mesaId);
        $payload = json_encode($mesa);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        #compilo datos
        $fecha_de_alta= date_format(new DateTime(date("d-m-Y H:i:s")), 'Y-m-d H:i:s');
        $codigo_mesa=$parametros["codigo"];

        if($codigo_mesa!=null&&isset($codigo_mesa))
        {
          #Creamos 
          $msa = new Mesa();
          $msa->AsignarEstado(6);
          $msa->codigo=$codigo_mesa;
          $msa->fecha_de_alta=$fecha_de_alta;


          #lo guardo en la base
          if($msa!=null)
          {
            try
            {
              $msa->crearMesa();
              $userF=Archivos::findUserHeader($request);
              
              $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Mesa dada de alta");
              $op->CargarUna();
              $payload = json_encode(array("mensaje" => "Mesa agregada con exito"));
            }
            catch(e)
            {
      
              $payload = json_encode(array("mensaje" => "Mesa no se pudo agregar"));
            }
            
            
          }
          else
          {
            $payload = json_encode(array("mensaje" => "Mesa no se pudo agregar"));
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
        $id = $parametros['id'];
       
        if($id!=null&&isset($id))
        {
          Mesa::borrarMesa($id);
          $userF=Archivos::findUserHeader($request);
              
          $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Mesa dada de baja");
          $op->CargarUna();
          $payload = json_encode(array("mensaje" => "Mesa eliminada con exito"));          
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Missing fields")); 
        }


        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public function SacarFoto($request, $response, $args)
    {
      $id_pedido = $args['id_pedido'];
      $fecha=date("d-m-Y");
      $foto=$_FILES['foto'];
      try
      {
        $dir_subida = 'FotosPedidos/';
        $fichero_subido = $dir_subida . $id_pedido . "_" . $fecha . ".".pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);

        if (!file_exists($dir_subida)) {

          mkdir('FotosPedidos/', 0777, true);
        }
        move_uploaded_file($_FILES['foto']['tmp_name'], $fichero_subido);
        #traigo pedido, agrego foto, modifico
        $pedido=Pedido::obtenerPedidoId($id_pedido);
        $pedido->foto=explode("/",$fichero_subido)[1];
       
        $pedido->agregarFotoPedido();

        $userF=Archivos::findUserHeader($request);
            
        $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Saca foto");
        $op->CargarUna();
        $payload = json_encode(array("mensaje" => "Foto agregada con exito"));
      }
      catch(e)
      {
        $payload = json_encode(array("mensaje" => "Foto no se pudo agregar"));
      }   
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
    public function CobrarMesa($request, $response, $args)
    {
      $id_pedido = $args['id_pedido'];
      $pedido=Pedido::obtenerPedidoId($id_pedido);

      if($pedido->estado=="Entregado")
      {
        $precioTotal=Pedido::traerPrecioTotal($id_pedido);
        $fecha=date("Y-m-d");
  
        $factura=new Factura();
        $factura->id_pedido=$id_pedido;
        $factura->fecha=$fecha;
        $factura->total=$precioTotal;
  
        
        $codigo_mesa=$pedido->codigo_mesa;
        $mesa=Mesa::obtenerMesa($codigo_mesa);
  
        try
        {      
          $mesa->AsignarEstado(4);
          $mesa->modificarEstadoMesa();
          $factura->CrearFactura();
          $userF=Archivos::findUserHeader($request);
              
          $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Cobra mesa");
          $op->CargarUna();
          $payload = json_encode(array("mensaje" => "Pagado con exito"));        
        }
        catch(e)
        {
          $payload = json_encode(array("mensaje" => "No se pudo pagar"));        
        }
      }
      else
      {
        $payload = json_encode(array("mensaje" => "Pedido aun sin entregar"));
      }


      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');

    }
    public function ListaParaCerrar($request, $response, $args)
    {
      $id_mesa = $args['id_mesa'];
      $mesa=Mesa::obtenerMesaPorId($id_mesa);
      $mesa->AsignarEstado(5);
      $mesa->modificarEstadoMesa();
      $userF=Archivos::findUserHeader($request);
            
      $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Asigna una mesa lista para cerrar");
      $op->CargarUna();
      $payload = json_encode(array("mensaje" => "Lista para cerrar"));   
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
    public function CerrarMesa($request, $response, $args)
    {
      $id_mesa = $args['id_mesa'];
      $mesa=Mesa::obtenerMesaPorId($id_mesa);
      if($mesa->estado=="Lista para cerrar")
      {
        $mesa->AsignarEstado(6);
        $mesa->modificarEstadoMesa();
        $userF=Archivos::findUserHeader($request);
              
        $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Cierra mesa");
        $op->CargarUna();
        $payload = json_encode(array("mensaje" => "Mesa Cerrada"));         
      }
      else
      {
        $payload = json_encode(array("mensaje" => "Mesa aun no lista para cerrar")); 
      }
  
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
    public function ListarListas($request, $response, $args)
    {
      $estado="Lista para cerrar";
      $lista = Mesa::obtenerPorEstado($estado);
      $payload = json_encode(array("listaMesas" => $lista));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
    public function VerEstadoPedido($request, $response, $args)
    {
      $codigo_p = $args['codigo_pedido'];
      $pedido=Pedido::obtenerPedidoCodigo($codigo_p);
      $hora_estimada=strtotime($pedido->duracion_estimada);
      $hora_actual=strtotime(date('h:i:s'));
   

      if($hora_estimada!=null)
      {
        $difference = round(abs($hora_estimada - $hora_actual) / 3600,2);
        $hora=explode(".",$difference)[0];
        $minutos=explode(".",$difference)[1];
        $payload = json_encode(array("Tiempo restantes: " => "{$hora} hora/s con {$minutos} minutos/s restantes"));
      }
      else
      {
        $payload = json_encode(array("Tiempo restantes: " => "Aun no se ha calculado el tiempo restante. Intente en unos minutos"));

      }
      

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
    
    public function GuardarCSV($request, $response, $args)
    {
      $todos=Mesa::obtenerTodos();
      Archivos::GuardarCSV($todos,dirname(__DIR__,1) . "\CSV\Mesas.csv");

      $payload = json_encode(array("mensaje" => "CSV Guardado con exito"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
    public function LeerCSV($request, $response, $args)
    {
      $todos=null;
      $todos=Archivos::LeerCSV(dirname(__DIR__,1) . "\CSV\Mesas.csv","Mesa");

      $payload = json_encode(array("CSV:" => $todos));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

}

?>
<?php
include_once "./models/Pedido.php";
include_once "./models/Mesa.php";
include_once "./models/ProductosPorPedido.php";
require_once './interfaces/IApiUsable.php';
require_once './models/Operaciones.php';
class PedidoController extends Pedido {

    public static function Estadisticas($request, $response, $args)
    {
      $fechaA = date("Y-m-d H:i:s");
      $fechaB = date("Y-m-d H:i:s", strtotime('-30 days'));
      $productosTotales=0;
      $pedidos = Pedido::obtenerPorFechas($fechaB,$fechaA);
      $array = array();
      foreach($pedidos as $pedido){
          $productoPedido = ProductosPorPedido::obtenerProductosPorPedido($pedido->id);
          foreach($productoPedido as $producto){
              $productod = Producto::obtenerProducto($producto->id_producto);
              $cantidad=$producto->cantidad;
              $nombre=$productod->nombre;

                $array[$nombre]=$cantidad+$array[$nombre];                
              

              }
              
      }

      arsort($array);
      $pedidosJson = json_encode(array("Total pedidos" => count($pedidos),"Del mas vendido al menos" => $array));
      $response->getBody()->write($pedidosJson);
      
      return $response
      ->withHeader('Content-Type', 'application/json');
    }
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        #compilo datos
        $fecha_de_alta= date_format(new DateTime(date("d-m-Y H:i:s")), 'Y-m-d H:i:s');
        $codigo_mesa=$parametros['codigo_mesa'];
        $codigo=$parametros['codigo'];


        if(isset($codigo_mesa)&&$codigo_mesa!=null&&isset($codigo)&&$codigo!=null)
        {
          #Creamos el pedido
          $pdd = new Pedido();
          $pdd->codigo_mesa = $codigo_mesa;
          $pdd->codigo = $codigo;
          $pdd->estado=Pedido::getEstado(1);
          $pdd->fecha_de_alta=$fecha_de_alta;

          #lo guardo en la base
          if($pdd!=null)
          {
            try
            {
              $pdd->crearPedido();
              $mesa=Mesa::obtenerMesa($codigo_mesa);
              if($mesa!=null)
              {
                $mesa->AsignarEstado(2);
                $mesa->modificarEstadoMesa();
                $userF=Archivos::findUserHeader($request);
                
                $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Pedido dado de alta");
                $op->CargarUna();
                $payload = json_encode(array("mensaje" => "Pedido creado con exito"));                
              }
              else
              {
                $payload = json_encode(array("mensaje" => "Codigo mesa erroneo")); 
              }

            }
            catch(e)
            {
        
              $payload = json_encode(array("mensaje" => "Pedido no se pudo crear"));
            }
            
            
          }
          else
          {
            $payload = json_encode(array("mensaje" => "Pedido no se pudo crear"));
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
        $pdd = $args['id'];
        $pedido = Pedido::obtenerPedidoId($pdd);
        $payload = json_encode($pedido);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public function TraerUnoCodigo($request, $response, $args)
    {
        // Buscamos usuario por nombre de usuario
        $pdd = $args['codigo'];
        $pedido = Pedido::obtenerPedidoCodigo($pdd);
        $payload = json_encode($pedido);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public function TraerTodos($request, $response, $args)
    {
     
        $lista = Pedido::obtenerTodos();
        $payload = json_encode(array("listaPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
  

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody(); #xq se pasa por raw
        $id = $parametros['id'];
       
        if(isset($id)&&$id!=null)
        {
          Pedido::borrarPedido($id);

          $codigo_mesa=Pedido::obtenerMesaPorPedido($id)->codigo_mesa;
          $mesa=Mesa::obtenerMesa($codigo_mesa);
          $mesa->AsignarEstado(5);
          $mesa->modificarEstadoMesa();
          $userF=Archivos::findUserHeader($request);
              
          $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Pedido dado de baja");
          $op->CargarUna();
          $payload = json_encode(array("mensaje" => "Pedido cancelado con exito"));          
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Missing fields")); 
        }


        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ListarPendientes($request, $response, $args)
    {
        $lista = Pedido::obtenerPendientes();
        $payload = json_encode(array("listaPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerProductos($request, $response, $args)
    {
        #paso id, traigo pedido, busco todos los productos x pedido que en id_pedido:=id
        $pdd = $args['id'];
        
        $productos = ProductosPorPedido::obtenerProductosPorPedido($pdd);
        $payload = json_encode(array("Productos de pedido $pdd" => $productos));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public function ListoParaEntregar($request, $response, $args)
    {
        $pdd = $args['id'];
        $pedido = Pedido::obtenerPedidoId($pdd);
        $pedido->estado=Pedido::getEstado(2);
        $pedido->modificarEstadoPedido();

        $userF=Archivos::findUserHeader($request);
            
        $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Marca pedido listo para entregar");
        $op->CargarUna();
        $payload = json_encode(array("mensaje" => "Modificado estado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function ListosParaEntregar($request, $response, $args)
    {
      $lista = Pedido::obtenerListos();
      $payload = json_encode(array("listaPedidos" => $lista));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function Entregar($request, $response, $args)
    {
      #mesa estado=3;
      #pedido estado="Entregado"
      #pedido estimado_final=hora actual
      $pdd = $args['id'];
      $pedido = Pedido::obtenerPedidoId($pdd);
      if($pedido->estado="Listo")
      {
        $pedido->estado=Pedido::getEstado(3);
          $pedido->duracion_final=date('h:i:s');
          $pedido->modificarEstadoPedido();
          $pedido->AsignoTiempoFinal(); #unificar funciones

          $codigo_mesa=$pedido->codigo_mesa;
          $mesa=Mesa::obtenerMesa($codigo_mesa);
          $mesa->AsignarEstado(3);
          $mesa->modificarEstadoMesa();

          $userF=Archivos::findUserHeader($request);
                
          $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Entrtega pedido");
          $op->CargarUna();
          $payload = json_encode(array("mensaje" => "Entregado con exito"));

      }
      else
      {
        $payload = json_encode(array("mensaje" => "Pedido aun no listo"));
      }
 
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
      
    }

    public function AsignarTiempoEstimado($request, $response, $args)
    {
      $pdd = $args['id'];
      $pedido = Pedido::obtenerPedidoId($pdd);
      $tiempo = Pedido::obtenerTiempoPorPedido($pdd); #en minutos
      if($tiempo!=null)
      {
        $tiempo=$tiempo[0][0];

        $hora_alta=$pedido->fecha_de_alta;
        $endTime = strtotime("+$tiempo minutes", strtotime($hora_alta));
        
        $estimate= date('h:i:s', $endTime);

        $pedido->duracion_estimada=$estimate;
        $pedido->modificarTiempoPedido();
        $userF=Archivos::findUserHeader($request);
              
        $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Asigna tiempo a pedido");
        $op->CargarUna(); 
        $payload = json_encode(array("mensaje" => "Asignado estado con exito"));        
      }
      else
      {
        $payload = json_encode(array("mensaje" => "No se puede asignar"));
      }

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');    
  
    }
    public function TraerCodigoPorId($request, $response, $args)
    {
      $pdd = $args['id'];
      $pedido = Pedido::obtenerPedidoId($pdd);
      $codigo=$pedido->codigo;
      $payload = json_encode(array("codigo: " => $codigo));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');  
    }
    
}

?>
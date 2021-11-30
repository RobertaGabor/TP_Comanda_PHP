<?php
include_once "./models/ProductosPorPedido.php";
include_once "./models/Pedido.php";
include_once "./models/Producto.php";
require_once './models/Operaciones.php';
Class ProductosPedidosController
{
    public function TraerTodos($request, $response, $args)
    {
     
        $lista = ProductosPorPedido::obtenerTodos();
        $payload = json_encode(array("listaProductosPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function ListarPendientes($request, $response, $args)
    {
     
        $lista = ProductosPorPedido::obtenerPendientes();
        $payload = json_encode(array("listaProductosPendientes" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por nombre de usuario
        $pdd = $args['id'];
        $pedido = ProductosPorPedido::obtenerProductoId($pdd);
        $payload = json_encode($pedido);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody(); #xq se pasa por raw
        $id = $parametros['id'];
       
        ProductosPorPedido::borrarProductoPedido($id);
        $userF=Archivos::findUserHeader($request);
            
        $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Producto para pedido borrado");
        $op->CargarUna();
        $payload = json_encode(array("mensaje" => "Producto cancelado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public function EntregarProducto($request, $response, $args)
    {
      #cambio estado  a LISTO
      $parametros = $request->getParsedBody(); #xq se pasa por raw
      $pdd = $parametros['id'];
      $producto = ProductosPorPedido::obtenerProductoId($pdd);
      $producto->estado="Listo"; #Generar una funcion q devuelva??
      $producto->modificarProducto();
      $userF=Archivos::findUserHeader($request);
            
      $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Producto entregado");
      $op->CargarUna();
      $payload = json_encode(array("mensaje" => "Producto entregado"));
    
    
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    

    }
    public function ElaborarProducto($request, $response, $args)
    {
      #cambio estado producto "EEn elaboracion"
      #"le da tiempo estimado"
      #vlido que sea del mismo sector
      $parametros = $request->getParsedBody();
      $id_p = $parametros['id'];
      $tiempo= $parametros['tiempo'];

      $producto=ProductosPorPedido::obtenerProductoId($id_p);
      $producto->estado="En elaboracion";
      $producto->duracion_estimada=$tiempo;

      $producto->modificarProducto();
      $userF=Archivos::findUserHeader($request);
      
      $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Producto tomado para elaboracion");
      $op->CargarUna();
      $payload = json_encode(array("mensaje" => "Producto en proceso de elaboracion"));
    
    
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    
      


    }
    public function ModificarCantidad($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $nueva_cantidad=$parametros["cantidad"];
      $id=$parametros["id"];


      $ppp=ProductosPorPedido::obtenerProductoId($id);
      $ppp->cantidad=$nueva_cantidad;
      $ppp->modificarCantidad();
      $userF=Archivos::findUserHeader($request);
            
      $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Cantidad modificada de producto");
      $op->CargarUna();
      $payload = json_encode(array("mensaje" => "Producto modificado con exito"));
    
    
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');


    }
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        #compilo datos
        $fecha_de_alta= date_format(new DateTime(date("d-m-Y H:i:s")), 'Y-m-d H:i:s');
        $id_pedido=$parametros['id_pedido'];
        $id_producto=$parametros['id_producto'];
        $cantidad=$parametros['cantidad'];
        $estado="Pendiente";

        #pedido y producto
        $pedido=Pedido::obtenerPedidoId($id_pedido);
        $producto=Producto::obtenerProducto($id_producto);

        $sector=$producto->sector;


        #Creamos 
        $pdd = new ProductosPorPedido();
        $pdd->id_pedido=$id_pedido;
        $pdd->id_producto=$id_producto;
        $pdd->sector=$sector;
        $pdd->cantidad=$cantidad;
        $pdd->fecha_de_alta=$fecha_de_alta;
        $pdd->estado=$estado;

        #lo guardo en la base
        if($pdd!=null)
        {
          try
          {
            $pdd->crearProductoPorPedido();
            $userF=Archivos::findUserHeader($request);
            
            $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Registro de nuevo producto para un pedido");
            $op->CargarUna();
            $payload = json_encode(array("mensaje" => "Producto agregado con exito al pedido"));
          }
          catch(e)
          {
     
            $payload = json_encode(array("mensaje" => "Producto no se pudo agregar al pedido"));
          }
          
          
        }
        else
        {
          $payload = json_encode(array("mensaje" => "Producto no se pudo agregar al pedido"));
        }
        
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}

?>
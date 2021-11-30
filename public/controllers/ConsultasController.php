<?php
require_once './models/Mesa.php';
require_once './models/Pedido.php';
require_once './models/Factura.php';
require_once './models/Usuario.php';
require_once './models/Operaciones.php';
include_once "./models/ProductosPorPedido.php";
class ConsultasController{
    public static function TraerConsulta_FechaDeIngreso($request, $response, $args)
    {
        #fechaa fechab
        $desde=$_GET["desde"];
        $hasta=$_GET["hasta"];

        $todos=Usuario::FechaDeIngreso($desde,$hasta);
        $payload = json_encode(array("Empleados" => $todos));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public static function TraerConsulta_CantidadOperacionPorSector($request, $response, $args)
    {
        #sector
        $sector=$_GET["sector"];
        $todos=Operacion::CantidadOperacionPorSector($sector);
        $payload = json_encode(array("cantidad" => $todos));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public static function TraerConsulta_CantidadOperacionPorSectorListadas($request, $response, $args)
    {
        #sector
        $sector=$_GET["sector"];
        $lista=Operacion::TraerConsulta_CantidadOperacionPorSectorListadas($sector);
        $payload = json_encode(array("cantidad" => count($lista), "Lista: " => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public static function TraerConsulta_CantidadOperacionPorEmpleado($request, $response, $args)
    {
        #emplead
        $id_empleado=$_GET["id_empleado"];
        $todos=Operacion::CantidadOperacionPorEmpleado($id_empleado);
        $payload = json_encode(array("cantidad:"=>count($todos),"operaciones" => $todos));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    #########
    public static function TraerConsulta_MasVendido($request, $response, $args)
    {
        $todos=ProductosPorPedido::MasVendido();
        $id_pro=$todos[0]->id_producto;
        $producto=Producto::obtenerProducto($id_pro);
        $payload = json_encode(array("mas vendido" => $producto));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public static function TraerConsulta_MenosVendido($request, $response, $args)
    {
        $todos=ProductosPorPedido::MasVendido();
        $id_pro=$todos[count($todos)-1]->id_producto;
        $producto=Producto::obtenerProducto($id_pro);
        $payload = json_encode(array("menos vendido" => $producto));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public static function TraerConsulta_NoEntregadosATiempo($request, $response, $args)
    {
        $todos=Pedido::NoEntregadosATiempo();
        $payload = json_encode(array("no entregados a tiempo" => $todos));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public static function TraerConsulta_Cancelados($request, $response, $args)
    {
        $todos=Pedido::Cancelados();
        $payload = json_encode(array("Cancelados" => $todos));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    ########
    public static function TraerConsulta_MasUsada($request, $response, $args)
    {
        $todos=Mesa::MasUsada();
        $payload = json_encode(array("Mas usada" => $todos[0]->codigo_mesa));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public static function TraerConsulta_MenosUsada($request, $response, $args)
    {
        $todos=Mesa::MasUsada();
        $payload = json_encode(array("Menos usada" => $todos[count($todos)-1]->codigo_mesa));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public static function TraerConsulta_MasFacturo($request, $response, $args)
    {
        $todos=Mesa::MasFacturo();
        $payload = json_encode(array("Mas facturo" => $todos[0]->codigo_mesa));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');  
    }
    public static function TraerConsulta_MenosFacturo($request, $response, $args)
    {
        $todos=Mesa::MasFacturo();
        $payload = json_encode(array("Menos facturo" => $todos[count($todos)-1]->codigo_mesa));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json'); 
    }
    public static function TraerConsulta_MayorGasto($request, $response, $args)
    {
        $todos=Mesa::MayorGasto();
        $payload = json_encode(array("Mayor gasto" => $todos[0]->codigo_mesa));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json'); 
    }
    public static function TraerConsulta_MenorGasto($request, $response, $args)
    {
        $todos=Mesa::MayorGasto();
        $payload = json_encode(array("Mayor gasto" => $todos[count($todos)-1]->codigo_mesa));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json'); 
    }
    public static function TraerConsulta_FacturadoEntreFechas($request, $response, $args)
    {
        #fechaa fechab
        $desde=$_GET["desde"];
        $hasta=$_GET["hasta"];
        
        $todos=Factura::FacturadoEntreFechas($desde,$hasta);
        $payload = json_encode(array("facturado" => $todos));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    public static function TraerConsulta_MejorPuntuacion($request, $response, $args)
    {
        $todos=Mesa::MejorPuntuacion();
        $payload = json_encode(array("Mejor puntuacion" => $todos[0]->codigo_mesa));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json'); 
    }
    public static function TraerConsulta_PeorPuntuacion($request, $response, $args)
    {
        $todos=Mesa::MejorPuntuacion();
        $payload = json_encode(array("Mejor puntuacion" => $todos[count($todos)-1]->codigo_mesa));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json'); 
    }
   
    
}

?>
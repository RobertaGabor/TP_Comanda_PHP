<?php
include_once "./models/Archivos.php";
include_once "./models/Producto.php";
require_once './interfaces/IApiUsable.php';
require_once './models/Operaciones.php';
class ProductoController extends Producto implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        #compilo datos
        $fecha_de_alta= date_format(new DateTime(date("d-m-Y")), 'Y-m-d H:i:s');
        $nombre=$parametros['nombre'];
        $precio=$parametros['precio'];
        $sector=$parametros['sector'];

        if(isset($nombre)&&$nombre!=null&&isset($precio)&&$precio!=null&&isset($sector)&&$sector!=null)
        {
          #Creamos el producto
          $pdt = new Producto();
          $pdt->nombre = $nombre;
          $pdt->precio = $precio;
          $pdt->sector=$sector;
          $pdt->fecha_de_alta=$fecha_de_alta;


          #lo guardo en la base
          if($pdt!=null)
          {
            try
            {
              $pdt->crearProducto();
              $userF=Archivos::findUserHeader($request);
              
              $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Producto cargado");
              $op->CargarUna();
              $payload = json_encode(array("mensaje" => "Producto creado con exito"));
            }
            catch(e)
            {
        
              $payload = json_encode(array("mensaje" => "Producto no se pudo crear"));
            }
            
            
          }
          else
          {
            $payload = json_encode(array("mensaje" => "Producto no se pudo crear"));
          }
        }
        else{
          $payload = json_encode(array("mensaje" => "Missing fields"));
        }
 

        

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por nombre de usuario
        $pdt = $args['id'];
        $producto = Producto::obtenerProducto($pdt);
        $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
     
        $lista = Producto::obtenerTodos();
        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
  
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $parametros['id'];
        $precio = $parametros['nuevo_precio'];

        if(isset($id)&&$id!=null)
        {
          $pdt = new Producto();
          $pdt->precio = $precio;
          $pdt->id = $id; 

          $aux=Producto::obtenerProducto($id);
          if($aux!=null)
          {
            $pdt->modificarPrecioProducto();
            $userF=Archivos::findUserHeader($request);
                
            $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Producto modificado");
            $op->CargarUna();
            $payload = json_encode(array("mensaje" => "Precio modificado con exito"));             
          }
          else
          {
            $payload = json_encode(array("mensaje" => "Producto no encontrado")); 
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
       
        if(isset($id)&&$id!=null)
        {
          $aux=Producto::obtenerProducto($id);
          if($aux!=null)
          {
            Producto::borrarProducto($id);
            $userF=Archivos::findUserHeader($request);
                
            $op=Operacion::auxConstruct($userF["id"],$userF["sector"],"Producto borrado");
            $op->CargarUna();
            $payload = json_encode(array("mensaje" => "Producto borrado con exito"));             
          }
          else
          {
            $payload = json_encode(array("mensaje" => "Producto no encontrado"));
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
      $todos=Producto::obtenerTodos();
      Archivos::GuardarCSV($todos,dirname(__DIR__,1) . "\CSV\Productos.csv");

      $payload = json_encode(array("mensaje" => "CSV Guardado con exito"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
    public function LeerCSV($request, $response, $args)
    {
      $todos=null;
      $todos=Archivos::LeerCSV(dirname(__DIR__,1) . "\CSV\Productos.csv","Producto");

      $payload = json_encode(array("CSV:" => $todos));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
}

?>
<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/ClienteController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/ConsultasController.php';
require_once './controllers/ProductosPedidosController.php';

require_once './db/AccesoDatos.php';
include_once './middlewares/Logger.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = AppFactory::create();
#$app->setBasePath('/public');
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Bienvenide a la comanda!");
    return $response;
});

#TIPO:MOZO,COCINERO,SOCIO,CERVECERO,BARTENDER 
#SECTORES: MOZO, COCINA, SOCIO, BARRA CERVEZA, BARRA TRAGOS
#PEDIDOS: PENDIENTE/LISTO/ENTREGADO

// usuarios
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('/TraerTodos', \UsuarioController::class . ':TraerTodos');
    $group->get('/TraerUno/{usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno'); #que el usuario no exista
    $group->delete('[/]',\UsuarioController::class . ':BorrarUno');
    $group->put('/CambiarClave',\UsuarioController::class . ':ModificarUno');
    $group->post('/Suspender/{usuario}', \UsuarioController::class . ':SuspenderUno'); 
    $group->post('/Reactivar/{usuario}', \UsuarioController::class . ':ReactivarUno'); 

    $group->post('/csv', \UsuarioController::class . ':GuardarCSV');
    $group->get('/csv', \UsuarioController::class . ':LeerCSV');
  })->add(\Logger::class . ':comprobarTipoSocioMW');

  // clientes
  $app->group('/clientes', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ClienteController::class . ':TraerTodos');
    $group->get('/{nombre}', \ClienteController::class . ':TraerUno');
    $group->post('[/]', \ClienteController::class . ':CargarUno'); 
    $group->delete('[/]',\ClienteController::class . ':BorrarUno');
    $group->post('/asignarMesa',\ClienteController::class . ':AsignarMesa');
    $group->post('/pdf',\ClienteController::class . ':CrearPdf');
  
  })->add(\Logger::class . ':comprobarTipoMozo'); 

  #Productos
  $app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('/TraerTodos', \ProductoController::class . ':TraerTodos');
    $group->get('/TraerUno/{id}', \ProductoController::class . ':TraerUno');
    $group->post('[/]', \ProductoController::class . ':CargarUno');
    $group->delete('[/]',\ProductoController::class . ':BorrarUno');
    $group->put('[/]',\ProductoController::class . ':ModificarUno'); 

    $group->post('/csv', \ProductoController::class . ':GuardarCSV');
    $group->get('/csv', \ProductoController::class . ':LeerCSV');
  })->add(\Logger::class . ':comprobarTipoSocioMW');
  

#pedidos
  $app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->get('/listarTodos', \PedidoController::class . ':TraerTodos')->add(\Logger::class . ':comprobarTipoSocioMozoMW');
    $group->get('/ListarUno/{id}', \PedidoController::class . ':TraerUno')->add(\Logger::class . ':comprobarTipoSocioMozoMW');
    $group->post('[/]', \PedidoController::class . ':CargarUno')->add(\Logger::class . ':comprobarTipoSocioMozoMW'); #3 PASO GENERO UN PEDIDO CUANDO YA DECIDIERON
    $group->delete('/cancelar',\PedidoController::class . ':BorrarUno')->add(\Logger::class . ':comprobarTipoSocioMozoMW');
    $group->get('/Pendientes',\PedidoController::class . ':ListarPendientes')->add(\Logger::class . ':comprobarEmpleadoTokenMW'); #con esto ves cuales id estan pendientes, en traeruno ves que falta, el de la caja si ve que esta todo hecho pone listo para servir, el mozo ve cuales estan listos para servir y lo entrega
    $group->get('/ListarProductos/{id}', \PedidoController::class . ':TraerProductos')->add(\Logger::class . ':comprobarEmpleadoTokenMW');
    $group->post('/Listo/{id}',\PedidoController::class . ':ListoParaEntregar')->add(\Logger::class . ':comprobarEmpleadoTokenMW');
    $group->get('/ListarListos',\PedidoController::class . ':ListosParaEntregar')->add(\Logger::class . ':comprobarEmpleadoTokenMW');#muestra los listos para entregar
    $group->post('/AsignarTiempoEstimado/{id}',\PedidoController::class . ':AsignarTiempoEstimado')->add(\Logger::class . ':comprobarEmpleadoTokenMW');#cuando vea que todos los productos estan en preparacion. Hora actual + la suma de los tiempos estimados d eproductos
    $group->post('/Entregar/{id}',\PedidoController::class . ':Entregar')->add(\Logger::class . ':comprobarTipoMozo');
    $group->get('/TraerCodigo/{id}',\PedidoController::class . ':TraerCodigoPorId')->add(\Logger::class . ':comprobarTipoSocioMozoMW');
    $group->get('/ListarPedido/{codigo}', \PedidoController::class . ':TraerUnoCodigo');
    $group->get('/estadisticas30Dias', \PedidoController::class . ':Estadisticas')->add(\Logger::class . ':comprobarTipoSocioMW');
  });

  $app->group('/ProductosPorPedido', function (RouteCollectorProxy $group) {
    $group->get('/ListarTodos', \ProductosPedidosController::class . ':TraerTodos')->add(\Logger::class . ':comprobarEmpleadoTokenMW');
    $group->get('/Listar/{id}', \ProductosPedidosController::class . ':TraerUno')->add(\Logger::class . ':comprobarEmpleadoTokenMW');
    $group->post('[/]', \ProductosPedidosController::class . ':CargarUno')->add(\Logger::class . ':comprobarTipoMozo');
    $group->delete('[/]',\ProductosPedidosController::class . ':BorrarUno')->add(\Logger::class . ':comprobarEmpleadoTokenMW');
    $group->put('[/]',\ProductosPedidosController::class . ':ModificarCantidad')->add(\Logger::class . ':comprobarTipoMozo');
    $group->post('/Elaborar', \ProductosPedidosController::class . ':ElaborarProducto')->add(\Logger::class . ':comprobarSectorMW');#VALIDO SECTOR PASO TIEMPO ESTIMADO
    $group->post('/Entregar', \ProductosPedidosController::class . ':EntregarProducto')->add(\Logger::class . ':comprobarSectorMW');#VALIDO SECTOR
    $group->get('/ListarPendientes', \ProductosPedidosController::class . ':ListarPendientes')->add(\Logger::class . ':comprobarEmpleadoTokenMW'); #lista Pendientes (no los que digan en elaboracion) 
  });

  // Mesas
  $app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('/ListarTodas', \MesaController::class . ':TraerTodos')->add(\Logger::class . ':comprobarEmpleadoTokenMW');
    $group->get('/Listar/{id}', \MesaController::class . ':TraerUno')->add(\Logger::class . ':comprobarEmpleadoTokenMW');
    $group->post('[/]', \MesaController::class . ':CargarUno')->add(\Logger::class . ':comprobarTipoSocioMW');
    $group->delete('[/]',\MesaController::class . ':BorrarUno')->add(\Logger::class . ':comprobarTipoSocioMW');
    $group->post('/TomarFoto/{id_pedido}',\MesaController::class . ':SacarFoto')->add(\Logger::class . ':comprobarTipoMozo');
    $group->post('/Cobrar/{id_pedido}',\MesaController::class . ':CobrarMesa')->add(\Logger::class . ':comprobarTipoMozo');
    $group->post('/ListaParaCerrar/{id_mesa}',\MesaController::class . ':ListaParaCerrar')->add(\Logger::class . ':comprobarEmpleadoTokenMW');#la mesa mesa:5
    $group->post('/Cerrar/{id_mesa}',\MesaController::class . ':CerrarMesa')->add(\Logger::class . ':comprobarTipoSocioMW');#cerrar mesa mesa:6 SOLO SOCIOS
    $group->get('/ListarListasParaCerrar',\MesaController::class . ':ListarListas')->add(\Logger::class . ':comprobarTipoSocioMW');
    $group->get('/{codigo_mesa}/{codigo_pedido}', \MesaController::class . ':VerEstadoPedido');

    $group->post('/csv', \MesaController::class . ':GuardarCSV');
    $group->get('/csv', \MesaController::class . ':LeerCSV');
  });

  $app->group('/Consultas/Empleados', function (RouteCollectorProxy $group) {
    $group->get('/FechaDeIngreso', \ConsultasController::class . ':TraerConsulta_FechaDeIngreso');
    $group->get('/CantidadOperacionPorSector', \ConsultasController::class . ':TraerConsulta_CantidadOperacionPorSector');
    $group->get('/CantidadOperacionPorSectorListadas', \ConsultasController::class . ':TraerConsulta_CantidadOperacionPorSectorListadas');
    $group->get('/CantidadOperacionPorEmpleado', \ConsultasController::class . ':TraerConsulta_CantidadOperacionPorEmpleado');
  });
  $app->group('/Consultas/Pedidos', function (RouteCollectorProxy $group) {
    $group->get('/MasVendido', \ConsultasController::class . ':TraerConsulta_MasVendido');
    $group->get('/MenosVendido', \ConsultasController::class . ':TraerConsulta_MenosVendido');
    $group->get('/NoEntregadosATiempo', \ConsultasController::class . ':TraerConsulta_NoEntregadosATiempo');
    $group->get('/Cancelados', \ConsultasController::class . ':TraerConsulta_Cancelados');
  });
  $app->group('/Consultas/Mesas', function (RouteCollectorProxy $group) {
    $group->get('/MasUsada', \ConsultasController::class . ':TraerConsulta_MasUsada');
    $group->get('/MenosUsada', \ConsultasController::class . ':TraerConsulta_MenosUsada');
    $group->get('/MasFacturo', \ConsultasController::class . ':TraerConsulta_MasFacturo');
    $group->get('/MenosFacturo', \ConsultasController::class . ':TraerConsulta_MenosFacturo');
    $group->get('/MayorGasto', \ConsultasController::class . ':TraerConsulta_MayorGasto');
    $group->get('/MenorGasto', \ConsultasController::class . ':TraerConsulta_MenorGasto');
    $group->get('/FacturadoEntreFechas', \ConsultasController::class . ':TraerConsulta_FacturadoEntreFechas');
    $group->get('/MejorPuntuacion', \ConsultasController::class . ':TraerConsulta_MejorPuntuacion');
    $group->get('/PeorPuntuacion', \ConsultasController::class . ':TraerConsulta_PeorPuntuacion');
  });

$app->group('/encuesta', function (RouteCollectorProxy $group){
  $group->post('/{id_cliente}', \ClienteController::class . ':Encuesta');
  });
$app->group('/login',function(RouteCollectorProxy $group){
  $group->post('[/]', \UsuarioController::class . ':Logearse');
});

// Run app
$app->addBodyParsingMiddleware();
$app->run();



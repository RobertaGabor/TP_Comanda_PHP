<?php
require_once './models/Mesa.php';
require_once './models/Usuario.php';
require_once './models/Producto.php';
class Archivos{

    public static function guardarCSV($lista,$path)
    {
        $ok=fopen($path,"w");

        if(!$ok)
        {
            mkdir('CSV/', 0777, true);
        }


        foreach ($lista as $key) {
            $Array = (array)$key;
            $return=fputcsv($ok,$Array);
        }
        
        
        fclose($ok);
            
        
        return $return;
    }

    public static function leerCSV($path,$tipo)
    {
        $total=Array();
        $ok=fopen($path,"r");
        $new=null;
        if(!$ok)
        {
            var_dump(error_clear_last);
        }
        else
        {

            while (($linea = fgetcsv($ok,1000,",")) !== FALSE)
            {
                switch($tipo)
                {
                    case "Usuario":
                        $new=Usuario::constructAux($linea[0],$linea[1],$linea[2],$linea[3],$linea[4],$linea[5],$linea[6],$linea[7],$linea[8],$linea[9]);
                        break;
                    case "Mesa":
                        $new=Mesa::constructAux($linea[0],$linea[1],$linea[2],$linea[3],$linea[4],$linea[5]);
                        break;
                    case "Producto":
                        $new=Producto::constructAux($linea[0],$linea[1],$linea[2],$linea[3],$linea[4],$linea[5],$linea[6]);
                        break;
                }
                array_push($total,$new);
            }
            fclose($ok);
        } 
        return $total;
    }

    public static function findUserHeader($request)
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $uno=AutentificadorJWT::obtenerData($token);    
        $id=$uno[0]->id;
        $sector=$uno[0]->tipo;

        return array("id"=>$id,"sector"=>$sector);
    }
}



?>
<?php 

class DATA {
    
	public static $conn;    
    private static $config="";
    //private static $connSql;

	private static function ConfiguracionIni(){
        require_once('Globals.php');
        if (file_exists('../../../ini/config.ini')) {
            self::$config = parse_ini_file('../../../ini/config.ini',true); 
        }         
    }  

    private static function Conectar(){
        try {          
            self::ConfiguracionIni();
            if(!isset(self::$conn)) {                                
                self::$conn = new PDO('mysql:host='. self::$config[Globals::app]['host'] .';port='. self::$config[Globals::app]['port'] .';dbname=' . self::$config[Globals::app]['dbname'].';charset=utf8', self::$config[Globals::app]['username'],   self::$config[Globals::app]['password']); 
                return self::$conn;
            }
        } catch (PDOException $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error de Conexión'))
            );
        }
        catch(Exception $e){
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error de Conexión'))
            );
        }
    }
    
    // Ejecuta consulta SQL, $fetch = false envía los datos en 'crudo', $fetch=TRUE envía los datos en arreglo (fetchAll).
    public static function Ejecutar($sql, $param=NULL, $fetch=true) {
        try{
            //conecta a BD
            self::Conectar();
            $st=self::$conn->prepare($sql);
            self::$conn->beginTransaction(); 
            if($st->execute($param))
            {
                self::$conn->commit(); 
                if($fetch)
                    return  $st->fetchAll();
                else return $st;    
            } else {
                self::$conn->rollback();
                throw new Exception('Error al ejecutar.',00);
            }            
        } catch (Exception $e) {
            self::$conn->rollback(); 
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }   

    public static function getLastID(){
        return self::$conn->lastInsertId();
    }

    // public static function ConectarSQL(){
    //     try {           
    //         if(!isset(self::$connSql)) {
    //             $config = parse_ini_file('../ini/config.ini'); 
    //             self::$connSql = new PDO("odbc:sqlserver", 'dbaadmin', 'dbaadmin'); 
    //             return self::$connSql;
    //         }
    //     } catch (PDOException $e) {
    //         require_once("Log.php");  
    //         log::AddD('FATAL', 'Ha ocurrido al Conectar con la base de datos SQL[01]', $e->getMessage());
    //         //$_SESSION['errmsg']= $e->getMessage();
    //         header('Location: ../Error.php');
    //         exit;
    //     }
    // }   
    
    // public static function EjecutarSQL($sql, $param=NULL, $fetch=true) {
    //     try{
    //         //conecta a BD
    //         self::ConectarSQL();    
    //         $st=self::$connSql->prepare($sql);
    //         self::$conn->beginTransaction(); 
    //         if($st->execute($param)){
    //             self::$conn->commit(); 
    //             if($fetch)
    //                 return  $st->fetchAll();
    //             else return $st;    
    //         } else {
    //             self::$conn->rollback(); 
    //             require_once("Log.php");  
    //             log::Add('ERROR', 'Ha ocurrido al Ejecutar la sentencia SQL[02]');
    //             return false;
    //         }
    //     } catch (Exception $e) {
    //         self::$conn->rollback(); 
    //         require_once("Log.php");  
    //         log::AddD('ERROR', 'Ha ocurrido al Ejecutar la sentencia SQL', $e->getMessage());
    //         //$_SESSION['errmsg']= $e->getMessage();
    //         header('Location: ../Error.php');
    //         exit;
    //     }
    // }
}
?>

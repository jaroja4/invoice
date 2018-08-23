<?php
    require_once("Conexion.php");

    if(isset($_POST["action"])){
        $opt= $_POST["action"];
        unset($_POST['action']);
        // Classes
        require_once("Usuario.php");
        // Session
        if (!isset($_SESSION))
            session_start();
            
        // Instance
        $productoXFactura= new ProductoXFactura();
        switch($opt){
            case "ReadbyID":
                echo json_encode($productoXFactura->ReadbyID());
                break;
        }
        
    }
    
class ProductoXFactura{
   
    public static $id=null;

    function __construct(){
        // identificador único
        if(isset($_POST["id"])){
            $this->id= $_POST["id"];
        }
        
        
        if(isset($_POST["obj"])){
            $obj= json_decode($_POST["obj"],true);
            self::$id= $obj ?? null;
        }
    }


    public static function Read(){
        try{
            $sql="SELECT detalle from productosXFactura
            where idFactura = :idDistribucion";
            $param= array(':idDistribucion'=>self::$id);
            $data = DATA::Ejecutar($sql,$param);            
            $lista = [];
            foreach ($data as $key => $value){
                $producto = new ProductoXFactura();
                $producto->detalle = $value['detalle']; //id del producto.       
                array_push ($lista, $producto);
            }
            return $lista;
        }
        catch(Exception $e) {
            return false;
        }
    }

    function ReadbyID(){
        try {
            $sql='SELECT detalle, cantidad, montoTotalLinea from productosXFactura
            where idFactura =:id';
            $param= array(':id'=>self::$id);
            $data= DATA::Ejecutar($sql,$param);   
            return $data;
        }     
        catch(Exception $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar el factura'))
            );
        }
    }

    public static function Create($obj){
        try {
            $created = true;
            $idUnidadMedida = 33;
            $montoTotal = 0;
            $subTotal = 0;
            $montoTotalLinea = 0;


            foreach ($obj as $item) {       
                
                $subTotal =  $item->cantidad * $item->precioUnitario;
                $montoTotalLinea = $item->montoTotal;
                $sql="INSERT INTO productosXFactura (id, idFactura, numeroLinea, cantidad, idUnidadMedida, detalle, 
                                                    precioUnitario, montoTotal, subTotal, montoTotalLinea)

                VALUES (uuid(), :idFactura, :numeroLinea, :cantidad, :idUnidadMedida, :detalle, 
                        :precioUnitario, :montoTotal, :subTotal, :montoTotalLinea)";              

                $param= array(':idFactura'=>self::$id, ':numeroLinea'=>$item->numeroLinea, 
                ':cantidad'=>$item->cantidad, ':idUnidadMedida'=>$idUnidadMedida,':detalle'=>$item->detalle, 
                ':precioUnitario'=>$item->precioUnitario, ':montoTotal'=>$item->montoTotal, ':subTotal'=>$subTotal, 
                ':montoTotalLinea'=>$montoTotalLinea);

                $data = DATA::Ejecutar($sql,$param, false);

            }
            return true;
        }     
        catch(Exception $e) {
            return false;
        }
    }
}
?>    
   


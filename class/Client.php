<?php
if(isset($_POST["action"])){
    $opt= $_POST["action"];
    unset($_POST['action']);
    // Classes
    require_once("Conexion.php");
    // require_once("Usuario.php");
    // Session
    // if (!isset($_SESSION))
    //     session_start();
    // Instance
    $client= new Client();
    switch($opt){
        case "ReadAll":
            echo json_encode($client->ReadAll());
            break;
        case "Create":
            $client->Create();
            break;
        case "Update":
            $client->Update();
            break;
        case "Delete":
            echo json_encode($client->Delete());
            break;   
        case "ReadByCode":  
            echo json_encode($client->ReadByCode());
            break;
    }
}

class client{

    public $id=null;
    public $name='';
    public $tb_Clients=[];
    public $tb_Device_x_Clients=[];
    public $company='';
    public $tel='';
    public $email='';

    function __construct(){
        // identificador único
        if(isset($_POST["id"])){
            $this->id= $_POST["id"];
        }
        if(isset($_POST["obj"])){
            $obj= json_decode($_POST["obj"],true);
            $this->id= $obj["id"] ?? null;
            $this->name= $obj["name"] ?? '';
            $this->tb_Clients= $obj["tb_Clients"] ?? '';
            $this->tb_Device_x_Clients= $obj["tb_Device_x_Clients"] ?? '';
            $this->company= $obj["company"] ?? '';
            $this->tel= $obj["tel"] ?? '';
            $this->email= $obj["email"] ?? '';
        }
    }

    function ReadAll(){
        try {
            $sql='SELECT id, name, company, tel, email 
                FROM client                 
                ORDER BY name desc';
            $data= DATA::Ejecutar($sql);
            return $data;
        }     
        catch(Exception $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    function ReadAllProductoVenta(){
        try {
            $sql='SELECT ib.id, p.codigo, p.nombre, p.txtColor, p.bgColor, p.nombreAbreviado, p.descripcion, ib.saldoCantidad, p.esVenta
            FROM insumosXBodega as ib
            INNER JOIN  producto as p on p.id = ib.idProducto
            WHERE (esVenta=1 or esVenta=2)
            and ib.idBodega = :idBodega
            ORDER BY p.nombre';
            $param= array(':idBodega'=>$_SESSION["userSession"]->idBodega);
            $data= DATA::Ejecutar($sql,$param);
            return $data;
        }     
        catch(Exception $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }
  
    // Si hago el filtro por tipo en el javascript ya no necesito esta funcion
    function ReadAllPrdVenta(){
        try {
            $sql='SELECT id, codigo, nombre, txtColor, bgColor, nombreAbreviado, descripcion, saldoCantidad, saldoCosto, costoPromedio, precioVenta, esVenta
                FROM     producto       
                WHERE esVenta=1 or esVenta=2
                ORDER BY codigo asc';
            $data= DATA::Ejecutar($sql);
            return $data;
        }     
        catch(Exception $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar la lista'))
            );
        }
    }

    function Read(){
        try {
            $sql='SELECT id, codigo, nombre, txtColor, bgColor, nombreAbreviado, descripcion, saldoCantidad, saldoCosto, costoPromedio, precioVenta, esVenta
                FROM producto  
                where id=:id';
            $param= array(':id'=>$this->id);
            $data= DATA::Ejecutar($sql,$param);
            return $data;
        }     
        catch(Exception $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar el producto'))
            );
        }
    }

    function ReadArticulo(){
        try {
            $sql='SELECT id, nombre, codigo, descripcion, saldoCosto, costoPromedio, precioVenta, esVenta
                FROM producto  
                where id=:id and esVenta=0';
            $param= array(':id'=>$this->id);
            $data= DATA::Ejecutar($sql,$param);
            return $data;
        }     
        catch(Exception $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar el producto'))
            );
        }
    }

    function ReadArticuloByCode(){
        try {
            $sql='SELECT id, nombre, codigo, descripcion, saldoCosto, costoPromedio, precioVenta, esVenta
                FROM producto  
                where codigo=:codigo and esVenta=0';
            $param= array(':codigo'=>$this->codigo);
            $data= DATA::Ejecutar($sql,$param);
            return $data;
        }     
        catch(Exception $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => 'Error al cargar el producto'))
            );
        }
    }

    function Create(){
        try {
            // $this->txtColor = "010203";
            // $this->bgColor = "040506";
            $sql="INSERT INTO tropical.producto   (id, nombre, codigo, txtColor, bgColor, nombreAbreviado, descripcion, saldoCantidad, saldoCosto, costoPromedio, precioVenta, esVenta) 
            VALUES (uuid(), :nombre, :codigo ,:txtColor, :bgColor, :nombreAbreviado, :descripcion, :saldoCantidad, :saldoCosto, :costoPromedio ,:precioVenta, :esVenta);";
            //
            $param= array(':nombre'=>$this->nombre, 
            ':codigo'=>$this->codigo,
            ':txtColor'=>$this->txtColor,
            ':bgColor'=>$this->bgColor,
            ':nombreAbreviado'=>$this->nombreAbreviado,
            ':descripcion'=>$this->descripcion,
            ':saldoCantidad'=>$this->saldoCantidad,
            ':saldoCosto'=>$this->saldoCosto,
            ':costoPromedio'=>$this->costoPromedio,
            ':precioVenta'=>$this->precioVenta, 
            ':esVenta'=>$this->esVenta);

            $data = DATA::Ejecutar($sql,$param, false);
            if($data)
            {
                //get id.
                //save array obj
                return true;
            }
            else throw new Exception('Error al guardar.', 02);
        }     
        catch(Exception $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    function Update(){
        try {
            $sql="UPDATE producto 
                SET codigo=:codigo, nombre=:nombre, txtColor=:txtColor, bgColor=:bgColor, nombreAbreviado=:nombreAbreviado, descripcion=:descripcion, saldoCantidad=:saldoCantidad, saldoCosto=:saldoCosto, costoPromedio=:costoPromedio, precioVenta=:precioVenta, esVenta=:esVenta
                WHERE id=:id";
            $param= array(':id'=>$this->id, ':codigo'=>$this->codigo, ':nombre'=>$this->nombre, ':txtColor'=>$this->txtColor,':bgColor'=>$this->bgColor,':nombreAbreviado'=>$this->nombreAbreviado,':descripcion'=>$this->descripcion,':saldoCantidad'=>$this->saldoCantidad,':saldoCosto'=>$this->saldoCosto,':costoPromedio'=>$this->costoPromedio,':precioVenta'=>$this->precioVenta, ':esVenta'=>$this->esVenta);
            $data = DATA::Ejecutar($sql,$param,false);
            if($data)
                return true;
            else throw new Exception('Error al guardar.', 123);
        }     
        catch(Exception $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    private function CheckRelatedItems(){
        try{
            $sql="SELECT id
                FROM /*  definir relacion */ R
                WHERE R./*definir campo relacion*/= :id";                
            $param= array(':id'=>$this->id);
            $data= DATA::Ejecutar($sql, $param);
            if(count($data))
                return true;
            else return false;
        }
        catch(Exception $e){
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    function Delete(){
        try {
            // if($this->CheckRelatedItems()){
            //     //$sessiondata array que devuelve si hay relaciones del objeto con otras tablas.
            //     $sessiondata['status']=1; 
            //     $sessiondata['msg']='Registro en uso'; 
            //     return $sessiondata;           
            // }                    
            $sql='DELETE FROM producto  
            WHERE id= :id';
            $param= array(':id'=>$this->id);
            $data= DATA::Ejecutar($sql, $param, false);
            if($data)
                return $sessiondata['status']=0; 
            else throw new Exception('Error al eliminar.', 978);
        }
        catch(Exception $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    function ReadByCode(){
        try{     
            $sql="SELECT id, nombre, codigo, descripcion, saldoCosto, costoPromedio, precioVenta, esVenta
                FROM producto 
                WHERE codigo= :codigo";
            $param= array(':codigo'=>$this->codigo);

            $data= DATA::Ejecutar($sql,$param);
            
            if(count($data))
                return $data;
            else return false;
        }
        catch(Exception $e){
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    function ActualizaPrecios(){
        try{     
            $created = true;
            foreach ($this->lista as $item) {
                $sql="UPDATE producto
                    SET precioVenta=:precioVenta
                    WHERE id= :id";
                $param= array(':id'=>$item->id, ':precioVenta'=>$item->precioVenta);
                $data= DATA::Ejecutar($sql,$param, false);
                if(!$data)
                    $created= false;                
            }
            if(!$created)
                throw new Exception('Error al actualizar precios, REVISAR manualmente.', 666);
            else return true;
            // 
        }
        catch(Exception $e){
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }

    public static function UpdateSaldoPromedioSalida($id, $ncantidad){
        try {
            $sql="CALL spUpdateSaldosPromedioProductoSalida(:mid, :ncantidad);";
            $param= array(':mid'=>$id, ':ncantidad'=>$ncantidad);
            $data = DATA::Ejecutar($sql,$param,false);
            if($data)
                return true;
            else throw new Exception('Error al calcular SALDOS Y PROMEDIOS, debe realizar el cálculo manualmente.', 666);
        }     
        catch(Exception $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    }  
    
    public static function UpdateSaldoProducto($id, $ncantidad, $ncosto){
        try {
            $sql="CALL spUpdateSaldosPromedioProducto(:mid, :ncantidad, :ncosto);";
            $param= array(':mid'=>$id, ':ncantidad'=>$ncantidad, ':ncosto'=>$ncosto);
            $data = DATA::Ejecutar($sql,$param,false);
            if($data)
                return true;
            else throw new Exception('Error al calcular SALDOS Y PROMEDIOS, debe realizar el cálculo manualmente.', 666);
        }     
        catch(Exception $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }
    } 
}



?>
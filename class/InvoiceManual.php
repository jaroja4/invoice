<?php
if(isset($_POST["action"])){
    $opt= $_POST["action"];
    unset($_POST['action']);
    // Classes
    require_once("Conexion.php");
    require_once("ProductoXFactura.php");
    include("WebToPDF/InvoicePrinter.php");
    require_once("UUID.php");
    require "mail/mail.php";
    
    // Instance
    $invoice= new Invoice();
    switch($opt){
        case "Create":
            $invoice->Create();
            break;
        case "ReadByCode":  
            echo json_encode($invoice->ReadByCode());
            break;
    }
}

class invoice{

    //Variables de Factura Incluye variables minimas para FE
    public $id=null;

    public $fechaCreacion = "";
    public $consecutivo = "";
    public $local = "001";
    public $terminal = "00001";
    public $idCondicionVenta = "1";
    public $idSituacionComprobante = "1";
    public $idEstadoComprobante = "1";
    public $idMedioPago = "1";
    public $fechaEmision = "";//Obligatorio
    public $totalVenta = 0;
    public $totalDescuentos = 0;
    public $totalVentaneta = "0.00000";
    public $totalImpuesto = "0";
    public $totalComprobante =0;
    public $idEmisor = "1f85f425-1c4b-4212-9d97-72e413cffb3c";
    public $idUser = "1f85f425-1c4b-4212-9d97-72e413cffb3c";

    // Variables propias de la empresa
    public $masterInvoice=[];
    public $frecuencyPay ="";
    public $cant =1;
    public $priceMonitoring ="";
    public $impuestos = 0.13;
    public $total_iv = 0;
    public $estado ="";
    public $detalleFactura =[];
   


    //Variables de Empresa
    public $nameCompany ="GPSMovilCR";
    public $Department ="Soporte al Cliente";
    public $location ="UltraPark II";
    public $address ="Heredia";
    public $country ="Costa Rica";


    //Variables de Cliente
    public $idClient=null;
    public $nameClient ="";
    public $companyClient ="";
    public $provincia ="";
    public $canton ="";
    public $countryClient ="";
    public $email ="";


    
    function __construct(){
        // identificador único
        if(isset($_POST["id"])){
            $this->id= $_POST["id"];
        }
        if(isset($_POST["nameClient"])){
            $this->nameClient= json_decode($_POST["nameClient"],true);
        }
    }
    
    function CreateMasterSQL(){
        try {

            $this->fechaCreacion = date("Y-m-d H:i:s");
            $this->fechaEmision = date("D \d\\e F Y");
            $this->estado = 0;
            $this->id = UUID::v4();

            $sql="INSERT INTO factura (id, estado, fechaCreacion, local, terminal, 
                                        idCondicionVenta,idSituacionComprobante,idEstadoComprobante, 
                                        idMedioPago,fechaEmision, totalVenta, totalDescuentos, 
                                        totalVentaneta, totalImpuesto, totalComprobante, idEmisor, 
                                        idUsuario)
                                       
            VALUES  (:uuid, :estado, :fechaCreacion, :local, :terminal, :idCondicionVenta, :idSituacionComprobante, :idEstadoComprobante, :idMedioPago, :fechaEmision, :totalVenta, :totalDescuentos, :totalVentaneta, :totalImpuesto, :totalComprobante, :idEmisor, :idUsuario)"; 
       
            $param= array(':uuid'=>$this->id, ':estado'=>$this->estado, ':fechaCreacion'=>$this->fechaCreacion, ':local'=>$this->local, ':terminal'=>$this->terminal, 
                    ':idCondicionVenta'=>$this->idCondicionVenta, ':idSituacionComprobante'=>$this->idSituacionComprobante, ':idEstadoComprobante'=>$this->idEstadoComprobante, 
                    ':idMedioPago'=>$this->idMedioPago, ':fechaEmision'=>$this->fechaEmision, ':totalVenta'=>$this->totalVenta, ':totalDescuentos'=>$this->totalDescuentos, 
                    ':totalVentaneta'=>$this->totalVentaneta, ':totalImpuesto'=>$this->totalImpuesto, ':totalComprobante'=>$this->totalComprobante, ':idEmisor'=>$this->idEmisor, 
                    ':idUsuario'=>$this->idUser/*$_SESSION["userSession"]->id*/);
            
            $data = DATA::Ejecutar($sql,$param, false);
            return true;
        }     
        catch(Exception $e) {
            header('HTTP/1.0 400 Bad error');
            die(json_encode(array(
                'code' => $e->getCode() ,
                'msg' => $e->getMessage()))
            );
        }

    }

    function Create(){

        // $this->masterInvoice = $this->ReadAll();

        foreach ($this->masterInvoice as $mInvoice) {

            if($this->CreateMasterSQL()){

                try {
                    $InvoicePrinter = new InvoicePrinter("A4", "$", "es");
    
                    /* Header Settings */
                    $InvoicePrinter->setTimeZone('America/Costa_Rica');
                    $InvoicePrinter->setLogo("WebToPDF/images/logo.png");
                    $InvoicePrinter->setColor("#007fff");//Numero de contrato
                    $InvoicePrinter->setType("Notificación de Factura");
                    $InvoicePrinter->setReference($mInvoice['number']); 
                    $InvoicePrinter->setDate(date('M dS ,Y',time()));
                    $InvoicePrinter->setTime(date('h:i:s A',time()));
                    $InvoicePrinter->setDue(date('M dS ,Y',strtotime('+1 months')));
                    $InvoicePrinter->setFrom(array("GPSMovilCR","Soporte al Cliente","UltraPark II","Heredia","Costa Rica"));
    
                    try {
                        $sql='SELECT company, city, state, country, email
                        FROM client
                        WHERE id = :id;';
                        $param= array(':id'=> $mInvoice['idCliente']);
                        $data= DATA::Ejecutar($sql, $param);
                        if($data){
                            $InvoicePrinter->setTo(array($mInvoice['name'],$data[0]['company'],$data[0]['city'],$data[0]['state'],$data[0]['country'])); 
                            $this->email = $data[0]['email'];
                        }
                    }     
                    catch(Exception $e) {
                        header('HTTP/1.0 400 Load Devices Bad error');
                        die(json_encode(array(
                            'code' => $e->getCode() ,
                            'msg' => 'Error al cargar la lista'))
                        );
                    }
    
                    /* Adding Items in table */
                    try {
                        $sql='SELECT description, frequencyPay, priceMonitoring
                        FROM device
                        WHERE idContract = :idContract;';
                        $param= array(':idContract'=> $mInvoice['idContrato']);
                        $data= DATA::Ejecutar($sql, $param);
                        if($data){
                            foreach ($data as $key => $value){
                                $this->description = $value['description'];
                                $this->frequencyPay = $value['frequencyPay'];
                                $this->priceMonitoring = $value['priceMonitoring'];
                                // $this->totalComprobante = $this->totalComprobante + $value['priceMonitoring'];
                                $item_iv = (($this->cant * $this->priceMonitoring)*$this->impuestos);
                                $item_total = ($this->cant*($item_iv+$this->priceMonitoring));
                                $discount = 0;
                                $InvoicePrinter->addItem($this->description, $this->frequencyPay, $this->cant, $item_iv, $this->priceMonitoring,$discount,$item_total);
                                
                                $item= new ProductoXFactura();
                                $item->detalle = $this->description . " " . $this->frequencyPay;
                                $item->cantidad= $this->cant;
                                $item->numeroLinea= $key+1;
                                $item->item_iv= $item_iv;
                                $item->precioUnitario=  $this->priceMonitoring;
                                $item->discount=  $discount;
                                $item->montoTotal=$item_total;
                                array_push ($this->detalleFactura, $item);

                                $this->totalComprobante = $this->totalComprobante + $item_total;
                                $this->total_iv = $this->total_iv + $item_iv;
                                $this->totalDescuentos = $this->totalDescuentos + $discount;
                            }
                            ProductoXFactura::$id=$this->id;
                            ProductoXFactura::Create($this->detalleFactura);
                        }
                    }     
                    catch(Exception $e) {
                        header('HTTP/1.0 400 Load Devices Bad error');
                        die(json_encode(array(
                            'code' => $e->getCode() ,
                            'msg' => 'Error al cargar la lista'))
                        );
                    }
                    
                    /* Add totals */
                    $InvoicePrinter->addTotal("Descuento",($this->totalDescuentos*-1));
                    $InvoicePrinter->addTotal("IV 13%",($this->total_iv));
                    $InvoicePrinter->addTotal("Total+IV",($this->totalComprobante),true);
                    $this->totalComprobante = 0;
                    $this->total_iv = 0;
                    $this->totalDescuentos = 0;
                    $this->detalleFactura = [];
                    /* Set badge */ 
                    // $InvoicePrinter->addBadge("Payment Paid");
                    $InvoicePrinter->addBadge("Factura Notificada");
                    /* Add title */
                    $InvoicePrinter->addTitle("Detalle:");
                    /* Add Paragraph */
                    $InvoicePrinter->addParagraph("FECHA DE EMISIÓN: " . date("d/m/Y") . ", HORA: 07:20 - AUTORIZADO MEDIANTE EL OFICIO DE LA DGT NO. 11-97 DEL 12 DE AGOSTO DE 1997.");
                    /* Set footer note */
                    $InvoicePrinter->setFooternote("GPSMovilCR");
                    /* Render */
                    $path_fecha = "../Invoices/" . date("dmYHi") ."_". str_replace(' ', '', $mInvoice['name']) . ".pdf";
                
                    // $InvoicePrinter->Output($path_fecha, 'I'); //Con esta funcion imprime el archivo en otra ubicacion
                
                    $InvoicePrinter->render($path_fecha,'F'); /* I => Display on browser, D => Force Download, F => local path save, S => return document path */
                    
                    
                    $mail = new Send_Mail();
                    $mail->address_to = $this->email;
                    $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
 
                    $mail->the_subject = "Factura Monitoreo: - " . $meses[date('n')-1] . " ".date('Y');   
                    $mail->addAttachment = $path_fecha;
                
                    // $mail->send();
                }     
                catch(Exception $e) {
                    header('HTTP/1.0 400 Error al generar la factura');
                    die(json_encode(array(
                        'code' => $e->getCode() ,
                        'msg' => $e->getMessage()))
                    );
                }
            };            
        }
    }

    function ReadByCode(){
        try{     
            $sql="SELECT cl.id as idCliente, cl.name, ct.id as idContrato, ct.number, ct.creation, ct.expires, ct.bill_day,  group_concat(DISTINCT dv.frequencyPay) as frequencyPay, sum(dv.priceMonitoring) as priceMonitoring, ct.methodPayment
            FROM contracts ct
            INNER JOIN client cl on ct.idClient = cl.id     
            InNER JOIN device dv on dv.idContract = ct.id     
            WHERE cl.name LIKE '%" .  $this->nameClient . "%'
            group by idContrato     
            ORDER BY cl.name asc;";
            // $param= array(':nameClient'=> $this->nameClient);
            // $data= DATA::Ejecutar($sql,$param);
            $data= DATA::Ejecutar($sql);
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
}



?>
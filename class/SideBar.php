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
    $sideBar= new SideBar();
    switch($opt){
        case "ReadAll":
            echo json_encode($sideBar->ReadAll());
            break;
    }
}

class sideBar{

    public $id=null;
    public $menuL1='';
    public $menuL2='';
    public $menuL3='';
    public $url='';
    public $icon='';

    function __construct(){
        // identificador único
        if(isset($_POST["url"])){
            $this->url= $_POST["url"];
        }
    }

    function ReadAll(){
        try {
            $sql='SELECT id, menuL1, menuL2, menuL3, url, icon 
            FROM Event;';
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
}



?>
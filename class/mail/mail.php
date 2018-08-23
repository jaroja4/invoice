<?php
    require 'Exception.php';
    require 'PHPMailer.php';
    require 'SMTP.php';
    setlocale(LC_ALL,"es_ES");
    mb_internal_encoding('UTF-8');

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    class Send_Mail {

        public $address_to="";
        public $the_subject = "";
        public $addAttachment = null;

        function send(){

            $email_user = "info@gpsmovilcr.com";
            $email_password = "Rmrm2088a1+";
            $from_name = "GPSMovil";

            $phpmailer  = new PHPMailer(true);   // Passing `true` enables exceptions
            $phpmailer -> CharSet = "UTF-8";
            try {
                //Server settings
            $phpmailer->Username = $email_user;
            $phpmailer->Password = $email_password; 
            //-----------------------------------------------------------------------
            // $phpmailer->SMTPDebug = 1;
            $phpmailer->SMTPSecure = 'ssl';
            // $phpmailer->Host = "smtp.gmail.com"; // GMail
            $phpmailer->Host = "smtpout.asia.secureserver.net";
            // $phpmailer->Port = 465; // Gmail
            $phpmailer->Port = 80;
            $phpmailer->IsSMTP(); // use SMTP Gmail
            $phpmailer->SMTPSecure = "none"; // GD
            // $phpmailer->SMTPAuth = true;
            $phpmailer->SMTPAuth = true;
            $phpmailer->setFrom($phpmailer->Username,$from_name);
            $phpmailer->AddAddress($this->address_to); // recipients email
            $phpmailer->Subject = $this->the_subject;	

            //Attachments
            // $phpmailer->addAttachment('../Invoices/example2.pdf');         // Add attachments
            $phpmailer->addAttachment($this->addAttachment);

            $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
            $mes = $meses[date('n')-2];

            $phpmailer->Body .="<h1 style='color:#3498db;'>Listo, aquí tienes tu factura por el mes de " . $mes . "</h1>";
            $phpmailer->Body .= "<p>Para cancelar la factura de tu servicio de monitoreo, puede contactarnos para inscribir su tarjeta de crédito en el servicio de cobro automático o puede realizar una transferencia a nuestra cuenta del Banco Nacional de Costa Rica:</p>
                                <br>
                                Cuenta: 100-01-173-000905-3 <br>
                                Cuenta Cliente: 15117310010009055 <br>
                                <br>
                                <p>Le agradecemos confirmar que recibió la información y para cualquier consulta puede contactarnos, muchas gracias por confiar en nosotros.</p>";
            
            $phpmailer->Body .= "<br><br> <img src='http://gpsmovilpro.com/img/logo.png' border='0' />
                                <p>Soporte al Cliente
                                <br><br>
                                UltraPark II<br>
                                Heredia<br>
                                Costa Rica<br>
                                CEL: +(506) 84903674<br>
                                TEL: +(506) 22323265<br>
                                web: www.gpsmovilcr.com</p>";
            
            $phpmailer->IsHTML(true);
            $phpmailer->Send();
            } catch (Exception $e) {
                echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
            }
        }
    }
?>
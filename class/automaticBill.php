<?php
$url = 'http://localhost:8080/gpsmovil/class/Invoice.php';
$data = array('action' => 'Create');
$options = array(
        'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
    )
);

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
?>
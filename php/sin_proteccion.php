<?php
session_start();
$sesion_i = $_SESSION['usuario'];
error_reporting(0);

if($sesion_i == null || $sesion_i = ""){
    echo `
    <script>
    alert("No tiene sesion activa");
    location.href = "index.php";
    </script>
    `;
    die();
}
?>
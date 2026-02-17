<?php 
session_start();
error_reporting(0);
$sesion_i = $_SESSION['usuario'];

if($sesion_i != ""){
    header("location:index.php");
}
?>
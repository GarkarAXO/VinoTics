<?php 
error_reporting(0); // Considerar si es necesario mantener esto globalmente o solo en desarrollo
include('php/conexion.php'); 
include('php/sin_proteccion.php'); 
$usuario = $_SESSION['usuario'];
?>

<?php require('./layout/header.php') ?>

<title>Perfil</title>
<h1>Sesion activa de : <?php echo $_SESSION['usuario']; ?></h1>

<?php require('./layout/footer.php') ?>
<?php
    // error_reporting(0); // Desactivado para depuración, dejar para ver errores
    include ('conexion.php');

    // Recoger y sanear los datos del formulario
    $nombre_completo = mysqli_real_escape_string($conexion, $_POST['nombre_completo']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $contrasena = mysqli_real_escape_string($conexion, $_POST['contrasena']);

    // Cifrar la contraseña
    $contrasena_hashed = password_hash($contrasena, PASSWORD_DEFAULT);
    
    // Verificar que el correo no se repita en la BD usando sentencia preparada
    $stmt_correo = mysqli_prepare($conexion, "SELECT COUNT(*) FROM usuarios WHERE correo = ?");
    mysqli_stmt_bind_param($stmt_correo, "s", $correo);
    mysqli_stmt_execute($stmt_correo);
    mysqli_stmt_bind_result($stmt_correo, $correo_count);
    mysqli_stmt_fetch($stmt_correo);
    mysqli_stmt_close($stmt_correo);

    if ($correo_count > 0) {
        echo '
            <script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Este correo ya está registrado, intenta con otro diferente.",
                    showConfirmButton: true
                }).then(() => {
                    window.location = "../login_registro.php";
                });
            </script>
        ';
        exit;
    }

    // Verificar que el usuario no se repita en la BD usando sentencia preparada
    $stmt_usuario = mysqli_prepare($conexion, "SELECT COUNT(*) FROM usuarios WHERE usuario = ?");
    mysqli_stmt_bind_param($stmt_usuario, "s", $usuario);
    mysqli_stmt_execute($stmt_usuario);
    mysqli_stmt_bind_result($stmt_usuario, $usuario_count);
    mysqli_stmt_fetch($stmt_usuario);
    mysqli_stmt_close($stmt_usuario);
        
    if ($usuario_count > 0) {
        echo '
            <script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Este usuario ya está registrado, intenta con otro diferente.",
                    showConfirmButton: true
                }).then(() => {
                    window.location = "../login_registro.php";
                });
            </script>
        ';
        exit;
    }

    // Registrando usuarios con sentencia preparada
    $stmt_insert = mysqli_prepare($conexion, "INSERT INTO usuarios(nombre_completo, correo, usuario, contrasena) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_insert, "ssss", $nombre_completo, $correo, $usuario, $contrasena_hashed);
    
    if (mysqli_stmt_execute($stmt_insert)) {
        echo '
            <script> 
                Swal.fire({
                    icon: "success",
                    title: "¡Registro Exitoso!",
                    text: "Usuario registrado exitosamente.",
                    showConfirmButton: true
                }).then(() => {
                    window.location = "../index.php";
                });
            </script>
        ';
    } else {
        echo '
            <script> 
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo registrar su usuario: ' . mysqli_error($conexion) . '",
                    showConfirmButton: true
                }).then(() => {
                    window.location = "../login_registro.php";
                });
            </script> 
        ';
    }
    mysqli_stmt_close($stmt_insert);
    mysqli_close($conexion);
?>
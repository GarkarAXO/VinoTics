<?php

    session_start();
    // error_reporting(0); // Desactivado para depuración

    include 'conexion.php';

    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
    $contrasena = $_POST['contrasena']; // La contraseña en texto plano para verificar

    $response = ['success' => false, 'message' => ''];

    // Usar sentencia preparada para buscar el usuario
    $stmt = mysqli_prepare($conexion, "SELECT id, usuario, contrasena FROM usuarios WHERE usuario = ?");
    mysqli_stmt_bind_param($stmt, "s", $usuario);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt); // Almacenar el resultado para poder usar num_rows
    
    if (mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_bind_result($stmt, $id_db, $usuario_db, $contrasena_hashed_db);
        mysqli_stmt_fetch($stmt);
        
        // Verificar la contraseña cifrada
        if (password_verify($contrasena, $contrasena_hashed_db)) {
            $_SESSION['usuario'] = $usuario_db;
            $_SESSION['login_message'] = '¡Bienvenido ' . htmlspecialchars($usuario_db) . '!';
            $_SESSION['login_message_type'] = 'success';
            // Redirección directa tras login exitoso
            header("Location: ../index.php");
            exit;
        } else {
            $response['message'] = 'Contraseña incorrecta. Por favor, verifique los datos.';
        }
    } else {
        $response['message'] = 'Usuario no encontrado. Por favor, verifique los datos.';
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conexion);

    // Si hubo un error o credenciales inválidas, mostrar SweetAlert y redirigir
    echo '
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: "error",
                title: "Error de inicio de sesión",
                text: "' . $response['message'] . '",
                showConfirmButton: true
            }).then(() => {
                window.location = "../login_registro.php";
            });
        </script>
    ';
    exit;
?>
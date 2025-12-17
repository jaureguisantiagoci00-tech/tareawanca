<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // MODIFICADO: Incluir el rol en la consulta
    $sql = "SELECT id, nombre_completo, contrasena_hash, rol FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['contrasena_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre_completo'];
            $_SESSION['user_role'] = $user['rol']; // NUEVO: Guardar rol en sesión
            $_SESSION['loggedin'] = true;
            
            // Redirigir según el rol
            if ($user['rol'] === 'ADMIN' || $user['rol'] === 'VENDEDOR') {
                header("Location: ../dashboard/index.php");
            } else {
                header("Location: ../index.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Contraseña incorrecta";
            header("Location: ../index.php?error=password");
            exit();
        }
    } else {
        $_SESSION['error'] = "Usuario no encontrado";
        header("Location: ../index.php?error=user");
        exit();
    }

} else {
    header('Location: ../index.php');
    exit();
}
?>
<?php
// Inicia la sesión para mostrar mensajes de éxito/error en el futuro
session_start();

// Datos de Conexión a la Base de Datos
// Asegúrate de que tu XAMPP esté corriendo el servicio MySQL.
$servername = "localhost";
$username = "root";       // Usuario por defecto de XAMPP
$dbpassword = "";         // Contraseña por defecto de XAMPP (vacía, cámbiala si la configuraste)
$dbname = "ProyectoVeterinaria"; // Nombre de la BD que indicaste

// 1. Verificar que el formulario haya sido enviado por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtener y limpiar los datos del formulario
    $nombre_completo = trim($_POST['nombre_completo']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirmacion = $_POST['password_confirmacion'];

    // 2. Validaciones básicas
    if (empty($nombre_completo) || empty($email) || empty($password) || empty($password_confirmacion)) {
        die("Todos los campos son obligatorios.");
    }
    
    if ($password !== $password_confirmacion) {
        die("Las contraseñas no coinciden.");
    }

    // 3. Crear el hash de la contraseña por seguridad
    $contrasena_hash = password_hash($password, PASSWORD_DEFAULT);

    // 4. Conexión a la BD
    $conn = new mysqli($servername, $username, $dbpassword, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // 5. Preparar la consulta SQL para INSERTAR (usando prepared statements por seguridad)
    $sql = "INSERT INTO usuarios (nombre_completo, email, contrasena_hash) VALUES (?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Vincular parámetros: "s" significa string
    $stmt->bind_param("sss", $nombre_completo, $email, $contrasena_hash);

    // 6. Ejecutar la consulta
    if ($stmt->execute()) {
        echo "✅ ¡Registro exitoso! Ahora puedes iniciar sesión.";
        // Si deseas, puedes redirigir al usuario a la página principal después de un tiempo
        // header("refresh:3; url=index.html");
    } else {
        // Error de BD (ej: el email ya existe porque es UNIQUE)
        if ($conn->errno == 1062) {
             echo "❌ Error: El correo electrónico ya está registrado.";
        } else {
             echo "❌ Error al registrar: " . $stmt->error;
        }
    }

    // 7. Cerrar la conexión
    $stmt->close();
    $conn->close();

} else {
    // Si no es POST, rechazar acceso directo
    header('Location: ../idex.html'); // Redirigir a la página principal
    exit();
}
?>
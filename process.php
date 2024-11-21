<?php

echo "<pre>";
print_r($_POST);
echo "</pre>";

// Cargar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';


// Conexión a la base de datos
$host = "localhost";
$user = "root";
$password = "";
$dbname = "formulario";

$conn = new mysqli($host, $user, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['correo']) && !empty($_POST['correo']) && filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
        $correo = $conn->real_escape_string($_POST['correo']);
    } else {
        die("Error: El correo no está definido, está vacío o no es válido.");
    }

    $nombre = $conn->real_escape_string($_POST['nombre']);
    $correo = $conn->real_escape_string($_POST['correo']);
    $direccion = $conn->real_escape_string($_POST['direccion']);
    $ubicacion = $conn->real_escape_string($_POST['ubicacion']);
    $mensaje = $conn->real_escape_string($_POST['mensaje']);
    
    // Manejo del archivo adjunto
    $archivo_nombre = "";
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
        $archivo_nombre = basename($_FILES['archivo']['name']);
        $ruta_destino = "uploads/" . $archivo_nombre;
        move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino);
    }

    // Insertar datos en la base de datos
    $sql = "INSERT INTO formulario_contacto (nombre, correo, direccion, ubicacion, mensaje, archivo_nombre) 
            VALUES ('$nombre', '$correo', '$direccion', '$ubicacion', '$mensaje', '$archivo_nombre')";

    if ($conn->query($sql) === TRUE) {
        // Configuración del correo

       
        
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor SMTP de Gmail
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'cosmos3events@gmail.com'; 
            $mail->Password = 'yselhbfsgslggnwn';       //  contraseña
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Destinatario y contenido
            $mail->setFrom('cosmos3events@gmail.com', 'Formulario Contacto');
            $mail->addAddress($correo, $nombre); // Correo del destinatario
            if ($archivo_nombre) {
                $mail->addAttachment($ruta_destino);
            }
            
            $mail->isHTML(true);
            $mail->Subject = 'Nuevo mensaje de contacto';
            $mail->Body = "<h3>Nuevo mensaje desde el formulario</h3>
                           <p><strong>Nombre:</strong> $nombre</p>
                           <p><strong>Correo:</strong> $correo</p>
                           <p><strong>Dirección:</strong> $direccion</p>
                           <p><strong>Ubicación:</strong> $ubicacion</p>
                           <p><strong>Mensaje:</strong> $mensaje</p>";

            $mail->send();
            echo "Datos guardados y correo enviado correctamente.";
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

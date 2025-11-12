<?php
// admin/perfil.php
session_start();

// Ajusta la ruta a tu database.php
include __DIR__ . '/../app/config/database.php';

// Verificar sesión
$usuario_id = $_SESSION['usuario_id'] ?? 0;
if (!$usuario_id) {
    header("Location: /views/usuarios/login.php");
    exit;
}

// Inicializar variables
$nombre = $apellido = $correo = $telefono = "";
$mensaje = "";

// Obtener datos del usuario
$sql = "SELECT nombre, apellido, correo, telefono FROM empleados WHERE id = ?";
$stmt = sqlsrv_query($conn, $sql, [$usuario_id]);
if ($stmt === false) die(print_r(sqlsrv_errors(), true));

if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $nombre = $row['nombre'];
    $apellido = $row['apellido'];
    $correo = $row['correo'];
    $telefono = $row['telefono'];
} else {
    $mensaje = "Usuario no encontrado.";
}

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'actualizar') {
    $nombre_post = trim($_POST['nombre'] ?? '');
    $apellido_post = trim($_POST['apellido'] ?? '');
    $correo_post = trim($_POST['correo'] ?? '');
    $telefono_post = trim($_POST['telefono'] ?? '');
    $password_post = $_POST['password'] ?? '';
    $confirm_post = $_POST['confirmar'] ?? '';

    $errores = [];

    if ($nombre_post === '') $errores[] = "El nombre no puede estar vacío.";
    if ($apellido_post === '') $errores[] = "El apellido no puede estar vacío.";
    if ($correo_post === '') $errores[] = "El correo no puede estar vacío.";
    if ($telefono_post === '') $errores[] = "El teléfono no puede estar vacío.";

    // Validar contraseña solo si se ingresó
    $hash_pass = null;
    if ($password_post !== '') {
        if ($password_post !== $confirm_post) {
            $errores[] = "Las contraseñas no coinciden.";
        } else {
            $hash_pass = password_hash($password_post, PASSWORD_BCRYPT);
        }
    }

    if (empty($errores)) {
        $params = [$nombre_post, $apellido_post, $correo_post, $telefono_post];
        $sql_update = "UPDATE empleados SET nombre = ?, apellido = ?, correo = ?, telefono = ?";
        if ($hash_pass !== null) {
            $sql_update .= ", password = ?";
            $params[] = $hash_pass;
        }
        $sql_update .= " WHERE id = ?";
        $params[] = $usuario_id;

        $stmt_update = sqlsrv_query($conn, $sql_update, $params);
        if ($stmt_update) {
            $mensaje = "Perfil actualizado correctamente.";
            $nombre = $nombre_post;
            $apellido = $apellido_post;
            $correo = $correo_post;
            $telefono = $telefono_post;
        } else {
            $errores[] = "Error al actualizar perfil.";
        }
    }
}

sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <style>
        :root {
            --humo-blanco: #F0F0F0;
            --negro: #111111;
            --rojo-brillante: #E7473C;
            --blanco-puro: #FFFFFF;
            --rojo-muy-claro: #FFE6E4;
            --sombra-negra-ligera: rgba(0, 0, 0, 0.1);
            --gris-claro: #DDD;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background-color: var(--humo-blanco);
            color: var(--negro);
        }

        /* Layout general con sidebar */
        .layout {
            min-height: 100vh;
            display: flex;
            padding: 2rem;
            background-color: var(--humo-blanco);
            margin-top: 140px;
        }

        /* Contenedor del perfil */
        .perfil-container {
            margin: 0 auto;
            max-width: 600px;
            width: 100%;
            background-color: var(--blanco-puro);
            padding: 2rem 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px var(--sombra-negra-ligera);
        }

        /* Título */
        .perfil-container h2 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--rojo-brillante);
        }

        /* Mensajes de error / éxito */
        .mensaje {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            box-shadow: 0 4px 10px var(--sombra-negra-ligera);
        }

        .mensaje.error {
            background-color: var(--rojo-muy-claro);
            border-left: 4px solid var(--rojo-brillante);
        }

        .mensaje.ok {
            background-color: var(--blanco-puro);
            border-left: 4px solid var(--rojo-brillante);
        }

        /* Formulario */
        .perfil-container form {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        /* Labels */
        .perfil-container label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--negro);
        }

        /* Inputs */
        .perfil-container input[type="text"],
        .perfil-container input[type="email"],
        .perfil-container input[type="tel"],
        .perfil-container input[type="password"] {
            width: 100%;
            padding: 0.55rem 0.7rem;
            border-radius: 8px;
            border: 1px solid var(--gris-claro);
            background-color: var(--humo-blanco);
            color: var(--negro);
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .perfil-container input[type="text"]::placeholder,
        .perfil-container input[type="email"]::placeholder,
        .perfil-container input[type="tel"]::placeholder,
        .perfil-container input[type="password"]::placeholder {
            color: #777;
        }

        .perfil-container input[type="text"]:focus,
        .perfil-container input[type="email"]:focus,
        .perfil-container input[type="tel"]:focus,
        .perfil-container input[type="password"]:focus {
            border-color: var(--rojo-brillante);
            background-color: var(--blanco-puro);
            box-shadow: 0 0 0 3px rgba(231, 71, 60, 0.25);
        }

        /* Botón */
        .perfil-container button[type="submit"] {
            margin-top: 1rem;
            padding: 0.7rem 1.2rem;
            border-radius: 999px;
            border: none;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            background-color: var(--rojo-brillante);
            color: var(--blanco-puro);
            box-shadow: 0 6px 14px rgba(231, 71, 60, 0.35);
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease, transform 0.1s ease;
        }

        .perfil-container button[type="submit"]:hover {
            background-color: var(--blanco-puro);
            color: var(--rojo-brillante);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.22);
        }

        .perfil-container button[type="submit"]:active {
            transform: translateY(1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18);
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .layout {
                padding: 1rem;
            }

            .perfil-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/sidebar.php'; ?>


    <div class="layout">
        <div class="perfil-container">
            <h2>Mi Perfil</h2>

            <?php
            if (!empty($errores)) {
                foreach ($errores as $e) echo "<div class='mensaje error'>" . htmlspecialchars($e) . "</div>";
            } elseif (!empty($mensaje)) {
                echo "<div class='mensaje ok'>" . htmlspecialchars($mensaje) . "</div>";
            }
            ?>

            <form method="POST" action="perfil.php">
                <input type="hidden" name="accion" value="actualizar">

                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>

                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($apellido); ?>" required>

                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($correo); ?>" required>

                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>" required>

                <label for="password">Nueva Contraseña (opcional)</label>
                <input type="password" id="password" name="password">

                <label for="confirmar">Confirmar Contraseña</label>
                <input type="password" id="confirmar" name="confirmar">

                <button type="submit">Actualizar Perfil</button>
            </form>
        </div>
    </div>
</body>

</html>
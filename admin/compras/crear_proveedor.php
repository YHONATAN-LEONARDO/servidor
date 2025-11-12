<?php
include '../../app/config/session.php';
include '../../app/config/database.php';

$errores = [];
$ok = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $correo    = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if ($nombre === '') $errores[] = 'El nombre del proveedor es obligatorio.';
    if ($correo && !filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = 'Correo inválido.';

    if (!$errores) {
        $sql = "INSERT INTO proveedores (nombre, telefono, correo, direccion, creado_en)
                VALUES (?, ?, ?, ?, SYSDATETIME())";
        $stmt = sqlsrv_query($conn, $sql, [$nombre, $telefono, $correo, $direccion]);
        if ($stmt) {
            $ok = 'Proveedor registrado correctamente.';
        } else {
            $errores[] = 'Error al guardar: ' . print_r(sqlsrv_errors(), true);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Proveedor</title>

</head>
<style>
    /* ----------------- BODY ----------------- */
    body {
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #F0F0F0;
        /* Fondo humo */
        color: #111;
        /* Texto principal */
    }

    /* ----------------- TITULO ----------------- */
    h1 {
        text-align: center;
        color: #E7473C;
        /* Rojo brillante */
        margin: 20px 0;
    }

    /* ----------------- ALERTAS ----------------- */
    .alert,
    .ok {
        max-width: 800px;
        margin: 10px auto;
        padding: 10px 15px;
        border-radius: 5px;
        font-weight: bold;
        text-align: center;
    }

    .alert {
        background-color: #FFE6E4;
        /* Rojo muy claro */
        border-left: 4px solid #E7473C;
        color: #111;
    }

    .ok {
        background-color: #E0FFE0;
        /* Verde claro para éxito */
        border-left: 4px solid #4CAF50;
        color: #111;
    }

    /* ----------------- FORMULARIO ----------------- */
    form {
        
        max-width: 600px;
        margin: 0 auto 30px auto;
        background-color: #FFF;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        margin-top: 140px;

    }

    form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    form input,
    form textarea,
    form select {
        width: 100%;
        padding: 8px 10px;
        margin-bottom: 15px;
        border: 1px solid #CCC;
        border-radius: 5px;
        font-size: 14px;
        box-sizing: border-box;
    }

    form textarea {
        resize: vertical;
    }

    /* ----------------- BOTONES ----------------- */
    button,
    .volver {
        display: inline-block;
        padding: 8px 15px;
        border-radius: 5px;
        font-weight: bold;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    button {
        background-color: #E7473C;
        color: #FFF;
    }

    button:hover {
        background-color: #FFE6E4;
        color: #111;
    }

    .volver {
        background-color: #111;
        color: #FFF;
    }

    .volver:hover {
        background-color: #EEE;
        color: #111;
    }
</style>

<body>
    <?php include '../sidebar.php'; ?>

    <h1>Crear Proveedor</h1>

    <?php if ($ok): ?><div class="ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <?php if ($errores): ?><div class="alert">
            <ul><?php foreach ($errores as $e) echo "<li>$e</li>"; ?></ul>
        </div><?php endif; ?>

    <form method="POST">
        <label>Nombre del Proveedor:</label>
        <input type="text" name="nombre" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono">

        <label>Correo:</label>
        <input type="email" name="correo">

        <label>Dirección:</label>
        <textarea name="direccion" rows="3"></textarea>

        <button type="submit">Guardar Proveedor</button>
    </form>
</body>

</html>
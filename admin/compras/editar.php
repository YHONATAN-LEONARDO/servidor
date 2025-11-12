<?php
include '../../app/config/session.php';
include '../../app/config/database.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Error: No se proporcionó un ID de proveedor válido.");
}

$errores = [];
$ok = null;

// Consultar proveedor actual
$sqlProveedor = "SELECT * FROM proveedores WHERE id = ?";
$stmtProveedor = sqlsrv_query($conn, $sqlProveedor, [$id]);
$proveedor = sqlsrv_fetch_array($stmtProveedor, SQLSRV_FETCH_ASSOC);

if (!$proveedor) {
    die("Error: Proveedor no encontrado.");
}

$nombre    = $proveedor['nombre'];
$telefono  = $proveedor['telefono'];
$correo    = $proveedor['correo'];
$direccion = $proveedor['direccion'];

// Si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $correo    = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if ($nombre === '') $errores[] = 'El nombre del proveedor es obligatorio.';
    if ($correo && !filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = 'Correo inválido.';

    if (!$errores) {
        $sql = "UPDATE proveedores 
                SET nombre = ?, telefono = ?, correo = ?, direccion = ?, actualizado_en = SYSDATETIME()
                WHERE id = ?";
        $params = [$nombre, $telefono, $correo, $direccion, $id];
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            $ok = 'Proveedor actualizado correctamente.';
            // Refrescar datos del proveedor
            $stmtProveedor = sqlsrv_query($conn, $sqlProveedor, [$id]);
            $proveedor = sqlsrv_fetch_array($stmtProveedor, SQLSRV_FETCH_ASSOC);
        } else {
            $errores[] = 'Error al actualizar: ' . print_r(sqlsrv_errors(), true);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Proveedor</title>
    <style>
        /* Body general */
        body {
            font-family: Arial, sans-serif;
            background: #f0f4ff;
            /* Fondo de body */
            color: #0a2e6f;
            /* Texto general */
            margin: 40px;
        }

        /* Título */
        h1 {
            text-align: center;
            color: #052c7a;
            /* Títulos */
        }

        /* Formulario */
        form {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            max-width: 600padmin/compras/ingresar_factura.phpx;
            margin: auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #c7d2fe;
            /* Bordes */
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: 600;
            color: #052c7a;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            border: 1px solid #a5b4fc;
            /* Bordes de inputs */
            border-radius: 6px;
            background: #e0e7ff;
            /* Fondo inputs */
            color: #0a2e6f;
            font-size: 14px;
        }

        /* Botones */
        button,
        .btn-volver {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-top: 15px;
        }

        button[type="submit"] {
            background: #1f4ed8;
            /* Botón guardar / nuevo */
        }

        button[type="submit"]:hover {
            background: #143cb0;
        }

        .btn-volver {
            background: #4a6edb;
            /* Botón volver */
            margin-left: 10px;
            padding: 10px 15px;
        }

        .btn-volver:hover {
            background: #3756a0;
        }

        /* Mensajes */
        .alert {
            background: #fee2e2;
            /* Error */
            border: 1px solid #fca5a5;
            color: #7f1d1d;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .ok {
            background: #ecfdf5;
            /* Éxito */
            border: 1px solid #34d399;
            color: #065f46;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <h1>Editar Proveedor</h1>

    <?php if ($ok): ?><div class="ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <?php if ($errores): ?><div class="alert">
            <ul><?php foreach ($errores as $e) echo "<li>$e</li>"; ?></ul>
        </div><?php endif; ?>
    <a class="btn-volver" href="l-proveedor.php">Volver</a>

    <form method="POST">
        <label>Nombre del Proveedor:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($telefono) ?>">

        <label>Correo:</label>
        <input type="email" name="correo" value="<?= htmlspecialchars($correo) ?>">

        <label>Dirección:</label>
        <textarea name="direccion" rows="3"><?= htmlspecialchars($direccion) ?></textarea>

        <button type="submit">Actualizar Proveedor</button>
    </form>
</body>

</html>
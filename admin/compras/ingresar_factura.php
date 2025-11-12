<?php
include '../../app/config/session.php';
include '../../app/config/database.php';

$errores = [];
$ok = null;

// Cargar proveedores
$proveedores = [];
$pq = sqlsrv_query($conn, "SELECT id, nombre FROM proveedores ORDER BY nombre");
while ($r = sqlsrv_fetch_array($pq, SQLSRV_FETCH_ASSOC)) $proveedores[] = $r;

// Cargar productos
$productos = [];
$pq2 = sqlsrv_query($conn, "SELECT id, titulo FROM productos ORDER BY titulo");
while ($r2 = sqlsrv_fetch_array($pq2, SQLSRV_FETCH_ASSOC)) $productos[] = $r2;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proveedor = trim($_POST['proveedor'] ?? '');
    $numero_factura = trim($_POST['numero_factura'] ?? '');
    $fecha_compra = trim($_POST['fecha_compra'] ?? date('Y-m-d'));
    $producto_id = (int)($_POST['producto_id'] ?? 0);
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $precio = (float)($_POST['precio'] ?? 0);
    $obs = trim($_POST['observacion'] ?? '');

    if ($proveedor === '') $errores[] = 'Proveedor es obligatorio.';
    if ($numero_factura === '') $errores[] = 'Número de factura es obligatorio.';
    if ($producto_id <= 0) $errores[] = 'Debe seleccionar un producto.';
    if ($cantidad <= 0) $errores[] = 'Cantidad inválida.';
    if ($precio <= 0) $errores[] = 'Precio inválido.';

    if (!$errores) {
        $total = $cantidad * $precio;
        sqlsrv_begin_transaction($conn);

        $compra_sql = "INSERT INTO compras (proveedor, numero_factura, fecha_compra, total, observacion)
                       OUTPUT INSERTED.id VALUES (?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($conn, $compra_sql, [$proveedor, $numero_factura, $fecha_compra, $total, $obs]);
        if ($stmt && ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
            $compra_id = $row['id'];

            $detalle_sql = "INSERT INTO compras_detalle (compra_id, producto_id, cantidad, precio_compra)
                            VALUES (?, ?, ?, ?)";
            sqlsrv_query($conn, $detalle_sql, [$compra_id, $producto_id, $cantidad, $precio]);

            // Actualiza inventario
            $update = "UPDATE productos SET cantidad = cantidad + ? WHERE id = ?";
            sqlsrv_query($conn, $update, [$cantidad, $producto_id]);

            sqlsrv_commit($conn);
            $ok = 'Factura registrada correctamente.';
        } else {
            sqlsrv_rollback($conn);
            $errores[] = 'Error al guardar la factura: ' . print_r(sqlsrv_errors(), true);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ingresar Factura de Proveedor</title>

</head>
<style>
    /* ----------------- RESET Y BODY ----------------- */
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
    .ok {
        max-width: 600px;
        margin: 10px auto;
        padding: 10px 15px;
        border-radius: 5px;
        font-weight: bold;
        text-align: center;
        background-color: #FFE6E4;
        /* Rojo muy claro */
        border-left: 4px solid #E7473C;
        color: #111;
    }

    .alert {
        max-width: 600px;
        margin: 10px auto;
        padding: 10px 15px;
        border-radius: 5px;
        background-color: #FFE6E4;
        /* Rojo muy claro */
        border-left: 4px solid #E7473C;
        color: #111;
    }

    /* ----------------- BOTON VOLVER ----------------- */
    .btn-volver {
        display: inline-block;
        margin: 10px 0 20px 20px;
        padding: 8px 12px;
        border-radius: 5px;
        background-color: #E7473C;
        color: #FFF;
        text-decoration: none;
        font-weight: bold;
        transition: all 0.2s;
    }

    .btn-volver:hover {
        background-color: #FFE6E4;
        color: #111;
    }

    /* ----------------- FORMULARIO ----------------- */
    form {

        max-width: 600px;
        margin: 0 auto 30px auto;
        /* Centrado con margen */
        padding: 20px;
        background-color: #FFF;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        margin-top: 135px;

    }

    /* Labels */
    form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    /* Inputs y Select */
    form input[type="text"],
    form input[type="email"],
    form input[type="number"],
    form input[type="date"],
    form select,
    form textarea {
        width: calc(100% - 20px);
        padding: 8px 10px;
        margin-bottom: 15px;
        border: 1px solid #CCC;
        border-radius: 5px;
        font-size: 14px;
    }

    /* Textarea */
    form textarea {
        resize: vertical;
    }

    /* Boton submit */
    form button {
        background-color: #E7473C;
        color: #FFF;
        font-weight: bold;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.2s;
    }

    form button:hover {
        background-color: #FFE6E4;
        color: #111;
    }
</style>

<body>
    <?php include '../sidebar.php'; ?>

    <h1>Ingresar Factura de Proveedor</h1>

    <?php if ($ok): ?><div class="ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <?php if ($errores): ?><div class="alert">
            <ul><?php foreach ($errores as $e) echo "<li>$e</li>"; ?></ul>
        </div><?php endif; ?>

    <form method="POST">
        <label>Proveedor:</label>
        <select name="proveedor" required>
            <option value="">Seleccione...</option>
            <?php foreach ($proveedores as $prov): ?>
                <option value="<?= htmlspecialchars($prov['nombre']) ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Número de Factura:</label>
        <input type="text" name="numero_factura" required>

        <label>Fecha de Compra:</label>
        <input type="date" name="fecha_compra" value="<?= date('Y-m-d') ?>">

        <label>Producto:</label>
        <select name="producto_id" required>
            <option value="">Seleccione...</option>
            <?php foreach ($productos as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['titulo']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Cantidad:</label>
        <input type="number" name="cantidad" required>

        <label>Precio Unitario (Bs):</label>
        <input type="number" step="0.01" name="precio" required>

        <label>Observación:</label>
        <textarea name="observacion" rows="3"></textarea>

        <button type="submit">Guardar Factura</button>
    </form>
</body>

</html>
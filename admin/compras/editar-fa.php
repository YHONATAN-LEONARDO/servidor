<?php
include '../../app/config/session.php';
include '../../app/config/database.php';

$errores = [];
$ok = null;

// Obtener ID de la compra a editar
$compra_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($compra_id <= 0) {
    header("Location: lista.php");
    exit;
}

// Cargar proveedores
$proveedores = [];
$pq = sqlsrv_query($conn, "SELECT id, nombre FROM proveedores ORDER BY nombre");
while ($r = sqlsrv_fetch_array($pq, SQLSRV_FETCH_ASSOC)) $proveedores[] = $r;

// Cargar productos
$productos = [];
$pq2 = sqlsrv_query($conn, "SELECT id, titulo FROM productos ORDER BY titulo");
while ($r2 = sqlsrv_fetch_array($pq2, SQLSRV_FETCH_ASSOC)) $productos[] = $r2;

// Cargar datos actuales de la compra
$sqlCompra = "
SELECT c.*, cd.producto_id, cd.cantidad, cd.precio_compra
FROM compras c
LEFT JOIN compras_detalle cd ON cd.compra_id = c.id
WHERE c.id = ?";
$stmt = sqlsrv_query($conn, $sqlCompra, [$compra_id]);
$compra = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$compra) {
    die("Compra no encontrada.");
}

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

        // Actualizar tabla compras
        $updateCompra = "UPDATE compras SET proveedor = ?, numero_factura = ?, fecha_compra = ?, total = ?, observacion = ? WHERE id = ?";
        $upd = sqlsrv_query($conn, $updateCompra, [$proveedor, $numero_factura, $fecha_compra, $total, $obs, $compra_id]);

        // Actualizar detalle compra (solo 1 producto, igual que ingreso)
        $updateDetalle = "UPDATE compras_detalle SET producto_id = ?, cantidad = ?, precio_compra = ? WHERE compra_id = ?";
        $updDet = sqlsrv_query($conn, $updateDetalle, [$producto_id, $cantidad, $precio, $compra_id]);

        if ($upd && $updDet) {
            sqlsrv_commit($conn);
            $ok = "Factura actualizada correctamente.";
            // Recargar datos para mostrar en el formulario
            $compra['proveedor'] = $proveedor;
            $compra['numero_factura'] = $numero_factura;
            $compra['fecha_compra'] = new DateTime($fecha_compra);
            $compra['producto_id'] = $producto_id;
            $compra['cantidad'] = $cantidad;
            $compra['precio_compra'] = $precio;
            $compra['observacion'] = $obs;
        } else {
            sqlsrv_rollback($conn);
            $errores[] = "Error al actualizar la factura: " . print_r(sqlsrv_errors(), true);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Factura de Proveedor</title>

</head>
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

    /* ===== LAYOUT GENERAL ===== */
    body {
        margin: 0;
        margin-top: 150px !important;
        font-family: system-ui, sans-serif;
        background-color: var(--humo-blanco);
        color: var(--negro);
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* ===== TÍTULO ===== */
    h1 {
        color: var(--rojo-brillante);
        margin-bottom: 1.5rem;
    }

    /* ===== MENSAJES ===== */
    .ok,
    .alert {
        width: 100%;
        max-width: 600px;
        padding: 0.8rem 1rem;
        margin-bottom: 1rem;
        border-radius: 10px;
        background-color: var(--blanco-puro);
        box-shadow: 0 4px 10px var(--sombra-negra-ligera);
        border-left: 4px solid var(--rojo-brillante);
        color: var(--negro);
    }

    .alert ul {
        margin: 0;
        padding-left: 1.2rem;
    }

    /* ===== BOTÓN VOLVER ===== */
    .btn-volver {
        display: inline-block;
        margin-bottom: 1rem;
        padding: 0.45rem 0.9rem;
        border-radius: 999px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        background-color: var(--blanco-puro);
        color: var(--rojo-brillante);
        border: 1px solid var(--rojo-brillante);
        box-shadow: 0 4px 10px var(--sombra-negra-ligera);
        transition: 0.2s;
    }

    .btn-volver:hover {
        background-color: var(--rojo-brillante);
        color: var(--blanco-puro);
    }

    /* ===== FORMULARIO ===== */
    form {
        width: 100%;
        max-width: 600px;
        padding: 1.5rem 1.8rem;
        background-color: var(--blanco-puro);
        border-radius: 12px;
        box-shadow: 0 8px 20px var(--sombra-negra-ligera);
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }

    form label {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--negro);
    }

    /* Inputs, selects, textarea */
    form input[type="text"],
    form input[type="date"],
    form input[type="number"],
    form select,
    form textarea {
        width: 100%;
        padding: 0.5rem 0.6rem;
        border-radius: 8px;
        border: 1px solid var(--gris-claro);
        background-color: var(--humo-blanco);
        color: var(--negro);
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease,
            background-color 0.15s ease;
    }

    form textarea {
        resize: vertical;
        min-height: 80px;
    }

    form input:focus,
    form select:focus,
    form textarea:focus {
        border-color: var(--rojo-brillante);
        background-color: var(--blanco-puro);
        box-shadow: 0 0 0 3px rgba(231, 71, 60, 0.25);
    }

    /* ===== BOTÓN PRINCIPAL ===== */
    form button[type="submit"] {
        margin-top: 0.5rem;
        align-self: flex-end;
        padding: 0.6rem 1.4rem;
        border-radius: 999px;
        border: none;
        background-color: var(--rojo-brillante);
        color: var(--blanco-puro);
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        box-shadow: 0 6px 14px rgba(231, 71, 60, 0.3);
        transition: background-color 0.2s ease, color 0.2s ease,
            box-shadow 0.2s ease, transform 0.1s ease;
    }

    form button[type="submit"]:hover {
        background-color: var(--blanco-puro);
        color: var(--rojo-brillante);
        border: 1px solid var(--rojo-brillante);
    }

    form button[type="submit"]:active {
        transform: translateY(1px);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        body {
            padding: 0 1rem;
        }

        form {
            padding: 1.2rem 1.2rem;
        }
    }
</style>

<body>
    <?php include '../sidebar.php'; ?>

    <h1>Editar Factura de Proveedor</h1>

    <?php if ($ok): ?><div class="ok"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <?php if ($errores): ?><div class="alert">
            <ul><?php foreach ($errores as $e) echo "<li>$e</li>"; ?></ul>
        </div><?php endif; ?>

    <form method="POST">
        <label>Proveedor:</label>
        <select name="proveedor" required>
            <option value="">Seleccione...</option>
            <?php foreach ($proveedores as $prov): ?>
                <option value="<?= htmlspecialchars($prov['nombre']) ?>" <?= ($compra['proveedor'] == $prov['nombre']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($prov['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Número de Factura:</label>
        <input type="text" name="numero_factura" value="<?= htmlspecialchars($compra['numero_factura']) ?>" required>

        <label>Fecha de Compra:</label>
        <input type="date" name="fecha_compra" value="<?= $compra['fecha_compra'] ? $compra['fecha_compra']->format('Y-m-d') : date('Y-m-d') ?>">

        <label>Producto:</label>
        <select name="producto_id" required>
            <option value="">Seleccione...</option>
            <?php foreach ($productos as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($compra['producto_id'] == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['titulo']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Cantidad:</label>
        <input type="number" name="cantidad" value="<?= (int)$compra['cantidad'] ?>" required>

        <label>Precio Unitario (Bs):</label>
        <input type="number" step="0.01" name="precio" value="<?= (float)$compra['precio_compra'] ?>" required>

        <label>Observación:</label>
        <textarea name="observacion" rows="3"><?= htmlspecialchars($compra['observacion']) ?></textarea>

        <button type="submit">Actualizar Factura</button>
    </form>
</body>

</html>
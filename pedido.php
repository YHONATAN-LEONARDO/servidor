<?php
require_once __DIR__ . '/app/config/session.php';
require_once __DIR__ . '/app/config/database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ---------- Autenticación ---------- */
if (!isset($_SESSION['login']) || !$_SESSION['login']) {
    header('Location: /views/usuarios/login.php');
    exit;
}

/* ---------- Carrito ---------- */
$cart = $_SESSION['cart'] ?? [];
if (!$cart || count($cart) === 0) {
    header('Location: carrito.php?empty=1');
    exit;
}

/* ---------- Cliente ---------- */
$usuario_nombre = $_SESSION['nombre'] ?? "Cliente";
$usuario_correo = $_SESSION['correo'] ?? "";

/* Buscar cliente_id */
$stmtC = sqlsrv_query($conn, "SELECT id FROM clientes WHERE correo = ?", [$usuario_correo]);
$cliente_id = ($stmtC && ($r = sqlsrv_fetch_array($stmtC, SQLSRV_FETCH_ASSOC))) ? (int)$r['id'] : 1;

/* ---------- Calcular total y pago mínimo ---------- */
function precio_final($i)
{
    $p = (float)($i['precio'] ?? 0);
    $d = (float)($i['descuento'] ?? 0);
    if ($d > 0) $p -= $p * ($d / 100);
    return max($p, 0);
}

$total = 0;
foreach ($cart as $p) $total += precio_final($p) * $p['cantidad'];
$pago_minimo = $total * 0.50;
$restante = $total - $pago_minimo;

$error = "";

/* ---------- Métodos de pago ---------- */
$metodos = [];
$q = sqlsrv_query($conn, "SELECT id, nombre FROM metodos_pago ORDER BY id");
while ($q && ($m = sqlsrv_fetch_array($q, SQLSRV_FETCH_ASSOC))) $metodos[] = $m;

/* ---------- Valores POST ---------- */
$metodo_pago_post      = $_POST['metodo_pago'] ?? '';
$referencia_post       = trim($_POST['referencia'] ?? '');
$nit_post              = trim($_POST['nit'] ?? '');
$razon_post            = trim($_POST['razon_social'] ?? '');
$obs_post              = trim($_POST['observacion'] ?? '');
$lugar_post            = trim($_POST['lugar_entrega'] ?? '');
$requiere_factura_post = isset($_POST['requiere_factura']) && $_POST['requiere_factura'] === '1';

$exito = "";

/* ---------- POST: Procesar pedido parcial ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $metodo_pago = (int)($metodo_pago_post ?: 0);

        // Validar método de pago no permitido (efectivo)
        $mp_row = null;
        foreach ($metodos as $m) {
            if ($m['id'] == $metodo_pago) $mp_row = $m;
        }
        if (!$mp_row) throw new Exception("Debe seleccionar un método de pago válido.");
        if (strtolower(trim($mp_row['nombre'])) === 'efectivo') throw new Exception("El método de pago 'efectivo' no está permitido.");

        $mp_nombre = $mp_row['nombre'];
        $mp_lc = strtolower($mp_nombre);

        $requiere_referencia = str_contains($mp_lc, 'transferencia') || str_contains($mp_lc, 'tarjeta') || str_contains($mp_lc, 'movil');

        // Validaciones
        $errores = [];
        if ($requiere_referencia && $referencia_post === '') $errores[] = "Debes ingresar la referencia para '{$mp_nombre}'.";
        if ($requiere_factura_post && ($nit_post === '' || $razon_post === '')) $errores[] = "Para facturar, NIT y Razón Social son obligatorios.";
        if (!empty($errores)) throw new Exception(implode(' ', $errores));

        if (!sqlsrv_begin_transaction($conn)) throw new Exception("No se pudo iniciar la transacción.");

        // Insert venta
        $sqlVenta = "INSERT INTO ventas (fecha, total, estado, cliente_id, estado_entrega)
                     OUTPUT INSERTED.id VALUES (SYSDATETIME(), ?, 'pendiente', ?, 'pendiente')";
        $stmtV = sqlsrv_query($conn, $sqlVenta, [$total, $cliente_id]);
        if (!$stmtV) throw new Exception("Error al crear la venta.");
        $venta_row = sqlsrv_fetch_array($stmtV, SQLSRV_FETCH_ASSOC);
        $venta_id = (int)$venta_row['id'];

        // Insert detalle
        foreach ($cart as $p) {
            $precioF = precio_final($p);
            $sqlDet = "INSERT INTO ventas_detalle (venta_id, producto_id, cantidad, precio_unitario, descuento)
                       VALUES (?,?,?,?,?)";
            sqlsrv_query($conn, $sqlDet, [$venta_id, $p['id'], $p['cantidad'], $precioF, $p['descuento']]);
        }

        // Insert pago parcial 50%
        $sqlPago = "INSERT INTO transacciones_pago (venta_id, metodo_pago_id, cuenta_id, referencia, monto, estado, fecha_pago)
                    VALUES (?, ?, NULL, ?, ?, 'parcial', SYSDATETIME())";
        sqlsrv_query($conn, $sqlPago, [$venta_id, $metodo_pago, $referencia_post, $pago_minimo]);

        // Insert factura si aplica
        if ($requiere_factura_post) {
            $num_factura = 'F-' . str_pad($venta_id, 6, '0', STR_PAD_LEFT);
            $sqlFactura = "INSERT INTO facturas (venta_id, numero, nit_cliente, razon_social, total, observacion, lugar_entrega, nombre_cliente)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            sqlsrv_query($conn, $sqlFactura, [$venta_id, $num_factura, $nit_post, $razon_post, $total, $obs_post, $lugar_post, $usuario_nombre]);
        }

        sqlsrv_commit($conn);
        $_SESSION['cart'] = [];
        $exito = "Pedido registrado. Pago inicial 50%: " . number_format($pago_minimo, 2) . " Bs. Pendiente: " . number_format($restante, 2) . " Bs.";
    } catch (Exception $e) {
        @sqlsrv_rollback($conn);
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido</title>
    <link rel="stylesheet" href="/public/css/estilos.css">
    <link rel="stylesheet" href="/public/css/normalize.css">
</head>

<body>
    <?php include __DIR__ . "/views/layouts/header.php"; ?>

    <div class="containerl ll" style="padding:5rem;">
        <h1>Confirmar Pedido</h1>

        <?php if ($exito): ?>
            <div class="nota" style="background:#d4ffe1;padding:14px;border-radius:8px;border:1px solid #8cd898;"><?= $exito ?></div>
            <a href="/" class="btn btn-primary">Volver al inicio</a>
        <?php exit;
        endif; ?>

        <?php if (!empty($error)): ?>
            <div class="nota" style="background:#ffd6d6;padding:12px;border:1px solid #ff9b9b;border-radius:8px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <p><strong>Total del pedido:</strong> <?= number_format($total, 2) ?> Bs</p>
        <p><strong>Pago inicial obligatorio (50%):</strong> <?= number_format($pago_minimo, 2) ?> Bs</p>
        <p><strong>Monto restante:</strong> <?= number_format($restante, 2) ?> Bs</p>

        <form method="POST" id="form-pago">
            <label>Método de pago</label>
            <select id="metodo_pago" name="metodo_pago" required>
                <option value="">Seleccione...</option>
                <?php foreach ($metodos as $m):
                    $nombre = strtolower(trim($m['nombre']));
                    if ($nombre === "efectivo" || $nombre === "pago móvil") continue;
                ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                <?php endforeach; ?>
            </select>


            <div class="form-group" id="grupo-referencia">
                <label for="referencia">Referencia de pago</label>
                <input type="text" name="referencia" id="referencia" placeholder="Ej: N° de operación" value="<?= htmlspecialchars($referencia_post) ?>">
                <small id="hint-referencia">Obligatoria para Transferencia, Tarjeta y Pago móvil.</small>
            </div>

            <div class="form-group">
                <label><input type="checkbox" name="requiere_factura" id="requiere_factura" value="1" <?= $requiere_factura_post ? 'checked' : '' ?>> ¿Requieres factura?</label>
            </div>

            <div class="form-group hidden" id="bloque-factura">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label for="nit">NIT</label>
                        <input type="text" name="nit" id="nit" value="<?= htmlspecialchars($nit_post) ?>">
                    </div>
                    <div>
                        <label for="razon_social">Razón Social</label>
                        <input type="text" name="razon_social" id="razon_social" value="<?= htmlspecialchars($razon_post) ?>">
                    </div>
                </div>
            </div>

            <label>Lugar de entrega</label>
            <input type="text" name="lugar_entrega" value="<?= htmlspecialchars($lugar_post) ?>">

            <label>Observaciones</label>
            <textarea name="observacion"><?= htmlspecialchars($obs_post) ?></textarea>

            <button type="submit" class="btn btn-primary">Confirmar Pedido</button>
        </form>
    </div>

    <?php include __DIR__ . "/views/layouts/footer.php"; ?>

    <script>
        (function() {
            const metodo = document.getElementById('metodo_pago');
            const grupoRef = document.getElementById('grupo-referencia');
            const ref = document.getElementById('referencia');
            const hintRef = document.getElementById('hint-referencia');

            const reqFac = document.getElementById('requiere_factura');
            const bloqueFac = document.getElementById('bloque-factura');
            const nit = document.getElementById('nit');
            const razon = document.getElementById('razon_social');

            function norm(t) {
                return (t || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }

            function actualizarReferencia() {
                const opt = metodo.options[metodo.selectedIndex];
                const nombre = norm(opt ? opt.textContent : '');
                let requiere = false;
                let placeholder = 'Ej: N° de operación';
                let hint = 'Obligatoria para Transferencia, Tarjeta y Pago móvil.';
                if (nombre.includes('transferencia')) requiere = true;
                else if (nombre.includes('tarjeta')) requiere = true;
                else if (nombre.includes('movil')) requiere = true;

                ref.placeholder = placeholder;
                hintRef.textContent = hint;
                ref.required = requiere;
                grupoRef.classList.toggle('hidden', !requiere);
            }

            function actualizarFactura() {
                const activo = reqFac.checked;
                bloqueFac.classList.toggle('hidden', !activo);
                nit.required = activo;
                razon.required = activo;
            }

            actualizarReferencia();
            actualizarFactura();

            metodo.addEventListener('change', actualizarReferencia);
            reqFac.addEventListener('change', actualizarFactura);
        })();
    </script>

</body>

</html>

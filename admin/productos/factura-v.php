<?php
require __DIR__ . '/../../vendor/autoload.php';
include '../../app/config/database.php';
include '../../app/config/session.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// --- Recibir datos del formulario ---
$id_producto     = (int)($_POST['id_producto'] ?? 0);
$cantidad        = (int)($_POST['cantidad'] ?? 1);
$cliente_nombre  = $_POST['cliente_nombre'] ?? '________________';
$cliente_tarjeta = $_POST['cliente_tarjeta'] ?? '________________';
$observaciones   = $_POST['observaciones'] ?? '';
$fecha_venta     = date('d/m/Y H:i');

if ($id_producto <= 0) die("Producto inválido.");

// Obtener producto
$sql = "
SELECT 
    p.id, p.titulo, p.precio, p.cantidad, p.categoria, p.talla, p.genero, p.color, p.imagen,
    v.nombre AS nombre_vendedor, v.apellido AS apellido_vendedor
FROM productos p
INNER JOIN vendedor v ON p.vendedor = v.id
WHERE p.id = ?
";
$stmt = sqlsrv_query($conn, $sql, [$id_producto]);
if (!$stmt) die("Error al consultar producto.");
$producto = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$producto) die("Producto no encontrado.");

// Calcular total
$total = (float)$producto['precio'] * $cantidad;

// Logo
$logoUrl = "http://3.134.103.143/public/img/logo.png";

// HTML de la factura
$html = "
<!DOCTYPE html>
<html lang='es'>
<head>
<meta charset='UTF-8'>
<style>
body { font-family:'DejaVu Sans', sans-serif; margin:40px; color:#000; }
header { display:flex; align-items:center; justify-content:flex-start; margin-bottom:20px; }
header img { height:100px; margin-right:20px; }
h1 { font-size:24px; text-align:center; margin-bottom:5px; }
h2 { font-size:16px; text-align:center; margin-top:0; margin-bottom:30px; }
table { width:100%; border-collapse:collapse; margin-bottom:20px; font-size:14px; }
th, td { border:1px solid #000; padding:8px; text-align:center; }
th { background:#4CAF50; color:white; }
.footer { text-align:right; font-size:11px; margin-top:20px; color:#666; }
</style>
</head>
<body>

<header>
<img src='$logoUrl' alt='Logo EcoAbrigo'>
<div>
    <strong>Factura de venta</strong><br>
    Fecha: $fecha_venta
</div>
</header>

<h1>Factura Ejecutiva</h1>
<h2>EcoAbrigo - Panel Administrativo</h2>

<table>
<tr><th>Cliente</th><td>$cliente_nombre</td></tr>
<tr><th>Método de Pago</th><td>$cliente_tarjeta</td></tr>
<tr><th>Observaciones</th><td>$observaciones</td></tr>
</table>

<h3>Detalle del producto</h3>
<table>
<tr>
<th>Producto</th><th>Categoría</th><th>Talla</th><th>Género</th><th>Precio Unitario</th><th>Cantidad</th><th>Total</th>
</tr>
<tr>
<td>{$producto['titulo']}</td>
<td>{$producto['categoria']}</td>
<td>{$producto['talla']}</td>
<td>{$producto['genero']}</td>
<td>Bs " . number_format($producto['precio'], 2) . "</td>
<td>$cantidad</td>
<td>Bs " . number_format($total, 2) . "</td>
</tr>
</table>

<footer class='footer'>
Generado automáticamente el " . date('d/m/Y H:i') . "
</footer>
</body>
</html>
";

// Generar PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("factura_producto_{$id_producto}.pdf", ["Attachment" => true]);

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../app/config/database.php';
include '../../app/config/session.php';
require '../../vendor/autoload.php'; // Dompdf

use Dompdf\Dompdf;

if (!isset($_POST['venta_id'])) {
    die("No se ha proporcionado la venta.");
}

$venta_id = (int)$_POST['venta_id'];

// Traer factura y detalle
$sqlFac = "SELECT f.numero, f.fecha_emision, f.total, f.nombre_cliente,
                  p.titulo, p.precio
           FROM facturas f
           JOIN ventas_detalle vd ON vd.venta_id = f.venta_id
           JOIN productos p ON vd.producto_id = p.id
           WHERE f.venta_id = ?";
$stmtFac = sqlsrv_query($conn, $sqlFac, [$venta_id]);
$factura = sqlsrv_fetch_array($stmtFac, SQLSRV_FETCH_ASSOC);

if (!$factura) {
    die("Factura no encontrada.");
}

// Preparar HTML para PDF
$fecha = $factura['fecha_emision'];
if (!($fecha instanceof DateTime)) $fecha = new DateTime($fecha);

$html = '
<h1>Factura #' . htmlspecialchars($factura['numero']) . '</h1>
<p><strong>Cliente:</strong> ' . htmlspecialchars($factura['nombre_cliente']) . '</p>
<p><strong>Fecha:</strong> ' . $fecha->format('Y-m-d H:i') . '</p>
<table border="1" cellpadding="5" cellspacing="0">
<tr>
<th>Producto</th>
<th>Precio Unitario</th>
<th>Total</th>
</tr>
<tr>
<td>' . htmlspecialchars($factura['titulo']) . '</td>
<td>' . number_format((float)$factura['precio'],2) . '</td>
<td>' . number_format((float)$factura['total'],2) . '</td>
</tr>
<tr>
<td colspan="2" align="right"><strong>Total</strong></td>
<td>' . number_format((float)$factura['total'],2) . '</td>
</tr>
</table>
';

// Generar PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream('Factura_'.$factura['numero'].'.pdf',['Attachment'=>true]);
exit;

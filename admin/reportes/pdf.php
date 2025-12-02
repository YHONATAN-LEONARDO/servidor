<?php
require __DIR__ . '/../../vendor/autoload.php';
include __DIR__ . '/../../app/config/database.php';
session_start();

use Dompdf\Dompdf;
use Dompdf\Options;

// Debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Parámetros
$tabla   = $_GET['tabla'] ?? 'ventas';
$tipo    = $_GET['tipo'] ?? 'hoy';
$desde   = $_GET['desde'] ?? '';
$hasta   = $_GET['hasta'] ?? '';
$usuario = $_SESSION['usuario_nombre'] ?? 'Desconocido';

// SQL con LEFT JOIN para traer productos y detalles
switch ($tipo) {
  case 'hoy':
    $sql = "SELECT v.id, v.fecha, v.total, v.estado, vd.producto_id, p.titulo AS producto,
                vd.cantidad, vd.precio_unitario, vd.descuento
                FROM ventas v
                LEFT JOIN ventas_detalle vd ON v.id = vd.venta_id
                LEFT JOIN productos p ON vd.producto_id = p.id
                WHERE CONVERT(date,v.fecha)=CONVERT(date,GETDATE())";
    $titulo = "Ventas de hoy";
    break;

  case 'ayer':
    $sql = "SELECT v.id, v.fecha, v.total, v.estado, vd.producto_id, p.titulo AS producto,
                vd.cantidad, vd.precio_unitario, vd.descuento
                FROM ventas v
                LEFT JOIN ventas_detalle vd ON v.id = vd.venta_id
                LEFT JOIN productos p ON vd.producto_id = p.id
                WHERE CONVERT(date,v.fecha)=CONVERT(date,DATEADD(day,-1,GETDATE()))";
    $titulo = "Ventas de ayer";
    break;

  case 'mes':
    $sql = "SELECT v.id, v.fecha, v.total, v.estado, vd.producto_id, p.titulo AS producto,
                vd.cantidad, vd.precio_unitario, vd.descuento
                FROM ventas v
                LEFT JOIN ventas_detalle vd ON v.id = vd.venta_id
                LEFT JOIN productos p ON vd.producto_id = p.id
                WHERE MONTH(v.fecha)=MONTH(GETDATE()) AND YEAR(v.fecha)=YEAR(GETDATE())";
    $titulo = "Ventas de este mes";
    break;

  case 'rango':
    if (!$desde || !$hasta) die("Debe seleccionar ambas fechas.");
    $sql = "SELECT v.id, v.fecha, v.total, v.estado, vd.producto_id, p.titulo AS producto,
                vd.cantidad, vd.precio_unitario, vd.descuento
                FROM ventas v
                LEFT JOIN ventas_detalle vd ON v.id = vd.venta_id
                LEFT JOIN productos p ON vd.producto_id = p.id
                WHERE v.fecha BETWEEN ? AND ?";
    $params = [$desde . ' 00:00:00', $hasta . ' 23:59:59'];
    $titulo = "Ventas desde $desde hasta $hasta";
    break;

  default:
    die("Parámetro inválido.");
}

// Ejecutar consulta
if ($tipo === 'rango') {
  $stmt = sqlsrv_query($conn, $sql, $params);
} else {
  $stmt = sqlsrv_query($conn, $sql);
}
if (!$stmt) die("Error al consultar la base de datos.");

// Logo dinámico
// Logo desde URL pública
$logoUrl = "http://3.134.103.143/public/img/logo.png";


// HTML para PDF
$html = "
<!DOCTYPE html>
<html lang='es'>
<head>
<meta charset='UTF-8'>
<style>
body { font-family: 'DejaVu Sans', sans-serif; margin:40px; color:#000; }
header { display:flex; align-items:center; justify-content:space-between; margin-bottom:30px; }
header img { height:70px; }
header div { text-align:right; }
h1 { font-size:26px; margin:0; color:#000; }
table { width:100%; border-collapse: collapse; margin-top:20px; font-size:13px; color:#000; }
th, td { padding:10px; text-align:center; border-bottom:1px solid #000; }
th { background:#4CAF50; color:white; } /* Cabecera verde */
tr:nth-child(even) td { background:#f2f2f2; }
tr:hover td { background:#e0e0e0; }
tfoot td { font-weight:bold; }
footer { margin-top:30px; font-size:11px; text-align:right; color:#000; }
header img {
    height: 120px; /* aumenta la altura */
    width: auto;   /* mantiene la proporción */
}


</style>
</head>
<body>

<header>
       <img src='" . $logoUrl . "' alt='Logo'>

    <div>
        <strong>Usuario:</strong> $usuario<br>
        <strong>Fecha:</strong> " . date('d/m/Y H:i') . "
    </div>
</header>

<h1>$titulo</h1>

<table>
<tr>
<th>ID</th>
<th>Fecha</th>
<th>Producto</th>
<th>Cantidad</th>
<th>Precio Unitario</th>
<th>Descuento</th>
<th>Total</th>
<th>Estado</th>
</tr>
";

// Inicializar total general
$totalGeneral = 0;

// Llenar tabla
while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
  $fecha = $r['fecha'] instanceof DateTime ? $r['fecha']->format('Y-m-d H:i') : $r['fecha'];
  $precio_unitario = number_format($r['precio_unitario'] ?? 0, 2, '.', ',');
  $descuento = number_format($r['descuento'] ?? 0, 2);
  $total = ($r['cantidad'] ?? 0) * ($r['precio_unitario'] ?? 0) - ($r['descuento'] ?? 0);
  $totalGeneral += $total;
  $total_formateado = number_format($total, 2, '.', ',');

  $html .= "<tr>
        <td>{$r['id']}</td>
        <td>{$fecha}</td>
        <td>{$r['producto']}</td>
        <td>{$r['cantidad']}</td>
        <td>Bs {$precio_unitario}</td>
        <td>Bs {$descuento}</td>
        <td>Bs {$total_formateado}</td>
        <td>{$r['estado']}</td>
    </tr>";
}

// Total general
$totalGeneralFormateado = number_format($totalGeneral, 2, '.', ',');
$html .= "<tfoot>
<tr>
<td colspan='6'>TOTAL GENERAL</td>
<td>Bs {$totalGeneralFormateado}</td>
<td>-</td>
</tr>
</tfoot>";

$html .= "</table>
<footer>EcoAbrigo - Sistema Administrativo</footer>
</body>
</html>";

// Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("reporte_" . strtolower($tabla) . ".pdf", ["Attachment" => true]);

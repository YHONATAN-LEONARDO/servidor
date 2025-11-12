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

// Construir SQL según filtro
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

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo; ?></title>

</head>
<style>
    /* ----------------- BODY ----------------- */
    body {
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #F0F0F0;
        color: #111;
        padding-top: 140px;
    }

    /* ----------------- TITULO ----------------- */
    h2 {
        text-align: center;
        color: #E7473C;
        margin: 20px 0;
    }

    /* ----------------- BOTONES ----------------- */
    a.btn {
        display: inline-block;
        text-decoration: none;
        padding: 8px 12px;
        margin: 10px 5px;
        border-radius: 5px;
        font-weight: bold;
        color: #FFF;
        background-color: #E7473C;
        transition: all 0.2s;

        /* NUEVO: alineación a la derecha */
        float: right;
    }

    a.btn:hover {
        background-color: #FFE6E4;
        color: #111;
    }


    /* ----------------- TABLA ----------------- */
    table {
        width: 100%;
        max-width: 1000px;
        margin: 20px auto;
        border-collapse: collapse;
        background-color: #FFF;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
        margin-top: 140px;

    }

    th,
    td {
        padding: 10px 12px;
        border-bottom: 1px solid #DDD;
        text-align: left;
    }

    th {
        background-color: #E7473C;
        color: #FFF;
        font-weight: bold;
    }

    tbody tr:hover {
        background-color: #FFE6E4;
    }

    td {
        vertical-align: middle;
    }

    /* ----------------- RESPONSIVE ----------------- */
    @media screen and (max-width: 768px) {

        table,
        thead,
        tbody,
        th,
        td,
        tr {
            display: block;
        }

        th {
            display: none;
        }

        td {
            display: flex;
            justify-content: space-between;
            padding: 8px 10px;
            border-bottom: 1px solid #DDD;
        }

        td::before {
            content: attr(data-label);
            font-weight: bold;
        }
    }
</style>

<body>
    <?php include '../sidebar.php'; ?>

    <h2><?php echo $titulo; ?></h2>
    <a class="btn" href="pdf.php?tabla=<?php echo $tabla; ?>&tipo=<?php echo $tipo; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>">Descargar PDF</a>

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

        <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['fecha']->format('Y-m-d H:i'); ?></td>
                <td><?php echo $row['producto'] ?? '-'; ?></td>
                <td><?php echo $row['cantidad'] ?? '-'; ?></td>
                <td>Bs <?php echo number_format($row['precio_unitario'] ?? 0, 2, '.', ','); ?></td>
                <td><?php echo number_format($row['descuento'] ?? 0, 2); ?></td>
                <td>Bs <?php echo number_format($row['total'], 2); ?></td>
                <td><?php echo $row['estado']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>

</html>
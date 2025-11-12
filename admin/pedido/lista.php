<?php
require_once __DIR__ . '/../../app/config/session.php';
require_once __DIR__ . '/../../app/config/database.php';

$sql = "
SELECT 
  v.id AS venta_id,
  v.fecha,
  v.estado,
  v.total,
  c.nombre AS cliente_nombre,
  c.apellido AS cliente_apellido,
  f.nit_cliente,
  f.razon_social,
  f.lugar_entrega,
  f.observacion,
  p.titulo AS producto_titulo,
  p.imagen,
  vd.cantidad,
  vd.precio_unitario
FROM ventas v
LEFT JOIN clientes c ON v.cliente_id = c.id
JOIN ventas_detalle vd ON v.id = vd.venta_id
JOIN productos p ON vd.producto_id = p.id
LEFT JOIN facturas f ON v.id = f.venta_id
ORDER BY v.fecha DESC, v.id DESC

";

$stmt = sqlsrv_query($conn, $sql);

$pedidos = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
  $vid = (int)$row['venta_id'];
  if (!isset($pedidos[$vid])) {
    $pedidos[$vid] = [
      'id' => $vid,
      'fecha' => $row['fecha'],
      'estado' => $row['estado'],
      'total' => $row['total'],
      'cliente' => trim(($row['cliente_nombre'] ?? '') . ' ' . ($row['cliente_apellido'] ?? '')),
      'nit' => $row['nit_cliente'] ?? '',
      'razon' => $row['razon_social'] ?? '',
      'lugar' => $row['lugar_entrega'] ?? '',
      'obs' => $row['observacion'] ?? '',
      'productos' => []
    ];
  }
  $pedidos[$vid]['productos'][] = [
    'titulo' => $row['producto_titulo'],
    'imagen' => $row['imagen'] ?? '',
    'cantidad' => $row['cantidad'],
    'precio' => $row['precio_unitario']
  ];
}

function fmt_fecha($f)
{
  if ($f instanceof DateTime) return $f->format('Y-m-d H:i');
  if (is_array($f) && isset($f['date'])) return date('Y-m-d H:i', strtotime($f['date']));
  return htmlspecialchars((string)$f);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Pedidos | EcoAbrigo</title>
  <link rel="stylesheet" href="../styles.css">

</head>
<style>
  /* ----------------- BODY ----------------- */
  body {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #F0F0F0;
    /* Humo blanco */
    color: #111111;
    /* Negro */
    padding-top: 140px;
  }

  /* ----------------- HEADER ----------------- */
  h1 {
    text-align: center;
    color: #E7473C;
    /* Rojo brillante */
    margin: 20px 0;
  }

  /* ----------------- PEDIDO ----------------- */
  .pedido {
    max-width: 1000px;
    margin: 20px auto;
    background-color: #FFFFFF;
    /* Blanco puro */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    /* Sombra negra ligera */
    border-radius: 8px;
    padding: 15px;
    
  }

  /* ----------------- ENCABEZADO PEDIDO ----------------- */
  .pedido h2 {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #E7473C;
    /* Rojo brillante */
    margin-bottom: 10px;
    font-size: 1.2em;
  }

  /* ----------------- ESTADO ----------------- */
  .estado {
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: bold;
    color: #FFFFFF;
    /* Texto blanco puro */
  }

  .estado-pendiente {
    background-color: #E7473C;
  }

  /* Rojo brillante */
  .estado-completado {
    background-color: #111111;
  }

  /* Negro */
  .estado-cancelado {
    background-color: #DDD;
    color: #111111;
  }

  /* Gris claro */

  /* ----------------- INFO PEDIDO ----------------- */
  .info {
    margin-bottom: 10px;
    font-size: 0.95em;
  }

  /* ----------------- DETALLE EXTRA ----------------- */
  .detalle-extra div {
    margin-bottom: 5px;
    font-size: 0.95em;
  }

  /* ----------------- TABLA PRODUCTOS ----------------- */
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    margin-bottom: 10px;
  }

  th,
  td {
    padding: 8px 10px;
    border-bottom: 1px solid #DDD;
    /* Gris claro */
    text-align: left;
  }

  th {
    background-color: #E7473C;
    /* Rojo brillante */
    color: #FFFFFF;
    /* Blanco puro */
  }

  tbody tr:hover {
    background-color: #FFE6E4;
    /* Rojo muy claro / rosa suave */
  }

  /* ----------------- IMAGEN PRODUCTO ----------------- */
  .img-prod {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
  }

  /* ----------------- TÍTULO PRODUCTO ----------------- */
  .prod-titulo {
    font-weight: bold;
  }

  /* ----------------- TOTAL ----------------- */
  .total {
    text-align: right;
    font-weight: bold;
    font-size: 1.1em;
    color: #E7473C;
    /* Rojo brillante */
    margin-top: 10px;
  }
</style>

<body>

  <?php include '../sidebar.php'; ?>

  <h1>Listado de Pedidos</h1>

  <?php foreach ($pedidos as $p): ?>
    <div class="pedido">
      <h2>
        Pedido #<?= $p['id'] ?> - <?= htmlspecialchars($p['cliente']) ?>
        <span class="estado estado-<?= strtolower($p['estado']) ?>">
          <?= htmlspecialchars($p['estado']) ?>
        </span>
      </h2>
      <div class="info">Fecha: <?= fmt_fecha($p['fecha']) ?></div>

      <?php if ($p['razon'] || $p['lugar'] || $p['obs']): ?>
        <div class="detalle-extra">
          <?php if ($p['razon']): ?>
            <div><strong>Factura:</strong> <?= htmlspecialchars($p['razon']) ?> (NIT: <?= htmlspecialchars($p['nit']) ?>)</div>
          <?php endif; ?>
          <?php if ($p['lugar']): ?>
            <div><strong>Lugar de entrega:</strong> <?= htmlspecialchars($p['lugar']) ?></div>
          <?php endif; ?>
          <?php if ($p['obs']): ?>
            <div><strong>Observaciones:</strong> <?= htmlspecialchars($p['obs']) ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <table>
        <thead>
          <tr>
            <th>Imagen</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio (Bs)</th>
            <th>Subtotal (Bs)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($p['productos'] as $pr):
            $sub = $pr['cantidad'] * $pr['precio'];
            $img = htmlspecialchars($pr['imagen'] ?? '');
          ?>
            <tr>
              <td>
                <?php if ($img): ?>
                  <img class="img-prod" src="/imagenes/<?= $img ?>" alt="<?= htmlspecialchars($pr['titulo']) ?>">
                <?php else: ?>
                  <img class="img-prod" src="/imagenes/default.png" alt="sin imagen">
                <?php endif; ?>
              </td>
              <td class="prod-titulo"><?= htmlspecialchars($pr['titulo']) ?></td>
              <td><?= (int)$pr['cantidad'] ?></td>
              <td><?= number_format($pr['precio'], 2) ?></td>
              <td><?= number_format($sub, 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="total">Total: <?= number_format($p['total'], 2) ?> Bs</div>
    </div>
  <?php endforeach; ?>
</body>

</html>
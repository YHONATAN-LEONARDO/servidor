<?php
// admin/index.php
include __DIR__ . '/../../app/config/database.php';

$active = 'dashboard';

// Funciones auxiliares
function kpi_scalar($conn, $sql)
{
  $stmt = sqlsrv_query($conn, $sql);
  if (!$stmt) return 0;
  $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC);
  return $row ? (float)$row[0] : 0;
}
function tabla_existe($conn, $nombre)
{
  $sql = "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ?";
  $s = sqlsrv_query($conn, $sql, [$nombre]);
  return $s && sqlsrv_fetch_array($s);
}
function fnum($n)
{
  return number_format((float)$n, 0, '.', ',');
}
function fmon($n)
{
  return number_format((float)$n, 2, '.', ',');
}

// KPIs base
$stock = tabla_existe($conn, 'productos') ? kpi_scalar($conn, "SELECT SUM(CAST(cantidad AS FLOAT)) FROM productos") : 0;
$vendidos = tabla_existe($conn, 'ventas_detalle') ? kpi_scalar($conn, "SELECT SUM(CAST(cantidad AS FLOAT)) FROM ventas_detalle") : 0;
$ventas = tabla_existe($conn, 'ventas') ? kpi_scalar($conn, "SELECT COUNT(*) FROM ventas") : 0;
$ingresos = tabla_existe($conn, 'ventas') ? kpi_scalar($conn, "SELECT SUM(CAST(total AS FLOAT)) FROM ventas") : 0;
$clientes = tabla_existe($conn, 'clientes') ? kpi_scalar($conn, "SELECT COUNT(*) FROM clientes") : 0;
$proveedores = tabla_existe($conn, 'proveedores') ? kpi_scalar($conn, "SELECT COUNT(*) FROM proveedores") : 0;
$hoy = tabla_existe($conn, 'ventas') ? kpi_scalar($conn, "SELECT SUM(CAST(total AS FLOAT)) FROM ventas WHERE CONVERT(date,fecha)=CONVERT(date,GETDATE())") : 0;
$mes = tabla_existe($conn, 'ventas') ? kpi_scalar($conn, "SELECT SUM(CAST(total AS FLOAT)) FROM ventas WHERE MONTH(fecha)=MONTH(GETDATE()) AND YEAR(fecha)=YEAR(GETDATE())") : 0;

// NUEVOS: gastos en compras y ganancia
$gastos = tabla_existe($conn, 'compras') ? kpi_scalar($conn, "SELECT SUM(CAST(total AS FLOAT)) FROM compras") : 0;
$ganancia = $ingresos - $gastos;
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Administración</title>
  <link rel="stylesheet" href="/public/css/normalize.css">
  <link rel="stylesheet" href="../styles.css">

  <style>

  </style>
</head>
<style>
  /* ----------------- BODY ----------------- */
  body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background-color: #F0F0F0;
    /* Humo blanco */
    color: #111111;
    /* Negro */
  }

  /* ----------------- BOTONES ----------------- */
  .btn {
    display: inline-block;
    background-color: #E7473C;
    /* Rojo brillante */
    color: #FFFFFF;
    /* Blanco puro */
    padding: 8px 14px;
    margin: 5px 5px 15px 0;
    text-decoration: none;
    font-weight: bold;
    border-radius: 5px;
    transition: all 0.2s;
  }

  .btn:hover {
    background-color: #FFE6E4;
    /* Rosa suave */
    color: #111111;
    /* Negro */
  }

  /* ----------------- LAYOUT ----------------- */
  .layout {
    display: flex;
  }

  .content {
    flex: 1;
    padding: 20px;
  }

  /* ----------------- KPIs ----------------- */
  .grid-kpis {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin: 20px 0;
  }

  .kpi {
    background-color: #FFFFFF;
    /* Blanco puro */
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    /* Sombra ligera */
    text-align: center;
  }

  .kpi-label {
    font-weight: bold;
    color: #111111;
    /* Negro */
    margin-bottom: 8px;
  }

  .kpi-value {
    font-size: 1.5em;
    color: #E7473C;
    /* Rojo brillante */
  }

  /* ----------------- GRÁFICAS ----------------- */
  .charts {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
  }

  .chart-card {
    background-color: #FFFFFF;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    flex: 1 1 300px;
  }

  .chart-card h3 {
    color: #E7473C;
    margin-top: 0;
  }
</style>

<body>
  <?php include '../sidebar.php'; ?>

  <div class="layout">

    <main class="content">
      <h1>PANEL DE ADMINISTRACIÓN</h1>

      <!-- Botón único -->
      <a class="btn" href="../index.php">Volver</a>
      <a class="btn" href="/admin/reportes/filtros.php">Reportes filtrados</a>

      <form action="/admin/reportes/informe_general.php" method="get">
        <button class="btn" type="submit">Generar reporte totales</button>
      </form>

      <!-- KPIs en 4 columnas -->
      <section class="grid-kpis">
        <div class="kpi">
          <p class="kpi-label">Stock disponible</p>
          <p class="kpi-value"><?php echo fnum($stock); ?></p>
        </div>
        <div class="kpi">
          <p class="kpi-label">Productos vendidos</p>
          <p class="kpi-value"><?php echo fnum($vendidos); ?></p>
        </div>
        <div class="kpi">
          <p class="kpi-label">Ventas totales</p>
          <p class="kpi-value"><?php echo fnum($ventas); ?></p>
        </div>
        <div class="kpi">
          <p class="kpi-label">Ingresos</p>
          <p class="kpi-value">Bs <?php echo fmon($ingresos); ?></p>
        </div>

        <div class="kpi">
          <p class="kpi-label">Gasto en compras</p>
          <p class="kpi-value">Bs <?php echo fmon($gastos); ?></p>
        </div>
        <div class="kpi">
          <p class="kpi-label">Ganancia neta</p>
          <p class="kpi-value">Bs <?php echo fmon($ganancia); ?></p>
        </div>
        <div class="kpi">
          <p class="kpi-label">Clientes</p>
          <p class="kpi-value"><?php echo fnum($clientes); ?></p>
        </div>
        <div class="kpi">
          <p class="kpi-label">Proveedores</p>
          <p class="kpi-value"><?php echo fnum($proveedores); ?></p>
        </div>
      </section>

      <!-- Gráficas -->
      <div class="charts">
        <div class="chart-card">
          <h3>Ventas últimos 7 días</h3><canvas id="chart7"></canvas>
        </div>
        <div class="chart-card">
          <h3>Ventas por género</h3><canvas id="chartCat"></canvas>
        </div>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Datos de ejemplo
    new Chart(document.getElementById('chart7'), {
      type: 'line',
      data: {
        labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
        datasets: [{
          label: 'Ventas (Bs)',
          data: [120, 90, 140, 110, 180, 150, 130]
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    // Solo hombre y mujer
    new Chart(document.getElementById('chartCat'), {
      type: 'doughnut',
      data: {
        labels: ['Hombre', 'Mujer'],
        datasets: [{
          data: [60, 40]
        }]
      },
      options: {
        responsive: true
      }
    });
  </script>
</body>

</html>
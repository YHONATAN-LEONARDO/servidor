  <?php
  // admin/index.php
  include __DIR__ . '/../app/config/database.php';
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

  // KPIs básicos (solo lo necesario)
  $stock       = tabla_existe($conn, 'productos') ? kpi_scalar($conn, "SELECT SUM(CAST(cantidad AS FLOAT)) FROM productos") : 0;
  $vendidos    = tabla_existe($conn, 'ventas_detalle') ? kpi_scalar($conn, "SELECT SUM(CAST(cantidad AS FLOAT)) FROM ventas_detalle") : 0;
  $ventas      = tabla_existe($conn, 'ventas') ? kpi_scalar($conn, "SELECT COUNT(*) FROM ventas") : 0;
  $clientes    = tabla_existe($conn, 'clientes') ? kpi_scalar($conn, "SELECT COUNT(*) FROM clientes") : 0;
  $proveedores = tabla_existe($conn, 'proveedores') ? kpi_scalar($conn, "SELECT COUNT(*) FROM proveedores") : 0;
  // session_start();

  // echo "<pre>";
  // var_dump($_SESSION);
  // echo "</pre>";
  ?>
  <!DOCTYPE html>
  <html lang="es">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Sistema</title>
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="./styles.css">
  </head>

  <body>
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="layout">

      <main class="content">

        <h1>Panel General</h1>
        <?php
        // Obtener nombre del usuario desde la sesión
        $nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

        // Mostrar mensaje de bienvenida con estilo
        echo "<div class='welcome-message'>
        <h2>¡Bienvenido, " . htmlspecialchars($nombre_usuario) . "!</h2>
        <p>Explora las secciones y mantén todo bajo control.</p>
      </div>";
        ?>

        <!-- Botón de reporte -->
        <form action="/admin/reportes/informe_general.php" method="get">
          <button class="btn" type="submit">Generar reporte</button>
        </form>

        <!-- KPIs esenciales -->
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
            <p class="kpi-label">Clientes registrados</p>
            <p class="kpi-value"><?php echo fnum($clientes); ?></p>
          </div>

          <div class="kpi">
            <p class="kpi-label">Proveedores activos</p>
            <p class="kpi-value"><?php echo fnum($proveedores); ?></p>
          </div>
        </section>
      </main>
    </div>
  </body>

  </html>
  <style>
    /* ========= PALETA =========
  Humo blanco       #F0F0F0  (fondo general, inputs)
  Negro             #111111  (texto)
  Rojo brillante    #E7473C  (botones, énfasis)
  Blanco puro       #FFFFFF  (tarjetas, contenedores)
  Rojo muy claro    #FFE6E4  (hover filas / efectos suaves)
  Sombra ligera     rgba(0,0,0,0.1)
  Gris claro        #DDD     (bordes)
============================= */

    /* Reset básico extra sobre normalize */
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background-color: #F0F0F0;
      /* Humo blanco */
      color: #111111;
      /* Negro */
    }

    /* Si el sidebar ocupa el lado izquierdo, el layout centra el contenido restante */
    .layout {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      /* Centra horizontalmente el contenido */
      align-items: flex-start;
      /* Arriba, pero centrado */
      padding: 40px 16px;
      margin-top: 160px;
    }

    /* Contenedor principal del panel */
    .content {
      background-color: #FFFFFF;
      /* Blanco puro para resaltar sobre el fondo */
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      /* Sombra negra ligera */
      border-radius: 18px;
      max-width: 960px;
      width: 100%;
      padding: 32px 40px;
    }

    /* Título principal centrado */
    .content h1 {
      margin: 0 0 16px;
      text-align: center;
      font-size: 1.8rem;
    }

    /* Mensaje de bienvenida centrado */
    .welcome-message {
      text-align: center;
      background-color: #F0F0F0;
      /* Humo blanco */
      border-radius: 14px;
      padding: 16px 20px;
      margin-bottom: 24px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .welcome-message h2 {
      margin: 0 0 6px;
      font-size: 1.4rem;
    }

    .welcome-message p {
      margin: 0;
      font-size: 0.95rem;
    }

    /* Botón principal (Reporte) */
    form {
      display: flex;
      justify-content: center;
      /* Centra el botón */
      margin-bottom: 24px;
    }

    .btn {
      border: none;
      cursor: pointer;
      background-color: #E7473C;
      /* Rojo brillante */
      color: #FFFFFF;
      /* Blanco puro */
      font-weight: 600;
      padding: 10px 20px;
      border-radius: 999px;
      font-size: 0.95rem;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      transition:
        background-color 0.2s ease,
        color 0.2s ease,
        transform 0.1s ease,
        box-shadow 0.2s ease;
    }

    .btn:hover {
      background-color: #FFFFFF;
      /* Hover blanco */
      color: #E7473C;
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
      transform: translateY(-1px);
      border: 1px solid #E7473C;
    }

    .btn:active {
      transform: translateY(0);
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
    }

    /* Grid de KPIs centrado */
    .grid-kpis {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 16px;
    }

    /* Tarjetas de KPI */
    .kpi {
      background-color: #F0F0F0;
      /* Humo blanco */
      border-radius: 14px;
      padding: 16px 14px;
      border: 1px solid #DDD;
      /* Gris claro */
      text-align: center;
      /* Contenido centrado */
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      position: relative;
      overflow: hidden;
    }

    /* Pequeña barra superior roja para énfasis */
    .kpi::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background-color: #E7473C;
      /* Rojo brillante */
    }

    /* Hover suave usando el rojo muy claro */
    .kpi:hover {
      background-color: #FFE6E4;
      /* Rojo muy claro / rosa suave */
    }

    /* Texto de KPI */
    .kpi-label {
      margin: 8px 0 4px;
      font-size: 0.9rem;
      color: #111111;
    }

    .kpi-value {
      margin: 0;
      font-size: 1.4rem;
      font-weight: 700;
      color: #E7473C;
      /* Rojo brillante para el número */
    }

    /* Responsivo básico */
    @media (max-width: 600px) {
      .content {
        padding: 24px 16px;
      }

      .content h1 {
        font-size: 1.5rem;
      }

      .welcome-message h2 {
        font-size: 1.2rem;
      }
    }
  </style>
<?php
// admin/contactos/lista.php
include '../../app/config/session.php';
include '../../app/config/database.php';

// Consulta de contactos (ajusta si tu esquema difiere)
$sql = "
  SELECT TOP 100
    id,
    creado_en,
    nombre,
    correo,
    asunto,
    mensaje,
    ip,
    user_agent
  FROM dbo.contactos
  ORDER BY id DESC
";
$resultado = sqlsrv_query($conn, $sql);
if ($resultado === false) {
  die('Error al consultar contactos: ' . print_r(sqlsrv_errors(), true));
}

function sv_fmt_fecha($f)
{
  // SQLSRV puede devolver DateTime o array con 'date'
  if ($f instanceof DateTime) return $f->format('Y-m-d H:i');
  if (is_array($f) && isset($f['date'])) return date('Y-m-d H:i', strtotime($f['date']));
  return htmlspecialchars((string)$f);
}
function sv_trunc($txt, $len = 120)
{
  $txt = (string)$txt;
  return mb_strlen($txt, 'UTF-8') > $len ? mb_substr($txt, 0, $len, 'UTF-8') . '…' : $txt;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contactos | Listado</title>
  <link rel="stylesheet" href="/admin/styles.css">
</head>
<style>
  /* ----------------- BODY ----------------- */
  body.sv-page {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #F0F0F0;
    /* Humo blanco */
    color: #111111;
    /* Negro */
  }

  /* ----------------- HEADER ----------------- */
  .sv-header {
    text-align: center;
    padding: 15px 0;
  }

  .sv-title {
    color: #E7473C;
    /* Rojo brillante */
    margin: 0;
  }

  /* ----------------- BOTÓN VOLVER ----------------- */
  .sv-actions {
    max-width: 1000px;
    margin: 10px auto;
  }

  .sv-btn.sv-btn--back {
    background-color: #E7473C;
    /* Rojo brillante */
    color: #FFFFFF;
    /* Blanco puro */
    padding: 6px 12px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
  }

  .sv-btn.sv-btn--back:hover {
    background-color: #FFE6E4;
    /* Rojo muy claro / rosa suave */
    color: #111111;
    /* Negro */
  }

  /* ----------------- TABLA ----------------- */
  .sv-container {
    max-width: 1000px;
    margin: 0 auto 30px;
    background-color: #FFFFFF;
    /* Blanco puro */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    /* Sombra negra ligera */
    border-radius: 8px;
    padding: 15px;
  }

  .sv-table {
    width: 100%;
    border-collapse: collapse;
  }

  .sv-th,
  .sv-td {
    padding: 8px 10px;
    border-bottom: 1px solid #DDD;
    /* Gris claro */
    text-align: left;
  }

  .sv-th {
    background-color: #E7473C;
    /* Rojo brillante */
    color: #FFFFFF;
    /* Blanco puro */
  }

  .sv-table__body tr:hover {
    background-color: #FFE6E4;
    /* Rojo muy claro / rosa suave */
  }

  /* ----------------- CELDAS ----------------- */
  .sv-td--id {
    width: 50px;
    font-weight: bold;
  }

  .sv-td--fecha {
    width: 120px;
  }

  .sv-td--cliente {
    width: 180px;
  }

  .sv-td--estado {
    width: 150px;
    font-weight: bold;
  }

  .sv-td--total {}

  /* ----------------- TEXTO Y MENSAJE ----------------- */
  .sv-td--total {
    white-space: pre-wrap;
    word-wrap: break-word;
    max-width: 300px;
    font-size: 0.95em;
    color: #111111;
  }
</style>

<body class="sv-page sv-page--contactos">
  <?php include '../sidebar.php'; ?>

  <header class="header sv-header">
    <h1 class="titulo sv-title">Listado de Contactos</h1>
  </header>

  <!-- Botón Volver -->
  <div class="sv-actions">
    <a href="../index.php" class="sv-btn sv-btn--back">Volver</a>
  </div>

  <main class="sv-container">
    <div class="sv-table-wrap">
      <table class="sv-table">
        <thead class="sv-table__head">
          <tr class="sv-table__head-row">
            <th class="sv-th sv-th--id">ID</th>
            <th class="sv-th sv-th--fecha">Fecha</th>
            <th class="sv-th sv-th--cliente">Nombre</th>
            <th class="sv-th sv-th--cliente">Correo</th>
            <th class="sv-th sv-th--estado">Asunto</th>
            <th class="sv-th sv-th--total">Mensaje</th>
          </tr>
        </thead>
        <tbody class="sv-table__body">
          <?php while ($row = sqlsrv_fetch_array($resultado, SQLSRV_FETCH_ASSOC)) { ?>
            <tr class="sv-row">
              <td class="sv-td sv-td--id"><?php echo (int)$row['id']; ?></td>
              <td class="sv-td sv-td--fecha"><?php echo sv_fmt_fecha($row['creado_en']); ?></td>
              <td class="sv-td sv-td--cliente"><?php echo htmlspecialchars($row['nombre'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
              <td class="sv-td sv-td--cliente"><?php echo htmlspecialchars($row['correo'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
              <td class="sv-td sv-td--estado"><?php echo htmlspecialchars($row['asunto'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
              <td class="sv-td sv-td--total">
                <?php echo nl2br(htmlspecialchars(sv_trunc($row['mensaje'] ?? ''), ENT_QUOTES, 'UTF-8')); ?>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </main>
</body>

</html>
<?php
// admin/clientes/lista.php
include '../../app/config/session.php';
// require_roles(['admin','vendedor']);
include '../../app/config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

/* -------------------- POST: eliminar (confirmado) -------------------- */
$notice_success = '';
$notice_error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = $_POST['accion'] ?? '';
  $token  = $_POST['csrf']   ?? '';
  if (!hash_equals($_SESSION['csrf'], $token)) {
    $notice_error = 'Operación rechazada por CSRF.';
  } else if ($accion === 'eliminar_confirmado') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
      $notice_error = 'ID inválido.';
    } else {
      $ok = sqlsrv_query($conn, "DELETE FROM dbo.clientes WHERE id = ?", [$id]);
      if ($ok) {
        // Redirección estilo PRG para evitar repost en refresh
        header('Location: ./lista.php?mensaje=3');
        exit;
      } else {
        $notice_error = 'No se pudo eliminar el cliente.';
      }
    }
  }
}

/* -------------------- GET + listado -------------------- */
$mensaje = $_GET['mensaje'] ?? null;
$q = trim($_GET['q'] ?? '');

$where = '';
$params = [];
if ($q !== '') {
  $where = "WHERE (nombre LIKE ? OR apellido LIKE ? OR correo LIKE ? OR telefono LIKE ?)";
  $like = '%' . $q . '%';
  $params = [$like, $like, $like, $like];
}

$sql = "
  SELECT id, nombre, apellido, correo, telefono, creado_en
  FROM dbo.clientes
  $where
  ORDER BY id DESC
";
$rs = sqlsrv_query($conn, $sql, $params);
if ($rs === false) die('Error al consultar clientes: ' . print_r(sqlsrv_errors(), true));

function cl_fmt_fecha($f)
{
  if ($f instanceof DateTime) return $f->format('Y-m-d');
  if (is_array($f) && isset($f['date'])) return date('Y-m-d', strtotime($f['date']));
  return htmlspecialchars((string)$f);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Clientes | Listado</title>

  <link rel="stylesheet" href="../styles.css">


</head>

<style>
  /* ----------------- BODY ----------------- */
  body.cl-page {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #F0F0F0;
    /* Humo blanco */
    color: #111111;
    /* Negro */
  }

  /* ----------------- HEADER ----------------- */
  .cl-header {
    text-align: center;
    margin: 20px 0;
  }

  .cl-title {
    font-size: 28px;
    font-weight: bold;
    color: #E7473C;
    /* Rojo brillante */
  }

  /* ----------------- BOTONES ----------------- */
  .cl-btn {
    display: inline-block;
    padding: 6px 12px;
    margin: 2px;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    color: #FFFFFF;
    /* Blanco puro */
    background-color: #E7473C;
    /* Rojo brillante */
    border: none;
  }

  .cl-btn:hover {
    background-color: #FFE6E4;
    /* Rojo muy claro / rosa suave */
    color: #111111;
    /* Negro */
  }

  .cl-btn--primary {
    background-color: #E7473C;
  }

  .cl-btn--back {
    background-color: #111111;
  }

  .cl-btn--danger {
    background-color: #E7473C;
  }

  .cl-btn--mini {
    padding: 4px 8px;
    font-size: 0.85em;
  }

  .cl-btn--update {
    background-color: #ff1a1aff;
  }

  /* puedes ajustar si quieres rojo también */

  /* ----------------- ALERTAS ----------------- */
  .noti {
    padding: 10px 15px;
    margin: 10px auto;
    max-width: 800px;
    border-radius: 5px;
    font-weight: bold;
    text-align: center;
  }

  .noti.ok {
    background-color: #FFFFFF;
    border-left: 4px solid #E7473C;
    color: #111111;
  }

  .noti.err {
    background-color: #FFE6E4;
    border-left: 4px solid #E7473C;
    color: #111111;
  }

  /* ----------------- ACCIONES TOP ----------------- */
  .cl-actions-top {
    max-width: 800px;
    margin: 0 auto 15px auto;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
  }

  /* ----------------- TABLA ----------------- */
  .cl-table-wrap {
    max-width: 1000px;
    margin: 0 auto 30px auto;
    overflow-x: auto;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    /* Sombra negra ligera */
    border-radius: 8px;
  }

  .cl-table {
    width: 100%;
    border-collapse: collapse;
    background-color: #FFFFFF;
    /* Blanco puro */
    border-radius: 8px;
  }

  .cl-th,
  .cl-td {
    padding: 8px 12px;
    border-bottom: 1px solid #DDD;
    /* Gris claro */
  }

  .cl-th {
    background-color: #E7473C;
    /* Rojo brillante */
    color: #FFFFFF;
    /* Blanco puro */
    font-weight: bold;
    text-align: left;
  }

  .cl-td--acciones {
    display: flex;
    gap: 5px;
  }

  .cl-table__body tr:hover {
    background-color: #FFE6E4;
    /* Rojo muy claro / rosa suave */
  }

  /* ----------------- MODAL ----------------- */
  .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }

  .modal.show {
    display: flex;
  }

  .modal-card {
    background-color: #FFFFFF;
    /* Blanco puro */
    border-radius: 8px;
    padding: 20px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    /* Sombra negra ligera */
  }

  .modal-title {
    font-size: 22px;
    margin-bottom: 15px;
    color: #E7473C;
    /* Rojo brillante */
  }

  .modal-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
  }
</style>

<body class="cl-page cl-page--clientes">
  <?php include '../sidebar.php'; ?>

  <header class="header cl-header">
    <div class="wrap">
      <h1 class="titulo cl-title">Clientes</h1>
    </div>
  </header>

  <div class="wrap">
    <div class="cl-actions-top">
      <a href="./crear.php" class="cl-btn cl-btn--primary">Nuevo Cliente</a>
    </div>

    <?php if ($mensaje == 1) { ?>
      <div class="noti ok" id="msg">Cliente creado correctamente</div>
    <?php } elseif ($mensaje == 2) { ?>
      <div class="noti ok" id="msg">Cliente actualizado correctamente</div>
    <?php } elseif ($mensaje == 3) { ?>
      <div class="noti ok" id="msg">Cliente eliminado correctamente</div>
    <?php } ?>

    <?php if ($notice_success): ?>
      <div class="noti ok" id="msg-ok"><?php echo htmlspecialchars($notice_success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($notice_error): ?>
      <div class="noti err" id="msg-err"><?php echo htmlspecialchars($notice_error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>



    <main class="cl-container">
      <div class="cl-table-wrap">
        <table class="cl-table">
          <thead class="cl-table__head">
            <tr class="cl-table__head-row">
              <th class="cl-th cl-th--nombre">Nombre</th>
              <th class="cl-th cl-th--correo">Correo</th>
              <th class="cl-th cl-th--telefono">Teléfono</th>
              <th class="cl-th cl-th--creado">Creado</th>
              <th class="cl-th cl-th--acciones">Acciones</th>
            </tr>
          </thead>
          <tbody class="cl-table__body">
            <?php while ($row = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC)) {
              $nombreCompleto = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? ''));
            ?>
              <tr class="cl-row">
                <td class="cl-td cl-td--nombre"><?php echo htmlspecialchars($nombreCompleto ?: '—'); ?></td>
                <td class="cl-td cl-td--correo"><?php echo htmlspecialchars($row['correo'] ?? '—'); ?></td>
                <td class="cl-td cl-td--telefono"><?php echo htmlspecialchars($row['telefono'] ?? '—'); ?></td>
                <td class="cl-td cl-td--creado"><?php echo cl_fmt_fecha($row['creado_en']); ?></td>
                <td class="cl-td cl-td--acciones">
                  <a class="cl-btn cl-btn--mini cl-btn--update" href="./editar.php?id=<?php echo (int)$row['id']; ?>">Editar</a>

                  <!-- Botón abre modal (sin alert()) -->
                  <!-- <button type="button"
                    class="cl-btn cl-btn--mini cl-btn--danger js-open-del"
                    data-id="<?php echo (int)$row['id']; ?>"
                    data-nombre="<?php echo htmlspecialchars($nombreCompleto ?: '', ENT_QUOTES, 'UTF-8'); ?>"
                    data-correo="<?php echo htmlspecialchars($row['correo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    Eliminar
                  </button> -->
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- Modal de confirmación (sin alert/confirm nativos) -->
  <section id="modal" class="modal" aria-hidden="true">
    <div class="modal-card">
      <h2 class="modal-title">Confirmar eliminación</h2>
      <div class="modal-grid">
        <div><strong>ID:</strong> <span id="del-id-text">—</span></div>
        <div><strong>Nombre:</strong> <span id="del-nombre-text">—</span></div>
        <div><strong>Correo:</strong> <span id="del-correo-text">—</span></div>
      </div>
      <form method="POST" class="modal-actions">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="accion" value="eliminar_confirmado">
        <input type="hidden" id="del-id" name="id" value="">
        <button type="button" class="cl-btn" id="btn-cancel">Cancelar</button>
        <button type="submit" class="cl-btn cl-btn--danger">Eliminar definitivamente</button>
      </form>
    </div>
  </section>

  <script>
    // Ocultar avisos después de 3s
    for (const id of ['msg', 'msg-ok', 'msg-err']) {
      const el = document.getElementById(id);
      if (el) setTimeout(() => el.style.display = 'none', 3000);
    }

    // Modal
    const modal = document.getElementById('modal');
    const idTxt = document.getElementById('del-id-text');
    const nmTxt = document.getElementById('del-nombre-text');
    const coTxt = document.getElementById('del-correo-text');
    const idInp = document.getElementById('del-id');
    const cancel = document.getElementById('btn-cancel');

    function openModal(id, nombre, correo) {
      idTxt.textContent = String(id);
      nmTxt.textContent = nombre || '—';
      coTxt.textContent = correo || '—';
      idInp.value = String(id);
      modal.classList.add('show');
      modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
      modal.classList.remove('show');
      modal.setAttribute('aria-hidden', 'true');
      idTxt.textContent = '—';
      nmTxt.textContent = '—';
      coTxt.textContent = '—';
      idInp.value = '';
    }

    document.querySelectorAll('.js-open-del').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        const nombre = btn.getAttribute('data-nombre') || '';
        const correo = btn.getAttribute('data-correo') || '';
        openModal(id, nombre, correo);
      });
    });
    cancel.addEventListener('click', closeModal);
    // Cerrar si clic fuera de la tarjeta
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeModal();
    });
    // ESC para cerrar
    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('show')) closeModal();
    });
  </script>
</body>

</html>
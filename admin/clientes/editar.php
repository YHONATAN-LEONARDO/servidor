<?php
// admin/clientes/editar.php
include '../../app/config/session.php';
// require_roles(['admin','vendedor']); 
include '../../app/config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die('ID inválido.');

$errores = [];
$okMsg   = '';
$okPass  = '';

/* Cargar cliente actual */
$st = sqlsrv_query($conn, "SELECT * FROM dbo.clientes WHERE id = ?", [$id]);
$cli = $st ? sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC) : null;
if ($st) sqlsrv_free_stmt($st);
if (!$cli) die('Cliente no encontrado.');

$nombre   = $cli['nombre']   ?? '';
$apellido = $cli['apellido'] ?? '';
$correo   = $cli['correo']   ?? '';
$telefono = $cli['telefono'] ?? '';

/* Handlers POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'], $token)) {
    $errores[] = 'Token inválido, recarga la página.';
  } else {
    $accion = $_POST['accion'] ?? 'datos';

    // 1) Guardar datos del cliente
    if ($accion === 'datos') {
      $nombre   = trim($_POST['nombre']   ?? '');
      $apellido = trim($_POST['apellido'] ?? '');
      $correo   = trim($_POST['correo']   ?? '');
      $telefono = trim($_POST['telefono'] ?? '');

      if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
      if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo no es válido.';
      }

      if ($correo !== '') {
        $chk = sqlsrv_query($conn, "SELECT 1 FROM dbo.clientes WHERE correo = ? AND id <> ?", [$correo, $id]);
        if ($chk && sqlsrv_fetch($chk) === true) $errores[] = 'Ese correo ya pertenece a otro cliente.';
        if ($chk) sqlsrv_free_stmt($chk);
      }

      if (empty($errores)) {
        $sql = "UPDATE dbo.clientes
                SET nombre=?, apellido=?, correo=?, telefono=?
                WHERE id=?";
        $ok = sqlsrv_query($conn, $sql, [
          $nombre,
          $apellido,
          $correo !== '' ? $correo : null,
          $telefono !== '' ? $telefono : null,
          $id
        ]);
        if ($ok) $okMsg = 'Cambios guardados correctamente.';
        else     $errores[] = 'Error al actualizar el cliente.';
      }
    }

    // 2) Resetear contraseña del usuario (por correo)
    if ($accion === 'pass') {
      $new = $_POST['newpass'] ?? '';
      $rep = $_POST['reppass'] ?? '';
      $mailForReset = trim($_POST['correo_ref'] ?? $correo);

      if ($mailForReset === '') {
        $errores[] = 'El cliente no tiene correo; no se puede resetear contraseña.';
      } elseif (!filter_var($mailForReset, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo actual del cliente no es válido.';
      } elseif ($new === '' || $rep === '') {
        $errores[] = 'Debes ingresar y confirmar la nueva contraseña.';
      } elseif ($new !== $rep) {
        $errores[] = 'Las contraseñas no coinciden.';
      } elseif (strlen($new) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
      } else {
        // Buscar usuario por correo
        $u = null;
        $su = sqlsrv_query($conn, "SELECT id FROM dbo.usuarios WHERE correo = ?", [$mailForReset]);
        if ($su !== false) {
          $u = sqlsrv_fetch_array($su, SQLSRV_FETCH_ASSOC) ?: null;
          sqlsrv_free_stmt($su);
        } else {
          $errores[] = 'Error al buscar el usuario por correo.';
        }

        if (empty($errores)) {
          if (!$u) {
            $errores[] = 'No existe usuario con ese correo.';
          } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $upd  = sqlsrv_query($conn, "UPDATE dbo.usuarios SET password_hash=? WHERE id=?", [$hash, (int)$u['id']]);
            if ($upd === false) $errores[] = 'No se pudo actualizar la contraseña.';
            else $okPass = 'Contraseña reseteada correctamente.';
          }
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Clientes | Editar</title>

</head>
<style>
  /* ----------------- GENERAL ----------------- */
  /* ----------------- GENERAL ----------------- */
  body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #F0F0F0;
    color: #111111;
    margin: 0;
    padding: 0;
  }

  .cl-container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 20px;
  }

  .page-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }

  /* ----------------- CARD ----------------- */
  .card {
    background-color: #FFFFFF;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
  }

  .card h2 {
    margin-top: 0;
    color: #E7473C;
  }

  /* ----------------- FORM ----------------- */
  .cl-label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
  }

  .cl-input {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #DDD;
    background: #FFFFFF;
    outline: none;
    transition: all 0.2s ease-in-out;
    box-sizing: border-box;
    /* evita que se salga */
  }

  .cl-input:focus {
    border-color: #E7473C;
    box-shadow: 0 0 5px rgba(231, 71, 60, 0.3);
  }

  /* ----------------- BOTONES ----------------- */
  .cl-btn {
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
  }

  .cl-btn--primary,
  .cl-btn--update {
    background-color: #E7473C;
    color: #FFFFFF;
  }

  .cl-btn--primary:hover,
  .cl-btn--update:hover {
    background-color: #c93b33;
  }

  .cl-btn--back {
    background-color: #111111;
    color: #FFFFFF;
    text-decoration: none;
    display: inline-block;
    margin-bottom: 15px;
  }

  .cl-btn--back:hover {
    background-color: #000000;
  }

  /* ----------------- MENSAJES ----------------- */
  .note {
    padding: 12px;
    border-radius: 6px;
    margin: 10px auto;
    font-weight: 600;
    max-width: 1000px;
    text-align: center;
  }

  .note.err {
    background-color: #FFE6E4;
    border: 1px solid #E7473C;
    color: #E7473C;
  }

  .note.ok {
    background-color: #FFFFFF;
    border: 1px solid #111111;
    color: #111111;
  }

  /* ----------------- PASSWORD PERFECTO ----------------- */

  /*
  Esta versión corrige TODO:
  ✅ Input y botón en la misma fila
  ✅ Botón NO rompe la estructura
  ✅ Input NO se sale
  ✅ Todo alineado centrado
*/

  .pw-row {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  /* Fila: input + botón */
  .pw-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  /* Input ocupa todo el espacio sin romper */
  .pw-wrap input.cl-input {
    flex: 1;
    min-width: 0;
    /* evita que empuje el botón */
    box-sizing: border-box;
  }

  /* Botón mostrar/ocultar */
  .pw-toggle {
    padding: 10px 15px;
    border-radius: 6px;
    border: 1px solid #DDD;
    background: #F0F0F0;
    cursor: pointer;
    white-space: nowrap;
    /* evita que se baje */
  }

  /* ----------------- TEXTO MUTED ----------------- */
  .muted {
    font-size: 0.85rem;
    color: #666;
  }

  /* ----------------- RESPONSIVE ----------------- */
  @media (max-width: 900px) {
    .page-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<body class="cl-page cl-page--editar">
  <?php include '../sidebar.php'; ?>

  <header class="header cl-header">
    <h1 class="titulo cl-title">Editar Cliente #<?php echo (int)$id; ?></h1>
  </header>

  <div class="cl-actions-top">
    <a href="./lista.php" class="cl-btn cl-btn--back">Volver</a>
  </div>

  <?php if ($okMsg): ?><div class="note ok" id="msg-ok"><?php echo htmlspecialchars($okMsg, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
  <?php if ($okPass): ?><div class="note ok" id="msg-pass"><?php echo htmlspecialchars($okPass, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
  <?php if ($errores) {
    foreach ($errores as $e) { ?><div class="note err" id="msg"><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></div><?php }
                                                                                                                          } ?>

  <main class="cl-container">
    <div class="page-grid">

      <!-- Columna izquierda: Datos -->
      <section class="card">
        <h2>Datos del cliente</h2>
        <p class="muted">Edita nombre, correo y teléfono. Los campos vacíos en correo/teléfono se guardan como NULL.</p>

        <form method="POST" autocomplete="off" novalidate>
          <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="accion" value="datos">

          <div class="form-grid">
            <div>
              <label class="cl-label">Nombre *</label>
              <input class="cl-input" type="text" name="nombre" required
                value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div>
              <label class="cl-label">Apellido</label>
              <input class="cl-input" type="text" name="apellido"
                value="<?php echo htmlspecialchars($apellido, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div>
              <label class="cl-label">Correo</label>
              <input class="cl-input" type="email" name="correo"
                value="<?php echo htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div>
              <label class="cl-label">Teléfono</label>
              <input class="cl-input" type="tel" name="telefono"
                value="<?php echo htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
          </div>

          <div class="actions">
            <button class="cl-btn cl-btn--primary" type="submit">Guardar cambios</button>
          </div>
        </form>
      </section>

      <!-- Columna derecha: Seguridad -->
      <section class="card">
        <h2>Seguridad</h2>
        <p class="muted">Resetea la contraseña del usuario con el mismo correo del cliente en <code>dbo.usuarios</code>.</p>

        <form method="POST" autocomplete="off" novalidate>
          <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="accion" value="pass">
          <input type="hidden" name="correo_ref" value="<?php echo htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'); ?>">

          <div class="pw-row">
            <div class="pw-wrap">
              <div>
                <label class="cl-label">Nueva contraseña (mín. 8)</label>
                <input id="pw1" class="cl-input" type="password" name="newpass" minlength="8" required>
              </div>
              <button type="button" class="pw-toggle" data-target="pw1">Mostrar</button>
            </div>

            <div class="pw-wrap">
              <div>
                <label class="cl-label">Repetir contraseña</label>
                <input id="pw2" class="cl-input" type="password" name="reppass" minlength="8" required>
              </div>
              <button type="button" class="pw-toggle" data-target="pw2">Mostrar</button>
            </div>
          </div>

          <div class="actions">
            <button class="cl-btn cl-btn--update" type="submit">Resetear contraseña</button>
          </div>
        </form>
      </section>

    </div>
  </main>

  <script>
    // Ocultar mensajes tras 3s
    for (const id of ['msg', 'msg-ok', 'msg-pass']) {
      const el = document.getElementById(id);
      if (el) setTimeout(() => el.style.display = 'none', 3000);
    }

    // Toggle de visibilidad de password (sin íconos)
    document.querySelectorAll('.pw-toggle').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-target');
        const input = document.getElementById(id);
        if (!input) return;
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        btn.textContent = isPass ? 'Ocultar' : 'Mostrar';
      });
    });
  </script>
</body>

</html>
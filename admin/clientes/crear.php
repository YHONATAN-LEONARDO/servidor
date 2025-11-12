<?php
include '../../app/config/session.php';
include '../../app/config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

$errores = [];
$nombre = $apellido = $correo = $telefono = $pass = $confirmar = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'], $token)) {
    $errores[] = 'Token inválido, recarga la página.';
  } else {
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellido  = trim($_POST['apellido'] ?? '');
    $correo    = trim($_POST['correo'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $pass      = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    // Validaciones
    if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
    if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL))
      $errores[] = 'Debes ingresar un correo válido.';
    if ($pass === '' || strlen($pass) < 8)
      $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    if ($pass !== $confirmar)
      $errores[] = 'Las contraseñas no coinciden.';

    // Verificar duplicados por correo en ambas tablas
    if (empty($errores)) {
      $chkU = sqlsrv_query($conn, "SELECT 1 FROM dbo.usuarios WHERE correo = ?", [$correo]);
      if ($chkU && sqlsrv_fetch($chkU) === true) $errores[] = 'El correo ya existe en usuarios.';
      if ($chkU) sqlsrv_free_stmt($chkU);

      $chkC = sqlsrv_query($conn, "SELECT 1 FROM dbo.clientes WHERE correo = ?", [$correo]);
      if ($chkC && sqlsrv_fetch($chkC) === true) $errores[] = 'El correo ya existe en clientes.';
      if ($chkC) sqlsrv_free_stmt($chkC);
    }

    if (empty($errores)) {
      // Transacción: crear usuario (login) y cliente (ficha)
      if (!sqlsrv_begin_transaction($conn)) {
        $errores[] = 'No se pudo iniciar la transacción.';
      } else {
        try {
          // 1) Crear usuario con rol cliente
          $hash = password_hash($pass, PASSWORD_DEFAULT);
          $sqlUser = "INSERT INTO dbo.usuarios (nombre, correo, password_hash, rol)
                                VALUES (?, ?, ?, 'cliente')";
          $okUser = sqlsrv_query($conn, $sqlUser, [$nombre, $correo, $hash]);
          if ($okUser === false) {
            throw new Exception('Error al crear el usuario.');
          }
          if ($okUser) sqlsrv_free_stmt($okUser);

          // 2) Crear cliente (sin contraseña)
          $sqlCli = "INSERT INTO dbo.clientes (nombre, apellido, correo, telefono)
                               VALUES (?, ?, ?, ?)";
          $okCli = sqlsrv_query($conn, $sqlCli, [
            $nombre,
            $apellido !== '' ? $apellido : null,
            $correo,
            $telefono !== '' ? $telefono : null
          ]);
          if ($okCli === false) {
            throw new Exception('Error al crear el cliente.');
          }
          if ($okCli) sqlsrv_free_stmt($okCli);

          // Commit
          if (!sqlsrv_commit($conn)) {
            throw new Exception('No se pudo confirmar la transacción.');
          }

          // Redirigir (PRG)
          header('Location: ./lista.php?mensaje=1');
          exit;
        } catch (Throwable $e) {
          sqlsrv_rollback($conn);
          // Para depurar, puedes descomentar la línea de abajo temporalmente:
          // $errores[] = print_r(sqlsrv_errors(), true);
          $errores[] = $e->getMessage();
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

  <title>Nuevo Cliente</title>

  <style>
    /* ----------------- GENERAL ----------------- */
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #F0F0F0;
      /* Humo blanco */
      color: #111111;
      /* Negro */
      margin: 0;
      padding: 0;
    }

    .wrap {
      max-width: 600px;
      margin: 40px auto;
      padding: 20px;
    }

    /* ----------------- CARD ----------------- */
    .card {
      background-color: #FFFFFF;
      /* Blanco */
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .card h1 {
      margin-top: 0;
      color: #E7473C;
      /* Rojo */
    }

    /* ----------------- FORM ----------------- */
    .card form div {
      margin-bottom: 15px;
    }

    .card label {
      font-weight: 600;
      display: block;
      margin-bottom: 5px;
      color: #111111;
    }

    .card input[type="text"],
    .card input[type="email"],
    .card input[type="tel"],
    .card input[type="password"] {
      width: 100%;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #DDD;
      /* Gris claro */
      background: #FFFFFF;
      outline: none;
      box-sizing: border-box;
      transition: all 0.2s ease-in-out;
    }

    .card input:focus {
      border-color: #E7473C;
      box-shadow: 0 0 5px rgba(231, 71, 60, 0.3);
    }

    /* ----------------- BOTONES ----------------- */
    .btn {
      padding: 10px 15px;
      border-radius: 6px;
      font-weight: bold;
      border: none;
      cursor: pointer;
      transition: 0.2s ease;
      text-decoration: none;
      display: inline-block;
    }

    /* Atrás */
    .btn-back {
      background: #111111;
      color: #FFFFFF;
      margin-bottom: 20px;
    }

    .btn-back:hover {
      background: #000000;
    }

    /* Crear cliente */
    .btn-primary {
      background-color: #E7473C;
      color: #FFFFFF;
      width: 100%;
    }

    .btn-primary:hover {
      background-color: #c93b33;
    }

    /* ----------------- MENSAJES ----------------- */
    .error {
      background-color: #FFE6E4;
      /* Rosa suave */
      border: 1px solid #E7473C;
      color: #E7473C;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 10px;
      font-weight: 600;
      text-align: center;
    }

    /* ----------------- ACTIONS ----------------- */
    .actions {
      margin-top: 20px;
    }
  </style>
</head>

<body>
  <?php include '../sidebar.php'; ?>

  <div class="wrap">
    <div class="card">
      <h1>Registrar nuevo cliente</h1>

      <?php if ($errores) foreach ($errores as $e): ?>
        <div class="error"><?= htmlspecialchars($e) ?></div>
      <?php endforeach; ?>

      <form method="POST" autocomplete="off" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">

        <div>
          <label>Nombre *</label>
          <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
        </div>

        <div>
          <label>Apellido</label>
          <input type="text" name="apellido" value="<?= htmlspecialchars($apellido) ?>">
        </div>

        <div>
          <label>Correo *</label>
          <input type="email" name="correo" value="<?= htmlspecialchars($correo) ?>" required>
        </div>

        <div>
          <label>Teléfono</label>
          <input type="tel" name="telefono" value="<?= htmlspecialchars($telefono) ?>">
        </div>

        <div>
          <label>Contraseña (mín. 8) *</label>
          <input type="password" name="password" required minlength="8">
        </div>

        <div>
          <label>Confirmar contraseña *</label>
          <input type="password" name="confirmar" required minlength="8">
        </div>

        <div class="actions">
          <button type="submit" class="btn btn-primary">Crear cliente</button>
        </div>
      </form>
    </div>
  </div>
</body>

</html>
<?php
include '../../app/config/session.php';
include '../../app/config/database.php';

// --- Eliminar proveedor si se envió una acción GET ---
if (isset($_GET['eliminar'])) {
    $idEliminar = (int)$_GET['eliminar'];
    if ($idEliminar > 0) {
        $sqlDelete = "DELETE FROM proveedores WHERE id = ?";
        sqlsrv_query($conn, $sqlDelete, [$idEliminar]);
        header('Location: ./l-proveedor.php');
        exit;
    }
}

// --- Consultar todos los proveedores ---
$sql = "SELECT id, nombre, telefono, correo, direccion, creado_en, actualizado_en 
        FROM proveedores 
        ORDER BY nombre ASC";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de Proveedores</title>

</head>
<style>
    /* ----------------- BODY ----------------- */
    body {
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
        background-color: #F0F0F0;
        /* Fondo humo */
        color: #111;
        /* Texto principal */
    }

    /* ----------------- TITULO ----------------- */
    h1 {
        text-align: center;
        color: #E7473C;
        /* Rojo brillante */
        margin: 20px 0;
    }

    /* ----------------- ACCIONES ----------------- */
    .acciones {
        max-width: 1000px;
        margin: 0 auto 20px auto;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .acciones a {
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 5px;
        font-weight: bold;
        color: #FFF;
        background-color: #E7473C;
        /* Rojo brillante */
        transition: all 0.2s;
    }

    .acciones a:hover {
        background-color: #FFE6E4;
        /* Rojo muy claro / rosa suave */
        color: #111;
    }

    /* ----------------- TABLA ----------------- */
    table {
        width: 100%;
        max-width: 1000px;
        margin: 0 auto 30px auto;
        border-collapse: collapse;
        background-color: #FFF;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    th,
    td {
        padding: 10px 12px;
        border-bottom: 1px solid #DDD;
        text-align: left;
    }

    th {
        background-color: #E7473C;
        /* Rojo brillante */
        color: #FFF;
        font-weight: bold;
    }

    tbody tr:hover {
        background-color: #FFE6E4;
        /* Rojo muy claro / rosa suave */
    }

    /* ----------------- BOTONES ----------------- */
    .btn {
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        text-decoration: none;
        color: #FFF;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        margin-right: 5px;
    }

    .btn.editar {
        background-color: #E7473C;
    }

    .btn.eliminar {
        background-color: #E7473C;
    }

    .btn:hover {
        background-color: #FFE6E4;
        color: #111;
    }

    /* ----------------- ALERTAS / MENSAJES ----------------- */
    .alerta {
        max-width: 800px;
        margin: 10px auto;
        padding: 10px 15px;
        border-radius: 5px;
        font-weight: bold;
        text-align: center;
        background-color: #FFE6E4;
        /* Rojo muy claro / rosa suave */
        border-left: 4px solid #E7473C;
        color: #111;
    }
</style>

<body>
    <?php include '../sidebar.php'; ?>

    <h1>Lista de Proveedores</h1>

    <div class="acciones">
        <a href="./crear_proveedor.php">Crear Proveedor</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Dirección</th>
                <th>Creado</th>
                <th>Actualizado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= htmlspecialchars($row['nombre']); ?></td>
                    <td><?= htmlspecialchars($row['telefono']); ?></td>
                    <td><?= htmlspecialchars($row['correo']); ?></td>
                    <td><?= htmlspecialchars($row['direccion']); ?></td>
                    <td><?= $row['creado_en'] ? $row['creado_en']->format('Y-m-d H:i') : ''; ?></td>
                    <td><?= $row['actualizado_en'] ? $row['actualizado_en']->format('Y-m-d H:i') : ''; ?></td>
                    <td>
                        <a class="btn editar" href="editar.php?id=<?= $row['id']; ?>">Actualizar</a>
                        <!-- <a class="btn eliminar" href="?eliminar=<?= $row['id']; ?>">Eliminar</a> -->
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</body>

</html>
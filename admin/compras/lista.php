<?php
include '../../app/config/session.php';
include '../../app/config/database.php';

// ELIMINAR COMPRA
if (isset($_POST['eliminar_id'])) {
    $compra_id = (int)$_POST['eliminar_id'];

    // Primero eliminar detalles de compra
    $sqlDelDetalle = "DELETE FROM compras_detalle WHERE compra_id = ?";
    sqlsrv_query($conn, $sqlDelDetalle, [$compra_id]);

    // Luego eliminar la compra
    $sqlDelCompra = "DELETE FROM compras WHERE id = ?";
    sqlsrv_query($conn, $sqlDelCompra, [$compra_id]);

    echo "<div class='alerta'>Compra eliminada correctamente</div>";
}

// Consulta de todas las compras con cantidad total de ropa
$sql = "
SELECT 
    c.id,
    c.proveedor,
    c.numero_factura,
    c.fecha_compra,
    c.total,
    c.observacion,
    c.creado_en,
    COUNT(cd.id) AS cantidad_productos,
    SUM(cd.cantidad) AS cantidad_ropa
FROM compras c
LEFT JOIN compras_detalle cd ON cd.compra_id = c.id
GROUP BY c.id, c.proveedor, c.numero_factura, c.fecha_compra, c.total, c.observacion, c.creado_en
ORDER BY c.fecha_compra DESC;
";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Compras</title>
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
    }

    /* ----------------- HEADER ----------------- */
    h1 {
        text-align: center;
        color: #E7473C;
        /* Rojo brillante */
        margin: 2/* ----------------- RESET Y BODY ----------------- */
body {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #F0F0F0; /* Fondo humo */
    color: #111; /* Texto principal */
}

/* ----------------- TITULO ----------------- */
h1 {
    text-align: center;
    color: #E7473C; /* Rojo brillante */
    margin: 20px 0;
}

/* ----------------- ALERTAS ----------------- */
.ok {
    max-width: 600px;
    margin: 10px auto;
    padding: 10px 15px;
    border-radius: 5px;
    font-weight: bold;
    text-align: center;
    background-color: #FFE6E4; /* Rojo muy claro */
    border-left: 4px solid #E7473C;
    color: #111;
}

.alert {
    max-width: 600px;
    margin: 10px auto;
    padding: 10px 15px;
    border-radius: 5px;
    background-color: #FFE6E4; /* Rojo muy claro */
    border-left: 4px solid #E7473C;
    color: #111;
}

/* ----------------- BOTON VOLVER ----------------- */
.btn-volver {
    display: inline-block;
    margin: 10px 0 20px 20px;
    padding: 8px 12px;
    border-radius: 5px;
    background-color: #E7473C;
    color: #FFF;
    text-decoration: none;
    font-weight: bold;
    transition: all 0.2s;
}

.btn-volver:hover {
    background-color: #FFE6E4;
    color: #111;
}

/* ----------------- FORMULARIO ----------------- */
form {
    max-width: 600px;
    margin: 0 auto 30px auto; /* Centrado con margen */
    padding: 20px;
    background-color: #FFF;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

/* Labels */
form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

/* Inputs y Select */
form input[type="text"],
form input[type="email"],
form input[type="number"],
form input[type="date"],
form select,
form textarea {
    width: calc(100% - 20px);
    padding: 8px 10px;
    margin-bottom: 15px;
    border: 1px solid #CCC;
    border-radius: 5px;
    font-size: 14px;
}

/* Textarea */
form textarea {
    resize: vertical;
}

/* Boton submit */
form button {
    background-color: #E7473C;
    color: #FFF;
    font-weight: bold;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.2s;
}

form button:hover {
    background-color: #FFE6E4;
    color: #111;
}
0px 0;
    }

    /* ----------------- ACCIONES ARRIBA ----------------- */
    .acciones {
        max-width: 1000px;
        margin: 0 auto 20px auto;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .acciones a {
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 5px;
        font-weight: bold;
        color: #FFFFFF;
        /* Blanco puro */
        background-color: #E7473C;
        /* Rojo brillante */
        transition: all 0.2s;
    }

    .acciones a:hover {
        background-color: #FFE6E4;
        /* Rojo muy claro / rosa suave */
        color: #111111;
    }

    /* ----------------- TABLA ----------------- */
    table {
        width: 100%;
        max-width: 1000px;
        margin: 0 auto 30px auto;
        border-collapse: collapse;
        background-color: #FFFFFF;
        /* Blanco puro */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        /* Sombra negra ligera */
        border-radius: 8px;
        overflow: hidden;
    }

    th,
    td {
        padding: 10px 12px;
        border-bottom: 1px solid #DDD;
        /* Gris claro */
        text-align: left;
    }

    th {
        background-color: #E7473C;
        /* Rojo brillante */
        color: #FFFFFF;
        /* Blanco puro */
        font-weight: bold;
    }

    tbody tr:hover {
        background-color: #FFE6E4;
        /* Rojo muy claro / rosa suave */
    }

    .total {
        font-weight: bold;
    }

    /* ----------------- BOTONES DE ACCIONES EN TABLA ----------------- */
    .btn-accion {
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        text-decoration: none;
        color: #FFFFFF;
        /* Blanco puro */
        background-color: #E7473C;
        /* Rojo brillante */
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        margin-right: 5px;
    }

    .btn-accion:hover {
        background-color: #FFE6E4;
        /* Rojo muy claro / rosa suave */
        color: #111111;
    }

    .btn-editar {
        background-color: #E7473C;
    }

    .btn-eliminar {
        background-color: #E7473C;
    }

    /* ----------------- ALERTAS ----------------- */
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
        /* Rojo brillante */
        color: #111111;
    }
</style>

<body>
    <?php include '../sidebar.php'; ?>

    <h1>Registro de Compras</h1>

    <div class="acciones">
        <a href="./l-proveedor.php">Lista de Proveedor</a>
        <a href="./ingresar_factura.php">Ingresar Factura de Compra</a>
        <!-- <a href="reporte_compras.php">Generar Reporte de Compras (PDF)</a> -->
    </div>

    <table>
        <thead>
            <tr>
                <th>Proveedor</th>
                <th>N° Factura</th>
                <th>Fecha Compra</th>
                <th>Productos Distintos</th>
                <th>Cantidad Total de Ropa</th>
                <th>Total (Bs)</th>
                <th>Observación</th>
                <th>Registrado En</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['proveedor']); ?></td>
                    <td><?= htmlspecialchars($row['numero_factura']); ?></td>
                    <td><?= $row['fecha_compra'] ? $row['fecha_compra']->format('Y-m-d') : ''; ?></td>
                    <td><?= (int)$row['cantidad_productos']; ?></td>
                    <td><?= (int)$row['cantidad_ropa']; ?> unidades</td>
                    <td class="total"><?= number_format($row['total'], 2); ?></td>
                    <td><?= htmlspecialchars($row['observacion']); ?></td>
                    <td><?= $row['creado_en'] ? $row['creado_en']->format('Y-m-d H:i') : ''; ?></td>
                    <td>
                        <a class="btn-accion btn-editar" href="editar-fa.php?id=<?= $row['id']; ?>">Actualizar</a>
                        <!-- <form method="POST" onsubmit="return confirm('¿Seguro que desea eliminar esta compra?');">
                            <input type="hidden" name="eliminar_id" value="<?= $row['id']; ?>">
                            <button type="submit" class="btn-accion btn-eliminar">Eliminar</button>
                        </form> -->
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>

</html>
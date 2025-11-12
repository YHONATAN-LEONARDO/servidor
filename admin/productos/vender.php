<?php
include '../../app/config/database.php';
include '../../app/config/session.php';

// Validar ID de producto
$id_producto = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_producto <= 0) {
    die("<h2 style='text-align:center;color:red;'>Producto inválido.</h2>");
}

// Obtener producto
$sql = "
    SELECT 
        p.id,
        p.titulo,
        p.imagen,
        p.precio,
        p.cantidad,
        p.categoria,
        p.talla,
        p.genero,
        p.color,
        p.creado_en,
        v.nombre   AS nombre_vendedor,
        v.apellido AS apellido_vendedor
    FROM productos p
    INNER JOIN vendedor v ON p.vendedor = v.id
    WHERE p.id = ?
";

$stmt = sqlsrv_query($conn, $sql, [$id_producto]);

if ($stmt === false) {
    die("<h2 style='text-align:center;color:red;'>Error al consultar producto: " . print_r(sqlsrv_errors(), true) . "</h2>");
}

$producto = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$producto) {
    die("<h2 style='text-align:center;color:red;'>Producto no encontrado.</h2>");
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Vender Producto</title>
    <!-- <link rel="stylesheet" href="/admin/panel.css"> -->

</head>

<body>
    <style>
        /* ----------------- GENERAL ----------------- */
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #F0F0F0;
            /* Fondo humo */
            color: #111111;
            /* Texto principal */
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            gap: 40px;
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        margin-top: 150px;

        }

        /* ----------------- FORMULARIO ----------------- */
        form {
            background-color: #FFFFFF;
            /* Blanco puro */
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        form label {
            font-weight: 600;
            margin-bottom: 5px;
        }

        form input[type="text"],
        form input[type="number"],
        form select {
            padding: 10px;
            border: 1px solid #DDD;
            /* Gris claro */
            border-radius: 6px;
            outline: none;
            width: 100%;
            transition: all 0.2s;
        }

        form input:focus,
        form select:focus {
            border-color: #E7473C;
            /* Rojo brillante */
            box-shadow: 0 0 5px rgba(231, 71, 60, 0.3);
        }

        form button {
            padding: 12px;
            background-color: #E7473C;
            /* Rojo brillante */
            color: #FFFFFF;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
        }

        form button:hover {
            background-color: #FF3B30;
            /* Rojo un poco más intenso */
        }

        /* ----------------- INFORMACIÓN DEL PRODUCTO ----------------- */
        .producto-info {
            background-color: #FFFFFF;
            /* Blanco puro */
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .producto-info img {
            max-width: 100%;
            border-radius: 10px;
            object-fit: contain;
        }

        .producto-info h2 {
            color: #E7473C;
            /* Rojo brillante */
            margin: 10px 0 5px;
        }

        .producto-info p {
            margin: 5px 0;
            font-weight: 500;
        }

        /* ----------------- RESPONSIVE ----------------- */
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
                padding: 20px;
            }
        }

        /* ----------------- INPUTS DESHABILITADOS ----------------- */
        input[disabled] {
            background-color: #F0F0F0;
            color: #111111;
        }

        /* ----------------- HOVER SUAVE ----------------- */
        form input[type="text"]:hover,
        form input[type="number"]:hover,
        form select:hover {
            border-color: #E7473C;
        }
    </style>

    <?php include '../sidebar.php'; ?>

    <div class="container">
        <!-- Formulario de venta -->
        <form action="factura-v.php" method="POST">
            <input type="hidden" name="id_producto" value="<?= $producto['id'] ?>">

            <label>Producto</label>
            <input type="text" value="<?= htmlspecialchars($producto['titulo']) ?>" disabled>

            <label>Precio Unitario (Bs)</label>
            <input type="text" value="<?= number_format($producto['precio'], 2) ?>" disabled>

            <label>Cantidad a vender</label>
            <input type="number" name="cantidad" min="1" max="<?= $producto['cantidad'] ?>" value="1" required>

            <label>Nombre del Cliente</label>
            <input type="text" name="cliente_nombre" placeholder="Ej: Juan Pérez" required>

            <label>Método de Pago / Tarjeta</label>
            <select name="cliente_tarjeta" required>
                <option value="Efectivo">Efectivo</option>
                <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                <option value="Otro">Otro</option>
            </select>

            <label>Observaciones (opcional)</label>
            <input type="text" name="observaciones" placeholder="Comentarios adicionales">

            <button type="submit">Generar Factura PDF</button>

        </form>

        <!-- Información del producto -->
        <div class="producto-info">
            <img src='../../imagenes/<?= $producto['imagen'] ?>' alt='<?= $producto['titulo'] ?>'>




            <h2><?= htmlspecialchars($producto['titulo']) ?></h2>
            <p><strong>Categoría:</strong> <?= htmlspecialchars($producto['categoria']) ?></p>
            <p><strong>Talla:</strong> <?= htmlspecialchars($producto['talla']) ?></p>
            <p><strong>Género:</strong> <?= htmlspecialchars($producto['genero']) ?></p>
            <p><strong>Color:</strong> <?= htmlspecialchars($producto['color']) ?></p>
            <p><strong>Vendedor:</strong> <?= htmlspecialchars($producto['nombre_vendedor'] . ' ' . $producto['apellido_vendedor']) ?></p>
            <p><strong>Stock disponible:</strong> <?= (int)$producto['cantidad'] ?></p>
            <p><strong>Precio:</strong> Bs <?= number_format($producto['precio'], 2) ?></p>
        </div>
    </div>

</body>

</html>
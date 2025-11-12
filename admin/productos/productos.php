<?php
include '../../app/config/database.php';
include '../../app/config/session.php';

$mensaje = $_GET['mensaje'] ?? null;

/* ------------------ LEE FILTROS ------------------ */
$q            = trim($_GET['q'] ?? '');
$categoria    = trim($_GET['categoria'] ?? '');
$talla        = trim($_GET['talla'] ?? '');
$genero       = trim($_GET['genero'] ?? '');
$color        = trim($_GET['color'] ?? '');
$vendedor_id  = isset($_GET['vendedor']) && $_GET['vendedor'] !== '' ? (int)$_GET['vendedor'] : null;

$precio_min   = $_GET['pmin'] ?? '';
$precio_max   = $_GET['pmax'] ?? '';
$cant_min     = $_GET['cmin'] ?? '';
$cant_max     = $_GET['cmax'] ?? '';

$fec_desde    = trim($_GET['fdesde'] ?? ''); // YYYY-MM-DD
$fec_hasta    = trim($_GET['fhasta'] ?? ''); // YYYY-MM-DD

// Normaliza rangos numéricos
if ($precio_min !== '' && $precio_max !== '' && (float)$precio_min > (float)$precio_max) {
    [$precio_min, $precio_max] = [$precio_max, $precio_min];
}
if ($cant_min !== '' && $cant_max !== '' && (int)$cant_min > (int)$cant_max) {
    [$cant_min, $cant_max] = [$cant_max, $cant_min];
}
// Normaliza rango de fechas
if ($fec_desde !== '' && $fec_hasta !== '' && strtotime($fec_desde) > strtotime($fec_hasta)) {
    [$fec_desde, $fec_hasta] = [$fec_hasta, $fec_desde];
}

/* ------------------ ELIMINAR PRODUCTO ------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Obtener imagen antes de eliminar
    $sqlImg = "SELECT imagen FROM productos WHERE id = ?";
    $stmtImg = sqlsrv_query($conn, $sqlImg, [$id]);
    $producto = $stmtImg ? sqlsrv_fetch_array($stmtImg, SQLSRV_FETCH_ASSOC) : null;
    if ($stmtImg) sqlsrv_free_stmt($stmtImg);

    if ($producto && !empty($producto['imagen'])) {
        $ruta = "../../imagenes/" . $producto['imagen'];
        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }

    // Eliminar
    $sqlDel = "DELETE FROM productos WHERE id = ?";
    $stmtDel = sqlsrv_query($conn, $sqlDel, [$id]);

    if ($stmtDel) {
        header("Location: ./productos.php?mensaje=3");
        exit;
    } else {
        echo "<div class='alerta error'>Error al eliminar el producto.</div>";
    }
}

/* ------------------ SELECT VENDEDORES PARA FILTRO ------------------ */
$vend_rs = sqlsrv_query($conn, "SELECT id, nombre, apellido FROM vendedor ORDER BY nombre, apellido");
$vendors = [];
if ($vend_rs) {
    while ($v = sqlsrv_fetch_array($vend_rs, SQLSRV_FETCH_ASSOC)) $vendors[] = $v;
    sqlsrv_free_stmt($vend_rs);
}

/* ------------------ ARMAR WHERE ------------------ */
$where  = [];
$params = [];

if ($q !== '') {
    $where[] = "(p.titulo LIKE ? OR p.categoria LIKE ? OR p.talla LIKE ? OR p.genero LIKE ? OR p.color LIKE ? OR v.nombre LIKE ? OR v.apellido LIKE ?)";
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like, $like, $like);
}
if ($categoria !== '') {
    $where[] = "p.categoria = ?";
    $params[] = $categoria;
}
if ($talla     !== '') {
    $where[] = "p.talla = ?";
    $params[] = $talla;
}
if ($genero    !== '') {
    $where[] = "p.genero = ?";
    $params[] = $genero;
}
if ($color     !== '') {
    $where[] = "p.color LIKE ?";
    $params[] = '%' . $color . '%';
}
if (!is_null($vendedor_id)) {
    $where[] = "p.vendedor = ?";
    $params[] = $vendedor_id;
}

if ($precio_min !== '') {
    $where[] = "p.precio >= ?";
    $params[] = (float)$precio_min;
}
if ($precio_max !== '') {
    $where[] = "p.precio <= ?";
    $params[] = (float)$precio_max;
}
if ($cant_min   !== '') {
    $where[] = "p.cantidad >= ?";
    $params[] = (int)$cant_min;
}
if ($cant_max   !== '') {
    $where[] = "p.cantidad <= ?";
    $params[] = (int)$cant_max;
}

if ($fec_desde !== '') {
    $where[] = "CONVERT(date, p.creado_en) >= CONVERT(date, ?)";
    $params[] = $fec_desde;
}
if ($fec_hasta !== '') {
    $where[] = "CONVERT(date, p.creado_en) <= CONVERT(date, ?)";
    $params[] = $fec_hasta;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

/* ------------------ CONSULTA LISTA ------------------ */
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
    $whereSql
    ORDER BY p.id DESC
";
$resultado = sqlsrv_query($conn, $sql, $params);
if ($resultado === false) {
    die('Error al consultar productos: ' . print_r(sqlsrv_errors(), true));
}

function fmt_num($n)
{
    return number_format((float)$n, 2, '.', '');
}
function fmt_fecha($f)
{
    if ($f instanceof DateTime) return $f->format('Y-m-d');
    if (is_array($f) && isset($f['date'])) return date('Y-m-d', strtotime($f['date']));
    return htmlspecialchars((string)$f);
}
function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Productos</title>
    <!-- <link rel="stylesheet" href="/admin/panel.css"> -->
    <link rel="stylesheet" href="../styles.css">

    <!-- CSS SOLO DE LO NUEVO (filtros adicionales) -->

</head>

<body class="pr-page pr-page--productos">
    <?php include '../sidebar.php'; ?>

    <header class="header pr-header">
        <h1 class="titulo pr-title">Registros de Ropa</h1>
    </header>

    <?php if ($mensaje == 1) { ?>
        <div class="alerta exito pr-alert pr-alert--success" id="mensaje">Se creó correctamente</div>
    <?php } elseif ($mensaje == 2) { ?>
        <div class="alerta exito pr-alert pr-alert--updated" id="mensaje">Producto actualizado correctamente</div>
    <?php } elseif ($mensaje == 3) { ?>
        <div class="alerta exito pr-alert pr-alert--deleted" id="mensaje">Producto eliminado correctamente</div>
    <?php } ?>

    <div class="pr-actions">
        <a href="../index.php">
        </a>
        <a href="./crear.php">
            <div class="crear pr-btn pr-btn--new">Nuevo Producto</div>
        </a>
    </div>

    <!-- BÚSQUEDA + FILTROS -->
    <form class="pr-filters" method="get" autocomplete="off">
        <div class="group" style="flex:1 1 220px">
            <label>Buscar</label>
            <input type="search" name="q" placeholder="Título, categoría, vendedor, color, talla o género…" value="<?= h($q) ?>">
        </div>

        <div class="group">
            <label>Categoría</label>
            <select name="categoria" class="md">
                <option value="">(todas)</option>
                <?php foreach (['camisa', 'pantalon', 'abrigo', 'falda', 'vestido'] as $opt): ?>
                    <option value="<?= h($opt) ?>" <?= $categoria === $opt ? 'selected' : '' ?>><?= h(ucfirst($opt)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="group">
            <label>Talla</label>
            <select name="talla" class="shrt">
                <option value="">(todas)</option>
                <?php foreach (['S', 'M', 'L', 'XL'] as $opt): ?>
                    <option value="<?= h($opt) ?>" <?= $talla === $opt ? 'selected' : '' ?>><?= h($opt) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="group">
            <label>Género</label>
            <select name="genero" class="md">
                <option value="">(todos)</option>
                <option value="hombre" <?= $genero === 'hombre' ? 'selected' : '' ?>>Hombre</option>
                <option value="mujer" <?= $genero === 'mujer' ? 'selected' : '' ?>>Mujer</option>
            </select>
        </div>

        <div class="group">
            <label>Color</label>
            <input type="text" name="color" class="md" placeholder="Ej: negro" value="<?= h($color) ?>">
        </div>

        <div class="group">
            <label>Vendedor</label>
            <select name="vendedor" class="md">
                <option value="">(todos)</option>
                <?php foreach ($vendors as $v):
                    $vid = (int)$v['id'];
                    $vn  = trim(($v['nombre'] ?? '') . ' ' . ($v['apellido'] ?? '')); ?>
                    <option value="<?= $vid ?>" <?= $vendedor_id === $vid ? 'selected' : '' ?>><?= h($vn ?: ('#' . $vid)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="group">
            <label>Precio mín</label>
            <input type="number" step="0.01" name="pmin" class="shrt" value="<?= h($precio_min) ?>">
        </div>
        <div class="group">
            <label>Precio máx</label>
            <input type="number" step="0.01" name="pmax" class="shrt" value="<?= h($precio_max) ?>">
        </div>

        <div class="group">
            <label>Cant. mín</label>
            <input type="number" name="cmin" class="shrt" value="<?= h($cant_min) ?>">
        </div>
        <div class="group">
            <label>Cant. máx</label>
            <input type="number" name="cmax" class="shrt" value="<?= h($cant_max) ?>">
        </div>

        <div class="group">
            <label>Desde</label>
            <input type="date" name="fdesde" class="md" value="<?= h($fec_desde) ?>">
        </div>
        <div class="group">
            <label>Hasta</label>
            <input type="date" name="fhasta" class="md" value="<?= h($fec_hasta) ?>">
        </div>

        <button class="pr-btn" type="submit">Aplicar</button>
        <?php if ($q !== '' || $categoria !== '' || $talla !== '' || $genero !== '' || $color !== '' || !is_null($vendedor_id) || $precio_min !== '' || $precio_max !== '' || $cant_min !== '' || $cant_max !== '' || $fec_desde !== '' || $fec_hasta !== ''): ?>
            <a class="pr-btn" href="./productos.php">Limpiar</a>
        <?php endif; ?>
    </form>

    <div class="table-wrap pr-table-wrap">
        <table class="pr-table">
            <thead class="pr-table__head">
                <tr class="pr-table__head-row">
                    <!-- SIN ID -->
                    <th class="pr-table__th pr-table__th--titulo">Título</th>
                    <th class="pr-table__th pr-table__th--imagen">Imagen</th>
                    <th class="pr-table__th pr-table__th--precio">Precio</th>
                    <th class="pr-table__th pr-table__th--cantidad">Cantidad</th>
                    <th class="pr-table__th pr-table__th--categoria">Categoría</th>
                    <th class="pr-table__th pr-table__th--talla">Talla</th>
                    <th class="pr-table__th pr-table__th--genero">Género</th>
                    <th class="pr-table__th pr-table__th--color">Color</th>
                    <th class="pr-table__th pr-table__th--vendedor">Vendedor</th>
                    <th class="pr-table__th pr-table__th--creado">Creado</th>
                    <th class="pr-table__th pr-table__th--acciones">Acciones</th>
                </tr>
            </thead>
            <tbody class="pr-table__body">
                <?php while ($row = sqlsrv_fetch_array($resultado, SQLSRV_FETCH_ASSOC)) {
                    $vend = trim(($row['nombre_vendedor'] ?? '') . ' ' . ($row['apellido_vendedor'] ?? ''));
                ?>
                    <tr class="pr-table__row">
                        <td class="pr-table__td pr-table__td--titulo"><?= h($row['titulo']) ?></td>
                        <td class="pr-table__td pr-table__td--imagen">
                            <?php if (!empty($row['imagen'])) { ?>
                                <img src="../../imagenes/<?= h($row['imagen']) ?>" alt="<?= h($row['titulo']) ?>">
                            <?php } else {
                                echo '—';
                            } ?>
                        </td>
                        <td class="pr-table__td pr-table__td--precio num"><?= fmt_num($row['precio']) ?> Bs</td>
                        <td class="pr-table__td pr-table__td--cantidad num"><?= (int)$row['cantidad'] ?></td>
                        <td class="pr-table__td pr-table__td--categoria"><?= h($row['categoria'] ?? '—') ?></td>
                        <td class="pr-table__td pr-table__td--talla"><?= h($row['talla'] ?? '—') ?></td>
                        <td class="pr-table__td pr-table__td--genero"><?= h($row['genero'] ?? '—') ?></td>
                        <td class="pr-table__td pr-table__td--color"><?= h($row['color'] ?? '—') ?></td>
                        <td class="pr-table__td pr-table__td--vendedor"><?= h($vend ?: '—') ?></td>
                        <td class="pr-table__td pr-table__td--creado"><?= fmt_fecha($row['creado_en']) ?></td>
                        <td class="pr-table__td pr-table__td--acciones">
                            <div class="pr-actions-row">
                                <!-- <form action="" method="POST" class="pr-form pr-form--delete">
                                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                    <input type="submit" value="Eliminar" class="boton eliminar pr-btn pr-btn--danger">
                                </form> -->
                                <a class="boton actualizar pr-btn pr-btn--update" href="actualizar.php?id=<?= (int)$row['id'] ?>">Actualizar</a>
                                <a class="boton vender pr-btn pr-btn--success" href="vender.php?id=<?= (int)$row['id'] ?>">Vender</a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
        const mensaje = document.getElementById('mensaje');
        if (mensaje) setTimeout(() => {
            mensaje.style.display = 'none';
        }, 3000);
    </script>
</body>

</html>
<style>
    :root {
        --humo-blanco: #F0F0F0;
        --negro: #111111;
        --rojo-brillante: #E7473C;
        --blanco-puro: #FFFFFF;
        --rojo-muy-claro: #FFE6E4;
        --sombra-negra-ligera: rgba(0, 0, 0, 0.1);
        --gris-claro: #DDD;
    }

    a {
        text-decoration: none;
    }

    /* Layout general */
    body.pr-page {
        margin: 0;
        margin-top: 150px !important;
        font-family: system-ui, sans-serif;
        background-color: var(--humo-blanco);
        color: var(--negro);
        padding: 4rem;
    }

    /* Header */
    .pr-title {
        color: var(--rojo-brillante);
        font-weight: 700;
        margin: 0 0 1rem 0;
    }

    /* Alertas */
    .pr-alert {
        padding: 0.8rem 1rem;
        background-color: var(--blanco-puro);
        border-left: 4px solid var(--rojo-brillante);
        border-radius: 10px;
        box-shadow: 0 4px 10px var(--sombra-negra-ligera);
        margin-bottom: 1rem;
        color: var(--negro);
    }

    /* Botones */
    .pr-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.55rem 1rem;
        border-radius: 999px;
        border: none;
        background-color: var(--rojo-brillante);
        color: var(--blanco-puro);
        cursor: pointer;
        font-weight: 600;
        transition: 0.2s;
        box-shadow: 0 6px 14px rgba(231, 71, 60, 0.3);
    }

    .pr-btn:hover {
        background-color: var(--blanco-puro);
        color: var(--rojo-brillante);
        border: 1px solid var(--rojo-brillante);
    }

    /* Filtros */
    .pr-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        padding: 1.2rem;
        background-color: var(--blanco-puro);
        border-radius: 12px;
        box-shadow: 0 6px 16px var(--sombra-negra-ligera);
    }

    .pr-filters label {
        color: var(--negro);
        font-weight: 600;
    }

    .pr-filters input,
    .pr-filters select {
        padding: 0.45rem 0.6rem;
        border-radius: 8px;
        border: 1px solid var(--gris-claro);
        background-color: var(--humo-blanco);
        color: var(--negro);
    }

    .pr-filters input:focus,
    .pr-filters select:focus {
        border-color: var(--rojo-brillante);
        background-color: var(--blanco-puro);
        box-shadow: 0 0 0 3px rgba(231, 71, 60, 0.25);
    }

    /* Tabla */
    .pr-table-wrap {
        margin-top: 1rem;
        overflow-x: auto;
        box-shadow: 0 8px 20px var(--sombra-negra-ligera);
        border-radius: 12px;
        background-color: var(--blanco-puro);
    }

    .pr-table {
        width: 100%;
        border-collapse: collapse;
        background-color: var(--blanco-puro);
    }

    .pr-table__head {
        background-color: var(--rojo-brillante);
        color: var(--blanco-puro);
    }

    .pr-table__head th {
        padding: 0.7rem;
        font-weight: 600;
        border-bottom: 1px solid var(--gris-claro);
    }

    .pr-table__td {
        padding: 0.6rem;
        border-bottom: 1px solid var(--gris-claro);
        color: var(--negro);
    }

    /* Hover fila */
    .pr-table__row:hover {
        background-color: var(--rojo-muy-claro);
    }

    /* Imágenes */
    .pr-table__td--imagen img {
        max-width: 60px;
        max-height: 60px;
        border-radius: 8px;
        object-fit: cover;
        box-shadow: 0 3px 8px var(--sombra-negra-ligera);
        border: 1px solid var(--gris-claro);
    }

    /* Acciones tabla */
    .pr-actions-row {
        display: flex;
        gap: 0.4rem;
    }
</style>
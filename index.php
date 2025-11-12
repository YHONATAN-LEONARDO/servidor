<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Configuración básica -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Título y SEO principal -->
    <title>EcoAbrigo | Moda moderna y accesible para todos</title>
    <meta name="description" content="EcoAbrigo es tu tienda de ropa moderna, cómoda y accesible. Encuentra prendas para hombre y mujer que combinan estilo y buen precio.">
    <meta name="keywords" content="EcoAbrigo, moda, ropa, tienda de ropa, hombre, mujer, abrigos, poleras, pantalones, tienda online">
    <meta name="author" content="EcoAbrigo">
    <meta name="robots" content="index, follow">

    <!-- URL principal del sitio -->
    <link rel="canonical" href="http://3.128.188.195/">

    <!-- Iconos -->
    <link rel="icon" href="/public/img/logo.png" type="image/png">
    <link rel="apple-touch-icon" href="/public/img/logo.png">

    <!-- Color barra navegador móvil -->
    <meta name="theme-color" content="#111111">

    <!-- Preload de logo (para que aparezca rápido) -->
    <link rel="preload" as="image" href="/public/img/logo.png">

    <!-- Google Fonts (ejemplo con Rubik) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- CSS (normalize primero, luego estilos generales, luego responsive) -->
    <link rel="stylesheet" href="/public/css/normalize.css">
    <link rel="stylesheet" href="/public/css/estilos.css">
    <link rel="stylesheet" href="/public/css/celular.css">

    <!-- Open Graph (para compartir en Facebook, WhatsApp, etc.) -->
    <meta property="og:title" content="EcoAbrigo | Moda moderna y accesible">
    <meta property="og:description" content="Ropa moderna para hombre y mujer. Tu estilo, tu identidad, todos los días.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://3.128.188.195/">
    <meta property="og:image" content="http://3.128.188.195/public/img/logo.png">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="EcoAbrigo | Moda moderna y accesible">
    <meta name="twitter:description" content="Descubre prendas con estilo actual y precios accesibles.">
    <meta name="twitter:image" content="http://3.128.188.195/public/img/logo.png">

    <!-- Manifest para futuro PWA (puedes crearlo luego) -->
    <!-- <link rel="manifest" href="/manifest.json"> -->

    <!-- Datos estructurados (JSON-LD) para Google: marca/tienda -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "ClothingStore",
            "name": "EcoAbrigo",
            "url": "http://3.128.188.195/",
            "logo": "http://3.128.188.195/public/img/logo.png",
            "image": "http://3.128.188.195/public/img/logo.png",
            "address": {
                "@type": "PostalAddress",
                "addressCountry": "BO",
                "addressLocality": "La Paz",
                "streetAddress": "Plaza Eguino"
            },
            "telephone": "+59170000000",
            "description": "Tienda de ropa moderna y accesible para hombre y mujer.",
            "currenciesAccepted": "BOB",
            "paymentAccepted": "Cash, Credit Card",
            "priceRange": "$$"
        }
    </script>
</head>


<body>
    <?php include "views/layouts/header.php"; ?>
    <!-- mensaje -->
    <h1 class="t-p">Moda que habla por ti</h1>
    <main class="principal">
        <div class="img-p">
            <img src="public/img/principal/hombre.jpg" class="img-principal" alt="">
            <h2>Estilo que te acompaña</h2>

            <a href="hombre.php?genero=1">
                <button>Comprar</button>
            </a>
        </div>
        <h1 class="t-p">Fuerza, estilo y confianza en cada detalle</h1>

        <div class="carousel">
            <div class="group">
                <img class="card" src="public/img/principal/im1.png" alt="">
                <img class="card" src="public/img/principal/im2.png" alt="">
                <img class="card" src="public/img/principal/im3.png" alt="">
                <img class="card" src="public/img/principal/im4.png" alt="">
                <img class="card" src="public/img/principal/im5.jpg" alt="">
                <img class="card" src="public/img/principal/im6.jpg" alt="">
                <img class="card" src="public/img/principal/im7.png" alt="">
            </div>
            <div aria-hidden class="group">
                <img class="card" src="public/img/principal/im1.png" alt="">
                <img class="card" src="public/img/principal/im2.png" alt="">
                <img class="card" src="public/img/principal/im3.png" alt="">
                <img class="card" src="public/img/principal/im4.png" alt="">
                <img class="card" src="public/img/principal/im5.jpg" alt="">
                <img class="card" src="public/img/principal/im6.jpg" alt="">
                <img class="card" src="public/img/principal/im7.png" alt="">
            </div>
        </div>
        <div class="img-p">
            <img src="public/img/principal/mujer.jpg" class="img-principal" alt="">
            <h2>Moda que inspira</h2>


            <a href="mujer.php?genero=0">
                <button>Comprar</button>

            </a>
        </div>
        <h1 class="t-p">Brilla con cada paso que das</h1>

        <div class="carousel">
            <div class="group">
                <img class="card" src="public/img/principal/m1.jpg" alt="">
                <img class="card" src="public/img/principal/m2.jpg" alt="">
                <img class="card" src="public/img/principal/m3.jpg" alt="">
                <img class="card" src="public/img/principal/m4.jpg" alt="">
                <img class="card" src="public/img/principal/m5.jpg" alt="">
                <img class="card" src="public/img/principal/m6.jpg" alt="">
                <img class="card" src="public/img/principal/m7.jpg" alt="">
                <img class="card" src="public/img/principal/m8.jpg" alt="">
            </div>
            <div aria-hidden class="group">
                <img class="card" src="public/img/principal/m1.jpg" alt="">
                <img class="card" src="public/img/principal/m2.jpg" alt="">
                <img class="card" src="public/img/principal/m3.jpg" alt="">
                <img class="card" src="public/img/principal/m4.jpg" alt="">
                <img class="card" src="public/img/principal/m5.jpg" alt="">
                <img class="card" src="public/img/principal/m6.jpg" alt="">
                <img class="card" src="public/img/principal/m7.jpg" alt="">
                <img class="card" src="public/img/principal/m8.jpg" alt="">
            </div>
        </div>
    </main>
    <?php
    session_start();
    require_once __DIR__ . '/app/config/database.php';

    // Productos destacados: solo con descuento
    $sql = "SELECT id, titulo, descripcion, imagen, precio, genero, descuento
        FROM productos
        WHERE descuento > 0 AND cantidad > 0
        ORDER BY id DESC";

    $result = sqlsrv_query($conn, $sql);
    if ($result === false) {
        die('Error en la consulta: ' . print_r(sqlsrv_errors(), true));
    }

    // Separar por género
    $destacados = ['hombre' => [], 'mujer' => []];
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $generoKey = strtolower($row['genero']);
        if (!isset($destacados[$generoKey])) $destacados[$generoKey] = [];
        $destacados[$generoKey][] = $row;
    }
    ?>

    <!-- <section class="productos-destacados">
        <h2 class="t-p">Productos Destacados</h2>

        <?php foreach (['hombre', 'mujer'] as $gen): ?>
            <?php if (!empty($destacados[$gen])): ?>
                <h3 class="genero-titulo"><?= ucfirst($gen); ?></h3>
                <div class="productos-grid">
                    <?php foreach ($destacados[$gen] as $prod):
                        $precioFinal = $prod['precio'] - ($prod['precio'] * ($prod['descuento'] / 100));
                    ?>
                        <div class="producto">
                            <div class="producto-img">
                                <img src="imagenes/<?= htmlspecialchars($prod['imagen']); ?>" alt="<?= htmlspecialchars($prod['titulo']); ?>">
                                <?php if ($prod['descuento'] > 0): ?>
                                    <span class="descuento">-<?= $prod['descuento']; ?>%</span>
                                <?php endif; ?>
                            </div>
                            <h4 class="yy"><?= htmlspecialchars($prod['titulo']); ?></h4>
                            <p class="precio">
                                <?php if ($prod['descuento'] > 0): ?>
                                    <span class="precio-antes"><?= number_format($prod['precio'], 2); ?> Bs</span>
                                    <?= number_format($precioFinal, 2); ?> Bs
                                <?php else: ?>
                                    <?= number_format($prod['precio'], 2); ?> Bs
                                <?php endif; ?>
                            </p>
                            <a href="anuncio.php?id=<?= $prod['id']; ?>"><button>Comprar</button></a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </section>

    <style>
        /* Contenedor general */
        .productos-destacados {
            padding: 3rem 1rem;
            background: #f9f9f9;
        }

        /* Título principal */
        .productos-destacados h2 {
            text-align: center;
            font-size: 2.8rem;
            margin-bottom: 2rem;
            color: #222;
        }

        /* Título de género */
        .genero-titulo {
            font-size: 2rem;
            margin-top: 2rem;
            margin-bottom: 1.5rem;
            color: #555;
            text-align: center;
        }
        .yy{
            height: 3rem;
            font-size: 1.2rem;
        }
        /* Grid de productos */
        .productos-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            justify-content: center;
        }

        /* Cada producto */
        .producto {
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
            width: 220px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            position: relative;
            transition: transform 0.3s;
        }

        .producto:hover {
            transform: translateY(-5px);
        }

        /* Imagen del producto */
        .producto-img {
            position: relative;
            margin-bottom: 0.8rem;
        }

        .producto-img img {
            width: 100%;
            border-radius: 12px;
        }

        /* Etiqueta de descuento */
        .descuento {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #ff1a1a;
            color: #fff;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        /* Precio */
        .precio {
            margin: 0.5rem 0;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .precio-antes {
            text-decoration: line-through;
            color: #999;
            margin-right: 0.5rem;
        }

        /* Botón fijo y estilizado */
        .producto button {
            margin-top: 0.5rem;
            padding: 0.6rem 1.2rem;
            border: none;
            background: #333;
            color: #fff;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: 0.3s;
        }

        .producto button:hover {
            background: #ff1a1a;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .productos-grid {
                display: grid;
                grid-template-columns: repeat(2,1fr);
                padding: 2rem;
            }
        }
    </style> -->





    <?php include "views/layouts/footer.php"; ?>
</body>

</html>
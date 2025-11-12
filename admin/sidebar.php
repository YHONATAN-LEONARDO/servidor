<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$rol = strtolower($_SESSION['usuario_rol'] ?? $_SESSION['rol'] ?? 'cliente');
?>

<!-- NAVBAR SUPERIOR -->
<header class="topbar">
  <div class="brand">
    <img src="/public/img/logo.png" alt="Logo" class="logo">
    <div class="brand-text">
      <h1>ECOABRIGO</h1>
      <div class="brand-status">
        <span class="status-dot"></span>
        <p class="rol"><?= ucfirst($rol) ?></p>
      </div>

    </div>
  </div>

  <nav class="topmenu">
    <a href="/admin/index.php" class="menu-item">Inicio</a>
    <a href="/admin/perfil.php" class="menu-item">Perfil</a>

    <?php if (in_array($rol, ['admin', 'empleado', 'vendedor'])): ?>
      <div class="menu-item has-submenu">
        <span>Productos</span>
        <div class="submenu">
          <a href="/admin/productos/productos.php">Lista de Productos</a>

          <a href="/admin/productos/crear.php">Crear Producto</a>
        </div>
      </div>

      <div class="menu-item has-submenu">
        <span>Compras</span>
        <div class="submenu">
          <a href="/admin/compras/ingresar_factura.php">Ingresar Factura</a>
          <a href="/admin/compras/lista.php">Lista y actualizacion de Compras</a>
          <?php if ($rol === 'admin'): ?>
            <a href="/admin/compras/l-proveedor.php">Lista y actualizacion de Proveedor</a>
            <a href="/admin/compras/crear_proveedor.php">Ingresar Proveedor</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="menu-item has-submenu">
        <span>Pedidos</span>
        <div class="submenu">
          <a href="/admin/pedido/lista.php">Lista de Pedidos</a>
        </div>
      </div>

      <div class="menu-item has-submenu">
        <span>Mensajes</span>
        <div class="submenu">
          <a href="/admin/contacto/mensajes.php">Ver Mensajes</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($rol === 'admin'): ?>
      <div class="menu-item has-submenu">
        <span>Pesonal</span>
        <div class="submenu">
          <a href="/admin/usuarios/crear.php">Crear Personal</a>
          <a href="/admin/usuarios/lista.php">Lista y actualizacion de Personal</a>
        </div>
      </div>

      <div class="menu-item has-submenu">
        <span>Clientes</span>
        <div class="submenu">
          <a href="/admin/clientes/lista.php">Lista y actualizacion de Cliente</a>
          <a href="/admin/clientes/crear.php">Crear Cliente</a>
        </div>
      </div>

      <div class="menu-item has-submenu">
        <span>Reportes</span>
        <div class="submenu">
          <a href="/admin/reportes/ventas_dia.php">Ventas del Día</a>
          <a href="/admin/reportes/filtros.php">Reporte con Filtro</a>
        </div>
      </div>
    <?php endif; ?>

    <a href="/views/usuarios/cerrar-sesion.php" class="menu-item">Salir</a>
  </nav>
</header>

<style>
  :root {
    --primary: #b50000;
    --primary-soft: #ffe6e6;
    --primary-soft-2: #fff4f4;
    --text-main: #111111;
    --text-muted: #666666;
    --bg: #ffffff;
  }


  * {
    box-sizing: border-box;
  }

  body {
    margin: 0;
    font-family: "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    background: #fafafa;
    color: var(--text-main);
  }

  /* ========= TOPBAR GENERAL ========= */
  .topbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 72px;
    padding: 0 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.04);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
    z-index: 1000;
    height: auto;
  }

  /* ========= BRAND / LOGO / NOMBRE ========= */
  .brand {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .brand .logo {
    width: 124px;
    height: auto;
    border-radius: 50%;
    object-fit: cover;
    padding: 7px;
    margin: 1rem;
  }

  .brand-text {
    display: flex;
    flex-direction: column;
    line-height: 1.1;
  }

  .brand-text h1 {
    margin: 0;
    font-size: 20px;
    letter-spacing: 0.18em;
    font-weight: 800;
    text-transform: uppercase;
    color: var(--primary);
  }

  .brand-text .rol {
    margin: 3px 0 0;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--text-muted);
  }

  /* Pequeño detalle de acento debajo del nombre */
  .brand-text h1::after {
    content: "";
    display: block;
    margin-top: 4px;
    width: 32px;
    height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--primary), #ff7b7b);
  }

  /* ========= MENÚ SUPERIOR ========= */
  .topmenu {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .topmenu a {
    text-decoration: none;
  }

  .menu-item {
    position: relative;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 14px;
    color: var(--text-main);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    border: 1px solid transparent;
    transition:
      background 0.2s ease,
      color 0.2s ease,
      border-color 0.2s ease,
      transform 0.15s ease;
    white-space: nowrap;
  }

  .menu-item:hover {
    background: var(--primary-soft);
    color: var(--primary);
    border-color: rgba(181, 0, 0, 0.2);
    transform: translateY(-1px);
  }

  /* ========= SUBMENÚS (DROPDOWNS) ========= */
  .has-submenu>span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }

  .has-submenu>span::after {
    content: "▾";
    font-size: 11px;
    transform: translateY(1px);
  }

  .submenu {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    min-width: 190px;
    background: #ffffff;
    border-radius: 12px;
    padding: 6px 0;
    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.12);
    border: 1px solid rgba(0, 0, 0, 0.04);
    display: none;
    z-index: 1100;
  }

  .submenu a {
    display: block;
    padding: 8px 14px;
    font-size: 14px;
    color: var(--text-main);
    white-space: nowrap;
    text-decoration: none;
    transition: background 0.18s ease, color 0.18s ease, padding-left 0.18s ease;
  }

  .submenu a:hover {
    background: var(--primary-soft-2);
    color: var(--primary);
    padding-left: 18px;
  }

  .has-submenu.active>.submenu {
    display: block;
  }

  /* ========= ESPACIO PARA EL CONTENIDO ========= */
  main,
  .layout,
  .content,
  .container,
  .wrapper {
    padding-top: 80px;
    /* espacio para la barra */
  }

  /* ========= RESPONSIVE ========= */
  @media (max-width: 1024px) {
    .topbar {
      padding: 0 16px;
    }

    .brand-text h1 {
      font-size: 18px;
      letter-spacing: 0.14em;
    }
  }

  @media (max-width: 820px) {
    .topbar {
      height: auto;
      padding: 10px 14px;
      flex-direction: column;
      align-items: flex-start;
      gap: 8px;
    }

    .topmenu {
      width: 100%;
      flex-wrap: wrap;
      justify-content: flex-start;
      row-gap: 4px;
    }

    .menu-item {
      padding: 6px 10px;
      font-size: 13px;
    }

    main,
    .layout,
    .content,
    .container,
    .wrapper {
      padding-top: 96px;
    }
  }

  .brand-status {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 3px;
  }

  /* Bolita verde de "en línea" */
  .status-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #22c55e;
    /* verde */
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.25);
    position: relative;
  }

  /* Efecto suave de pulso (opcional pero se ve bonito) */
  .status-dot::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5);
    animation: pulse-online 1.8s infinite;
  }

  @keyframes pulse-online {
    0% {
      transform: scale(1);
      opacity: 0.9;
    }

    70% {
      transform: scale(1.9);
      opacity: 0;
    }

    100% {
      transform: scale(1.9);
      opacity: 0;
    }
  }
</style>


<script>
  document.addEventListener("DOMContentLoaded", () => {
    const submenus = document.querySelectorAll(".has-submenu");

    submenus.forEach(menu => {
      menu.addEventListener("click", (e) => {
        e.stopPropagation();
        // Cerrar otros
        submenus.forEach(m => {
          if (m !== menu) m.classList.remove("active");
        });
        // Abrir/cerrar el seleccionado
        menu.classList.toggle("active");
      });
    });

    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener("click", () => {
      submenus.forEach(m => m.classList.remove("active"));
    });
  });
</script>
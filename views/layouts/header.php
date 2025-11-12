<?php
if (!isset($_SESSION)) {
  session_start();
}

$auth = $_SESSION['login'] ?? false;

// Detectar si estamos en el index.php
$currentPage = basename($_SERVER['PHP_SELF']);

function isActive($file)
{
  return basename($_SERVER['PHP_SELF']) === $file ? 'active-link' : '';
}
?>

<header class="<?php echo ($currentPage === 'index.php') ? 'with-video' : 'no-video'; ?>">
  <nav class="navegacion">

    <!-- Móvil: hamburguesa + título -->
    <div class="mobile-header">
      <div class="menu-toggle" id="menu-toggle">
        <span></span>
        <span></span>
        <span></span>
      </div>
      <span class="mobile-title">EcoAbrigo</span>
    </div>

    <!-- Enlaces principales -->
    <div class="enlace-uno">
      <a href="/" class="especial">
        <img class="logo" src="/public/img/logo.png" alt="Logo EcoAbrigo">
      </a>
      <a href="/">Inicio</a>
      <a href="/hombre.php?genero=1" class="<?php echo isActive('hombre.php'); ?>">Hombre</a>
      <a href="/mujer.php?genero=0" class="<?php echo isActive('mujer.php'); ?>">Mujer</a>
      <a href="/acerca.php" class="<?php echo isActive('acerca.php'); ?>">Nosotros</a>
      <a href="/contacto.php" class="<?php echo isActive('contacto.php'); ?>">Contáctanos</a>
      <a class="carrito <?php echo isActive('carrito.php'); ?>" href="/carrito.php">
        <ion-icon name="cart-outline"></ion-icon>
      </a>
    </div>

    <!-- Login / Perfil -->
    <div class="enlace-dos enlace-uno">
      <?php if ($auth): ?>
        <a href="/views/usuarios/cerrar-sesion.php">Cerrar Sesión</a>
        <a class="carrito" href="/perfil.php"><ion-icon name="person-outline"></ion-icon></a>
      <?php else: ?>
        <a href="/views/usuarios/login.php" class="<?php echo isActive('login.php'); ?>">Iniciar Sesión</a>
        <a href="/views/usuarios/registro.php" class="<?php echo isActive('registro.php'); ?>">Registrarse</a>

      <?php endif; ?>
    </div>
  </nav>

  <!-- Video solo en index.php -->
  <?php if ($currentPage === 'index.php'): ?>
    <div class="video-container">
      <video autoplay muted loop playsinline>
        <source src="/public/video/sis2.mp4" type="video/mp4">
      </video>

      <div class="de">
        <p class="rotating-text" id="rotating-text">EcoAbrigo</p>
      </div>

      <style>
        .de {
          text-align: center;
          color: #fff;
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
        }

        .rotating-text {
          font-style: italic;
          /* cursiva */
          font-weight: 900;
          font-size: 4rem;
          /* más grande */
          font-family: 'Georgia', 'Times New Roman', serif;
          text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.6);
          opacity: 1;
          transition: opacity 0.8s ease;
        }
      </style>

      <script>
        const frases = [
          "EcoAbrigo", // primera frase sola
          "La mejor ropa sostenible EcoAbrigo",
          "Calidad y confort en cada prenda EcoAbrigo",
          "Moda responsable y duradera EcoAbrigo",
          "Diseño elegante para todos EcoAbrigo",
          "Tu estilo diario y consciente EcoAbrigo"
        ];


        let index = 0;
        const textoElemento = document.getElementById("rotating-text");

        setInterval(() => {
          // desaparecer
          textoElemento.style.opacity = 0;

          setTimeout(() => {
            // cambiar texto
            index = (index + 1) % frases.length;
            textoElemento.textContent = frases[index];
            // aparecer
            textoElemento.style.opacity = 1;
          }, 800); // debe coincidir con la transición de opacidad
        }, 3000); // cada 3 segundos cambia
      </script>


    </div>
  <?php endif; ?>
</header>

<!-- Script del menú hamburguesa -->
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const menuToggle = document.getElementById("menu-toggle");
    const menu = document.querySelector(".enlace-uno");
    const loginMenu = document.querySelector(".enlace-dos");

    const toggleMenu = () => {
      menu.classList.toggle("active");
      loginMenu.classList.toggle("active");
      menuToggle.classList.toggle("open");
    };

    menuToggle.addEventListener("click", toggleMenu);
  });
</script>

<!-- Estilos completos -->
<style>
  /* Título solo visible en móvil */
  .mobile-title {
    color: #fff;
    font-size: 1.5rem;
    font-weight: bold;
    display: inline-block;
    /* se muestra por defecto */
  }

  /* Ocultar en escritorio */
  @media (min-width: 769px) {
    .mobile-title {
      display: none;

    }

  }

  /* Línea activa de menú */
  .active-link::after {
    width: 100% !important;
  }

  /* HEADER GENERAL */
  header {
    position: relative;
    width: 100%;
  }

  header.with-video {
    height: 75rem;
  }

  header.no-video {
    height: auto;
  }

  /* VIDEO */
  .video-container {
    position: relative;
    width: 100%;
    height: 75rem;
  }

  .video-container video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: -10;
  }

  /* TEXTO SOBRE EL VIDEO */
  /* .dentro-v {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: #fff;
    z-index: 1;
  }

  .dentro-v p {
    font-size: 2.2rem;
  } */

  /* NAV */
  nav {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    padding: 2rem 6rem;
    background: rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(0.5rem);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 100;
  }

  /* LOGO */
  .logo {
    width: 12rem;
  }

  /* ENLACES */
  nav div a {
    position: relative;
    color: #fff;
    text-decoration: none;
    margin-left: 2.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  nav div a::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: -0.5rem;
    width: 0%;
    height: 0.2rem;
    background-color: #fff;
    transition: width 0.3s ease;
  }

  nav div a:hover::after {
    width: 100%;
  }

  /* RESPONSIVE: HAMBURGUESA Y MÓVIL */
  @media (max-width: 768px) {

    /* Nav flexible vertical */
    nav {
      flex-direction: column;
      align-items: flex-start;
      padding: 1rem 2rem;
    }

    /* Header móvil: hamburguesa + título */
    .mobile-header {
      display: flex;
      align-items: center;
      gap: 1rem;
      width: 100%;
      margin-bottom: 0.5rem;
    }

    .mobile-title {
      color: #fff;
      font-size: 1.5rem;
      font-weight: bold;
    }

    /* Menú hamburguesa */
    .menu-toggle {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      width: 30px;
      height: 25px;
      cursor: pointer;
      z-index: 2000;
    }

    .menu-toggle span {
      display: block;
      height: 3px;
      width: 100%;
      background-color: white;
      border-radius: 2px;
      transition: all 0.3s ease;
    }

    .menu-toggle.open span:nth-child(1) {
      transform: rotate(45deg) translate(5px, 5px);
    }

    .menu-toggle.open span:nth-child(2) {
      opacity: 0;
    }

    .menu-toggle.open span:nth-child(3) {
      transform: rotate(-45deg) translate(5px, -5px);
    }

    /* Menú enlaces oculto por defecto */
    .enlace-uno,
    .enlace-dos {
      display: none;
      flex-direction: column;
      width: 100%;
      margin-top: 10px;
    }

    /* Menú abierto */
    .enlace-uno.active,
    .enlace-dos.active {
      display: flex;
    }

    /* Ajuste enlaces en móvil */
    nav div a {
      margin-left: 0;
      padding: 0.8rem 0;
      font-size: 1.2rem;
    }

    .logo {
      width: 10rem;
    }
  }


  /* VIDEO FULL SCREEN HEADER */
  .video-container {
    position: relative;
    /* no fijo, sigue el flujo */
    width: 100%;
    height: 100vh;
    /* ocupa toda la pantalla al inicio */
    overflow: hidden;
    z-index: -10;
    /* detrás del contenido del header */
  }

  .video-container video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    /* cubre toda la pantalla sin deformar */
  }
</style>
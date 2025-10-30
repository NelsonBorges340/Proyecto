<?php
session_start();
$rol = isset($_SESSION['usuario_rol']) ? $_SESSION['usuario_rol'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MANEKI</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <nav>
        <input type="checkbox" id="toggle">
        <div class="contenedor-logo">
            <span class="texlogo"><a href="index.php" style="color: #e1d0bd; text-decoration: none;">MANEKI</a></span>
            <img class="logo" src="../img/Diseño sin título (10).png" alt="Logo Inoxfile">
        </div>
        <ul class="navegacion">
            <?php if ($rol === 'cliente'): ?>
               
                <li><a href="#foot">sobre nosotros</a></li>
                <li><a href="servicios.html">servicios</a></li>
                <li><a href="mensajes.html">mensaje</a></li>
                <li class="usuario-menu">
                    <a href="#" class="usuario-nombre"><?php echo $_SESSION['nombreCompleto']; ?> ▼</a>
                    <ul class="submenu">
                        <li><a href="perfil.php">Mi Perfil</a></li>
                        <li><a href="../php/logout.php">Cerrar sesión</a></li>
                    </ul>
                </li>
            <?php elseif ($rol === 'proveedor'): ?>
               
                <li><a href="#foot">sobre nosotros</a></li>
                <li><a href="servicios.html">servicios</a></li>
                <li><a href="publicar-servicio.php">publicar</a></li>
                <li><a href="mensajes.html">mensaje</a></li>
                <li class="usuario-menu">
                    <a href="#" class="usuario-nombre"><?php echo $_SESSION['nombreCompleto']; ?> ▼</a>
                    <ul class="submenu">
                        <li><a href="perfil.php">Mi Perfil</a></li>
                        <li><a href="../php/logout.php">Cerrar sesión</a></li>
                    </ul>
                </li>
            <?php elseif ($rol === 'administrador'): ?>
                 <li><a href="mensajes.html">mensaje</a></li>
                <li><a href="servicios.html">servicios</a></li>
                <li><a href="admin-panel.html">Panel Admin</a></li>
                <li class="usuario-menu">
                    <a href="#" class="usuario-nombre"><?php echo $_SESSION['nombreCompleto']; ?> ▼</a>
                    <ul class="submenu">
                        <li><a href="perfil.php">Mi Perfil</a></li>
                        <li><a href="../php/logout.php">Cerrar sesión</a></li>
                    </ul>
                </li>
            <?php else: ?>
             
                <li><a href="#foot">sobre nosotros</a></li>
                <li><a href="servicios.html">servicios</a></li>
                <li><a href="finicio.html">iniciar sesion</a></li>
            <?php endif; ?>
        </ul>
        <label for="toggle" class="icon-bars">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
        </label>
    </nav>
    
    <div class="imagen_centro">
        <img class="imagenc" src="../img/centro.avif" alt="">
        <div class="contenedor-busqueda">
            <input type="search" class="search" placeholder="   Búsqueda...">
            <button class="boton-filtros">Filtros</button>
        </div>
    </div>

    
    </div>


    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const carousels = [
            {
                trackId: 'track-proveedores',
                totalCards: 6,
                currentIndex: 0
            },
            {
                trackId: 'track-productos',
                totalCards: 6,
                currentIndex: 0
            }
        ];

        function getCardsPerView(track) {
            const cardWidth = 320;
            const container = track.closest('.carousel-container');
            const containerWidth = container.offsetWidth;
            return Math.floor(containerWidth / cardWidth) || 1;
        }

        function moveCarousel(carousel, direction) {
            const track = document.getElementById(carousel.trackId);
            const cardsPerView = getCardsPerView(track);
            const maxIndex = carousel.totalCards - cardsPerView;
            carousel.currentIndex += direction;

            if (carousel.currentIndex < 0) carousel.currentIndex = 0;
            if (carousel.currentIndex > maxIndex) carousel.currentIndex = maxIndex;

            const moveX = -carousel.currentIndex * 320;
            track.style.transform = `translateX(${moveX}px)`;
        }

        // Botones manuales
        document.querySelectorAll('.btn-prev, .btn-next').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.getAttribute('data-carousel');
                const direction = this.classList.contains('btn-prev') ? -1 : 1;
                const carousel = carousels.find(c => c.trackId === `track-${type}`);
                moveCarousel(carousel, direction);
            });
        });

        // Auto-slide y resize
        carousels.forEach(carousel => {
            const track = document.getElementById(carousel.trackId);

            setInterval(() => {
                const cardsPerView = getCardsPerView(track);
                const maxIndex = carousel.totalCards - cardsPerView;
                if (carousel.currentIndex < maxIndex) {
                    moveCarousel(carousel, 1);
                } else {
                    carousel.currentIndex = -1;
                    moveCarousel(carousel, 1);
                }
            }, 6000);

            window.addEventListener('resize', () => {
                carousel.currentIndex = 0;
                track.style.transform = `translateX(0px)`;
            });
        });
    });

    // Cerrar sesión (opcional, destruye la sesión en PHP)
 function cerrarSesion() {
    fetch('../php/logout.php', { method: 'POST' })
        .then(() => {
            window.location = 'index.php';
        });
}


    function cargarProveedores() {
        fetch('../php/listar-proveedores.php')
            .then(res => res.json())
            .then(data => {
                const track = document.getElementById('track-proveedores');
                track.innerHTML = '';
                data.forEach(p => {
                    track.innerHTML += `
                        <div class="card">
                            <img src="${p.imagen_url || '../img/Diseño sin título (10).png'}" alt="Proveedor">
                            <h3 class="name-probedor">${p.nombreCompleto}</h3>
                            <p><b>${p.nombreNegocio || 'Negocio'}</b></p>
                            <p>${p.descripcion || ''}</p>
                            <p>Especialidad: ${p.especialidad || ''}</p>
                            <a href="perfil.php?id=${p.id}" class="ver-perfil">Ver Perfil</a>
                        </div>
                    `;
                });
            });
    }
    cargarProveedores();

    function cargarServiciosRecomendados() {
        fetch('../php/listar-servicios-recomendados.php')
            .then(res => res.json())
            .then data => {
                const track = document.getElementById('track-productos');
                track.innerHTML = '';
                data.forEach(s => {
                    track.innerHTML += `
                        <div class="card">
                            <img src="${s.imagen_url || '../img/Diseño sin título (10).png'}" alt="Servicio">
                            <h3 class="name-probedor">${s.titulo}</h3>
                            <p>${s.descripcion || ''}</p>
                            <p>Categoría: ${s.categoria || 'Sin categoría'}</p>
                            <p>Precio: ${s.precio} ${s.moneda || 'UYU'}</p>
                            <p>Proveedor: ${s.proveedor}</p>
                            <p>Ubicación: ${s.ubicacion || 'No especificada'}</p>
                            <a href="perfil.php?id=${s.usuario_id}" class="ver-perfil">Ver Proveedor</a>
                        </div>s
                    `;
                });
            });
    }
    cargarServiciosRecomendados();
    </script>
    
  <footer id="foot">
    <div class="footer-container">
      <!-- Logo o nombre -->
      <div class="footer-logo">
        <h2>MANEKI</h2>
        <p>“La solución que necesitas, con la suerte de tu lado.”.</p>
      </div>

      <!-- Enlaces -->
      <div class="footer-section">
        <h3>Enlaces</h3>
        <ul>
          <li><a href="#">Inicio</a></li>
          <li><a href="#">Sobre Nosotros</a></li>
          <li><a href="#">Servicios</a></li>
          <li><a href="#">Contacto</a></li>
        </ul>
      </div>

      <!-- Contacto -->
      <div class="footer-section">
        <h3>Contacto</h3>
        <p>Email: juanarriola806@gmail.com</p>
        <p>Tel: +598 099 479 555</p>
      </div>

      <!-- Redes sociales -->
      <div class="footer-section">
        <h3>Síguenos</h3>
        <div class="social-icons">
          <a href="#" aria-label="Facebook">🌐</a>
          <a href="#" aria-label="Twitter">🐦</a>
          <a href="#" aria-label="Instagram">📸</a>
          <a href="#" aria-label="LinkedIn">💼</a>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© 2025 MANEKI - Todos los derechos reservados</p>
    </div>
  </footer>
</body>
</html>
/*
 * Restaurador de sesión PHP para el panel de administración.
 * El panel identifica al usuario con localStorage (userData) y la sesión PHP
 * se pierde al reiniciar el servidor / expirar (Render, múltiples instancias).
 * Este script re-establece la sesión PHP llamando a ensure_session.php con los
 * datos del admin guardados en el navegador, ANTES de que las peticiones a los
 * endpoints protegidos se ejecuten (XHR síncrono en <head>).
 */
(function () {
  if (window.__AdminSessionRestored) return;

  function getAdminUser() {
    try {
      var raw = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
      if (!raw) return null;
      var data = JSON.parse(raw);
      var u = data && data.user ? data.user : data;
      if (!u) return null;
      var role = u.rol || u.role || u.tipo || '';
      if (String(role).toLowerCase() !== 'admin') return null;
      var id = u.id || u.id_usuario;
      var email = u.correo_electronico || u.email;
      if (!id || !email) return null;
      return { id: String(id), email: String(email) };
    } catch (e) {
      return null;
    }
  }

  function restoreSession() {
    var u = getAdminUser();
    if (!u) return;
    try {
      // XHR síncrono: garantiza que la sesión exista antes de los fetch del resto de la página.
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '../backend/php/auth/ensure_session.php', false);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.send('id=' + encodeURIComponent(u.id) + '&email=' + encodeURIComponent(u.email));
    } catch (e) {
      // Silencioso: si falla, los endpoints responderán 403 y se manejarán en cada página.
    }
  }

  restoreSession();

  // Helper de fetch para endpoints admin: siempre con cookies y sin caché.
  window.adminApi = function (path, opts) {
    var o = opts || {};
    o.credentials = 'same-origin';
    if (o.cache === undefined) o.cache = 'no-store';
    return fetch(path, o);
  };

  // Cargar cliente CSRF (inyecta el header X-CSRF-Token en escrituras).
  (function () {
    if (window.__DcCsrfLoaded) return;
    var s = document.createElement('script');
    s.src = '../static/js/csrf_client.js';
    s.async = false;
    document.head.appendChild(s);
  })();

  window.__AdminSessionRestored = true;
})();

/* ============================================================
   Overlay del sidebar para móvil — se inyecta una sola vez.
   Cierra el sidebar al tocar fuera de él en pantallas pequeñas.
   ============================================================ */
(function () {
  if (window.__AdminSidebarOverlayInit) return;
  window.__AdminSidebarOverlayInit = true;

  document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.getElementById('sidebar');
    var toggle  = document.getElementById('menuToggle');
    var mainContent = document.getElementById('mainContent');
    if (!sidebar || !toggle) return;

    /* Crear overlay */
    var overlay = document.createElement('div');
    overlay.id = 'sidebarOverlay';
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);

    /* Cerrar sidebar al pulsar overlay */
    overlay.addEventListener('click', function () {
      sidebar.classList.remove('active');
      overlay.classList.remove('active');
      document.body.style.overflow = '';
    });

    /* Mejorar el toggle existente: añadir overlay y bloquear scroll */
    toggle.addEventListener('click', function () {
      var isMobile = window.innerWidth <= 992;
      if (isMobile) {
        var isOpen = sidebar.classList.contains('active');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active', !isOpen);
        document.body.style.overflow = isOpen ? '' : 'hidden';
      } else {
        /* Escritorio: colapsar sidebar y ajustar main-content */
        sidebar.classList.toggle('collapsed');
        if (mainContent) mainContent.classList.toggle('expanded');
      }
    });

    /* Al redimensionar, limpiar estado si se pasa a escritorio */
    window.addEventListener('resize', function () {
      if (window.innerWidth > 992) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        sidebar.classList.remove('active');
      }
    });
  });
})();

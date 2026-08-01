// Normalizar notificaciones SweetAlert2 como toasts en la esquina superior derecha
(function () {
  try {
    if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
      const originalFire = Swal.fire.bind(Swal);
      const toastFactory = (opts) => Swal.mixin({
        toast: true,
        position: (opts && opts.position) || 'top-end',
        timer: (opts && Object.prototype.hasOwnProperty.call(opts, 'timer')) ? Math.max(5000, Number(opts.timer) || 0) : 5000,
        timerProgressBar: true,
        showConfirmButton: false
      });
      const shouldUseModal = (opts) => {
        if (!opts || typeof opts !== 'object') return false;
        if (opts.showCancelButton || opts.showDenyButton) return true;
        if (opts.input || opts.html) return true;
        if (opts.confirmButtonText && opts.timer == null) return true;
        if (opts.draggable) return true;
        return false;
      };
      Swal.fire = function (options, ...rest) {
        const opts = (typeof options === 'string') ? { title: options } : (options || {});
        if (!shouldUseModal(opts)) {
          // Evitar recursión infinita llamando a originalFire directamente
          const mergedOptions = Object.assign({
            toast: true,
            position: opts.position || 'top-end',
            timer: Object.prototype.hasOwnProperty.call(opts, 'timer') ? Math.max(5000, Number(opts.timer) || 0) : 5000,
            timerProgressBar: true,
            showConfirmButton: false
          }, opts, {
            background: '#000000',
            color: '#ffffff'
          });
          return originalFire(mergedOptions);
        }
        const forced = Object.assign({}, opts, {
          background: '#000000',
          color: '#ffffff',
          confirmButtonColor: '#e50914',
          cancelButtonColor: '#1f1f1f'
        });
        return originalFire(forced, ...rest);
      };
      // Atajo global explícito
      window.toast = function (opts) { return toastFactory(opts || {}).fire(opts || {}); };
    }
  } catch (e) { /* noop */ }
})();

document.addEventListener('DOMContentLoaded', () => {
  try {
    const header = document.querySelector('.header');
    const headerContent = document.querySelector('.header .header-content');
    if (header && headerContent) {
      const logoEl = headerContent.querySelector('.logo');
      let burger = headerContent.querySelector('.mobile-toggle');
      const hasMobileHeader = !!document.querySelector('.mobile-header');
      if (!burger && !hasMobileHeader) {
        burger = document.createElement('button');
        burger.className = 'mobile-toggle';
        burger.setAttribute('aria-label', 'Menú');
        burger.innerHTML = '<i class="fas fa-bars"></i>';
        // Asegurar hamburguesa como primer elemento (lado izquierdo)
        headerContent.insertBefore(burger, headerContent.firstChild);
      }
      // Drawer lateral y overlay
      let drawerOverlay = document.getElementById('mobileDrawerOverlay');
      if (!drawerOverlay) {
        drawerOverlay = document.createElement('div');
        drawerOverlay.id = 'mobileDrawerOverlay';
        drawerOverlay.className = 'mobile-drawer-overlay';
        document.body.appendChild(drawerOverlay);
      }
      let drawer = document.getElementById('mobileDrawer');
      if (!drawer) {
        drawer = document.createElement('aside');
        drawer.id = 'mobileDrawer';
        drawer.className = 'mobile-drawer';
        drawer.innerHTML = `
          <div class="drawer-header">
            <div class="drawer-grip"></div>
            <div class="drawer-title">Menú</div>
            <button class="drawer-close" aria-label="Cerrar"><i class="fas fa-times"></i></button>
          </div>
          <div class="drawer-content"></div>
        `;
        document.body.appendChild(drawer);
      }
      const drawerContent = drawer.querySelector('.drawer-content');
      const navMenu = header.querySelector('.nav-menu');
      const authButtons = document.getElementById('authButtons');
      const userLoggedButtons = document.getElementById('userLoggedButtons');
      const quickLinks = document.getElementById('quickLinks');

      const pageDrawer = document.getElementById('drawerAuthButtons');
      const moveToDrawer = () => {
        // Si la página ya trae su propio drawer (con botones de acceso), NO mover el DOM
        if (!drawerContent || pageDrawer) return;
        // QuickLinks (carrito) primero, para que sea visible inmediatamente
        if (quickLinks && quickLinks.parentElement !== drawerContent) {
          quickLinks.classList.add('drawer-quicklinks');
          drawerContent.insertBefore(quickLinks, drawerContent.firstChild);
        }
        if (navMenu && navMenu.parentElement !== drawerContent) drawerContent.appendChild(navMenu);
        if (authButtons && authButtons.parentElement !== drawerContent) drawerContent.appendChild(authButtons);
        if (userLoggedButtons && userLoggedButtons.parentElement !== drawerContent) drawerContent.appendChild(userLoggedButtons);
      };
      const moveToHeader = () => {
        if (navMenu && navMenu.parentElement !== header) header.appendChild(navMenu);
        // authButtons y userLoggedButtons viven en header-content originalmente
        if (authButtons && authButtons.parentElement !== headerContent) headerContent.appendChild(authButtons);
        if (userLoggedButtons && userLoggedButtons.parentElement !== headerContent) headerContent.appendChild(userLoggedButtons);
        if (quickLinks && quickLinks.parentElement !== headerContent) {
          quickLinks.classList.remove('drawer-quicklinks');
          headerContent.appendChild(quickLinks);
        }
      };
      const applyLayout = () => {
        if (window.matchMedia('(max-width: 992px)').matches) { moveToDrawer(); }
        else { document.body.classList.remove('drawer-open'); moveToHeader(); }
      };
      applyLayout();
      let rT = null;
      window.addEventListener('resize', () => { clearTimeout(rT); rT = setTimeout(applyLayout, 120); });
      burger.addEventListener('click', (e) => { 
        e.preventDefault(); 
        document.body.classList.toggle('drawer-open'); 
      });
      // Cierre rápido
      const closeDrawer = () => document.body.classList.remove('drawer-open');
      const btnClose = drawer.querySelector('.drawer-close');
      if (btnClose) btnClose.addEventListener('click', closeDrawer);
      if (drawerOverlay) drawerOverlay.addEventListener('click', closeDrawer);
      document.addEventListener('keydown', (ev) => { if (ev.key === 'Escape') closeDrawer(); });
      // Cerrar al navegar desde el drawer
      if (drawerContent) {
        drawerContent.addEventListener('click', (e) => {
          const a = e.target.closest('a');
          if (a && a.getAttribute('href')) { closeDrawer(); }
        });
      }
      // Escuchar cambios en storage para actualizar visibilidad de botones (login vs usuario)
      window.addEventListener('storage', (ev) => {
        if (ev.key === 'userData' || ev.key === 'currentSession') {
          try { AuthSystem.checkUserSession(); } catch (e) {}
        }
      });
      const quick = headerContent.querySelector('#quickLinks');
      const existsCountry = document.querySelectorAll('.country-selector');
      if (existsCountry && existsCountry.length) {
        existsCountry.forEach(el => { try { el.remove(); } catch (_) {} });
      }
    }
  } catch (e) {}
  // Toggle user dropdown safely
  const menuButton = document.querySelector('.menu-button');
  const userDropdown = document.getElementById('userDropdown');
  if (menuButton && userDropdown) {
    menuButton.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = userDropdown.classList.contains('active');
      if (isOpen) {
        userDropdown.classList.remove('active');
        userDropdown.style.display = 'none';
      } else {
        userDropdown.classList.add('active');
        userDropdown.style.display = 'block';
      }
      menuButton.setAttribute('aria-expanded', (!isOpen).toString());
    });

    document.addEventListener('click', (e) => {
      if (userDropdown.classList.contains('active')) {
        const within = userDropdown.contains(e.target) || menuButton.contains(e.target);
        if (!within) {
          userDropdown.classList.remove('active');
          userDropdown.style.display = 'none';
          menuButton.setAttribute('aria-expanded', 'false');
        }
      }
    });
  }

  const mlSearch = document.querySelector('.ml-search');
  const searchForm = mlSearch && mlSearch.querySelector('form');
  const searchInput = mlSearch && mlSearch.querySelector('input[type="search"]');
  const searchBtn = mlSearch && mlSearch.querySelector('button[type="submit"]');
  if (searchForm && searchInput && searchBtn) {
    searchForm.addEventListener('submit', (e) => {
      const q = (searchInput.value || '').trim();
      if (!q.length && window.matchMedia('(max-width: 992px)').matches) {
        e.preventDefault();
        mlSearch.classList.add('expanded');
        searchInput.focus();
      }
    });
    searchBtn.addEventListener('click', (e) => {
      const q = (searchInput.value || '').trim();
      if (!q.length && window.matchMedia('(max-width: 992px)').matches) {
        e.preventDefault();
        mlSearch.classList.toggle('expanded');
        if (mlSearch.classList.contains('expanded')) searchInput.focus();
      }
    });
    document.addEventListener('click', (evt) => {
      if (mlSearch && mlSearch.classList.contains('expanded')) {
        const inside = mlSearch.contains(evt.target);
        if (!inside && window.matchMedia('(max-width: 992px)').matches) mlSearch.classList.remove('expanded');
      }
    });
  }

  // ====== Lógica de autenticación y visibilidad global (carrito / botones login) ======
  const AuthSystem = {
    getSession() {
      const userData = localStorage.getItem('userData');
      const sessionData = sessionStorage.getItem('currentSession');
      let raw = null;
      try { raw = userData ? JSON.parse(userData) : (sessionData ? JSON.parse(sessionData) : null); } catch (e) { raw = null; }
      const obj = raw && raw.user ? raw.user : raw;
      // Aceptar sesión si el contenedor indica login o si existe objeto usuario
      const isTrue = !!(raw && (raw.isLoggedIn === true || raw.isLoggedIn === 'true' || raw.user));
      if (!isTrue) return null;
      // Bloqueados no cuentan como sesión válida
      const blocked = String((obj.estado || '').toLowerCase()) === 'bloqueado' || Boolean(obj.bloqueado);
      if (blocked) return null;
      return obj;
    },
    isLoggedIn(user) {
      if (user) return true;
      try {
        const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
        const raw = rawStr ? JSON.parse(rawStr) : null;
        return !!(raw && (raw.isLoggedIn === true || raw.isLoggedIn === 'true' || raw.user));
      } catch (_) { return false; }
    },
    isBlocked(user) {
      if (!user) return false;
      return String((user.estado || '').toLowerCase()) === 'bloqueado' || Boolean(user.bloqueado);
    },
    sanitizeStorage() {
      // Si hay objetos en storage sin isLoggedIn verdadero, eliminarlos para evitar estados falsos
      try {
        const userData = localStorage.getItem('userData');
        const sessionData = sessionStorage.getItem('currentSession');
        const parse = (s) => { try { return JSON.parse(s || 'null'); } catch (_) { return null; } };
        const u = parse(userData);
        const s = parse(sessionData);
        const valid = (x) => !!(x && (x.isLoggedIn === true || x.isLoggedIn === 'true' || x.user));
        if (userData && !valid(u)) localStorage.removeItem('userData');
        if (sessionData && !valid(s)) sessionStorage.removeItem('currentSession');
      } catch (_) {}
    },
    checkUserSession() {
      const authButtons = document.getElementById('authButtons');
      const userLoggedButtons = document.getElementById('userLoggedButtons');

      this.sanitizeStorage();
      const user = this.getSession();
      const logged = this.isLoggedIn(user);
      const blocked = this.isBlocked(user);

      // La visibilidad de botones la decide el CSS (clase .logged-in en <body>),
      // no estilos inline: así en móvil los botones de acceso se ocultan de inmediato.
      if (logged) {
        document.body.classList.add('logged-in');
        if (authButtons) authButtons.style.display = 'none';
        if (userLoggedButtons) userLoggedButtons.style.display = 'block';
      } else {
        document.body.classList.remove('logged-in');
        if (authButtons) authButtons.style.display = 'block';
        if (userLoggedButtons) userLoggedButtons.style.display = 'none';
      }
    }
  };

  // Exponer para que otras páginas puedan invocarlo
  window.AuthSystem = AuthSystem;
  // Ejecutar al cargar
  try { AuthSystem.checkUserSession(); } catch (e) { /* no-op */ }
  // Actualizar avatar con foto si existe
  try {
    const raw = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
    if (raw) {
      const data = JSON.parse(raw);
      const u = data && (data.user || data);
      // Si no hay sesión válida, no continuar con avatar
      if (!(data && (data.isLoggedIn === true || data.isLoggedIn === 'true' || data.user))) { throw new Error('No active session'); }
      let photo = u && (u.usuario_foto || u.foto || u.picture);
      const name = (u && (u.nombres_completos || u.nombre || u.email)) || 'U';
      const initials = (name.charAt(0) || 'U').toUpperCase();
      const userAvatar = document.getElementById('userAvatar');
      const userAvatarLarge = document.getElementById('userAvatarLarge');
      const resolvePhotoUrl = (url) => {
        if (!url || url.startsWith('http')) return url;
        return url.startsWith('/') ? url : '/' + url.replace(/^\.?\//,'');
      };
      const applyPhoto = (url) => {
        if (!url) return;
        const resolved = resolvePhotoUrl(url);
        if (userAvatar) { userAvatar.style.backgroundImage = `url("${resolved}")`; userAvatar.style.backgroundSize = 'cover'; userAvatar.style.backgroundPosition = 'center'; userAvatar.style.backgroundRepeat = 'no-repeat'; userAvatar.textContent = ''; userAvatar.classList.add('has-photo'); }
        if (userAvatarLarge) { userAvatarLarge.style.backgroundImage = `url("${resolved}")`; userAvatarLarge.style.backgroundSize = 'cover'; userAvatarLarge.style.backgroundPosition = 'center'; userAvatarLarge.style.backgroundRepeat = 'no-repeat'; userAvatarLarge.textContent = ''; userAvatarLarge.classList.add('has-photo'); }
      };
      if (photo) {
        applyPhoto(photo);
      } else {
        if (userAvatar && !userAvatar.textContent) userAvatar.textContent = initials;
        if (userAvatarLarge && !userAvatarLarge.textContent) userAvatarLarge.textContent = initials;
        const email = (u && (u.email || u.correo_electronico)) || null;
        if (email) {
          (async () => {
            try {
              const resp = await fetch('/backend/php/get_user_by_email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ email })
              });
              const result = await resp.json();
              if (result && result.success && result.user && result.user.foto) {
                photo = result.user.foto;
                applyPhoto(photo);
                // Guardar de vuelta en storage para próximas vistas
                try {
                  if (data && data.user) {
                    data.user.usuario_foto = photo;
                    localStorage.setItem('userData', JSON.stringify(data));
                    sessionStorage.setItem('currentSession', JSON.stringify(data));
                  }
                } catch (e) {/* no-op */ }
              }
            } catch (e) { /* silencioso */ }
          })();
        }
      }
    }
  } catch (_) { }

  document.addEventListener('click', function (e) {
    var a = e.target && e.target.closest ? e.target.closest('a') : null;
    if (!a) return;
    var href = a.getAttribute('href') || '';
    var isCartLink = a.classList.contains('mh-cart') || a.id === 'cartButton' || href.indexOf('/carrito-de-compras/') >= 0 || /carrito-de-compras\/index\.php$/i.test(href);
    if (!isCartLink) return;
    try {
      var user = AuthSystem.getSession();
      var logged = AuthSystem.isLoggedIn(user);
      if (!logged) {
        e.preventDefault();
        var goLogin = function(){ window.location.href = 'https://districarnes.online/login/login.php'; };
        if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
          Swal.fire({ icon: 'warning', title: 'Debes iniciar sesión para ver tu carrito', showCancelButton: true, confirmButtonText: 'Iniciar sesión', cancelButtonText: 'Cerrar' }).then(function(r){ if (r && r.isConfirmed) goLogin(); });
        } else if (window.toast) {
          toast({ icon: 'warning', title: 'Inicia sesión para ver tu carrito' });
          setTimeout(goLogin, 300);
        } else {
          goLogin();
        }
      }
    } catch (_) { /* noop */ }
  });

  // Navegación de menú de usuario
  document.addEventListener('click', function (e) {
    var link = e.target && e.target.closest ? e.target.closest('#userDropdown .menu-item') : null;
    if (!link) return;
    var text = (link.textContent || '').toLowerCase();
    var url = null;
    var tab = null;
    if (text.indexOf('mi perfil') >= 0) { url = '/perfil.php'; tab = 'overview'; }
    else if (text.indexOf('editar perfil') >= 0) { url = '/perfil.php'; tab = 'edit'; }
    else if (text.indexOf('cambiar contraseña') >= 0) { url = '/perfil.php'; tab = 'password'; }
    else if (text.indexOf('configuración') >= 0) { url = '/perfil.php'; tab = 'settings'; }
    else if (text.indexOf('historial') >= 0) { url = '/historial.php'; }
    else if (text.indexOf('favoritos') >= 0) { url = '/favoritos.php'; }
    if (url) {
      e.preventDefault();
      var dd = document.getElementById('userDropdown');
      if (dd) { dd.style.display = 'none'; dd.classList.remove('active'); }
      var sep = url.indexOf('?') === -1 ? '?' : '&';
      if (tab) { window.location.href = url + sep + 'tab=' + encodeURIComponent(tab); }
      else { window.location.href = url; }
    }
  });
});

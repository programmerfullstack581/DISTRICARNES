var showToast = (function () {
    var container = null;

    function ensureContainer() {
        if (container) return container;
        container = document.createElement('div');
        container.className = 'toast-container';
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-atomic', 'true');
        document.body.appendChild(container);
        return container;
    }

    function createToast(text, type, options) {
        options = options || {};

        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type + ' toast-enter';

        var icon = document.createElement('span');
        icon.className = 'toast-icon';
        var iconMap = {
            success: 'fas fa-check-circle',
            error: 'fas fa-times-circle',
            info: 'fas fa-info-circle',
            warning: 'fas fa-exclamation-triangle'
        };
        icon.innerHTML = '<i class="' + (iconMap[type] || iconMap.info) + '"></i>';
        toast.appendChild(icon);

        var msg = document.createElement('span');
        msg.className = 'toast-message';
        msg.textContent = text;
        toast.appendChild(msg);

        var close = document.createElement('button');
        close.className = 'toast-close';
        close.setAttribute('aria-label', 'Cerrar');
        close.innerHTML = '&times;';
        toast.appendChild(close);

        var progress = document.createElement('div');
        progress.className = 'toast-progress toast-' + type + '-progress';
        toast.appendChild(progress);

        var duration = options.duration !== undefined ? options.duration : 4000;

        close.addEventListener('click', function () {
            dismiss(toast);
        });

        function dismiss(el) {
            el.classList.remove('toast-enter');
            el.classList.add('toast-exit');
            setTimeout(function () {
                if (el.parentNode) {
                    el.parentNode.removeChild(el);
                }
            }, 300);
        }

        if (duration > 0) {
            toast._hideTimer = setTimeout(function () {
                dismiss(toast);
            }, duration);
        }

        return toast;
    }

    function show(text, type, options) {
        if (!type) type = 'info';
        var toast = createToast(text, type, options);
        var el = ensureContainer();
        el.appendChild(toast);
        return toast;
    }

    function clearAll() {
        if (!container) return;
        var toasts = container.querySelectorAll('.toast');
        toasts.forEach(function (t) {
            if (t._hideTimer) clearTimeout(t._hideTimer);
            t.classList.remove('toast-enter');
            t.classList.add('toast-exit');
            setTimeout(function () {
                if (t.parentNode) t.parentNode.removeChild(t);
            }, 300);
        });
    }

    return show;
})();

window.showToast = showToast;
window.clearAllToasts = function () {
    var container = document.querySelector('.toast-container');
    if (container) {
        var toasts = container.querySelectorAll('.toast');
        toasts.forEach(function (t) {
            if (t._hideTimer) clearTimeout(t._hideTimer);
            container.removeChild(t);
        });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenu = document.getElementById('userMenu');

    if (userMenuBtn && userMenu) {
        userMenuBtn.addEventListener('click', function() {
            userMenu.classList.toggle('user-menu-active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!userMenuBtn.contains(event.target) && !userMenu.contains(event.target)) {
                userMenu.classList.remove('user-menu-active');
            }
        });
    }
});

function handleLogout(e) {
    e.preventDefault();

    const modal = document.createElement('div');
    modal.innerHTML = `
<div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;">
<div style="background: #111; padding: 24px; border-radius: 12px; text-align: center; max-width: 400px; width: 90%; border: 1px solid #333;">
<h3 style="margin-bottom: 15px; color: #fff;">¿Estás seguro que deseas cerrar sesión?</h3>
<div style="display: flex; justify-content: center; gap: 12px;">
<button onclick="confirmLogout()" 
style="background: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
Sí, cerrar sesión
</button>
<button onclick="this.closest('div[style*=\"position: fixed\"]').remove()" 
style="background: #333; color: #fff; padding: 10px 20px; border: 1px solid #555; border-radius: 6px; cursor: pointer; font-weight: 600;">
Cancelar
</button>
</div>
</div>
</div>
`;
    document.body.appendChild(modal);
}

function confirmLogout() {
    try {
        sessionStorage.setItem('logoutFlag', '1');
        localStorage.removeItem('userData');
        sessionStorage.removeItem('currentSession');
        window.dispatchEvent(new CustomEvent('auth:loggedOut'));
    } catch (e) {}

    showToast('¡Sesión cerrada exitosamente! Redirigiendo...', 'success', { duration: 1500 });

    setTimeout(() => {
        var redirectUrl = (window.DISTRICARNES_CONFIG && window.DISTRICARNES_CONFIG.baseUrl)
            ? window.DISTRICARNES_CONFIG.baseUrl + '/index.php'
            : window.location.origin + '/index.php';
        window.location.href = redirectUrl;
    }, 1500);
}
//-----------------------------CARRUSERL DE EQUIPO FUNIONALIDAD-----------------------------

// Funcionalidad del carrusel de equipos
class TeamCarousel {
    constructor() {
        this.currentSlide = 0;
        this.slides = document.querySelectorAll('.team-slide');
        this.dots = document.querySelectorAll('.dot');
        this.prevBtn = document.getElementById('prevBtn');
        this.nextBtn = document.getElementById('nextBtn');
        this.totalSlides = this.slides.length;
        this.autoPlayInterval = null;
        this.isAutoPlaying = true;
        this.autoPlayDelay = 5000; // 5 segudos de retraso entre diapositivas

        this.init();
    }

    init() {
        // Configurar los controladores de eventos
        this.setupEventListeners();

        // Iniciar la reproducción automática cuando la sección sea visible
        this.setupIntersectionObserver();

        // Inicializar la primera diapositiva
        this.showSlide(0);
    }

    setupEventListeners() {
        // Flechas de navegación
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.prevSlide());
        }

        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.nextSlide());
        }

        // Puntos de navegación
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => this.goToSlide(index));
        });

        // Pausa al pasar el ratón por encima
        const carouselContainer = document.querySelector('.team-carousel-container');
        if (carouselContainer) {
            carouselContainer.addEventListener('mouseenter', () => this.pauseAutoPlay());
            carouselContainer.addEventListener('mouseleave', () => this.resumeAutoPlay());
        }

        // Navegación por teclado
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') this.prevSlide();
            if (e.key === 'ArrowRight') this.nextSlide();
        });

        // Compatibilidad con gestos táctiles/de deslizamiento
        this.setupTouchEvents();
    }

    setupIntersectionObserver() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.startAutoPlay();
                } else {
                    this.pauseAutoPlay();
                }
            });
        }, {
            threshold: 0.3
        });

        const teamSection = document.getElementById('team-section');
        if (teamSection) {
            observer.observe(teamSection);
        }
    }

    setupTouchEvents() {
        let startX = 0;
        let endX = 0;

        const carousel = document.querySelector('.team-carousel');
        if (!carousel) return;

        carousel.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
        });

        carousel.addEventListener('touchend', (e) => {
            endX = e.changedTouches[0].clientX;
            this.handleSwipe(startX, endX);
        });
    }

    handleSwipe(startX, endX) {
        const threshold = 50;
        const diff = startX - endX;

        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                this.nextSlide();
            } else {
                this.prevSlide();
            }
        }
    }

    showSlide(index) {
        // Eliminar la clase "active" de todas las diapositivas y los puntos de navegación.
        this.slides.forEach(slide => {
            slide.classList.remove('active', 'prev', 'next');
        });

        this.dots.forEach(dot => {
            dot.classList.remove('active');
        });

        //Añadir la clase "active" a la diapositiva y al punto de navegación actuales
        if (this.slides[index]) {
            this.slides[index].classList.add('active');
        }

        if (this.dots[index]) {
            this.dots[index].classList.add('active');
        }

        // Establecer las clases prev y next para lograr transiciones suaves
        const prevIndex = (index - 1 + this.totalSlides) % this.totalSlides;
        const nextIndex = (index + 1) % this.totalSlides;

        if (this.slides[prevIndex]) {
            this.slides[prevIndex].classList.add('prev');
        }

        if (this.slides[nextIndex]) {
            this.slides[nextIndex].classList.add('next');
        }

        this.currentSlide = index;

        //Activar la animación de entrada
        this.triggerSlideAnimation(index);
    }

    triggerSlideAnimation(index) {
        const slide = this.slides[index];
        if (!slide) return;

        // Reiniciar animación
        const card = slide.querySelector('.team-card-carousel');
        const image = slide.querySelector('.team-image-carousel');
        const glow = slide.querySelector('.card-glow');

        if (card) {
            card.style.animation = 'none';
            card.offsetHeight; //Activar el reflujo
            card.style.animation = 'cardEntrance 1s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards';
        }

        if (image) {
            image.style.animation = 'none';
            image.offsetHeight; //Activar el reflujo
            image.style.animation = 'imageFloat 4s ease-in-out infinite';
        }

        if (glow) {
            glow.style.animation = 'none';
            glow.offsetHeight; //Activar el reflujo
            glow.style.animation = 'glowPulse 3s ease-in-out infinite';
        }
    }

    nextSlide() {
        const nextIndex = (this.currentSlide + 1) % this.totalSlides;
        this.showSlide(nextIndex);
        this.resetAutoPlay();
    }

    prevSlide() {
        const prevIndex = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
        this.showSlide(prevIndex);
        this.resetAutoPlay();
    }

    goToSlide(index) {
        if (index !== this.currentSlide) {
            this.showSlide(index);
            this.resetAutoPlay();
        }
    }

    startAutoPlay() {
        if (this.isAutoPlaying && !this.autoPlayInterval) {
            this.autoPlayInterval = setInterval(() => {
                this.nextSlide();
            }, this.autoPlayDelay);
        }
    }

    pauseAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.autoPlayInterval = null;
        }
    }

    resumeAutoPlay() {
        if (this.isAutoPlaying) {
            this.startAutoPlay();
        }
    }

    resetAutoPlay() {
        this.pauseAutoPlay();
        this.resumeAutoPlay();
    }

    toggleAutoPlay() {
        this.isAutoPlaying = !this.isAutoPlaying;
        if (this.isAutoPlaying) {
            this.startAutoPlay();
        } else {
            this.pauseAutoPlay();
        }
    }
}

// Inicializar el carrusel cuando se haya cargado el DOM
document.addEventListener('DOMContentLoaded', function() {
    // Comprueba si existen los elementos del carrusel del equipo
    const teamCarouselContainer = document.querySelector('.team-carousel-container');
    if (teamCarouselContainer) {
        new TeamCarousel();
    }

    // Añadir desplazamiento suave para mejorar la experiencia del usuario
    document.querySelectorAll('a[href^="# "]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Añadir animación de carga para la sección del equipo
window.addEventListener('load', function() {
    const teamSection = document.getElementById('team-section');
    if (teamSection) {
        teamSection.style.opacity = '0';
        teamSection.style.transform = 'translateY(50px)';
        teamSection.style.transition = 'all 1s ease-out';

        setTimeout(() => {
            teamSection.style.opacity = '1';
            teamSection.style.transform = 'translateY(0)';
        }, 300);
    }
});
//-----------------------------MENU MOVIL-----------------------------
// menú móvil
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.getElementById('mobileToggle');
    const navMenu = document.getElementById('navMenu');
    const authButtons = document.getElementById('authButtons');
    const quickLinks = document.getElementById('quickLinks');
    const userLoggedButtons = document.getElementById('userLoggedButtons');
    const headerContent = document.querySelector('.header-content');
    let authInNav = false;
    let userInNav = false;

    if (!(mobileToggle && navMenu)) return;

    const isMobile = () => window.matchMedia('(max-width: 992px)').matches;
    const closeMenu = () => navMenu.classList.remove('active');
    const toggleMenu = () => navMenu.classList.toggle('active');
    const moveAuthIntoNav = () => {
        if (authButtons && navMenu && !authInNav) {
            navMenu.appendChild(authButtons);
            authInNav = true;
        }
    };
    const restoreAuthToQuickLinks = () => {
        if (authButtons && quickLinks && authInNav) {
            quickLinks.appendChild(authButtons);
            authInNav = false;
        }
    };

    mobileToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        toggleMenu();
        if (navMenu.classList.contains('active')) {
            moveAuthIntoNav();
            document.body.classList.add('menu-open');
        } else {
            restoreAuthToQuickLinks();
            document.body.classList.remove('menu-open');
        }
    });

    // Cierra al hacer click fuera
    document.addEventListener('click', function (e) {
        if (!isMobile()) return;
        if (!navMenu.contains(e.target) && !mobileToggle.contains(e.target)) {
            closeMenu();
            restoreAuthToQuickLinks();
            document.body.classList.remove('menu-open');
        }
    });

    // Cierra al navegar (click en enlace del menú)
    navMenu.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => {
            if (isMobile()) closeMenu();
            if (isMobile()) restoreAuthToQuickLinks();
            if (isMobile()) document.body.classList.remove('menu-open');
        });
    });

    // Cierra con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeMenu();
        if (e.key === 'Escape') restoreAuthToQuickLinks();
        if (e.key === 'Escape') document.body.classList.remove('menu-open');
    });

    // Resetea al cambiar de tamaño
    window.addEventListener('resize', () => {
        if (!isMobile()) {
            // En escritorio nos aseguramos de que no quede "active" colgado
            navMenu.classList.remove('active');
            restoreAuthToQuickLinks();
            document.body.classList.remove('menu-open');
        }
    });

    // Colocar el menú del usuario junto a la lupa en móvil
    function placeUserButtons() {
        if (!userLoggedButtons) return;
        if (window.matchMedia('(max-width: 992px)').matches) {
            // Mostrar solo el botón/trigger, no todo el bloque
            quickLinks && quickLinks.appendChild(userLoggedButtons);
        } else {
            headerContent && headerContent.appendChild(userLoggedButtons);
        }
    }
    placeUserButtons();
    window.addEventListener('resize', placeUserButtons);
});

// ===========================
// Modal de búsqueda global
// ===========================
(function(){
  function ensureSearchModal(){
    if (document.getElementById('searchModalOverlay')) return;
    const overlay = document.createElement('div');
    overlay.id = 'searchModalOverlay';
    overlay.className = 'search-modal-overlay';
    overlay.innerHTML = `
      <div class="search-modal" role="dialog" aria-label="Búsqueda">
        <h3>Buscar productos</h3>
        <div class="search-row">
          <input id="searchModalInput" type="search" placeholder="Buscar productos, marcas y más…" />
          <button id="searchModalGo" aria-label="Buscar">Buscar</button>
        </div>
        <div class="actions">
          <button class="btn-secondary" id="searchModalClose">Cerrar</button>
        </div>
      </div>`;
    document.body.appendChild(overlay);

    const inputEl = document.getElementById('searchModalInput');
    const goBtn = document.getElementById('searchModalGo');
    const closeBtn = document.getElementById('searchModalClose');

    function open(){ 
      overlay.style.display='flex'; 
      setTimeout(()=> inputEl && inputEl.focus(), 50);
    }
    function close(){ overlay.style.display='none'; }
    function isOpen(){ return overlay.style.display === 'flex'; }

    closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', (e)=>{ if(e.target===overlay) close(); });
    inputEl.addEventListener('keydown', (e)=>{
      if(e.key==='Escape') close();
      if(e.key==='Enter') go();
    });
    goBtn.addEventListener('click', go);

    function go(){
      const q = (inputEl.value || '').trim();
      const path = deriveProductsPath();
      const url = new URL(path, window.location.href);
      if(q) url.searchParams.set('q', q);
      window.location.href = url.toString();
    }
    window.SearchModal = { open, close, isOpen };
  }

  function deriveProductsPath(){
    // Detectar si estamos dentro de subcarpeta
    const parts = window.location.pathname.split('/').filter(Boolean);
    const base = parts.length && parts[0] !== 'admin' ? '/' + parts[0] + '/' : '/';
    // Páginas en subcarpetas (checkout/, carrito-de-compras/, etc.)
    if (parts.length >= 2) {
      return base + 'productos.php';
    }
    return base + 'productos.php';
  }

  function shouldUseModal(){
    return window.matchMedia('(max-width: 992px)').matches;
  }

  document.addEventListener('DOMContentLoaded', function(){
    ensureSearchModal();

    document.querySelectorAll('.ml-search form').forEach(form=>{
      const btn = form.querySelector('button[type="submit"]');
      if(!btn) return;
      btn.addEventListener('click', function(e){
        if (shouldUseModal()){
          e.preventDefault();
          if (window.SearchModal) window.SearchModal.open();
        }
      });
      form.addEventListener('submit', function(e){
        if (shouldUseModal()){
          e.preventDefault();
          if (window.SearchModal) window.SearchModal.open();
        }
      });
    });

    const mobileToggle = document.getElementById('mobileToggle');
    if (mobileToggle){
      mobileToggle.addEventListener('click', function(){
        if (window.SearchModal && window.SearchModal.isOpen()) window.SearchModal.close();
      });
    }
    document.querySelectorAll('.menu-button').forEach(btn=>{
      btn.addEventListener('click', function(){
        if (window.SearchModal && window.SearchModal.isOpen()) window.SearchModal.close();
      });
    });
    window.addEventListener('resize', function(){
      if (window.SearchModal && window.SearchModal.isOpen() && !shouldUseModal()){
        window.SearchModal.close();
      }
    });
  });
})();

//-----------------------------USER DROPDOWN MENU-----------------------------
// Función para alternar el menú desplegable del usuario
function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    const profileContainer = dropdown.closest('.user-profile-container');
    
    if (dropdown && profileContainer) {
        dropdown.classList.toggle('show');
        profileContainer.classList.toggle('active');
    }
}

// Cerrar el menú cuando se hace clic fuera de él
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('userDropdown');
    const profileContainer = document.querySelector('.user-profile-container');
    
    if (dropdown && profileContainer) {
        const isClickInsideMenu = profileContainer.contains(event.target);
        
        if (!isClickInsideMenu && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
            profileContainer.classList.remove('active');
        }
    }
});

// Función para actualizar la información del usuario en el menú
function updateUserMenuInfo(userData) {
    // Actualizar avatar pequeño
    const userAvatar = document.getElementById('userAvatar');
    const userName = document.getElementById('userName');
    
    // Actualizar avatar grande y detalles en el dropdown
    const userAvatarLarge = document.getElementById('userAvatarLarge');
    const userFullName = document.getElementById('userFullName');
    const userEmail = document.getElementById('userEmail');
    const userRole = document.getElementById('userRole');
    
    if (userData) {
        const initials = userData.nombre ? userData.nombre.charAt(0).toUpperCase() : 'A';
        
        // Actualizar elementos del botón principal
        if (userAvatar) userAvatar.textContent = initials;
        if (userName) userName.textContent = userData.nombre || 'Administrador';
        
        // Actualizar elementos del dropdown
        if (userAvatarLarge) userAvatarLarge.textContent = initials;
        if (userFullName) userFullName.textContent = userData.nombre || 'Administrador';
        if (userEmail) userEmail.textContent = userData.email || 'admin@districarnes.com';
        if (userRole) userRole.textContent = userData.rol || 'Administrador';
    }
}

/**
 * Sistema de Autenticación Global - DISTRICARNES
 * Este script maneja la autenticación de usuarios en todas las páginas del sitio
 */

(function() {
    // Objeto local para manejar la autenticación
    const AuthSystem = {

    // clave usada por session_guard para marcar cierre de sesión
    LOGOUT_FLAG_KEY: 'logoutFlag',

    // valida que la estructura de sesión sea confiable
    _isValidSession(raw) {
        if (!raw || (raw.isLoggedIn !== true && raw.isLoggedIn !== 'true')) return false;
        const u = raw.user ? raw.user : raw;
        const blocked = String((u && u.estado || '')).toLowerCase() === 'bloqueado' || !!(u && u.bloqueado);
        if (blocked) return false;
        const hasIdentity = !!(u && (u.correo_electronico || u.email || u.telefono || u.celular || u.phone || u.numero_telefono));
        return hasIdentity;
    },

    /**
     * Inicializa el sistema de autenticación
     */
    init: function () {
        this.checkUserSession();
        this.checkLogoutMessage();
        this.setupEventListeners();
    },

    /**
     * Verifica si se debe mostrar mensaje de logout
     */
    checkLogoutMessage: function() {
        if (sessionStorage.getItem('showLogoutMessage') === 'true') {
            sessionStorage.removeItem('showLogoutMessage');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    title: 'Has cerrado sesión correctamente.',
                    icon: 'success',
                    timer: 5000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            }
        }
    },

    /**
     * Verifica si el usuario está logueado
     */
    checkUserSession: function () {
        try {
            // si el guard marcó logout explícito, forzar estado no logueado
            if (sessionStorage.getItem(this.LOGOUT_FLAG_KEY) === '1') {
                this.showLoggedOutState();
                return false;
            }
        } catch (e) { /* noop */ }
        const userData = localStorage.getItem('userData');
        const sessionData = sessionStorage.getItem('currentSession');

        const parse = (s) => {
            try { return s ? JSON.parse(s) : null; } catch (_) { return null; }
        };
        const parsedLocal = parse(userData);
        const parsedSession = parse(sessionData);
        const candidate = this._isValidSession(parsedLocal) ? parsedLocal : (this._isValidSession(parsedSession) ? parsedSession : null);

        if (candidate) {
            this.showLoggedInState(candidate);
            return true;
        }

        this.showLoggedOutState();
        return false;
    },

    /**
     * Muestra el estado de usuario logueado
     */
    showLoggedInState: function (user) {
        // Normalizar estructura: algunos flujos guardan { isLoggedIn, user: { ... } }
        const currentUser = (user && user.user) ? user.user : user;
        // Marcar estado global para CSS
        try { document.body.classList.add('logged-in'); } catch (e) {}
        // Ocultar botones de login/registro
        const authButtons = document.getElementById('authButtons');
        if (authButtons) {
            authButtons.style.display = 'none';
        }
        const drawerAuthButtons = document.getElementById('drawerAuthButtons');
        if (drawerAuthButtons) {
            drawerAuthButtons.style.setProperty('display', 'none', 'important');
        }

        // Mostrar botones de usuario logueado
        const userLoggedButtons = document.getElementById('userLoggedButtons');
        if (userLoggedButtons) {
            userLoggedButtons.style.display = 'block';
        }
        const drawerUserLogged = document.getElementById('drawerUserLogged');
        if (drawerUserLogged) {
            drawerUserLogged.style.display = 'flex';
            drawerUserLogged.style.flexDirection = 'column';
        }
        try {
            const mhLink = document.getElementById('mhUserLink');
            if (mhLink) mhLink.href = '/perfil.php';
        } catch (e) {}

        // Mostrar mensaje de bienvenida
        const welcomeElement = document.getElementById('userWelcome');
        
        // Función para convertir a Title Case (Solo primera letra de cada palabra en mayúscula)
        const toTitleCase = (str) => {
            if (!str) return '';
            // Si es un email, dejarlo como está o solo la parte antes del @
            if (str.includes('@')) return str.toLowerCase();
            
            return str.toLowerCase().split(' ').map(word => {
                return word.charAt(0).toUpperCase() + word.slice(1);
            }).join(' ');
        };

        if (welcomeElement && currentUser) {
            const rawName = currentUser.nombres_completos || currentUser.nombre || currentUser.correo_electronico || currentUser.email || 'Usuario';
            const displayName = toTitleCase(rawName);
            welcomeElement.textContent = `¡Bienvenido, ${displayName}!`;
        }

        // Actualizar elementos del menú de usuario si existen
        if (currentUser) {
            const rawName = currentUser.nombres_completos || currentUser.nombre || currentUser.correo_electronico || currentUser.email || 'Usuario';
            const nameForUI = toTitleCase(rawName);
            const initials = (nameForUI.charAt(0) || 'U').toUpperCase();

            const userAvatar = document.getElementById('userAvatar');
            const userName = document.getElementById('userName');
            const userAvatarLarge = document.getElementById('userAvatarLarge');
            const userFullName = document.getElementById('userFullName');
            const userEmail = document.getElementById('userEmail');
            const userRole = document.getElementById('userRole');
            const mhUserLink = document.getElementById('mhUserLink');
            const mhUserIcon = document.getElementById('mhUserIcon');

            const resolvePhotoUrl = (url) => {
                if (!url) return '';
                if (url.startsWith('http')) return url;
                // usar ruta absoluta desde la raíz del sitio para evitar prefijos como /checkout
                return url.startsWith('/') ? url : '/' + url;
            };
            const applyPhoto = (el, url) => {
                if (!el || !url) return;
                const resolved = resolvePhotoUrl(url);
                el.style.backgroundImage = `url("${resolved}")`;
                el.style.backgroundSize = 'cover';
                el.style.backgroundPosition = 'center';
                el.style.backgroundRepeat = 'no-repeat';
                el.textContent = '';
                el.classList.add('has-photo');
            };
            let photoVal = currentUser.usuario_foto || currentUser.foto || currentUser.picture || '';
            if (photoVal) {
                applyPhoto(userAvatar, photoVal);
                applyPhoto(userAvatarLarge, photoVal);
                applyPhoto(mhUserLink, photoVal);
                if (mhUserIcon) mhUserIcon.style.display = 'none';
            } else {
                if (userAvatar) { userAvatar.textContent = initials; userAvatar.classList.remove('has-photo'); userAvatar.style.backgroundImage = ''; }
                if (userAvatarLarge) { userAvatarLarge.textContent = initials; userAvatarLarge.classList.remove('has-photo'); userAvatarLarge.style.backgroundImage = ''; }
                if (mhUserLink) { mhUserLink.style.backgroundImage = ''; mhUserLink.classList.remove('has-photo'); }
                if (mhUserIcon) { mhUserIcon.style.display = 'inline-block'; }
                const email = currentUser.correo_electronico || currentUser.email || '';
                if (email) {
                    // llamar siempre a la ruta absoluta del backend
                    fetch('/backend/php/auth/get_user_by_email.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ email }) })
                        .then(r => r.json())
                        .then(result => {
                            if (result && result.success && result.user && result.user.foto) {
                                photoVal = result.user.foto;
                                applyPhoto(userAvatar, photoVal);
                                applyPhoto(userAvatarLarge, photoVal);
                                applyPhoto(mhUserLink, photoVal);
                                if (mhUserIcon) mhUserIcon.style.display = 'none';
                                try {
                                    if (user && user.user) {
                                        user.user.usuario_foto = photoVal;
                                        localStorage.setItem('userData', JSON.stringify(user));
                                        sessionStorage.setItem('currentSession', JSON.stringify(user));
                                    }
                                } catch (e) { }
                            }
                        })
                        .catch(() => { });
                }
            }
            if (userName) {
                userName.textContent = nameForUI;
                userName.style.textTransform = 'none';
            }
            if (userFullName) {
                userFullName.textContent = nameForUI;
                userFullName.style.textTransform = 'none';
            }
            if (userEmail) userEmail.textContent = currentUser.correo_electronico || currentUser.email || '';
            if (userRole) userRole.textContent = currentUser.rol ? currentUser.rol.charAt(0).toUpperCase() + currentUser.rol.slice(1) : '';
        }

        // Actualizar cualquier otro elemento específico de la página
        this.updatePageSpecificElements(user, true);
    },

    /**
     * Muestra el estado de usuario no logueado
     */
    showLoggedOutState: function () {
        try { document.body.classList.remove('logged-in'); } catch (e) {}
        // Mostrar botones de login/registro
        const authButtons = document.getElementById('authButtons');
        if (authButtons) {
            authButtons.style.display = 'inline-flex';
        }
        const drawerAuthButtons = document.getElementById('drawerAuthButtons');
        if (drawerAuthButtons) {
            drawerAuthButtons.style.setProperty('display', 'flex', 'important');
        }

        // Ocultar botones de usuario logueado
        const userLoggedButtons = document.getElementById('userLoggedButtons');
        if (userLoggedButtons) {
            userLoggedButtons.style.display = 'none';
        }
        const drawerUserLogged = document.getElementById('drawerUserLogged');
        if (drawerUserLogged) {
            drawerUserLogged.style.display = 'none';
        }
        try {
            const mhLink = document.getElementById('mhUserLink');
            const mhUserIcon = document.getElementById('mhUserIcon');
            if (mhLink) {
                mhLink.href = '/login/login.php';
                mhLink.style.backgroundImage = '';
                mhLink.classList.remove('has-photo');
            }
            if (mhUserIcon) {
                mhUserIcon.style.display = 'inline-block';
            }
        } catch (e) {}

        // Limpiar mensaje de bienvenida
        const welcomeElement = document.getElementById('userWelcome');
        if (welcomeElement) {
            welcomeElement.textContent = '';
        }

        // Actualizar cualquier otro elemento específico de la página
        this.updatePageSpecificElements(null, false);
    },

    /**
     * Actualiza elementos específicos de cada página según el estado de login
     */
    updatePageSpecificElements: function (user, isLoggedIn) {
        // Esta función puede ser extendida para manejar elementos específicos de cada página

        // Ejemplo: Mostrar/ocultar secciones premium
        const premiumSections = document.querySelectorAll('.premium-content');
        premiumSections.forEach(section => {
            section.style.display = isLoggedIn ? 'block' : 'none';
        });

        // Carrito visible solo si el usuario está logueado
        const cartButton = document.getElementById('cartButton');
        if (cartButton) {
            cartButton.style.display = isLoggedIn ? 'inline-flex' : 'none';
        }

        // Ejemplo: Actualizar enlaces de carrito
        const cartLinks = document.querySelectorAll('.cart-link');
        cartLinks.forEach(link => {
            if (isLoggedIn) {
                link.style.opacity = '1';
                link.style.pointerEvents = 'auto';
            } else {
                link.style.opacity = '0.5';
                link.style.pointerEvents = 'none';
            }
        });
    },

    /**
     * Función para cerrar sesión
     */
    logout: function () {
        // Usar SweetAlert2 si está disponible, sino usar confirm nativo
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Cerrar Sesión?',
                text: '¿Estás seguro de que deseas cerrar sesión?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, cerrar sesión',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.performLogout();
                }
            });
        } else {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                this.performLogout();
            }
        }
    },

    /**
     * Ejecuta el proceso de logout
     */
    performLogout: function () {
        // Limpiar datos de sesión de la aplicación
        localStorage.removeItem('userData');
        sessionStorage.removeItem('currentSession');

        // Marcar flag para mostrar mensaje de despedida en la página de destino
        sessionStorage.setItem('showLogoutMessage', 'true');

        // Actualizar UI
        this.showLoggedOutState();

        // Notificar a la app que se cerró sesión
        try {
            window.dispatchEvent(new CustomEvent('auth:loggedOut'));
        } catch (e) { /* noop */ }

        // Redirigir siempre a la página principal
        window.location.href = 'https://districarnes-83qm.onrender.com/index.php';
    },

    /**
     * Configura los event listeners
     */
    setupEventListeners: function () {
        // Listener para cambios en localStorage (para sincronizar entre pestañas)
        window.addEventListener('storage', (e) => {
            if (e.key === 'userData' || e.key === 'currentSession') {
                this.checkUserSession();
            }
        });

        // Listener para el evento de logout personalizado
        window.addEventListener('userLogout', () => {
            this.performLogout();
        });

        // Listener para el evento de login personalizado
        window.addEventListener('userLogin', (e) => {
            this.showLoggedInState(e.detail.user);
        });
    },

    /**
     * Obtiene los datos del usuario actual
     */
    getCurrentUser: function () {
        const userData = localStorage.getItem('userData');
        const sessionData = sessionStorage.getItem('currentSession');

        if (userData || sessionData) {
            try {
                const parse = (s) => { try { return s ? JSON.parse(s) : null; } catch (_) { return null; } };
                const parsedLocal = parse(userData);
                const parsedSession = parse(sessionData);
                const raw = this._isValidSession(parsedLocal) ? parsedLocal : (this._isValidSession(parsedSession) ? parsedSession : null);
                if (!raw) return null;
                return (raw && raw.user) ? raw.user : raw;
            } catch (e) {
                return null;
            }
        }

        return null;
    },

    /**
     * Verifica si el usuario está logueado
     */
    isLoggedIn: function () {
        return this.getCurrentUser() !== null;
    }
};

// Exportar a window para acceso global
window.AuthSystem = AuthSystem;
window.logout = function() {
    AuthSystem.logout();
};

// Inicializar el sistema cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {
    AuthSystem.init();
});

// También inicializar en window.onload como respaldo
window.addEventListener('load', function () {
    AuthSystem.init();
});

})();

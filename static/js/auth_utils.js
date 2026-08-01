// auth_utils.js
// Centralized functions for authentication state management and UI updates.

(function() {
  const LOGOUT_FLAG_KEY = 'logoutFlag'; // Defined in session_guard.js, keep consistent

  // Helper to convert string to Title Case (only first letter of each word in uppercase)
  function toTitleCase(str) {
    if (!str) return '';
    // Si es un email, no transformar
    if (str.includes('@')) return str.toLowerCase();
    
    return str.toLowerCase().split(' ').map(function(word) {
      if (!word) return '';
      return (word.charAt(0).toUpperCase() + word.slice(1));
    }).join(' ');
  }

  // Function to check user session and update header UI
  function checkUserSession() {
    const userData = localStorage.getItem('userData');
    const sessionData = sessionStorage.getItem('currentSession');

    const authButtons = document.getElementById('authButtons');
    const userLoggedButtons = document.getElementById('userLoggedButtons');
    const heroButtons = document.getElementById('userLoggedButtonsHero'); // Assuming this exists on some pages
    const drawerAuth = document.getElementById('drawerAuthButtons');
    const drawerUser = document.getElementById('drawerUserLogged');

    if (userData || sessionData) {
      let raw = null;
      try { raw = JSON.parse(userData || sessionData); } catch (e) { raw = null; }
      if (raw && (raw.isLoggedIn || raw.user)) {
        const currentUser = raw.user ? raw.user : raw;

        // Show logged-in elements, hide auth buttons
        if (authButtons) authButtons.style.display = 'none';
        if (userLoggedButtons) userLoggedButtons.style.display = 'flex';
        if (heroButtons) heroButtons.style.display = 'block'; // Show if exists
        if (drawerAuth) drawerAuth.style.display = 'none';
        if (drawerUser) drawerUser.style.display = 'flex';

        // Populate user data in header (if elements exist)
        const displayNameRaw = currentUser.nombres_completos || currentUser.nombre || currentUser.correo_electronico || currentUser.email || 'Usuario';
        const displayName = toTitleCase(displayNameRaw);
        const displayEmail = currentUser.correo_electronico || currentUser.email || '';
        const displayRole = currentUser.rol || '';
        const initials = (displayName.charAt(0) || 'U').toUpperCase();

  const userAvatar = document.getElementById('userAvatar');
  const userName = document.getElementById('userName');
  if (userName) {
    userName.textContent = displayName;
    userName.style.textTransform = 'none'; // Prevenir CSS force uppercase
  }
  const userAvatarLarge = document.getElementById('userAvatarLarge');
        const userFullName = document.getElementById('userFullName');
        if (userFullName) {
          userFullName.textContent = displayName;
          userFullName.style.textTransform = 'none';
        }
        const userEmail = document.getElementById('userEmail');
        const userRole = document.getElementById('userRole');

  const photo = currentUser.usuario_foto || currentUser.foto || currentUser.picture || '';
  if (photo) {
    if (userAvatar) {
      userAvatar.style.backgroundImage = `url("${photo}")`;
      userAvatar.style.backgroundSize = 'cover';
      userAvatar.style.backgroundPosition = 'center';
      userAvatar.textContent = '';
      userAvatar.classList.add('has-photo');
    }
    if (userAvatarLarge) {
      userAvatarLarge.style.backgroundImage = `url("${photo}")`;
      userAvatarLarge.style.backgroundSize = 'cover';
      userAvatarLarge.style.backgroundPosition = 'center';
      userAvatarLarge.textContent = '';
      userAvatarLarge.classList.add('has-photo');
    }
  } else {
    if (userAvatar) userAvatar.textContent = initials;
    if (userAvatarLarge) userAvatarLarge.textContent = initials;

    // Fallback: consultar al backend si hay foto guardada para este email
    if (displayEmail) {
      try {
        fetch('./backend/php/get_user_by_email.php?email=' + encodeURIComponent(displayEmail))
          .then(r => r.ok ? r.json() : Promise.reject())
          .then(d => {
            if (d && d.success && d.user && d.user.foto) {
              const url = d.user.foto;
              if (userAvatar) {
                userAvatar.style.backgroundImage = `url("${url}")`;
                userAvatar.style.backgroundSize = 'cover';
                userAvatar.style.backgroundPosition = 'center';
                userAvatar.textContent = '';
                userAvatar.classList.add('has-photo');
              }
              if (userAvatarLarge) {
                userAvatarLarge.style.backgroundImage = `url("${url}")`;
                userAvatarLarge.style.backgroundSize = 'cover';
                userAvatarLarge.style.backgroundPosition = 'center';
                userAvatarLarge.textContent = '';
                userAvatarLarge.classList.add('has-photo');
              }
              try {
                const data = userData ? JSON.parse(userData) : (sessionData ? JSON.parse(sessionData) : null);
                if (data) {
                  if (data.user) data.user.usuario_foto = url;
                  else data.usuario_foto = url;
                  if (userData) localStorage.setItem('userData', JSON.stringify(data));
                  else if (sessionData) sessionStorage.setItem('currentSession', JSON.stringify(data));
                }
              } catch (_) {}
            }
          })
          .catch(() => {});
      } catch (_) {}
    }
  }
  if (userName) userName.textContent = displayName;
        if (userFullName) userFullName.textContent = displayName;
        if (userEmail) userEmail.textContent = displayEmail;
        if (userRole) userRole.textContent = displayRole ? displayRole.charAt(0).toUpperCase() + displayRole.slice(1) : '';

        try {
          var mhLink = document.getElementById('mhUserLink');
          var mhIcon = document.getElementById('mhUserIcon');
          if (mhLink) mhLink.href = './perfil.php';
          var photoVal = currentUser.usuario_foto || currentUser.foto || currentUser.picture || '';
          if (photoVal && mhLink) {
            mhLink.style.backgroundImage = 'url("' + photoVal + '")';
            mhLink.style.backgroundSize = 'cover';
            mhLink.style.backgroundPosition = 'center';
            mhLink.style.backgroundRepeat = 'no-repeat';
            if (mhIcon) mhIcon.style.display = 'none';
          } else {
            if (mhLink) mhLink.style.backgroundImage = '';
            if (mhIcon) mhIcon.style.display = 'inline-block';
          }
        } catch (e) {}

        // Welcome message (if element exists)
        const welcomeElement = document.getElementById('userWelcome');
        if (welcomeElement) {
          welcomeElement.textContent = `¡Bienvenido, ${displayName}!`;
        }
        return; // User is logged in, no need to proceed further
      }
    }

    // If not logged in, ensure auth buttons are visible and user elements are hidden
    if (authButtons) authButtons.style.display = 'flex'; // Use flex as it was originally
    if (userLoggedButtons) userLoggedButtons.style.display = 'none';
    if (heroButtons) heroButtons.style.display = 'none';
    if (drawerAuth) drawerAuth.style.display = 'flex';
    if (drawerUser) drawerUser.style.display = 'none';
    try {
      var mhLink = document.getElementById('mhUserLink');
      var mhIcon = document.getElementById('mhUserIcon');
      if (mhLink) {
        mhLink.href = './login/login.php';
        mhLink.style.backgroundImage = '';
      }
      if (mhIcon) mhIcon.style.display = 'inline-block';
    } catch (e) {}
  }

  // Function to handle logout
  function logout() {
    // Clear session data
    localStorage.removeItem('userData');
    sessionStorage.removeItem('currentSession');
    sessionStorage.setItem(LOGOUT_FLAG_KEY, '1'); // Mark as logged out for session_guard

    // Dispatch a custom event to notify other parts of the application
    window.dispatchEvent(new CustomEvent('auth:loggedOut'));

    // Redirect to main site after logout
    window.location.href = 'https://districarnes.online/index.php';
  }

  // Listen for custom events to update UI
  window.addEventListener('auth:loggedOut', () => {
    checkUserSession(); // Update UI after logout
    // Optionally redirect to login page after a short delay
    // setTimeout(() => { window.location.href = './login/login.php'; }, 500);
  });

  window.addEventListener('auth:loggedIn', () => {
    sessionStorage.removeItem(LOGOUT_FLAG_KEY); // Clear logout flag on successful login
    checkUserSession(); // Update UI after login
  });

  // Expose functions globally if needed (e.g., for onclick attributes)
  window.logout = logout;
  window.checkUserSession = checkUserSession;

  // Initial check when the DOM is ready
  document.addEventListener('DOMContentLoaded', checkUserSession);
})();

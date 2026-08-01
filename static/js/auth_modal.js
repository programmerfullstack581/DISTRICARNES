// Modal global de acceso / registro para la tienda DistriCarnes
// Reemplaza los botones "INICIAR SESIÓN" y "REGISTRARSE" del header por un
// único botón "INGRESAR" que abre el modal sin salir de la página.
(function () {
  'use strict';

  // ---------- Rutas base derivadas de la ubicación de este script ----------
  var scriptEl = document.currentScript;
  var src = (scriptEl && scriptEl.getAttribute('src')) || '';
  var BASE = (src.replace(/static\/js\/auth_modal\.js[^/]*$/, '') || './');
  var ENDPOINT_LOGIN = BASE + 'backend/php/login_verify.php';
  var ENDPOINT_REGISTER = BASE + 'backend/php/guardar_usuario.php';
  var ENDPOINT_FORGOT = BASE + 'backend/php/request_password_reset.php';
  var ENDPOINT_CHANGE = BASE + 'backend/php/perform_password_reset.php';
  var URL_PROFILE = BASE + 'perfil.php';
  var URL_ADMIN = 'https://districarnes-83qm.onrender.com/admin/admin_dashboard.html';
  var LOGO_URL = '/assets/icon/LOGO-DISTRICARNES.png';

  var PWD_REGEX = /^(?=.*[A-Z])(?=.*\d)(?=.*[!#$%&])[A-Za-z\d!#$%&]{8,}$/;

  // ---------- Estilos ----------
  var MODAL_CSS =
    '#dcAmOverlay{position:fixed;inset:0;z-index:2147483000;background:rgba(0,0,0,.78);display:flex;align-items:center;justify-content:center;padding:16px;overflow-y:auto}' +
    '#dcAmOverlay.dc-am-open{display:flex}' +
    '#dcAmDialog{position:relative;width:100%;max-width:430px;background:#0c0c0c;border:1px solid #2a2a2a;border-radius:18px;padding:26px 26px 22px;box-shadow:0 24px 60px rgba(0,0,0,.65);animation:dcAmPop .22s ease-out}' +
    '@keyframes dcAmPop{from{opacity:0;transform:translateY(14px) scale(.97)}to{opacity:1;transform:none}}' +
    '#dcAmClose{position:absolute;top:12px;right:14px;background:none;border:none;color:#888;font-size:26px;line-height:1;cursor:pointer;padding:2px 6px;border-radius:8px}' +
    '#dcAmClose:hover{color:#ff0000}' +
    '#dcAmLogo{text-align:center;margin:0 0 6px}' +
    '#dcAmLogo img{max-width:150px;height:auto;max-height:64px;object-fit:contain}' +
    '#dcAmTabs{display:flex;background:#161616;border:1px solid #242424;border-radius:50px;padding:4px;margin:12px 0 18px}' +
    '#dcAmTabs.dcAm-hidden{display:none}' +
    '#dcAmTabs .dcAmTab{flex:1;background:none;border:none;color:#aaa;font-weight:700;font-size:14px;padding:9px 8px;border-radius:50px;cursor:pointer;transition:all .15s}' +
    '#dcAmTabs .dcAmTab.is-active{background:#ff0000;color:#fff;box-shadow:0 4px 14px rgba(255,0,0,.35)}' +
    '.dcAmPanel{display:none}' +
    '.dcAmPanel.is-active{display:block}' +
    '.dcAmField{margin-bottom:13px}' +
    '.dcAmField label{display:block;color:#ccc;font-size:13px;font-weight:600;margin-bottom:5px}' +
    '.dcAmField input{width:100%;background:#101010;border:1px solid #2c2c2c;color:#fff;border-radius:10px;padding:11px 12px;font-size:14px;outline:none;transition:border-color .15s}' +
    '.dcAmField input:focus{border-color:#ff0000}' +
    '.dcAmPass{position:relative}' +
    '.dcAmPass input{padding-right:42px}' +
    '.dcAmEye{position:absolute;top:50%;right:6px;transform:translateY(-50%);background:none;border:none;color:#999;font-size:16px;cursor:pointer;padding:6px}' +
    '.dcAmEye:hover{color:#ff0000}' +
    '.dcAmHint{color:#777;font-size:12px;line-height:1.45;margin:0 0 10px}' +
    '.dcAmError{display:none;color:#ff6b6b;font-size:13px;line-height:1.4;margin:0 0 12px;padding:9px 11px;background:rgba(255,0,0,.08);border:1px solid rgba(255,0,0,.3);border-radius:8px}' +
    '.dcAmError.dcAm-show{display:block}' +
    '.dcAmError.dcAm-ok{color:#7ee2a0;background:rgba(46,204,113,.1);border-color:rgba(46,204,113,.35)}' +
    '.dcAmSubmit{width:100%;background:#ff0000;color:#fff;border:2px solid #ff0000;border-radius:50px;font-weight:800;font-size:15px;letter-spacing:.6px;padding:12px 14px;cursor:pointer;transition:all .15s}' +
    '.dcAmSubmit:hover{background:#000;color:#fff}' +
    '.dcAmSubmit:disabled{opacity:.55;cursor:not-allowed}' +
    '.dcAmFoot{text-align:center;margin-top:12px}' +
    '.dcAmLink{color:#ff6b6b;text-decoration:none;font-size:13px}' +
    '.dcAmLink:hover{text-decoration:underline;color:#ff0000}' +
    '.dcAmSwitch{color:#999;font-size:13px;text-align:center;margin:14px 0 0}' +
    '.dcAmSwitch a{color:#ff0000;text-decoration:none;font-weight:700}' +
    '.dcAmSwitch a:hover{text-decoration:underline}' +
    '.dcAmRow{display:grid;grid-template-columns:1fr 1fr;gap:12px}' +
    '@media(max-width:400px){.dcAmRow{grid-template-columns:1fr}}' +
    '#authButtons .dcAmHeaderBtn{background:#ff0000;color:#fff;border:2px solid #ff0000;border-radius:50px;font-weight:800;font-size:13px;padding:9px 18px;cursor:pointer;transition:all .15s;display:inline-flex;align-items:center;gap:7px}' +
    '#authButtons .dcAmHeaderBtn:hover{background:#000;color:#fff}' +
    '#drawerAuthButtons .dcAmHeaderBtn{background:#ff0000;color:#fff;border:2px solid #ff0000;border-radius:999px;font-weight:800;font-size:14px;padding:10px 14px;cursor:pointer;transition:all .15s;display:flex;align-items:center;justify-content:center;gap:8px;width:100%}' +
    '#drawerAuthButtons .dcAmHeaderBtn:hover{background:#000;color:#fff}';

  // ---------- Estructura del modal ----------
  var MODAL_HTML =
    '<div id="dcAmOverlay" class="dc-am-overlay" style="display:none" aria-hidden="true">' +
      '<div id="dcAmDialog" role="dialog" aria-modal="true" aria-labelledby="dcAmTitle">' +
        '<button type="button" id="dcAmClose" aria-label="Cerrar">&times;</button>' +
        '<div id="dcAmLogo"><img src="' + LOGO_URL + '" alt="DistriCarnes"></div>' +
        '<div id="dcAmTabs" role="tablist">' +
          '<button type="button" class="dcAmTab is-active" data-dcAmTab="login" role="tab">Iniciar sesión</button>' +
          '<button type="button" class="dcAmTab" data-dcAmTab="register" role="tab">Registrarse</button>' +
        '</div>' +
        '<form id="dcAmLogin" class="dcAmPanel is-active" novalidate>' +
          '<div class="dcAmField">' +
            '<label for="dcAmEmail">Correo electrónico</label>' +
            '<input id="dcAmEmail" name="email" type="email" autocomplete="email" placeholder="tu.correo@example.com" required>' +
          '</div>' +
          '<div class="dcAmField">' +
            '<label for="dcAmPass">Contraseña</label>' +
            '<div class="dcAmPass">' +
              '<input id="dcAmPass" name="password" type="password" autocomplete="current-password" placeholder="Tu contraseña" required>' +
              '<button type="button" class="dcAmEye" data-dcAmEye="dcAmPass" aria-label="Mostrar u ocultar contraseña"><i class="bi bi-eye"></i></button>' +
            '</div>' +
          '</div>' +
          '<p class="dcAmError" data-dcAmError="login"></p>' +
          '<button type="submit" class="dcAmSubmit" data-dcAmSubmit="login"><i class="bi bi-box-arrow-in-right"></i> INGRESAR</button>' +
          '<div class="dcAmFoot"><a href="#" data-dcAmGoto="forgot" class="dcAmLink">¿Olvidaste tu contraseña?</a></div>' +
          '<p class="dcAmSwitch">¿No tienes cuenta? <a href="#" data-dcAmGoto="register">Regístrate aquí</a></p>' +
        '</form>' +
        '<form id="dcAmForgot" class="dcAmPanel" novalidate>' +
          '<div class="dcAmField">' +
            '<label for="dcAmForgotEmail">Correo electrónico</label>' +
            '<input id="dcAmForgotEmail" name="email" type="email" autocomplete="email" placeholder="tu.correo@example.com" required>' +
          '</div>' +
          '<p class="dcAmHint">Te enviaremos un enlace para restablecer tu contraseña. Revisa también tu bandeja de spam.</p>' +
          '<p class="dcAmError" data-dcAmError="forgot"></p>' +
          '<button type="submit" class="dcAmSubmit" data-dcAmSubmit="forgot"><i class="bi bi-envelope"></i> ENVIAR ENLACE</button>' +
          '<p class="dcAmSwitch"><a href="#" data-dcAmGoto="login">Volver al inicio de sesión</a></p>' +
        '</form>' +
        '<form id="dcAmChange" class="dcAmPanel" novalidate>' +
          '<input type="hidden" id="dcAmChangeToken" name="token">' +
          '<div class="dcAmField">' +
            '<label for="dcAmChangePass">Nueva contraseña</label>' +
            '<div class="dcAmPass">' +
              '<input id="dcAmChangePass" name="password" type="password" autocomplete="new-password" placeholder="Tu nueva contraseña" required>' +
              '<button type="button" class="dcAmEye" data-dcAmEye="dcAmChangePass" aria-label="Mostrar u ocultar contraseña"><i class="bi bi-eye"></i></button>' +
            '</div>' +
          '</div>' +
          '<div class="dcAmField">' +
            '<label for="dcAmChangeConfirm">Confirmar contraseña</label>' +
            '<div class="dcAmPass">' +
              '<input id="dcAmChangeConfirm" name="confirm" type="password" autocomplete="new-password" placeholder="Repite la contraseña" required>' +
              '<button type="button" class="dcAmEye" data-dcAmEye="dcAmChangeConfirm" aria-label="Mostrar u ocultar contraseña"><i class="bi bi-eye"></i></button>' +
            '</div>' +
          '</div>' +
          '<p class="dcAmHint">La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial (ej: !#$%&amp;).</p>' +
          '<p class="dcAmError" data-dcAmError="change"></p>' +
          '<button type="submit" class="dcAmSubmit" data-dcAmSubmit="change"><i class="bi bi-key-fill"></i> CAMBIAR CONTRASEÑA</button>' +
          '<p class="dcAmSwitch"><a href="#" data-dcAmGoto="login">Volver al inicio de sesión</a></p>' +
        '</form>' +
        '<form id="dcAmRegister" class="dcAmPanel" novalidate>' +
          '<div class="dcAmRow">' +
            '<div class="dcAmField">' +
              '<label for="dcAmNombre">Nombres Completos</label>' +
              '<input id="dcAmNombre" name="nombre" type="text" autocomplete="name" placeholder="Ej: Ana Sofía" required>' +
            '</div>' +
            '<div class="dcAmField">' +
              '<label for="dcAmCedula">Cédula</label>' +
              '<input id="dcAmCedula" name="cedula" type="text" autocomplete="off" placeholder="N° de identificación" required>' +
            '</div>' +
          '</div>' +
          '<div class="dcAmRow">' +
            '<div class="dcAmField">' +
              '<label for="dcAmDireccion">Dirección</label>' +
              '<input id="dcAmDireccion" name="direccion" type="text" autocomplete="street-address" placeholder="Ej: Calle 10 # 20-30" required>' +
            '</div>' +
            '<div class="dcAmField">' +
              '<label for="dcAmCelular">Celular</label>' +
              '<input id="dcAmCelular" name="celular" type="tel" autocomplete="tel" placeholder="Ej: 3001234567" required>' +
            '</div>' +
          '</div>' +
          '<div class="dcAmField">' +
            '<label for="dcAmRegEmail">Correo Electrónico</label>' +
            '<input id="dcAmRegEmail" name="email" type="email" autocomplete="email" placeholder="tu.correo@example.com" required>' +
          '</div>' +
          '<div class="dcAmField">' +
            '<label for="dcAmRegPass">Contraseña</label>' +
            '<div class="dcAmPass">' +
              '<input id="dcAmRegPass" name="contrasena" type="password" autocomplete="new-password" placeholder="Crea una contraseña segura" required>' +
              '<button type="button" class="dcAmEye" data-dcAmEye="dcAmRegPass" aria-label="Mostrar u ocultar contraseña"><i class="bi bi-eye"></i></button>' +
            '</div>' +
          '</div>' +
          '<p class="dcAmHint">La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial (ej: !#$%&amp;).</p>' +
          '<p class="dcAmError" data-dcAmError="register"></p>' +
          '<button type="submit" class="dcAmSubmit" data-dcAmSubmit="register"><i class="bi bi-person-plus-fill"></i> REGISTRARSE</button>' +
          '<p class="dcAmSwitch">¿Ya tienes cuenta? <a href="#" data-dcAmGoto="login">Inicia sesión</a></p>' +
        '</form>' +
      '</div>' +
    '</div>';

  var overlay, dialog, loginPanel, registerPanel, forgotPanel, changePanel;

  function $(id) { return document.getElementById(id); }

  function escHtml(s) {
    return String(s || '').replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function hasSession() {
    try {
      if (window.AuthSystem && typeof AuthSystem.isLoggedIn === 'function') return AuthSystem.isLoggedIn();
      var raw = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
      if (!raw) return false;
      var d = JSON.parse(raw);
      return !!(d && (d.isLoggedIn === true || d.isLoggedIn === 'true' || d.user));
    } catch (e) { return false; }
  }

  function injectCss() {
    if (document.getElementById('dcAmCss')) return;
    var style = document.createElement('style');
    style.id = 'dcAmCss';
    style.type = 'text/css';
    style.textContent = MODAL_CSS;
    document.head.appendChild(style);
  }

  function injectModal() {
    if (document.getElementById('dcAmOverlay')) return;
    var div = document.createElement('div');
    div.innerHTML = MODAL_HTML;
    document.body.appendChild(div.firstElementChild);
  }

  function openModal(tab) {
    if (!overlay) return;
    overlay.style.display = 'flex';
    overlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    switchTab(tab === 'register' || tab === 'forgot' || tab === 'change' ? tab : 'login');
    var focus = tab === 'register' ? $('dcAmNombre') : (tab === 'forgot' ? $('dcAmForgotEmail') : (tab === 'change' ? $('dcAmChangePass') : $('dcAmEmail')));
    if (focus) setTimeout(function () { focus.focus(); }, 60);
  }

  function closeModal() {
    if (!overlay) return;
    overlay.style.display = 'none';
    overlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  function switchTab(tab) {
    var tabs = overlay.querySelectorAll('.dcAmTab');
    for (var i = 0; i < tabs.length; i++) {
      var on = tabs[i].getAttribute('data-dcAmTab') === tab;
      tabs[i].classList.toggle('is-active', on);
      tabs[i].setAttribute('aria-selected', on ? 'true' : 'false');
    }
    var tabsBar = $('dcAmTabs');
    if (tabsBar) tabsBar.classList.toggle('dcAm-hidden', tab === 'forgot' || tab === 'change');
    var loginOn = tab === 'login';
    var regOn = tab === 'register';
    loginPanel.classList.toggle('is-active', loginOn);
    registerPanel.classList.toggle('is-active', regOn);
    forgotPanel.classList.toggle('is-active', tab === 'forgot');
    changePanel.classList.toggle('is-active', tab === 'change');
    clearErrors();
  }

  function showError(name, msg, isOk) {
    var el = overlay.querySelector('[data-dcAmError="' + name + '"]');
    if (!el) return;
    el.textContent = msg || '';
    el.classList.toggle('dcAm-show', !!msg);
    el.classList.toggle('dcAm-ok', !!isOk);
  }

  function clearErrors() {
    var els = overlay.querySelectorAll('.dcAmError');
    for (var i = 0; i < els.length; i++) {
      els[i].textContent = '';
      els[i].classList.remove('dcAm-show', 'dcAm-ok');
    }
  }

  function setBusy(name, busy, text) {
    var btn = overlay.querySelector('[data-dcAmSubmit="' + name + '"]');
    if (!btn) return;
    btn.disabled = busy;
    btn.innerHTML = busy ? text : (name === 'login'
      ? '<i class="bi bi-box-arrow-in-right"></i> INGRESAR'
      : name === 'forgot'
        ? '<i class="bi bi-envelope"></i> ENVIAR ENLACE'
        : name === 'change'
          ? '<i class="bi bi-key-fill"></i> CAMBIAR CONTRASEÑA'
          : '<i class="bi bi-person-plus-fill"></i> REGISTRARSE');
  }

  function persistSession(user) {
    var session = {
      user: {
        id: user.id,
        nombre: user.nombre,
        email: user.email,
        rol: user.rol,
        nombres_completos: user.nombre
      },
      isLoggedIn: true,
      loginTime: Date.now()
    };
    try {
      localStorage.setItem('userData', JSON.stringify(session));
      sessionStorage.setItem('currentSession', JSON.stringify(session));
    } catch (e) { /* sin permisos de storage */ }
  }

  function redirectAfterLogin(user) {
    var pending = null;
    try { pending = sessionStorage.getItem('postLoginRedirect'); sessionStorage.removeItem('postLoginRedirect'); } catch (e) {}
    if (pending) { window.location.href = pending; return; }
    if (user && String(user.rol || '').toLowerCase() === 'admin') {
      window.location.href = URL_ADMIN;
    } else {
      window.location.href = URL_PROFILE;
    }
  }

  // ---------- Acciones ----------
  async function handleLogin(e) {
    e.preventDefault();
    var email = ($('dcAmEmail').value || '').trim();
    var pass = ($('dcAmPass').value || '');
    if (!email || !pass) { showError('login', 'Por favor, completa todos los campos.'); return; }
    setBusy('login', true, 'Ingresando…');
    try {
      var res = await fetch(ENDPOINT_LOGIN, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ email: email, password: pass })
      });
      var data = await res.json();
      if (data.success && data.user) {
        persistSession(data.user);
        closeModal();
        try { if (window.AuthSystem) AuthSystem.checkUserSession(); } catch (err) {}
        document.dispatchEvent(new CustomEvent('auth:loggedIn', { detail: data.user }));
        setTimeout(function () { redirectAfterLogin(data.user); }, 250);
      } else {
        showError('login', data.message || 'No se pudo iniciar sesión.');
      }
    } catch (err) {
      showError('login', 'Error de conexión. Verifica tu internet e intenta de nuevo.');
    } finally {
      setBusy('login', false);
    }
  }

  async function handleRegister(e) {
    e.preventDefault();
    var fields = {
      nombre: ($('dcAmNombre').value || '').trim(),
      cedula: ($('dcAmCedula').value || '').trim(),
      direccion: ($('dcAmDireccion').value || '').trim(),
      celular: ($('dcAmCelular').value || '').trim(),
      email: ($('dcAmRegEmail').value || '').trim(),
      contrasena: ($('dcAmRegPass').value || '')
    };
    for (var k in fields) {
      if (!fields[k]) { showError('register', 'Todos los campos son obligatorios.'); return; }
    }
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(fields.email)) {
      showError('register', 'Ingresa un correo electrónico válido.');
      return;
    }
    if (!PWD_REGEX.test(fields.contrasena)) {
      showError('register', 'La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial (ej: !#$%&).');
      return;
    }
    setBusy('register', true, 'Registrando…');
    try {
      var body = new URLSearchParams();
      for (var key in fields) body.append(key, fields[key]);
      var res = await fetch(ENDPOINT_REGISTER, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
      });
      var data = await res.json();
      if (data.success) {
        clearErrors();
        switchTab('login');
        $('dcAmEmail').value = fields.email;
        $('dcAmPass').value = '';
        showError('login', 'Cuenta creada. Revisa tu correo para verificar tu cuenta y luego inicia sesión.', true);
      } else {
        showError('register', data.message || 'No se pudo completar el registro.');
      }
    } catch (err) {
      showError('register', 'Error de conexión. Verifica tu internet e intenta de nuevo.');
    } finally {
      setBusy('register', false);
    }
  }

  async function handleForgot(e) {
    e.preventDefault();
    var email = ($('dcAmForgotEmail').value || '').trim();
    if (!email) { showError('forgot', 'Ingresa tu correo electrónico.'); return; }
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
      showError('forgot', 'Ingresa un correo electrónico válido.');
      return;
    }
    setBusy('forgot', true, 'Enviando…');
    try {
      var res = await fetch(ENDPOINT_FORGOT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ email: email })
      });
      var data = await res.json();
      var msgEl = overlay.querySelector('[data-dcAmError="forgot"]');
      if (!msgEl) return;
      var html = escHtml(data.message || (data.success ? 'Te enviamos el enlace para restablecer la contraseña.' : 'No se pudo enviar el enlace.'));
      if (data.reset_url) {
        html += ' <a href="' + escHtml(data.reset_url) + '" target="_blank" rel="noopener noreferrer" style="color:#7ee2a0;text-decoration:underline;font-weight:700">Abrir enlace de restablecimiento</a>';
      }
      msgEl.innerHTML = html;
      msgEl.classList.toggle('dcAm-show', true);
      msgEl.classList.toggle('dcAm-ok', !!data.success);
    } catch (err) {
      showError('forgot', 'Error de conexión. Verifica tu internet e intenta de nuevo.');
    } finally {
      setBusy('forgot', false);
    }
  }

  async function handleChange(e) {
    e.preventDefault();
    var token = ($('dcAmChangeToken').value || '').trim();
    var pass = ($('dcAmChangePass').value || '');
    var confirm = ($('dcAmChangeConfirm').value || '');
    if (!token) { showError('change', 'Falta el token de recuperación. Solicita un nuevo enlace.'); return; }
    if (pass.length < 8 || !PWD_REGEX.test(pass)) {
      showError('change', 'La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial (ej: !#$%&).');
      return;
    }
    if (pass !== confirm) {
      showError('change', 'Las contraseñas no coinciden.');
      return;
    }
    setBusy('change', true, 'Guardando…');
    try {
      var res = await fetch(ENDPOINT_CHANGE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ token: token, password: pass })
      });
      var data = await res.json();
      var msgEl = overlay.querySelector('[data-dcAmError="change"]');
      if (!msgEl) return;
      if (data.success) {
        msgEl.innerHTML = escHtml(data.message || 'Contraseña actualizada correctamente.');
        msgEl.classList.toggle('dcAm-show', true);
        msgEl.classList.toggle('dcAm-ok', true);
        setBusy('change', false);
        setTimeout(function () {
          window.location.href = BASE + 'index.php';
        }, 2000);
      } else {
        showError('change', data.message || 'No se pudo actualizar la contraseña.');
        setBusy('change', false);
      }
    } catch (err) {
      showError('change', 'Error de conexión. Verifica tu internet e intenta de nuevo.');
      setBusy('change', false);
    }
  }

  // ---------- Reemplazo de botones del header y del drawer ----------
  function replaceAuthButtons() {
    var desktop = $('authButtons');
    if (desktop) {
      desktop.className = 'flex gap-3';
      desktop.style.display = 'none';
      desktop.innerHTML = '<button type="button" class="dcAmHeaderBtn" data-dcAmOpen="login"><i class="bi bi-box-arrow-in-right" style="font-size:1.4rem"></i> INGRESAR</button>';
    }
    var drawer = $('drawerAuthButtons');
    if (drawer) {
      drawer.innerHTML = '<button type="button" class="dcAmHeaderBtn" data-dcAmOpen="login"><i class="bi bi-box-arrow-in-right"></i> INGRESAR</button>';
    }
  }

  function bindEvents() {
    var closeBtn = $('dcAmClose');
    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) closeModal();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && overlay.style.display !== 'none') closeModal();
    });

    var tabs = overlay.querySelectorAll('.dcAmTab');
    for (var i = 0; i < tabs.length; i++) {
      tabs[i].addEventListener('click', function () {
        switchTab(this.getAttribute('data-dcAmTab'));
      });
    }

    var gotos = overlay.querySelectorAll('[data-dcAmGoto]');
    for (var j = 0; j < gotos.length; j++) {
      gotos[j].addEventListener('click', function (e) {
        e.preventDefault();
        switchTab(this.getAttribute('data-dcAmGoto'));
      });
    }

    var eyes = overlay.querySelectorAll('[data-dcAmEye]');
    for (var k = 0; k < eyes.length; k++) {
      eyes[k].addEventListener('click', function () {
        var input = $(this.getAttribute('data-dcAmEye'));
        if (!input) return;
        var hidden = input.type === 'password';
        input.type = hidden ? 'text' : 'password';
        this.innerHTML = hidden ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
      });
    }

    $('dcAmLogin').addEventListener('submit', handleLogin);
    $('dcAmRegister').addEventListener('submit', handleRegister);
    $('dcAmForgot').addEventListener('submit', handleForgot);
    $('dcAmChange').addEventListener('submit', handleChange);

    document.addEventListener('click', function (e) {
      var link = e.target && e.target.closest ? e.target.closest('#mhUserLink') : null;
      if (!link || hasSession()) return;
      e.preventDefault();
      openModal('login');
    });

    document.addEventListener('click', function (e) {
      var btn = e.target && e.target.closest ? e.target.closest('[data-dcAmOpen]') : null;
      if (btn) {
        e.preventDefault();
        var body = document.body;
        if (body.classList.contains('drawer-open')) body.classList.remove('drawer-open');
        openModal(btn.getAttribute('data-dcAmOpen') || 'login');
      }
    });
  }

  // ---------- Arranque ----------
  function init() {
    injectCss();
    injectModal();
    overlay = $('dcAmOverlay');
    dialog = $('dcAmDialog');
    loginPanel = $('dcAmLogin');
    registerPanel = $('dcAmRegister');
    forgotPanel = $('dcAmForgot');
    changePanel = $('dcAmChange');
    replaceAuthButtons();
    bindEvents();
    try { if (window.AuthSystem) AuthSystem.checkUserSession(); } catch (e) {}
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  window.openAuthModal = openModal;
  window.closeAuthModal = closeModal;
  window.openChangeModal = function (token) {
    var t = $('dcAmChangeToken');
    if (t) t.value = token || '';
    var pass = $('dcAmChangePass');
    var confirm = $('dcAmChangeConfirm');
    if (pass) pass.value = '';
    if (confirm) confirm.value = '';
    openModal('change');
  };
  window.goAuth = function (tab, fallbackUrl) {
    if (typeof window.openAuthModal === 'function') { window.openAuthModal(tab); return; }
    if (fallbackUrl) window.location.href = fallbackUrl;
  };
})();

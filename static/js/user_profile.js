(function () {
  function el(id) { return document.getElementById(id); }

  function toTitleCase(str) {
    if (!str) return '';
    return str.toLowerCase().split(' ').map(function(word) {
      return (word.charAt(0).toUpperCase() + word.slice(1));
    }).join(' ');
  }

  function showTab(name) {
    var tabs = ['overview', 'edit', 'password', 'settings'];
    for (var i = 0; i < tabs.length; i++) {
      var c = el('tab-' + tabs[i]);
      if (c) c.className = (tabs[i] === name ? 'content active' : 'content');
    }
    var btns = document.querySelectorAll('.tabs button');
    for (var j = 0; j < btns.length; j++) {
      var b = btns[j];
      var t = b.getAttribute('data-tab');
      b.className = (t === name ? 'active' : '');
    }
  }
  function getInitialTab() {
    var allowed = ['overview', 'edit', 'password', 'settings'];
    var tab = 'overview';
    try {
      var qs = new URLSearchParams(window.location.search);
      var t1 = qs.get('tab');
      if (t1 && allowed.indexOf(t1) >= 0) { tab = t1; }
      else if (window.location.hash) {
        var t2 = window.location.hash.replace('#', '');
        if (allowed.indexOf(t2) >= 0) { tab = t2; }
      }
    } catch (e) { }
    return tab;
  }
  function getSessionUser() {
    try {
      var raw = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
      if (!raw) return null;
      var data = JSON.parse(raw);
      if (data && data.isLoggedIn) { return data.user ? data.user : data; }
      return null;
    } catch (e) { return null; }
  }
  function ensureServerSession() {
    return new Promise(function(resolve){
      try {
        var u = getSessionUser();
        if (!u) { resolve(false); return; }
        var id = u.id || u.id_usuario;
        var email = u.correo_electronico || u.email;
        if (!id || !email) { resolve(false); return; }
        fetch('/backend/php/auth/ensure_session.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ id: String(id), email: String(email) })
        }).then(function(r){ return r.json(); })
        .then(function(d){ resolve(!!(d && d.success)); })
        .catch(function(){ resolve(false); });
      } catch (e) { resolve(false); }
    });
  }
  function loadProfile() {
    var u = getSessionUser();
    if (!u) { try { if (typeof window.openAuthModal === 'function') { try { sessionStorage.setItem('postLoginRedirect', location.pathname + location.search); } catch (e) {} window.openAuthModal('login'); } else { location.assign('./login/login.php'); } } catch (e) { } return; }
    var nameRaw = u.nombres_completos || u.nombre || 'Usuario';
    el('ovName').textContent = toTitleCase(nameRaw);
    el('ovEmail').textContent = u.correo_electronico || u.email || '';
    el('ovRole').textContent = u.rol || '';
    var initials = String(el('ovName').textContent || 'U').charAt(0).toUpperCase();
    var av = document.getElementById('profileInitial'); if (av) av.textContent = initials;
    // Previsualizar foto si existe
    var photo = u.usuario_foto || u.foto || u.picture || '';
    var prev = document.getElementById('avatarPreview');
    var prevO = document.getElementById('avatarPreviewOverview');
    if (photo) {
      if (prev) { prev.style.backgroundImage = 'url(\"' + photo + '\")'; prev.textContent = ''; }
      if (prevO) { prevO.style.backgroundImage = 'url(\"' + photo + '\")'; if (av) av.textContent = ''; }
    } else {
      if (prev) { prev.style.backgroundImage = ''; prev.textContent = initials; }
      if (prevO) { prevO.style.backgroundImage = ''; }
    }
    el('fullName').value = toTitleCase(nameRaw);
    el('email').value = u.correo_electronico || u.email || '';
  }
  function reloadServerProfile() {
    fetch('./backend/php/user/user_profile_manage.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'get_profile' })
    }).then(function (r) { return r.json(); })
      .then(function (d) {
        if (d && d.success && d.user) {
          var nameRaw = d.user.nombres_completos || 'Usuario';
          el('ovName').textContent = toTitleCase(nameRaw);
          el('ovEmail').textContent = d.user.correo_electronico || '';
          el('ovRole').textContent = d.user.rol || '';
          el('fullName').value = toTitleCase(nameRaw);
          el('email').value = d.user.correo_electronico || '';
          var av = document.getElementById('profileInitial'); if (av) { av.textContent = String(el('ovName').textContent || 'U').charAt(0).toUpperCase(); }
        }
      }).catch(function () { });
  }
  function bindTabs() {
    var btns = document.querySelectorAll('.tabs button');
    for (var i = 0; i < btns.length; i++) {
      btns[i].addEventListener('click', function () {
        var t = this.getAttribute('data-tab'); showTab(t);
      });
    }
  }
  function bindEdit() {
    var saveBtn = el('saveProfile');
    var resetBtn = el('resetProfile');
    var alertBox = el('editAlert');
    var inputFile = el('profilePhoto');
    var uploadBtn = el('uploadPhoto');
    var prev = document.getElementById('avatarPreview');
    // Vista previa local
    if (inputFile) {
      inputFile.addEventListener('change', function () {
        var f = this.files && this.files[0];
        if (!f) return;
        if (f.size > 2 * 1024 * 1024) { if (alertBox) alertBox.textContent = 'La imagen supera 2MB.'; if (typeof Swal !== 'undefined') { Swal.fire({icon:'warning',title:'Archivo demasiado grande',text:'La imagen no debe superar 2MB.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'});} this.value = ''; return; }
        var url = URL.createObjectURL(f);
        if (prev) { prev.style.backgroundImage = 'url(\"' + url + '\")'; prev.textContent = ''; }
      });
    }
    if (uploadBtn) {
      uploadBtn.addEventListener('click', function () {
        if (!inputFile || !inputFile.files || !inputFile.files[0]) { if (alertBox) alertBox.textContent = 'Selecciona una imagen.'; if (typeof Swal !== 'undefined') { Swal.fire({icon:'warning',title:'Sin imagen',text:'Selecciona una imagen antes de subir.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'});} return; }
        var fd = new FormData();
        fd.append('photo', inputFile.files[0]);
        fetch('/backend/php/user/profile_photo_upload.php', { method: 'POST', body: fd })
          .then(function (r) { return r.json(); })
          .then(function (d) {
            if (d && d.success && d.url) {
              if (alertBox) alertBox.textContent = 'Foto actualizada.';
              if (typeof Swal !== 'undefined') { Swal.fire({icon:'success',title:'Foto actualizada',text:'Tu foto de perfil se actualizó correctamente.',timer:1800,showConfirmButton:false,background:'#1a1a1a',color:'#ffffff'}); }
              // Actualizar storage
              try {
                var raw = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                if (raw) {
                  var data = JSON.parse(raw);
                  var u = data.user ? data.user : data;
                  u.usuario_foto = d.url;
                  if (data.user) data.user = u; else data = u;
                  localStorage.setItem('userData', JSON.stringify(data));
                  sessionStorage.setItem('currentSession', JSON.stringify(data));
                }
              } catch (e) { }
              // Actualizar vistas
              var prevO = document.getElementById('avatarPreviewOverview');
              if (prev) { prev.style.backgroundImage = 'url(\"' + d.url + '\")'; prev.textContent = ''; }
              if (prevO) { prevO.style.backgroundImage = 'url(\"' + d.url + '\")'; }
              var userAvatar = document.getElementById('userAvatar');
              var userAvatarLarge = document.getElementById('userAvatarLarge');
              if (userAvatar) { userAvatar.style.backgroundImage = 'url(\"' + d.url + '\")'; userAvatar.textContent = ''; userAvatar.classList.add('has-photo'); }
              if (userAvatarLarge) { userAvatarLarge.style.backgroundImage = 'url(\"' + d.url + '\")'; userAvatarLarge.textContent = ''; userAvatarLarge.classList.add('has-photo'); }
            } else {
              if (alertBox) alertBox.textContent = (d && d.message) ? d.message : 'No se pudo actualizar la foto.';
              if (typeof Swal !== 'undefined') { Swal.fire({icon:'error',title:'No se pudo actualizar',text:(d && d.message)?d.message:'No se pudo actualizar la foto.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'}); }
            }
          }).catch(function () { if (alertBox) alertBox.textContent = 'Error de red.'; if (typeof Swal !== 'undefined') { Swal.fire({icon:'error',title:'Error de red',text:'No fue posible subir la foto.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'}); } });
      });
    }
    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        alertBox.textContent = '';
        var fullName = el('fullName').value.trim();
        var email = el('email').value.trim();
        if (fullName === '' || email === '') { alertBox.textContent = 'Completa nombre y correo.'; if (typeof Swal !== 'undefined') { Swal.fire({icon:'warning',title:'Campos requeridos',text:'Completa nombre y correo.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'}); } return; }
        fetch('/backend/php/user/user_profile_manage.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ action: 'update_profile', fullName: fullName, email: email })
        }).then(function (r) { return r.json(); })
          .then(function (d) {
            alertBox.textContent = d.message || (d.success ? 'Guardado' : 'Error');
            if (d && d.success) {
              if (typeof Swal !== 'undefined') { Swal.fire({icon:'success',title:'Perfil actualizado',text:d.message || 'Tus datos se guardaron correctamente.',timer:1800,showConfirmButton:false,background:'#1a1a1a',color:'#ffffff'}); }
              try {
                var raw = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
                if (raw) {
                  var data = JSON.parse(raw);
                  if (data.user) { data.user.nombres_completos = fullName; data.user.correo_electronico = email; }
                  else { data.nombres_completos = fullName; data.correo_electronico = email; }
                  localStorage.setItem('userData', JSON.stringify(data));
                  sessionStorage.setItem('currentSession', JSON.stringify(data));
                }
              } catch (e) { }
              reloadServerProfile();
            } else {
              if (d && d.message && /iniciar sesión/i.test(String(d.message))) {
                ensureServerSession().then(function(ok){
                  if (ok) {
                    return fetch('/backend/php/user/user_profile_manage.php', {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                      body: new URLSearchParams({ action: 'update_profile', fullName: fullName, email: email })
                    }).then(function(r2){ return r2.json(); })
                      .then(function(d2){
                        alertBox.textContent = d2.message || (d2.success ? 'Guardado' : 'Error');
                        if (d2 && d2.success) {
                          if (typeof Swal !== 'undefined') { Swal.fire({icon:'success',title:'Perfil actualizado',text:d2.message || 'Tus datos se guardaron correctamente.',timer:1800,showConfirmButton:false,background:'#1a1a1a',color:'#ffffff'}); }
                          reloadServerProfile();
                        } else {
                          if (typeof Swal !== 'undefined') { Swal.fire({icon:'error',title:'No se pudo guardar',text:d2 && d2.message ? d2.message : 'Ocurrió un error al guardar.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'}); }
                        }
                      });
                  } else {
                    if (typeof Swal !== 'undefined') { Swal.fire({icon:'error',title:'Sesión requerida',text:'Vuelve a iniciar sesión para guardar cambios.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'}); }
                  }
                });
              } else {
                if (typeof Swal !== 'undefined') { Swal.fire({icon:'error',title:'No se pudo guardar',text:d && d.message ? d.message : 'Ocurrió un error al guardar.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'}); }
              }
            }
          }).catch(function () { alertBox.textContent = 'Error de red.'; if (typeof Swal !== 'undefined') { Swal.fire({icon:'error',title:'Error de red',text:'No fue posible guardar los cambios.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'}); } });
      });
    }
    if (resetBtn) {
      resetBtn.addEventListener('click', function () { loadProfile(); alertBox.textContent = ''; if (typeof Swal !== 'undefined') { Swal.fire({icon:'info',title:'Valores restablecidos',text:'Se restauraron los datos del perfil.',timer:1500,showConfirmButton:false,background:'#1a1a1a',color:'#ffffff'}); } });
    }
  }
  function bindPassword() {
    var btn = el('changePassword');
    var alertBox = el('passAlert');
    if (btn) {
      btn.addEventListener('click', function () {
        alertBox.textContent = '';
        var currentPassword = el('currentPassword').value;
        var newPassword = el('newPassword').value;
        var confirmPassword = el('confirmPassword').value;
        if ((newPassword || '').length < 8) { alertBox.textContent = 'La nueva contraseña debe tener al menos 8 caracteres.'; if (typeof Swal !== 'undefined') { Swal.fire({icon:'warning',title:'Contraseña muy corta',text:'Debe tener al menos 8 caracteres.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'}); } return; }
        if (newPassword !== confirmPassword) { alertBox.textContent = 'Las contraseñas nuevas no coinciden.'; if (typeof Swal !== 'undefined') { Swal.fire({icon:'warning',title:'No coinciden',text:'Las contraseñas nuevas no coinciden.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'}); } return; }
        fetch('/backend/php/user/user_profile_manage.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            action: 'change_password',
            currentPassword: currentPassword,
            newPassword: newPassword,
            confirmPassword: confirmPassword
          })
        }).then(function (r) { return r.json(); })
          .then(function (d) {
            alertBox.textContent = d.message || (d.success ? 'Contraseña actualizada' : 'Error');
            if (d && d.success) {
              if (typeof Swal !== 'undefined') { Swal.fire({icon:'success',title:'Contraseña actualizada',text:'Tu contraseña se cambió correctamente.',timer:1800,showConfirmButton:false,background:'#1a1a1a',color:'#ffffff'}); }
              el('currentPassword').value = '';
              el('newPassword').value = '';
              el('confirmPassword').value = '';
            } else {
              if (d && d.message && /iniciar sesión/i.test(String(d.message))) {
                ensureServerSession().then(function(ok){
                  if (ok) {
                    return fetch('/backend/php/user/user_profile_manage.php', {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                      body: new URLSearchParams({
                        action: 'change_password',
                        currentPassword: currentPassword,
                        newPassword: newPassword,
                        confirmPassword: confirmPassword
                      })
                    }).then(function(r2){ return r2.json(); })
                      .then(function(d2){
                        alertBox.textContent = d2.message || (d2.success ? 'Contraseña actualizada' : 'Error');
                        if (d2 && d2.success) {
                          if (typeof Swal !== 'undefined') { Swal.fire({icon:'success',title:'Contraseña actualizada',text:'Tu contraseña se cambió correctamente.',timer:1800,showConfirmButton:false,background:'#1a1a1a',color:'#ffffff'}); }
                          el('currentPassword').value = '';
                          el('newPassword').value = '';
                          el('confirmPassword').value = '';
                        } else {
                          if (typeof Swal !== 'undefined') { Swal.fire({icon:'error',title:'No se pudo actualizar',text:d2 && d2.message ? d2.message : 'Ocurrió un error al actualizar la contraseña.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'}); }
                        }
                      });
                  } else {
                    if (typeof Swal !== 'undefined') { Swal.fire({icon:'error',title:'Sesión requerida',text:'Vuelve a iniciar sesión para cambiar la contraseña.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'}); }
                  }
                });
              } else {
                if (typeof Swal !== 'undefined') { Swal.fire({icon:'error',title:'No se pudo actualizar',text:d && d.message ? d.message : 'Ocurrió un error al actualizar la contraseña.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'}); }
              }
            }
          })
          .catch(function () { alertBox.textContent = 'Error de red.'; if (typeof Swal !== 'undefined') { Swal.fire({icon:'error',title:'Error de red',text:'No fue posible actualizar la contraseña.',confirmButtonColor:'#ff0000',background:'#1a1a1a',color:'#ffffff'}); } });
      });
    }
  }
  function bindPasswordVisibility() {
    var toggles = document.querySelectorAll('.password-toggle');
    if (!toggles || !toggles.length) return;
    for (var i = 0; i < toggles.length; i++) {
      toggles[i].addEventListener('click', function () {
        var targetId = this.getAttribute('data-target');
        if (!targetId) return;
        var input = document.getElementById(targetId);
        if (!input) return;
        var isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        if (isHidden) this.classList.add('active'); else this.classList.remove('active');
        var icon = this.querySelector('i');
        if (icon) {
          icon.classList.toggle('fa-eye');
          icon.classList.toggle('fa-eye-slash');
        }
      });
    }
  }
  function bindSettings() {
    var u = getSessionUser(); if (!u) return;
    var key = 'userSettings_' + String(u.id || u.id_usuario || u.email || 'anon');
    var emailNotifs = el('stEmailNotifs');
    var rememberFavs = el('stRememberFavs');
    var showIVA = el('stShowIVA');
    var alertBox = el('settingsAlert');
    if (!emailNotifs || !rememberFavs || !showIVA) return;
    try {
      var raw = localStorage.getItem(key);
      var cfg = raw ? JSON.parse(raw) : {};
      emailNotifs.checked = !!cfg.emailNotifs;
      rememberFavs.checked = !!cfg.rememberFavs;
      showIVA.checked = !!cfg.showIVA;
    } catch (e) { }
    (function loadServerSettings(){
      ensureServerSession().then(function(){
        fetch('/backend/php/user/user_settings_manage.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ action: 'get_settings' })
        }).then(function(r){ return r.json(); })
        .then(function(d){
          if (d && d.success && d.settings) {
            emailNotifs.checked = !!d.settings.emailNotifs;
            rememberFavs.checked = !!d.settings.rememberFavs;
            showIVA.checked = !!d.settings.showIVA;
            try { localStorage.setItem(key, JSON.stringify(d.settings)); } catch (e) {}
          }
        }).catch(function(){});
      });
    })();
    var saveBtn = el('saveSettings');
    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        var cfg = {
          emailNotifs: !!emailNotifs.checked,
          rememberFavs: !!rememberFavs.checked,
          showIVA: !!showIVA.checked
        };
        ensureServerSession().then(function(){
          fetch('/backend/php/user/user_settings_manage.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
              action: 'save_settings',
              emailNotifs: cfg.emailNotifs ? 'true' : 'false',
              rememberFavs: cfg.rememberFavs ? 'true' : 'false',
              showIVA: cfg.showIVA ? 'true' : 'false'
            })
          }).then(function(r){ return r.json(); })
          .then(function(d){
            if (d && d.success) {
              try { localStorage.setItem(key, JSON.stringify(cfg)); } catch (e) {}
              alertBox.textContent = 'Configuración guardada.';
              if (typeof Swal !== 'undefined') {
                Swal.fire({
                  icon: 'success',
                  title: 'Configuración guardada',
                  text: 'Tus preferencias se han guardado correctamente.',
                  timer: 1600,
                  showConfirmButton: false,
                  background: '#1a1a1a',
                  color: '#ffffff'
                });
              }
              try { window.dispatchEvent(new CustomEvent('settings:updated', { detail: { key: key, settings: cfg } })); } catch (e) {}
            } else if (d && d.message && /iniciar sesión/i.test(String(d.message))) {
              // Reintentar tras restaurar sesión
              ensureServerSession().then(function(ok){
                if (!ok) {
                  try { localStorage.setItem(key, JSON.stringify(cfg)); } catch (e) {}
                  alertBox.textContent = 'Configuración guardada localmente.';
                  if (typeof Swal !== 'undefined') {
                    Swal.fire({
                      icon: 'info',
                      title: 'Guardado local',
                      text: 'Se guardó en este dispositivo por requerir inicio de sesión.',
                      confirmButtonColor: '#ff0000',
                      background: '#1a1a1a',
                      color: '#ffffff'
                    });
                  }
                  return;
                }
                fetch('/backend/php/user/user_settings_manage.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: new URLSearchParams({
                    action: 'save_settings',
                    emailNotifs: cfg.emailNotifs ? 'true' : 'false',
                    rememberFavs: cfg.rememberFavs ? 'true' : 'false',
                    showIVA: cfg.showIVA ? 'true' : 'false'
                  })
                }).then(function(r2){ return r2.json(); })
                .then(function(d2){
                  if (d2 && d2.success) {
                    try { localStorage.setItem(key, JSON.stringify(cfg)); } catch (e) {}
                    alertBox.textContent = 'Configuración guardada.';
                    if (typeof Swal !== 'undefined') {
                      Swal.fire({
                        icon: 'success',
                        title: 'Configuración guardada',
                        text: 'Tus preferencias se han guardado correctamente.',
                        timer: 1600,
                        showConfirmButton: false,
                        background: '#1a1a1a',
                        color: '#ffffff'
                      });
                    }
                    try { window.dispatchEvent(new CustomEvent('settings:updated', { detail: { key: key, settings: cfg } })); } catch (e) {}
                  } else {
                    try { localStorage.setItem(key, JSON.stringify(cfg)); } catch (e) {}
                    alertBox.textContent = d2 && d2.message ? d2.message : 'Configuración guardada localmente.';
                    if (typeof Swal !== 'undefined') {
                      Swal.fire({
                        icon: 'warning',
                        title: 'Guardado local',
                        text: 'No se pudo guardar en el servidor. Se guardó en este dispositivo.',
                        confirmButtonColor: '#ff0000',
                        background: '#1a1a1a',
                        color: '#ffffff'
                      });
                    }
                  }
                }).catch(function(){
                  try { localStorage.setItem(key, JSON.stringify(cfg)); } catch (e) {}
                  alertBox.textContent = 'Configuración guardada localmente.';
                  if (typeof Swal !== 'undefined') {
                    Swal.fire({
                      icon: 'info',
                      title: 'Guardado local',
                      text: 'Se guardó en este dispositivo por un problema de red.',
                      confirmButtonColor: '#ff0000',
                      background: '#1a1a1a',
                      color: '#ffffff'
                    });
                  }
                });
              });
            } else {
              try { localStorage.setItem(key, JSON.stringify(cfg)); } catch (e) {}
              alertBox.textContent = d && d.message ? d.message : 'No se pudo guardar.';
              if (typeof Swal !== 'undefined') {
                Swal.fire({
                  icon: 'warning',
                  title: 'Guardado local',
                  text: 'No se pudo guardar en el servidor. Se guardó en este dispositivo.',
                  confirmButtonColor: '#ff0000',
                  background: '#1a1a1a',
                  color: '#ffffff'
                });
              }
            }
          }).catch(function(){
            try { localStorage.setItem(key, JSON.stringify(cfg)); } catch (e) {}
            alertBox.textContent = 'Configuración guardada localmente.';
            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'info',
                title: 'Guardado local',
                text: 'Se guardó en este dispositivo por un problema de red.',
                confirmButtonColor: '#ff0000',
                background: '#1a1a1a',
                color: '#ffffff'
              });
            }
          });
        });
      });
    }
  }
  function init() {
    bindTabs();
    showTab(getInitialTab());
    loadProfile();
    ensureServerSession().then(function(){ reloadServerProfile(); });
    bindEdit();
    bindPassword();
    bindPasswordVisibility();
    bindSettings();
  }
  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); }
  else { init(); }
})();

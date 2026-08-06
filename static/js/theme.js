/* Tema claro/oscuro global — DISTRICARNES
   Claro por defecto (blanco como color principal); el header se mantiene negro.
   El botón (luna/sol) abre un menú con las opciones "Modo claro" y "Modo oscuro".
   Persiste la preferencia en localStorage['dcTheme'] y expone window.DCTheme. */
(function () {
  'use strict';
  var KEY = 'dcTheme';
  var root = document.documentElement;

  function current() {
    return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
  }

  function closeMenus() {
    var menus = document.querySelectorAll('.dc-theme-menu');
    for (var i = 0; i < menus.length; i++) {
      menus[i].classList.remove('open');
    }
  }

  function updateButtons() {
    var dark = current() === 'dark';
    var icons = document.querySelectorAll('.dc-theme-toggle i');
    for (var i = 0; i < icons.length; i++) {
      icons[i].className = dark ? 'fas fa-moon' : 'fas fa-sun';
    }
    var label = 'Cambiar modo de color';
    var btns = document.querySelectorAll('.dc-theme-toggle');
    for (var j = 0; j < btns.length; j++) {
      btns[j].setAttribute('aria-label', label);
      btns[j].setAttribute('title', label);
    }
    var menus = document.querySelectorAll('.dc-theme-menu');
    for (var k = 0; k < menus.length; k++) {
      var lightOpt = menus[k].querySelector('[data-theme-option="light"]');
      var darkOpt = menus[k].querySelector('[data-theme-option="dark"]');
      if (lightOpt) lightOpt.classList.toggle('active', !dark);
      if (darkOpt) darkOpt.classList.toggle('active', dark);
    }
  }

  // Cambio automático del logo según el tema:
  //   Modo claro  -> "DISTRICARNES FONDO MODO CLARO.png" (logo oficial para fondo claro)
  //   Modo oscuro -> "LOGO-DISTRICARNES.png" (logo original)
  function updateLogos() {
    var dark = current() === 'dark';
    var imgs = document.querySelectorAll('img[src*="LOGO-DISTRICARNES.png"]');
    for (var i = 0; i < imgs.length; i++) {
      var src = imgs[i].getAttribute('src') || '';
      var m = src.match(/^(.*\/)LOGO-DISTRICARNES\.png$/i);
      if (!m) continue;
      var target = (dark ? 'LOGO-DISTRICARNES.png' : 'DISTRICARNES FONDO MODO CLARO.png');
      if (m[1] + target !== src) imgs[i].src = m[1] + target;
    }
  }

  function apply(theme) {
    var t = theme === 'dark' ? 'dark' : 'light';
    root.setAttribute('data-theme', t);
    try { localStorage.setItem(KEY, t); } catch (e) {}
    updateButtons();
    updateLogos();
    try { window.dispatchEvent(new CustomEvent('theme:changed', { detail: { theme: t } })); } catch (e) {}
  }

  function makeMenu(wrap) {
    var menu = document.createElement('div');
    menu.className = 'dc-theme-menu';
    menu.innerHTML =
      '<div class="dc-theme-menu-label">Modo de color</div>' +
      '<button type="button" class="dc-theme-option" data-theme-option="light"><i class="fas fa-sun"></i> Modo claro</button>' +
      '<button type="button" class="dc-theme-option" data-theme-option="dark"><i class="fas fa-moon"></i> Modo oscuro</button>';
    menu.querySelector('[data-theme-option="light"]').addEventListener('click', function () {
      apply('light');
      closeMenus();
    });
    menu.querySelector('[data-theme-option="dark"]').addEventListener('click', function () {
      apply('dark');
      closeMenus();
    });
    wrap.appendChild(menu);
    return menu;
  }

  function makeButton() {
    var wrap = document.createElement('span');
    wrap.className = 'dc-theme-wrap';
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'dc-theme-toggle';
    b.setAttribute('aria-label', 'Cambiar modo de color');
    b.innerHTML = '<i class="fas fa-sun"></i>';
    b.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var menu = wrap.querySelector('.dc-theme-menu');
      if (menu) {
        var wasOpen = menu.classList.contains('open');
        closeMenus();
        if (!wasOpen) menu.classList.add('open');
      }
    });
    wrap.appendChild(b);
    makeMenu(wrap);
    return wrap;
  }

  function inject() {
    var desktop = document.getElementById('quickLinks');
    if (desktop) {
      desktop.insertBefore(makeButton(), desktop.firstChild);
    } else {
      var hc = document.querySelector('.header-content');
      if (hc) hc.appendChild(makeButton());
    }
    var mh = document.querySelector('.mobile-header .mh-right');
    if (mh) mh.insertBefore(makeButton(), mh.firstChild);
    updateButtons();
    updateLogos();
  }

  // Aplicar antes del primer paint para evitar el parpadeo claro/oscuro
  // Modo oscuro por defecto; el usuario puede elegir claro/oscuro con el botón.
  var stored = null;
  try { stored = localStorage.getItem(KEY); } catch (e) {}
  apply(stored || 'dark');

  window.DCTheme = { get: current, set: apply, toggle: function () { apply(current() === 'dark' ? 'light' : 'dark'); } };

  document.addEventListener('click', function (e) {
    if (!e.target.closest || !e.target.closest('.dc-theme-wrap')) closeMenus();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeMenus();
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inject);
  } else {
    inject();
  }

  // El modal de login/registro (auth_modal.js) inyecta su logo después de cargar,
  // así que observamos el DOM para intercambiarlo también.
  if (window.MutationObserver) {
    var logoObs = new MutationObserver(function (mutations) {
      var need = false;
      for (var mi = 0; mi < mutations.length; mi++) {
        var added = mutations[mi].addedNodes;
        for (var aj = 0; aj < added.length; aj++) {
          var node = added[aj];
          if (node.nodeType !== 1) continue;
          if (node.matches && node.matches('img[src*="LOGO-DISTRICARNES.png"]')) { need = true; break; }
          if (node.querySelector && node.querySelector('img[src*="LOGO-DISTRICARNES.png"]')) { need = true; break; }
        }
        if (need) break;
      }
      if (need) updateLogos();
    });
    if (document.body) logoObs.observe(document.body, { childList: true, subtree: true });
  }
})();

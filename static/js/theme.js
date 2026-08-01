/* Tema claro/oscuro global — DISTRICARNES
   Claro por defecto (blanco como color principal); el header se mantiene negro.
   Persiste la preferencia en localStorage['dcTheme'] y expone window.DCTheme. */
(function () {
  'use strict';
  var KEY = 'dcTheme';
  var root = document.documentElement;

  function current() {
    return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
  }

  function updateButtons() {
    var dark = current() === 'dark';
    var icons = document.querySelectorAll('.dc-theme-toggle i');
    for (var i = 0; i < icons.length; i++) {
      icons[i].className = dark ? 'fas fa-sun' : 'fas fa-moon';
    }
    var label = dark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';
    var btns = document.querySelectorAll('.dc-theme-toggle');
    for (var j = 0; j < btns.length; j++) {
      btns[j].setAttribute('aria-label', label);
      btns[j].setAttribute('title', label);
    }
  }

  function apply(theme) {
    var t = theme === 'dark' ? 'dark' : 'light';
    root.setAttribute('data-theme', t);
    try { localStorage.setItem(KEY, t); } catch (e) {}
    updateButtons();
    try { window.dispatchEvent(new CustomEvent('theme:changed', { detail: { theme: t } })); } catch (e) {}
  }

  function toggle() {
    apply(current() === 'dark' ? 'light' : 'dark');
  }

  function makeButton() {
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'dc-theme-toggle';
    b.setAttribute('aria-label', 'Cambiar a modo oscuro');
    b.innerHTML = '<i class="fas fa-moon"></i>';
    b.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      toggle();
    });
    return b;
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
  }

  // Aplicar antes del primer paint para evitar el parpadeo claro/oscuro
  var stored = null;
  try { stored = localStorage.getItem(KEY); } catch (e) {}
  apply(stored || 'light');

  window.DCTheme = { get: current, set: apply, toggle: toggle };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inject);
  } else {
    inject();
  }
})();

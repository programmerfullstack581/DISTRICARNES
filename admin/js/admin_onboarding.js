/*!
 * admin_onboarding.js - Guía interactiva para el panel de administración
 * de DistriCarnes (driver.js). Detecta la primera visita del navegador
 * (localStorage) y muestra un recorrido por el panel. Expone
 * window.startAdminTour() y agrega un botón flotante "?" de ayuda.
 */
(function () {
  'use strict';

  var DRIVER_VERSION = '1.8.0';
  var DRIVER_JS = 'https://cdn.jsdelivr.net/npm/driver.js@' + DRIVER_VERSION + '/dist/driver.js.iife.min.js';
  var DRIVER_CSS = 'https://cdn.jsdelivr.net/npm/driver.js@' + DRIVER_VERSION + '/dist/driver.min.css';
  var STORAGE_KEY = 'dc_tour_admin_seen';

  var running = false;
  var helpBtn = null;

  function saveSeen() {
    try { localStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}
  }

  function alreadySeen() {
    try { return !!localStorage.getItem(STORAGE_KEY); } catch (e) { return false; }
  }

  function loadDriver() {
    if (window.__dcDriverPromise) return window.__dcDriverPromise;
    window.__dcDriverPromise = new Promise(function (resolve, reject) {
      var link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = DRIVER_CSS;
      document.head.appendChild(link);

      var s = document.createElement('script');
      s.src = DRIVER_JS;
      s.async = true;
      s.onload = function () {
        var root = window.driver;
        var ctor =
          (root && root.js && typeof root.js.driver === 'function' && root.js.driver) ||
          (root && typeof root.driver === 'function' && root.driver) ||
          (root && typeof root === 'function' && root) ||
          null;
        if (ctor) resolve(ctor);
        else reject(new Error('No se encontró el constructor de driver.js'));
      };
      s.onerror = function () { reject(new Error('No se pudo cargar driver.js')); };
      document.head.appendChild(s);
    });
    return window.__dcDriverPromise;
  }

  function visibleEl(sel) {
    var el = document.querySelector(sel);
    if (!el) return null;
    if (el.getClientRects && el.getClientRects().length === 0) return null;
    var st = window.getComputedStyle(el);
    if (st.display === 'none' || st.visibility === 'hidden') return null;
    return el;
  }

  function pick(selectors) {
    for (var i = 0; i < selectors.length; i++) {
      var el = visibleEl(selectors[i]);
      if (el) return el;
    }
    return null;
  }

  function step(popover, selectors) {
    var el = selectors ? pick(selectors) : null;
    if (selectors && !el) return null;
    return el ? { element: el, popover: popover } : { popover: popover };
  }

  function buildSteps() {
    var steps = [];

    steps.push(step({
      title: 'Panel de administración 🛠️',
      description: 'Bienvenido. Esta guía te muestra dónde está cada cosa para que administres tu tienda sin complicaciones.',
      align: 'center'
    }));

    var s = step({
      title: 'Menú lateral',
      description: 'Desde aquí navegas entre todos los módulos: Usuarios, Productos, Categorías, Pedidos, Facturas, Inventario, Ventas, Reportes, Promociones y Configuración.',
      side: 'right',
      align: 'center'
    }, ['#sidebar', '.sidebar']);
    if (s) steps.push(s);

    s = step({
      title: 'Contraer el menú',
      description: 'Este botón oculta o muestra la barra lateral para ganar más espacio de trabajo.',
      side: 'bottom',
      align: 'center'
    }, ['#menuToggle', '.menu-toggle']);
    if (s) steps.push(s);

    s = step({
      title: 'Buscador',
      description: 'Encuentra rápido cualquier registro (clientes, pedidos, productos, etc.).',
      side: 'bottom',
      align: 'center'
    }, ['.search-box']);
    if (s) steps.push(s);

    s = step({
      title: 'Notificaciones',
      description: 'Aquí verás los eventos recientes del sistema: nuevos pedidos, pagos, alertas de stock y más.',
      side: 'bottom',
      align: 'center'
    }, ['.notifications']);
    if (s) steps.push(s);

    s = step({
      title: 'Tu perfil',
      description: 'Edita tu perfil, cambia tu contraseña, revisa la configuración o cierra sesión.',
      side: 'bottom',
      align: 'center'
    }, ['.user-profile']);
    if (s) steps.push(s);

    s = step({
      title: 'Acciones rápidas',
      description: 'Atajos para las tareas más comunes: agregar producto, nuevo usuario, ver pedidos o generar un reporte.',
      side: 'bottom',
      align: 'center'
    }, ['.quick-actions']);
    if (s) steps.push(s);

    s = step({
      title: 'Indicadores del día',
      description: 'Ventas, pedidos en ruta, stock bajo y clientes activos. Son las cifras clave de tu negocio.',
      side: 'bottom',
      align: 'center'
    }, ['.stats-grid']);
    if (s) steps.push(s);

    s = step({
      title: 'Gráfico de ventas',
      description: 'Visualiza el comportamiento de las ventas del mes para tomar mejores decisiones.',
      side: 'right',
      align: 'center'
    }, ['.chart-container']);
    if (s) steps.push(s);

    s = step({
      title: 'Actividad reciente',
      description: 'El historial de lo último que ocurrió en tu tienda: ventas, pedidos y movimientos de inventario.',
      side: 'left',
      align: 'center'
    }, ['.activity-feed']);
    if (s) steps.push(s);

    steps.push(step({
      title: '¡Listo! 🎉',
      description: 'Ya conoces lo esencial del panel. Si necesitas volver a ver esta guía, usa el botón "?" que quedó en la pantalla.',
      align: 'center'
    }));

    return steps;
  }

  window.startAdminTour = function () {
    if (running) return;

    running = true;
    if (helpBtn) helpBtn.style.display = 'none';

    loadDriver().then(function (driverCtor) {
      var d = driverCtor({
        showProgress: true,
        progressText: 'Paso {{current}} de {{total}}',
        animate: true,
        overlayOpacity: 0.65,
        stagePadding: 6,
        stageRadius: 8,
        allowClose: true,
        allowScroll: true,
        popoverClass: 'dc-tour-popover',
        nextBtnText: 'Siguiente',
        prevBtnText: 'Anterior',
        doneBtnText: 'Terminar',
        onDestroyed: function () {
          running = false;
          if (helpBtn) helpBtn.style.display = '';
          saveSeen();
        },
        steps: buildSteps()
      });
      d.drive();
    }).catch(function (err) {
      running = false;
      if (helpBtn) helpBtn.style.display = '';
      console.error('admin_onboarding.js:', err && err.message ? err.message : err);
    });
  };

  function addHelpButton() {
    if (document.getElementById('dcTourBtnAdmin')) return;
    var btn = document.createElement('button');
    btn.id = 'dcTourBtnAdmin';
    btn.type = 'button';
    btn.title = 'Ver guía del panel';
    btn.setAttribute('aria-label', 'Ver guía del panel');
    btn.textContent = '?';
    btn.style.cssText = 'position:fixed;right:20px;bottom:20px;width:46px;height:46px;border-radius:50%;border:2px solid #ff0000;background:#000;color:#fff;font-size:22px;font-weight:700;cursor:pointer;z-index:2147483000;box-shadow:0 4px 14px rgba(255,0,0,.35);display:flex;align-items:center;justify-content:center;transition:transform .15s ease;';
    btn.addEventListener('mouseenter', function () { btn.style.transform = 'scale(1.08)'; });
    btn.addEventListener('mouseleave', function () { btn.style.transform = 'scale(1)'; });
    btn.addEventListener('click', function () { window.startAdminTour(); });
    document.body.appendChild(btn);
    helpBtn = btn;
  }

  function injectStyles() {
    if (document.getElementById('dcTourStyles')) return;
    var style = document.createElement('style');
    style.id = 'dcTourStyles';
    style.textContent =
      '.dc-tour-popover{--driver-popover-font-family:"Segoe UI",Arial,sans-serif;border-radius:14px;border:1px solid rgba(255,0,0,.25)}' +
      '.dc-tour-popover .driver-popover-title{font-weight:800}' +
      '.dc-tour-popover .driver-popover-progress-text{color:#ff0000;font-weight:700}' +
      '.dc-tour-popover .driver-popover-footer-btn{border:none;border-radius:8px;padding:6px 12px;font-weight:700;cursor:pointer}' +
      '.dc-tour-popover .driver-popover-next-btn,.dc-tour-popover .driver-popover-done-btn{background:#ff0000;color:#fff}' +
      '.dc-tour-popover .driver-popover-prev-btn{background:#333;color:#fff}' +
      '.dc-tour-popover .driver-popover-close-btn{color:#888}';
    document.head.appendChild(style);
  }

  function autoStart() {
    if (window.__dcAdminTourStarted) return;
    window.__dcAdminTourStarted = true;
    if (alreadySeen()) return;
    setTimeout(window.startAdminTour, 900);
  }

  function init() {
    injectStyles();
    addHelpButton();
    autoStart();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

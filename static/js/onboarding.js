/*!
 * onboarding.js - Guía interactiva para nuevos visitantes (driver.js)
 * Detecta la primera visita en el navegador (localStorage) y muestra un
 * recorrido por las partes principales de la tienda DistriCarnes.
 * Expone window.startSiteTour() para poder reiniciar la guía desde cualquier
 * botón y agrega un botón flotante "?" de ayuda.
 */
(function () {
  'use strict';

  var DRIVER_VERSION = '1.8.0';
  var DRIVER_JS = 'https://cdn.jsdelivr.net/npm/driver.js@' + DRIVER_VERSION + '/dist/driver.js.iife.min.js';
  var DRIVER_CSS = 'https://cdn.jsdelivr.net/npm/driver.js@' + DRIVER_VERSION + '/dist/driver.min.css';
  var STORAGE_KEY = 'dc_tour_site_seen';

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

  // Devuelve el primer elemento visible de una lista de selectores,
  // o null si ninguno existe/está visible (el paso se omite).
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
      title: '¡Bienvenido a DistriCarnes! 🥩',
      description: 'Esta breve guía te muestra lo más importante de la tienda para que compres como todo un experto.',
      align: 'center'
    }));

    var s = step({
      title: 'Logo / Inicio',
      description: 'Haz clic en el logo para volver al inicio en cualquier momento.',
      side: 'bottom',
      align: 'center'
    }, ['.header .logo', '.mobile-header .mh-center a']);
    if (s) steps.push(s);

    s = step({
      title: 'Buscador',
      description: 'Escribe el nombre de un producto, una marca o un corte y pulsa Enter para encontrarlo.',
      side: 'bottom',
      align: 'center'
    }, ['#site-search', '.ml-search', '.mh-right .header-search-toggle']);
    if (s) steps.push(s);

    s = step({
      title: 'Menú de navegación',
      description: 'Recorre Inicio, Productos, Ofertas, Contacto y Quiénes Somos.',
      side: 'bottom',
      align: 'center'
    }, ['#navMenu', '.mobile-drawer .drawer-nav', '.mh-left .mh-icon']);
    if (s) steps.push(s);

    s = step({
      title: 'Carrito de compras',
      description: 'Aquí se guardan los productos que agregues. Podrás ver el total y finalizar tu pedido.',
      side: 'bottom',
      align: 'center'
    }, ['#cartButton', '.mh-cart']);
    if (s) steps.push(s);

    s = step({
      title: 'Inicia sesión o regístrate',
      description: 'Crea tu cuenta para guardar tu historial, tus favoritos y recibir promociones.',
      side: 'bottom',
      align: 'center'
    }, ['#authButtons', '.mh-right']);
    if (s) steps.push(s);

    s = step({
      title: 'Compra online',
      description: 'Explora nuestra carne fresca, segura y de calidad. Usa el botón "Comprar online".',
      side: 'right',
      align: 'center'
    }, ['#heroTitle', '#heroCta']);
    if (s) steps.push(s);

    s = step({
      title: '¿Pedidos o dudas?',
      description: 'Escríbenos por WhatsApp, llámanos al +57 301 5210177 o visítanos en nuestras redes sociales.',
      side: 'top',
      align: 'center'
    }, ['.brand-marquee', 'footer', '.footer']);
    if (s) steps.push(s);

    steps.push(step({
      title: '¡Listo! 🎉',
      description: 'Ya conoces lo esencial. Si en algún momento vuelves a necesitar ayuda, usa el botón "?" que quedó en la pantalla.',
      align: 'center'
    }));

    return steps;
  }

  window.startSiteTour = function () {
    if (running) return;

    // Si el modal de bienvenida está abierto, lo cerramos antes para no superponerse.
    var modal = document.getElementById('welcomeModal');
    if (modal && getComputedStyle(modal).display !== 'none' && window.closeModal) {
      try { window.closeModal(); } catch (e) {}
    }

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
      console.error('onboarding.js:', err && err.message ? err.message : err);
    });
  };

  function addHelpButton() {
    if (document.getElementById('dcTourBtn')) return;
    var btn = document.createElement('button');
    btn.id = 'dcTourBtn';
    btn.type = 'button';
    btn.title = 'Ver guía de la tienda';
    btn.setAttribute('aria-label', 'Ver guía de la tienda');
    btn.textContent = '?';
    btn.style.cssText = 'position:fixed;left:20px;bottom:20px;width:46px;height:46px;border-radius:50%;border:2px solid #ff0000;background:#000;color:#fff;font-size:22px;font-weight:700;cursor:pointer;z-index:2147483000;box-shadow:0 4px 14px rgba(255,0,0,.35);display:flex;align-items:center;justify-content:center;transition:transform .15s ease;';
    btn.addEventListener('mouseenter', function () { btn.style.transform = 'scale(1.08)'; });
    btn.addEventListener('mouseleave', function () { btn.style.transform = 'scale(1)'; });
    btn.addEventListener('click', function () { window.startSiteTour(); });
    document.body.appendChild(btn);
    helpBtn = btn;
  }

  function injectStyles() {
    if (document.getElementById('dcTourStyles')) return;
    var style = document.createElement('style');
    style.id = 'dcTourStyles';
    style.textContent =
      '.dc-tour-popover{--driver-popover-font-family:Montserrat,"Segoe UI",Arial,sans-serif;border-radius:14px;border:1px solid rgba(255,0,0,.25)}' +
      '.dc-tour-popover .driver-popover-title{font-weight:800}' +
      '.dc-tour-popover .driver-popover-progress-text{color:#ff0000;font-weight:700}' +
      '.dc-tour-popover .driver-popover-footer-btn{border:none;border-radius:8px;padding:6px 12px;font-weight:700;cursor:pointer}' +
      '.dc-tour-popover .driver-popover-next-btn,.dc-tour-popover .driver-popover-done-btn{background:#ff0000;color:#fff}' +
      '.dc-tour-popover .driver-popover-prev-btn{background:#333;color:#fff}' +
      '.dc-tour-popover .driver-popover-close-btn{color:#888}';
    document.head.appendChild(style);
  }

  function autoStart() {
    if (window.__dcSiteTourStarted) return;
    window.__dcSiteTourStarted = true;
    if (alreadySeen()) return;

    var modal = document.getElementById('welcomeModal');
    var modalVisible = modal && getComputedStyle(modal).display !== 'none';

    if (!modalVisible) {
      setTimeout(window.startSiteTour, 900);
      return;
    }

    // Espera a que el usuario cierre el modal de bienvenida para iniciar la guía.
    var tries = 0;
    var timer = setInterval(function () {
      tries++;
      var visible = getComputedStyle(modal).display !== 'none';
      if (!visible || tries > 80) {
        clearInterval(timer);
        if (!visible) setTimeout(window.startSiteTour, 250);
      }
    }, 250);
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

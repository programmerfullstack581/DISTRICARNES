/*!
 * public_tour.js - Guía interactiva de primera visita para el sitio público
 * de DistriCarnes (driver.js). Detecta la primera visita del navegador
 * (localStorage) y muestra un recorrido por la página actual. Expone
 * window.startPublicTour() y agrega un botón flotante "?" de ayuda.
 */
(function () {
  'use strict';

  var DRIVER_VERSION = '1.8.0';
  var DRIVER_JS = 'https://cdn.jsdelivr.net/npm/driver.js@' + DRIVER_VERSION + '/dist/driver.js.iife.min.js';
  var DRIVER_CSS = 'https://cdn.jsdelivr.net/npm/driver.js@' + DRIVER_VERSION + '/dist/driver.min.css';
  var STORAGE_KEY = 'dc_tour_public_seen';

  var running = false;
  var helpBtn = null;

  function saveSeen() {
    try { localStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}
  }

  function alreadySeen() {
    try { return !!localStorage.getItem(STORAGE_KEY); } catch (e) { return false; }
  }

  function loadDriver() {
    if (window.__dcPublicDriverPromise) return window.__dcPublicDriverPromise;
    window.__dcPublicDriverPromise = new Promise(function (resolve, reject) {
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
    return window.__dcPublicDriverPromise;
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

  function getPageType() {
    var path = window.location.pathname;
    if (path.indexOf('productos.php') !== -1 || path.indexOf('productos') !== -1) return 'products';
    if (path.indexOf('carrito') !== -1 || path.indexOf('cart') !== -1) return 'cart';
    if (path.indexOf('checkout') !== -1 || path.indexOf('direccion') !== -1) return 'checkout';
    if (path.indexOf('perfil') !== -1 || path.indexOf('profile') !== -1) return 'profile';
    if (path.indexOf('promociones') !== -1) return 'promotions';
    if (path.indexOf('contacto') !== -1) return 'contact';
    return 'home';
  }

  function buildSteps() {
    var page = getPageType();
    var steps = [];

    if (page === 'home') {
      steps.push(step({
        title: 'Bienvenido a DistriCarnes 🥩',
        description: 'Esta guía te ayudará a conocer lo esencial de nuestra tienda para que aproveches al máximo cada visita.',
        align: 'center'
      }));

      var s = step({
        title: 'Nuestros productos',
        description: 'Haz clic aquí para ver todo el catálogo de cortes premium, carnes, embutidos y más.',
        side: 'bottom',
        align: 'center'
      }, ['#heroCta', '.hero-cta', 'a[href*="productos.php"]', 'a[href*="productos"]']);
      if (s) steps.push(s);

      s = step({
        title: 'Teléfono',
        description: 'Llama directamente para asesoría personalizada y pedidos rápidos. Te atendemos al instante.',
        side: 'top',
        align: 'center'
      }, ['a[href*="tel:"]', '.fa-phone-alt', '.fa-phone']);
      if (s) steps.push(s);

      s = step({
        title: 'WhatsApp',
        description: 'Escríbenos por WhatsApp para hacer pedidos, consultar disponibilidad o recibir atención personalizada.',
        side: 'top',
        align: 'center'
      }, ['a[href*="wa.me"]', '.fa-whatsapp']);
      if (s) steps.push(s);

      s = step({
        title: 'Compra en línea',
        description: 'Realiza tu compra desde casa, agrega productos al carrito y recibe en la puerta de tu hogar.',
        side: 'top',
        align: 'center'
      }, ['a[href*="carrito"]', 'a[href*="cart"]', '.fa-shopping-cart']);
      if (s) steps.push(s);

      s = step({
        title: 'Ubicación y horarios',
        description: 'Encuéntranos en Olaya Herrera #34-71A-60, Cartagena. Lunes a Sábado 8:00–20:00, Domingos 9:00–17:00.',
        side: 'top',
        align: 'center'
      }, ['#districarnes-map', '.map-wrapper', '.ubicanos-card']);
      if (s) steps.push(s);

      s = step({
        title: 'Contáctanos',
        description: 'Usa este formulario para enviarnos consultas, sugerencias o pedidos especiales.',
        side: 'top',
        align: 'center'
      }, ['#contactForm', '.contact-form']);
      if (s) steps.push(s);

      steps.push(step({
        title: '¡Listo! 🎉',
        description: 'Ya conoces lo esencial de DistriCarnes. Si necesitas volver a ver esta guía, usa el botón "?" en la esquina.',
        align: 'center'
      }));
    } else if (page === 'products') {
      steps.push(step({
        title: 'Catálogo de productos 🛒',
        description: 'Explora nuestra selección de cortes premium. Usa los filtros para encontrar exactamente lo que necesitas.',
        align: 'center'
      }));

      s = step({
        title: 'Buscador',
        description: 'Escribe el nombre de un producto, marca o categoría para filtrar los resultados.',
        side: 'bottom',
        align: 'center'
      }, ['#searchModalInput', '#searchBox', 'input[type="search"]', 'input[name="q"]']);
      if (s) steps.push(s);

      s = step({
        title: 'Filtros de categoría',
        description: 'Selecciona una categoría para ver solo los productos de esa línea (res, cerdo, pollo, embutidos, etc.).',
        side: 'left',
        align: 'center'
      }, ['#categoryFilter', '#productCategorySelect', '.category-filter', '.filter-category']);
      if (s) steps.push(s);

      s = step({
        title: 'Productos',
        description: 'Haz clic en cualquier producto para ver detalles, precio y agregarlo al carrito.',
        side: 'top',
        align: 'center'
      }, ['.product-card', '.producto-card', '.product-item', '#productList', '.products-grid']);
      if (s) steps.push(s);

      s = step({
        title: 'Carrito',
        description: 'Aquí verás tus productos seleccionados y podrás proceder al pago.',
        side: 'bottom',
        align: 'center'
      }, ['#cartCount', '.cart-badge', 'a[href*="carrito"]', 'a[href*="cart"]']);
      if (s) steps.push(s);

      steps.push(step({
        title: '¡Listo! 🎉',
        description: 'Ya sabes cómo buscar y filtrar productos. Si necesitas ayuda, usa el botón "?" en la esquina.',
        align: 'center'
      }));
    } else if (page === 'cart') {
      steps.push(step({
        title: 'Tu carrito de compras 🛒',
        description: 'Revisa tus productos, modifica cantidades o elimina items antes de confirmar tu pedido.',
        align: 'center'
      }));

      s = step({
        title: 'Productos en el carrito',
        description: 'Aquí aparecen todos los productos que has agregado. Puedes cambiar cantidades o eliminarlos.',
        side: 'top',
        align: 'center'
      }, ['#cartItems', '.cart-items', '.cart-list', 'table']);
      if (s) steps.push(s);

      s = step({
        title: 'Vaciar carrito',
        description: 'Si deseas empezar de cero, usa este botón para eliminar todos los productos del carrito.',
        side: 'left',
        align: 'center'
      }, ['#btnClearHead', '.cart-clear', '.btn-clear-cart']);
      if (s) steps.push(s);

      s = step({
        title: 'Proceder al pago',
        description: 'Cuando estés listo, haz clic aquí para ir al checkout y completar tu pedido.',
        side: 'top',
        align: 'center'
      }, ['#btnCheckout', '.checkout-btn', 'a[href*="checkout"]', 'a[href*="direccion"]', '.btn-checkout']);
      if (s) steps.push(s);

      steps.push(step({
        title: '¡Listo! 🎉',
        description: 'Ya sabes cómo gestionar tu carrito. Si necesitas ayuda, usa el botón "?" en la esquina.',
        align: 'center'
      }));
    } else {
      steps.push(step({
        title: 'Bienvenido a DistriCarnes 🥩',
        description: 'Explora nuestra tienda y descubre los mejores cortes de carne con la calidad que nos caracteriza.',
        align: 'center'
      }));

      s = step({
        title: 'Menú principal',
        description: 'Navega por el menú para acceder a productos, promociones, historial de pedidos y más.',
        side: 'bottom',
        align: 'center'
      }, ['#navMenu', '.nav-menu', 'header nav', 'nav']);
      if (s) steps.push(s);

      s = step({
        title: 'Mi cuenta',
        description: 'Gestiona tu perfil, revisa tu historial de pedidos, lista de deseos y más.',
        side: 'bottom',
        align: 'center'
      }, ['#userDropdown', '.user-profile', '#userLoggedButtons', '#authButtons']);
      if (s) steps.push(s);

      steps.push(step({
        title: '¡Listo! 🎉',
        description: 'Ya conoces lo esencial. Si necesitas volver a ver esta guía, usa el botón "?" en la esquina.',
        align: 'center'
      }));
    }

    return steps;
  }

  window.startPublicTour = function () {
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
      console.error('public_tour.js:', err && err.message ? err.message : err);
    });
  };

  function addHelpButton() {
    if (document.getElementById('dcTourBtnPublic')) return;
    var btn = document.createElement('button');
    btn.id = 'dcTourBtnPublic';
    btn.type = 'button';
    btn.title = 'Ver guía de la página';
    btn.setAttribute('aria-label', 'Ver guía de la página');
    btn.textContent = '?';
    btn.style.cssText = 'position:fixed;right:20px;bottom:20px;width:46px;height:46px;border-radius:50%;border:2px solid #ff0000;background:#fff;color:#000;font-size:22px;font-weight:700;cursor:pointer;z-index:2147483000;box-shadow:0 4px 14px rgba(0,0,0,.25);display:flex;align-items:center;justify-content:center;transition:transform .15s ease;';
    btn.addEventListener('mouseenter', function () { btn.style.transform = 'scale(1.08)'; });
    btn.addEventListener('mouseleave', function () { btn.style.transform = 'scale(1)'; });
    btn.addEventListener('click', function () { window.startPublicTour(); });
    document.body.appendChild(btn);
    helpBtn = btn;
  }

  function injectStyles() {
    if (document.getElementById('dcTourStylesPublic')) return;
    var style = document.createElement('style');
    style.id = 'dcTourStylesPublic';
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
    if (window.__dcPublicTourStarted) return;
    window.__dcPublicTourStarted = true;
    if (alreadySeen()) return;
    setTimeout(window.startPublicTour, 1200);
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

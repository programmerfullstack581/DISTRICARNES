/*
 * Indicador de carga para el panel de administración.
 * - NO bloquea la pantalla: es un aviso pequeño flotante arriba, sin pointer-events.
 * - Solo aparece si una petición GET tarda más de ~350 ms (no destella en cargas rápidas).
 * - Las peticiones de polling en segundo plano deben marcarse con { silent: true }.
 * - Expone showLoading(msg), hideLoading(), hideAllLoading(), setLoadingText(msg) por compatibilidad.
 * - Puede cargarse en <head> (crea el indicador al primer uso).
 */
(function () {
  if (window.__LoadingOverlay) return;

  var GRACE_MS = 350;

  var STYLE =
    '#loadingOverlay{position:fixed;top:14px;left:50%;transform:translateX(-50%);' +
    'display:none;align-items:center;gap:10px;z-index:1500;' +
    'background:rgba(20,20,20,0.92);border:1px solid #333;border-radius:999px;' +
    'padding:8px 16px;box-shadow:0 6px 18px rgba(0,0,0,0.5);' +
    'font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif;pointer-events:none;max-width:90%;}';
  STYLE +=
    '#loadingOverlay .loading-spinner{width:16px;height:16px;flex:0 0 auto;' +
    'border:2px solid rgba(255,255,255,0.25);border-top-color:#e61515;border-radius:50%;' +
    'animation:loadingSpin 0.9s linear infinite;}';
  STYLE += '@keyframes loadingSpin{to{transform:rotate(360deg)}}';
  STYLE +=
    '#loadingOverlay .loading-text{color:#fff;font-size:0.82rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}';
  STYLE += '#loadingOverlay .loading-dots::after{content:"...";}';

  var styleEl = document.createElement('style');
  styleEl.textContent = STYLE;
  (document.head || document.documentElement).appendChild(styleEl);

  var overlay = null;
  var textEl = null;
  var labelNode = null;

  function ensureOverlay() {
    if (overlay && document.body && document.body.contains(overlay)) return;
    if (!document.body) {
      document.addEventListener('DOMContentLoaded', ensureOverlay);
      return;
    }
    overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.setAttribute('aria-hidden', 'true');
    overlay.innerHTML =
      '<div class="loading-spinner"></div>' +
      '<div class="loading-text">Cargando información<span class="loading-dots"></span></div>';
    document.body.appendChild(overlay);
    textEl = overlay.querySelector('.loading-text');
    labelNode = textEl.firstChild;
  }

  var active = 0;
  var showTimer = null;
  var visible = false;

  function setText(msg) {
    ensureOverlay();
    if (!labelNode) return;
    if (msg) {
      labelNode.nodeValue = msg + ' ';
    } else {
      labelNode.nodeValue = 'Cargando información ';
    }
  }

  function render() {
    ensureOverlay();
    if (overlay && visible) {
      overlay.style.display = 'flex';
      overlay.setAttribute('aria-hidden', 'false');
    } else if (overlay) {
      overlay.style.display = 'none';
      overlay.setAttribute('aria-hidden', 'true');
    }
  }

  function show(msg) {
    active++;
    setText(msg);
    if (showTimer) { clearTimeout(showTimer); showTimer = null; }
    showTimer = setTimeout(function () {
      showTimer = null;
      if (active > 0 && !visible) {
        visible = true;
        render();
      }
    }, GRACE_MS);
  }

  function hide() {
    active = Math.max(0, active - 1);
    if (active === 0 && showTimer) {
      clearTimeout(showTimer);
      showTimer = null;
    }
    if (active === 0 && visible) {
      visible = false;
      render();
    }
  }

  function hideAll() {
    active = 0;
    if (showTimer) { clearTimeout(showTimer); showTimer = null; }
    visible = false;
    render();
  }

  window.showLoading = show;
  window.hideLoading = hide;
  window.hideAllLoading = hideAll;
  window.setLoadingText = setText;

  // Interceptar fetch: avisar solo si la petición GET (no silenciada) se demora.
  var origFetch = window.fetch;
  window.fetch = function (input, init) {
    var opts = init || {};
    var method = ((opts.method || (input && input.method)) || 'GET').toUpperCase();
    if (method !== 'GET' || opts.silent) {
      return origFetch.call(this, input, init);
    }
    show('Cargando información');
    var p;
    try {
      p = origFetch.call(this, input, init);
    } catch (e) {
      hide();
      throw e;
    }
    p.then(function () { hide(); }, function () { hide(); });
    return p;
  };

  window.__LoadingOverlay = true;
})();

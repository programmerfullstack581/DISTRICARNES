/*
 * Overlay de carga global para el panel de administración.
 * - Muestra "Cargando información..." mientras hay peticiones GET en curso.
 * - Las peticiones de polling en segundo plano deben marcarse con { silent: true }.
 * - Expone showLoading(msg), hideLoading(), hideAllLoading(), setLoadingText(msg).
 * - Puede cargarse en <head> (crea el overlay al primer uso).
 */
(function () {
  if (window.__LoadingOverlay) return;

  var STYLE =
    '#loadingOverlay{position:fixed;top:0;left:0;width:100%;height:100%;' +
    'background:rgba(0,0,0,0.72);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);' +
    'display:none;align-items:center;justify-content:center;flex-direction:column;z-index:1500;' +
    'font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif;}';
  STYLE +=
    '#loadingOverlay .loading-box{background:#1a1a1a;border:1px solid #333;border-top:3px solid #e61515;' +
    'border-radius:10px;padding:28px 40px;display:flex;flex-direction:column;align-items:center;gap:16px;' +
    'box-shadow:0 10px 30px rgba(0,0,0,0.6);max-width:90%;text-align:center;}';
  STYLE +=
    '#loadingOverlay .loading-spinner{width:46px;height:46px;border:4px solid rgba(255,255,255,0.15);' +
    'border-top-color:#e61515;border-radius:50%;animation:loadingSpin 0.9s linear infinite;}';
  STYLE += '@keyframes loadingSpin{to{transform:rotate(360deg)}}';
  STYLE +=
    '#loadingOverlay .loading-text{color:#fff;font-size:1.05rem;font-weight:500;letter-spacing:0.3px;}';
  STYLE +=
    '#loadingOverlay .loading-dots{display:inline-block;width:1.2em;text-align:left;' +
    'animation:loadingDots 1.2s steps(4,end) infinite;overflow:hidden;vertical-align:bottom;}';
  STYLE += '@keyframes loadingDots{0%{width:0}50%{width:1.2em}100%{width:1.2em}}';
  STYLE += '#loadingOverlay .loading-dots::after{content:"...";}';
  STYLE +=
    '#loadingOverlay .loading-hint{color:rgba(255,255,255,0.65);font-size:0.85rem;max-width:320px;}';

  var styleEl = document.createElement('style');
  styleEl.textContent = STYLE;
  (document.head || document.documentElement).appendChild(styleEl);

  var overlay = null;
  var textEl = null;
  var hintEl = null;
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
      '<div class="loading-box">' +
      '<div class="loading-spinner"></div>' +
      '<div class="loading-text">Cargando información<span class="loading-dots"></span></div>' +
      '<div class="loading-hint"></div>' +
      '</div>';
    document.body.appendChild(overlay);
    textEl = overlay.querySelector('.loading-text');
    hintEl = overlay.querySelector('.loading-hint');
    labelNode = textEl.firstChild;
  }

  var active = 0;
  var slowTimer = null;

  function setText(msg) {
    ensureOverlay();
    if (!labelNode) return;
    if (msg) {
      labelNode.nodeValue = msg + ' ';
    } else {
      labelNode.nodeValue = 'Cargando información ';
    }
  }

  function show(msg) {
    active++;
    ensureOverlay();
    setText(msg);
    overlay.style.display = 'flex';
    overlay.setAttribute('aria-hidden', 'false');
    if (!slowTimer) {
      slowTimer = setTimeout(function () {
        if (hintEl) {
          hintEl.textContent =
            'La información está tardando más de lo habitual, por favor espera un momento más...';
        }
      }, 12000);
    }
  }

  function hide() {
    active = Math.max(0, active - 1);
    if (active === 0 && overlay) {
      overlay.style.display = 'none';
      overlay.setAttribute('aria-hidden', 'true');
      if (slowTimer) { clearTimeout(slowTimer); slowTimer = null; }
      if (hintEl) hintEl.textContent = '';
    }
  }

  function hideAll() {
    active = 0;
    if (overlay) {
      overlay.style.display = 'none';
      overlay.setAttribute('aria-hidden', 'true');
      if (slowTimer) { clearTimeout(slowTimer); slowTimer = null; }
      if (hintEl) hintEl.textContent = '';
    }
  }

  window.showLoading = show;
  window.hideLoading = hide;
  window.hideAllLoading = hideAll;
  window.setLoadingText = setText;

  // Interceptar fetch: mostrar el overlay en peticiones GET (no silenciadas).
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

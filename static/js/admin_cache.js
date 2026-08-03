/*
 * Caché ligera para el panel admin (localStorage).
 * - adminCacheGet(url): devuelve copia fresca o null.
 * - adminCacheSet(url, data, ttlMs): guarda una copia.
 * - adminCacheFetchJson(url, opts): stale-while-revalidate. Si hay copia fresca
 *   la devuelve al instante y refresca en segundo plano; si no, hace fetch.
 * TTL por defecto: 45s (el admin debe verse fresco; la caché de servidor refuerza).
 */
(function () {
  if (window.__AdminCache) return;
  var PREFIX = 'dc_cache_';
  var MEM = {};

  function rawGet(url) {
    var k = PREFIX + url;
    try {
      var o = JSON.parse(localStorage.getItem(k) || 'null');
      if (o && typeof o.t === 'number' && (Date.now() - o.t) < o.ttl) {
        return o.d;
      }
    } catch (e) {}
    return null;
  }

  function rawSet(url, data, ttlMs) {
    var k = PREFIX + url;
    try {
      localStorage.setItem(k, JSON.stringify({ t: Date.now(), ttl: ttlMs, d: data }));
      MEM[k] = data;
    } catch (e) {}
  }

  function refresh(url, opts) {
    fetch(url, { cache: 'no-store', ...(opts.fetch || {}) })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        rawSet(url, d, opts.ttl || 45000);
        window.dispatchEvent(new CustomEvent('admin:data', { detail: { url: url, data: d } }));
      })
      .catch(function () {});
  }

  window.adminCacheGet = function (url) { return rawGet(url); };
  window.adminCacheSet = function (url, data, ttlMs) { rawSet(url, data, ttlMs); };
  window.adminCacheClear = function () {
    try {
      var keys = [];
      for (var i = 0; i < localStorage.length; i++) {
        var k = localStorage.key(i);
        if (k && k.indexOf(PREFIX) === 0) keys.push(k);
      }
      keys.forEach(function (k) { localStorage.removeItem(k); });
    } catch (e) {}
  };
  window.adminCacheFetchJson = function (url, opts) {
    opts = opts || {};
    var cached = rawGet(url);
    if (cached !== null) {
      refresh(url, opts);
      return Promise.resolve({ data: cached, fromCache: true });
    }
    return fetch(url, { cache: 'no-store', ...(opts.fetch || {}) })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        rawSet(url, d, opts.ttl || 45000);
        return { data: d, fromCache: false };
      });
  };

  window.__AdminCache = true;
})();

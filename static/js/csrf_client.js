/* CSRF client — DISTRICARNES
   Obtiene el token CSRF desde el backend y lo adjunta a las peticiones de
   escritura (POST/PUT/PATCH/DELETE) mediante el header X-CSRF-Token.
   Expone:
     window.dcCsrfToken()  -> Promise<string>
     window.dcFetchJson(url, options) -> fetch() con token inyectado
   El token se cachea en memoria; si el servidor devuelve 403 por token
   inválido, se descarta y se reintenta una vez. */
(function () {
  'use strict';
  if (window.__DcCsrfLoaded) return;
  window.__DcCsrfLoaded = true;
  var cached = null;
  var pending = null;

  function endpoint() {
    if (window.DC_CSRF_ENDPOINT) return window.DC_CSRF_ENDPOINT;
    var here = window.location.pathname || '/';
    var dir = here.substring(0, here.lastIndexOf('/') + 1);
    var base = (dir === '/' || dir === '') ? '' : (dir + '../');
    return base + 'backend/php/core/csrf_token.php';
  }

  window.dcCsrfToken = function () {
    if (cached) return Promise.resolve(cached);
    if (pending) return pending;
    pending = fetch(endpoint(), { method: 'GET', credentials: 'same-origin', cache: 'no-store' })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d && d.ok && d.token) {
          cached = d.token;
          return d.token;
        }
        throw new Error('No se pudo obtener token CSRF');
      })
      .finally(function () { pending = null; });
    return pending;
  };

  window.dcCsrfReset = function () { cached = null; };

  window.dcFetchJson = function (url, options) {
    var o = options || {};
    o.credentials = o.credentials || 'same-origin';
    if (o.cache === undefined) o.cache = 'no-store';

    var method = (o.method || 'GET').toUpperCase();
    var isWrite = method === 'POST' || method === 'PUT' || method === 'PATCH' || method === 'DELETE';

    function doFetch(withToken) {
      var headers = Object.assign({}, o.headers || {});
      if (withToken) headers['X-CSRF-Token'] = withToken;
      var opts = Object.assign({}, o, { headers: headers });
      return fetch(url, opts).then(function (res) {
        if (res.status === 403) return res.json().then(function (d) {
          var err = new Error((d && d.error) || 'Solicitud rechazada');
          err.status = 403;
          throw err;
        });
        return res;
      });
    }

    if (!isWrite) return doFetch(null);

    return window.dcCsrfToken()
      .then(function (token) { return doFetch(token); })
      .catch(function (err) {
        // Reintentar una vez con token fresco si el anterior se invalidó
        if (err && err.status === 403) {
          window.dcCsrfReset();
          return window.dcCsrfToken().then(function (token) { return doFetch(token); });
        }
        throw err;
      });
  };
})();

/*
 * Indicador de carga para el panel de administración.
 *
 * Desactivado: ya NO se muestra la pastilla flotante "Cargando información..."
 * en la parte superior de la pantalla (debajo del header). El loading ahora
 * se muestra dentro del contenido de cada página (spinner rojo girando),
 * igual que en la página de Facturas.
 *
 * Se conservan las funciones de compatibilidad (showLoading, hideLoading,
 * hideAllLoading, setLoadingText) como no-op para no romper código existente.
 */
(function () {
  if (window.__LoadingOverlay) return;

  window.showLoading = function () {};
  window.hideLoading = function () {};
  window.hideAllLoading = function () {};
  window.setLoadingText = function () {};

  window.__LoadingOverlay = true;
})();

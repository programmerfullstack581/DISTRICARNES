// Actualiza el badge del carrito leyendo por usuario
(function(){
  function getUserKey(){
    try{
      const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
      if(!rawStr) return null;
      const raw = JSON.parse(rawStr);
      const user = raw && raw.user ? raw.user : raw;
      const email = (user && (user.correo_electronico || user.email)) || '';
      const id = (user && (user.id_usuario || user.id)) || '';
      const key = email || String(id||'').trim();
      return key || null;
    }catch(e){ return null; }
  }

  function getCartKey(){
    const userKey = getUserKey();
    // Si no hay usuario autenticado, no contamos carrito
    return userKey ? ('cart_items:' + userKey) : null;
  }

  function readCart(){
    const key = getCartKey();
    if(!key) return [];
    try{
      const raw = localStorage.getItem(key);
      return raw ? (JSON.parse(raw) || []) : [];
    }catch(e){ return []; }
  }

  function countItems(items){
    // Retornamos la cantidad de tipos de productos diferentes (longitud del array)
    return items.length;
  }

  function updateBadge(){
    const items = readCart();
    const value = String(countItems(items));
    const ids = ['cartCount', 'mhCartCount'];
    for (var i = 0; i < ids.length; i++) {
      var el = document.getElementById(ids[i]);
      if (el) el.textContent = value;
    }
  }

  window.CartBadge = { update: updateBadge };
  document.addEventListener('DOMContentLoaded', updateBadge);
  window.addEventListener('cart:updated', updateBadge);
  window.addEventListener('auth:loggedOut', updateBadge);
  window.addEventListener('userLogin', updateBadge);
  window.addEventListener('storage', function(e){ if (e && (e.key === 'userData' || e.key === 'currentSession')) updateBadge(); });
})();

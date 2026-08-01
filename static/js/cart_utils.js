/* Utilidades del carrito con persistencia por usuario */
(function () {
  function getUserKey() {
    try {
      const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
      if (!rawStr) return null;
      const raw = JSON.parse(rawStr);
      const user = raw && raw.user ? raw.user : raw;
      const email = (user && (user.correo_electronico || user.email)) || '';
      const id = (user && (user.id_usuario || user.id)) || '';
      const key = email || String(id || '').trim();
      return key || null;
    } catch (e) { return null; }
  }

  function getCartKey() {
    const userKey = getUserKey();
    // Si no hay usuario autenticado, no permitimos usar carrito
    return userKey ? ('cart_items:' + userKey) : null;
  }

  function getCart() {
    const key = getCartKey();
    if (!key) return [];
    try { return JSON.parse(localStorage.getItem(key) || '[]'); } catch (e) { return []; }
  }
  function saveCart(items) {
    const key = getCartKey();
    if (!key) return;
    localStorage.setItem(key, JSON.stringify(items));
    window.dispatchEvent(new CustomEvent('cart:updated', { detail: { items } }));
  }
  function safeParsePrice(str) {
    if (typeof str !== 'string') return 0;
    const num = Number(str.replace(/[^0-9.,-]/g, '').replace(',', '.'));
    return isNaN(num) ? 0 : num;
  }
  function addItem({ id, title, price, image, qty = 1 }) {
    const key = getCartKey();
    // Bloquear agregado si no hay sesión iniciada
    if (!key) return false;
    const items = getCart();
    const uniq = id || `${title}-${price}`;
    const found = items.find(i => (i.id || `${i.title}-${i.price}`) === uniq);
    if (found) { found.qty = (found.qty || 1) + qty; }
    else { items.push({ id, title: title || 'Producto', price: Number(price) || 0, image: image || '', qty }); }
    saveCart(items);
    return true;
  }

  async function showQuantityModal({ title, price, image, id }) {
    const { value: qty } = await Swal.fire({
      title: '<span style="color: #fff;">¿Cuántos kilos desea agregar?</span>',
      html: `
        <div style="text-align: center; color: #ccc; margin-bottom: 15px;">
          <img src="${image}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
          <h4 style="color: #fff; margin: 0;">${title}</h4>
          <p style="color: #ff0000; font-weight: bold; font-size: 1.2rem; margin-top: 5px;">${new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(price)} / Kg</p>
        </div>
        <div class="kg-selector" style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-top: 20px;">
          <button type="button" onclick="this.nextElementSibling.stepDown(); this.nextElementSibling.dispatchEvent(new Event('input'))" style="background: #333; color: #fff; border: 1px solid #444; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 20px;">-</button>
          <input type="number" id="swal-input-qty" class="swal2-input" value="1" min="0.5" step="0.5" style="margin: 0; width: 100px; text-align: center; background: #222; color: #fff; border: 1px solid #444;">
          <button type="button" onclick="this.previousElementSibling.stepUp(); this.previousElementSibling.dispatchEvent(new Event('input'))" style="background: #ff0000; color: #fff; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 20px;">+</button>
        </div>
        <p style="color: #888; font-size: 0.9rem; margin-top: 10px;">Incrementos de 0.5 Kg</p>
      `,
      background: '#1a1a1a',
      showCancelButton: true,
      confirmButtonText: 'Agregar al Carrito',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#ff0000',
      cancelButtonColor: '#444',
      focusConfirm: false,
      preConfirm: () => {
        const val = parseFloat(document.getElementById('swal-input-qty').value);
        if (isNaN(val) || val <= 0) {
          Swal.showValidationMessage('Por favor ingrese una cantidad válida');
          return false;
        }
        return val;
      }
    });
    return qty;
  }

  function bindButtons() {
    // Botones genéricos en promos/productos
    document.querySelectorAll('.add-to-cart-btn, .btn.btn-add, .boton-item, .add-to-cart').forEach((btn) => {
      // Evitar doble binding si ya tiene el listener de promociones
      if (btn.hasAttribute('data-cart-bound')) return;
      btn.setAttribute('data-cart-bound', 'true');

      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        
        const card = btn.closest('.product-card') || btn.closest('.promo-card') || btn.closest('.item') || document.body;
        const id = btn.dataset.id || null;
        const title = btn.dataset.title || card.querySelector('.product-name, .promo-title, .titulo-item, .card-title, .product-title')?.textContent?.trim() || 'Producto';
        const priceText = btn.dataset.price || card.querySelector('.product-price, .new-price, .precio-item, .current-price')?.textContent;
        const price = priceText ? safeParsePrice(priceText) : Number(btn.dataset.price || 0);
        const image = btn.dataset.image || card.querySelector('img.product-image, img.img-item, .promo-img img, .product-image img, img')?.src || '';
        
        const userKey = getCartKey();
        if (!userKey) {
          Swal.fire({
            icon: 'warning',
            title: 'Debes iniciar sesión para continuar',
            text: 'Inicia sesión para agregar productos al carrito.',
            showCancelButton: true,
            confirmButtonText: 'Iniciar sesión',
            cancelButtonText: 'Cerrar',
            confirmButtonColor: '#ff0000',
            background: '#1a1a1a',
            color: '#fff'
          }).then((r) => { if (r && r.isConfirmed) { window.location.href = (window.location.pathname.includes('/admin/') || window.location.pathname.includes('/login/')) ? '../login/login.php' : './login/login.php'; } });
          return;
        }

        const qty = await showQuantityModal({ title, price, image, id });
        if (qty) {
          const ok = addItem({ id, title, price, image, qty });
          if (ok) {
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: `¡${qty} Kg de ${title} agregados!`,
              showConfirmButton: false,
              timer: 3000,
              background: '#1a1a1a',
              color: '#fff'
            });
          }
        }
      });
    });
  }

  window.CartUtils = { getCart, saveCart, addItem, safeParsePrice, showQuantityModal };
  window.addEventListener('DOMContentLoaded', bindButtons);
  // Al cerrar sesión, notificar limpieza visual (persistencia queda atada al usuario)
  window.addEventListener('auth:loggedOut', () => {
    window.dispatchEvent(new CustomEvent('cart:updated', { detail: { items: [] } }));
  });
})();

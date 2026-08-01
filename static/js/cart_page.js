/* Página del carrito: CRUD con persistencia por usuario */
(function(){
  // Umbral para envío gratis (COP)
  var FREE_SHIP_THRESHOLD = 200000;

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
    // Si no hay usuario autenticado, no hay carrito
    return userKey ? ('cart_items:' + userKey) : null;
  }

  function getCart(){
    const key = getCartKey();
    if(!key) return [];
    try{ return JSON.parse(localStorage.getItem(key) || '[]'); }catch(e){ return []; }
  }
  function saveCart(items){
    const key = getCartKey();
    if(!key) return;
    localStorage.setItem(key, JSON.stringify(items));
    window.dispatchEvent(new CustomEvent('cart:updated',{detail:{items}}));
  }

  function getSaved(){
    const u = getUserKey() || 'guest';
    try{ return JSON.parse(localStorage.getItem('saved_items:' + u) || '[]'); }catch(e){ return []; }
  }
  function saveSaved(items){
    const u = getUserKey() || 'guest';
    localStorage.setItem('saved_items:' + u, JSON.stringify(items));
  }

  function formatCurrency(n){
    const v = Number(n||0);
    try{
      return v.toLocaleString('es-CO',{style:'currency',currency:'COP', maximumFractionDigits:0});
    }catch(e){
      return '$' + Number(v).toLocaleString('es-CO');
    }
  }

  function render(){
    const rowsEl = document.getElementById('cartRows');
    const emptyEl = document.getElementById('emptyCart');
    const countEl = document.getElementById('cartCount');
    const items = getCart();
    if (rowsEl) rowsEl.innerHTML = '';
    if (countEl) countEl.textContent = String(items.length);
    if(!items.length){
      if (emptyEl) emptyEl.style.display = 'block';
    } else {
      if (emptyEl) emptyEl.style.display = 'none';
      items.forEach((item, idx)=>{
        const row = document.createElement('div');
        row.className = 'cart-item';
        row.innerHTML = `
          <img class="cart-img" src="${item.image||''}" alt="${(item.title||'Producto').replace(/"/g,'&quot;')}"/>
          <div class="cart-info">
            <div class="cart-title">${item.title||'Producto'}</div>
            <div class="cart-meta">Precio: <span class="cart-unit-price">${formatCurrency(Number(item.price||0))}</span> /Kg &middot; <button type="button" class="save-later">Guardar para después</button></div>
          </div>
          <div class="qty">
            <button type="button" class="btn-dec" aria-label="Disminuir">-</button>
            <input class="qty-input" type="number" min="0.5" step="0.5" value="${Number(item.qty||1)}" />
            <span class="qty-unit">Kg</span>
            <button type="button" class="btn-inc" aria-label="Aumentar">+</button>
          </div>
          <div class="cart-price">${formatCurrency(item.price * (item.qty||1))}</div>
          <button type="button" class="remove-btn" aria-label="Eliminar"><i class="fas fa-trash-alt"></i></button>
        `;
        // Eventos
        row.querySelector('.btn-dec').addEventListener('click', ()=> updateQty(idx, (Number(item.qty||1)-0.5)) );
        row.querySelector('.btn-inc').addEventListener('click', ()=> updateQty(idx, (Number(item.qty||1)+0.5)) );
        row.querySelector('.qty-input').addEventListener('change', (e)=> updateQty(idx, Number(e.target.value||1)) );
        row.querySelector('.remove-btn').addEventListener('click', ()=> removeItem(idx) );
        const saveEl = row.querySelector('.save-later');
        if (saveEl) saveEl.addEventListener('click', ()=> saveForLater(idx));
        rowsEl.appendChild(row);
      });
    }
    renderSaved();
    recalcTotals();
  }

  function renderSaved(){
    const section = document.getElementById('savedSection');
    const rowsEl = document.getElementById('savedRows');
    if (!section || !rowsEl) return;
    const saved = getSaved();
    if (!saved.length){ section.style.display = 'none'; return; }
    section.style.display = 'block';
    rowsEl.innerHTML = '';
    saved.forEach((item, idx)=>{
      const el = document.createElement('div');
      el.className = 'saved-item';
      el.innerHTML = `
        <img src="${item.image||''}" alt="${(item.title||'Producto').replace(/"/g,'&quot;')}"/>
        <div class="cart-info">
          <div class="cart-title">${item.title||'Producto'}</div>
          <div class="cart-meta">${formatCurrency(Number(item.price||0))} /Kg</div>
        </div>
        <div class="saved-actions">
          <button type="button" class="move-back" aria-label="Mover al carrito"><i class="fas fa-cart-plus"></i> Mover</button>
          <button type="button" class="remove-saved" aria-label="Eliminar"><i class="fas fa-trash-alt"></i></button>
        </div>
      `;
      el.querySelector('.move-back').addEventListener('click', ()=> moveToCart(idx));
      el.querySelector('.remove-saved').addEventListener('click', ()=> removeSaved(idx));
      rowsEl.appendChild(el);
    });
  }

  function updateQty(index, qty){
    const items = getCart();
    if(!items[index]) return;
    const newQty = Math.max(0.5, Number(qty||0.5));
    items[index].qty = newQty;
    saveCart(items);
    render();
  }

  function removeItem(index){
    const items = getCart();
    items.splice(index,1);
    saveCart(items);
    render();
  }

  function clearCart(){
    const key = getCartKey();
    if(!key) return;
    localStorage.setItem(key, JSON.stringify([]));
    window.dispatchEvent(new CustomEvent('cart:updated',{detail:{items:[]}}));
    render();
  }

  function saveForLater(index){
    const items = getCart();
    const saved = getSaved();
    const removed = items.splice(index,1);
    if (removed && removed.length){ saved.push(removed[0]); }
    saveSaved(saved);
    saveCart(items);
    if (window.toast){ toast({ icon:'success', title:'Guardado para después' }); }
    render();
  }

  function moveToCart(savedIdx){
    const saved = getSaved();
    const moved = saved.splice(savedIdx,1);
    saveSaved(saved);
    if (moved && moved.length){
      const items = getCart();
      items.push(moved[0]);
      saveCart(items);
      if (window.toast){ toast({ icon:'success', title:'Movido al carrito' }); }
    }
    render();
  }

  function removeSaved(savedIdx){
    const saved = getSaved();
    saved.splice(savedIdx,1);
    saveSaved(saved);
    render();
  }

  async function recalcTotals(){
    const items = getCart();
    const subtotal = items.reduce((sum,i)=> sum + (Number(i.price||0) * Number(i.qty||1)), 0);
    // IVA incluido (asumiendo precios con IVA): 19% por defecto
    const IVA_RATE = 0.19;
    const base = subtotal / (1 + IVA_RATE);
    const tax = Math.max(0, subtotal - base);
    // Cotizar envío real con backend (estimación: domicilio)
    let shipping = 0;
    try{
      const res = await fetch('../backend/php/shipping_quote.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ items, delivery: 'domicilio' })
      });
      const out = await res.json();
      if(out && out.ok){ shipping = Number(out.cost||0); }
    }catch(e){ shipping = 0; }
    // Promos
    const promo = getPromo();
    let discount = 0;
    if (promo && promo.type === 'percent') discount = Math.round(subtotal * (promo.value||0));
    if (promo && promo.type === 'fixed') discount = Math.round(promo.value||0);
    // Envío gratis por umbral o por cupón
    const freeByThreshold = subtotal >= FREE_SHIP_THRESHOLD;
    if (freeByThreshold || (promo && promo.freeShipping)) shipping = 0;
    const total = Math.max(0, subtotal - discount) + shipping;

    document.getElementById('subtotal').textContent = formatCurrency(subtotal);
    const taxEl = document.getElementById('tax');
    if (taxEl) taxEl.textContent = formatCurrency(tax);
    const shippingEl = document.getElementById('shipping');
    if (shippingEl) shippingEl.textContent = shipping > 0 ? formatCurrency(shipping) : 'Gratis';
    const freeShipMsg = document.getElementById('freeShippingMsg');
    if (freeShipMsg) {
      if (freeByThreshold || (promo && promo.freeShipping)) {
        freeShipMsg.style.display = 'block';
        freeShipMsg.textContent = freeByThreshold ? '¡Envío gratis aplicado!' : '¡Envío gratis por cupón!';
      } else {
        freeShipMsg.style.display = 'none';
      }
    }
    const discountRow = document.getElementById('discountRow');
    if (discountRow) {
      if (discount > 0) {
        discountRow.style.display = '';
        const dEl = document.getElementById('discount');
        if (dEl) dEl.textContent = '-' + formatCurrency(discount);
      } else {
        discountRow.style.display = 'none';
      }
    }
    document.getElementById('total').textContent = formatCurrency(total);

    // Progreso hacia envío gratis
    const fill = document.getElementById('shippingBarFill');
    const msg = document.getElementById('shippingGoalMsg');
    if (fill && msg) {
      if (items.length === 0) {
        fill.style.width = '0%';
        msg.textContent = 'Agrega productos a tu carrito.';
      } else if (freeByThreshold) {
        fill.style.width = '100%';
        msg.innerHTML = '¡Felicidades! Tienes <b>envío gratis</b>.';
      } else {
        const pct = Math.min(100, Math.round((subtotal / FREE_SHIP_THRESHOLD) * 100));
        fill.style.width = pct + '%';
        const falta = Math.max(0, FREE_SHIP_THRESHOLD - subtotal);
        msg.innerHTML = 'Te faltan <b>' + formatCurrency(falta) + '</b> para el envío gratis.';
      }
    }
  }

  function getPromo(){
    try{ return JSON.parse(sessionStorage.getItem('cartPromo')||'null'); }catch(e){ return null; }
  }
  function setPromo(p){ sessionStorage.setItem('cartPromo', JSON.stringify(p||null)); }

  function guessPromo(code){
    const c = (code||'').trim().toUpperCase();
    if (!c) return null;
    if (['DC10','DISTRICARNES10','PROMO10'].includes(c)) return { type:'percent', value:0.10 };
    if (['ENVIOGRATIS','FREESHIP'].includes(c)) return { type:'fixed', value:0, freeShipping:true };
    return null;
  }

  function setSessionInfo(){
    const el = document.getElementById('sessionInfo');
    if(!el) return;
    // Si ya existe clave de usuario para el carrito, ocultar el mensaje
    const hasUserKey = !!getUserKey();
    if (hasUserKey) {
      el.style.display = 'none';
      return;
    }
    let user = null;
    let logged = false;
    // Preferir el sistema global de auth si está presente
    try{
      if (window.AuthSystem) {
        if (typeof window.AuthSystem.getCurrentUser === 'function') {
          user = window.AuthSystem.getCurrentUser();
        }
        if (typeof window.AuthSystem.isLoggedIn === 'function') {
          logged = window.AuthSystem.isLoggedIn();
        }
      }
    }catch(e){ /* noop */ }
    // Fallback: leer de userData / currentSession (formato { isLoggedIn, user })
    if(!user){
      try{
        const rawStr = localStorage.getItem('userData') || sessionStorage.getItem('currentSession');
        if(rawStr){
          const raw = JSON.parse(rawStr);
          user = raw && raw.user ? raw.user : raw;
          logged = Boolean(raw && (raw.isLoggedIn || raw.user || raw.email || raw.correo_electronico));
        }
      }catch(e){ /* noop */ }
    }
    if(logged && user && typeof user === 'object'){
      const name = user.nombres_completos || user.nombre || user.name || '';
      const email = user.correo_electronico || user.email || '';
      const display = name || email || 'Usuario';
      el.textContent = 'Sesión iniciada: ' + display + '. Continúa con tu compra.';
      el.classList && el.classList.remove('hidden');
      el.style.display = 'block';
    } else {
      el.textContent = 'No has iniciado sesión. Inicia sesión para continuar.';
      el.classList && el.classList.remove('hidden');
      el.style.display = 'block';
    }
  }

  function goCheckout(){
    const items = getCart();
    if(!items.length){
      if(window.toast){ toast({ icon:'info', title:'Tu carrito está vacío' }); }
      return;
    }
    if(window.toast){ toast({ icon:'info', title:'Cargando checkout…' }); }
    setTimeout(()=>{ window.location.href = '../checkout/direccion.php'; }, 300);
  }

  // Pago gestionado únicamente en la página de checkout

  function init(){
    setSessionInfo();
    render();
    const btnCheckout = document.getElementById('btnCheckout');
    const btnClear = document.getElementById('btnClear');
    const btnClearHead = document.getElementById('btnClearHead');
    const btnPayPalQuick = document.getElementById('btnPayPalQuick');
    const applyPromoBtn = document.getElementById('applyPromo');
    const promoInput = document.getElementById('promoInput');
    const promoStatus = document.getElementById('promoStatus');
    if(btnCheckout) btnCheckout.addEventListener('click', goCheckout);

    function askClear(){
      if(window.Swal){
        Swal.fire({
          title:'¿Vaciar carrito?',
          text:'Se eliminarán todos los productos.',
          icon:'warning',
          showCancelButton:true,
          confirmButtonColor:'#e50914',
          cancelButtonColor:'#333',
          confirmButtonText:'Sí, vaciar'
        }).then((r)=>{ if(r.isConfirmed) clearCart(); });
      } else { clearCart(); }
    }
    if(btnClear) btnClear.addEventListener('click', askClear);
    if(btnClearHead) btnClearHead.addEventListener('click', askClear);

    if (btnPayPalQuick) btnPayPalQuick.addEventListener('click', ()=>{
      sessionStorage.setItem('preferredPay','paypal');
      goCheckout();
    });
    if (applyPromoBtn) applyPromoBtn.addEventListener('click', applyPromoHandler);
    if (promoInput) promoInput.addEventListener('keydown', (e)=>{
      if (e.key === 'Enter'){ e.preventDefault(); applyPromoHandler(); }
    });

    function applyPromoHandler(){
      const code = (promoInput && promoInput.value) || '';
      const p = guessPromo(code);
      if (!p){
        setPromo(null);
        if (promoStatus) promoStatus.textContent = '';
        if (window.toast) toast({ icon:'info', title:'Código no válido' });
      } else {
        setPromo(p);
        if (promoStatus) promoStatus.textContent = 'Código "' + (code||'').toUpperCase() + '" aplicado';
        if (window.toast) toast({ icon:'success', title:'Código aplicado' });
      }
      recalcTotals();
    }

    window.addEventListener('cart:updated', ()=>{ render(); });
    window.addEventListener('auth:loggedOut', ()=>{ setSessionInfo(); render(); });
    window.addEventListener('userLogin', ()=>{ setSessionInfo(); render(); });
    window.addEventListener('storage', (e)=>{
      if (e.key === 'userData' || e.key === 'currentSession') { setSessionInfo(); }
    });
  }

  window.addEventListener('DOMContentLoaded', init);
})();

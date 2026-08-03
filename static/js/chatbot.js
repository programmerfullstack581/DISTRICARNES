// ===== CHATBOT DISTRICARNES HERMANOS NAVARRO =====

function toggleChatbot() {
    const chatbot = document.querySelector('.chatbot-container');
    chatbot.classList.toggle('active');
    if (chatbot.classList.contains('active')) {
        setTimeout(() => {
            const input = document.getElementById('userInput') || document.querySelector('.chat-input');
            if (input) input.focus();
        }, 200);
    }
}

function sendMessageUI() {
    const input = document.querySelector('.chat-input');
    const messageText = input.value.trim();
    if (!messageText) return;

    // Dejado como wrapper por compatibilidad; usa la versión con IA
    const inputEl = document.getElementById('userInput');
    if (inputEl) inputEl.value = messageText;
    sendMessage();
}

function handleKeyPress(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

function getCurrentTime() {
    const now = new Date();
    return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function showTypingIndicator() {
    const messages = document.querySelector('.chatbot-messages');
    let typingIndicator = document.querySelector('.typing-indicator');
    if (!typingIndicator) {
        typingIndicator = document.createElement('div');
        typingIndicator.className = 'typing-indicator';
        typingIndicator.innerHTML = '<div class="dot"></div><div class="dot"></div><div class="dot"></div>';
        messages.appendChild(typingIndicator);
    }
    typingIndicator.style.display = 'flex';
    messages.scrollTop = messages.scrollHeight;
}

function hideTypingIndicator() {
    const typingIndicator = document.querySelector('.typing-indicator');
    if (typingIndicator) {
        typingIndicator.style.display = 'none';
    }
}

// ===== RESPUESTAS INTELIGENTES DEL BOT =====
function getBotResponse(message) {
    message = message.toLowerCase();
    
    // Productos cárnicos
    if (message.includes('productos') || message.includes('carnes') || message.includes('carne')) {
        return getProductsResponse(message);
    }
    
    // Tipos de cortes
    else if (message.includes('cortes') || message.includes('corte') || message.includes('filete') || message.includes('chuleta')) {
        return getCutsResponse(message);
    }
    
    // Precios y ofertas
    else if (message.includes('precio') || message.includes('precios') || message.includes('costo') || message.includes('ofertas') || message.includes('descuento')) {
        return getPricesResponse();
    }
    
    // Horarios y ubicación
    else if (message.includes('horario') || message.includes('horarios') || message.includes('abierto') || message.includes('ubicación') || message.includes('dirección')) {
        return getScheduleLocationResponse();
    }
    
    // Información sobre la empresa
    else if (message.includes('sobre') || message.includes('empresa') || message.includes('historia') || message.includes('navarro') || message.includes('hermanos')) {
        return getAboutResponse();
    }
    
    // Contacto
    else if (message.includes('contacto') || message.includes('teléfono') || message.includes('whatsapp') || message.includes('llamar')) {
        return getContactResponse();
    }
    
    // Calidad y frescura
    else if (message.includes('fresco') || message.includes('frescura') || message.includes('calidad') || message.includes('premium')) {
        return getQualityResponse();
    }
    
    // Preparación y consejos
    else if (message.includes('cocinar') || message.includes('preparar') || message.includes('receta') || message.includes('consejos')) {
        return getCookingTipsResponse(message);
    }
    
    // Disponibilidad
    else if (message.includes('disponible') || message.includes('stock') || message.includes('hay') || message.includes('tienen')) {
        return getAvailabilityResponse();
    }
    
    // Saludos
    else if (message.includes('hola') || message.includes('buenos') || message.includes('buenas') || message.includes('saludos')) {
        return '¡Hola! 👋 Bienvenido a DISTRICARNES Hermanos Navarro. Somos especialistas en carnes premium con más de 28 años de tradición. ¿En qué puedo ayudarte hoy?';
    }
    
    // Despedidas
    else if (message.includes('gracias') || message.includes('adiós') || message.includes('chao') || message.includes('bye')) {
        return '¡De nada! 😊 Gracias por elegir DISTRICARNES Hermanos Navarro. ¡Esperamos verte pronto en nuestra carnicería! 🥩';
    }
    
    // Respuesta por defecto
    else {
        return getDefaultResponse();
    }
}

// ===== RESPUESTAS ESPECÍFICAS =====
function getProductsResponse(message) {
    const responses = [
        '🥩 <strong>Nuestros productos estrella:</strong><br>• Carne de res premium (lomo, filete, chuleta)<br>• Carne de cerdo fresca (lomo, chuleta, costillas)<br>• Pollo fresco y orgánico<br>• Pescados y mariscos del día<br>• Embutidos artesanales<br><br>¿Te interesa algún producto en particular?',
        '🍖 <strong>Carnes Premium disponibles:</strong><br>• Filete de res (corte especial)<br>• Lomo de cerdo fresco<br>• Chuletas de cerdo<br>• Robalo fresco<br>• Pollo de granja<br><br>Todas nuestras carnes son seleccionadas cuidadosamente para garantizar la máxima calidad.',
        '🥓 <strong>Especialidades de la casa:</strong><br>• Carne BBQ marinada<br>• Cortes premium para asados<br>• Pescados frescos del día<br>• Embutidos caseros<br>• Carnes orgánicas<br><br>¡Pregúntame por disponibilidad y precios!'
    ];
    return responses[Math.floor(Math.random() * responses.length)];
}

function getCutsResponse(message) {
    if (message.includes('res') || message.includes('beef')) {
        return '🥩 <strong>Cortes de res disponibles:</strong><br>• Filete mignon<br>• Lomo alto y bajo<br>• Chuleta de res<br>• Costillas<br>• Carne para guisar<br>• Carne molida premium<br><br>Todos nuestros cortes son frescos y de la mejor calidad.';
    } else if (message.includes('cerdo') || message.includes('pork')) {
        return '🐷 <strong>Cortes de cerdo frescos:</strong><br>• Lomo de cerdo<br>• Chuletas de cerdo<br>• Costillas BBQ<br>• Tocino fresco<br>• Pernil<br>• Carne molida de cerdo<br><br>Perfectos para cualquier ocasión especial.';
    } else {
        return '🔪 <strong>Nuestros cortes especializados:</strong><br>• Cortes de res premium<br>• Cortes de cerdo frescos<br>• Filetes de pescado<br>• Cortes para BBQ<br>• Cortes para guisos<br><br>¿Qué tipo de corte necesitas? ¡Puedo darte más detalles!';
    }
}

function getPricesResponse() {
    return '💰 <strong>Información de precios:</strong><br>• Manejamos precios competitivos y justos<br>• Ofertas especiales los fines de semana<br>• Descuentos por compras al mayor<br>• Promociones en productos de temporada<br><br>📞 Para precios específicos, contáctanos directamente. ¡Los precios pueden variar según disponibilidad!';
}

function getScheduleLocationResponse() {
    return '🕒 <strong>Horarios de atención:</strong><br>• Lunes a Sábado: 7:00 AM - 7:00 PM<br>• Domingos: 8:00 AM - 2:00 PM<br><br>📍 <strong>Ubicación:</strong><br>Estamos ubicados en el corazón de la ciudad, fácil acceso y estacionamiento disponible.<br><br>🚗 ¡Ven a visitarnos y conoce nuestras instalaciones!';
}

function getAboutResponse() {
    return '🏪 <strong>DISTRICARNES Hermanos Navarro</strong><br><br>Con más de <strong>28 años de tradición</strong>, somos una empresa familiar dedicada a ofrecer las mejores carnes premium. Hemos atendido a más de <strong>8,500 familias</strong> con productos 100% frescos.<br><br>🏆 <strong>Nuestros valores:</strong><br>• Calidad garantizada<br>• Frescura diaria<br>• Servicio personalizado<br>• Tradición familiar';
}

function getContactResponse() {
    return '📞 <strong>Contáctanos:</strong><br>• Teléfono: [Número de teléfono]<br>• WhatsApp: [Número de WhatsApp]<br>• Email: info@districarnes.com<br><br>🏪 <strong>Visítanos:</strong><br>• Dirección: [Dirección completa]<br>• Horarios: Lun-Sáb 7AM-7PM, Dom 8AM-2PM<br><br>¡Estamos aquí para atenderte!';
}

function getQualityResponse() {
    return '⭐ <strong>Nuestra garantía de calidad:</strong><br>• 100% carnes frescas diariamente<br>• Productos premium seleccionados<br>• Cadena de frío garantizada<br>• Más de 28 años de experiencia<br>• Certificaciones de calidad<br><br>🥩 ¡La frescura que tu familia merece!';
}

function getCookingTipsResponse(message) {
    if (message.includes('res') || message.includes('filete')) {
        return '👨‍🍳 <strong>Consejos para carne de res:</strong><br>• Saca la carne del refrigerador 30 min antes<br>• Sazona con sal y pimienta<br>• Sella a fuego alto por ambos lados<br>• Cocina al término deseado<br>• Deja reposar 5 minutos antes de servir<br><br>¡El secreto está en no sobrecocinar!';
    } else if (message.includes('cerdo')) {
        return '🐷 <strong>Consejos para carne de cerdo:</strong><br>• Cocina completamente (75°C interno)<br>• Marina previamente para más sabor<br>• Cocina a fuego medio-bajo<br>• Usa termómetro para verificar cocción<br>• Deja reposar antes de cortar<br><br>¡Perfecta para asados familiares!';
    } else {
        return '🍳 <strong>Consejos generales de cocción:</strong><br>• Usa las temperaturas adecuadas<br>• No voltees la carne constantemente<br>• Deja reposar después de cocinar<br>• Sazona al gusto<br>• Acompaña con vegetales frescos<br><br>¿Necesitas consejos para algún corte específico?';
    }
}

function getAvailabilityResponse() {
    return '✅ <strong>Disponibilidad actual:</strong><br>• Productos frescos diariamente<br>• Stock renovado cada mañana<br>• Reservas disponibles por teléfono<br>• Productos de temporada según disponibilidad<br><br>📞 ¡Llámanos para confirmar disponibilidad de productos específicos!';
}

function getDefaultResponse() {
    const responses = [
        '🤔 No estoy seguro de entender tu pregunta. Puedo ayudarte con:<br>• Productos cárnicos<br>• Tipos de cortes<br>• Precios y ofertas<br>• Horarios y ubicación<br>• Información sobre nosotros<br>• Contacto',
        '❓ ¿Podrías ser más específico? Estoy aquí para ayudarte con:<br>• Carnes y productos<br>• Consejos de cocina<br>• Horarios de atención<br>• Información de contacto<br>• Preguntas sobre calidad',
        '💭 No entendí completamente. ¿Te interesa saber sobre:<br>• Nuestros productos frescos<br>• Cortes especiales<br>• Horarios de la carnicería<br>• Cómo contactarnos<br>• Nuestra historia familiar'
    ];
    return responses[Math.floor(Math.random() * responses.length)];
}

// ===== MANEJO DE ACCIONES RÁPIDAS =====
function handleQuickAction(action) {
    let actionText = '';
    switch(action) {
        case 'productos':
            actionText = 'Ver productos cárnicos';
            fetchAndShowProducts('');
            return;
        case 'cortes':
            actionText = 'Tipos de cortes';
            fetchAndShowCategories();
            return;
        case 'precios':
        case 'ofertas':
            actionText = 'Precios y ofertas';
            fetchAndShowOffers();
            return;
        case 'horarios':
            actionText = 'Horarios y ubicación';
            // Mostrar información local rápida
            try{
                const chatBox = document.getElementById('chatBox');
                chatBox.innerHTML += `<div class="message bot-message">${getScheduleLocationResponse()}</div>`;
                chatBox.scrollTop = chatBox.scrollHeight;
            }catch(_){}
            break;
        case 'contacto':
            actionText = 'Contactar';
            try{
                const chatBox = document.getElementById('chatBox');
                chatBox.innerHTML += `<div class="message bot-message">${getContactResponse()}</div>`;
                chatBox.scrollTop = chatBox.scrollHeight;
            }catch(_){}
            break;
        case 'sobre':
            try{
                const chatBox = document.getElementById('chatBox');
                chatBox.innerHTML += `<div class="message bot-message">${getAboutResponse()}</div>`;
                chatBox.scrollTop = chatBox.scrollHeight;
            }catch(_){}
            return;
        default:
            actionText = action;
    }
    sendToAI(actionText);
}

function getBase(){
    return '';
}

function chatEndpoint(){
    const base = getBase();
    return base + '/backend/api/chat.php';
}

function productsEndpoint(params){
    const base = getBase();
    const qs = params ? ('?' + new URLSearchParams(params).toString()) : '';
    return base + '/backend/php/catalog/get_products.php' + qs;
}

function categoriesEndpoint(params){
    const base = getBase();
    const qs = params ? ('?' + new URLSearchParams(params).toString()) : '';
    return base + '/backend/php/catalog/get_categories.php' + qs;
}

function offersEndpoint(params){
    const base = getBase();
    const query = new URLSearchParams(Object.assign({ only_active: '1' }, (params||{}))).toString();
    return base + '/backend/php/catalog/get_offers.php?' + query;
}

function parseMenuActionLabel(txt){
    const t = (txt||'').toLowerCase();
    if (t.includes('producto')) return 'productos';
    if (t.includes('horario') || t.includes('ubicación')) return 'horarios';
    if (t.includes('contact')) return 'contacto';
    if (t.includes('corte')) return 'cortes';
    if (t.includes('precio') || t.includes('oferta')) return 'precios';
    if (t.includes('sobre')) return 'sobre';
    return t.trim();
}

function formatCOP(v){
    try { return new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(Number(v)||0); }
    catch(_) { return '$ ' + String(Math.round(Number(v)||0)).replace(/\B(?=(\d{3})+(?!\d))/g,'.'); }
}

async function fetchAndShowProducts(q){
    const chatBox = document.getElementById('chatBox');
    showTypingIndicator();
    try{
        const url = productsEndpoint(q ? { q } : undefined);
        const res = await fetch(url);
        const data = await res.json();
        const list = (data && data.products) ? data.products : [];
        const items = list.slice(0, 6).map(function(p){
            const id = (p.id_producto ?? p.id ?? p.producto_id ?? '');
            const name = (p.nombre ?? p.name ?? 'Producto');
            const price = (p.precio_venta ?? p.price ?? p.precio ?? p.precio_base ?? 0);
            const img = (p.imagen ?? p.image ?? '');
            let href = 'productos.php';
            if (id) { href = 'detalle_producto.php?id=' + encodeURIComponent(id); }
            return { id, name, price, img, href };
        });
        if (!items.length){
            chatBox.innerHTML += `<div class="message bot-message">No encontré productos para mostrar ahora mismo.</div>`;
        } else {
            let html = '<div class="message bot-message"><div style="display:flex;flex-direction:column;gap:10px;max-width:360px">';
            items.forEach(function(it){
                const img = it.img ? `<img src="${it.img}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:6px;border:1px solid #333;margin-right:10px">` : '';
                html += `<div style="display:flex;align-items:center;background:#151515;border:1px solid #2a2a2a;border-radius:10px;padding:8px">
                    ${img}
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:600;color:#eee;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${it.name}</div>
                        <div style="color:#ccc;margin-top:4px">${formatCOP(it.price)}</div>
                    </div>
                    <a href="${it.href}" style="margin-left:10px;background:#ff0000;color:#fff;text-decoration:none;padding:6px 10px;border-radius:8px;white-space:nowrap">Ver</a>
                </div>`;
            });
            html += '</div></div>';
            chatBox.innerHTML += html;
        }
    }catch(e){
        chatBox.innerHTML += `<div class="message bot-message">No pude cargar productos ahora mismo.</div>`;
    }finally{
        hideTypingIndicator();
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

async function fetchAndShowCategories(){
    const chatBox = document.getElementById('chatBox');
    showTypingIndicator();
    try{
        const res = await fetch(categoriesEndpoint());
        const data = await res.json();
        const cats = (data && data.categories) ? data.categories : (data && data.items) ? data.items : [];
        const items = cats.slice(0, 8).map(function(c){
            const id = (c.id ?? c.id_categoria ?? '');
            const name = (c.name ?? '').toString();
            const display = (c.display ?? c.nombre ?? name).toString();
            const count = c.product_count ?? c.count ?? null;
            let href = 'productos.php?categoria=' + encodeURIComponent(name);
            if (id) href += '&categoria_id=' + encodeURIComponent(id);
            return { id, name, display, count, href };
        });
        if (!items.length){
            chatBox.innerHTML += `<div class="message bot-message">No encontré categorías/cortes en la base de datos.</div>`;
        } else {
            let html = '<div class="message bot-message"><div style="display:flex;flex-direction:column;gap:8px;max-width:360px">';
            items.forEach(function(it){
                const cnt = (it.count !== null && it.count !== undefined) ? ` <span style="color:#aaa">(${it.count})</span>` : '';
                html += `<div style="display:flex;align-items:center;justify-content:space-between;background:#151515;border:1px solid #2a2a2a;border-radius:10px;padding:8px 10px">
                    <div style="color:#eee;font-weight:600">${it.display}${cnt}</div>
                    <a href="${it.href}" style="margin-left:10px;background:#ff0000;color:#fff;text-decoration:none;padding:6px 10px;border-radius:8px;white-space:nowrap">Ver</a>
                </div>`;
            });
            html += '</div></div>';
            chatBox.innerHTML += html;
        }
    }catch(e){
        chatBox.innerHTML += `<div class="message bot-message">No pude cargar las categorías ahora mismo.</div>`;
    }finally{
        hideTypingIndicator();
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

async function fetchAndShowOffers(){
    const chatBox = document.getElementById('chatBox');
    showTypingIndicator();
    try{
        const res = await fetch(offersEndpoint());
        if (!res.ok) throw new Error('http ' + res.status);
        const data = await res.json();
        const promos = (data && (data.promotions || data.offers || data.ofertas)) ? (data.promotions || data.offers || data.ofertas) : [];
        const items = promos.slice(0, 6).map(function(o){
            const title = o.title ?? o.nombre ?? 'Oferta';
            const desc = o.description ?? o.descripcion ?? '';
            const type = (o.type ?? o.tipo ?? '').toString();
            const val = o.discount_value ?? o.valor_descuento ?? '';
            const img = o.image ?? o.imagen ?? '';
            const href = 'promociones.php';
            return { title, desc, type, val, img, href };
        });
        if (items.length){
            let html = '<div class="message bot-message"><div style="display:flex;flex-direction:column;gap:10px;max-width:360px">';
            items.forEach(function(it){
                const img = it.img ? `<img src="${it.img}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:6px;border:1px solid #333;margin-right:10px">` : '';
                const typ = it.type ? `<span style="color:#bbb;font-size:12px;text-transform:uppercase">${it.type}</span>` : '';
                const vv = it.val !== '' ? ` - ${it.val}` : '';
                html += `<div style="display:flex;align-items:center;background:#151515;border:1px solid #2a2a2a;border-radius:10px;padding:8px">
                    ${img}
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:700;color:#eee;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${it.title}</div>
                        <div style="color:#ccc;margin-top:4px;font-size:12px">${typ}${vv}</div>
                        <div style="color:#aaa;margin-top:4px;font-size:12px;max-height:34px;overflow:hidden;text-overflow:ellipsis">${it.desc}</div>
                    </div>
                    <a href="${it.href}" style="margin-left:10px;background:#ff0000;color:#fff;text-decoration:none;padding:6px 10px;border-radius:8px;white-space:nowrap">Ver</a>
                </div>`;
            });
            html += '</div></div>';
            chatBox.innerHTML += html;
        } else {
            // Fallback: mostrar productos económicos desde base de datos
            await fetchAndShowCheapestProducts();
        }
    }catch(e){
        await fetchAndShowCheapestProducts();
    }finally{
        hideTypingIndicator();
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

async function fetchAndShowCheapestProducts(){
    const chatBox = document.getElementById('chatBox');
    try{
        const res = await fetch(productsEndpoint());
        const data = await res.json();
        const list = (data && data.products) ? data.products : [];
        if (!list.length){
            chatBox.innerHTML += `<div class="message bot-message">Por ahora no hay ofertas activas. Vuelve más tarde.</div>`;
            return;
        }
        const items = list
            .map(function(p){
                const id = (p.id_producto ?? p.id ?? p.producto_id ?? '');
                const name = (p.nombre ?? p.name ?? 'Producto');
                const price = (p.precio_venta ?? p.price ?? p.precio ?? p.precio_base ?? 0);
                const img = (p.imagen ?? p.image ?? '');
                let href = 'productos.php';
                if (id) { href = 'detalle_producto.php?id=' + encodeURIComponent(id); }
                return { id, name, price: Number(price)||0, img, href };
            })
            .sort(function(a,b){ return a.price - b.price; })
            .slice(0,6);
        let html = '<div class="message bot-message"><div style="font-weight:700;margin-bottom:6px">Productos con mejor precio</div><div style="display:flex;flex-direction:column;gap:10px;max-width:360px">';
        items.forEach(function(it){
            const img = it.img ? `<img src="${it.img}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:6px;border:1px solid #333;margin-right:10px">` : '';
            html += `<div style="display:flex;align-items:center;background:#151515;border:1px solid #2a2a2a;border-radius:10px;padding:8px">
                ${img}
                <div style="flex:1;min-width:0">
                    <div style="font-weight:600;color:#eee;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${it.name}</div>
                    <div style="color:#ccc;margin-top:4px">${formatCOP(it.price)}</div>
                </div>
                <a href="${it.href}" style="margin-left:10px;background:#ff0000;color:#fff;text-decoration:none;padding:6px 10px;border-radius:8px;white-space:nowrap">Ver</a>
            </div>`;
        });
        html += '</div></div>';
        chatBox.innerHTML += html;
    }catch(_){
        chatBox.innerHTML += `<div class="message bot-message">No pude cargar las ofertas ahora mismo.</div>`;
    }
}
// ===== INICIALIZACIÓN DEL CHATBOT =====
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.menu-option').forEach(option => {
        option.addEventListener('click', () => {
            const act = option.dataset && option.dataset.action ? option.dataset.action : parseMenuActionLabel(option.textContent.trim());
            handleQuickAction(act);
        });
    });
    
    const sendBtn = document.querySelector('.send-btn');
    if (sendBtn) {
        sendBtn.addEventListener('click', function (e) {
            e.preventDefault();
            sendMessage();
        });
    }
    const inputEl = document.getElementById('userInput') || document.querySelector('.chat-input');
    if (inputEl) {
        inputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });
    }
    
    const chatbotToggle = document.querySelector('.chatbot-toggle');
    if (chatbotToggle) {
        chatbotToggle.addEventListener('click', (e) => {
            e.preventDefault();
            e.preventDefault();
            e.stopPropagation();
            try { toggleChatbot(); } catch (_) {}
            setTimeout(() => {
                const messages = document.querySelector('.chatbot-messages');
                if (messages) {
                    messages.scrollTop = messages.scrollHeight;
                }
            }, 300);
        });
    }
    setupVoiceInput();
});

let __voiceRecog = null;
let __voiceActive = false;
function setupVoiceInput(){
    const btn = document.querySelector('.voice-btn');
    if (!btn) return;
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) {
        btn.addEventListener('click', function(){
            const chatBox = document.getElementById('chatBox');
            if (chatBox) {
                chatBox.innerHTML += '<div class="message bot-message">Tu navegador no soporta entrada por voz.</div>';
                const messages = document.querySelector('.chatbot-messages');
                if (messages) messages.scrollTop = messages.scrollHeight;
            }
        });
        return;
    }
    const rec = new SR();
    rec.lang = 'es-CO';
    rec.interimResults = true;
    rec.maxAlternatives = 1;
    rec.onresult = function(e){
        let txt = '';
        for (let i = e.resultIndex; i < e.results.length; i++) {
            txt += e.results[i][0].transcript;
        }
        const input = document.getElementById('userInput') || document.querySelector('.chat-input');
        if (input) {
            input.value = txt.trim();
            input.focus();
            try { input.setSelectionRange(input.value.length, input.value.length); } catch(_) {}
        }
    };
    rec.onend = function(){
        __voiceActive = false;
        btn.classList.remove('listening');
    };
    btn.addEventListener('click', function(){
        if (__voiceActive) {
            try { rec.stop(); } catch(_) {}
            return;
        }
        __voiceActive = true;
        btn.classList.add('listening');
        try { rec.start(); } catch(_) {
            __voiceActive = false;
            btn.classList.remove('listening');
        }
    });
    __voiceRecog = rec;
}

// ===== FUNCIONES ADICIONALES =====
function clearChat() {
    const messages = document.querySelector('.chatbot-messages');
    messages.innerHTML = `
        <div class="message bot-message"> 
            ¡Hola! 🥩 Soy tu asistente de DISTRICARNES. ¿En qué puedo ayudarte hoy? 
            <div class="menu-options"> 
                <div class="menu-option" data-action="productos"> 
                    <i class="fas fa-drumstick-bite"></i> Ver productos cárnicos 
                </div> 
                <div class="menu-option" data-action="cortes"> 
                    <i class="fas fa-cut"></i> Tipos de cortes 
                </div> 
                <div class="menu-option" data-action="horarios"> 
                    <i class="fas fa-clock"></i> Horarios y ubicación 
                </div> 
                <div class="menu-option" data-action="precios"> 
                    <i class="fas fa-tags"></i> Precios y ofertas 
                </div> 
                <div class="menu-option" data-action="sobre"> 
                    <i class="fas fa-info-circle"></i> Sobre nosotros 
                </div> 
                <div class="menu-option" data-action="contacto"> 
                    <i class="fas fa-phone"></i> Contactar 
                </div> 
            </div> 
            <div class="message-timestamp">${getCurrentTime()}</div> 
        </div>
    `;
    document.querySelectorAll('.menu-option').forEach(option => {
        option.addEventListener('click', () => {
            const act = option.dataset && option.dataset.action ? option.dataset.action : parseMenuActionLabel(option.textContent.trim());
            handleQuickAction(act);
        });
    });
}

async function sendToAI(message) {
    if (!message) return;

    const chatBox = document.getElementById('chatBox');
    chatBox.innerHTML += `<div class="message user-message">${message}</div>`;
    chatBox.scrollTop = chatBox.scrollHeight;

    showTypingIndicator();
    try {
        if (window.__chatRemoteFail) {
            throw new Error('offline-fallback');
        }
        const endpoint = chatEndpoint();
        const res = await fetch(endpoint, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ message })
        });
        const resClone = res.clone();
        let data;
        try {
            data = await res.json();
        } catch (parseErr) {
            const txt = await resClone.text();
            throw new Error(txt && txt.length ? txt : 'Respuesta no válida del servidor');
        }
        if (!res.ok) {
            const msg = data && (data.error || data.message) ? (data.error || data.message) : `HTTP ${res.status}`;
            throw new Error(msg);
        }
        const botReply = (data && data.error)
            ? `Error: ${data.error}`
            : (data && data.reply)
              ? data.reply
              : (data && data.choices && data.choices[0] && data.choices[0].message && data.choices[0].message.content)
                ? data.choices[0].message.content
                : "Lo siento, no pude procesar tu mensaje en este momento.";
        chatBox.innerHTML += `<div class="message bot-message">${botReply}</div>`;
    } catch (e) {
        console.error('Chat error:', e);
        window.__chatRemoteFail = true;
        const fallback = getBotResponse(message);
        chatBox.innerHTML += `<div class="message bot-message">${fallback}</div>`;
    } finally {
        hideTypingIndicator();
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

async function sendMessage() {
    const input = document.getElementById("userInput") || document.querySelector(".chat-input");
    const message = input ? input.value.trim() : "";
    if (!message) return;

    const chatBox = document.getElementById("chatBox");
    chatBox.innerHTML += `<div class="message user-message">${message}</div>`;
    if (input) input.value = "";
    chatBox.scrollTop = chatBox.scrollHeight;

    showTypingIndicator();
    try {
        if (window.__chatRemoteFail) {
            throw new Error('offline-fallback');
        }
        const endpoint = chatEndpoint();
        const response = await fetch(endpoint, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ message })
        });
        const respClone = response.clone();
        let data;
        try {
            data = await response.json();
        } catch (parseErr) {
            const txt = await respClone.text();
            throw new Error(txt && txt.length ? txt : 'Respuesta no válida del servidor');
        }
        if (!response.ok) {
            const msg = data && (data.error || data.message) ? (data.error || data.message) : `HTTP ${response.status}`;
            throw new Error(msg);
        }
        const botReply = (data && data.error)
            ? `Error: ${data.error}`
            : (data && data.reply)
              ? data.reply
              : (data && data.choices && data.choices[0] && data.choices[0].message && data.choices[0].message.content)
                ? data.choices[0].message.content
                : "Lo siento, no pude procesar tu mensaje en este momento.";
        chatBox.innerHTML += `<div class="message bot-message">${botReply}</div>`;
    } catch (e) {
        console.error('Chat error:', e);
        window.__chatRemoteFail = true;
        const fallback = getBotResponse(message);
        chatBox.innerHTML += `<div class="message bot-message">${fallback}</div>`;
    } finally {
        hideTypingIndicator();
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

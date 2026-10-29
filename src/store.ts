import fs from 'fs';
import path from 'path';

export interface Product {
  id_producto: number;
  id: number;
  codigo?: string;
  nombre: string;
  name: string;
  precio_venta: number;
  precio: number;
  precio_base?: number;
  precio_abastecimiento?: number;
  descripcion: string;
  stock: number;
  id_categoria: number;
  categoria_id: number;
  categoria: string;
  subcategoria: string;
  imagen: string;
  image: string;
  estado: boolean;
  tipo_unidad: string;
  vencido?: boolean;
  lotes?: any[];
}

export interface Category {
  id: string | number;
  name: string;
  display: string;
  product_count: number;
}

export interface Offer {
  id: number;
  title: string;
  nombre: string;
  description: string;
  descripcion: string;
  type: string;
  tipo: string;
  discount_value: number;
  valor_descuento: number;
  start_date?: string;
  end_date?: string;
  status: string;
  estado: string;
  image: string;
  imagen: string;
  products?: string[];
}

export interface OrderItem {
  id: number;
  order_id: number;
  title: string;
  price: number;
  qty: number;
  image: string;
}

export interface Order {
  id: number;
  paypal_id?: string;
  user_email: string;
  user_name: string;
  status: string;
  total: number;
  delivery_method: string;
  address_json?: string;
  schedule_json?: string;
  created_at: string;
  items: OrderItem[];
  pay_method?: string;
}

export interface User {
  id_usuario: number;
  id: number;
  nombres_completos: string;
  nombre: string;
  correo_electronico: string;
  email: string;
  rol: string;
  celular?: string;
  direccion?: string;
  cedula?: string;
  usuario_foto?: string;
  password?: string;
}

const CATEGORY_NAMES: Record<number, string> = {
  1: 'Cortes Premium Res',
  2: 'Cortes Corrientes Res',
  3: 'Costillas Res',
  4: 'Carne Molida Res',
  5: 'Vísceras Res',
  6: 'Huesos Res',
  7: 'Cortes Premium Cerdo',
  8: 'Tocinos Cerdo',
  9: 'Costillas Cerdo',
  10: 'Pollo Entero',
  11: 'Partes de Pollo',
  12: 'Otros'
};

function deriveCategoryName(name: string, catId: number): string {
  if (CATEGORY_NAMES[catId]) return CATEGORY_NAMES[catId];
  const n = (name || '').toLowerCase();
  if (/(res|vaca|ternera|lomo|barcino|posta|bistec)/i.test(n)) return 'res';
  if (/(cerdo|puerco|chuleta|tocino|papada|codillo)/i.test(n)) return 'cerdo';
  if (/(pollo|gallina|alas|muslo|pechuga)/i.test(n)) return 'pollo';
  if (/(pescado|robalo|mojarra)/i.test(n)) return 'pescado';
  return 'otros';
}

function deriveAnimalType(name: string, catId: number): string {
  if (catId >= 1 && catId <= 6) return 'res';
  if (catId >= 7 && catId <= 9) return 'cerdo';
  if (catId >= 10 && catId <= 11) return 'pollo';
  const n = (name || '').toLowerCase();
  if (/(res|vaca|lomo|posta|bistec|cohete|morro)/i.test(n)) return 'res';
  if (/(cerdo|chuleta|tocino|papada)/i.test(n)) return 'cerdo';
  if (/(pollo|alas|muslo)/i.test(n)) return 'pollo';
  return 'otros';
}

// Load parsed products
let rawParsed: any[] = [];
try {
  const parsedPath = path.resolve(process.cwd(), 'src/products_parsed.json');
  if (fs.existsSync(parsedPath)) {
    rawParsed = JSON.parse(fs.readFileSync(parsedPath, 'utf8'));
  }
} catch (e) {
  console.error('Error loading products_parsed.json', e);
}

export const productsStore: Product[] = rawParsed.map((p: any) => {
  const catName = deriveCategoryName(p.nombre, p.id_categoria);
  const animalType = deriveAnimalType(p.nombre, p.id_categoria);
  const fallbackImg = animalType === 'cerdo' ? '/static/images/lomo_de_cerdo.jpeg'
    : animalType === 'res' ? '/static/images/lomo fresco.jpg'
    : animalType === 'pollo' ? '/static/images/imagenhero1.jpeg'
    : '/static/images/image.png';

  const img = (p.imagen && p.imagen.trim()) ? p.imagen : fallbackImg;

  return {
    id_producto: p.id_producto,
    id: p.id_producto,
    codigo: 'PROD-' + String(p.id_producto).padStart(4, '0'),
    nombre: p.nombre,
    name: p.nombre,
    precio_venta: p.precio_venta,
    precio: p.precio_venta,
    precio_base: p.precio_venta,
    precio_abastecimiento: p.precio_abastecimiento || p.precio_venta * 0.7,
    descripcion: p.descripcion || `Corte selecto de ${p.nombre}, seleccionado bajo los más estrictos estándares de frescura y calidad por DistriCarnes Hermanos Navarro.`,
    stock: p.stock || 50,
    id_categoria: p.id_categoria || 1,
    categoria_id: p.id_categoria || 1,
    categoria: animalType,
    subcategoria: catName.toLowerCase(),
    imagen: img,
    image: img,
    estado: true,
    tipo_unidad: 'Unidad',
    vencido: false,
    lotes: []
  };
});

export const categoriesStore: Category[] = Object.keys(CATEGORY_NAMES).map(k => {
  const catId = Number(k);
  const name = CATEGORY_NAMES[catId];
  const count = productsStore.filter(p => p.id_categoria === catId).length;
  return {
    id: catId,
    name: name.toLowerCase(),
    display: name.toUpperCase(),
    product_count: count
  };
});

export const offersStore: Offer[] = [
  {
    id: 5,
    title: 'Lomo fino',
    nombre: 'Lomo fino',
    description: 'Corte premium tierno, jugoso y de textura inigualable. Ideal para asados o medallones a la plancha.',
    descripcion: 'Corte premium tierno, jugoso y de textura inigualable. Ideal para asados o medallones a la plancha.',
    type: 'fixed',
    tipo: 'fixed',
    discount_value: 23000,
    valor_descuento: 23000,
    status: 'active',
    estado: 'active',
    image: '/static/images/products/prod_68ebea69caa298.78139733.jpeg',
    imagen: '/static/images/products/prod_68ebea69caa298.78139733.jpeg'
  },
  {
    id: 6,
    title: 'Lomo ancho 2x1',
    nombre: 'Lomo ancho 2x1',
    description: 'Excelente marmoleo y sabor tradicional. Llévate dos cortes por precio especial.',
    descripcion: 'Excelente marmoleo y sabor tradicional. Llévate dos cortes por precio especial.',
    type: 'bogo',
    tipo: 'bogo',
    discount_value: 24000,
    valor_descuento: 24000,
    status: 'active',
    estado: 'active',
    image: '/static/images/products/prod_68ebea8dc8ae31.97147879.jpeg',
    imagen: '/static/images/products/prod_68ebea8dc8ae31.97147879.jpeg'
  },
  {
    id: 7,
    title: 'Punta gorda - 15% OFF',
    nombre: 'Punta gorda - 15% OFF',
    description: 'Capa de grasa justa para darle el sabor inconfundible a la parrilla.',
    descripcion: 'Capa de grasa justa para darle el sabor inconfundible a la parrilla.',
    type: 'percentage',
    tipo: 'percentage',
    discount_value: 15,
    valor_descuento: 15,
    status: 'active',
    estado: 'active',
    image: '/static/images/products/prod_68ebeaf6726954.44629935.jpeg',
    imagen: '/static/images/products/prod_68ebeaf6726954.44629935.jpeg'
  },
  {
    id: 8,
    title: 'Costilla especial de cerdo',
    nombre: 'Costilla especial de cerdo',
    description: 'Carnuda y suave, lista para marinar y dorar a fuego lento.',
    descripcion: 'Carnuda y suave, lista para marinar y dorar a fuego lento.',
    type: 'fixed',
    tipo: 'fixed',
    discount_value: 18000,
    valor_descuento: 18000,
    status: 'active',
    estado: 'active',
    image: '/static/images/products/prod_costilla-de-cerdo_68ebe9c0ebcd8.jpeg',
    imagen: '/static/images/products/prod_costilla-de-cerdo_68ebe9c0ebcd8.jpeg'
  }
];

export const usersStore: User[] = [
  {
    id_usuario: 8,
    id: 8,
    nombres_completos: 'JUAN HUMBERTO VEGA SANCHEZ',
    nombre: 'JUAN HUMBERTO VEGA SANCHEZ',
    correo_electronico: 'juanhumbertovega600@gmail.com',
    email: 'juanhumbertovega600@gmail.com',
    rol: 'admin',
    celular: '3108392866',
    direccion: 'CR 11B 17-95 VILLANUEVA BOLIVAR',
    cedula: '1052732445'
  },
  {
    id_usuario: 7,
    id: 7,
    nombres_completos: 'Comprador DistriCarnes',
    nombre: 'Comprador DistriCarnes',
    correo_electronico: 'comprador@gmail.com',
    email: 'comprador@gmail.com',
    rol: 'trabajo',
    celular: '3015210177',
    direccion: 'Avenida Pedro de Heredia, Cartagena',
    cedula: '12234'
  }
];

export const ordersStore: Order[] = [
  {
    id: 1,
    paypal_id: '92T721159F630974S',
    user_email: 'comprador@gmail.com',
    user_name: 'Comprador DistriCarnes',
    status: 'COMPLETED',
    total: 35000,
    delivery_method: 'domicilio',
    address_json: JSON.stringify({ street: 'Avenida Pedro de Heredia #32-15', city: 'Cartagena', dept: 'Bolívar' }),
    created_at: new Date(Date.now() - 86400000 * 2).toISOString(),
    pay_method: 'PayPal',
    items: [
      { id: 1, order_id: 1, title: 'Lomo fino', price: 25000, qty: 1, image: '/static/images/products/prod_68ebea69caa298.78139733.jpeg' },
      { id: 2, order_id: 1, title: 'Masa de cerdo', price: 10000, qty: 1, image: '/static/images/products/prod_68ebf7241b6910.98394274.jpeg' }
    ]
  },
  {
    id: 2,
    paypal_id: '6DS76703S2114415R',
    user_email: 'comprador@gmail.com',
    user_name: 'Comprador DistriCarnes',
    status: 'PROCESSING',
    total: 45000,
    delivery_method: 'domicilio',
    address_json: JSON.stringify({ street: 'Calle 10 #14-20, Bocagrande', city: 'Cartagena', dept: 'Bolívar' }),
    created_at: new Date(Date.now() - 3600000 * 4).toISOString(),
    pay_method: 'Efectivo',
    items: [
      { id: 3, order_id: 2, title: 'Costilla especial', price: 20000, qty: 1, image: '/static/images/products/prod_68ebf5e4dafd05.90954993.jpeg' },
      { id: 4, order_id: 2, title: 'Lomo fino', price: 25000, qty: 1, image: '/static/images/products/prod_68ebea69caa298.78139733.jpeg' }
    ]
  }
];

export const notificationsStore: any[] = [
  { id: 1, type: 'order', title: 'Nuevo pedido #2', message: 'comprador@gmail.com realizó un pedido en preparación', created_at: new Date().toISOString(), read: false }
];

export function getFilteredProducts(options: {
  q?: string;
  categoria?: string;
  subcategoria?: string;
  categoria_id?: string | number;
  ids?: string;
  incluir_vencidos?: boolean;
}): Product[] {
  let list = [...productsStore];

  if (options.ids) {
    const idSet = new Set(options.ids.split(',').map(s => s.trim()));
    list = list.filter(p => idSet.has(String(p.id_producto)) || idSet.has(String(p.id)));
  }

  if (options.categoria_id) {
    list = list.filter(p => String(p.id_categoria) === String(options.categoria_id));
  } else if (options.categoria) {
    const catLow = options.categoria.toLowerCase();
    list = list.filter(p => p.categoria.toLowerCase().includes(catLow) || p.subcategoria.toLowerCase().includes(catLow));
  }

  if (options.subcategoria) {
    const subLow = options.subcategoria.toLowerCase();
    list = list.filter(p => p.subcategoria.toLowerCase().includes(subLow));
  }

  if (options.q) {
    const term = options.q.toLowerCase().trim();
    list = list.filter(p =>
      p.nombre.toLowerCase().includes(term) ||
      p.descripcion.toLowerCase().includes(term) ||
      p.categoria.toLowerCase().includes(term) ||
      p.subcategoria.toLowerCase().includes(term)
    );
  }

  return list;
}

export function saveNewOrder(orderData: any): Order {
  const newId = ordersStore.length > 0 ? Math.max(...ordersStore.map(o => o.id)) + 1 : 1;
  const items: OrderItem[] = (orderData.items || []).map((it: any, idx: number) => ({
    id: idx + 1,
    order_id: newId,
    title: it.title || it.name || 'Producto',
    price: Number(it.price || 0),
    qty: Number(it.qty || it.quantity || 1),
    image: it.image || it.imagen || '/static/images/image.png'
  }));

  const order: Order = {
    id: newId,
    paypal_id: orderData.paypal_id || (orderData.pay === 'paypal' ? 'PAYPAL-' + Date.now() : undefined),
    user_email: orderData.user?.email || orderData.user_email || 'cliente@districarnes.com',
    user_name: orderData.user?.name || orderData.user?.nombres_completos || orderData.user_name || 'Consumidor Final',
    status: orderData.status || (orderData.paypal_id ? 'COMPLETED' : 'PENDING'),
    total: Number(orderData.total || 0),
    delivery_method: orderData.delivery || 'domicilio',
    address_json: typeof orderData.address === 'object' ? JSON.stringify(orderData.address) : (orderData.address_json || '{}'),
    schedule_json: typeof orderData.schedule === 'object' ? JSON.stringify(orderData.schedule) : (orderData.schedule_json || '{}'),
    created_at: new Date().toISOString(),
    pay_method: orderData.pay || 'Efectivo',
    items: items
  };

  ordersStore.unshift(order);

  // Decrement stock
  for (const it of items) {
    const prod = productsStore.find(p => p.nombre.toLowerCase() === it.title.toLowerCase() || p.id === (it as any).id);
    if (prod && prod.stock >= it.qty) {
      prod.stock -= it.qty;
    }
  }

  // Add notification
  notificationsStore.unshift({
    id: notificationsStore.length + 1,
    type: 'order',
    title: `Nuevo pedido #${newId}`,
    message: `${order.user_email} realizó un pedido por $${Number(order.total).toLocaleString('es-CO')}`,
    created_at: new Date().toISOString(),
    read: false
  });

  return order;
}

export function getStats() {
  const salesToday = ordersStore
    .filter(o => o.status === 'COMPLETED' || o.status === 'PROCESSING')
    .reduce((sum, o) => sum + (o.total || 0), 0);

  const ordersInRoute = ordersStore.filter(o => o.status === 'PROCESSING' || o.status === 'PENDING').length;
  const lowStockItems = productsStore.filter(p => p.stock < 10).length;
  const activeCustomers = usersStore.length;

  return {
    ok: true,
    salesToday: salesToday || 85000,
    ordersInRoute: ordersInRoute || 1,
    lowStockItems: lowStockItems || 3,
    activeCustomers: activeCustomers || 8
  };
}

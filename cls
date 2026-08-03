* [33m63ff93d[m[33m ([m[1;36mHEAD[m[33m -> [m[1;32mmain[m[33m, [m[1;31morigin/main[m[33m, [m[1;31morigin/HEAD[m[33m)[m fix(inicio): esfera 3d mas grande cubriendo toda la seccion hero y mas puntos (400/540/700)
* [33mef098ae[m feat(inicio): esfera 3d de puntos giratoria con conexiones tipo telarana, brillo segun profundidad, pulso de energia al hacer clic y anillos expansivos
* [33m8709e12[m feat(inicio): animacion hero espectacular con animejs (titulo letra por letra, red neuronal con pulsos de energia, vortice al cursor, ondas de choque al hacer clic, supernova y estrellas fugaces)
* [33m1d49783[m fix(seguridad): redirigir al inicio si se accede al panel admin sin sesion valida
* [33me1fb7fa[m fix(tema): cargar la tienda en modo oscuro por defecto
* [33m9688cf2[m[33m ([m[1;32mmaster[m[33m)[m refactor(backend): organizar archivos PHP en subcarpetas (core, auth, catalog, orders, payments, sales, admin, user, tests) y actualizar todas las referencias, includes y .htaccess
* [33mc6dbbfc[m[33m ([m[1;31morigin/master[m[33m)[m fix(tema): corregir textos invisibles en modo claro (reseñas vacias, paginacion, breadcrumb, titulo perfil) y adaptar secciones oscuras de sobre nosotros + menu claro/oscuro en el toggle
* [33m138a476[m feat(carrito): carrito profesional con envio gratis, cupones, guardados y IVA; tema claro/oscuro con header negro en las 11 paginas
* [33me92f185[m fix(login): redirigir al panel admin segun rol, sin que postLoginRedirect de la tienda gane al rol admin
* [33mb2ad207[m Nav en pildoras (Inicio, Productos, Ofertas, Contacto, Quienes Somos) en las 11 paginas de la tienda
* [33m68a9f5e[m Proteccion de sesion para pagina de configuracion del panel admin
* [33meb9ff80[m Panel admin usa el mismo modal de login (unico login admin/usuario) y corrige redirecciones rotas
* [33m34dec44[m Modal de acceso en todos los puntos que requieren iniciar sesion (validacion completa)
* [33m1cfb94a[m Modal para crear nueva contrasena (cambiar_contrasena)
* [33m0f5a4a2[m Modal de recuperacion de contrasena y modales en todos los enlaces de acceso
* [33mbfed749[m Modal global de acceso/registro con boton INGRESAR en el header
* [33mda52840[m Rendimiento y feedback del panel admin: overlay 'Cargando informacion...' en todas las paginas, polling menos frecuente y solo con pestana visible, consultas information_schema con cache por request, limpieza de logs, indices en orders_pg/order_items_pg/facturas
* [33m72a7524[m Fix: redirigir login al nuevo dominio districarnes-83qm.onrender.com - todos los usuarios van al panel admin tras iniciar sesion; actualizar URLs de logout, reset de password, facturas y guard de sesion al nuevo dominio
* [33mdf05908[m Fix: grid de productos a pantalla completa - contenedor de 1200 a 1800px, auto-fit minmax(225px) para 5 tarjetas en pantallas anchas sin huecos en la ultima fila, 4 en laptop, 3/2/1 adaptativo
* [33m0c9b7da[m Fix: grid de ofertas responsivo y a pantalla completa - imagenes con aspecto 4:3 uniforme, 4 columnas en pantallas >=1600, 3 en >=1200, 2 en tablet/movil, 1 en movil pequeno; lineas decorativas y sombreado de imagen al pasar el cursor en productos y ofertas
* [33mbee9b5a[m Fix: tarjetas de producto responsivas sin cortar contenido - imagen sin zoom fijo (scale 1, hover 1.08), tipografia y padding adaptativos por breakpoint, botones apilados en tarjetas angostas
* [33m7e1df0d[m Fix: grid de productos responsivo - tarjetas por fila segun ancho de pantalla (4 en PC grande, 3 en PC, 2 en tablet/movil, 1 en movil pequeno). Se agrega dump completo de la base (districarnes_completo.sql)
* [33mf4ee564[m Fix: menu hamburguesa activo desde 992px en tablet/PC - los botones de acceso ya no se colocan debajo del buscador, breakpoints 768->992 en CSS y matchMedia en JS
* [33maa9b632[m Fix: menu movil hamburguesa - ocultar botones de acceso en movil con CSS !important, eliminar reglas del menu antiguo en responsive.css que causaban conflictos, y centralizar visibilidad por clase logged-in
* [33m5bfa87b[m Fix: CSS de Tailwind precompilado (estatico) en lugar del JIT del navegador que generaba CSS incompleto; resets universales en @layer base para que no anulen las utilidades
* [33m4e9a859[m Proyecto DistriCarnes Hermanos Navarro - e-commerce completo

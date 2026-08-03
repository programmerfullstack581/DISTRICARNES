<?php
header("Content-Type: application/xml; charset=utf-8");
$base = 'https://districarnes-83qm.onrender.com';
$pages = ['', 'productos.php', 'promociones.php', 'contacto.php', 'sobre_nosotros.php', 'perfil.php', 'favoritos.php', 'historial.php', 'detalle_producto.php', 'politica-de-privacidad.php', 'terminos-y-condiciones.php'];
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?= $base ?>/</loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>
<?php foreach ($pages as $p): ?>
<?php if ($p === '') continue; ?>
  <url>
    <loc><?= $base ?>/<?= $p ?></loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
<?php endforeach; ?>
</urlset>

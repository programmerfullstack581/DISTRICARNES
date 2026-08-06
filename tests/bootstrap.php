<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// composer puede no estar instalado en entornos locales; los tests de unidad
// no dependen de clases de terceros (solo de funciones core propias).
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

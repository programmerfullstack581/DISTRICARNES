<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
$basePath = str_replace('\\', '/', $basePath);
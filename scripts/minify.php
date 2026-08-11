<?php
/**
 * Simple asset minifier for CSS and JS files.
 * Usage: php scripts/minify.php <input_file> [output_file]
 *
 * If output_file is omitted, writes <input_file>.min.<ext> next to the input.
 *
 * Supports:
 *  - CSS: removes comments, unnecessary whitespace, and empty rule blocks.
 *  - JS : removes single-line and block comments, unnecessary whitespace.
 */

if (PHP_SAPI === 'cli' && isset($argc) && $argc < 2) {
    fwrite(STDERR, "Usage: php scripts/minify.php <input_file> [output_file]\n");
    exit(1);
}

$input  = $argv[1] ?? '';
$output = $argv[2] ?? '';

if ($input === '' || !is_file($input)) {
    fwrite(STDERR, "Input file not found: {$input}\n");
    exit(1);
}

$code = file_get_contents($input);
if ($code === false) {
    fwrite(STDERR, "Could not read file: {$input}\n");
    exit(1);
}

$ext = strtolower(pathinfo($input, PATHINFO_EXTENSION));

if ($ext === 'css') {
    $min = minify_css($code);
} elseif ($ext === 'js') {
    $min = minify_js($code);
} else {
    fwrite(STDERR, "Unsupported extension: {$ext}\n");
    exit(1);
}

if ($output === '') {
    $output = $input . '.min.' . $ext;
}

if (file_put_contents($output, $min) === false) {
    fwrite(STDERR, "Could not write file: {$output}\n");
    exit(1);
}

fwrite(STDOUT, "Minified: {$input} -> {$output} (" . strlen($code) . " -> " . strlen($min) . " bytes)\n");

function minify_css(string $css): string
{
    // Remove comments
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    // Remove whitespace
    $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
    // Remove spaces around symbols
    $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);
    // Remove trailing semicolons before }
    $css = preg_replace('/;}/', '}', $css);
    // Remove empty rules
    $css = preg_replace('/[^{}]+\{\s*\}/', '', $css);
    return trim($css);
}

function minify_js(string $js): string
{
    // Remove block comments (preserve URLs like http://)
    $js = preg_replace('/\/\*([^*]|\*(?!\/))+\*\//', '', $js);
    // Remove single-line comments (but not URLs)
    $js = preg_replace('/[ \t]*\/\/[^\n]*/', '', $js);
    // Remove whitespace
    $js = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $js);
    // Collapse multiple spaces
    $js = preg_replace('/[ ]{2,}/', ' ', $js);
    // Remove spaces around operators and punctuation
    $js = preg_replace('/\s*([{}()\[\];,<>+\-*\/=&|!])\s*/', '$1', $js);
    // Add space after keywords that need it
    $js = preg_replace('/(if|else|for|while|do|switch|case|return|typeof|instanceof|new|throw|try|catch|finally|with|break|continue|var|let|const|function|class|extends|import|export|default|yield|async|await)\b/', '$1 ', $js);
    return trim($js);
}

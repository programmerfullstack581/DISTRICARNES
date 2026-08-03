<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../core/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
  exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión.']);
  exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$userId = (int)$_SESSION['user_id'];

// Intentar usar columna JSONB en 'usuario' primero; si falla, caer a tabla auxiliar
function ensureUsuarioSettingsColumn(PDO $cnn) {
  try {
    $cnn->exec("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS user_settings JSONB NULL");
  } catch (Throwable $e) { /* ignorar */ }
}

function getUsuarioSettings(PDO $cnn, int $userId) {
  try {
    $stmt = $cnn->prepare("SELECT user_settings FROM usuario WHERE id_usuario = ? LIMIT 1");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && isset($row['user_settings']) && $row['user_settings'] !== null && $row['user_settings'] !== '') {
      $data = json_decode($row['user_settings'], true);
      if (is_array($data)) return $data;
    }
  } catch (Throwable $e) { /* ignorar */ }
  return null;
}

function saveUsuarioSettings(PDO $cnn, int $userId, array $settings) {
  try {
    $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
    $stmt = $cnn->prepare("UPDATE usuario SET user_settings = :js::jsonb WHERE id_usuario = :id");
    return $stmt->execute([':js' => $json, ':id' => $userId]);
  } catch (Throwable $e) {
    return false;
  }
}

// Fallback: tabla auxiliar user_settings
function ensureSettingsTable(PDO $cnn) {
  try {
    $ddl = "CREATE TABLE IF NOT EXISTS user_settings (
      id_usuario INTEGER PRIMARY KEY,
      email_notifs BOOLEAN NOT NULL DEFAULT FALSE,
      remember_favs BOOLEAN NOT NULL DEFAULT FALSE,
      show_iva BOOLEAN NOT NULL DEFAULT FALSE,
      updated_at TIMESTAMP NULL
    )";
    $cnn->exec($ddl);
  } catch (Throwable $e) { /* ignorar */ }
}

switch ($action) {
  case 'get_settings':
    getSettings($conexion, $userId);
    break;
  case 'save_settings':
    saveSettings($conexion, $userId);
    break;
  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    break;
}

function getSettings(PDO $conexion, int $userId) {
  // Intentar columna JSONB
  ensureUsuarioSettingsColumn($conexion);
  $js = getUsuarioSettings($conexion, $userId);
  if (is_array($js)) {
    echo json_encode([
      'success' => true,
      'settings' => [
        'emailNotifs' => (bool)($js['emailNotifs'] ?? false),
        'rememberFavs' => (bool)($js['rememberFavs'] ?? false),
        'showIVA' => (bool)($js['showIVA'] ?? false)
      ]
    ]);
    return;
  }
  // Fallback a tabla auxiliar
  ensureSettingsTable($conexion);
  try {
    $stmt = $conexion->prepare("SELECT email_notifs, remember_favs, show_iva FROM user_settings WHERE id_usuario = ? LIMIT 1");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      echo json_encode([
        'success' => true,
        'settings' => [
          'emailNotifs' => (bool)$row['email_notifs'],
          'rememberFavs' => (bool)$row['remember_favs'],
          'showIVA' => (bool)$row['show_iva']
        ]
      ]);
    } else {
      echo json_encode([
        'success' => true,
        'settings' => [
          'emailNotifs' => false,
          'rememberFavs' => false,
          'showIVA' => false
        ]
      ]);
    }
  } catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'No se pudieron obtener las preferencias.']);
  }
}

function toBool($v): bool {
  if (is_bool($v)) return $v;
  $s = strtolower(trim((string)$v));
  return in_array($s, ['1','true','on','yes','si','sí'], true);
}

function saveSettings(PDO $conexion, int $userId) {
  $emailNotifs = toBool($_POST['emailNotifs'] ?? 'false');
  $rememberFavs = toBool($_POST['rememberFavs'] ?? 'false');
  $showIVA = toBool($_POST['showIVA'] ?? 'false');
  $settingsArr = [
    'emailNotifs' => $emailNotifs,
    'rememberFavs' => $rememberFavs,
    'showIVA' => $showIVA
  ];
  // Intentar guardar en columna JSONB
  ensureUsuarioSettingsColumn($conexion);
  if (saveUsuarioSettings($conexion, $userId, $settingsArr)) {
    echo json_encode(['success' => true, 'message' => 'Preferencias guardadas.']);
    return;
  }
  // Fallback a tabla auxiliar
  ensureSettingsTable($conexion);
  try {
    $sql = "INSERT INTO user_settings (id_usuario, email_notifs, remember_favs, show_iva, updated_at)
            VALUES (?, ?, ?, ?, NOW())
            ON CONFLICT (id_usuario) DO UPDATE
            SET email_notifs = EXCLUDED.email_notifs,
                remember_favs = EXCLUDED.remember_favs,
                show_iva = EXCLUDED.show_iva,
                updated_at = NOW()";
    $stmt = $conexion->prepare($sql);
    $ok = $stmt->execute([$userId, $emailNotifs, $rememberFavs, $showIVA]);
    if ($ok) {
      echo json_encode(['success' => true, 'message' => 'Preferencias guardadas.']);
    } else {
      echo json_encode(['success' => false, 'message' => 'No se pudieron guardar las preferencias.']);
    }
  } catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar preferencias.']);
  }
}
?>

<?php
// =============================================
//  DIÁRIO DE MEMÓRIAS — Configuração
// =============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'diario');
define('DB_USER', 'root');        // trocar pelo seu usuário MySQL
define('DB_PASS', '');            // trocar pela sua senha MySQL
define('DB_PORT', '3306');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// URL pública da pasta uploads — detectada automaticamente
(function () {
    $proto  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '/diario/api/index.php';
    $base   = rtrim(dirname(dirname($script)), '/');
    define('UPLOAD_URL', $proto . '://' . $host . $base . '/uploads/');
})();

define('MAX_UPLOAD_MB', 50);

// Força limites de upload em tempo de execução
@ini_set('upload_max_filesize', '50M');
@ini_set('post_max_size',       '60M');

// CORS
define('ALLOWED_ORIGIN', '*');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT .
               ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function respond(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function respondError(string $msg, int $code = 400): void {
    respond(['ok' => false, 'error' => $msg], $code);
}

function body(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

function uuid(): string {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff));
}

<?php
require_once __DIR__ . '/config.php';

session_start();

$action = $_GET['action'] ?? '';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

switch ($action) {

    case 'login':
        $b    = json_decode(file_get_contents('php://input'), true) ?? [];
        $user = trim($b['username'] ?? '');
        $pass = $b['password'] ?? '';
        if (!$user || !$pass) { echo json_encode(['ok'=>false,'error'=>'Preencha todos os campos']); exit; }
        $stmt = db()->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$user]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($pass, $row['password'])) {
            http_response_code(401);
            echo json_encode(['ok'=>false,'error'=>'Usuário ou senha incorretos']);
            exit;
        }
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $user;
        echo json_encode(['ok'=>true]);
        exit;

    case 'logout':
        session_destroy();
        echo json_encode(['ok'=>true]);
        exit;

    case 'check':
        echo json_encode(['ok' => isset($_SESSION['user_id'])]);
        exit;

    default:
        http_response_code(404);
        echo json_encode(['ok'=>false,'error'=>'unknown action']);
}
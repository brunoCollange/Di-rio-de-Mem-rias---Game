<?php
// =============================================
//  DIÁRIO DE MEMÓRIAS — API Principal
// =============================================

require_once __DIR__ . '/config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    respond(['ok' => false, 'error' => 'não autenticado'], 401);
}

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {

        // ── DIAGNÓSTICO ──────────────────────────────────────
        case 'debug':
            respond([
                'ok'                  => true,
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size'       => ini_get('post_max_size'),
                'MAX_UPLOAD_MB'       => MAX_UPLOAD_MB,
                'UPLOAD_DIR'          => UPLOAD_DIR,
                'UPLOAD_URL'          => UPLOAD_URL,
                'upload_dir_exists'   => is_dir(UPLOAD_DIR),
                'upload_dir_writable' => is_dir(UPLOAD_DIR) && is_writable(UPLOAD_DIR),
            ]);

            // ── GET ALL SPOTS WITH MEMORIES ──────────────────────
        case 'spots':
            $spots = db()->query("SELECT * FROM spots ORDER BY created_at ASC")->fetchAll();
            foreach ($spots as &$spot) {
                $stmt = db()->prepare(
                    "SELECT * FROM memories WHERE spot_id = ? ORDER BY position ASC"
                );
                $stmt->execute([$spot['id']]);
                $mems = $stmt->fetchAll();
                foreach ($mems as &$m) {
                    $m['reversed'] = (bool)$m['reversed'];
                    $m['image_url'] = $m['image_path']
                        ? UPLOAD_URL . $m['image_path']
                        : null;
                }
                $spot['memories'] = $mems;
                $spot['music_url'] = $spot['music_path']
                    ? UPLOAD_URL . $spot['music_path']
                    : null;
                unset($spot['secret_password']); // nunca expõe a senha ao frontend
            }
            respond(['ok' => true, 'spots' => $spots]);


            // ── CREATE SPOT ──────────────────────────────────────
        case 'spot_create':
            $b = body();
            $id = uuid();
            $name      = trim($b['name'] ?? 'Novo local');
            $x         = (float)($b['x'] ?? 0);
            $y         = (float)($b['y'] ?? 0);
            $isSecret  = !empty($b['is_secret']) ? 1 : 0;
            $secretPwd = null;
            if ($isSecret && !empty($b['secret_password'])) {
                $secretPwd = password_hash($b['secret_password'], PASSWORD_DEFAULT);
            }
            db()->prepare(
                "INSERT INTO spots (id, name, world_x, world_y, is_secret, secret_password)
         VALUES (?, ?, ?, ?, ?, ?)"
            )->execute([$id, $name, $x, $y, $isSecret, $secretPwd]);
            db()->prepare(
                "INSERT INTO memories (spot_id, position, title, body, icon, reversed)
         VALUES (?, 0, 'Nova memória', 'Clique no ✎ para editar.', '➕', 0)"
            )->execute([$id]);
            respond(['ok' => true, 'id' => $id]);


            // ── UPDATE SPOT ──────────────────────────────────────
        case 'spot_update':
            $b = body();
            $id = $b['id'] ?? '';
            if (!$id) respondError('id required');
            $fields = [];
            $params = [];
            if (isset($b['name'])) {
                $fields[] = 'name = ?';
                $params[] = trim($b['name']);
            }
            if (isset($b['world_x'])) {
                $fields[] = 'world_x = ?';
                $params[] = (float)$b['world_x'];
            }
            if (isset($b['world_y'])) {
                $fields[] = 'world_y = ?';
                $params[] = (float)$b['world_y'];
            }
            if (empty($fields)) respondError('nothing to update');
            $params[] = $id;
            db()->prepare("UPDATE spots SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
            respond(['ok' => true]);


            // ── DELETE SPOT ──────────────────────────────────────
        case 'spot_delete':
            $b  = body();
            $id = $b['id'] ?? '';
            if (!$id) respondError('id required');
            $stmt = db()->prepare("SELECT image_path FROM memories WHERE spot_id = ?");
            $stmt->execute([$id]);
            foreach ($stmt->fetchAll() as $row) {
                if ($row['image_path']) {
                    $file = UPLOAD_DIR . $row['image_path'];
                    if (file_exists($file)) unlink($file);
                }
            }
            // deleta música do spot se existir
            $stmt2 = db()->prepare("SELECT music_path FROM spots WHERE id = ?");
            $stmt2->execute([$id]);
            $musicPath = $stmt2->fetchColumn();
            if ($musicPath) {
                $mf = UPLOAD_DIR . $musicPath;
                if (file_exists($mf)) unlink($mf);
            }
            db()->prepare("DELETE FROM spots WHERE id = ?")->execute([$id]);
            respond(['ok' => true]);


            // ── CREATE MEMORY ────────────────────────────────────
        case 'memory_create':
            $b = body();
            $spot_id = $b['spot_id'] ?? '';
            if (!$spot_id) respondError('spot_id required');
            $stmt = db()->prepare("SELECT COALESCE(MAX(position),0)+1 AS pos FROM memories WHERE spot_id = ?");
            $stmt->execute([$spot_id]);
            $pos = (int)$stmt->fetchColumn();
            $rev = ($pos % 2 === 1) ? 1 : 0;
            $stmt = db()->prepare(
                "INSERT INTO memories (spot_id, position, title, body, icon, reversed)
                 VALUES (?, ?, 'Nova memória', 'Clique no ✎ para editar.', '➕', ?)"
            );
            $stmt->execute([$spot_id, $pos, $rev]);
            respond(['ok' => true, 'id' => db()->lastInsertId()]);


            // ── UPDATE MEMORY ────────────────────────────────────
        case 'memory_update':
            $b  = body();
            $id = $b['id'] ?? '';
            if (!$id) respondError('id required');
            $fields = [];
            $params = [];
            if (isset($b['title'])) {
                $fields[] = 'title = ?';
                $params[] = trim($b['title']);
            }
            if (isset($b['body'])) {
                $fields[] = 'body = ?';
                $params[] = trim($b['body']);
            }
            if (isset($b['icon'])) {
                $fields[] = 'icon = ?';
                $params[] = $b['icon'];
            }
            if (empty($fields)) respondError('nothing to update');
            $params[] = $id;
            db()->prepare("UPDATE memories SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
            respond(['ok' => true]);


            // ── DELETE MEMORY ────────────────────────────────────
        case 'memory_delete':
            $b  = body();
            $id = (int)($b['id'] ?? 0);
            if (!$id) respondError('id required');
            $stmt = db()->prepare("SELECT image_path FROM memories WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row && $row['image_path']) {
                $file = UPLOAD_DIR . $row['image_path'];
                if (file_exists($file)) unlink($file);
            }
            db()->prepare("DELETE FROM memories WHERE id = ?")->execute([$id]);
            respond(['ok' => true]);


            // ── UPLOAD IMAGE ─────────────────────────────────────
        case 'memory_upload':
            $id = (int)($_POST['memory_id'] ?? 0);
            if (!$id) respondError('memory_id required');

            if (!isset($_FILES['image'])) {
                respondError('nenhum arquivo recebido — verifique se file_uploads está habilitado no php.ini');
            }
            $uploadErr = $_FILES['image']['error'];
            if ($uploadErr !== UPLOAD_ERR_OK) {
                $uploadMessages = [
                    UPLOAD_ERR_INI_SIZE   => 'arquivo maior que upload_max_filesize no php.ini (' . ini_get('upload_max_filesize') . ')',
                    UPLOAD_ERR_FORM_SIZE  => 'arquivo maior que MAX_FILE_SIZE no formulário',
                    UPLOAD_ERR_PARTIAL    => 'upload incompleto',
                    UPLOAD_ERR_NO_FILE    => 'nenhum arquivo enviado',
                    UPLOAD_ERR_NO_TMP_DIR => 'pasta temporária ausente no servidor',
                    UPLOAD_ERR_CANT_WRITE => 'falha ao gravar arquivo temporário (permissão)',
                    UPLOAD_ERR_EXTENSION  => 'upload bloqueado por extensão PHP',
                ];
                respondError('upload error: ' . ($uploadMessages[$uploadErr] ?? 'código ' . $uploadErr));
            }

            $file     = $_FILES['image'];
            $maxBytes = MAX_UPLOAD_MB * 1024 * 1024;
            if ($file['size'] > $maxBytes) respondError('arquivo muito grande (máx ' . MAX_UPLOAD_MB . 'MB)');

            $mime = mime_content_type($file['tmp_name']);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                respondError('tipo de imagem inválido: ' . $mime);
            }

            if (!is_dir(UPLOAD_DIR)) {
                if (!mkdir(UPLOAD_DIR, 0755, true)) {
                    respondError('não foi possível criar a pasta uploads/ — verifique permissões do servidor');
                }
            }
            if (!is_writable(UPLOAD_DIR)) {
                respondError('pasta uploads/ sem permissão de escrita — execute: chmod 755 uploads/');
            }

            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg');
            $name = $id . '_' . time() . '.' . $ext;

            $stmt = db()->prepare("SELECT image_path FROM memories WHERE id = ?");
            $stmt->execute([$id]);
            $old = $stmt->fetchColumn();
            if ($old) {
                $oldFile = UPLOAD_DIR . $old;
                if (file_exists($oldFile)) unlink($oldFile);
            }

            if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) {
                respondError('falha ao mover o arquivo para uploads/ — verifique permissões');
            }

            db()->prepare("UPDATE memories SET image_path = ? WHERE id = ?")->execute([$name, $id]);
            respond(['ok' => true, 'image_url' => UPLOAD_URL . $name]);


            // ── REMOVE IMAGE ─────────────────────────────────────
        case 'memory_remove_img':
            $b  = body();
            $id = (int)($b['id'] ?? 0);
            if (!$id) respondError('id required');
            $stmt = db()->prepare("SELECT image_path FROM memories WHERE id = ?");
            $stmt->execute([$id]);
            $path = $stmt->fetchColumn();
            if ($path) {
                $f = UPLOAD_DIR . $path;
                if (file_exists($f)) unlink($f);
            }
            db()->prepare("UPDATE memories SET image_path = NULL WHERE id = ?")->execute([$id]);
            respond(['ok' => true]);


            // ── GET ALL STICKERS ─────────────────────────────────
        case 'stickers':
            $rows = db()->query("SELECT * FROM stickers ORDER BY created_at ASC")->fetchAll();
            foreach ($rows as &$row) {
                $row['image_url'] = UPLOAD_URL . $row['image_path'];
            }
            respond(['ok' => true, 'stickers' => $rows]);

            // ── UPLOAD STICKER ────────────────────────────────────
        case 'sticker_upload':
            $x = (float)($_POST['world_x'] ?? 0);
            $y = (float)($_POST['world_y'] ?? 0);
            if (!isset($_FILES['image'])) respondError('nenhum arquivo recebido');
            $uploadErr = $_FILES['image']['error'];
            if ($uploadErr !== UPLOAD_ERR_OK) respondError('upload error: código ' . $uploadErr);
            $file = $_FILES['image'];
            $maxBytes = MAX_UPLOAD_MB * 1024 * 1024;
            if ($file['size'] > $maxBytes) respondError('arquivo muito grande');
            $mime = mime_content_type($file['tmp_name']);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']))
                respondError('tipo inválido: ' . $mime);
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            if (!is_writable(UPLOAD_DIR)) respondError('uploads/ sem permissão de escrita');
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'png');
            $name = 'sticker_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name))
                respondError('falha ao salvar arquivo');
            db()->prepare("INSERT INTO stickers (world_x, world_y, image_path) VALUES (?,?,?)")
                ->execute([$x, $y, $name]);
            $id = db()->lastInsertId();
            respond(['ok' => true, 'id' => $id, 'image_url' => UPLOAD_URL . $name]);

            // ── DELETE STICKER ────────────────────────────────────
        case 'sticker_delete':
            $b  = body();
            $id = (int)($b['id'] ?? 0);
            if (!$id) respondError('id required');
            $stmt = db()->prepare("SELECT image_path FROM stickers WHERE id = ?");
            $stmt->execute([$id]);
            $path = $stmt->fetchColumn();
            if ($path) {
                $f = UPLOAD_DIR . $path;
                if (file_exists($f)) unlink($f);
            }
            db()->prepare("DELETE FROM stickers WHERE id = ?")->execute([$id]);
            respond(['ok' => true]);

            // ── UPLOAD MÚSICA DO LOCAL ────────────────────────────────
        case 'spot_music_upload':
            $spot_id = $_POST['spot_id'] ?? '';
            if (!$spot_id) respondError('spot_id required');
            if (!isset($_FILES['music'])) respondError('nenhum arquivo recebido');
            $uploadErr = $_FILES['music']['error'];
            if ($uploadErr !== UPLOAD_ERR_OK) respondError('upload error: código ' . $uploadErr);
            $file = $_FILES['music'];
            $maxBytes = MAX_UPLOAD_MB * 1024 * 1024;
            if ($file['size'] > $maxBytes) respondError('arquivo muito grande');
            $mime = mime_content_type($file['tmp_name']);
            if (!str_starts_with($mime, 'audio/')) respondError('tipo inválido: ' . $mime);
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            if (!is_writable(UPLOAD_DIR)) respondError('uploads/ sem permissão de escrita');
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'mp3');
            $name = 'spot_' . $spot_id . '_music_' . time() . '.' . $ext;
            // remove música antiga
            $stmt = db()->prepare("SELECT music_path FROM spots WHERE id = ?");
            $stmt->execute([$spot_id]);
            $old = $stmt->fetchColumn();
            if ($old) {
                $f = UPLOAD_DIR . $old;
                if (file_exists($f)) unlink($f);
            }
            if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name))
                respondError('falha ao mover arquivo');
            db()->prepare("UPDATE spots SET music_path = ?, music_name = ? WHERE id = ?")
                ->execute([$name, $file['name'], $spot_id]);
            respond(['ok' => true, 'music_url' => UPLOAD_URL . $name]);

            // ── REMOVER MÚSICA DO LOCAL ───────────────────────────────
        case 'spot_music_remove':
            $b  = body();
            $id = $b['id'] ?? '';
            if (!$id) respondError('id required');
            $stmt = db()->prepare("SELECT music_path FROM spots WHERE id = ?");
            $stmt->execute([$id]);
            $path = $stmt->fetchColumn();
            if ($path) {
                $f = UPLOAD_DIR . $path;
                if (file_exists($f)) unlink($f);
            }
            db()->prepare("UPDATE spots SET music_path = NULL, music_name = NULL WHERE id = ?")
                ->execute([$id]);
            respond(['ok' => true]);

            // ── VERIFICAR SENHA DO LOCAL SECRETO ─────────────────
        case 'spot_unlock':
            $b  = body();
            $id = $b['id'] ?? '';
            $pw = $b['password'] ?? '';
            if (!$id || !$pw) respondError('dados incompletos');
            $stmt = db()->prepare("SELECT secret_password FROM spots WHERE id = ? AND is_secret = 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) respondError('local não encontrado', 404);
            if (!password_verify($pw, $row['secret_password'])) {
                respond(['ok' => false, 'error' => 'senha incorreta'], 401);
            }
            respond(['ok' => true]);

        default:
            respondError('unknown action', 404);
    }
} catch (Exception $e) {
    respondError('server error: ' . $e->getMessage(), 500);
}

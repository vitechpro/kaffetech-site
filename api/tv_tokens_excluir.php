<?php
require_once '../inc/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception('ID inválido');
    }

    $db = db();
    $st = $db->prepare("DELETE FROM tv_tokens WHERE id = ?");
    $st->execute([$id]);

    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'   => false,
        'erro' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

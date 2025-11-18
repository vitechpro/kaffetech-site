<?php
require_once '../inc/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $db = db();

    $st = $db->query("
        SELECT id, token, descricao, ativo, criado_em
        FROM tv_tokens
        ORDER BY criado_em DESC, id DESC
    ");

    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'ok'   => true,
        'data' => $rows,
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'   => false,
        'erro' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

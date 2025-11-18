<?php
require_once '../inc/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

try {
    // aqui você pode validar se o usuário logado é admin, se quiser
    // ex: if (!is_admin()) { throw new Exception('Sem permissão'); }

    $id        = (int)($_POST['id'] ?? 0);
    $descricao = trim($_POST['descricao'] ?? '');
    $ativo     = isset($_POST['ativo']) ? (int)$_POST['ativo'] : 1;
    $regen     = isset($_POST['regen']) ? (int)$_POST['regen'] : 0;

    if ($descricao === '') {
        throw new Exception('Descrição é obrigatória');
    }

    $db = db();

    if ($id > 0) {
        // update
        $campos = ['descricao = ?', 'ativo = ?'];
        $params = [$descricao, $ativo];

        if ($regen === 1) {
            $novoToken = bin2hex(random_bytes(16)); // 32 chars
            $campos[] = 'token = ?';
            $params[] = $novoToken;
        }

        $params[] = $id;

        $sql = "UPDATE tv_tokens SET " . implode(', ', $campos) . " WHERE id = ?";
        $st  = $db->prepare($sql);
        $st->execute($params);

    } else {
        // insert
        $token = bin2hex(random_bytes(16)); // 32 chars
        $sql   = "INSERT INTO tv_tokens (token, descricao, ativo) VALUES (?, ?, ?)";
        $st    = $db->prepare($sql);
        $st->execute([$token, $descricao, $ativo]);
        $id = (int)$db->lastInsertId();
    }

    echo json_encode(['ok' => true, 'id' => $id], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'   => false,
        'erro' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

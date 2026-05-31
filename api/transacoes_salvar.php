<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

try {
    $id          = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $tipo        = $_POST['tipo'] ?? '';
    $descricao   = trim($_POST['descricao'] ?? '');
    $valor       = $_POST['valor'] ?? '';
    $data        = $_POST['data'] ?? '';
    $id_cat      = (int)($_POST['id_categoria'] ?? 0);
    $observacao  = trim($_POST['observacao'] ?? '') ?: null;

    if (!in_array($tipo, ['receita', 'despesa'])) {
        echo json_encode(['success' => false, 'error' => 'Tipo inválido.']);
        exit;
    }
    if ($descricao === '') {
        echo json_encode(['success' => false, 'error' => 'Descrição é obrigatória.']);
        exit;
    }
    if (!is_numeric($valor) || (float)$valor <= 0) {
        echo json_encode(['success' => false, 'error' => 'Valor deve ser maior que zero.']);
        exit;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) || !checkdate(
        (int)substr($data, 5, 2), (int)substr($data, 8, 2), (int)substr($data, 0, 4)
    )) {
        echo json_encode(['success' => false, 'error' => 'Data inválida.']);
        exit;
    }
    if ($id_cat <= 0) {
        echo json_encode(['success' => false, 'error' => 'Categoria é obrigatória.']);
        exit;
    }

    $pdo = Database::getInstance();

    if ($id === null) {
        $stmt = $pdo->prepare(
            'INSERT INTO transacoes (tipo, descricao, valor, data, id_categoria, observacao)
             VALUES (:tipo, :descricao, :valor, :data, :id_categoria, :observacao)'
        );
        $stmt->execute([
            ':tipo'         => $tipo,
            ':descricao'    => $descricao,
            ':valor'        => (float)$valor,
            ':data'         => $data,
            ':id_categoria' => $id_cat,
            ':observacao'   => $observacao,
        ]);
        $id = $pdo->lastInsertId();
    } else {
        $stmt = $pdo->prepare(
            'UPDATE transacoes SET tipo=:tipo, descricao=:descricao, valor=:valor,
             data=:data, id_categoria=:id_categoria, observacao=:observacao WHERE id=:id'
        );
        $stmt->execute([
            ':tipo'         => $tipo,
            ':descricao'    => $descricao,
            ':valor'        => (float)$valor,
            ':data'         => $data,
            ':id_categoria' => $id_cat,
            ':observacao'   => $observacao,
            ':id'           => $id,
        ]);
    }

    echo json_encode(['success' => true, 'id' => $id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao salvar transação.']);
}

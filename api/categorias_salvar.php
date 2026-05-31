<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

try {
    $id   = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $nome = trim($_POST['nome'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $cor  = trim($_POST['cor'] ?? '');

    if ($nome === '') {
        echo json_encode(['success' => false, 'error' => 'Nome é obrigatório.']);
        exit;
    }
    if (!in_array($tipo, ['receita', 'despesa', 'ambos'])) {
        echo json_encode(['success' => false, 'error' => 'Tipo inválido.']);
        exit;
    }
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $cor)) {
        echo json_encode(['success' => false, 'error' => 'Cor inválida.']);
        exit;
    }

    $pdo = Database::getInstance();

    if ($id === null) {
        $stmt = $pdo->prepare('INSERT INTO categorias (nome, tipo, cor) VALUES (:nome, :tipo, :cor)');
        $stmt->execute([':nome' => $nome, ':tipo' => $tipo, ':cor' => $cor]);
        $id = $pdo->lastInsertId();
    } else {
        $stmt = $pdo->prepare('UPDATE categorias SET nome=:nome, tipo=:tipo, cor=:cor WHERE id=:id');
        $stmt->execute([':nome' => $nome, ':tipo' => $tipo, ':cor' => $cor, ':id' => $id]);
    }

    echo json_encode(['success' => true, 'id' => $id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao salvar categoria.']);
}

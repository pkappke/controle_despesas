<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

try {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID inválido.']);
        exit;
    }

    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('DELETE FROM transacoes WHERE id = :id');
    $stmt->execute([':id' => $id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao excluir transação.']);
}

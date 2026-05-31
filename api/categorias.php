<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

try {
    $pdo = Database::getInstance();
    $tipo = $_GET['tipo'] ?? '';

    $sql = 'SELECT id, nome, tipo, cor FROM categorias';
    $params = [];

    if (in_array($tipo, ['receita', 'despesa'])) {
        $sql .= " WHERE tipo = :tipo OR tipo = 'ambos'";
        $params[':tipo'] = $tipo;
    }

    $sql .= ' ORDER BY nome';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $dados = $stmt->fetchAll();

    foreach ($dados as &$cat) {
        $cat['texto_cor'] = calcularTextoCor($cat['cor']);
    }

    echo json_encode(['success' => true, 'data' => $dados]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao carregar categorias.']);
}

function calcularTextoCor(string $hex): string {
    $r = hexdec(substr($hex, 1, 2));
    $g = hexdec(substr($hex, 3, 2));
    $b = hexdec(substr($hex, 5, 2));
    $luma = ($r * 299 + $g * 587 + $b * 114) / 1000;
    return $luma < 128 ? '#ffffff' : '#000000';
}

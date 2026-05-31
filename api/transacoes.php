<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

function calcularTextoCor(string $hex): string {
    $r = hexdec(substr($hex, 1, 2));
    $g = hexdec(substr($hex, 3, 2));
    $b = hexdec(substr($hex, 5, 2));
    $luma = ($r * 299 + $g * 587 + $b * 114) / 1000;
    return $luma < 128 ? '#ffffff' : '#000000';
}

try {
    $pdo = Database::getInstance();

    // Single transaction by id
    if (!empty($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare(
            'SELECT t.id, t.tipo, t.descricao, t.valor, t.data, t.id_categoria, t.observacao,
                    c.nome AS categoria, c.cor
             FROM transacoes t
             JOIN categorias c ON t.id_categoria = c.id
             WHERE t.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            echo json_encode(['success' => false, 'error' => 'Transação não encontrada.']);
            exit;
        }
        $row['texto_cor'] = calcularTextoCor($row['cor']);
        echo json_encode(['success' => true, 'data' => $row]);
        exit;
    }

    // Weekly query
    if (!empty($_GET['semana']) && !empty($_GET['ano'])) {
        $ano    = (int)$_GET['ano'];
        $semana = (int)$_GET['semana'];

        $stmt = $pdo->prepare(
            'SELECT t.id, t.tipo, t.descricao, t.valor, t.data, t.id_categoria, t.observacao,
                    c.nome AS categoria, c.cor
             FROM transacoes t
             JOIN categorias c ON t.id_categoria = c.id
             WHERE YEARWEEK(t.data, 3) = YEARWEEK(STR_TO_DATE(CONCAT(:ano, :semana), \'%X%V\'), 3)
             ORDER BY t.data ASC, t.id ASC'
        );
        $stmt->execute([':ano' => $ano, ':semana' => str_pad($semana, 2, '0', STR_PAD_LEFT)]);
        $rows = $stmt->fetchAll();

        $totais = ['receitas' => 0, 'despesas' => 0];
        foreach ($rows as &$row) {
            $row['texto_cor'] = calcularTextoCor($row['cor']);
            if ($row['tipo'] === 'receita') $totais['receitas'] += $row['valor'];
            else $totais['despesas'] += $row['valor'];
        }
        $totais['saldo'] = $totais['receitas'] - $totais['despesas'];

        echo json_encode(['success' => true, 'data' => $rows, 'totais' => $totais]);
        exit;
    }

    // Monthly query (default)
    $ano = (int)($_GET['ano'] ?? date('Y'));
    $mes = (int)($_GET['mes'] ?? date('n'));

    $stmt = $pdo->prepare(
        'SELECT t.id, t.tipo, t.descricao, t.valor, t.data, t.id_categoria, t.observacao,
                c.nome AS categoria, c.cor
         FROM transacoes t
         JOIN categorias c ON t.id_categoria = c.id
         WHERE YEAR(t.data) = :ano AND MONTH(t.data) = :mes
         ORDER BY t.data DESC, t.id DESC'
    );
    $stmt->execute([':ano' => $ano, ':mes' => $mes]);
    $rows = $stmt->fetchAll();

    $totais = ['receitas' => 0, 'despesas' => 0];
    foreach ($rows as &$row) {
        $row['texto_cor'] = calcularTextoCor($row['cor']);
        if ($row['tipo'] === 'receita') $totais['receitas'] += $row['valor'];
        else $totais['despesas'] += $row['valor'];
    }
    $totais['saldo'] = $totais['receitas'] - $totais['despesas'];

    echo json_encode(['success' => true, 'data' => $rows, 'totais' => $totais]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao carregar transações.']);
}

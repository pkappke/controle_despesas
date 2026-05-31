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
    $ano = (int)($_GET['ano'] ?? date('Y'));
    $mes = (int)($_GET['mes'] ?? date('n'));

    // Summary
    $stmt = $pdo->prepare(
        'SELECT
            SUM(CASE WHEN tipo=\'receita\' THEN valor ELSE 0 END) AS total_receitas,
            SUM(CASE WHEN tipo=\'despesa\' THEN valor ELSE 0 END) AS total_despesas,
            COUNT(*) AS num_transacoes
         FROM transacoes
         WHERE YEAR(data) = :ano AND MONTH(data) = :mes'
    );
    $stmt->execute([':ano' => $ano, ':mes' => $mes]);
    $resumo = $stmt->fetch();
    $resumo['total_receitas'] = (float)($resumo['total_receitas'] ?? 0);
    $resumo['total_despesas'] = (float)($resumo['total_despesas'] ?? 0);
    $resumo['saldo'] = $resumo['total_receitas'] - $resumo['total_despesas'];
    $resumo['num_transacoes'] = (int)$resumo['num_transacoes'];

    // Expenses by category (for doughnut chart)
    $stmt = $pdo->prepare(
        'SELECT c.nome AS categoria, c.cor, SUM(t.valor) AS total
         FROM transacoes t
         JOIN categorias c ON t.id_categoria = c.id
         WHERE t.tipo = \'despesa\' AND YEAR(t.data) = :ano AND MONTH(t.data) = :mes
         GROUP BY c.id
         ORDER BY total DESC'
    );
    $stmt->execute([':ano' => $ano, ':mes' => $mes]);
    $porCategoria = $stmt->fetchAll();
    foreach ($porCategoria as &$cat) {
        $cat['total'] = (float)$cat['total'];
        $cat['texto_cor'] = calcularTextoCor($cat['cor']);
    }

    // Income vs expenses by day (for bar chart)
    $stmt = $pdo->prepare(
        'SELECT DAY(data) AS dia,
            SUM(CASE WHEN tipo=\'receita\' THEN valor ELSE 0 END) AS receitas,
            SUM(CASE WHEN tipo=\'despesa\' THEN valor ELSE 0 END) AS despesas
         FROM transacoes
         WHERE YEAR(data) = :ano AND MONTH(data) = :mes
         GROUP BY DAY(data)
         ORDER BY dia'
    );
    $stmt->execute([':ano' => $ano, ':mes' => $mes]);
    $porDia = $stmt->fetchAll();
    foreach ($porDia as &$d) {
        $d['dia']      = (int)$d['dia'];
        $d['receitas'] = (float)$d['receitas'];
        $d['despesas'] = (float)$d['despesas'];
    }

    // Recent transactions (last 10)
    $stmt = $pdo->prepare(
        'SELECT t.id, t.tipo, t.descricao, t.valor, t.data,
                c.nome AS categoria, c.cor
         FROM transacoes t
         JOIN categorias c ON t.id_categoria = c.id
         WHERE YEAR(t.data) = :ano AND MONTH(t.data) = :mes
         ORDER BY t.data DESC, t.id DESC
         LIMIT 10'
    );
    $stmt->execute([':ano' => $ano, ':mes' => $mes]);
    $recentes = $stmt->fetchAll();
    foreach ($recentes as &$r) {
        $r['texto_cor'] = calcularTextoCor($r['cor']);
    }

    echo json_encode([
        'success'      => true,
        'resumo'       => $resumo,
        'por_categoria' => $porCategoria,
        'por_dia'      => $porDia,
        'recentes'     => $recentes,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao carregar dashboard.']);
}

<?php
// Run once to (re)create the database schema and seed data.
// Access: http://localhost/controle_despesas/setup.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'controle_despesas');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Setup — Controle de Despesas</title>
<style>
    body { font-family: sans-serif; max-width: 700px; margin: 2rem auto; padding: 0 1rem; }
    .ok  { color: #2E7D32; } .err { color: #C62828; }
    pre  { background: #f5f5f5; padding: 1rem; border-radius: 8px; font-size: .85rem; }
    h1   { color: #2E7D32; }
    a.btn { display:inline-block;margin-top:1.5rem;padding:.6rem 1.4rem;background:#2E7D32;
            color:#fff;border-radius:8px;text-decoration:none;font-weight:600; }
</style>
</head>
<body>
<h1>⚙️ Setup — Controle de Despesas</h1>
<?php

$log = [];
$ok  = true;

function logStep(string $msg, bool $success): void {
    global $log, $ok;
    $log[] = ['msg' => $msg, 'ok' => $success];
    if (!$success) $ok = false;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false,
         PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"]
    );
    logStep('Conexão com MySQL estabelecida.', true);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    logStep('Banco de dados criado/verificado.', true);

    $pdo->exec("USE `" . DB_NAME . "`");

    $pdo->exec("CREATE TABLE IF NOT EXISTS categorias (
        id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nome      VARCHAR(100) NOT NULL,
        tipo      ENUM('receita','despesa','ambos') NOT NULL DEFAULT 'ambos',
        cor       CHAR(7) NOT NULL DEFAULT '#607D8B',
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    logStep('Tabela <code>categorias</code> criada/verificada.', true);

    $pdo->exec("CREATE TABLE IF NOT EXISTS transacoes (
        id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tipo          ENUM('receita','despesa') NOT NULL,
        descricao     VARCHAR(255) NOT NULL,
        valor         DECIMAL(12,2) NOT NULL,
        data          DATE NOT NULL,
        id_categoria  INT UNSIGNED NOT NULL,
        observacao    TEXT DEFAULT NULL,
        criado_em     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON UPDATE CASCADE ON DELETE RESTRICT,
        INDEX idx_data (data),
        INDEX idx_tipo (tipo),
        INDEX idx_categoria (id_categoria)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    logStep('Tabela <code>transacoes</code> criada/verificada.', true);

    // Seed categories only if table is empty
    $count = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    if ($count == 0) {
        $seed = [
            ['Salário',          'receita', '#4CAF50'],
            ['Freelance',        'receita', '#8BC34A'],
            ['Investimentos',    'receita', '#00BCD4'],
            ['Outros (Receita)', 'receita', '#9E9E9E'],
            ['Moradia',          'despesa', '#F44336'],
            ['Alimentação',      'despesa', '#FF9800'],
            ['Transporte',       'despesa', '#FF5722'],
            ['Saúde',            'despesa', '#E91E63'],
            ['Educação',         'despesa', '#9C27B0'],
            ['Lazer',            'despesa', '#3F51B5'],
            ['Roupas',           'despesa', '#2196F3'],
            ['Utilidades',       'despesa', '#009688'],
            ['Outros (Despesa)', 'despesa', '#757575'],
        ];
        $stmt = $pdo->prepare("INSERT INTO categorias (nome, tipo, cor) VALUES (:nome, :tipo, :cor)");
        foreach ($seed as [$nome, $tipo, $cor]) {
            $stmt->execute([':nome' => $nome, ':tipo' => $tipo, ':cor' => $cor]);
        }
        logStep('Categorias padrão inseridas (' . count($seed) . ' registros).', true);
    } else {
        // Truncate and re-seed (clean slate)
        $pdo->exec("DELETE FROM categorias");
        $pdo->exec("ALTER TABLE categorias AUTO_INCREMENT = 1");
        $seed = [
            ['Salário',          'receita', '#4CAF50'],
            ['Freelance',        'receita', '#8BC34A'],
            ['Investimentos',    'receita', '#00BCD4'],
            ['Outros (Receita)', 'receita', '#9E9E9E'],
            ['Moradia',          'despesa', '#F44336'],
            ['Alimentação',      'despesa', '#FF9800'],
            ['Transporte',       'despesa', '#FF5722'],
            ['Saúde',            'despesa', '#E91E63'],
            ['Educação',         'despesa', '#9C27B0'],
            ['Lazer',            'despesa', '#3F51B5'],
            ['Roupas',           'despesa', '#2196F3'],
            ['Utilidades',       'despesa', '#009688'],
            ['Outros (Despesa)', 'despesa', '#757575'],
        ];
        $stmt = $pdo->prepare("INSERT INTO categorias (nome, tipo, cor) VALUES (:nome, :tipo, :cor)");
        foreach ($seed as [$nome, $tipo, $cor]) {
            $stmt->execute([':nome' => $nome, ':tipo' => $tipo, ':cor' => $cor]);
        }
        logStep('Categorias re-inseridas com dados corretos (' . count($seed) . ' registros).', true);
    }

} catch (PDOException $e) {
    logStep('Erro PDO: ' . htmlspecialchars($e->getMessage()), false);
}

foreach ($log as $entry) {
    $cls = $entry['ok'] ? 'ok' : 'err';
    $ico = $entry['ok'] ? '✔' : '✘';
    echo "<p class=\"{$cls}\">{$ico} {$entry['msg']}</p>";
}

if ($ok): ?>
<p style="margin-top:1.5rem;font-size:1.1rem"><strong>✅ Setup concluído com sucesso!</strong></p>
<a class="btn" href="index.php">Ir para o Dashboard →</a>
<?php else: ?>
<p style="margin-top:1rem" class="err"><strong>❌ Setup finalizado com erros. Verifique as mensagens acima.</strong></p>
<?php endif; ?>
</body>
</html>

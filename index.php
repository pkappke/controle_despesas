<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Controle de Despesas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <a href="index.php" class="navbar-brand"><span>💰</span> Controle de Despesas</a>
    <div class="navbar-nav">
        <a href="index.php"       class="nav-link ativo">Dashboard</a>
        <a href="mensal.php"      class="nav-link">Mensal</a>
        <a href="semanal.php"     class="nav-link">Semanal</a>
        <a href="categorias.php"  class="nav-link">Categorias</a>
    </div>
    <div class="navbar-actions">
        <button class="btn-nova-transacao" id="btn-nova-transacao-nav">+ Nova Transação</button>
    </div>
    <button class="nav-toggle" id="nav-toggle" aria-label="Menu">☰</button>
</nav>

<main class="container">
    <!-- Period Selector -->
    <div class="period-selector">
        <button class="btn-periodo" id="btn-mes-anterior" title="Mês anterior">‹</button>
        <h2 id="titulo-mes">Carregando...</h2>
        <button class="btn-periodo" id="btn-mes-seguinte" title="Próximo mês">›</button>
        <button class="btn-periodo atual" id="btn-mes-atual">Mês Atual</button>
    </div>

    <!-- Loading -->
    <div id="loading-dash" class="loading-center" style="display:none">
        <div class="spinner"></div>
    </div>

    <!-- Dashboard Content -->
    <div id="dash-conteudo">
        <!-- Summary Cards -->
        <div class="resumo-grid">
            <div class="resumo-card receita">
                <div class="resumo-card-label">Total Receitas</div>
                <div class="resumo-card-valor" id="card-receitas">R$ 0,00</div>
            </div>
            <div class="resumo-card despesa">
                <div class="resumo-card-label">Total Despesas</div>
                <div class="resumo-card-valor" id="card-despesas">R$ 0,00</div>
            </div>
            <div class="resumo-card saldo positivo" id="resumo-saldo">
                <div class="resumo-card-label">Saldo</div>
                <div class="resumo-card-valor" id="card-saldo">R$ 0,00</div>
            </div>
            <div class="resumo-card total">
                <div class="resumo-card-label">Transações</div>
                <div class="resumo-card-valor" id="card-num">0</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3>Despesas por Categoria</h3>
                <div class="chart-container">
                    <canvas id="grafico-categorias"></canvas>
                    <div id="sem-dados-dona" class="vazio" style="display:none">
                        <p>Sem despesas neste mês.</p>
                    </div>
                </div>
            </div>
            <div class="chart-card">
                <h3>Receitas vs Despesas por Dia</h3>
                <div class="chart-container">
                    <canvas id="grafico-mensal"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="section-header">
            <h3>Últimas Transações do Mês</h3>
            <a href="mensal.php" class="btn btn-ghost btn-sm">Ver todas →</a>
        </div>
        <div class="tabela-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Descrição / Categoria</th>
                        <th>Valor</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody-recentes">
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:#9E9E9E">Carregando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Floating Add Button -->
<button class="btn-fab" id="btn-fab" title="Nova Transação">+</button>

<!-- Toast -->
<div id="toast"></div>

<?php include 'includes/modal_transacao.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="assets/js/app.js"></script>
<script src="assets/js/dashboard.js"></script>
</body>
</html>

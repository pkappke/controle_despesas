<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visão Mensal — Controle de Despesas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <a href="index.php" class="navbar-brand"><span>💰</span> Controle de Despesas</a>
    <div class="navbar-nav">
        <a href="index.php"       class="nav-link">Dashboard</a>
        <a href="mensal.php"      class="nav-link ativo">Mensal</a>
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

    <!-- Summary Cards -->
    <div class="resumo-grid" style="margin-bottom:1.5rem">
        <div class="resumo-card receita">
            <div class="resumo-card-label">Total Receitas</div>
            <div class="resumo-card-valor" id="res-receitas">R$ 0,00</div>
        </div>
        <div class="resumo-card despesa">
            <div class="resumo-card-label">Total Despesas</div>
            <div class="resumo-card-valor" id="res-despesas">R$ 0,00</div>
        </div>
        <div class="resumo-card saldo positivo" id="res-saldo-card">
            <div class="resumo-card-label">Saldo</div>
            <div class="resumo-card-valor" id="res-saldo">R$ 0,00</div>
        </div>
    </div>

    <!-- Transaction list grouped by day -->
    <div id="lista-mensal">
        <div class="loading-center"><div class="spinner"></div></div>
    </div>

    <!-- Monthly totals footer -->
    <div class="totais-rodape" id="rodape-mensal"></div>
</main>

<button class="btn-fab" id="btn-fab" title="Nova Transação">+</button>
<div id="toast"></div>

<?php include 'includes/modal_transacao.php'; ?>

<script src="assets/js/app.js"></script>
<script src="assets/js/mensal.js"></script>
</body>
</html>

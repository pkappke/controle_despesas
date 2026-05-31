<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visão Semanal — Controle de Despesas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <a href="index.php" class="navbar-brand"><span>💰</span> Controle de Despesas</a>
    <div class="navbar-nav">
        <a href="index.php"       class="nav-link">Dashboard</a>
        <a href="mensal.php"      class="nav-link">Mensal</a>
        <a href="semanal.php"     class="nav-link ativo">Semanal</a>
        <a href="categorias.php"  class="nav-link">Categorias</a>
    </div>
    <div class="navbar-actions">
        <button class="btn-nova-transacao" id="btn-nova-transacao-nav">+ Nova Transação</button>
    </div>
    <button class="nav-toggle" id="nav-toggle" aria-label="Menu">☰</button>
</nav>

<main class="container">
    <!-- Week Selector -->
    <div class="period-selector">
        <button class="btn-periodo" id="btn-semana-anterior" title="Semana anterior">‹</button>
        <h2 id="titulo-semana">Carregando...</h2>
        <button class="btn-periodo" id="btn-semana-seguinte" title="Próxima semana">›</button>
        <button class="btn-periodo atual" id="btn-semana-atual">Esta Semana</button>
    </div>

    <!-- Summary Cards -->
    <div class="resumo-grid" style="margin-bottom:1.5rem">
        <div class="resumo-card receita">
            <div class="resumo-card-label">Receitas</div>
            <div class="resumo-card-valor" id="res-receitas">R$ 0,00</div>
        </div>
        <div class="resumo-card despesa">
            <div class="resumo-card-label">Despesas</div>
            <div class="resumo-card-valor" id="res-despesas">R$ 0,00</div>
        </div>
        <div class="resumo-card saldo positivo" id="res-saldo-card">
            <div class="resumo-card-label">Saldo</div>
            <div class="resumo-card-valor" id="res-saldo">R$ 0,00</div>
        </div>
    </div>

    <!-- Weekly transaction list -->
    <div id="lista-semanal">
        <div class="loading-center"><div class="spinner"></div></div>
    </div>
</main>

<button class="btn-fab" id="btn-fab" title="Nova Transação">+</button>
<div id="toast"></div>

<?php include 'includes/modal_transacao.php'; ?>

<script src="assets/js/app.js"></script>
<script src="assets/js/semanal.js"></script>
</body>
</html>

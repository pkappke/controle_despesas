<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias — Controle de Despesas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <a href="index.php" class="navbar-brand"><span>💰</span> Controle de Despesas</a>
    <div class="navbar-nav">
        <a href="index.php"       class="nav-link">Dashboard</a>
        <a href="mensal.php"      class="nav-link">Mensal</a>
        <a href="semanal.php"     class="nav-link">Semanal</a>
        <a href="categorias.php"  class="nav-link ativo">Categorias</a>
    </div>
    <div class="navbar-actions">
        <button class="btn-nova-transacao" id="btn-nova-transacao-nav">+ Nova Transação</button>
    </div>
    <button class="nav-toggle" id="nav-toggle" aria-label="Menu">☰</button>
</nav>

<main class="container">
    <h1 class="page-title">Gerenciar Categorias</h1>

    <div class="cats-grid">
        <!-- Receitas -->
        <div class="cats-col card">
            <h3>
                <span>💰 Receitas</span>
                <button class="btn btn-primary btn-sm" id="btn-nova-cat-receita">+ Nova</button>
            </h3>
            <div class="cat-list" id="lista-receitas">
                <div class="loading-center"><div class="spinner"></div></div>
            </div>
        </div>

        <!-- Despesas -->
        <div class="cats-col card">
            <h3>
                <span>💸 Despesas</span>
                <button class="btn btn-primary btn-sm" id="btn-nova-cat-despesa">+ Nova</button>
            </h3>
            <div class="cat-list" id="lista-despesas">
                <div class="loading-center"><div class="spinner"></div></div>
            </div>
        </div>
    </div>
</main>

<button class="btn-fab" id="btn-fab" title="Nova Transação">+</button>
<div id="toast"></div>

<!-- Transaction Modal (for FAB and nav button) -->
<?php include 'includes/modal_transacao.php'; ?>

<!-- Category Modal -->
<dialog id="modal-categoria" aria-modal="true">
    <div class="modal-header">
        <h2 id="modal-cat-titulo">Nova Categoria</h2>
        <button class="btn-fechar" id="btn-fechar-modal-cat" title="Fechar">×</button>
    </div>
    <form id="form-categoria" method="post" novalidate>
        <div class="modal-body">
            <input type="hidden" id="campo-cat-id">

            <div class="form-row">
                <label for="campo-cat-nome">Nome</label>
                <input type="text" id="campo-cat-nome" placeholder="Ex: Alimentação, Salário..." required maxlength="100">
            </div>

            <div class="form-row">
                <label>Tipo</label>
                <div class="radio-group">
                    <label class="radio-opt despesa">
                        <input type="radio" name="cat-tipo" value="despesa" checked>
                        💸 Despesa
                    </label>
                    <label class="radio-opt receita">
                        <input type="radio" name="cat-tipo" value="receita">
                        💰 Receita
                    </label>
                    <label class="radio-opt" style="flex:.8">
                        <input type="radio" name="cat-tipo" value="ambos">
                        🔄 Ambos
                    </label>
                </div>
            </div>

            <div class="form-row">
                <label for="campo-cat-cor">Cor</label>
                <input type="color" id="campo-cat-cor" value="#607D8B" style="width:100%;height:42px;padding:4px;border-radius:8px;border:1px solid #E0E0E0;cursor:pointer">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('modal-categoria').close()">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</dialog>

<script src="assets/js/app.js"></script>
<script src="assets/js/categorias.js"></script>
</body>
</html>

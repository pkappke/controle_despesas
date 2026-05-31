<dialog id="modal-transacao" aria-modal="true">
    <div class="modal-header">
        <h2 id="modal-titulo">Nova Transação</h2>
        <button class="btn-fechar" id="btn-fechar-modal" title="Fechar">×</button>
    </div>
    <form id="form-transacao" method="post" novalidate>
        <div class="modal-body">
            <input type="hidden" id="campo-id" name="id">

            <div class="form-row">
                <label>Tipo</label>
                <div class="radio-group">
                    <label class="radio-opt despesa">
                        <input type="radio" name="tipo" value="despesa" checked>
                        💸 Despesa
                    </label>
                    <label class="radio-opt receita">
                        <input type="radio" name="tipo" value="receita">
                        💰 Receita
                    </label>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-row">
                    <label for="campo-data">Data</label>
                    <input type="date" id="campo-data" name="data" required>
                </div>
                <div class="form-row">
                    <label for="campo-valor">Valor (R$)</label>
                    <input type="number" id="campo-valor" name="valor" min="0.01" step="0.01" placeholder="0,00" required>
                </div>
            </div>

            <div class="form-row">
                <label for="campo-descricao">Descrição</label>
                <input type="text" id="campo-descricao" name="descricao" placeholder="Ex: Mercado, Salário..." required maxlength="255">
            </div>

            <div class="form-row">
                <label for="campo-categoria">Categoria</label>
                <select id="campo-categoria" name="id_categoria" required>
                    <option value="">Selecione...</option>
                </select>
            </div>

            <div class="form-row">
                <label for="campo-observacao">Observação <span style="font-weight:400;text-transform:none">(opcional)</span></label>
                <textarea id="campo-observacao" name="observacao" placeholder="Detalhes adicionais..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" id="btn-cancelar-modal" onclick="fecharModal()">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</dialog>

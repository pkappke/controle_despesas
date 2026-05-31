let _estadoSemana = null;

function getISOWeek(date) {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    const dayNum = d.getUTCDay() || 7;
    d.setUTCDate(d.getUTCDate() + 4 - dayNum);
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return { semana: Math.ceil((((d - yearStart) / 86400000) + 1) / 7), ano: d.getUTCFullYear() };
}

function getEstadoSemana() {
    if (_estadoSemana) return _estadoSemana;
    const salvo = localStorage.getItem('cd_semana');
    if (salvo) { try { _estadoSemana = JSON.parse(salvo); } catch(_) {} }
    if (!_estadoSemana) _estadoSemana = getISOWeek(new Date());
    return _estadoSemana;
}

function salvarEstadoSemana() {
    localStorage.setItem('cd_semana', JSON.stringify(_estadoSemana));
}

// Get start (Monday) and end (Sunday) of ISO week
function datasISOSemana(ano, semana) {
    const jan4 = new Date(Date.UTC(ano, 0, 4));
    const startOfWeek1 = new Date(jan4);
    startOfWeek1.setUTCDate(jan4.getUTCDate() - (jan4.getUTCDay() || 7) + 1);
    const start = new Date(startOfWeek1);
    start.setUTCDate(startOfWeek1.getUTCDate() + (semana - 1) * 7);
    const end = new Date(start);
    end.setUTCDate(start.getUTCDate() + 6);
    return { start, end };
}

function formatDataCurta(d) {
    return `${String(d.getUTCDate()).padStart(2,'0')}/${String(d.getUTCMonth()+1).padStart(2,'0')}`;
}

function navSemana(delta) {
    const e = getEstadoSemana();
    const { start } = datasISOSemana(e.ano, e.semana);
    start.setUTCDate(start.getUTCDate() + delta * 7);
    _estadoSemana = getISOWeek(new Date(start.getUTCFullYear(), start.getUTCMonth(), start.getUTCDate()));
    salvarEstadoSemana();
    renderizarSeletorSemana();
    carregarSemanal();
}

function irSemanaAtual() {
    _estadoSemana = getISOWeek(new Date());
    salvarEstadoSemana();
    renderizarSeletorSemana();
    carregarSemanal();
}

function renderizarSeletorSemana() {
    const { ano, semana } = getEstadoSemana();
    const { start, end } = datasISOSemana(ano, semana);
    const el = document.getElementById('titulo-semana');
    if (el) el.textContent = `Semana ${semana} de ${ano} (${formatDataCurta(start)} – ${formatDataCurta(end)})`;
}

async function carregarSemanal() {
    const { ano, semana } = getEstadoSemana();
    const container = document.getElementById('lista-semanal');
    if (container) container.innerHTML = '<div class="loading-center"><div class="spinner"></div></div>';

    const res = await apiGet('api/transacoes.php', { ano, semana });

    if (!res.success) {
        toast(res.error ?? 'Erro ao carregar.', 'error');
        return;
    }

    renderizarResumoSemanal(res.totais);
    renderizarListaSemanal(res.data, res.totais, container);
}

function renderizarResumoSemanal(totais) {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    set('res-receitas', formatMoeda(totais.receitas));
    set('res-despesas', formatMoeda(totais.despesas));
    set('res-saldo',    formatMoeda(totais.saldo));
    const saldoEl = document.getElementById('res-saldo-card');
    if (saldoEl) saldoEl.className = `resumo-card saldo ${totais.saldo >= 0 ? 'positivo' : 'negativo'}`;
}

function renderizarListaSemanal(transacoes, totais, container) {
    const { ano, semana } = getEstadoSemana();
    const { start, end } = datasISOSemana(ano, semana);

    if (!transacoes.length) {
        container.innerHTML = `
        <div class="semana-bloco">
            <div class="semana-header">
                <h3>Semana ${semana} — ${formatDataCurta(start)} a ${formatDataCurta(end)}</h3>
                <div class="semana-totais"><span class="sal pos">Sem transações</span></div>
            </div>
            <div class="tabela-wrapper" style="border-radius:0 0 12px 12px">
                <table><tbody><tr><td colspan="5" style="text-align:center;padding:2rem;color:#9E9E9E">
                    Nenhuma transação nesta semana.
                </td></tr></tbody></table>
            </div>
        </div>`;
        return;
    }

    const saldoCor = totais.saldo >= 0 ? 'pos' : 'neg';
    const saldoStr = (totais.saldo >= 0 ? '+' : '') + formatMoeda(totais.saldo);

    container.innerHTML = `
    <div class="semana-bloco">
        <div class="semana-header">
            <h3>Semana ${semana} — ${formatDataCurta(start)} a ${formatDataCurta(end)}</h3>
            <div class="semana-totais">
                <span class="rec">+${formatMoeda(totais.receitas)}</span>
                <span class="des">-${formatMoeda(totais.despesas)}</span>
                <span class="sal ${saldoCor}">${saldoStr}</span>
            </div>
        </div>
        <div class="tabela-wrapper" style="border-radius:0 0 12px 12px">
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th class="col-obs">Obs.</th>
                        <th style="text-align:right">Valor</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    ${transacoes.map(t => linhaTransacaoSemanal(t)).join('')}
                </tbody>
            </table>
        </div>
    </div>`;
}

function linhaTransacaoSemanal(t) {
    const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    return `
    <tr>
        <td style="white-space:nowrap">${formatData(t.data)}</td>
        <td><span class="tipo-badge ${t.tipo}">${t.tipo === 'receita' ? 'Receita' : 'Despesa'}</span></td>
        <td>
            <div>${esc(t.descricao)}</div>
            <span class="cat-badge" style="background:${t.cor};color:${t.texto_cor}">${esc(t.categoria)}</span>
        </td>
        <td class="col-obs td-obs">${esc(t.observacao)}</td>
        <td class="${t.tipo === 'receita' ? 'valor-receita' : 'valor-despesa'}" style="text-align:right;white-space:nowrap">
            ${t.tipo === 'receita' ? '+' : '-'}${formatMoeda(t.valor)}
        </td>
        <td>
            <div class="acoes">
                <button class="btn-acao editar" title="Editar" onclick="editarTransacao(${t.id})">✏️</button>
                <button class="btn-acao excluir" title="Excluir" onclick="excluirTransacao(${t.id})">🗑️</button>
            </div>
        </td>
    </tr>`;
}

function recarregarDados() { carregarSemanal(); }

document.addEventListener('DOMContentLoaded', () => {
    renderizarSeletorSemana();
    carregarSemanal();

    document.getElementById('btn-semana-anterior')?.addEventListener('click', () => navSemana(-1));
    document.getElementById('btn-semana-seguinte')?.addEventListener('click', () => navSemana(+1));
    document.getElementById('btn-semana-atual')?.addEventListener('click', irSemanaAtual);
});

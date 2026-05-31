let _estadoMensal = null;

function getEstadoMensal() {
    if (_estadoMensal) return _estadoMensal;
    const salvo = localStorage.getItem('cd_mes');
    if (salvo) { try { _estadoMensal = JSON.parse(salvo); } catch(_) {} }
    if (!_estadoMensal) {
        const now = new Date();
        _estadoMensal = { ano: now.getFullYear(), mes: now.getMonth() + 1 };
    }
    return _estadoMensal;
}

function salvarEstadoMensal() {
    localStorage.setItem('cd_mes', JSON.stringify(_estadoMensal));
}

function renderizarSeletorMensal() {
    const { ano, mes } = getEstadoMensal();
    const el = document.getElementById('titulo-mes');
    if (el) el.textContent = `${nomeMes(mes)} de ${ano}`;
}

function navMensal(delta) {
    const e = getEstadoMensal();
    let m = e.mes + delta, a = e.ano;
    if (m > 12) { m = 1; a++; }
    if (m < 1)  { m = 12; a--; }
    _estadoMensal = { ano: a, mes: m };
    salvarEstadoMensal();
    renderizarSeletorMensal();
    carregarMensal();
}

function irMesAtualMensal() {
    const now = new Date();
    _estadoMensal = { ano: now.getFullYear(), mes: now.getMonth() + 1 };
    salvarEstadoMensal();
    renderizarSeletorMensal();
    carregarMensal();
}

async function carregarMensal() {
    const { ano, mes } = getEstadoMensal();
    const container = document.getElementById('lista-mensal');
    if (container) container.innerHTML = '<div class="loading-center"><div class="spinner"></div></div>';

    const res = await apiGet('api/transacoes.php', { ano, mes });

    if (!res.success) {
        toast(res.error ?? 'Erro ao carregar.', 'error');
        return;
    }

    renderizarResumoMensal(res.totais);
    renderizarListaMensal(res.data, container);
    renderizarRodapeMensal(res.totais);
}

function renderizarResumoMensal(totais) {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    set('res-receitas', formatMoeda(totais.receitas));
    set('res-despesas', formatMoeda(totais.despesas));
    set('res-saldo',    formatMoeda(totais.saldo));

    const saldoEl = document.getElementById('res-saldo-card');
    if (saldoEl) saldoEl.className = `resumo-card saldo ${totais.saldo >= 0 ? 'positivo' : 'negativo'}`;
}

function renderizarListaMensal(transacoes, container) {
    if (!transacoes.length) {
        container.innerHTML = '<div class="vazio"><p>Nenhuma transação neste mês.</p></div>';
        return;
    }

    // Group by date
    const grupos = {};
    transacoes.forEach(t => {
        if (!grupos[t.data]) grupos[t.data] = [];
        grupos[t.data].push(t);
    });

    const datas = Object.keys(grupos).sort((a,b) => b.localeCompare(a));

    container.innerHTML = datas.map(data => {
        const rows = grupos[data];
        const subRec = rows.filter(r => r.tipo==='receita').reduce((s,r) => s+Number(r.valor),0);
        const subDes = rows.filter(r => r.tipo==='despesa').reduce((s,r) => s+Number(r.valor),0);

        const [ano, mes, dia] = data.split('-');
        const nomeData = `${Number(dia)} de ${nomeMes(Number(mes))} de ${ano}`;

        const subLabel = [
            subRec > 0 ? `<span class="valor-receita">+${formatMoeda(subRec)}</span>` : '',
            subDes > 0 ? `<span class="valor-despesa">-${formatMoeda(subDes)}</span>` : '',
        ].filter(Boolean).join(' ');

        return `
        <div class="grupo-dia">
            <div class="grupo-dia-header">
                <span>${nomeData}</span>
                <span class="subtotal">${subLabel}</span>
            </div>
            <div class="tabela-wrapper" style="border-radius:0 0 12px 12px">
                <table>
                    <tbody>
                        ${rows.map(t => linhaTransacao(t)).join('')}
                    </tbody>
                </table>
            </div>
        </div>`;
    }).join('');
}

function linhaTransacao(t) {
    return `
    <tr>
        <td><span class="tipo-badge ${t.tipo}">${t.tipo === 'receita' ? 'Receita' : 'Despesa'}</span></td>
        <td>
            <div>${escHtmlMensal(t.descricao)}</div>
            <span class="cat-badge" style="background:${t.cor};color:${t.texto_cor}">${escHtmlMensal(t.categoria)}</span>
        </td>
        <td class="col-obs td-obs">${t.observacao ? escHtmlMensal(t.observacao) : ''}</td>
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

function escHtmlMensal(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function renderizarRodapeMensal(totais) {
    const el = document.getElementById('rodape-mensal');
    if (!el) return;
    const saldoCor = totais.saldo >= 0 ? 'valor-receita' : 'valor-despesa';
    el.innerHTML = `
        <span><span class="label">Receitas:</span><span class="valor-receita">${formatMoeda(totais.receitas)}</span></span>
        <span><span class="label">Despesas:</span><span class="valor-despesa">${formatMoeda(totais.despesas)}</span></span>
        <span><span class="label">Saldo:</span><span class="${saldoCor}">${formatMoeda(totais.saldo)}</span></span>
    `;
}

function recarregarDados() { carregarMensal(); }

document.addEventListener('DOMContentLoaded', () => {
    renderizarSeletorMensal();
    carregarMensal();

    document.getElementById('btn-mes-anterior')?.addEventListener('click', () => navMensal(-1));
    document.getElementById('btn-mes-seguinte')?.addEventListener('click', () => navMensal(+1));
    document.getElementById('btn-mes-atual')?.addEventListener('click', irMesAtualMensal);
});

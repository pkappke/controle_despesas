let _graficoDona    = null;
let _graficoBarra   = null;
let _estadoMes      = null;

function getEstadoMes() {
    if (_estadoMes) return _estadoMes;
    const salvo = localStorage.getItem('cd_mes');
    if (salvo) {
        try { _estadoMes = JSON.parse(salvo); } catch(_) {}
    }
    if (!_estadoMes) {
        const now = new Date();
        _estadoMes = { ano: now.getFullYear(), mes: now.getMonth() + 1 };
    }
    return _estadoMes;
}

function salvarEstadoMes() {
    localStorage.setItem('cd_mes', JSON.stringify(_estadoMes));
}

function renderizarSeletorMes() {
    const { ano, mes } = getEstadoMes();
    const el = document.getElementById('titulo-mes');
    if (el) el.textContent = `${nomeMes(mes)} de ${ano}`;
}

function navMes(delta) {
    const e = getEstadoMes();
    let m = e.mes + delta;
    let a = e.ano;
    if (m > 12) { m = 1; a++; }
    if (m < 1)  { m = 12; a--; }
    _estadoMes = { ano: a, mes: m };
    salvarEstadoMes();
    renderizarSeletorMes();
    carregarDashboard();
}

function irMesAtual() {
    const now = new Date();
    _estadoMes = { ano: now.getFullYear(), mes: now.getMonth() + 1 };
    salvarEstadoMes();
    renderizarSeletorMes();
    carregarDashboard();
}

async function carregarDashboard() {
    const { ano, mes } = getEstadoMes();
    mostrarLoading(true);

    const res = await apiGet('api/dashboard.php', { ano, mes });
    mostrarLoading(false);

    if (!res.success) {
        toast(res.error ?? 'Erro ao carregar dashboard.', 'error');
        return;
    }

    renderizarCards(res.resumo);
    renderizarGraficoDona(res.por_categoria);
    renderizarGraficoBarra(res.por_dia, res.resumo);
    renderizarRecentes(res.recentes);
}

function mostrarLoading(sim) {
    const el = document.getElementById('loading-dash');
    const conteudo = document.getElementById('dash-conteudo');
    if (el) el.style.display = sim ? 'flex' : 'none';
    if (conteudo) conteudo.style.display = sim ? 'none' : 'block';
}

function renderizarCards(resumo) {
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    set('card-receitas', formatMoeda(resumo.total_receitas));
    set('card-despesas', formatMoeda(resumo.total_despesas));
    set('card-saldo',    formatMoeda(resumo.saldo));
    set('card-num',      resumo.num_transacoes);

    const saldoCard = document.getElementById('resumo-saldo');
    if (saldoCard) {
        saldoCard.className = `resumo-card saldo ${resumo.saldo >= 0 ? 'positivo' : 'negativo'}`;
    }
}

function renderizarGraficoDona(porCategoria) {
    const canvas = document.getElementById('grafico-categorias');
    if (!canvas) return;

    if (_graficoDona) { _graficoDona.destroy(); _graficoDona = null; }

    if (!porCategoria.length) {
        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('sem-dados-dona')?.style && (document.getElementById('sem-dados-dona').style.display = 'block');
        return;
    }
    document.getElementById('sem-dados-dona') && (document.getElementById('sem-dados-dona').style.display = 'none');

    _graficoDona = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: porCategoria.map(c => c.categoria),
            datasets: [{
                data: porCategoria.map(c => c.total),
                backgroundColor: porCategoria.map(c => c.cor),
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right', labels: { font: { size: 12 }, padding: 12 } },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${formatMoeda(ctx.raw)}`
                    }
                }
            },
            cutout: '60%',
        }
    });
}

function renderizarGraficoBarra(porDia, resumo) {
    const canvas = document.getElementById('grafico-mensal');
    if (!canvas) return;

    if (_graficoBarra) { _graficoBarra.destroy(); _graficoBarra = null; }

    if (!porDia.length) return;

    _graficoBarra = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: porDia.map(d => String(d.dia)),
            datasets: [
                {
                    label: 'Receitas',
                    data: porDia.map(d => d.receitas),
                    backgroundColor: 'rgba(46,125,50,.75)',
                    borderRadius: 4,
                },
                {
                    label: 'Despesas',
                    data: porDia.map(d => d.despesas),
                    backgroundColor: 'rgba(198,40,40,.7)',
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 12 } } },
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.dataset.label}: ${formatMoeda(ctx.raw)}` }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => 'R$' + Number(v).toLocaleString('pt-BR'),
                        font: { size: 11 }
                    },
                    grid: { color: '#f0f0f0' }
                },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });
}

function renderizarRecentes(recentes) {
    const tbody = document.getElementById('tbody-recentes');
    if (!tbody) return;

    if (!recentes.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="vazio" style="padding:2rem;text-align:center;color:#9E9E9E">
            Nenhuma transação neste mês.</td></tr>`;
        return;
    }

    tbody.innerHTML = recentes.map(t => `
        <tr>
            <td>${formatData(t.data)}</td>
            <td><span class="tipo-badge ${t.tipo}">${t.tipo === 'receita' ? 'Receita' : 'Despesa'}</span></td>
            <td>
                <div>${t.descricao}</div>
                <span class="cat-badge" style="background:${t.cor};color:${t.texto_cor}">${t.categoria}</span>
            </td>
            <td class="${t.tipo === 'receita' ? 'valor-receita' : 'valor-despesa'}">${formatMoeda(t.valor)}</td>
            <td>
                <div class="acoes">
                    <button class="btn-acao editar" title="Editar" onclick="editarTransacao(${t.id})">✏️</button>
                    <button class="btn-acao excluir" title="Excluir" onclick="excluirTransacao(${t.id})">🗑️</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function recarregarDados() { carregarDashboard(); }

document.addEventListener('DOMContentLoaded', () => {
    renderizarSeletorMes();
    carregarDashboard();

    document.getElementById('btn-mes-anterior')?.addEventListener('click', () => navMes(-1));
    document.getElementById('btn-mes-seguinte')?.addEventListener('click', () => navMes(+1));
    document.getElementById('btn-mes-atual')?.addEventListener('click', irMesAtual);
});

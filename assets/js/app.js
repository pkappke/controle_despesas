// ── Fetch Helpers ──────────────────────────────────────────────
async function apiGet(url, params = {}) {
    const qs = new URLSearchParams(params).toString();
    const res = await fetch(qs ? `${url}?${qs}` : url);
    return res.json();
}

async function apiPost(url, data) {
    const body = data instanceof FormData ? data : (() => {
        const fd = new FormData();
        Object.entries(data).forEach(([k, v]) => fd.append(k, v ?? ''));
        return fd;
    })();
    const res = await fetch(url, { method: 'POST', body });
    return res.json();
}

// ── Formatters ─────────────────────────────────────────────────
const fmtMoeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

function formatMoeda(valor) {
    return fmtMoeda.format(Number(valor));
}

function formatData(iso) {
    if (!iso) return '';
    const [a, m, d] = iso.split('-');
    return `${d}/${m}/${a}`;
}

function nomeMes(num) {
    const meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                   'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    return meses[Number(num) - 1] ?? '';
}

// ── Toast ──────────────────────────────────────────────────────
let _toastTimer = null;
function toast(msg, tipo = 'success') {
    let el = document.getElementById('toast');
    if (!el) {
        el = document.createElement('div');
        el.id = 'toast';
        document.body.appendChild(el);
    }
    el.textContent = msg;
    el.className = `visivel ${tipo}`;
    clearTimeout(_toastTimer);
    _toastTimer = setTimeout(() => { el.className = tipo; }, 3200);
}

// ── Modal ──────────────────────────────────────────────────────
function getModal() { return document.getElementById('modal-transacao'); }

async function abrirModal(dados = null) {
    const dlg = getModal();
    if (!dlg) return;

    // Título
    dlg.querySelector('#modal-titulo').textContent = dados ? 'Editar Transação' : 'Nova Transação';

    // Reset form
    const form = dlg.querySelector('#form-transacao');
    form.reset();
    dlg.querySelector('#campo-id').value = '';

    // Default date = today
    const hoje = new Date().toISOString().slice(0, 10);
    dlg.querySelector('#campo-data').value = hoje;

    // Set tipo
    const tipo = dados?.tipo ?? 'despesa';
    dlg.querySelector(`input[name="tipo"][value="${tipo}"]`).checked = true;

    // Load categorias filtered by tipo
    await carregarCatsNoModal(tipo);

    if (dados) {
        dlg.querySelector('#campo-id').value          = dados.id;
        dlg.querySelector('#campo-descricao').value   = dados.descricao;
        dlg.querySelector('#campo-valor').value       = dados.valor;
        dlg.querySelector('#campo-data').value        = dados.data;
        dlg.querySelector('#campo-categoria').value   = dados.id_categoria;
        dlg.querySelector('#campo-observacao').value  = dados.observacao ?? '';
    }

    dlg.showModal();
}

async function carregarCatsNoModal(tipo) {
    const sel = document.getElementById('campo-categoria');
    if (!sel) return;
    const catAtual = sel.value;
    sel.innerHTML = '<option value="">Carregando...</option>';

    const res = await apiGet('api/categorias.php', { tipo });
    sel.innerHTML = '<option value="">Selecione...</option>';
    if (res.success) {
        res.data.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nome;
            if (catAtual && String(c.id) === String(catAtual)) opt.selected = true;
            sel.appendChild(opt);
        });
    }
}

function fecharModal() {
    const dlg = getModal();
    if (dlg) dlg.close();
}

// ── Transaction Save ───────────────────────────────────────────
async function salvarTransacao(e) {
    e.preventDefault();
    const form = e.target;
    const btn  = form.querySelector('[type="submit"]');
    btn.disabled = true;

    const fd = new FormData(form);
    const res = await apiPost('api/transacoes_salvar.php', fd);

    btn.disabled = false;

    if (res.success) {
        fecharModal();
        toast('Transação salva com sucesso!');
        if (typeof recarregarDados === 'function') recarregarDados();
    } else {
        toast(res.error ?? 'Erro ao salvar.', 'error');
    }
}

async function editarTransacao(id) {
    const res = await apiGet('api/transacoes.php', { id });
    if (res.success) {
        await abrirModal(res.data);
    } else {
        toast('Erro ao carregar transação.', 'error');
    }
}

async function excluirTransacao(id) {
    if (!confirm('Excluir esta transação?')) return;
    const res = await apiPost('api/transacoes_excluir.php', { id });
    if (res.success) {
        toast('Transação excluída.');
        if (typeof recarregarDados === 'function') recarregarDados();
    } else {
        toast(res.error ?? 'Erro ao excluir.', 'error');
    }
}

// ── Modal Event Wiring ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Form submit
    const form = document.getElementById('form-transacao');
    if (form) form.addEventListener('submit', salvarTransacao);

    // Close button
    document.getElementById('btn-fechar-modal')?.addEventListener('click', fecharModal);

    // Close on backdrop click
    getModal()?.addEventListener('click', e => { if (e.target === getModal()) fecharModal(); });

    // Tipo radio → reload categorias
    document.querySelectorAll('input[name="tipo"]').forEach(r => {
        r.addEventListener('change', () => carregarCatsNoModal(r.value));
    });

    // Nova Transação buttons
    document.getElementById('btn-nova-transacao-nav')?.addEventListener('click', () => abrirModal());
    document.getElementById('btn-fab')?.addEventListener('click', () => abrirModal());

    // Mobile nav toggle
    document.getElementById('nav-toggle')?.addEventListener('click', () => {
        document.querySelector('.navbar-nav')?.classList.toggle('aberto');
    });
});

let _catModalModo = null; // {id, tipo} ou null para novo

async function carregarCategorias() {
    const res = await apiGet('api/categorias.php');
    if (!res.success) { toast('Erro ao carregar categorias.', 'error'); return; }

    const receitas = res.data.filter(c => c.tipo === 'receita' || c.tipo === 'ambos');
    const despesas = res.data.filter(c => c.tipo === 'despesa' || c.tipo === 'ambos');

    renderizarColuna('lista-receitas', receitas);
    renderizarColuna('lista-despesas', despesas);
}

function renderizarColuna(elId, cats) {
    const el = document.getElementById(elId);
    if (!el) return;
    if (!cats.length) {
        el.innerHTML = '<p style="color:#9E9E9E;font-size:.88rem">Nenhuma categoria.</p>';
        return;
    }
    el.innerHTML = cats.map(c => `
        <span class="cat-item" style="background:${c.cor};color:${c.texto_cor}">
            ${escHtml(c.nome)}
            <span class="cat-item-acoes">
                <button class="btn-cat" title="Editar" onclick="abrirModalCat(${c.id})">✏️</button>
                <button class="btn-cat" title="Excluir" onclick="excluirCategoria(${c.id}, '${escHtml(c.nome)}')">×</button>
            </span>
        </span>
    `).join('');
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function abrirModalCat(id = null) {
    const dlg = document.getElementById('modal-categoria');
    if (!dlg) return;

    document.getElementById('modal-cat-titulo').textContent = id ? 'Editar Categoria' : 'Nova Categoria';
    document.getElementById('form-categoria').reset();
    document.getElementById('campo-cat-id').value = '';

    if (id) {
        const res = await apiGet('api/categorias.php');
        if (res.success) {
            const cat = res.data.find(c => c.id == id);
            if (cat) {
                document.getElementById('campo-cat-id').value   = cat.id;
                document.getElementById('campo-cat-nome').value = cat.nome;
                document.getElementById('campo-cat-cor').value  = cat.cor;
                const radio = document.querySelector(`input[name="cat-tipo"][value="${cat.tipo}"]`);
                if (radio) radio.checked = true;
            }
        }
    }

    dlg.showModal();
}

async function salvarCategoria(e) {
    e.preventDefault();
    const form = e.target;
    const btn  = form.querySelector('[type="submit"]');
    btn.disabled = true;

    const fd = new FormData(form);
    // Remap field names to API expected names
    const data = new FormData();
    data.append('id',   form.querySelector('#campo-cat-id').value);
    data.append('nome', form.querySelector('#campo-cat-nome').value);
    data.append('tipo', form.querySelector('input[name="cat-tipo"]:checked')?.value ?? '');
    data.append('cor',  form.querySelector('#campo-cat-cor').value);

    const res = await apiPost('api/categorias_salvar.php', data);
    btn.disabled = false;

    if (res.success) {
        document.getElementById('modal-categoria').close();
        toast('Categoria salva!');
        carregarCategorias();
    } else {
        toast(res.error ?? 'Erro ao salvar.', 'error');
    }
}

async function excluirCategoria(id, nome) {
    if (!confirm(`Excluir a categoria "${nome}"?`)) return;
    const res = await apiPost('api/categorias_excluir.php', { id });
    if (res.success) {
        toast('Categoria excluída.');
        carregarCategorias();
    } else {
        toast(res.error ?? 'Erro ao excluir.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    carregarCategorias();

    document.getElementById('form-categoria')?.addEventListener('submit', salvarCategoria);

    document.getElementById('btn-fechar-modal-cat')?.addEventListener('click', () => {
        document.getElementById('modal-categoria').close();
    });
    document.getElementById('modal-categoria')?.addEventListener('click', e => {
        if (e.target === document.getElementById('modal-categoria'))
            document.getElementById('modal-categoria').close();
    });

    document.getElementById('btn-nova-cat-receita')?.addEventListener('click', () => {
        abrirModalCat();
        setTimeout(() => {
            const r = document.querySelector('input[name="cat-tipo"][value="receita"]');
            if (r) r.checked = true;
        }, 50);
    });
    document.getElementById('btn-nova-cat-despesa')?.addEventListener('click', () => {
        abrirModalCat();
        setTimeout(() => {
            const r = document.querySelector('input[name="cat-tipo"][value="despesa"]');
            if (r) r.checked = true;
        }, 50);
    });
});
